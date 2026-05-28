<?php

session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

// Validar sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'error' => true,
        'mensaje' => 'No autorizado'
    ]);
    exit;
}

$conn = getConexion();

// Acción recibida
$accion = $_GET['accion'] ?? 'listar';

// Leer JSON body
$input = json_decode(file_get_contents("php://input"), true);

// LISTAR
if ($accion === 'listar') {

    $sql = "SELECT 
            id_servicio,
            nombre,
            categoria,
            precio_base,
            descripcion,
            activo
            FROM servicio
            WHERE activo = 1
            ORDER BY id_servicio DESC";

    $res = $conn->query($sql);

    $datos = [];

    while ($row = $res->fetch_assoc()) {
        $datos[] = $row;
    }

    echo json_encode($datos);
    exit;
}

// CREAR
if ($accion === 'crear') {

    $nombre      = trim($input['nombre'] ?? '');
    $categoria   = trim($input['categoria'] ?? '');
    $precio = str_replace(',', '.', $input['precio_base'] ?? 0);
    $precio = floatval($precio);
    $descripcion = trim($input['descripcion'] ?? '');

    if ($nombre === '' || $categoria === '') {
        echo json_encode([
            'error' => true,
            'mensaje' => 'Nombre y categoría son obligatorios'
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO servicio
        (nombre, categoria, precio_base, descripcion, activo)
        VALUES (?, ?, ?, ?, 1)
    ");

    $stmt->bind_param("ssds", $nombre, $categoria, $precio, $descripcion);

    if ($stmt->execute()) {
        echo json_encode([
            'error' => false,
            'mensaje' => 'Servicio creado'
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'mensaje' => 'Error al crear'
        ]);
    }

    exit;
}

// EDITAR

if ($accion === 'editar') {

    $id          = intval($input['id_servicio'] ?? 0);
    $nombre      = trim($input['nombre'] ?? '');
    $categoria   = trim($input['categoria'] ?? '');
    $precio = str_replace(',', '.', $input['precio_base'] ?? 0);
    $precio = floatval($precio);
    $descripcion = trim($input['descripcion'] ?? '');

    if ($id <= 0) {
        echo json_encode([
            'error' => true,
            'mensaje' => 'ID inválido'
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE servicio
        SET nombre = ?, categoria = ?, precio_base = ?, descripcion = ?
        WHERE id_servicio = ?
    ");

    $stmt->bind_param("ssdsi", $nombre, $categoria, $precio, $descripcion, $id);

    if ($stmt->execute()) {
        echo json_encode([
            'error' => false,
            'mensaje' => 'Servicio actualizado'
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'mensaje' => 'Error al actualizar'
        ]);
    }

    exit;
}

// DESACTIVAR (SOFT DELETE)
if ($accion === 'desactivar') {

    $id = intval($input['id_servicio'] ?? 0);

    if ($id <= 0) {
        echo json_encode([
            'error' => true,
            'mensaje' => 'ID inválido'
        ]);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE servicio
        SET activo = 0
        WHERE id_servicio = ?
    ");

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode([
            'error' => false,
            'mensaje' => 'Servicio desactivado'
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'mensaje' => 'Error al desactivar'
        ]);
    }

    exit;
}

// ACCIÓN INVÁLIDA

echo json_encode([
    'error' => true,
    'mensaje' => 'Acción no válida'
]);
?>