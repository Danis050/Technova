<?php
// HU-23 | Generacion y envio de enlace de recuperacion con expiracion
// Dev: Danis I. Vides Aparicio

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => true, 'mensaje' => 'Metodo no permitido']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => true, 'mensaje' => 'Ingresa un correo valido.']);
    exit;
}

$conn = getConexion();

$stmt = $conn->prepare("SELECT id_usuario, nombre FROM usuario WHERE email = ? AND estado = 1 LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();
$stmt->close();

$respuesta = [
    'error' => false,
    'mensaje' => 'Si el correo esta registrado, recibiras un enlace para recuperar tu acceso.'
];

if (!$usuario) {
    echo json_encode($respuesta);
    $conn->close();
    exit;
}

$token = bin2hex(random_bytes(32));
$token_hash = hash('sha256', $token);
$id_usuario = (int) $usuario['id_usuario'];

$conn->begin_transaction();

try {
    $stmtExpirar = $conn->prepare(
        "UPDATE password_reset_token
         SET usado_en = NOW()
         WHERE id_usuario = ? AND usado_en IS NULL"
    );
    $stmtExpirar->bind_param("i", $id_usuario);
    $stmtExpirar->execute();
    $stmtExpirar->close();

    $stmtToken = $conn->prepare(
        "INSERT INTO password_reset_token (id_usuario, token_hash, expira_en, creado_en)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE), NOW())"
    );
    $stmtToken->bind_param("is", $id_usuario, $token_hash);
    $stmtToken->execute();
    $stmtToken->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => true, 'mensaje' => 'No se pudo generar el enlace de recuperacion.']);
    $conn->close();
    exit;
}

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
$enlace = $baseUrl . '/restablecer_password.html?token=' . urlencode($token);

$asunto = 'Recuperacion de contrasena - TechNova';
$mensaje = "Hola {$usuario['nombre']},\n\nUsa este enlace para recuperar tu contrasena. Expira en 30 minutos:\n$enlace\n\nSi no solicitaste este cambio, ignora este correo.";
$headers = 'From: no-reply@technova.local' . "\r\n";

$enviado = @mail($email, $asunto, $mensaje, $headers);

if (!$enviado) {
    $respuesta['modoPrueba'] = true;
    $respuesta['enlacePrueba'] = $enlace;
}

echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
$conn->close();
?>
