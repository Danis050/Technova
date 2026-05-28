-- Sprint 4 - Tareas de Misael A. Juarez Reyes
-- Ejecutar despues del script base de TechNova.

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `entregable` (
  `id_entregable` int(11) NOT NULL AUTO_INCREMENT,
  `id_proyecto` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `id_responsable` int(11) NOT NULL,
  `fecha_estimada` date NOT NULL,
  `estado` enum('Pendiente','En desarrollo','Entregado') NOT NULL DEFAULT 'Pendiente',
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_entregable`),
  KEY `idx_entregable_proyecto` (`id_proyecto`),
  KEY `idx_entregable_responsable` (`id_responsable`),
  CONSTRAINT `fk_entregable_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_entregable_responsable` FOREIGN KEY (`id_responsable`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_token` (
  `id_token` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado_en` datetime DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_token`),
  UNIQUE KEY `uk_password_reset_token_hash` (`token_hash`),
  KEY `idx_password_reset_usuario` (`id_usuario`),
  CONSTRAINT `fk_password_reset_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `incidencia`
  MODIFY `prioridad` enum('Baja','Media','Alta','Critica','Crítica') NOT NULL DEFAULT 'Media',
  MODIFY `estado` enum('Pendiente','En atencion','En atención','Resuelto','Cerrado','Abierta','En Proceso','Resuelta','Cerrada') NOT NULL DEFAULT 'Pendiente';

ALTER TABLE `incidencia`
  ADD COLUMN IF NOT EXISTS `comentario_resolucion` text DEFAULT NULL AFTER `estado`,
  ADD COLUMN IF NOT EXISTS `actualizado_por` int(11) DEFAULT NULL AFTER `comentario_resolucion`,
  ADD COLUMN IF NOT EXISTS `notificacion_alta` tinyint(1) NOT NULL DEFAULT 0 AFTER `actualizado_por`;

ALTER TABLE `incidencia`
  ADD INDEX IF NOT EXISTS `idx_inc_actualizado_por` (`actualizado_por`);

CREATE TABLE IF NOT EXISTS `soporte_notificacion` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_incidencia` int(11) NOT NULL,
  `tipo` enum('Prioridad Alta') NOT NULL DEFAULT 'Prioridad Alta',
  `mensaje` varchar(255) NOT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notificacion`),
  KEY `idx_soporte_notificacion_incidencia` (`id_incidencia`),
  KEY `idx_soporte_notificacion_leida` (`leida`),
  CONSTRAINT `fk_soporte_notificacion_incidencia` FOREIGN KEY (`id_incidencia`) REFERENCES `incidencia` (`id_incidencia`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;
