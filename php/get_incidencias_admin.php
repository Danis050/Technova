<?php
// get_incidencias_admin.php
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

$stmt = $conn->prepare("
    SELECT 
        i.id_incidencia,
        i.titulo,
        i.descripcion,
        i.prioridad,
        i.estado,
        i.comentario_resolucion,
        i.notificacion_alta,
        i.creado_en,
        p.nombre        AS nombre_proyecto,
        p.id_proyecto,
        CONCAT(u.nombre, ' ', u.apellido) AS nombre_cliente
    FROM incidencia i
    INNER JOIN proyecto p ON i.id_proyecto = p.id_proyecto
    INNER JOIN cliente  c ON p.id_cliente  = c.id_cliente
    INNER JOIN usuario  u ON c.id_usuario  = u.id_usuario
    ORDER BY i.creado_en DESC
");
$stmt->execute();
$res = $stmt->get_result();

$incidencias = [];
while ($row = $res->fetch_assoc()) {
    $incidencias[] = [
        'id'                   => (int)$row['id_incidencia'],
        'titulo'               => $row['titulo'],
        'descripcion'          => $row['descripcion'],
        'prioridad'            => $row['prioridad'],
        'estado'               => $row['estado'],
        'comentario_resolucion'=> $row['comentario_resolucion'],
        'notificacion_alta'    => (bool)$row['notificacion_alta'],
        'creadoEn'             => $row['creado_en'],
        'nombreProyecto'       => $row['nombre_proyecto'],
        'idProyecto'           => (int)$row['id_proyecto'],
        'nombreCliente'        => $row['nombre_cliente']
    ];
}

$stmt->close();
$conn->close();

echo json_encode($incidencias, JSON_UNESCAPED_UNICODE);
?>