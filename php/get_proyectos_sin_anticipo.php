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
            COALESCE(SUM(pg.monto), 0) AS total_pagado
     FROM proyecto p
     JOIN cliente c ON p.id_cliente = c.id_cliente
     LEFT JOIN pago pg ON pg.id_proyecto = p.id_proyecto
     WHERE p.anticipo_pagado = 0
       AND p.estado NOT IN ('Completado', 'Cancelado')
     GROUP BY p.id_proyecto
     ORDER BY p.creado_en DESC"
);

$proyectos = [];
while ($row = $r->fetch_assoc()) {
    $proyectos[] = $row;
}

$conn->close();
echo json_encode(['error' => false, 'proyectos' => $proyectos]);
?>