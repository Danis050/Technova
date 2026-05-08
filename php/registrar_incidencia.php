<?php
// HU-16 | Reportar incidencia post-entrega
// Dev: David A. Urias Blanco (U20240435)
// Tarea: Formulario de reporte con prioridad y descripción

session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}
if ($_SESSION['rol'] !== 'Cliente') {
    echo json_encode(['error' => true, 'mensaje' => 'Solo clientes pueden reportar incidencias']); exit;
}

$id_proyecto = intval($_POST['id_proyecto']   ?? 0);
$titulo      = trim($_POST['titulo']          ?? '');
$descripcion = trim($_POST['descripcion']     ?? '');
$prioridad   = trim($_POST['prioridad']       ?? '');

if (!$id_proyecto || !$titulo || !$descripcion || !$prioridad) {
    echo json_encode(['error' => true, 'mensaje' => 'Todos los campos son requeridos.']); exit;
}
if (!in_array($prioridad, ['Baja', 'Media', 'Alta', 'Crítica'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Prioridad inválida.']); exit;
}
if (strlen($titulo) > 150) {
    echo json_encode(['error' => true, 'mensaje' => 'El título no puede superar 150 caracteres.']); exit;
}

$conn = getConexion();

// Verificar que el proyecto pertenece al cliente en sesión
$stmt_v = $conn->prepare(
    "SELECT p.id_proyecto FROM proyecto p
     INNER JOIN cliente c ON p.id_cliente = c.id_cliente
     WHERE p.id_proyecto = ? AND c.id_usuario = ? AND p.estado = 'Finalizado'"
);
$stmt_v->bind_param("ii", $id_proyecto, $_SESSION['id_usuario']);
$stmt_v->execute();
$valido = $stmt_v->get_result()->fetch_assoc();
$stmt_v->close();

if (!$valido) {
    echo json_encode(['error' => true, 'mensaje' => 'El proyecto no existe, no te pertenece, o aún no está finalizado.']); exit;
}

// Insertar incidencia
$stmt = $conn->prepare(
    "INSERT INTO incidencia (id_proyecto, id_usuario, titulo, descripcion, prioridad, estado, creado_en)
     VALUES (?, ?, ?, ?, ?, 'Abierta', NOW())"
);
$stmt->bind_param("iisss",
    $id_proyecto, $_SESSION['id_usuario'], $titulo, $descripcion, $prioridad
);

if (!$stmt->execute()) {
    echo json_encode(['error' => true, 'mensaje' => 'Error al registrar incidencia: ' . $stmt->error]); exit;
}
$stmt->close();
$conn->close();

echo json_encode(['error' => false, 'mensaje' => 'Incidencia reportada correctamente.']);
?>
