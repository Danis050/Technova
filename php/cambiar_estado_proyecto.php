<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No hay sesión activa. Inicie sesión.']);
    exit;
}

if ($_SESSION['rol'] !== 'Administrador') {
    echo json_encode(['error' => true, 'mensaje' => 'Acceso denegado. Se requiere rol Administrador.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => true, 'mensaje' => 'Método no permitido. Use POST.']);
    exit;
}

$id_proyecto  = isset($_POST['id_proyecto'])  ? (int) trim($_POST['id_proyecto'])  : 0;
$nuevo_estado = isset($_POST['nuevo_estado']) ? trim($_POST['nuevo_estado']) : '';

$estados_validos = ['Activo', 'Pausado', 'Cerrado'];

if ($id_proyecto <= 0) {
    echo json_encode(['error' => true, 'mensaje' => 'El campo id_proyecto es obligatorio y debe ser un entero positivo.']);
    exit;
}

if (empty($nuevo_estado) || !in_array($nuevo_estado, $estados_validos)) {
    echo json_encode([
        'error'   => true,
        'mensaje' => 'El campo nuevo_estado es inválido. Valores permitidos: ' . implode(', ', $estados_validos)
    ]);
    exit;
}

$conn = getConexion();

$stmt = $conn->prepare("SELECT estado FROM proyecto WHERE id_proyecto = ?");
$stmt->bind_param("i", $id_proyecto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close(); $conn->close();
    echo json_encode(['error' => true, 'mensaje' => 'Proyecto no encontrado.']);
    exit;
}

$proyecto        = $result->fetch_assoc();
$estado_anterior = $proyecto['estado'];
$stmt->close();

if ($estado_anterior === $nuevo_estado) {
    $conn->close();
    echo json_encode(['error' => true, 'mensaje' => "El proyecto ya se encuentra en estado '$nuevo_estado'."]);
    exit;
}

$conn->begin_transaction();

try {
    $stmtUpdate = $conn->prepare(
        "UPDATE proyecto SET estado = ? WHERE id_proyecto = ?"
    );
    $stmtUpdate->bind_param("si", $nuevo_estado, $id_proyecto);
    $stmtUpdate->execute();

    if ($stmtUpdate->affected_rows === 0) {
        throw new Exception('No se pudo actualizar el estado del proyecto.');
    }
    $stmtUpdate->close();

    $id_usuario = $_SESSION['id_usuario'];
    $stmtLog = $conn->prepare(
        "INSERT INTO proyecto_estado_log (id_proyecto, estado_anterior, estado_nuevo, cambiado_por)
         VALUES (?, ?, ?, ?)"
    );
    $stmtLog->bind_param("issi", $id_proyecto, $estado_anterior, $nuevo_estado, $id_usuario);
    $stmtLog->execute();
    $stmtLog->close();

    $conn->commit();

    echo json_encode([
        'error'           => false,
        'mensaje'         => 'Estado actualizado correctamente.',
        'id_proyecto'     => $id_proyecto,
        'estado_anterior' => $estado_anterior,
        'estado_nuevo'    => $nuevo_estado
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => true, 'mensaje' => 'Error al cambiar el estado: ' . $e->getMessage()]);
}

$conn->close();
?>
