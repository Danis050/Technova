<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}
if (!in_array($_SESSION['rol'], ['Administrador', 'Empleado'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Sin permisos para registrar pagos']); exit;
}

$id_proyecto = intval($_POST['id_proyecto'] ?? 0);
$monto       = floatval($_POST['monto'] ?? 0);
$fecha_pago  = trim($_POST['fecha_pago'] ?? '');
$metodo      = trim($_POST['metodo_pago'] ?? '');
$comprobante = trim($_POST['comprobante'] ?? '');

if (!$id_proyecto || $monto <= 0 || !$fecha_pago || !$metodo) {
    echo json_encode(['error' => true, 'mensaje' => 'Todos los campos requeridos deben completarse.']); exit;
}
if (!in_array($metodo, ['Efectivo', 'Transferencia', 'Cheque'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Método de pago inválido.']); exit;
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_pago)) {
    echo json_encode(['error' => true, 'mensaje' => 'Formato de fecha inválido.']); exit;
}

$conn = getConexion();

$stmt = $conn->prepare("SELECT anticipo_pagado, estado FROM proyecto WHERE id_proyecto = ?");
$stmt->bind_param("i", $id_proyecto);
$stmt->execute();
$proyecto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$proyecto) {
    echo json_encode(['error' => true, 'mensaje' => 'Proyecto no encontrado.']); exit;
}
if ($proyecto['anticipo_pagado'] == 1) {
    echo json_encode(['error' => true, 'mensaje' => 'Este proyecto ya tiene un anticipo registrado.']); exit;
}
if (in_array($proyecto['estado'], ['Completado', 'Cancelado'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No se puede registrar anticipo en un proyecto ' . $proyecto['estado'] . '.']); exit;
}

$stmt2 = $conn->prepare(
    "INSERT INTO pago (id_proyecto, tipo_pago, monto, fecha_pago, metodo_pago, comprobante, registrado_por)
     VALUES (?, 'Anticipo', ?, ?, ?, ?, ?)"
);
$stmt2->bind_param("idsssi", $id_proyecto, $monto, $fecha_pago, $metodo, $comprobante, $_SESSION['id_usuario']);

if (!$stmt2->execute()) {
    echo json_encode(['error' => true, 'mensaje' => 'Error al guardar el pago: ' . $stmt2->error]); 
    $stmt2->close(); $conn->close(); exit;
}
$stmt2->close();

$stmt3 = $conn->prepare("UPDATE proyecto SET anticipo_pagado = 1 WHERE id_proyecto = ?");
$stmt3->bind_param("i", $id_proyecto);
$stmt3->execute();
$stmt3->close();

$conn->close();
echo json_encode(['error' => false, 'mensaje' => 'Anticipo registrado exitosamente. El proyecto puede iniciar.']);
?>