<?php
// ============================================================
// CREAR USUARIO - Solo Administradores | TechNova
// ============================================================
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

// 1. Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado. Inicia sesión.']);
    exit;
}

// 2. Verificar que sea Administrador
if ($_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['error' => true, 'mensaje' => 'Acceso denegado. Solo administradores pueden crear usuarios.']);
    exit;
}

// 3. Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => true, 'mensaje' => 'Método no permitido.']);
    exit;
}

// 4. Recoger y limpiar campos
$nombre   = trim($_POST['nombre']   ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$confirmar= trim($_POST['confirmar'] ?? '');
$rol      = trim($_POST['rol']      ?? '');
$puesto   = trim($_POST['puesto']   ?? '');

// 5. Validaciones
if (empty($nombre) || empty($apellido) || empty($email) || empty($password) || empty($confirmar)) {
    echo json_encode(['error' => true, 'mensaje' => 'Todos los campos son requeridos.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => true, 'mensaje' => 'El correo electrónico no es válido.']);
    exit;
}

if ($password !== $confirmar) {
    echo json_encode(['error' => true, 'mensaje' => 'Las contraseñas no coinciden.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['error' => true, 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.']);
    exit;
}

// Roles permitidos
$rolesPermitidos = ['Administrador', 'Empleado', 'Cliente'];
if (!in_array($rol, $rolesPermitidos)) {
    echo json_encode(['error' => true, 'mensaje' => 'Rol no válido.']);
    exit;
}

// Si es empleado, validar puesto
$puestosPermitidos = ['Desarrollador', 'Diseñador', 'Gerente de Proyectos', 'Soporte Técnico', 'Ventas', 'Contador'];
if ($rol === 'Empleado' && !in_array($puesto, $puestosPermitidos)) {
    echo json_encode(['error' => true, 'mensaje' => 'Selecciona un puesto válido para el empleado.']);
    exit;
}

$conn = getConexion();

// 6. Verificar email duplicado
$check = $conn->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['error' => true, 'mensaje' => 'Este correo ya está registrado en el sistema.']);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// 7. Hashear contraseña
$hash = password_hash($password, PASSWORD_BCRYPT);

// 8. Insertar usuario
// Columna 'puesto' solo se guarda si es Empleado, si no queda vacío
$puestoFinal = ($rol === 'Empleado') ? $puesto : '';

$stmt = $conn->prepare("INSERT INTO usuario (nombre, apellido, email, password_hash, rol, puesto, estado) VALUES (?, ?, ?, ?, ?, ?, 1)");
$stmt->bind_param("ssssss", $nombre, $apellido, $email, $hash, $rol, $puestoFinal);

if (!$stmt->execute()) {
    echo json_encode(['error' => true, 'mensaje' => 'Error al crear el usuario: ' . $conn->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$id_nuevo = $conn->insert_id;
$stmt->close();

// 9. Si es Cliente, crear registro en tabla cliente automáticamente
if ($rol === 'Cliente') {
    $empresa = $nombre . ' ' . $apellido;
    $stmt2 = $conn->prepare("INSERT INTO cliente (id_usuario, nombre_empresa) VALUES (?, ?)");
    $stmt2->bind_param("is", $id_nuevo, $empresa);
    $stmt2->execute();
    $stmt2->close();
}

$conn->close();

echo json_encode([
    'error'   => false,
    'mensaje' => 'Usuario creado exitosamente.',
    'rol'     => $rol,
    'puesto'  => $puestoFinal
]);
?>
