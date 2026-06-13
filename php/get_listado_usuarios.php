<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

if ($_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['error' => true, 'mensaje' => 'Acceso denegado']);
    exit;
}

$conn = getConexion();

$stmt = $conn->prepare(
    "SELECT id_usuario, nombre, apellido, email, rol, puesto, estado,
            DATE_FORMAT(creado_en, '%Y-%m-%d') AS creado_en
     FROM usuario
     ORDER BY creado_en DESC, id_usuario DESC"
);
$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = [
        'id_usuario' => (int)$row['id_usuario'],
        'nombre'     => $row['nombre'],
        'apellido'   => $row['apellido'],
        'email'      => $row['email'],
        'rol'        => $row['rol'],
        'puesto'     => $row['puesto'] ?? '',
        'estado'     => (int)$row['estado'],
        'creado_en'  => $row['creado_en']
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    'error'    => false,
    'usuario'  => $_SESSION['nombre'],
    'rol'      => $_SESSION['rol'],
    'usuarios' => $usuarios
], JSON_UNESCAPED_UNICODE);
?>
