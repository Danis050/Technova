<?php
/**
 * validar_estado_proyecto.php  — HU-09 Tarea 3
 * Compatible con la conexión mysqli de TechNova (conexion.php)
 *
 * Uso en cualquier endpoint:
 *   require_once 'validar_estado_proyecto.php';
 *   validar_proyecto_activo($conn, $id_proyecto);
 */

function validar_proyecto_activo($conn, int $id_proyecto): void
{
    $stmt = $conn->prepare("SELECT estado FROM proyecto WHERE id_proyecto = ? LIMIT 1");
    $stmt->bind_param("i", $id_proyecto);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'Proyecto no encontrado.'
        ]);
        exit;
    }

    $proyecto = $resultado->fetch_assoc();

    if ($proyecto['estado'] !== 'Activo') {
        http_response_code(409);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'No se pueden realizar cambios: el proyecto está Cerrado.'
        ]);
        exit;
    }

    $stmt->close();
}
?>
