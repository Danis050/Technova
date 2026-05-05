<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}
if (!in_array($_SESSION['rol'], ['Administrador', 'Empleado'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Sin permisos']); exit;
}

$id_proyecto = intval($_POST['id_proyecto'] ?? 0);
$tipo_pago   = trim($_POST['tipo_pago']   ?? '');
$monto       = floatval($_POST['monto']   ?? 0);
$fecha_pago  = trim($_POST['fecha_pago']  ?? '');
$metodo      = trim($_POST['metodo_pago'] ?? '');
$comprobante = trim($_POST['comprobante'] ?? '');

if (!$id_proyecto || !$tipo_pago || !$fecha_pago || !$metodo) {
    echo json_encode(['error' => true, 'mensaje' => 'Todos los campos son requeridos.']); exit;
}

if (!in_array($tipo_pago, ['Parcial', 'Saldo Final'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Tipo de pago no permitido. Los anticipos se registran al crear el proyecto.']); exit;
}

if ($monto <= 0) {
    echo json_encode(['error' => true, 'mensaje' => 'El monto debe ser mayor a 0.']); exit;
}
if (!in_array($metodo, ['Efectivo', 'Transferencia', 'Cheque'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Método de pago inválido.']); exit;
}

$conn = getConexion();

$stmt = $conn->prepare(
    "SELECT p.monto AS monto_proyecto,
            COALESCE(SUM(pg.monto), 0) AS total_pagado
     FROM proyecto p
     LEFT JOIN pago pg ON pg.id_proyecto = p.id_proyecto
     WHERE p.id_proyecto = ?
     GROUP BY p.id_proyecto"
);
$stmt->bind_param("i", $id_proyecto);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['error' => true, 'mensaje' => 'Proyecto no encontrado.']); exit;
}

$saldo_pendiente = max(0, floatval($row['monto_proyecto']) - floatval($row['total_pagado']));

if ($monto > $saldo_pendiente + 0.01) {
    echo json_encode([
        'error'   => true,
        'mensaje' => 'El monto ($' . number_format($monto, 2) . ') supera el saldo pendiente ($' . number_format($saldo_pendiente, 2) . ').'
    ]); exit;
}

if ($tipo_pago === 'Saldo Final') {
    $monto = $saldo_pendiente;
}

$stmt2 = $conn->prepare(
    "INSERT INTO pago (id_proyecto, tipo_pago, monto, fecha_pago, metodo_pago, comprobante, registrado_por)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt2->bind_param("isdsssi",
    $id_proyecto, $tipo_pago, $monto, $fecha_pago, $metodo, $comprobante, $_SESSION['id_usuario']
);

if (!$stmt2->execute()) {
    echo json_encode(['error' => true, 'mensaje' => 'Error al guardar el pago: ' . $stmt2->error]); exit;
}
$stmt2->close();
$conn->close();

$msg = $tipo_pago === 'Saldo Final'
    ? 'Saldo final registrado. El proyecto queda saldado.'
    : 'Pago parcial registrado exitosamente.';

echo json_encode(['error' => false, 'mensaje' => $msg]);
?>
