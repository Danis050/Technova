<?php
// ============================================================
// REGISTRO DE USUARIO - TechNova
// ============================================================
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => true, 'mensaje' => 'Método no permitido']);
    exit;
}

$nombre   = trim($_POST['nombre']   ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$confirmar= trim($_POST['confirmar'] ?? '');

// Validaciones
if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
    echo json_encode(['error' => true, 'mensaje' => 'Todos los campos son requeridos']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => true, 'mensaje' => 'Email no válido']);
    exit;
}

if ($password !== $confirmar) {
    echo json_encode(['error' => true, 'mensaje' => 'Las contraseñas no coinciden']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['error' => true, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres']);
    exit;
}

$conn = getConexion();

// Verificar si el email ya existe
$check = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(['error' => true, 'mensaje' => 'El email ya está registrado']);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// Encriptar contraseña
$hash = password_hash($password, PASSWORD_BCRYPT);

// Insertar usuario con rol Cliente por defecto
$stmt = $conn->prepare("INSERT INTO usuario (nombre, apellido, email, password_hash, rol) VALUES (?, ?, ?, ?, 'Cliente')");
$stmt->bind_param("ssss", $nombre, $apellido, $email, $hash);

if ($stmt->execute()) {
    $id_nuevo = $conn->insert_id;

    // Crear registro en tabla cliente automáticamente
    $empresa = $nombre . ' ' . $apellido;
    $stmt2 = $conn->prepare("INSERT INTO cliente (id_usuario, nombre_empresa) VALUES (?, ?)");
    $stmt2->bind_param("is", $id_nuevo, $empresa);
    $stmt2->execute();
    $stmt2->close();

    echo json_encode(['error' => false, 'mensaje' => 'Cuenta creada exitosamente']);
} else {
    echo json_encode(['error' => true, 'mensaje' => 'Error al crear la cuenta: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
