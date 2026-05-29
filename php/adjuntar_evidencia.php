<?php
// ============================================================
// ADJUNTAR EVIDENCIA A INCIDENCIA DE SOPORTE — HU-26 | TechNova
// Desarrollador: David Alexander Urias Blanco (U20240435)
// Historia:  Como Cliente, quiero adjuntar evidencia (imagen o
//            documento) al reportar una incidencia post-entrega.
// Criterio:  Solo proyectos Finalizado/Completado/Cerrado.
//            El cliente recibe confirmación visual.
// ============================================================
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// ── Autorización: solo el Cliente ───────────────────────────
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => true, 'mensaje' => 'No autorizado']); exit;
}
if ($_SESSION['rol'] !== 'Cliente') {
    echo json_encode(['error' => true, 'mensaje' => 'Solo el Cliente puede adjuntar evidencia']); exit;
}

// ── Validar archivo recibido ─────────────────────────────────
if (!isset($_FILES['evidencia']) || $_FILES['evidencia']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => true, 'mensaje' => 'No se recibio ningun archivo o hubo un error en la subida.']); exit;
}

$id_incidencia = isset($_POST['id_incidencia']) ? (int)$_POST['id_incidencia'] : 0;
if ($id_incidencia <= 0) {
    echo json_encode(['error' => true, 'mensaje' => 'id_incidencia es obligatorio.']); exit;
}

// ── Tipos y tamaño permitidos ────────────────────────────────
$tipos_permitidos = [
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/gif'       => 'gif',
    'image/webp'      => 'webp',
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
];
$tamano_maximo = 5 * 1024 * 1024; // 5 MB

$archivo     = $_FILES['evidencia'];
$mime_type   = mime_content_type($archivo['tmp_name']);
$tamano      = $archivo['size'];
$nombre_orig = basename($archivo['name']);

if (!array_key_exists($mime_type, $tipos_permitidos)) {
    echo json_encode(['error' => true, 'mensaje' => 'Tipo de archivo no permitido. Se aceptan: JPG, PNG, GIF, WEBP, PDF, DOC, DOCX.']); exit;
}
if ($tamano > $tamano_maximo) {
    echo json_encode(['error' => true, 'mensaje' => 'El archivo supera el limite de 5 MB.']); exit;
}

try {
    $conn = getConexion();
    $id_usuario = (int)$_SESSION['id_usuario'];

    // ── 1. Verificar que la incidencia existe y pertenece al cliente ─
    $stmt_inc = $conn->prepare(
        "SELECT i.id_incidencia, i.estado, p.estado AS estado_proyecto
         FROM incidencia i
         INNER JOIN proyecto p ON p.id_proyecto = i.id_proyecto
         INNER JOIN cliente c  ON c.id_cliente  = p.id_cliente
         WHERE i.id_incidencia = ? AND c.id_usuario = ?
         LIMIT 1"
    );
    $stmt_inc->bind_param('ii', $id_incidencia, $id_usuario);
    $stmt_inc->execute();
    $res = $stmt_inc->get_result()->fetch_assoc();
    $stmt_inc->close();

    if (!$res) {
        echo json_encode(['error' => true, 'mensaje' => 'Incidencia no encontrada o no pertenece a tu cuenta.']); exit;
    }

    // ── 2. Verificar que el proyecto está Finalizado/Completado/Cerrado ─
    if (!in_array($res['estado_proyecto'], ['Finalizado', 'Completado', 'Cerrado'], true)) {
        echo json_encode(['error' => true, 'mensaje' => 'Solo se puede adjuntar evidencia en incidencias de proyectos finalizados. Estado actual: ' . $res['estado_proyecto']]); exit;
    }

    // ── 3. Verificar que la incidencia no está Cerrada ───────
    if ($res['estado'] === 'Cerrado') {
        echo json_encode(['error' => true, 'mensaje' => 'No se puede adjuntar evidencia a una incidencia cerrada.']); exit;
    }

    // ── 4. Crear directorio y guardar archivo ────────────────
    $directorio = __DIR__ . '/../uploads/evidencias/';
    if (!is_dir($directorio)) mkdir($directorio, 0755, true);

    $extension    = $tipos_permitidos[$mime_type];
    $nombre_unico = 'evidencia_' . $id_incidencia . '_' . time() . '_' . uniqid() . '.' . $extension;
    $ruta_destino = $directorio . $nombre_unico;
    $ruta_publica = 'uploads/evidencias/' . $nombre_unico;

    if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        throw new Exception('Error al guardar el archivo en el servidor.');
    }

    // ── 5. Registrar en BD ───────────────────────────────────
    $stmt_ev = $conn->prepare(
        "INSERT INTO evidencia_incidencia
             (id_incidencia, id_usuario, nombre_original, nombre_archivo,
              ruta_archivo, tipo_mime, tamano_bytes, fecha_subida)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt_ev->bind_param('iissssi', $id_incidencia, $id_usuario, $nombre_orig,
        $nombre_unico, $ruta_publica, $mime_type, $tamano);

    if (!$stmt_ev->execute()) {
        @unlink($ruta_destino);
        throw new Exception('Error al registrar la evidencia: ' . $stmt_ev->error);
    }
    $id_evidencia = $stmt_ev->insert_id;
    $stmt_ev->close();
    $conn->close();

    echo json_encode([
        'error'        => false,
        'mensaje'      => 'Evidencia adjuntada correctamente.',
        'id_evidencia' => $id_evidencia,
        'nombre'       => $nombre_orig,
        'ruta'         => $ruta_publica,
        'tamano_kb'    => round($tamano / 1024, 2)
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => true, 'mensaje' => $e->getMessage()]);
}
?>
