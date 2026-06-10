<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// 1. Validación estricta de seguridad: Solo Administradores
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode([
        'error' => true, 
        'mensaje' => 'Acceso denegado. Se requieren privilegios de Administrador para ver la auditoría.'
    ]);
    exit;
}

$conn = getConexion();

// 2. Captura de parámetros GET (filtros y paginación)
$usuario_filtro = isset($_GET['usuario']) ? trim($_GET['usuario']) : '';
$accion_filtro  = isset($_GET['accion']) ? trim($_GET['accion']) : '';
$fecha_inicio   = isset($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : '';
$fecha_fin      = isset($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : '';

// Lógica de Paginación
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
if ($pagina < 1) $pagina = 1;

$limite = 50;
$offset = ($pagina - 1) * $limite;

try {
    // 3. Construcción dinámica de la consulta base
    $base_where = " WHERE 1=1";
    $params = [];
    $types = "";

    // Filtro por nombre de usuario (Búsqueda parcial)
    if (!empty($usuario_filtro)) {
        $base_where .= " AND nombre_usuario LIKE ?";
        $params[] = "%" . $usuario_filtro . "%";
        $types .= "s";
    }

    // Filtro por acción exacta
    if (!empty($accion_filtro)) {
        $base_where .= " AND accion = ?";
        $params[] = $accion_filtro;
        $types .= "s";
    }

    // Filtro por rango de fechas
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $base_where .= " AND fecha BETWEEN ? AND ?";
        // Truco: Agregar horas para asegurar que cubra todo el día si envían formato YYYY-MM-DD
        $params[] = strlen($fecha_inicio) === 10 ? $fecha_inicio . " 00:00:00" : $fecha_inicio;
        $params[] = strlen($fecha_fin) === 10 ? $fecha_fin . " 23:59:59" : $fecha_fin;
        $types .= "ss";
    }

    // ---------------------------------------------------------
    // PASO 4: OBTENER EL TOTAL DE REGISTROS (Para el frontend)
    // ---------------------------------------------------------
    $sql_count = "SELECT COUNT(*) AS total FROM auditoria" . $base_where;
    $stmt_count = $conn->prepare($sql_count);
    
    if (!$stmt_count) {
        throw new Exception("Error al preparar conteo: " . $conn->error);
    }

    // Vincular parámetros dinámicos si existen
    if (count($params) > 0) {
        $stmt_count->bind_param($types, ...$params);
    }
    
    $stmt_count->execute();
    $res_count = $stmt_count->get_result()->fetch_assoc();
    $total_registros = (int)$res_count['total'];
    $stmt_count->close();

    // ---------------------------------------------------------
    // PASO 5: OBTENER LOS REGISTROS PAGINADOS
    // ---------------------------------------------------------
    $sql_data = "SELECT id, id_usuario, nombre_usuario, accion, entidad, id_entidad, descripcion, ip_origen, fecha 
                 FROM auditoria" . $base_where . " 
                 ORDER BY fecha DESC 
                 LIMIT ? OFFSET ?";
    
    $stmt_data = $conn->prepare($sql_data);
    if (!$stmt_data) {
        throw new Exception("Error al preparar listado: " . $conn->error);
    }

    // Añadir $limite y $offset a la lista de parámetros
    $params[] = $limite;
    $params[] = $offset;
    $types .= "ii"; // Dos enteros más para LIMIT y OFFSET

    // Vincular todos los parámetros (los de WHERE + los del LIMIT)
    $stmt_data->bind_param($types, ...$params);
    
    $stmt_data->execute();
    $result = $stmt_data->get_result();

    $registros = [];
    while ($row = $result->fetch_assoc()) {
        $registros[] = $row;
    }
    $stmt_data->close();

    // ---------------------------------------------------------
    // PASO 6: RESPUESTA FINAL
    // ---------------------------------------------------------
    echo json_encode([
        'error' => false,
        'total_registros' => $total_registros,
        'total_paginas' => ceil($total_registros / $limite),
        'pagina_actual' => $pagina,
        'registros' => $registros
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error en el servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    $conn->close();
}
?>