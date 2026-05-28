<?php
// HU-26 / HU-29 | Reportar incidencia post-entrega
// Validacion de proyecto finalizado y notificacion de prioridad Alta: Misael A. Juarez Reyes.

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}
if ($_SESSION['rol'] !== 'Cliente') {
    echo json_encode(['error' => true, 'mensaje' => 'Solo clientes pueden reportar incidencias']); exit;
}

$id_proyecto = intval($_POST['id_proyecto'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$prioridad = trim($_POST['prioridad'] ?? '');

if (!$id_proyecto || !$titulo || !$descripcion || !$prioridad) {
    echo json_encode(['error' => true, 'mensaje' => 'Todos los campos son requeridos.']); exit;
}
if ($prioridad === 'Critica') {
    $prioridad = 'Crítica';
}
if (!in_array($prioridad, ['Baja', 'Media', 'Alta', 'Crítica'], true)) {
    echo json_encode(['error' => true, 'mensaje' => 'Prioridad invalida.']); exit;
}
if (strlen($titulo) > 150) {
    echo json_encode(['error' => true, 'mensaje' => 'El titulo no puede superar 150 caracteres.']); exit;
}

$conn = getConexion();

$stmt_v = $conn->prepare(
    "SELECT p.id_proyecto, p.estado FROM proyecto p
     INNER JOIN cliente c ON p.id_cliente = c.id_cliente
     WHERE p.id_proyecto = ? AND c.id_usuario = ?"
);
$stmt_v->bind_param("ii", $id_proyecto, $_SESSION['id_usuario']);
$stmt_v->execute();
$valido = $stmt_v->get_result()->fetch_assoc();
$stmt_v->close();

if (!$valido || !in_array($valido['estado'], ['Finalizado', 'Completado', 'Cerrado'], true)) {
    echo json_encode(['error' => true, 'mensaje' => 'El proyecto no existe, no te pertenece, o aun no esta finalizado.']); exit;
}

$conn->begin_transaction();

$stmt = $conn->prepare(
    "INSERT INTO incidencia (id_proyecto, id_usuario, titulo, descripcion, prioridad, estado, creado_en)
     VALUES (?, ?, ?, ?, ?, 'Pendiente', NOW())"
);
$stmt->bind_param("iisss", $id_proyecto, $_SESSION['id_usuario'], $titulo, $descripcion, $prioridad);

if (!$stmt->execute()) {
    $conn->rollback();
    echo json_encode(['error' => true, 'mensaje' => 'Error al registrar incidencia: ' . $stmt->error]); exit;
}
$id_incidencia = $conn->insert_id;
$stmt->close();

if ($prioridad === 'Alta') {
    $mensaje = 'Nueva solicitud de soporte de prioridad Alta: ' . $titulo;
    $stmt_n = $conn->prepare("INSERT INTO soporte_notificacion (id_incidencia, mensaje) VALUES (?, ?)");
    if ($stmt_n) {
        $stmt_n->bind_param("is", $id_incidencia, $mensaje);
        $stmt_n->execute();
        $stmt_n->close();
    }

    $stmt_m = $conn->prepare("UPDATE incidencia SET notificacion_alta = 1 WHERE id_incidencia = ?");
    if ($stmt_m) {
        $stmt_m->bind_param("i", $id_incidencia);
        $stmt_m->execute();
        $stmt_m->close();
    }
}

$conn->commit();
$conn->close();

echo json_encode([
    'error' => false,
    'mensaje' => $prioridad === 'Alta'
        ? 'Incidencia reportada correctamente. El equipo fue notificado por prioridad Alta.'
        : 'Incidencia reportada correctamente.',
    'id_incidencia' => $id_incidencia
], JSON_UNESCAPED_UNICODE);
?>
