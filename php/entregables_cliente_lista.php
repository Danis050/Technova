<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$conn = getConexion();
$action = isset($_GET['action']) ? $_GET['action'] : 'entregables';

try {
    if ($action === 'proyectos') {
        $sql = "SELECT p.id_proyecto, p.nombre 
                FROM proyecto p 
                JOIN cliente c ON p.id_cliente = c.id_cliente 
                WHERE c.id_usuario = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $proyectos = [];
        while ($row = $result->fetch_assoc()) {
            $proyectos[] = $row;
        }
        echo json_encode(['proyectos' => $proyectos]);
        exit;
    }

    $id_proyecto_filtro = isset($_GET['id_proyecto']) ? intval($_GET['id_proyecto']) : 0;

    $sql = "SELECT e.id_entregable, e.id_proyecto, e.nombre, e.descripcion, e.estado, e.fecha_entrega, p.nombre as nombre_proyecto 
            FROM entregable e
            JOIN proyecto p ON e.id_proyecto = p.id_proyecto
            JOIN cliente c ON p.id_cliente = c.id_cliente
            WHERE c.id_usuario = ?";

    if ($id_proyecto_filtro > 0) {
        $sql .= " AND p.id_proyecto = ?";
    }

    $stmt = $conn->prepare($sql);
    if ($id_proyecto_filtro > 0) {
        $stmt->bind_param('ii', $id_usuario, $id_proyecto_filtro);
    } else {
        $stmt->bind_param('i', $id_usuario);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $entregables = [];
    while ($row = $result->fetch_assoc()) {
        $entregables[] = $row;
    }
    
    echo json_encode(['error' => false, 'entregables' => $entregables]);

} catch (Exception $e) {
    echo json_encode(['error' => true, 'mensaje' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>