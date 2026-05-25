<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id_proyecto = isset($data['id_proyecto']) ? (int)$data['id_proyecto'] : null;
$id_usuario = isset($data['id_usuario']) ? (int)$data['id_usuario'] : null;
$accion = isset($data['accion']) ? $data['accion'] : null; // 'asignar' o 'desasignar'

if (!$id_proyecto || !$id_usuario || !in_array($accion, ['asignar', 'desasignar'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Faltan datos obligatorios o la acción es inválida.']);
    exit;
}

try {
    $conn = getConexion();

    $stmt_check = $conn->prepare("SELECT estado FROM proyecto WHERE id_proyecto = ?");
    $stmt_check->bind_param("i", $id_proyecto);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        throw new Exception("El proyecto no existe.");
    }

    $proyecto = $result_check->fetch_assoc();
    if (in_array($proyecto['estado'], ['Completado', 'Cancelado'])) {
        throw new Exception("No se pueden modificar miembros. El proyecto está " . $proyecto['estado'] . ".");
    }
    $stmt_check->close();

    if ($accion === 'asignar') {
        $stmt = $conn->prepare("INSERT IGNORE INTO proyecto_usuario (id_proyecto, id_usuario) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_proyecto, $id_usuario);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al asignar el miembro: " . $stmt->error);
        }
        $mensaje = "Miembro asignado correctamente.";
        
    } else if ($accion === 'desasignar') {
        $stmt = $conn->prepare("DELETE FROM proyecto_usuario WHERE id_proyecto = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $id_proyecto, $id_usuario);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al desasignar el miembro: " . $stmt->error);
        }
        $mensaje = "Miembro desasignado correctamente.";
    }

    $stmt->close();
    $conn->close();

    echo json_encode(['error' => false, 'mensaje' => $mensaje]);

} catch (Exception $e) {
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}
?>