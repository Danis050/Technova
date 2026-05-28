<?php
// HU-29 | Misael A. Juarez Reyes
// Consultar notificaciones automaticas de soporte con prioridad Alta.

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}
if (!in_array($_SESSION['rol'], ['Administrador', 'Empleado'], true)) {
    echo json_encode(['error' => true, 'mensaje' => 'Sin permisos']); exit;
}

$conn = getConexion();

$sql = "SELECT n.id_notificacion, n.id_incidencia, n.mensaje, n.leida, n.creado_en,
               i.prioridad, i.titulo, p.nombre AS proyecto
        FROM soporte_notificacion n
        INNER JOIN incidencia i ON n.id_incidencia = i.id_incidencia
        INNER JOIN proyecto p ON i.id_proyecto = p.id_proyecto
        WHERE n.leida = 0
        ORDER BY n.creado_en DESC";
$res = $conn->query($sql);

$notificaciones = [];
while ($row = $res->fetch_assoc()) {
    $notificaciones[] = [
        'id' => (int)$row['id_notificacion'],
        'idIncidencia' => (int)$row['id_incidencia'],
        'mensaje' => $row['mensaje'],
        'prioridad' => $row['prioridad'],
        'titulo' => $row['titulo'],
        'proyecto' => $row['proyecto'],
        'creadoEn' => $row['creado_en']
    ];
}

$conn->close();
echo json_encode(['error' => false, 'total' => count($notificaciones), 'notificaciones' => $notificaciones], JSON_UNESCAPED_UNICODE);
?>
