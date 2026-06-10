<?php
// ============================================================
// SCRUM-HU20-04 | HU-20: Notificaciones Internas
// Función reutilizable crearNotificacion()
// Responsable : Carlos Lobos (U20220190)
// Sprint      : Sprint 4 · Semana 3 · TechNova · Equipo Aevum
// Ubicación   : php/notificaciones_helper.php
// ============================================================

require_once __DIR__ . '/conexion.php';

/**
 * Inserta una notificación interna para un usuario.
 *
 * @param int    $id_usuario  ID del usuario destinatario
 * @param string $mensaje     Texto de la notificación (máx 255 chars)
 * @param string $url_ref     URL de referencia al elemento afectado (opcional)
 */
function crearNotificacion(int $id_usuario, string $mensaje, string $url_ref = ''): void
{
    try {
        $conn = getConexion();

        $stmt = $conn->prepare(
            "INSERT INTO notificaciones (id_usuario, mensaje, url_referencia)
             VALUES (?, ?, ?)"
        );
        $stmt->bind_param('iss', $id_usuario, $mensaje, $url_ref);
        $stmt->execute();
        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        // Las notificaciones nunca deben interrumpir el flujo principal
        error_log('[notificaciones_helper] Error al crear notificación: ' . $e->getMessage());
    }
}
