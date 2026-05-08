<?php
// HU-12 | Registrar pago de factura
// Dev: David A. Urias Blanco (U20240435)
// Tarea: Endpoint registrar pago (total/parcial) + cambio automático estado 'Pagada'

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
$tipo_pago   = trim($_POST['tipo_pago']     ?? '');
$monto       = floatval($_POST['monto']     ?? 0);
$fecha_pago  = trim($_POST['fecha_pago']    ?? '');
$metodo      = trim($_POST['metodo_pago']   ?? '');
$comprobante = trim($_POST['comprobante']   ?? '');

if (!$id_proyecto || !$tipo_pago || !$fecha_pago || !$metodo) {
    echo json_encode(['error' => true, 'mensaje' => 'Todos los campos son requeridos.']); exit;
}
if (!in_array($tipo_pago, ['Parcial', 'Saldo Final'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Tipo de pago no permitido.']); exit;
}
if ($monto <= 0) {
    echo json_encode(['error' => true, 'mensaje' => 'El monto debe ser mayor a 0.']); exit;
}
if (!in_array($metodo, ['Efectivo', 'Transferencia', 'Cheque'])) {
    echo json_encode(['error' => true, 'mensaje' => 'Método de pago inválido.']); exit;
}

$conn = getConexion();

// Saldo actual del proyecto
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

$monto_proyecto  = floatval($row['monto_proyecto']);
$total_pagado    = floatval($row['total_pagado']);
$saldo_pendiente = max(0, $monto_proyecto - $total_pagado);

if ($monto > $saldo_pendiente + 0.01) {
    echo json_encode([
        'error'   => true,
        'mensaje' => 'El monto ($' . number_format($monto, 2) . ') supera el saldo pendiente ($' . number_format($saldo_pendiente, 2) . ').'
    ]); exit;
}

if ($tipo_pago === 'Saldo Final') {
    $monto = $saldo_pendiente;
}

// Insertar pago
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

// Calcular nuevo saldo
$nuevo_saldo     = max(0, $saldo_pendiente - $monto);
$proyecto_pagado = ($nuevo_saldo <= 0.01);

// Facturación
$IVA_PCT          = 0.13;
$subtotal_factura = round($monto / (1 + $IVA_PCT), 2);
$iva_factura      = round($monto - $subtotal_factura, 2);
$id_usuario       = $_SESSION['id_usuario'];
$fecha_emision    = date('Y-m-d');
$anio             = date('Y');

$stmt_c = $conn->prepare("SELECT id_cliente FROM proyecto WHERE id_proyecto = ?");
$stmt_c->bind_param("i", $id_proyecto);
$stmt_c->execute();
$row_c = $stmt_c->get_result()->fetch_assoc();
$stmt_c->close();

if ($row_c) {
    $id_cliente = $row_c['id_cliente'];

    // Buscar factura Pendiente existente para el proyecto
    $stmt_exist = $conn->prepare(
        "SELECT id_factura FROM factura
         WHERE id_proyecto = ? AND estado = 'Pendiente'
         ORDER BY id_factura DESC LIMIT 1"
    );
    $stmt_exist->bind_param("i", $id_proyecto);
    $stmt_exist->execute();
    $row_exist = $stmt_exist->get_result()->fetch_assoc();
    $stmt_exist->close();

    if ($row_exist) {
        // Actualizar factura y cambiar estado si ya está pagada
        $nuevo_estado = $proyecto_pagado ? 'Pagada' : 'Pendiente';
        $stmt_upd = $conn->prepare(
            "UPDATE factura
             SET subtotal = subtotal + ?,
                 iva      = iva + ?,
                 total    = total + ?,
                 estado   = ?
             WHERE id_factura = ?"
        );
        $stmt_upd->bind_param("dddsi",
            $subtotal_factura, $iva_factura, $monto, $nuevo_estado, $row_exist['id_factura']
        );
        $stmt_upd->execute();
        $stmt_upd->close();
    } else {
        // Crear nueva factura
        $stmt_n = $conn->prepare("SELECT COUNT(*) AS cnt FROM factura WHERE YEAR(fecha_emision) = ?");
        $stmt_n->bind_param("i", $anio);
        $stmt_n->execute();
        $cnt = $stmt_n->get_result()->fetch_assoc()['cnt'];
        $stmt_n->close();

        $numero_factura = 'TN-' . $anio . '-' . str_pad($cnt + 1, 6, '0', STR_PAD_LEFT);
        $estado_factura = $proyecto_pagado ? 'Pagada' : 'Pendiente';

        $stmt_f = $conn->prepare(
            "INSERT INTO factura (numero_factura, id_cliente, id_proyecto, subtotal, iva, total,
                                  estado, generada_por, fecha_emision)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt_f->bind_param("siidddiss",
            $numero_factura, $id_cliente, $id_proyecto,
            $subtotal_factura, $iva_factura, $monto,
            $estado_factura, $id_usuario, $fecha_emision
        );
        $stmt_f->execute();
        $stmt_f->close();
    }
}

$conn->close();

$msg = $proyecto_pagado
    ? 'Pago registrado. Factura marcada como Pagada ✓'
    : ($tipo_pago === 'Parcial'
        ? 'Pago parcial registrado. Saldo pendiente: $' . number_format($nuevo_saldo, 2)
        : 'Saldo final registrado.');

echo json_encode([
    'error'           => false,
    'mensaje'         => $msg,
    'saldo_pendiente' => $nuevo_saldo,
    'pagado'          => $proyecto_pagado
]);
?>
