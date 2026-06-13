<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

if ($_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['error' => true, 'mensaje' => 'Sin permisos para ver calificaciones']);
    exit;
}

try {
    $conn = getConexion();
    $sql = "
        SELECT
            cal.id,
            cal.puntuacion,
            cal.comentario,
            cal.fecha,
            p.nombre AS proyecto,
            COALESCE(NULLIF(c.nombre_empresa, ''), CONCAT(u.nombre, ' ', u.apellido)) AS cliente
        FROM calificaciones cal
        INNER JOIN proyecto p ON p.id_proyecto = cal.id_proyecto
        INNER JOIN cliente c ON c.id_cliente = cal.id_cliente
        LEFT JOIN usuario u ON u.id_usuario = c.id_usuario
        ORDER BY cal.fecha DESC, cal.id DESC
    ";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception($conn->error);
    }

    $calificaciones = [];
    while ($row = $result->fetch_assoc()) {
        $calificaciones[] = [
            'id' => (int)$row['id'],
            'proyecto' => $row['proyecto'],
            'cliente' => $row['cliente'],
            'estrellas' => (int)$row['puntuacion'],
            'comentario' => $row['comentario'] ?? '',
            'fecha' => $row['fecha']
        ];
    }

    $conn->close();
    echo json_encode(['error' => false, 'calificaciones' => $calificaciones], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'mensaje' => 'Error al listar calificaciones: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
