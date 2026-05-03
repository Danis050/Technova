<?php
header('Content-Type: application/json');
require 'conexion.php';

$conn = getConexion();


$data = json_decode(file_get_contents("php://input"), true);

$id           = $data['id'];
$nombre       = $data['nombre'];
$idCliente    = $data['idCliente'];
$estado       = $data['estado'];
$fechaInicio  = $data['fechaInicio'];
$fechaFin     = $data['fechaFin'];

$sql = "UPDATE proyecto SET 
            nombre = ?, 
            id_cliente = ?, 
            estado = ?, 
            fecha_inicio = ?, 
            fecha_entrega = ?
        WHERE id_proyecto = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sisssi", $nombre, $idCliente, $estado, $fechaInicio, $fechaFin, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}