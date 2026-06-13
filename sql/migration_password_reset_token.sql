-- ============================================================
-- MIGRACIÓN: Tabla password_reset_token
-- Ejecutar en bases de datos existentes para habilitar
-- la recuperación de contraseña desde login.
-- ============================================================

-- Eliminar tabla antigua (esquema incorrecto)
DROP TABLE IF EXISTS `password_reset_token`;

-- Crear tabla con columnas que espera el PHP
CREATE TABLE `password_reset_token` (
  `id_token`   int(11)      NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11)      NOT NULL,
  `token_hash` varchar(64)  NOT NULL,
  `expira_en`  datetime     NOT NULL,
  `creado_en`  datetime     NOT NULL DEFAULT current_timestamp(),
  `usado_en`   datetime     DEFAULT NULL,
  PRIMARY KEY (`id_token`),
  KEY `idx_id_usuario` (`id_usuario`),
  CONSTRAINT `fk_prt_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
