<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}

$conn = getConexion();

$r = $conn->query(
    "SELECT p.id_proyecto, p.nombre, p.estado, p.fecha_entrega,
            c.nombre_empresa,
            IF(EXISTS(
                SELECT 1 FROM pago pg
                WHERE pg.id_proyecto = p.id_proyecto
                AND pg.tipo_pago = 'Saldo Final'
            ), 1, 0) AS saldado
     FROM proyecto p
     JOIN cliente c ON p.id_cliente = c.id_cliente
     WHERE p.estado NOT IN ('Cancelado')
     ORDER BY saldado ASC, p.creado_en DESC"
);

$proyectos = [];
while ($row = $r->fetch_assoc()) {
    $proyectos[] = $row;
}

$conn->close();
echo json_encode(['error' => false, 'proyectos' => $proyectos]);
