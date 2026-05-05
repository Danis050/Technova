<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require 'conexion.php';

$conn = getConexion();

$sql = "SELECT 
    p.id_proyecto,
    p.nombre,
    p.estado,
    p.fecha_inicio,
    p.fecha_entrega,
    p.monto,
    c.id_cliente AS id_cliente,
    c.nombre_empresa
FROM proyecto p
INNER JOIN cliente c ON p.id_cliente = c.id_cliente";

$result = $conn->query($sql);

$proyectos = [];

while ($row = $result->fetch_assoc()) {
    $proyectos[] = [
        "id" => (int)$row["id_proyecto"],
        "nombre" => $row["nombre"],
        "estado" => $row["estado"],
        "fechaInicio" => $row["fecha_inicio"],
        "fechaFin" => $row["fecha_entrega"],
        "idCliente" => $row["id_cliente"],
        "nombreCliente" => $row["nombre_empresa"],
        "monto" => $row["monto"]
    ];
}

echo json_encode($proyectos);