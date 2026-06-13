<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$id_proyecto = isset($_GET['id_proyecto']) ? (int) $_GET['id_proyecto'] : 0;
if ($id_proyecto <= 0) {
    echo json_encode(['error' => true, 'mensaje' => 'Seleccione un proyecto valido.']);
    exit;
}

try {
    $conn = getConexion();

    $stmt = $conn->prepare(
        "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.rol, u.puesto, pu.fecha_asignacion
         FROM proyecto_usuario pu
         INNER JOIN usuario u ON u.id_usuario = pu.id_usuario
         WHERE pu.id_proyecto = ? AND u.estado = 1
         ORDER BY pu.fecha_asignacion DESC, u.nombre ASC, u.apellido ASC"
    );
    $stmt->bind_param('i', $id_proyecto);
    $stmt->execute();
    $result = $stmt->get_result();

    $miembros = [];
    while ($row = $result->fetch_assoc()) {
        $miembros[] = [
            'id_usuario' => (int) $row['id_usuario'],
            'nombre' => trim($row['nombre'] . ' ' . $row['apellido']),
            'email' => $row['email'],
            'rol' => $row['rol'],
            'puesto' => $row['puesto'] ?? '',
            'fecha_asignacion' => $row['fecha_asignacion']
        ];
    }

    $stmt->close();
    $conn->close();

    echo json_encode(['error' => false, 'miembros' => $miembros], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}
?>
