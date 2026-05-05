<?php
session_start();
header('Content-Type: application/json');
require 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']); exit;
}
if (!in_array($_SESSION['rol'], ['Administrador', 'Empleado'])) {
    echo json_encode(['success' => false, 'error' => 'Sin permisos']); exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No se recibieron datos']); exit;
}

$nombre      = trim($data['nombre']      ?? '');
$idCliente   = intval($data['idCliente'] ?? 0);
$estado      = trim($data['estado']      ?? 'Pendiente');
$fechaInicio = trim($data['fechaInicio'] ?? '');
$fechaFin    = trim($data['fechaFin']    ?? '');
$monto       = floatval($data['monto']   ?? 0);

$anticipo_monto  = round($monto / 2, 2);
$anticipo_fecha  = trim($data['anticipo_fecha']       ?? '');
$anticipo_metodo = trim($data['anticipo_metodo']      ?? '');
$anticipo_comp   = trim($data['anticipo_comprobante'] ?? '');

if (!$nombre || !$idCliente || !$fechaInicio || !$fechaFin) {
    echo json_encode(['success' => false, 'error' => 'Datos del proyecto incompletos']); exit;
}
if ($monto <= 0) {
    echo json_encode(['success' => false, 'error' => 'El monto del proyecto debe ser mayor a 0']); exit;
}

// ── Validaciones anticipo ──
if (!$anticipo_fecha) {
    echo json_encode(['success' => false, 'error' => 'La fecha del anticipo es requerida']); exit;
}
if (!in_array($anticipo_metodo, ['Efectivo', 'Transferencia', 'Cheque'])) {
    echo json_encode(['success' => false, 'error' => 'Método de pago del anticipo inválido']); exit;
}

$conn = getConexion();
$conn->begin_transaction();

try {
    $stmt1 = $conn->prepare(
        "INSERT INTO proyecto (nombre, estado, fecha_inicio, fecha_entrega, id_cliente, monto, anticipo_pagado)
         VALUES (?, ?, ?, ?, ?, ?, 1)"
    );
    $stmt1->bind_param("ssssid", $nombre, $estado, $fechaInicio, $fechaFin, $idCliente, $monto);
    $stmt1->execute();
    $id_proyecto = $conn->insert_id;
    $stmt1->close();

    $stmt2 = $conn->prepare(
        "INSERT INTO pago (id_proyecto, tipo_pago, monto, fecha_pago, metodo_pago, comprobante, registrado_por)
         VALUES (?, 'Anticipo', ?, ?, ?, ?, ?)"
    );
    $stmt2->bind_param("idsssi",
        $id_proyecto,
        $anticipo_monto,
        $anticipo_fecha,
        $anticipo_metodo,
        $anticipo_comp,
        $_SESSION['id_usuario']
    );
    $stmt2->execute();
    $stmt2->close();

    $conn->commit();
    echo json_encode(['success' => true, 'id_proyecto' => $id_proyecto]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
}

$conn->close();
?>