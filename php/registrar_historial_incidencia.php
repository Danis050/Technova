<?php
// HU-28 | Registro automatico del historial de cambios de soporte
// Dev: Danis I. Vides Aparicio

function registrar_historial_incidencia(mysqli $conn, int $id_incidencia, ?string $estado_anterior, string $estado_nuevo, int $id_usuario, ?string $comentario = null): void
{
    $stmt = $conn->prepare(
        "INSERT INTO incidencia_estado_log
            (id_incidencia, estado_anterior, estado_nuevo, comentario, cambiado_por, cambiado_en)
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    if (!$stmt) {
        throw new Exception('No se pudo preparar el registro de historial.');
    }

    $stmt->bind_param("isssi", $id_incidencia, $estado_anterior, $estado_nuevo, $comentario, $id_usuario);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new Exception('No se pudo guardar el historial: ' . $error);
    }

    $stmt->close();
}
?>
