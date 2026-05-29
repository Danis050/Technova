<?php
//php para el filtro de incidencias
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

$estado_filtro = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$id_proyecto_filtro = isset($_GET['id_proyecto']) ? intval($_GET['id_proyecto']) : 0;

try {
    $conn = getConexion();

    $sql = "SELECT i.id_incidencia, i.titulo, i.descripcion, i.prioridad, i.estado, i.creado_en, p.nombre as nombre_proyecto 
            FROM incidencia i
            JOIN proyecto p ON i.id_proyecto = p.id_proyecto
            WHERE i.id_usuario_reporta = ?";

    $types = "i";
    $params = [$id_usuario];

    if ($estado_filtro !== '' && $estado_filtro !== 'Todos') {
        $sql .= " AND i.estado = ?";
        $types .= "s";
        $params[] = $estado_filtro;
    }

    if ($id_proyecto_filtro > 0) {
        $sql .= " AND i.id_proyecto = ?";
        $types .= "i";
        $params[] = $id_proyecto_filtro;
    }
    
    $sql .= " ORDER BY i.creado_en DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $incidencias = [];
    while ($row = $result->fetch_assoc()) {
        $incidencias[] = $row;
    }

    echo json_encode(['error' => false, 'incidencias' => $incidencias]);
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['error' => true, 'mensaje' => 'Error al consultar: ' . $e->getMessage()]);
}
?>