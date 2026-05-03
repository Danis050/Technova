<?php
header('Content-Type: application/json');
require 'conexion.php';

$conn = getConexion();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "No se recibieron datos"]);
    exit;
}

$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "error" => "ID inválido"]);
    exit;
}

$sql = "DELETE FROM proyecto WHERE id_proyecto = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}