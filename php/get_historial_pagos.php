<?php
// ============================================================
// GET HISTORIAL PAGOS - TechNova
// ============================================================
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}

$conn = getConexion();

$pagos = [];
$r = $conn->query(
    "SELECT pg.id_pago, pg.tipo_pago, pg.monto, pg.fecha_pago,
            pg.metodo_pago, pg.comprobante, pg.creado_en,
            p.nombre AS proyecto,
            c.nombre_empresa,
            CONCAT(u.nombre, ' ', u.apellido) AS registrado_por
     FROM pago pg
     JOIN proyecto p ON pg.id_proyecto = p.id_proyecto
     JOIN cliente c ON p.id_cliente = c.id_cliente
     JOIN usuario u ON pg.registrado_por = u.id_usuario
     ORDER BY pg.creado_en DESC
     LIMIT 50"
);

while ($row = $r->fetch_assoc()) {
    $pagos[] = $row;
}

// Totales resumen
$r2 = $conn->query("SELECT 
    COALESCE(SUM(monto),0) AS total_general,
    COALESCE(SUM(CASE WHEN tipo_pago='Anticipo' THEN monto ELSE 0 END),0) AS total_anticipos,
    COALESCE(SUM(CASE WHEN tipo_pago='Parcial' THEN monto ELSE 0 END),0) AS total_parciales,
    COALESCE(SUM(CASE WHEN tipo_pago='Saldo Final' THEN monto ELSE 0 END),0) AS total_saldos,
    COUNT(*) AS num_pagos
    FROM pago");
$resumen = $r2->fetch_assoc();

$conn->close();
echo json_encode([
    'error'   => false,
    'usuario' => $_SESSION['nombre'],
    'rol'     => $_SESSION['rol'],
    'pagos'   => $pagos,
    'resumen' => $resumen
]);
?>