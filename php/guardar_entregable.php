<?php
// HU-19 | Misael A. Juarez Reyes
// Crear y editar entregables vinculados a proyectos activos.

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
    responder(true, 'Sin permisos para guardar entregables.');
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    $data = $_POST;
}

$id_entregable = intval($data['id_entregable'] ?? $data['id'] ?? 0);
$id_proyecto = intval($data['id_proyecto'] ?? 0);
$nombre = trim($data['nombre'] ?? '');
$descripcion = trim($data['descripcion'] ?? '');
$id_responsable = intval($data['id_responsable'] ?? 0);
$fecha_estimada = trim($data['fecha_estimada'] ?? '');
$estado = trim($data['estado'] ?? 'Pendiente');
$estados_validos = ['Pendiente', 'En desarrollo', 'Entregado'];

if ($id_proyecto <= 0 || $id_responsable <= 0 || $nombre === '' || $descripcion === '' || $fecha_estimada === '') {
    responder(true, 'Proyecto, nombre, descripcion, responsable y fecha estimada son obligatorios.');
}
if (strlen($nombre) > 150) {
    responder(true, 'El nombre del entregable no puede superar 150 caracteres.');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_estimada)) {
    responder(true, 'La fecha estimada debe tener formato YYYY-MM-DD.');
}
if (!in_array($estado, $estados_validos, true)) {
    responder(true, 'Estado de entregable invalido.');
}

$conn = getConexion();

$stmtProyecto = $conn->prepare("SELECT estado FROM proyecto WHERE id_proyecto = ? LIMIT 1");
$stmtProyecto->bind_param('i', $id_proyecto);
$stmtProyecto->execute();
$proyecto = $stmtProyecto->get_result()->fetch_assoc();
$stmtProyecto->close();

if (!$proyecto) {
    responder(true, 'Proyecto no encontrado.');
}
if (!in_array($proyecto['estado'], ['Activo', 'En Proceso', 'Pendiente'], true)) {
    responder(true, 'Solo se pueden registrar o editar entregables en proyectos activos.');
}

$stmtResponsable = $conn->prepare("SELECT id_usuario FROM usuario WHERE id_usuario = ? AND estado = 1 LIMIT 1");
$stmtResponsable->bind_param('i', $id_responsable);
$stmtResponsable->execute();
$responsable = $stmtResponsable->get_result()->fetch_assoc();
$stmtResponsable->close();

if (!$responsable) {
    responder(true, 'Responsable no encontrado o inactivo.');
}

if ($id_entregable > 0) {
    $stmt = $conn->prepare(
        "UPDATE entregable
         SET id_proyecto = ?, nombre = ?, descripcion = ?, id_responsable = ?, fecha_estimada = ?, estado = ?
         WHERE id_entregable = ?"
    );
    $stmt->bind_param('ississi', $id_proyecto, $nombre, $descripcion, $id_responsable, $fecha_estimada, $estado, $id_entregable);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        responder(true, 'Error al actualizar el entregable.');
    }
    responder(false, 'Entregable actualizado correctamente.', ['id_entregable' => $id_entregable]);
}

$stmt = $conn->prepare(
    "INSERT INTO entregable (id_proyecto, nombre, descripcion, id_responsable, fecha_estimada, estado)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('ississ', $id_proyecto, $nombre, $descripcion, $id_responsable, $fecha_estimada, $estado);

if (!$stmt->execute()) {
    responder(true, 'Error al registrar el entregable.');
}

$id_nuevo = $conn->insert_id;
$stmt->close();
$conn->close();

responder(false, 'Entregable registrado correctamente.', ['id_entregable' => $id_nuevo]);
?>
