<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$conn = getConexion();
$idUsuario = (int) $_SESSION['id_usuario'];

$stmt = $conn->prepare(
    "SELECT nombre, apellido, email, rol, puesto FROM usuario WHERE id_usuario = ? AND estado = 1 LIMIT 1"
);
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$usuario) {
    echo json_encode(['error' => true, 'mensaje' => 'Usuario no encontrado']);
    exit;
}

echo json_encode([
    'error'    => false,
    'nombre'   => $usuario['nombre'],
    'apellido' => $usuario['apellido'],
    'email'    => $usuario['email'],
    'rol'      => $usuario['rol'],
    'puesto'   => $usuario['puesto'] ?? ''
], JSON_UNESCAPED_UNICODE);
?>
