<?php
// HU-28 | Actualizacion de estado con historial automatico
// Dev historial: Danis I. Vides Aparicio

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';
require_once 'registrar_historial_incidencia.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

if (!in_array($_SESSION['rol'] ?? '', ['Administrador', 'Empleado'], true)) {
    echo json_encode(['error' => true, 'mensaje' => 'Sin permisos para actualizar incidencias']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => true, 'mensaje' => 'Metodo no permitido']);
    exit;
}

$id_incidencia = (int) ($_POST['id_incidencia'] ?? 0);
$nuevo_estado = trim($_POST['nuevo_estado'] ?? '');
$comentario = trim($_POST['comentario'] ?? '');
$estados_validos = ['Abierta', 'En Proceso', 'Resuelta', 'Cerrada'];

if ($id_incidencia <= 0 || !in_array($nuevo_estado, $estados_validos, true)) {
    echo json_encode(['error' => true, 'mensaje' => 'Datos invalidos para actualizar la incidencia.']);
    exit;
}

$conn = getConexion();
$conn->begin_transaction();

try {
    $stmt = $conn->prepare("SELECT estado FROM incidencia WHERE id_incidencia = ? FOR UPDATE");
    $stmt->bind_param("i", $id_incidencia);
    $stmt->execute();
    $incidencia = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$incidencia) {
        throw new Exception('Incidencia no encontrada.');
    }

    $estado_anterior = $incidencia['estado'];
    if ($estado_anterior === $nuevo_estado) {
        throw new Exception('La incidencia ya tiene ese estado.');
    }

    $stmtUpdate = $conn->prepare(
        "UPDATE incidencia
         SET estado = ?, actualizado_en = NOW()
         WHERE id_incidencia = ?"
    );
    $stmtUpdate->bind_param("si", $nuevo_estado, $id_incidencia);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    registrar_historial_incidencia(
        $conn,
        $id_incidencia,
        $estado_anterior,
        $nuevo_estado,
        (int) $_SESSION['id_usuario'],
        $comentario !== '' ? $comentario : null
    );

    $conn->commit();
    echo json_encode([
        'error' => false,
        'mensaje' => 'Estado actualizado e historial registrado.',
        'estadoAnterior' => $estado_anterior,
        'estadoNuevo' => $nuevo_estado
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}

$conn->close();
?>
