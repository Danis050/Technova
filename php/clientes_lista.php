<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$conn = getConexion();

$result = $conn->query("SELECT id, nombre_empresa FROM clientes ORDER BY nombre_empresa ASC");

$clientes = [];
while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}

$conn->close();
echo json_encode($clientes);
