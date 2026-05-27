<?php
// HU-16 | Listar incidencias del cliente en sesión
// Dev: David A. Urias Blanco (U20240435)

session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}
if ($_SESSION['rol'] !== 'Cliente') {
    echo json_encode(['error' => true, 'mensaje' => 'Sin permisos']); exit;
}

$conn = getConexion();

$stmt = $conn->prepare(
    "SELECT i.id_incidencia, i.titulo, i.descripcion, i.prioridad, i.estado,
            i.creado_en, p.nombre AS nombre_proyecto
     FROM incidencia i
     INNER JOIN proyecto p ON i.id_proyecto = p.id_proyecto
     INNER JOIN cliente c  ON p.id_cliente  = c.id_cliente
     WHERE c.id_usuario = ?
     ORDER BY i.creado_en DESC"
);
$stmt->bind_param("i", $_SESSION['id_usuario']);
$stmt->execute();
$res = $stmt->get_result();

$incidencias = [];
while ($row = $res->fetch_assoc()) {
    $estado = [
        'Abierta' => 'Pendiente',
        'En Proceso' => 'En atención',
        'Resuelta' => 'Resuelto',
        'Cerrada' => 'Cerrado'
    ][$row['estado']] ?? $row['estado'];

    $incidencias[] = [
        'id'              => (int)$row['id_incidencia'],
        'titulo'          => $row['titulo'],
        'descripcion'     => $row['descripcion'],
        'prioridad'       => $row['prioridad'],
        'estado'          => $estado,
        'creadoEn'        => $row['creado_en'],
        'nombreProyecto'  => $row['nombre_proyecto']
    ];
}

$stmt->close();
$conn->close();

echo json_encode($incidencias);
?>
