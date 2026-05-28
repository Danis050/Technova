<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); 
    exit;
}

try {
    $conn = getConexion();

    // He cambiado pg.monto_pagado por pg.monto basándome en el error
    $sql = "SELECT 
                p.id_proyecto, 
                p.nombre, 
                p.monto AS monto_total,
                c.nombre_empresa,
                (SELECT COALESCE(SUM(pg.monto), 0) 
                 FROM pago pg 
                 WHERE pg.id_proyecto = p.id_proyecto) AS total_pagado
            FROM proyecto p
            JOIN cliente c ON p.id_cliente = c.id_cliente
            WHERE p.estado NOT IN ('Cancelado')";

    $r = $conn->query($sql);

    if (!$r) {
        throw new Exception("Error en la base de datos: " . $conn->error);
    }

    $proyectos = [];
    while ($row = $r->fetch_assoc()) {
        $monto_total = (float)$row['monto_total'];
        $total_pagado = (float)$row['total_pagado'];
        $saldo_pendiente = $monto_total - $total_pagado;

        // Solo incluimos proyectos que deban más de 1 centavo
        if ($saldo_pendiente > 0.01) {
            $proyectos[] = [
                'id_proyecto'     => $row['id_proyecto'],
                'nombre'          => $row['nombre'],
                'monto_total'     => $monto_total,
                'nombre_empresa'  => $row['nombre_empresa'],
                'total_pagado'    => $total_pagado,
                'saldo_pendiente' => $saldo_pendiente
            ];
        }
    }

    $conn->close();
    echo json_encode(['error' => false, 'proyectos' => $proyectos]);

} catch (Exception $e) {
    // Si sigue dando error de "Unknown column", el mensaje te dirá qué columna falla
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}