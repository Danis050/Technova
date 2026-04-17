<?php
header('Content-Type: application/json');
require 'conexion.php';

$conn = getConexion();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "No se recibieron datos"]);
    exit;
}

$nombre      = $data['nombre'] ?? '';
$idCliente   = $data['idCliente'] ?? null;
$estado      = $data['estado'] ?? '';
$fechaInicio = $data['fechaInicio'] ?? '';
$fechaFin    = $data['fechaFin'] ?? '';

if (!$nombre || !$idCliente) {
    echo json_encode(["success" => false, "error" => "Datos incompletos"]);
    exit;
}

$sql = "INSERT INTO proyecto
        (nombre, estado, fecha_inicio, fecha_entrega, id_cliente)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param("ssssi", 
    $nombre, 
    $estado, 
    $fechaInicio, 
    $fechaFin, 
    $idCliente
);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}