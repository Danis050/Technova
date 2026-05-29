<?php
// entregables_lista.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}

$id_proyecto = intval($_GET['id_proyecto'] ?? 0);
if ($id_proyecto <= 0) {
    echo json_encode(['error' => true, 'mensaje' => 'id_proyecto requerido']); exit;
}

$conn = getConexion();

$stmt = $conn->prepare("
    SELECT 
        e.id_entregable,
        e.id_proyecto,
        e.nombre,
        e.descripcion,
        e.id_responsable,
        CONCAT(u.nombre, ' ', u.apellido) AS nombre_responsable,
        e.fecha_estimada,
        e.estado,
        e.creado_en
    FROM entregable e
    INNER JOIN usuario u ON u.id_usuario = e.id_responsable
    WHERE e.id_proyecto = ?
    ORDER BY e.fecha_estimada ASC, e.id_entregable ASC
");
$stmt->bind_param('i', $id_proyecto);
$stmt->execute();
$result = $stmt->get_result();

$entregables = [];
while ($row = $result->fetch_assoc()) {
    $entregables[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($entregables, JSON_UNESCAPED_UNICODE);
?>