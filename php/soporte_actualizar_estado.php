<?php
// HU-28 | Misael A. Juarez Reyes
// Cambiar estado de una solicitud de soporte y guardar comentario de resolucion.

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

function responder($error, $mensaje, $extra = []) {
    echo json_encode(array_merge(['error' => $error, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(true, 'Metodo no permitido.');
}
if (!isset($_SESSION['id_usuario'])) {
    responder(true, 'No autorizado.');
}
if (!in_array($_SESSION['rol'], ['Administrador', 'Empleado'], true)) {
    responder(true, 'Sin permisos para gestionar solicitudes.');
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    $data = $_POST;
}

$id_incidencia = intval($data['id_incidencia'] ?? $data['id'] ?? 0);
$estado = trim($data['estado'] ?? '');
$comentario = trim($data['comentario_resolucion'] ?? $data['comentario'] ?? '');
$estados = ['En atencion', 'En atención', 'Resuelto', 'Cerrado'];

if ($id_incidencia <= 0 || $estado === '') {
    responder(true, 'Solicitud y estado son obligatorios.');
}
if (!in_array($estado, $estados, true)) {
    responder(true, 'Estado de soporte invalido.');
}
if ($comentario === '') {
    responder(true, 'Agrega un comentario de resolucion para documentar el cambio.');
}

$conn = getConexion();

$stmtExiste = $conn->prepare("SELECT id_incidencia FROM incidencia WHERE id_incidencia = ? LIMIT 1");
$stmtExiste->bind_param('i', $id_incidencia);
$stmtExiste->execute();
$existe = $stmtExiste->get_result()->fetch_assoc();
$stmtExiste->close();

if (!$existe) {
    responder(true, 'Solicitud de soporte no encontrada.');
}

$stmt = $conn->prepare(
    "UPDATE incidencia
     SET estado = ?, comentario_resolucion = ?, actualizado_por = ?, actualizado_en = NOW()
     WHERE id_incidencia = ?"
);
$stmt->bind_param('ssii', $estado, $comentario, $_SESSION['id_usuario'], $id_incidencia);

if (!$stmt->execute()) {
    responder(true, 'Error al actualizar la solicitud.');
}

$stmt->close();
$conn->close();

responder(false, 'Solicitud actualizada correctamente.', [
    'id_incidencia' => $id_incidencia,
    'estado' => $estado
]);
?>
