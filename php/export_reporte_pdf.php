<?php
// HU-22 | SCRUM-HU22-04 | Misael Juarez
session_start();

require_once __DIR__ . '/conexion.php';

function bindPdfParams(mysqli_stmt $stmt, string $types, array &$params): void
{
    $refs = [];
    foreach ($params as $key => &$value) {
        $refs[$key] = &$value;
    }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

if (!isset($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? '') !== 'Administrador') {
    http_response_code(403);
    exit('Acceso denegado. Se requiere rol Administrador.');
}

$estado = trim($_GET['estado'] ?? '');
$cliente = isset($_GET['cliente']) && $_GET['cliente'] !== '' ? (int) $_GET['cliente'] : 0;
$estadosValidos = ['Pendiente', 'En Proceso', 'Completado', 'Cancelado'];

if ($estado !== '' && !in_array($estado, $estadosValidos, true)) {
    http_response_code(400);
    exit('Estado invalido para el reporte.');
}

$conn = getConexion();
$where = [];
$types = '';
$params = [];

if ($estado !== '') {
    $where[] = 'p.estado = ?';
    $types .= 's';
    $params[] = $estado;
}

if ($cliente > 0) {
    $where[] = 'p.id_cliente = ?';
    $types .= 'i';
    $params[] = $cliente;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT
            p.id_proyecto,
            p.nombre,
            c.nombre_empresa AS cliente,
            p.estado,
            p.fecha_inicio,
            p.fecha_entrega,
            COUNT(e.id_entregable) AS total_entregables
        FROM proyecto p
        LEFT JOIN cliente c ON p.id_cliente = c.id_cliente
        LEFT JOIN entregable e ON e.id_proyecto = p.id_proyecto
        $whereSql
        GROUP BY p.id_proyecto, p.nombre, c.nombre_empresa, p.estado, p.fecha_inicio, p.fecha_entrega
        ORDER BY p.fecha_entrega IS NULL, p.fecha_entrega ASC, p.id_proyecto DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    exit('Error al preparar consulta: ' . $conn->error);
}
if ($types !== '') {
    bindPdfParams($stmt, $types, $params);
}
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
$stmt->close();
$conn->close();

function pdfText(string $text): string
{
    $text = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
    if ($text === false) {
        $text = '';
    }
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

function generarPdfReporte(array $rows, string $estado, int $cliente): string
{
    $contentObjects = [];
    $pageObjects = [];
    $pageRefs = [];
    $lineHeight = 14;
    $maxLines = 42;
    $lines = [];
    $lines[] = ['TechNova - Reporte de estado de proyectos', 20, true];
    $filters = 'Filtros: Estado=' . ($estado !== '' ? $estado : 'Todos') . ' | Cliente=' . ($cliente > 0 ? (string) $cliente : 'Todos');
    $lines[] = [$filters, 11, false];
    $lines[] = ['ID | Proyecto | Cliente | Estado | Inicio | Entrega | Entregables', 10, true];

    foreach ($rows as $row) {
        $line = sprintf(
            '%s | %s | %s | %s | %s | %s | %s',
            $row['id_proyecto'],
            $row['nombre'],
            $row['cliente'] ?: 'Sin cliente',
            $row['estado'],
            $row['fecha_inicio'] ?: '-',
            $row['fecha_entrega'] ?: '-',
            $row['total_entregables']
        );
        $lines[] = [$line, 9, false];
    }

    if (!$rows) {
        $lines[] = ['Sin resultados para los filtros seleccionados.', 11, false];
    }

    $chunks = array_chunk($lines, $maxLines);
    foreach ($chunks as $index => $chunk) {
        $content = "BT\n/F1 10 Tf\n50 790 Td\n";
        $first = true;
        foreach ($chunk as $item) {
            [$text, $size, $bold] = $item;
            if (!$first) {
                $content .= "0 -$lineHeight Td\n";
            }
            $font = $bold ? 'F2' : 'F1';
            $content .= "/$font $size Tf (" . pdfText(substr($text, 0, 120)) . ") Tj\n";
            $first = false;
        }
        $content .= "ET";
        $contentId = 5 + ($index * 2);
        $pageId = $contentId + 1;
        $contentObjects[] = "<< /Length " . strlen($content) . " >>\nstream\n$content\nendstream";
        $pageObjects[] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents $contentId 0 R >>";
        $pageRefs[] = "$pageId 0 R";
    }

    $objects = [
        "<< /Type /Catalog /Pages 2 0 R >>",
        "<< /Type /Pages /Kids [" . implode(' ', $pageRefs) . "] /Count " . count($pageRefs) . " >>",
        "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
        "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>"
    ];

    foreach ($contentObjects as $i => $contentObject) {
        $objects[] = $contentObject;
        $objects[] = $pageObjects[$i];
    }

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $i => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($i + 1) . " 0 obj\n$object\nendobj\n";
    }

    $xref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";

    return $pdf;
}

$pdf = generarPdfReporte($rows, $estado, $cliente);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="reporte_proyectos.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
?>
