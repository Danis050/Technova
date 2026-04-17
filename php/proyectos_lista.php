<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$conn = getConexion();

$result = $conn->query(
    "SELECT p.id_proyecto, p.nombre AS nombre_proyecto, p.id_cliente,
            c.nombre_empresa AS nombre_cliente,
            p.estado, p.fecha_inicio, p.fecha_entrega AS fecha_fin
     FROM proyecto p
     LEFT JOIN cliente c ON p.id_cliente = c.id_cliente
     ORDER BY p.id_proyecto DESC"
);

if (!$result) {
    echo json_encode(['error' => true, 'mensaje' => 'Error al consultar']);
    $conn->close();
    exit;
}

$proyectos = [];
while ($row = $result->fetch_assoc()) {
    $proyectos[] = $row;
}

$conn->close();
echo json_encode(['error' => false, 'proyectos' => $proyectos]);
?>
