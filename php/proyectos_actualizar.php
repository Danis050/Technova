<?php
header('Content-Type: application/json');
require 'conexion.php';

$conn = getConexion();


$data = json_decode(file_get_contents("php://input"), true);

$id           = $data['id'];
$nombre       = $data['nombre'];
$idCliente    = $data['idCliente'];
$estado       = $data['estado'];
$fechaInicio  = $data['fechaInicio'];
$fechaFin     = $data['fechaFin'];
$monto        = $data['monto'] ?? 0;
$mapEstados = [
    'Pendiente' => 'Activo',
    'En Proceso' => 'Activo',
    'Completado' => 'Cerrado',
    'Cancelado' => 'Cerrado',
    'Finalizado' => 'Cerrado'
];
$estadoDb = $mapEstados[$estado] ?? $estado;
if (!in_array($estadoDb, ['Activo', 'Pausado', 'Cerrado'], true)) {
    $estadoDb = 'Activo';
}

$sql = "UPDATE proyecto SET 
            nombre = ?, 
            id_cliente = ?, 
            estado = ?, 
            fecha_inicio = ?, 
            fecha_fin = ?,
            monto = ?
        WHERE id_proyecto = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sisssdi", $nombre, $idCliente, $estadoDb, $fechaInicio, $fechaFin, $monto, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}
