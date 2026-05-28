<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'No autorizado'
    ]);
    exit;
}

if ($_SESSION['rol'] !== 'Cliente') {
    echo json_encode([
        'error' => true,
        'mensaje' => 'Acceso denegado'
    ]);
    exit;
}

$conn = getConexion();

$id_usuario = (int) $_SESSION['id_usuario'];

/* =====================================================
   BUSCAR CLIENTE DEL USUARIO
===================================================== */
$stmtCliente = $conn->prepare("
    SELECT id_cliente
    FROM cliente
    WHERE id_usuario = ?
    LIMIT 1
");

if (!$stmtCliente) {
    echo json_encode([
        'error' => true,
        'mensaje' => $conn->error
    ]);
    exit;
}

$stmtCliente->bind_param("i", $id_usuario);
$stmtCliente->execute();

$resCliente = $stmtCliente->get_result();

if ($resCliente->num_rows == 0) {
    echo json_encode([]);
    exit;
}

$fila = $resCliente->fetch_assoc();
$id_cliente = (int)$fila['id_cliente'];

$stmtCliente->close();

/* =====================================================
   PROYECTOS DEL CLIENTE
===================================================== */
$stmtProyectos = $conn->prepare("
SELECT
    id_proyecto,
    nombre,
    descripcion,
    fecha_inicio,
    fecha_fin,
    estado,
    creado_en
FROM proyecto
WHERE id_cliente = ?
ORDER BY creado_en DESC
");
if (!$stmtProyectos) {
    echo json_encode([
        'error' => true,
        'mensaje' => $conn->error
    ]);
    exit;
}

$stmtProyectos->bind_param("i", $id_cliente);
$stmtProyectos->execute();

$res = $stmtProyectos->get_result();

$proyectos = [];

while ($row = $res->fetch_assoc()) {
    $proyectos[] = [
        'id'            => $row['id_proyecto'],
        'nombre'        => $row['nombre'],
        'fechaInicio'   => $row['fecha_inicio'],
        'fechaEntrega'  => $row['fecha_fin'],
        'fechaFin'      => $row['fecha_fin'],
        'estado'        => $row['estado']
    ];
}

$stmtProyectos->close();
$conn->close();

echo json_encode($proyectos, JSON_UNESCAPED_UNICODE);
?>
