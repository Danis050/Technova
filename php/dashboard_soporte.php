<?php
// ============================================================
// DASHBOARD DE SOPORTE POST-ENTREGA — HU-29 | TechNova
// Desarrollador: David Alexander Urias Blanco (U20240435)
// Historia:  Conteo y resumen de incidencias por estado para
//            visibilidad general del módulo de soporte.
// Criterio:  Admin ve todo. Empleado solo sus proyectos.
//            Alerta visual cuando hay incidencias Alta/Crítica
//            en estado Pendiente.
// ============================================================
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// ── Autorización ─────────────────────────────────────────────
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}
if (!in_array($_SESSION['rol'], ['Administrador', 'Empleado'], true)) {
    echo json_encode(['error' => true, 'mensaje' => 'Sin permisos para acceder al dashboard de soporte']); exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];
$rol        = $_SESSION['rol'];

try {
    $conn = getConexion();

    // ── Scope según rol ──────────────────────────────────────
    // Admin: todas las incidencias
    // Empleado: solo proyectos donde está asignado en proyecto_usuario
    $join_scope = ($rol === 'Empleado')
        ? "INNER JOIN proyecto_usuario pu ON pu.id_proyecto = i.id_proyecto AND pu.id_usuario = {$id_usuario}"
        : '';

    // ── 1. Conteo por estado ─────────────────────────────────
    $res_est = $conn->query("
        SELECT i.estado, COUNT(*) AS total
        FROM incidencia i {$join_scope}
        GROUP BY i.estado
        ORDER BY FIELD(i.estado, 'Pendiente', 'En atencion', 'En atención', 'Resuelto', 'Cerrado')
    ");
    $conteo_estados = ['Pendiente' => 0, 'En atención' => 0, 'Resuelto' => 0, 'Cerrado' => 0];
    while ($row = $res_est->fetch_assoc()) {
        $e = ($row['estado'] === 'En atencion') ? 'En atención' : $row['estado'];
        if (isset($conteo_estados[$e])) $conteo_estados[$e] = (int)$row['total'];
    }

    // ── 2. Conteo por prioridad ──────────────────────────────
    $res_prio = $conn->query("
        SELECT i.prioridad, COUNT(*) AS total
        FROM incidencia i {$join_scope}
        GROUP BY i.prioridad
        ORDER BY FIELD(i.prioridad, 'Crítica', 'Alta', 'Media', 'Baja')
    ");
    $conteo_prioridad = ['Crítica' => 0, 'Alta' => 0, 'Media' => 0, 'Baja' => 0];
    while ($row = $res_prio->fetch_assoc()) {
        if (isset($conteo_prioridad[$row['prioridad']])) $conteo_prioridad[$row['prioridad']] = (int)$row['total'];
    }

    // ── 3. Alertas: Alta/Crítica en estado Pendiente ─────────
    $res_alerta = $conn->query("
        SELECT i.id_incidencia, i.titulo, i.prioridad, i.creado_en,
               p.nombre AS nombre_proyecto,
               CONCAT(u.nombre, ' ', u.apellido) AS cliente
        FROM incidencia i
        INNER JOIN proyecto p ON p.id_proyecto = i.id_proyecto
        INNER JOIN cliente  c ON c.id_cliente  = p.id_cliente
        INNER JOIN usuario  u ON u.id_usuario  = c.id_usuario
        {$join_scope}
        WHERE i.estado = 'Pendiente' AND i.prioridad IN ('Alta', 'Crítica')
        ORDER BY FIELD(i.prioridad, 'Crítica', 'Alta'), i.creado_en ASC
    ");
    $alertas = [];
    while ($row = $res_alerta->fetch_assoc()) $alertas[] = $row;

    // ── 4. Top 5 proyectos con más incidencias abiertas ──────
    $res_top = $conn->query("
        SELECT p.id_proyecto, p.nombre AS nombre_proyecto,
               COUNT(i.id_incidencia) AS total,
               SUM(CASE WHEN i.estado IN ('Pendiente','En atencion','En atención') THEN 1 ELSE 0 END) AS abiertas,
               SUM(CASE WHEN i.prioridad IN ('Alta','Crítica') THEN 1 ELSE 0 END) AS prioridad_alta
        FROM incidencia i
        INNER JOIN proyecto p ON p.id_proyecto = i.id_proyecto
        {$join_scope}
        GROUP BY p.id_proyecto, p.nombre
        ORDER BY abiertas DESC, prioridad_alta DESC
        LIMIT 5
    ");
    $top_proyectos = [];
    while ($row = $res_top->fetch_assoc()) {
        $top_proyectos[] = [
            'id_proyecto'     => (int)$row['id_proyecto'],
            'nombre_proyecto' => $row['nombre_proyecto'],
            'total'           => (int)$row['total'],
            'abiertas'        => (int)$row['abiertas'],
            'prioridad_alta'  => (int)$row['prioridad_alta'],
        ];
    }

    // ── 5. Actividad reciente (notificaciones no leídas) ──────
    $res_notif = $conn->query("
        SELECT n.id_notificacion, n.id_incidencia, n.mensaje, n.creado_en,
               i.prioridad, i.titulo, p.nombre AS proyecto
        FROM soporte_notificacion n
        INNER JOIN incidencia i ON n.id_incidencia = i.id_incidencia
        INNER JOIN proyecto   p ON i.id_proyecto   = p.id_proyecto
        {$join_scope}
        WHERE n.leida = 0
        ORDER BY n.creado_en DESC
        LIMIT 10
    ");
    $notificaciones = [];
    while ($row = $res_notif->fetch_assoc()) $notificaciones[] = $row;

    $conn->close();

    $total_general = array_sum($conteo_estados);
    $total_abiertas = $conteo_estados['Pendiente'] + $conteo_estados['En atención'];

    echo json_encode([
        'error'       => false,
        'rol_usuario' => $rol,
        'dashboard'   => [
            'total_incidencias' => $total_general,
            'total_abiertas'    => $total_abiertas,
            'total_resueltas'   => $conteo_estados['Resuelto'],
            'total_cerradas'    => $conteo_estados['Cerrado'],
            'por_estado'        => $conteo_estados,
            'por_prioridad'     => $conteo_prioridad,
        ],
        'alertas_alta_prioridad' => [
            'tiene_alertas' => count($alertas) > 0,
            'total'         => count($alertas),
            'incidencias'   => $alertas,
        ],
        'top_proyectos'      => $top_proyectos,
        'notificaciones_pendientes' => $notificaciones,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}
?>
