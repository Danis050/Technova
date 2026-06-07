<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$conn = getConexion();

$estado_filtro = isset($_GET['estado']) ? trim($_GET['estado']) : '';
$cliente_filtro = isset($_GET['cliente']) ? intval($_GET['cliente']) : 0;

try {
    $where_clauses = [];
    $params = [];
    $types = "";

    if (!empty($estado_filtro)) {
        $where_clauses[] = "p.estado = ?";
        $params[] = $estado_filtro;
        $types .= "s";
    }

    if ($cliente_filtro > 0) {
        $where_clauses[] = "p.id_cliente = ?";
        $params[] = $cliente_filtro;
        $types .= "i";
    }

    $where_sql = "";
    if (count($where_clauses) > 0) {
        $where_sql = "WHERE " . implode(" AND ", $where_clauses);
    }

    $where_cards = ($cliente_filtro > 0) ? "WHERE id_cliente = ?" : "";
    $sql_cards = "SELECT estado, COUNT(*) as total FROM proyecto $where_cards GROUP BY estado";
    
    $stmt_cards = $conn->prepare($sql_cards);
    if ($cliente_filtro > 0) {
        $stmt_cards->bind_param("i", $cliente_filtro);
    }
    $stmt_cards->execute();
    $res_cards = $stmt_cards->get_result();

    $contadores = [
        'Total' => 0,
        'Pendiente' => 0,
        'En Proceso' => 0,
        'Completado' => 0,
        'Cancelado' => 0
    ];

    while ($row = $res_cards->fetch_assoc()) {
        $estado_db = $row['estado'];
        if (array_key_exists($estado_db, $contadores)) {
            $contadores[$estado_db] = (int)$row['total'];
        }
        $contadores['Total'] += (int)$row['total'];
    }

    $sql = "SELECT 
                p.id_proyecto, 
                p.nombre, 
                c.nombre_empresa AS cliente, 
                p.estado, 
                p.fecha_inicio, 
                p.fecha_fin, 
                COUNT(e.id_entregable) AS total_entregables 
            FROM proyecto p 
            LEFT JOIN cliente c ON p.id_cliente = c.id_cliente 
            LEFT JOIN entregable e ON e.id_proyecto = p.id_proyecto 
            $where_sql 
            GROUP BY p.id_proyecto";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => true, 'mensaje' => 'Error en preparación de consulta: ' . $conn->error]);
        exit;
    }

    if (count($params) > 0) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $proyectos = [];
    while ($row = $result->fetch_assoc()) {
        $proyectos[] = $row;
    }

    echo json_encode([
        'error' => false,
        'contadores' => $contadores,
        'proyectos' => $proyectos
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => true, 
        'mensaje' => 'Error en el servidor de reportes: ' . $e->getMessage()
    ]);
}
?>