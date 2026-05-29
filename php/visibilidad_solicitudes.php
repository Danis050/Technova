<?php
// ============================================================
// VISIBILIDAD DE INCIDENCIAS SEGÚN ROL — HU-28 | TechNova
// Desarrollador: David Alexander Urias Blanco (U20240435)
// Historia:  Como miembro del equipo, quiero ver las incidencias
//            de soporte según mi rol.
// Criterio:  Administrador ve todas. Empleado solo las de
//            proyectos donde participa. Filtros por estado,
//            proyecto y prioridad.
// ============================================================
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// ── Autorización ─────────────────────────────────────────────
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}
if (!in_array($_SESSION['rol'], ['Administrador', 'Empleado'], true)) {
    echo json_encode(['error' => true, 'mensaje' => 'Sin permisos para acceder a las incidencias de soporte']); exit;
}

$id_usuario = (int)$_SESSION['id_usuario'];
$rol        = $_SESSION['rol'];

// ── Filtros opcionales (GET) ─────────────────────────────────
$filtro_estado    = isset($_GET['estado'])      ? trim($_GET['estado'])      : '';
$filtro_proyecto  = isset($_GET['id_proyecto']) ? (int)$_GET['id_proyecto'] : 0;
$filtro_prioridad = isset($_GET['prioridad'])   ? trim($_GET['prioridad'])   : '';

$estados_validos    = ['Pendiente', 'En atencion', 'En atención', 'Resuelto', 'Cerrado'];
$prioridades_validas = ['Alta', 'Media', 'Baja', 'Crítica'];

if ($filtro_estado !== '' && !in_array($filtro_estado, $estados_validos, true)) {
    echo json_encode(['error' => true, 'mensaje' => 'Estado no valido.']); exit;
}
if ($filtro_prioridad !== '' && !in_array($filtro_prioridad, $prioridades_validas, true)) {
    echo json_encode(['error' => true, 'mensaje' => 'Prioridad no valida.']); exit;
}

try {
    $conn = getConexion();

    // ── Construir query según rol ────────────────────────────
    $join_scope  = '';
    $where_parts = [];
    $params      = [];
    $types       = '';

    if ($rol === 'Empleado') {
        // Solo proyectos donde el empleado está asignado
        $join_scope    = "INNER JOIN proyecto_usuario pu ON pu.id_proyecto = i.id_proyecto AND pu.id_usuario = ?";
        $params[]      = $id_usuario;
        $types        .= 'i';
    }
    // Administrador: sin restricción de proyecto

    if ($filtro_estado !== '') {
        $where_parts[] = 'i.estado = ?';
        $params[]      = $filtro_estado;
        $types        .= 's';
    }
    if ($filtro_proyecto > 0) {
        $where_parts[] = 'i.id_proyecto = ?';
        $params[]      = $filtro_proyecto;
        $types        .= 'i';
    }
    if ($filtro_prioridad !== '') {
        $where_parts[] = 'i.prioridad = ?';
        $params[]      = $filtro_prioridad;
        $types        .= 's';
    }

    $where = !empty($where_parts) ? 'WHERE ' . implode(' AND ', $where_parts) : '';

    $sql = "
        SELECT
            i.id_incidencia,
            i.titulo,
            i.descripcion,
            i.prioridad,
            i.estado,
            i.comentario_resolucion,
            i.notificacion_alta,
            i.creado_en,
            p.id_proyecto,
            p.nombre        AS nombre_proyecto,
            CONCAT(u.nombre, ' ', u.apellido) AS nombre_cliente,
            (SELECT COUNT(*) FROM evidencia_incidencia e WHERE e.id_incidencia = i.id_incidencia) AS total_evidencias
        FROM incidencia i
        INNER JOIN proyecto p ON p.id_proyecto = i.id_proyecto
        INNER JOIN cliente  c ON c.id_cliente  = p.id_cliente
        INNER JOIN usuario  u ON u.id_usuario  = c.id_usuario
        {$join_scope}
        {$where}
        ORDER BY
            FIELD(i.prioridad, 'Crítica', 'Alta', 'Media', 'Baja'),
            FIELD(i.estado, 'Pendiente', 'En atencion', 'En atención', 'Resuelto', 'Cerrado'),
            i.creado_en DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $incidencias   = [];
    $conteo_estado = ['Pendiente' => 0, 'En atención' => 0, 'Resuelto' => 0, 'Cerrado' => 0];
    $conteo_prio   = ['Crítica' => 0, 'Alta' => 0, 'Media' => 0, 'Baja' => 0];

    while ($row = $res->fetch_assoc()) {
        $incidencias[] = $row;
        $e = $row['estado'] === 'En atencion' ? 'En atención' : $row['estado'];
        if (isset($conteo_estado[$e]))        $conteo_estado[$e]++;
        if (isset($conteo_prio[$row['prioridad']])) $conteo_prio[$row['prioridad']]++;
    }
    $stmt->close();
    $conn->close();

    echo json_encode([
        'error'      => false,
        'rol_usuario' => $rol,
        'total'      => count($incidencias),
        'resumen'    => ['por_estado' => $conteo_estado, 'por_prioridad' => $conteo_prio],
        'incidencias' => $incidencias,
        'filtros_aplicados' => array_filter([
            'estado'      => $filtro_estado    ?: null,
            'id_proyecto' => $filtro_proyecto  ?: null,
            'prioridad'   => $filtro_prioridad ?: null,
        ])
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}
?>
