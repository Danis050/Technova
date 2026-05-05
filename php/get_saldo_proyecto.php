<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['error' => true, 'mensaje' => 'ID de proyecto requerido']); exit;
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
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$row) {
    echo json_encode(['error' => true, 'mensaje' => 'Proyecto no encontrado']); exit;
}

$saldo = max(0, floatval($row['monto_proyecto']) - floatval($row['total_pagado']));

echo json_encode([
    'error'           => false,
    'monto_proyecto'  => floatval($row['monto_proyecto']),
    'total_pagado'    => floatval($row['total_pagado']),
    'saldo_pendiente' => $saldo
]);
?>
