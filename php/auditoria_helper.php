<?php
// ============================================================
// SCRUM-HU24-02 | HU-24: Registro de Auditoría
// Función reutilizable registrarAuditoria()
// Responsable : Carlos Lobos (U20220190)
// Sprint      : Sprint 4 · Semana 3 · TechNova · Equipo Aevum
// ⚠️ PRIORITARIA — debe existir antes de HU17-04 y HU24-03
// Ubicación   : php/auditoria_helper.php
// ============================================================

require_once __DIR__ . '/conexion.php';

/**
 * Registra una acción crítica en la tabla auditoria.
 *
 * @param string   $accion      Tipo de acción: login | logout | cambio_rol |
 *                              cambio_estado_proyecto | registro_pago |
 *                              crear_cliente | editar_cliente | eliminar_cliente
 * @param string   $entidad     Nombre de la tabla/entidad afectada (ej: 'usuario', 'proyecto')
 * @param int|null $id_entidad  PK del registro afectado (null si no aplica)
 * @param string   $descripcion Detalle legible de la acción realizada
 */
function registrarAuditoria(string $accion, string $entidad, ?int $id_entidad, string $descripcion): void
{
    try {
        $id_usuario     = $_SESSION['id_usuario'] ?? null;
        $nombre_usuario = $_SESSION['nombre']     ?? 'Sistema';
        $ip_origen      = $_SERVER['REMOTE_ADDR'] ?? '';

        $conn = getConexion();

        $stmt = $conn->prepare(
            "INSERT INTO auditoria
                (id_usuario, nombre_usuario, accion, entidad, id_entidad, descripcion, ip_origen)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'isssiss',
            $id_usuario,
            $nombre_usuario,
            $accion,
            $entidad,
            $id_entidad,
            $descripcion,
            $ip_origen
        );
        $stmt->execute();
        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        // Auditoría nunca debe interrumpir el flujo principal
        error_log('[auditoria_helper] Error al registrar: ' . $e->getMessage());
    }
}
