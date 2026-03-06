<?php
// ============================================================
// LOGIN - TechNova
// ============================================================
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => true, 'mensaje' => 'Método no permitido']);
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(['error' => true, 'mensaje' => 'Email y contraseña son requeridos']);
    exit;
}

$conn = getConexion();

$stmt = $conn->prepare("SELECT id_usuario, nombre, apellido, email, password_hash, rol, estado FROM usuario WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => true, 'mensaje' => 'Credenciales incorrectas']);
    exit;
}

$usuario = $result->fetch_assoc();

if (!$usuario['estado']) {
    echo json_encode(['error' => true, 'mensaje' => 'Usuario inactivo. Contacte al administrador']);
    exit;
}

if (!password_verify($password, $usuario['password_hash'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Credenciales incorrectas']);
    exit;
}

// Guardar sesión
$_SESSION['id_usuario'] = $usuario['id_usuario'];
$_SESSION['nombre']     = $usuario['nombre'] . ' ' . $usuario['apellido'];
$_SESSION['email']      = $usuario['email'];
$_SESSION['rol']        = $usuario['rol'];

$stmt->close();
$conn->close();

echo json_encode([
    'error'   => false,
    'mensaje' => 'Login exitoso',
    'rol'     => $usuario['rol'],
    'nombre'  => $usuario['nombre']
]);
?>
