<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';
require_once 'notificaciones_helper.php';

// Validar sesión activa
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado. Inicia sesión para continuar.']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];

// Recibir los datos del formulario (asumiendo que el frontend usa fetch con JSON)
$data = json_decode(file_get_contents("php://input"), true);

$id_proyecto = isset($data['id_proyecto']) ? (int)$data['id_proyecto'] : null;
$asunto      = isset($data['asunto']) ? trim($data['asunto']) : '';
$descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : '';

// 1. Validar que los campos no estén vacíos
if (!$id_proyecto || empty($asunto) || empty($descripcion)) {
    echo json_encode(['error' => true, 'mensaje' => 'Todos los campos (proyecto, asunto, descripción) son obligatorios.']);
    exit;
}

try {
    $conn = getConexion();

    // 2. Validar que el proyecto exista y esté FINALIZADO ('Completado')
    $stmt_check = $conn->prepare("SELECT estado FROM proyecto WHERE id_proyecto = ?");
    $stmt_check->bind_param("i", $id_proyecto);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("El proyecto seleccionado no existe.");
    }

    $proyecto = $result->fetch_assoc();

    if ($proyecto['estado'] !== 'Completado') {
        throw new Exception("Solo se pueden crear solicitudes para proyectos finalizados. El proyecto actual está: " . $proyecto['estado']);
    }
    $stmt_check->close();

    // 3. Guardar la solicitud en la base de datos con estado inicial 'Pendiente'
    $stmt_insert = $conn->prepare("
        INSERT INTO solicitud (id_proyecto, id_usuario, asunto, descripcion, estado) 
        VALUES (?, ?, ?, ?, 'Pendiente')
    ");
    $stmt_insert->bind_param("iiss", $id_proyecto, $id_usuario, $asunto, $descripcion);

    if (!$stmt_insert->execute()) {
        throw new Exception("Error al guardar la solicitud: " . $stmt_insert->error);
    }

    $id_solicitud = $stmt_insert->insert_id;
    $stmt_insert->close();

    $admins = $conn->query("SELECT id_usuario FROM usuario WHERE rol = 'Administrador' AND estado = 1");
    while ($admin = $admins->fetch_assoc()) {
        crearNotificacion(
            (int)$admin['id_usuario'],
            'Nueva solicitud de soporte: ' . $asunto,
            '../gestion_solicitudes.html?id=' . $id_solicitud
        );
    }
    $conn->close();

    // 4. Respuesta exitosa
    echo json_encode([
        'error' => false, 
        'mensaje' => 'Solicitud registrada correctamente. El equipo la atenderá pronto.'
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}
?>
