<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

$conn = getConexion();

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$sqlAuto = "
    UPDATE factura f
    JOIN proyecto p ON f.id_proyecto = p.id_proyecto
    JOIN (
        SELECT id_proyecto, SUM(total) AS suma_facturas
        FROM factura
        WHERE estado != 'Anulada'
        GROUP BY id_proyecto
    ) sf ON f.id_proyecto = sf.id_proyecto
    SET f.estado = 'Pagada'
    WHERE f.estado = 'Pendiente'
      AND sf.suma_facturas >= p.monto
";
$conn->query($sqlAuto);
$auto_actualizadas = $conn->affected_rows;

$estado = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$estados = ['Pendiente', 'Pagada', 'Anulada'];

$where = "";
if ($estado && in_array($estado, $estados)) {
    $where = "WHERE f.estado = ?";
}

$sql = "
SELECT
    f.id_factura,
    f.numero_factura,
    f.fecha_emision,
    f.subtotal,
    f.iva,
    f.total,
    f.estado,
    f.creado_en,
    c.nombre_empresa,
    pr.nombre AS nombre_proyecto,
    COALESCE(pg.total_pagado, 0) AS total_pagado,
    CONCAT(u.nombre, ' ', u.apellido) AS generado_por
FROM factura f
JOIN cliente  c  ON f.id_cliente  = c.id_cliente
JOIN proyecto pr ON f.id_proyecto = pr.id_proyecto
LEFT JOIN (
    SELECT id_proyecto, SUM(monto) AS total_pagado
    FROM pago
    GROUP BY id_proyecto
) pg ON f.id_proyecto = pg.id_proyecto
LEFT JOIN usuario u ON f.generada_por = u.id_usuario
$where
ORDER BY f.creado_en DESC
";

if ($where) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $estado);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$facturas = [];
while ($row = $result->fetch_assoc()) {
    $facturas[] = $row;
}

$sqlStats = "
SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
    SUM(CASE WHEN estado = 'Pagada' THEN 1 ELSE 0 END) AS pagadas,
    SUM(CASE WHEN estado = 'Anulada' THEN 1 ELSE 0 END) AS anuladas,
    IFNULL(SUM(CASE WHEN estado != 'Anulada' THEN total END), 0) AS monto_total
FROM factura
";

$resStats = $conn->query($sqlStats);
$stats = $resStats->fetch_assoc();

echo json_encode([
    'error' => false,
    'facturas' => $facturas,
    'stats' => $stats,
    'auto_actualizadas' => (int)$auto_actualizadas,
    'usuario' => $_SESSION['nombre'],
    'rol' => $_SESSION['rol']
]);