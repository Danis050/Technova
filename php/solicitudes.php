<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

function responder($error, $mensaje = '', $extra = []) {
    echo json_encode(array_merge(['error' => $error, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    responder(true, 'No autorizado');
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'listar';
$estadoDb = [
    'Abierta' => 'Pendiente',
    'En Proceso' => 'En Revisión',
    'Resuelta' => 'Atendida',
    'Cerrada' => 'Cerrada',
    'Pendiente' => 'Pendiente',
    'En Revisión' => 'En Revisión',
    'Atendida' => 'Atendida'
];

try {
    $conn = getConexion();

    if ($action === 'actualizar_estado') {
        if (!in_array($_SESSION['rol'], ['Administrador', 'Empleado'], true)) {
            responder(true, 'Sin permisos para gestionar solicitudes');
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) $data = $_POST;

        $id = intval($data['id_solicitud'] ?? $data['id'] ?? 0);
        $estadoFront = trim($data['estado'] ?? '');
        $estado = $estadoDb[$estadoFront] ?? $estadoFront;

        if ($id <= 0 || !in_array($estado, ['Pendiente', 'En Revisión', 'Atendida', 'Cerrada'], true)) {
            responder(true, 'Solicitud o estado inválido');
        }

        $stmt = $conn->prepare("UPDATE solicitud SET estado = ? WHERE id_solicitud = ?");
        $stmt->bind_param('si', $estado, $id);
        if (!$stmt->execute()) {
            responder(true, 'Error al actualizar la solicitud');
        }
        $stmt->close();
        $conn->close();
        responder(false, 'Solicitud actualizada correctamente', ['estado' => $estado]);
    }

    $where = '';
    $params = [];
    $types = '';

    if ($_SESSION['rol'] === 'Cliente') {
        $where = 'WHERE s.id_usuario = ?';
        $params[] = (int)$_SESSION['id_usuario'];
        $types .= 'i';
    } elseif (!in_array($_SESSION['rol'], ['Administrador', 'Empleado'], true)) {
        responder(true, 'Sin permisos para ver solicitudes');
    }

    $sql = "
        SELECT
            s.id_solicitud,
            s.asunto,
            s.descripcion,
            s.estado,
            s.creado_en,
            p.nombre AS proyecto,
            COALESCE(NULLIF(c.nombre_empresa, ''), CONCAT(u.nombre, ' ', u.apellido)) AS cliente
        FROM solicitud s
        INNER JOIN proyecto p ON p.id_proyecto = s.id_proyecto
        INNER JOIN usuario u ON u.id_usuario = s.id_usuario
        LEFT JOIN cliente c ON c.id_usuario = u.id_usuario
        $where
        ORDER BY s.creado_en DESC, s.id_solicitud DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception($conn->error);
    }
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $estadoFront = [
        'Pendiente' => 'Abierta',
        'En Revisión' => 'En Proceso',
        'Atendida' => 'Resuelta',
        'Cerrada' => 'Cerrada'
    ];

    $solicitudes = [];
    while ($row = $result->fetch_assoc()) {
        $dias = max(0, floor((time() - strtotime($row['creado_en'])) / 86400));
        $estado = $estadoFront[$row['estado']] ?? $row['estado'];
        $prioridad = ($row['estado'] === 'Pendiente' && $dias >= 7) ? 'Alta' : 'Media';
        $solicitudes[] = [
            'id_num' => (int)$row['id_solicitud'],
            'id' => 'SOL-' . str_pad((string)$row['id_solicitud'], 3, '0', STR_PAD_LEFT),
            'cliente' => $row['cliente'],
            'asunto' => $row['asunto'],
            'proyecto' => $row['proyecto'],
            'prioridad' => $prioridad,
            'estado' => $estado,
            'asignado' => null,
            'fecha' => substr($row['creado_en'], 0, 10),
            'desc' => $row['descripcion']
        ];
    }

    $stmt->close();
    $conn->close();
    responder(false, '', ['solicitudes' => $solicitudes]);
} catch (Exception $e) {
    responder(true, 'Error al procesar solicitudes: ' . $e->getMessage());
}
?>
