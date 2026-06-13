<?php
// HU-21 | SCRUM-HU21-02 | Danis Vides
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';

function responderPerfil(bool $error, string $mensaje, array $extra = []): void
{
    echo json_encode(array_merge([
        'error' => $error,
        'success' => !$error,
        'mensaje' => $mensaje
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    responderPerfil(true, 'No hay sesion activa. Inicie sesion.');
}

$idUsuario = (int) $_SESSION['id_usuario'];
$conn = getConexion();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $conn->prepare(
            "SELECT id_usuario, nombre, apellido, email, rol, puesto, estado
             FROM usuario
             WHERE id_usuario = ? AND estado = 1
             LIMIT 1"
        );
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$usuario) {
            responderPerfil(true, 'Usuario no encontrado o inactivo.');
        }

        responderPerfil(false, 'Perfil cargado correctamente.', [
            'usuario' => [
                'id_usuario' => (int) $usuario['id_usuario'],
                'nombre' => $usuario['nombre'],
                'apellido' => $usuario['apellido'],
                'nombre_completo' => trim($usuario['nombre'] . ' ' . $usuario['apellido']),
                'email' => $usuario['email'],
                'rol' => $usuario['rol'],
                'puesto' => $usuario['puesto'] ?? ''
            ]
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responderPerfil(true, 'Metodo no permitido. Use GET o POST.');
    }

    $accion = trim($_POST['accion'] ?? '');

    if ($accion === 'actualizar_nombre') {
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '') {
            responderPerfil(true, 'El nombre no puede estar vacio.');
        }

        if (strlen($nombre) > 100) {
            responderPerfil(true, 'El nombre no puede superar 100 caracteres.');
        }

        $stmt = $conn->prepare("UPDATE usuario SET nombre = ? WHERE id_usuario = ?");
        $stmt->bind_param('si', $nombre, $idUsuario);
        $stmt->execute();
        $stmt->close();

        $stmtUsuario = $conn->prepare("SELECT nombre, apellido FROM usuario WHERE id_usuario = ? LIMIT 1");
        $stmtUsuario->bind_param('i', $idUsuario);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->get_result()->fetch_assoc();
        $stmtUsuario->close();

        $_SESSION['nombre'] = trim($usuario['nombre'] . ' ' . $usuario['apellido']);

        responderPerfil(false, 'Nombre actualizado correctamente.', [
            'nombre' => $usuario['nombre'],
            'nombre_completo' => $_SESSION['nombre']
        ]);
    }

    if ($accion === 'cambiar_contrasena') {
        $actual = $_POST['contrasena_actual'] ?? ($_POST['password_actual'] ?? '');
        $nueva = $_POST['nueva_contrasena'] ?? ($_POST['password_nueva'] ?? '');
        $confirmar = $_POST['confirmar_contrasena'] ?? ($_POST['password_confirmar'] ?? '');

        if ($actual === '' || $nueva === '' || $confirmar === '') {
            responderPerfil(true, 'Todos los campos de contrasena son obligatorios.');
        }

        if ($nueva !== $confirmar) {
            responderPerfil(true, 'La nueva contrasena y la confirmacion no coinciden.');
        }

        if (strlen($nueva) < 8 || !preg_match('/[A-Z]/', $nueva) || !preg_match('/[0-9]/', $nueva)) {
            responderPerfil(true, 'La nueva contrasena debe tener minimo 8 caracteres, una mayuscula y un numero.');
        }

        $stmt = $conn->prepare("SELECT password_hash FROM usuario WHERE id_usuario = ? AND estado = 1 LIMIT 1");
        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$usuario) {
            responderPerfil(true, 'Usuario no encontrado o inactivo.');
        }

        if (!password_verify($actual, $usuario['password_hash'])) {
            responderPerfil(true, 'La contrasena actual es incorrecta.');
        }

        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $stmtUpdate = $conn->prepare("UPDATE usuario SET password_hash = ? WHERE id_usuario = ?");
        $stmtUpdate->bind_param('si', $hash, $idUsuario);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        responderPerfil(false, 'Contrasena actualizada correctamente.');
    }

    responderPerfil(true, 'Accion no valida.');
} finally {
    $conn->close();
}
?>
