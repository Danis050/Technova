<?php
// Eliminar entregable
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

function responder($error, $mensaje) {
    echo json_encode(['error' => $error, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    responder(true, 'No autorizado.');
}
if (!in_array($_SESSION['rol'], ['Administrador', 'Empleado'], true)) {
    responder(true, 'Sin permisos para eliminar entregables.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(true, 'Método no permitido.');
}

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id_entregable'] ?? 0);

if ($id <= 0) {
    responder(true, 'ID de entregable inválido.');
}

$conn = getConexion();

$stmt = $conn->prepare("DELETE FROM entregable WHERE id_entregable = ?");
$stmt->bind_param('i', $id);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

if ($ok) {
    responder(false, 'Entregable eliminado correctamente.');
} else {
    responder(true, 'Error al eliminar el entregable.');
}