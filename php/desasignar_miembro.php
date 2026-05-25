<?php
// ============================================================
// DESASIGNAR MIEMBRO DE PROYECTO ACTIVO — HU-18 | TechNova
// Desarrollador: David Alexander Urias Blanco (U20240435)
// Historia:  Como Administrador, quiero desasignar miembros
//            de un proyecto activo para mantener actualizado
//            el equipo de trabajo.
// Criterio:  Solo se puede desasignar si el proyecto está Activo.
//            El miembro desasignado deja de ver el proyecto en
//            su panel personal.
// ============================================================
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

// ── Autorización: solo el Administrador puede desasignar ─────
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    http_response_code(403);
    echo json_encode([
        'error'   => true,
        'mensaje' => 'No autorizado. Solo el Administrador puede desasignar miembros.'
    ]);
    exit;
}

// ── Leer cuerpo JSON ─────────────────────────────────────────
$data        = json_decode(file_get_contents('php://input'), true);
$id_proyecto = isset($data['id_proyecto']) ? (int)$data['id_proyecto'] : 0;
$id_usuario  = isset($data['id_usuario'])  ? (int)$data['id_usuario']  : 0;

// ── Validar campos obligatorios ──────────────────────────────
if ($id_proyecto <= 0 || $id_usuario <= 0) {
    http_response_code(400);
    echo json_encode([
        'error'   => true,
        'mensaje' => 'id_proyecto e id_usuario son obligatorios y deben ser valores válidos.'
    ]);
    exit;
}

try {
    $conn = getConexion();

    // ── 1. Verificar que el proyecto existe y está Activo ────
    $stmt_proy = $conn->prepare(
        "SELECT estado FROM proyecto WHERE id_proyecto = ? LIMIT 1"
    );
    $stmt_proy->bind_param('i', $id_proyecto);
    $stmt_proy->execute();
    $res_proy = $stmt_proy->get_result();

    if ($res_proy->num_rows === 0) {
        throw new Exception('El proyecto especificado no existe.');
    }

    $proyecto = $res_proy->fetch_assoc();
    $stmt_proy->close();

    if ($proyecto['estado'] !== 'Activo') {
        http_response_code(409);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'Solo se puede desasignar miembros de proyectos en estado Activo. '
                       . 'Estado actual: ' . $proyecto['estado'] . '.'
        ]);
        exit;
    }

    // ── 2. Verificar que el miembro está asignado al proyecto ─
    $stmt_check = $conn->prepare(
        "SELECT id FROM proyecto_usuario
         WHERE id_proyecto = ? AND id_usuario = ? LIMIT 1"
    );
    $stmt_check->bind_param('ii', $id_proyecto, $id_usuario);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows === 0) {
        $stmt_check->close();
        http_response_code(404);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'El miembro no está asignado a este proyecto.'
        ]);
        exit;
    }
    $stmt_check->close();

    // ── 3. Verificar que el usuario tiene rol permitido ──────
    //    Solo Diseñador o Desarrollador pueden ser desasignados.
    $stmt_rol = $conn->prepare(
        "SELECT rol FROM usuario WHERE id_usuario = ? LIMIT 1"
    );
    $stmt_rol->bind_param('i', $id_usuario);
    $stmt_rol->execute();
    $res_rol = $stmt_rol->get_result();

    if ($res_rol->num_rows === 0) {
        throw new Exception('El usuario especificado no existe.');
    }

    $usuario = $res_rol->fetch_assoc();
    $stmt_rol->close();

    $roles_permitidos = ['Diseñador', 'Desarrollador'];
    if (!in_array($usuario['rol'], $roles_permitidos)) {
        http_response_code(422);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'Solo se pueden desasignar usuarios con rol Diseñador o Desarrollador. '
                       . 'Rol actual: ' . $usuario['rol'] . '.'
        ]);
        exit;
    }

    // ── 4. Ejecutar desasignación ────────────────────────────
    $stmt_del = $conn->prepare(
        "DELETE FROM proyecto_usuario
         WHERE id_proyecto = ? AND id_usuario = ?"
    );
    $stmt_del->bind_param('ii', $id_proyecto, $id_usuario);

    if (!$stmt_del->execute()) {
        throw new Exception('Error al desasignar el miembro: ' . $stmt_del->error);
    }

    $filas_afectadas = $stmt_del->affected_rows;
    $stmt_del->close();

    // ── 5. Registrar en historial de actividad (auditoría) ───
    $id_admin = (int)$_SESSION['id_usuario'];
    $stmt_log = $conn->prepare(
        "INSERT INTO historial_actividad
             (id_proyecto, id_usuario_accion, tipo_accion, descripcion, fecha_accion)
         VALUES (?, ?, 'DESASIGNACION', ?, NOW())"
    );
    $descripcion = "Miembro (ID: {$id_usuario}) desasignado del proyecto por el Administrador (ID: {$id_admin}).";
    $stmt_log->bind_param('iis', $id_proyecto, $id_admin, $descripcion);
    $stmt_log->execute(); // No fatal si falla el log
    $stmt_log->close();

    $conn->close();

    echo json_encode([
        'error'          => false,
        'mensaje'        => 'Miembro desasignado correctamente. Ya no tiene acceso al proyecto.',
        'filas_afectadas' => $filas_afectadas
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => true,
        'mensaje' => $e->getMessage()
    ]);
}
?>
