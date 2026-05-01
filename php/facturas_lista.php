<?php
session_start();
require 'conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

// ── AUTO-CAMBIO DE ESTADO ──────────────────────────────────────────────────
// Si la suma de pagos registrados para un proyecto >= total de la factura,
// la factura pasa automáticamente de 'Pendiente' a 'Pagada'.
$autoUpdate = $pdo->prepare("
    UPDATE factura f
    INNER JOIN (
        SELECT id_proyecto, SUM(monto) AS total_pagado
        FROM pago
        GROUP BY id_proyecto
    ) pg ON f.id_proyecto = pg.id_proyecto
    SET f.estado = 'Pagada'
    WHERE f.estado = 'Pendiente'
      AND pg.total_pagado >= f.total
");
$autoUpdate->execute();
$auto_actualizadas = $autoUpdate->rowCount();

// ── FILTRO POR ESTADO ──────────────────────────────────────────────────────
$estado  = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$estados = ['Pendiente', 'Pagada', 'Anulada'];
$where   = ($estado && in_array($estado, $estados)) ? "WHERE f.estado = :estado" : "";

// ── LISTADO COMPLETO CON DATOS CLAVE ──────────────────────────────────────
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
        pr.nombre       AS nombre_proyecto,
        COALESCE(pg.total_pagado, 0) AS total_pagado,
        CONCAT(u.nombre, ' ', u.apellido) AS generado_por
    FROM factura f
    JOIN cliente  c  ON f.id_cliente   = c.id_cliente
    JOIN proyecto pr ON f.id_proyecto  = pr.id_proyecto
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
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':estado' => $estado]);
} else {
    $stmt = $pdo->query($sql);
}
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── RESUMEN ESTADÍSTICO ────────────────────────────────────────────────────
$stats = $pdo->query("
    SELECT
        COUNT(*)                                                      AS total,
        SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END)        AS pendientes,
        SUM(CASE WHEN estado = 'Pagada'    THEN 1 ELSE 0 END)        AS pagadas,
        SUM(CASE WHEN estado = 'Anulada'   THEN 1 ELSE 0 END)        AS anuladas,
        COALESCE(SUM(CASE WHEN estado != 'Anulada' THEN total END),0) AS monto_total
    FROM factura
")->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'error'            => false,
    'facturas'         => $facturas,
    'stats'            => $stats,
    'auto_actualizadas'=> (int)$auto_actualizadas,
    'usuario'          => $_SESSION['nombre'],
    'rol'              => $_SESSION['rol']
]);
