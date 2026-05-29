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
if (!$anticipo_fecha) {
    echo json_encode(['success' => false, 'error' => 'La fecha del anticipo es requerida']); exit;
}
if (!in_array($anticipo_metodo, ['Efectivo', 'Transferencia', 'Cheque'])) {
    echo json_encode(['success' => false, 'error' => 'Método de pago del anticipo inválido']); exit;
}

$conn = getConexion();
$conn->begin_transaction();

try {
    // 1. Insertar proyecto con monto
    $stmt1 = $conn->prepare(
        "INSERT INTO proyecto (nombre, estado, fecha_inicio, fecha_entrega, id_cliente, monto, anticipo_pagado)
         VALUES (?, ?, ?, ?, ?, ?, 1)"
    );
    $stmt1->bind_param("ssssid", $nombre, $estado, $fechaInicio, $fechaFin, $idCliente, $monto);
    $stmt1->execute();
    $id_proyecto = $conn->insert_id;
    $stmt1->close();

    // 2. Insertar anticipo en tabla pago
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

    // 3. Generar factura del anticipo
    $IVA_PCT          = 0.13;
    $monto_factura    = $anticipo_monto;
    $subtotal_factura = round($monto_factura / (1 + $IVA_PCT), 2);
    $iva_factura      = round($monto_factura - $subtotal_factura, 2);
    $id_usuario       = $_SESSION['id_usuario'];
    $fecha_emision    = date('Y-m-d');
    $anio             = date('Y');

    $stmt_n = $conn->prepare("SELECT COUNT(*) AS cnt FROM factura WHERE YEAR(fecha_emision) = ?");
    $stmt_n->bind_param("i", $anio);
    $stmt_n->execute();
    $cnt = $stmt_n->get_result()->fetch_assoc()['cnt'];
    $stmt_n->close();
    $numero_factura = 'TN-' . $anio . '-' . str_pad($cnt + 1, 6, '0', STR_PAD_LEFT);

    $stmt_f = $conn->prepare(
        "INSERT INTO factura (numero_factura, id_cliente, id_proyecto, subtotal, iva, total,
                              estado, generada_por, fecha_emision)
         VALUES (?, ?, ?, ?, ?, ?, 'Pendiente', ?, ?)"
    );
    $stmt_f->bind_param("siidddis",
        $numero_factura, $idCliente, $id_proyecto,
        $subtotal_factura, $iva_factura, $monto_factura,
        $id_usuario, $fecha_emision
    );
    $stmt_f->execute();
    $stmt_f->close();

    echo json_encode(['success' => true, 'id_proyecto' => $id_proyecto]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Error al guardar: ' . $e->getMessage()]);
}

$conn->close();
?>