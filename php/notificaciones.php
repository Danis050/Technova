<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// Protección de ruta: Validar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$id_usuario = intval($_SESSION['id_usuario']);
$conn = getConexion();

// Soporta recibir la acción por parámetro URL (GET) o cuerpo de formulario (POST)
$action = $_GET['action'] ?? $_POST['action'] ?? 'listar';

try {
    switch ($action) {
        
        // ---------------------------------------------------------
        // ACCIÓN: LISTAR NOTIFICACIONES Y CONTEO DE UNREADS
        // ---------------------------------------------------------
        case 'listar':
            // 1. Obtener las últimas 10 notificaciones del usuario actual
            $sql = "SELECT id, id_usuario, mensaje, url_referencia, leida, fecha_creacion 
                    FROM notificaciones 
                    WHERE id_usuario = ? 
                    ORDER BY fecha_creacion DESC 
                    LIMIT 10";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error al preparar listado: " . $conn->error);
            }
            $stmt->bind_param('i', $id_usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $notificaciones = [];
            while ($row = $result->fetch_assoc()) {
                // Casteo explícito del campo tinyint a entero para JS
                $row['leida'] = (int)$row['leida'];
                $notificaciones[] = $row;
            }
            $stmt->close();

            // 2. Obtener el conteo total de notificaciones sin leer para el badge
            $sql_count = "SELECT COUNT(*) AS no_leidas FROM notificaciones WHERE id_usuario = ? AND leida = 0";
            $stmt_count = $conn->prepare($sql_count);
            if (!$stmt_count) {
                throw new Exception("Error al preparar conteo: " . $conn->error);
            }
            $stmt_count->bind_param('i', $id_usuario);
            $stmt_count->execute();
            $res_count = $stmt_count->get_result()->fetch_assoc();
            $no_leidas = (int)($res_count['no_leidas'] ?? 0);
            $stmt_count->close();

            // Retornar datos exitosos
            echo json_encode([
                'error' => false,
                'notificaciones' => $notificaciones,
                'no_leidas' => $no_leidas
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ---------------------------------------------------------
        // ACCIÓN: MARCAR UNA NOTIFICACIÓN COMO LEÍDA
        // ---------------------------------------------------------
        case 'marcar_leida':
            // Soporta lectura si envías los parámetros vía raw JSON (fetch body) o POST clásico
            $data = json_decode(file_get_contents('php://input'), true);
            $id_notificacion = intval($data['id'] ?? $_POST['id'] ?? $_GET['id'] ?? 0);

            if ($id_notificacion <= 0) {
                echo json_encode(['error' => true, 'mensaje' => 'ID de notificación no válido']);
                exit;
            }

            // Seguridad crítica: El WHERE incluye id_usuario para asegurar pertenencia
            $sql = "UPDATE notificaciones SET leida = 1 WHERE id = ? AND id_usuario = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error al preparar marcar_leida: " . $conn->error);
            }
            $stmt->bind_param('ii', $id_notificacion, $id_usuario);
            $stmt->execute();
            $stmt->close();

            echo json_encode([
                'error' => false,
                'mensaje' => 'Notificación marcada como leída'
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ---------------------------------------------------------
        // ACCIÓN: MARCAR TODAS LAS NOTIFICACIONES COMO LEÍDAS
        // ---------------------------------------------------------
        case 'marcar_todas':
            // Seguridad: Solo afecta a las filas del id_usuario en sesión
            $sql = "UPDATE notificaciones SET leida = 1 WHERE id_usuario = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error al preparar marcar_todas: " . $conn->error);
            }
            $stmt->bind_param('i', $id_usuario);
            $stmt->execute();
            $stmt->close();

            echo json_encode([
                'error' => false,
                'mensaje' => 'Todas las notificaciones se marcaron como leídas'
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode(['error' => true, 'mensaje' => 'Acción solicitada no válida']);
            break;
    }

} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'Error en el servidor de notificaciones: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    $conn->close();
}
?>