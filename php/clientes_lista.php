<?php
header('Content-Type: application/json');
require 'conexion.php';

$conn = getConexion();

$sql = "SELECT id_cliente AS id, nombre_empresa FROM cliente";

$result = $conn->query($sql);

$clientes = [];

while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}

echo json_encode($clientes);