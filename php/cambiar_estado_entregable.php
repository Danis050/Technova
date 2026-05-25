<?php
// ============================================================
// CAMBIAR ESTADO DE ENTREGABLE — HU-19 | TechNova
// Desarrollador: David Alexander Urias Blanco (U20240435)
// Historia:  Como miembro del equipo, quiero cambiar el estado
//            de un entregable para reflejar el avance real
//            del trabajo en el proyecto.
// Criterio:  Flujo válido: Pendiente → En desarrollo → Entregado.
//            Solo se puede operar sobre entregables de proyectos
//            activos. El cliente ve los cambios en modo solo lectura.
// ============================================================
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

// ── Autorización: Admin, Diseñador o Desarrollador ───────────
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    echo json_encode([
        'error'   => true,
        'mensaje' => 'No autorizado. Debes iniciar sesión.'
    ]);
    exit;
}

$roles_permitidos = ['Administrador', 'Diseñador', 'Desarrollador'];
if (!in_array($_SESSION['rol'], $roles_permitidos)) {
    http_response_code(403);
    echo json_encode([
        'error'   => true,
        'mensaje' => 'Sin permisos para cambiar el estado de entregables.'
    ]);
    exit;
}

// ── Leer cuerpo JSON ─────────────────────────────────────────
$data           = json_decode(file_get_contents('php://input'), true);
$id_entregable  = isset($data['id_entregable'])  ? (int)$data['id_entregable']    : 0;
$nuevo_estado   = isset($data['nuevo_estado'])   ? trim($data['nuevo_estado'])     : '';

// ── Validar campos obligatorios ──────────────────────────────
if ($id_entregable <= 0 || $nuevo_estado === '') {
    http_response_code(400);
    echo json_encode([
        'error'   => true,
        'mensaje' => 'id_entregable y nuevo_estado son obligatorios.'
    ]);
    exit;
}

// ── Estados válidos del flujo definido en HU-19 ─────────────
$estados_validos = ['Pendiente', 'En desarrollo', 'Entregado'];

if (!in_array($nuevo_estado, $estados_validos)) {
    http_response_code(422);
    echo json_encode([
        'error'   => true,
        'mensaje' => 'Estado no válido. Los estados permitidos son: '
                   . implode(', ', $estados_validos) . '.'
    ]);
    exit;
}

// ── Mapa del flujo de transiciones permitidas ────────────────
// Cada estado solo puede avanzar en una dirección (no retrocede).
$transiciones_permitidas = [
    'Pendiente'     => ['En desarrollo'],
    'En desarrollo' => ['Entregado'],
    'Entregado'     => []   // estado final, sin avance posible
];

try {
    $conn = getConexion();

    // ── 1. Obtener el entregable y validar que existe ────────
    $stmt_ent = $conn->prepare(
        "SELECT e.id_entregable, e.estado AS estado_actual, e.id_proyecto,
                p.estado AS estado_proyecto
         FROM entregable e
         INNER JOIN proyecto p ON p.id_proyecto = e.id_proyecto
         WHERE e.id_entregable = ?
         LIMIT 1"
    );
    $stmt_ent->bind_param('i', $id_entregable);
    $stmt_ent->execute();
    $res_ent = $stmt_ent->get_result();

    if ($res_ent->num_rows === 0) {
        $stmt_ent->close();
        http_response_code(404);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'El entregable no existe.'
        ]);
        exit;
    }

    $entregable = $res_ent->fetch_assoc();
    $stmt_ent->close();

    $estado_actual    = $entregable['estado_actual'];
    $estado_proyecto  = $entregable['estado_proyecto'];
    $id_proyecto      = (int)$entregable['id_proyecto'];

    // ── 2. Verificar que el proyecto está Activo ─────────────
    if ($estado_proyecto !== 'Activo') {
        http_response_code(409);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'Solo se pueden gestionar entregables de proyectos Activos. '
                       . 'Estado del proyecto: ' . $estado_proyecto . '.'
        ]);
        exit;
    }

    // ── 3. Validar que la transición sea permitida ───────────
    $destinos_posibles = $transiciones_permitidas[$estado_actual] ?? [];

    if (!in_array($nuevo_estado, $destinos_posibles)) {
        // Construir mensaje descriptivo del flujo
        if (empty($destinos_posibles)) {
            $detalle = "El entregable ya está en estado final ('{$estado_actual}') y no puede avanzar.";
        } else {
            $detalle = "Desde '{$estado_actual}' solo se puede pasar a: "
                     . implode(', ', $destinos_posibles)
                     . ". No se puede ir a '{$nuevo_estado}'.";
        }

        http_response_code(422);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'Transición de estado no permitida. ' . $detalle
        ]);
        exit;
    }

    // ── 4. Verificar permisos de miembro sobre este proyecto ─
    //    Admin ve todo; Diseñador/Desarrollador solo sus proyectos.
    if ($_SESSION['rol'] !== 'Administrador') {
        $id_sesion = (int)$_SESSION['id_usuario'];
        $stmt_perm = $conn->prepare(
            "SELECT id FROM proyecto_usuario
             WHERE id_proyecto = ? AND id_usuario = ? LIMIT 1"
        );
        $stmt_perm->bind_param('ii', $id_proyecto, $id_sesion);
        $stmt_perm->execute();
        $stmt_perm->store_result();

        if ($stmt_perm->num_rows === 0) {
            $stmt_perm->close();
            http_response_code(403);
            echo json_encode([
                'error'   => true,
                'mensaje' => 'No tienes asignación en este proyecto. Solo puedes gestionar entregables de tus propios proyectos.'
            ]);
            exit;
        }
        $stmt_perm->close();
    }

    // ── 5. Aplicar el cambio de estado ───────────────────────
    $id_usuario_sesion = (int)$_SESSION['id_usuario'];
    $stmt_upd = $conn->prepare(
        "UPDATE entregable
         SET estado            = ?,
             fecha_actualizacion = NOW(),
             id_usuario_ultima_accion = ?
         WHERE id_entregable = ?"
    );
    $stmt_upd->bind_param('sii', $nuevo_estado, $id_usuario_sesion, $id_entregable);

    if (!$stmt_upd->execute()) {
        throw new Exception('Error al actualizar el estado: ' . $stmt_upd->error);
    }
    $stmt_upd->close();

    // ── 6. Registrar en historial de cambios de entregable ───
    $stmt_hist = $conn->prepare(
        "INSERT INTO historial_entregable
             (id_entregable, id_proyecto, id_usuario, estado_anterior, estado_nuevo, fecha_cambio)
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $stmt_hist->bind_param(
        'iiiss',
        $id_entregable,
        $id_proyecto,
        $id_usuario_sesion,
        $estado_actual,
        $nuevo_estado
    );
    $stmt_hist->execute(); // No fatal; el negocio ya se completó
    $stmt_hist->close();

    $conn->close();

    // ── Respuesta de éxito ───────────────────────────────────
    echo json_encode([
        'error'          => false,
        'mensaje'        => "Estado del entregable actualizado correctamente: '{$estado_actual}' → '{$nuevo_estado}'.",
        'id_entregable'  => $id_entregable,
        'estado_anterior' => $estado_actual,
        'estado_nuevo'   => $nuevo_estado
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error'   => true,
        'mensaje' => $e->getMessage()
    ]);
}
?>
