<?php
// HU-17 | SCRUM-HU17-02 y SCRUM-HU17-04 | Misael Juarez
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexion.php';

$auditoriaPaths = [
    __DIR__ . '/auditoria_helper.php',
    __DIR__ . '/includes/auditoria_helper.php'
];

foreach ($auditoriaPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

function responderGestionRoles(bool $error, string $mensaje, array $extra = []): void
{
    echo json_encode(array_merge([
        'error' => $error,
        'success' => !$error,
        'mensaje' => $mensaje
    ], $extra));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderGestionRoles(true, 'Metodo no permitido. Use POST.');
}

if (!isset($_SESSION['id_usuario'])) {
    responderGestionRoles(true, 'No hay sesion activa. Inicie sesion.');
}

if (($_SESSION['rol'] ?? '') !== 'Administrador') {
    responderGestionRoles(true, 'Acceso denegado. Se requiere rol Administrador.');
}

$idUsuario = isset($_POST['id_usuario']) ? (int) $_POST['id_usuario'] : 0;
$nuevoRol = trim($_POST['nuevo_rol'] ?? '');

$rolesValidos = ['Administrador', 'Disenador', 'Diseñador', 'Desarrollador', 'Cliente'];

if ($idUsuario <= 0) {
    responderGestionRoles(true, 'El campo id_usuario es obligatorio y debe ser un entero positivo.');
}

if (!in_array($nuevoRol, $rolesValidos, true)) {
    responderGestionRoles(true, 'Rol invalido. Valores permitidos: Administrador, Diseñador, Desarrollador, Cliente.');
}

$rolBase = $nuevoRol;
$puesto = '';

if ($nuevoRol === 'Disenador' || $nuevoRol === 'Diseñador') {
    $rolBase = 'Empleado';
    $puesto = 'Diseñador';
} elseif ($nuevoRol === 'Desarrollador') {
    $rolBase = 'Empleado';
    $puesto = 'Desarrollador';
}

$conn = getConexion();
$conn->begin_transaction();

try {
    $stmtUsuario = $conn->prepare(
        "SELECT id_usuario, nombre, apellido, rol, puesto, estado
           FROM usuario
          WHERE id_usuario = ?
          LIMIT 1"
    );
    $stmtUsuario->bind_param('i', $idUsuario);
    $stmtUsuario->execute();
    $resultUsuario = $stmtUsuario->get_result();

    if ($resultUsuario->num_rows === 0) {
        throw new Exception('Usuario no encontrado.');
    }

    $usuarioActual = $resultUsuario->fetch_assoc();
    $stmtUsuario->close();

    if ((int) $usuarioActual['estado'] !== 1) {
        throw new Exception('No se puede cambiar el rol de un usuario inactivo.');
    }

    // SCRUM-HU17-03 | Danis Vides: protege contra degradar al ultimo admin activo.
    if ($usuarioActual['rol'] === 'Administrador' && $rolBase !== 'Administrador') {
        $stmtAdmins = $conn->prepare(
            "SELECT COUNT(*) AS total
               FROM usuario
              WHERE rol = 'Administrador'
                AND estado = 1
                AND id_usuario != ?"
        );
        $stmtAdmins->bind_param('i', $idUsuario);
        $stmtAdmins->execute();
        $totalAdmins = (int) $stmtAdmins->get_result()->fetch_assoc()['total'];
        $stmtAdmins->close();

        if ($totalAdmins === 0) {
            throw new Exception('No se puede degradar al ultimo Administrador activo.');
        }
    }

    $stmtUpdate = $conn->prepare(
        "UPDATE usuario
            SET rol = ?, puesto = ?
          WHERE id_usuario = ?"
    );
    $stmtUpdate->bind_param('ssi', $rolBase, $puesto, $idUsuario);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    if (function_exists('registrarAuditoria')) {
        registrarAuditoria('cambio_rol', 'usuario', $idUsuario, 'Rol cambiado a: ' . $nuevoRol);
    }

    $conn->commit();

    responderGestionRoles(false, 'Rol actualizado correctamente.', [
        'id_usuario' => $idUsuario,
        'rol' => $rolBase,
        'puesto' => $puesto,
        'rol_visible' => $nuevoRol
    ]);
} catch (Exception $e) {
    $conn->rollback();
    responderGestionRoles(true, $e->getMessage());
} finally {
    $conn->close();
}
?>
