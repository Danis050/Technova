<?php
// get_usuarios_activos.php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}

$conn = getConexion();

$stmt = $conn->prepare("
    SELECT id_usuario, nombre, apellido, email, rol, puesto
    FROM usuario
    WHERE estado = 1
      AND rol IN ('Empleado')
    ORDER BY nombre ASC, apellido ASC
");
$stmt->execute();
$result = $stmt->get_result();

$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = [
        'id_usuario' => (int)$row['id_usuario'],
        'nombre'     => $row['nombre'] . ' ' . $row['apellido'],
        'email'      => $row['email'],
        'rol'        => $row['rol'],
        'puesto'     => $row['puesto'] ?? ''
    ];
}

$stmt->close();
$conn->close();

echo json_encode($usuarios, JSON_UNESCAPED_UNICODE);
?>
