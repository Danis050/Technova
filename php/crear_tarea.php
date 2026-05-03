<?php
// ============================================================
// CREAR TAREA — HU-09 Tarea 3 | TechNova
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
$titulo      = trim($_POST['titulo']       ?? '');
$descripcion = trim($_POST['descripcion']  ?? '');
$id_asignado = intval($_POST['id_asignado'] ?? 0) ?: null;

// Validar campos obligatorios
if ($id_proyecto <= 0 || $titulo === '') {
    echo json_encode(['error' => true, 'mensaje' => 'id_proyecto y titulo son obligatorios']);
    exit;
}

$conn = getConexion();

// ── ★ HU-09: verificar que el proyecto esté Activo ★ ──────────────────────────
validar_proyecto_activo($conn, $id_proyecto);
// Si llega aquí, el proyecto existe y está Activo.

// Insertar tarea
$stmt = $conn->prepare(
    "INSERT INTO tarea (id_proyecto, titulo, descripcion, id_asignado, fecha_creacion)
     VALUES (?, ?, ?, ?, NOW())"
);
$stmt->bind_param("issi", $id_proyecto, $titulo, $descripcion, $id_asignado);

if ($stmt->execute()) {
    echo json_encode([
        'error'    => false,
        'mensaje'  => 'Tarea creada exitosamente',
        'id_tarea' => $conn->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => true, 'mensaje' => 'Error al crear la tarea: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
