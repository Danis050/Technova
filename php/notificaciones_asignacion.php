<?php
// HU-18 | Notificacion visual al miembro asignado al iniciar sesion
// Dev: Danis I. Vides Aparicio

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']);
    exit;
}

$conn = getConexion();
$id_usuario = (int) $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) {
        $ids = [$ids];
    }

    $ids = array_values(array_filter(array_map('intval', $ids), function ($id) {
        return $id > 0;
    }));

    if (count($ids) === 0) {
        echo json_encode(['error' => false, 'mensaje' => 'Sin notificaciones por actualizar']);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE asignacion_notificacion
         SET leida = 1, leida_en = NOW()
         WHERE id_usuario = ? AND id_notificacion = ?"
    );

    foreach ($ids as $id_notificacion) {
        $stmt->bind_param("ii", $id_usuario, $id_notificacion);
        $stmt->execute();
    }
    $stmt->close();

    echo json_encode(['error' => false, 'mensaje' => 'Notificaciones marcadas como leidas']);
    $conn->close();
    exit;
}

$stmt = $conn->prepare(
    "SELECT n.id_notificacion, n.id_proyecto, n.mensaje, n.creado_en, p.nombre AS proyecto
     FROM asignacion_notificacion n
     INNER JOIN proyecto p ON p.id_proyecto = n.id_proyecto
     WHERE n.id_usuario = ? AND n.leida = 0
     ORDER BY n.creado_en DESC"
);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();

$notificaciones = [];
while ($row = $res->fetch_assoc()) {
    $notificaciones[] = [
        'id' => (int) $row['id_notificacion'],
        'idProyecto' => (int) $row['id_proyecto'],
        'proyecto' => $row['proyecto'],
        'mensaje' => $row['mensaje'],
        'creadoEn' => $row['creado_en']
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['error' => false, 'notificaciones' => $notificaciones], JSON_UNESCAPED_UNICODE);
?>
