<?php
// ============================================================
// mis_proyectos.php
// T17 | HU-10 | Sprint 2 – TechNova
// Responsable: Danis Ismael Vides Aparicio
// Descripción: Retorna JSON con los proyectos del cliente
//              logueado. Solo lectura. Valida rol Cliente.
// ============================================================

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'conexion.php';

// ── Validar sesión activa ──────────────────────────────────
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado. Inicia sesión.']);
    exit;
}

// ── Validar rol Cliente ────────────────────────────────────
if ($_SESSION['rol'] !== 'Cliente') {
    http_response_code(403);
    echo json_encode(['error' => true, 'mensaje' => 'Acceso denegado.']);
    exit;
}

$conn = getConexion();

// ── Obtener id_cliente desde id_usuario en sesión ─────────
$id_usuario = (int) $_SESSION['id_usuario'];

$stmtCliente = $conn->prepare(
    "SELECT id_cliente FROM cliente WHERE id_usuario = ? LIMIT 1"
);
$stmtCliente->bind_param("i", $id_usuario);
$stmtCliente->execute();
$resCliente = $stmtCliente->get_result();

if ($resCliente->num_rows === 0) {
    // Usuario con rol Cliente pero sin registro en tabla cliente
    echo json_encode([]);
    $stmtCliente->close();
    $conn->close();
    exit;
}

$id_cliente = (int) $resCliente->fetch_assoc()['id_cliente'];
$stmtCliente->close();

// ── Consultar proyectos del cliente ───────────────────────
$stmtProyectos = $conn->prepare(
    "SELECT 
        p.id_proyecto,
        p.nombre,
        p.descripcion,
        p.fecha_inicio,
        p.fecha_fin,
        p.estado,
        p.creado_en
     FROM proyecto p
     WHERE p.id_cliente = ?
     ORDER BY p.creado_en DESC"
);
$stmtProyectos->bind_param("i", $id_cliente);
$stmtProyectos->execute();
$resProyectos = $stmtProyectos->get_result();

// ── Construir array de proyectos ──────────────────────────
$proyectos = [];
while ($row = $resProyectos->fetch_assoc()) {
    $proyectos[] = [
        'id'          => (int) $row['id_proyecto'],
        'nombre'      => $row['nombre'],
        'descripcion' => $row['descripcion'],
        'fechaInicio' => $row['fecha_inicio'],
        'fechaFin'    => $row['fecha_fin'],
        'estado'      => $row['estado'],
        'creadoEn'    => $row['creado_en']
    ];
}

$stmtProyectos->close();
$conn->close();

// ── Retornar JSON (array vacío si no tiene proyectos) ─────
echo json_encode($proyectos, JSON_UNESCAPED_UNICODE);
?>
