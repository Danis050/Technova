<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

if ($_SESSION['rol'] !== 'Empleado') {
    echo json_encode(['error' => true, 'mensaje' => 'Acceso denegado']);
    exit;
}

$id_usuario = (int) $_SESSION['id_usuario'];

try {
    $conn = getConexion();

    $stmt = $conn->prepare("
        SELECT
            p.id_proyecto,
            p.nombre,
            p.descripcion,
            p.fecha_inicio,
            p.fecha_entrega,
            p.estado,
            p.creado_en,
            COALESCE(NULLIF(c.nombre_empresa, ''), CONCAT(u.nombre, ' ', u.apellido)) AS cliente
        FROM proyecto p
        INNER JOIN proyecto_usuario pu ON pu.id_proyecto = p.id_proyecto
        LEFT JOIN cliente c ON c.id_cliente = p.id_cliente
        LEFT JOIN usuario u ON u.id_usuario = c.id_usuario
        WHERE pu.id_usuario = ?
        ORDER BY p.creado_en DESC
    ");

    $stmt->bind_param('i', $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    $proyectos = [];
    while ($row = $result->fetch_assoc()) {
        $proyectos[] = [
            'id'           => (int) $row['id_proyecto'],
            'nombre'       => $row['nombre'],
            'descripcion'  => $row['descripcion'],
            'fechaInicio'  => $row['fecha_inicio'],
            'fechaEntrega' => $row['fecha_entrega'],
            'estado'       => $row['estado'],
            'cliente'      => $row['cliente'] ?? ''
        ];
    }

    $stmt->close();
    $conn->close();

    echo json_encode($proyectos, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}
?>
