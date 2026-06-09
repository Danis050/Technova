<?php
// HU-22 | Soporte funcional para SCRUM-HU22-03 | Misael Juarez
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';

function responderReporte(bool $error, string $mensaje, array $extra = []): void
{
    echo json_encode(array_merge([
        'error' => $error,
        'success' => !$error,
        'mensaje' => $mensaje
    ], $extra));
    exit;
}

function bindReporteParams(mysqli_stmt $stmt, string $types, array &$params): void
{
    $refs = [];
    foreach ($params as $key => &$value) {
        $refs[$key] = &$value;
    }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

if (!isset($_SESSION['id_usuario'])) {
    responderReporte(true, 'No hay sesion activa. Inicie sesion.');
}

if (($_SESSION['rol'] ?? '') !== 'Administrador') {
    responderReporte(true, 'Acceso denegado. Se requiere rol Administrador.');
}

$estado = trim($_GET['estado'] ?? '');
$cliente = isset($_GET['cliente']) && $_GET['cliente'] !== '' ? (int) $_GET['cliente'] : 0;
$estadosValidos = ['Pendiente', 'En Proceso', 'Completado', 'Cancelado'];

if ($estado !== '' && !in_array($estado, $estadosValidos, true)) {
    responderReporte(true, 'Estado invalido para el reporte.');
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
            c.id_cliente,
            c.nombre_empresa AS cliente,
            p.estado,
            p.fecha_inicio,
            p.fecha_entrega,
            COUNT(e.id_entregable) AS total_entregables
        FROM proyecto p
        LEFT JOIN cliente c ON p.id_cliente = c.id_cliente
        LEFT JOIN entregable e ON e.id_proyecto = p.id_proyecto
        $whereSql
        GROUP BY p.id_proyecto, p.nombre, c.id_cliente, c.nombre_empresa, p.estado, p.fecha_inicio, p.fecha_entrega
        ORDER BY p.fecha_entrega IS NULL, p.fecha_entrega ASC, p.id_proyecto DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    responderReporte(true, 'Error al preparar consulta: ' . $conn->error);
}
if ($types !== '') {
    bindReporteParams($stmt, $types, $params);
}
$stmt->execute();
$result = $stmt->get_result();

$proyectos = [];
$contadores = [
    'Total' => 0,
    'Pendiente' => 0,
    'En Proceso' => 0,
    'Completado' => 0,
    'Cancelado' => 0
];

while ($row = $result->fetch_assoc()) {
    $estadoProyecto = $row['estado'];
    if (array_key_exists($estadoProyecto, $contadores)) {
        $contadores[$estadoProyecto]++;
    }
    $contadores['Total']++;

    $proyectos[] = [
        'id' => (int) $row['id_proyecto'],
        'nombre' => $row['nombre'],
        'id_cliente' => (int) $row['id_cliente'],
        'cliente' => $row['cliente'] ?? 'Sin cliente',
        'estado' => $estadoProyecto,
        'fecha_inicio' => $row['fecha_inicio'],
        'fecha_entrega' => $row['fecha_entrega'],
        'total_entregables' => (int) $row['total_entregables']
    ];
}
$stmt->close();

$clientes = [];
$resClientes = $conn->query("SELECT id_cliente, nombre_empresa FROM cliente ORDER BY nombre_empresa ASC");
while ($clienteRow = $resClientes->fetch_assoc()) {
    $clientes[] = [
        'id' => (int) $clienteRow['id_cliente'],
        'nombre' => $clienteRow['nombre_empresa']
    ];
}

$conn->close();

responderReporte(false, 'Reporte generado correctamente.', [
    'contadores' => $contadores,
    'proyectos' => $proyectos,
    'clientes' => $clientes
]);
?>
