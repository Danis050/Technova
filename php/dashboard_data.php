<?php
// ============================================================
// API DASHBOARD - TechNova
// ============================================================
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$conn = getConexion();

// Total clientes
$r1 = $conn->query("SELECT COUNT(*) AS total FROM cliente");
$total_clientes = $r1->fetch_assoc()['total'];

// Total proyectos
$r2 = $conn->query("SELECT COUNT(*) AS total FROM proyecto");
$total_proyectos = $r2->fetch_assoc()['total'];

// Total facturado
$r3 = $conn->query("SELECT COALESCE(SUM(total),0) AS total FROM factura WHERE estado != 'Anulada'");
$total_facturado = $r3->fetch_assoc()['total'];

// Total pagos recibidos
$r4 = $conn->query("SELECT COALESCE(SUM(monto),0) AS total FROM pago");
$total_pagos = $r4->fetch_assoc()['total'];

// Proyectos recientes
$proyectos = [];
$r5 = $conn->query("SELECT p.nombre, p.estado, p.fecha_entrega, c.nombre_empresa 
                    FROM proyecto p 
                    JOIN cliente c ON p.id_cliente = c.id_cliente 
                    ORDER BY p.creado_en DESC LIMIT 5");
while ($row = $r5->fetch_assoc()) {
    $proyectos[] = $row;
}

// Facturas recientes
$facturas = [];
$r6 = $conn->query("SELECT f.numero_factura, f.total, f.estado, f.fecha_emision, c.nombre_empresa 
                    FROM factura f 
                    JOIN cliente c ON f.id_cliente = c.id_cliente 
                    ORDER BY f.creado_en DESC LIMIT 5");
while ($row = $r6->fetch_assoc()) {
    $facturas[] = $row;
}

$conn->close();

echo json_encode([
    'error'           => false,
    'usuario'         => $_SESSION['nombre'],
    'rol'             => $_SESSION['rol'],
    'total_clientes'  => $total_clientes,
    'total_proyectos' => $total_proyectos,
    'total_facturado' => $total_facturado,
    'total_pagos'     => $total_pagos,
    'proyectos'       => $proyectos,
    'facturas'        => $facturas
]);
?>
