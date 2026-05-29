<?php
// HU-23 | Misael A. Juarez Reyes
// Validar token de recuperacion y guardar nueva contrasena encriptada.

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

function responder($error, $mensaje) {
    echo json_encode(['error' => $error, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit;
}

function password_segura($password) {
    return strlen($password) >= 8
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(true, 'Metodo no permitido.');
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    $data = $_POST;
}

$token = trim($data['token'] ?? '');
$password = trim($data['password'] ?? $data['nueva_password'] ?? '');
$confirmar = trim($data['confirmar'] ?? $data['confirmar_password'] ?? '');

if ($token === '' || $password === '' || $confirmar === '') {
    responder(true, 'Token, nueva contrasena y confirmacion son obligatorios.');
}
if ($password !== $confirmar) {
    responder(true, 'Las contrasenas no coinciden.');
}
if (!password_segura($password)) {
    responder(true, 'La contrasena debe tener minimo 8 caracteres, una mayuscula, una minuscula y un numero.');
}

$token_hash = hash('sha256', $token);
$conn = getConexion();
$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        "SELECT id_token, id_usuario
         FROM password_reset_token
         WHERE token_hash = ? AND usado_en IS NULL AND expira_en >= NOW()
         LIMIT 1"
    );
    $stmt->bind_param('s', $token_hash);
    $stmt->execute();
    $registro = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$registro) {
        $conn->rollback();
        responder(true, 'El enlace de recuperacion es invalido, ya fue usado o expiro.');
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmtPass = $conn->prepare("UPDATE usuario SET password_hash = ? WHERE id_usuario = ?");
    $stmtPass->bind_param('si', $hash, $registro['id_usuario']);
    $stmtPass->execute();
    $stmtPass->close();

    $stmtToken = $conn->prepare("UPDATE password_reset_token SET usado_en = NOW() WHERE id_token = ?");
    $stmtToken->bind_param('i', $registro['id_token']);
    $stmtToken->execute();
    $stmtToken->close();

    $conn->commit();
    responder(false, 'Contrasena actualizada correctamente. Ya puedes iniciar sesion.');
} catch (Exception $e) {
    $conn->rollback();
    responder(true, 'Error al restablecer la contrasena.');
}
?>
