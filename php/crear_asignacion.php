<?php
// ============================================================
// CREAR ASIGNACIÓN — HU-09 Tarea 3 | TechNova
// ============================================================
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';
require_once 'validar_estado_proyecto.php';   // ← HU-09

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

// Leer campos POST
$id_proyecto = intval($_POST['id_proyecto'] ?? 0);
$id_tarea    = intval($_POST['id_tarea']    ?? 0);
$id_usuario  = intval($_POST['id_usuario']  ?? 0);

// Validar campos obligatorios
if ($id_proyecto <= 0 || $id_tarea <= 0 || $id_usuario <= 0) {
    echo json_encode(['error' => true, 'mensaje' => 'id_proyecto, id_tarea e id_usuario son obligatorios']);
    exit;
}

$conn = getConexion();

// ── ★ HU-09: verificar que el proyecto esté Activo ★ ──────────────────────────
validar_proyecto_activo($conn, $id_proyecto);
// Si llega aquí, el proyecto existe y está Activo.

// Verificar que la tarea pertenece al proyecto
$check = $conn->prepare("SELECT id_tarea FROM tarea WHERE id_tarea = ? AND id_proyecto = ?");
$check->bind_param("ii", $id_tarea, $id_proyecto);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => true, 'mensaje' => 'Tarea no encontrada en este proyecto']);
    $check->close(); $conn->close();
    exit;
}
$check->close();

// Insertar asignación
$stmt = $conn->prepare(
    "INSERT INTO asignacion (id_tarea, id_usuario, fecha_asignacion)
     VALUES (?, ?, NOW())"
);
$stmt->bind_param("ii", $id_tarea, $id_usuario);

if ($stmt->execute()) {
    echo json_encode([
        'error'         => false,
        'mensaje'       => 'Asignación creada exitosamente',
        'id_asignacion' => $conn->insert_id
    ]);
} else {
    // Error 1062 = entrada duplicada (usuario ya asignado)
    if ($conn->errno === 1062) {
        http_response_code(409);
        echo json_encode(['error' => true, 'mensaje' => 'El usuario ya está asignado a esta tarea']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => true, 'mensaje' => 'Error al crear la asignación: ' . $conn->error]);
    }
}

$stmt->close();
$conn->close();
?>
