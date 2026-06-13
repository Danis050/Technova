<?php
// ============================================================
// SCRUM-HU25-03 | HU-25: Calificar Servicio Recibido
// Endpoint: calificar_servicio.php
// Responsable : David Alexander Urias Blanco (U20240435)
// Sprint      : Sprint 4 · Semana 3 · TechNova · Equipo Aevum
// Método      : POST
// Campos POST : id_proyecto (int), puntuacion (int 1-5),
//               comentario (string, opcional)
// ============================================================

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';
require_once 'notificaciones_helper.php';

// ------------------------------------------------------------
// 1. Validar sesión y rol Cliente
// ------------------------------------------------------------
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No hay sesión activa. Inicie sesión.']);
    exit;
}

if ($_SESSION['rol'] !== 'Cliente') {
    echo json_encode(['error' => true, 'mensaje' => 'Solo los clientes pueden calificar proyectos.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => true, 'mensaje' => 'Método no permitido. Use POST.']);
    exit;
}

// ------------------------------------------------------------
// 2. Recoger y validar parámetros
// ------------------------------------------------------------
$id_proyecto = isset($_POST['id_proyecto']) ? (int) $_POST['id_proyecto'] : 0;
$puntuacion  = isset($_POST['puntuacion'])  ? (int) $_POST['puntuacion']  : 0;
$comentario  = trim($_POST['comentario'] ?? '');

if ($id_proyecto <= 0) {
    echo json_encode(['error' => true, 'mensaje' => 'El campo id_proyecto es obligatorio.']);
    exit;
}

if ($puntuacion < 1 || $puntuacion > 5) {
    echo json_encode(['error' => true, 'mensaje' => 'La puntuación debe estar entre 1 y 5.']);
    exit;
}

$conn = getConexion();

// ------------------------------------------------------------
// 3. Obtener id_cliente a partir del id_usuario en sesión
// ------------------------------------------------------------
$stmtCliente = $conn->prepare(
    "SELECT id_cliente FROM cliente WHERE id_usuario = ? LIMIT 1"
);
$stmtCliente->bind_param('i', $_SESSION['id_usuario']);
$stmtCliente->execute();
$rowCliente = $stmtCliente->get_result()->fetch_assoc();
$stmtCliente->close();

if (!$rowCliente) {
    $conn->close();
    echo json_encode(['error' => true, 'mensaje' => 'No se encontró un perfil de cliente asociado a tu cuenta.']);
    exit;
}

$id_cliente = (int) $rowCliente['id_cliente'];

// ------------------------------------------------------------
// 4. Verificar que el proyecto exista y esté Completado
// ------------------------------------------------------------
$stmtProyecto = $conn->prepare(
    "SELECT estado, id_cliente FROM proyecto WHERE id_proyecto = ? LIMIT 1"
);
$stmtProyecto->bind_param('i', $id_proyecto);
$stmtProyecto->execute();
$proyecto = $stmtProyecto->get_result()->fetch_assoc();
$stmtProyecto->close();

if (!$proyecto) {
    $conn->close();
    echo json_encode(['error' => true, 'mensaje' => 'Proyecto no encontrado.']);
    exit;
}

$estadosPermitidos = ['Completado', 'Finalizado', 'Cerrado'];
if (!in_array($proyecto['estado'], $estadosPermitidos)) {
    $conn->close();
    echo json_encode(['error' => true, 'mensaje' => 'Solo puedes calificar proyectos completados.']);
    exit;
}

// Verificar que el proyecto pertenezca al cliente en sesión
if ((int) $proyecto['id_cliente'] !== $id_cliente) {
    $conn->close();
    echo json_encode(['error' => true, 'mensaje' => 'No tienes permiso para calificar este proyecto.']);
    exit;
}

// ------------------------------------------------------------
// 5. Verificar que no exista calificación previa
// ------------------------------------------------------------
$stmtExiste = $conn->prepare(
    "SELECT id FROM calificaciones WHERE id_proyecto = ? AND id_cliente = ? LIMIT 1"
);
$stmtExiste->bind_param('ii', $id_proyecto, $id_cliente);
$stmtExiste->execute();
$yaCalificado = $stmtExiste->get_result()->num_rows > 0;
$stmtExiste->close();

if ($yaCalificado) {
    $conn->close();
    echo json_encode(['error' => true, 'mensaje' => 'Ya calificaste este proyecto.']);
    exit;
}

// ------------------------------------------------------------
// 6. Insertar la calificación
// ------------------------------------------------------------
$stmtInsert = $conn->prepare(
    "INSERT INTO calificaciones (id_proyecto, id_cliente, puntuacion, comentario)
     VALUES (?, ?, ?, ?)"
);
$stmtInsert->bind_param('iiis', $id_proyecto, $id_cliente, $puntuacion, $comentario);

if (!$stmtInsert->execute()) {
    $stmtInsert->close();
    $conn->close();
    echo json_encode(['error' => true, 'mensaje' => 'Error al guardar la calificación: ' . $stmtInsert->error]);
    exit;
}

$stmtInsert->close();

$admins = $conn->query("SELECT id_usuario FROM usuario WHERE rol = 'Administrador' AND estado = 1");
while ($admin = $admins->fetch_assoc()) {
    crearNotificacion(
        (int)$admin['id_usuario'],
        'Nueva calificación recibida: ' . $puntuacion . ' estrellas',
        '../calificaciones.html'
    );
}
$conn->close();

echo json_encode([
    'error'   => false,
    'mensaje' => 'Calificación registrada exitosamente. ¡Gracias por tu opinión!'
]);
?>
