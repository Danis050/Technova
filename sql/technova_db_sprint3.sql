-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 06-03-2026 a las 18:37:43
-- Actualizado Sprint 2 – T01, T06, T09 (16-04-2026)
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `technova_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL COMMENT 'FK → usuario con rol Cliente',
  `nombre_empresa` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `nit` varchar(30) DEFAULT NULL COMMENT 'Para facturación electrónica',
  `nrc` varchar(30) DEFAULT NULL COMMENT 'Para facturación electrónica',
  `registrado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de clientes de TechNova';

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `id_usuario`, `nombre_empresa`, `telefono`, `direccion`, `nit`, `nrc`, `registrado_en`) VALUES
(1, 5, 'Empresa Pérez S.A. de C.V.', '7890-1234', 'San Miguel, El Salvador', '0614-190185-101-6', '12345-6', '2026-03-05 21:11:07'),
(2, 6, 'Juan Ramirez', NULL, NULL, NULL, NULL, '2026-03-05 21:31:08'),
(3, 7, 'Juan Ramirez', NULL, NULL, NULL, NULL, '2026-03-06 10:22:38'),
(4, 10, 'juan perez perez', NULL, NULL, NULL, NULL, '2026-03-06 11:32:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_factura`
--

CREATE TABLE `detalle_factura` (
  `id_detalle` int(11) NOT NULL,
  `id_factura` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Líneas de detalle de cada factura';

--
-- Volcado de datos para la tabla `detalle_factura`
--

INSERT INTO `detalle_factura` (`id_detalle`, `id_factura`, `id_servicio`, `descripcion`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 'Desarrollo de Sistema de Facturación Web', 1, 2500.00, 2500.00),
(2, 1, 2, 'Diseño de identidad visual del sistema', 1, 700.00, 700.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `id_factura` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `numero_factura` varchar(50) NOT NULL COMMENT 'Correlativo electrónico único',
  `fecha_emision` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `iva` decimal(10,2) NOT NULL COMMENT 'IVA 13% El Salvador',
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Pendiente','Pagada','Anulada') NOT NULL DEFAULT 'Pendiente',
  `generada_por` int(11) NOT NULL COMMENT 'FK → usuario que genera la factura',
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Módulo de facturación electrónica obligatorio';

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`id_factura`, `id_proyecto`, `id_cliente`, `numero_factura`, `fecha_emision`, `subtotal`, `iva`, `total`, `estado`, `generada_por`, `creado_en`) VALUES
(1, 1, 1, 'FAC-2026-0001', '2026-02-14', 3200.00, 416.00, 3616.00, 'Pendiente', 1, '2026-03-05 21:11:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

CREATE TABLE `pago` (
  `id_pago` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `tipo_pago` enum('Anticipo','Parcial','Saldo Final') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL,
  `metodo_pago` enum('Efectivo','Transferencia','Cheque') NOT NULL,
  `comprobante` varchar(255) DEFAULT NULL COMMENT 'Número o ruta del comprobante',
  `registrado_por` int(11) NOT NULL COMMENT 'FK → usuario que registra el pago',
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Control de pagos y anticipos por proyecto';

--
-- Volcado de datos para la tabla `pago`
--

INSERT INTO `pago` (`id_pago`, `id_proyecto`, `tipo_pago`, `monto`, `fecha_pago`, `metodo_pago`, `comprobante`, `registrado_por`, `creado_en`) VALUES
(1, 1, 'Anticipo', 1600.00, '2026-02-14', 'Transferencia', 'TRF-2026-0001', 1, '2026-03-05 21:11:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto`
-- Sprint 2 – T01 | Crear tabla `proyecto` en MySQL
-- Campos: id_proyecto PK AUTO_INCREMENT, nombre, descripcion,
--         fecha_inicio, fecha_fin, estado ENUM('Activo','Pausado','Cerrado'),
--         id_cliente FK → cliente.id_cliente, creado_en TIMESTAMP
-- Nota: reemplaza estructura anterior (estados Pendiente/En Proceso/
--       Completado/Cancelado y campo anticipo_pagado) por la definida en T01.
-- --------------------------------------------------------

CREATE TABLE `proyecto` (
  `id_proyecto`  int(11)      NOT NULL AUTO_INCREMENT                COMMENT 'PK – identificador único del proyecto',
  `nombre`       varchar(150) NOT NULL                               COMMENT 'Nombre descriptivo del proyecto',
  `descripcion`  text         DEFAULT NULL                           COMMENT 'Detalle ampliado del alcance',
  `fecha_inicio` date         NOT NULL                               COMMENT 'Fecha en que arranca el proyecto',
  `fecha_fin`    date         DEFAULT NULL                           COMMENT 'Fecha estimada de entrega (T01)',
  `estado`       enum('Activo','Pausado','Cerrado') NOT NULL
                              DEFAULT 'Activo'                       COMMENT 'Estado del ciclo de vida (T01)',
  `id_cliente`   int(11)      NOT NULL                               COMMENT 'FK → cliente.id_cliente (T01 / T06)',
  `creado_en`    timestamp    NOT NULL DEFAULT current_timestamp()   COMMENT 'Registro automático de creación (T01)',
  PRIMARY KEY (`id_proyecto`)  -- Fix #1075: AUTO_INCREMENT requiere PK declarada en el CREATE TABLE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Proyectos gestionados por TechNova – Sprint 2 T01';

--
-- Volcado de datos para la tabla `proyecto`
--

INSERT INTO `proyecto` (`id_proyecto`, `nombre`, `descripcion`, `fecha_inicio`, `fecha_fin`, `estado`, `id_cliente`, `creado_en`) VALUES
(1, 'Sistema de Facturación Web', 'Desarrollo de sistema de facturación electrónica para la empresa', '2026-02-14', '2026-05-14', 'Activo', 1, '2026-03-05 21:11:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto_servicio`
--

CREATE TABLE `proyecto_servicio` (
  `id_proyecto` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_acordado` decimal(10,2) NOT NULL COMMENT 'Precio pactado puede diferir del precio base'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relación N:M entre proyectos y servicios';

--
-- Volcado de datos para la tabla `proyecto_servicio`
--

INSERT INTO `proyecto_servicio` (`id_proyecto`, `id_servicio`, `cantidad`, `precio_acordado`) VALUES
(1, 1, 1, 2500.00),
(1, 2, 1, 700.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte`
--

CREATE TABLE `reporte` (
  `id_reporte` int(11) NOT NULL,
  `tipo` enum('Pagos','Proyectos','Facturación') NOT NULL COMMENT 'Mínimo 3 tipos de reporte requeridos',
  `generado_por` int(11) NOT NULL,
  `fecha_generacion` datetime NOT NULL DEFAULT current_timestamp(),
  `parametros` text DEFAULT NULL COMMENT 'Filtros usados, almacenados en JSON',
  `archivo_url` varchar(255) DEFAULT NULL COMMENT 'Ruta del reporte exportado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de los 3 reportes administrativos obligatorios';

--
-- Volcado de datos para la tabla `reporte`
--

INSERT INTO `reporte` (`id_reporte`, `tipo`, `generado_por`, `fecha_generacion`, `parametros`, `archivo_url`) VALUES
(1, 'Proyectos', 1, '2026-03-05 21:11:07', '{"estado":"En Proceso","fecha_desde":"2026-01-01"}', NULL),
(2, 'Pagos', 1, '2026-03-05 21:11:07', '{"tipo_pago":"Anticipo","mes":"02","anio":"2026"}', NULL),
(3, 'Facturación', 1, '2026-03-05 21:11:07', '{"estado":"Pendiente","mes":"02","anio":"2026"}', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio`
-- Sprint 2 – T09 | Crear tabla `servicio` en MySQL
-- Campos agregados respecto a versión anterior:
--   · categoria VARCHAR(80)  – clasificación del servicio
--   · creado_en TIMESTAMP    – registro automático de alta
-- Campos conservados: id_servicio PK, nombre, descripcion,
--                     precio_base DECIMAL(10,2), activo TINYINT(1) DEFAULT 1
-- --------------------------------------------------------

CREATE TABLE `servicio` (
  `id_servicio`  int(11)       NOT NULL AUTO_INCREMENT              COMMENT 'PK – identificador único del servicio',
  `nombre`       varchar(100)  NOT NULL                             COMMENT 'Nombre comercial del servicio',
  `descripcion`  text          DEFAULT NULL                         COMMENT 'Descripción detallada',
  `categoria`    varchar(80)   DEFAULT NULL                         COMMENT 'Clasificación del servicio (T09)',
  `precio_base`  decimal(10,2) NOT NULL                             COMMENT 'Precio de referencia antes de negociación',
  `activo`       tinyint(1)    NOT NULL DEFAULT 1                   COMMENT '1=disponible, 0=descontinuado (T09)',
  `creado_en`    timestamp     NOT NULL DEFAULT current_timestamp() COMMENT 'Registro automático de alta (T09)',
  PRIMARY KEY (`id_servicio`)  -- Fix #1075: AUTO_INCREMENT requiere PK declarada en el CREATE TABLE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de servicios ofrecidos por TechNova – Sprint 2 T09';

--
-- Volcado de datos para la tabla `servicio`
--

INSERT INTO `servicio` (`id_servicio`, `nombre`, `descripcion`, `categoria`, `precio_base`, `activo`, `creado_en`) VALUES
(1, 'Desarrollo de Software a Medida', 'Sistema web personalizado según requerimientos del cliente', 'Desarrollo', 2500.00, 1, '2026-03-05 21:11:07'),
(2, 'Diseño Gráfico', 'Diseño de identidad visual, logos y material publicitario', 'Diseño', 800.00, 1, '2026-03-05 21:11:07'),
(3, 'Venta de Demo (Software Base)', 'Software base adaptable a distintos rubros', 'Desarrollo', 1200.00, 1, '2026-03-05 21:11:07'),
(4, 'Curso de JavaScript', 'Curso presencial/virtual de JavaScript desde cero', 'Capacitación', 350.00, 1, '2026-03-05 21:11:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'Contraseña encriptada con bcrypt',
  `rol` enum('Administrador','Empleado','Cliente') DEFAULT 'Cliente',
  `puesto` varchar(50) DEFAULT '',
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestión de acceso con roles diferenciados';

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `email`, `password_hash`, `rol`, `puesto`, `estado`, `creado_en`) VALUES
(1, 'Carlos', 'Lobos Soriano', 'carlos.lobos@technova.com', '$2b$10$ejemplo_hash_admin', 'Administrador', '', 1, '2026-03-05 21:11:07'),
(2, 'Misael', 'Juárez Reyes', 'misael.juarez@technova.com', '$2b$10$ejemplo_hash_dev1', '', '', 1, '2026-03-05 21:11:07'),
(3, 'Kevin', 'Benavides Ruiz', 'kevin.benavides@technova.com', '$2b$10$ejemplo_hash_dev2', '', '', 1, '2026-03-05 21:11:07'),
(4, 'Luisa', 'Alvarez Tejada', 'luisa.alvarez@technova.com', '$2b$10$ejemplo_hash_dis', '', '', 1, '2026-03-05 21:11:07'),
(5, 'Juan', 'Pérez García', 'juan.perez@cliente.com', '$2b$10$ejemplo_hash_cli', 'Cliente', '', 1, '2026-03-05 21:11:07'),
(6, 'Juan', 'Ramirez', 'jure@gmail.com', '$2y$10$5f2QdQ1feQUeZ8fEBxXFe.KOGpIjcYN8oAsSscVzLKhSlda.qWCxm', 'Administrador', '', 1, '2026-03-05 21:31:08'),
(7, 'Juan', 'Ramirez', 'bidto@gmail.com', '$2y$10$gQ9OUDDqL59QvTgJf4ZuN.69j6bbMI45ehQrkZcjuJxpTIgYmjtzu', 'Cliente', '', 1, '2026-03-06 10:22:38'),
(8, 'juan', 'perez', 'juanpere@gmail.com', '$2y$10$WZ3lEyzJakvFm4G15aG8BuRg4BmuFpADVqfhaHWVq65mL0GRwDAmi', 'Administrador', '', 1, '2026-03-06 11:30:10'),
(9, 'ju', 'perez', 'perez@gmail.com', '$2y$10$7Fjt/dvHYCPFo.oQYZR4wO2tDDLJJzdeWfbaOtb2iVEpbLM7kk/dS', 'Empleado', 'Diseñador', 1, '2026-03-06 11:31:13'),
(10, 'juan perez', 'perez', 'ysya@gmail.com', '$2y$10$NkrkvzPRG0BBQ/5xoCV0Le8vVD2vn/YjMBujl9nTns05Pp620MOjK', 'Cliente', '', 1, '2026-03-06 11:32:01');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_facturas_detalle`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_facturas_detalle` (
`numero_factura` varchar(50)
,`fecha_emision` date
,`subtotal` decimal(10,2)
,`iva` decimal(10,2)
,`total` decimal(10,2)
,`estado_factura` enum('Pendiente','Pagada','Anulada')
,`cliente` varchar(150)
,`proyecto` varchar(150)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_pagos_por_proyecto`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_pagos_por_proyecto` (
`id_proyecto` int(11)
,`proyecto` varchar(150)
,`total_pagado` decimal(32,2)
,`num_pagos` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_proyectos_clientes`
-- (Véase abajo para la vista actual)
-- Nota: actualizada en T01 – refleja nuevos campos fecha_fin y estado
--
CREATE TABLE `v_proyectos_clientes` (
`id_proyecto` int(11)
,`proyecto` varchar(150)
,`estado` enum('Activo','Pausado','Cerrado')
,`fecha_fin` date
,`cliente` varchar(150)
,`email_cliente` varchar(150)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_facturas_detalle`
--
DROP TABLE IF EXISTS `v_facturas_detalle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_facturas_detalle` AS
  SELECT `f`.`numero_factura`  AS `numero_factura`,
         `f`.`fecha_emision`   AS `fecha_emision`,
         `f`.`subtotal`        AS `subtotal`,
         `f`.`iva`             AS `iva`,
         `f`.`total`           AS `total`,
         `f`.`estado`          AS `estado_factura`,
         `c`.`nombre_empresa`  AS `cliente`,
         `pr`.`nombre`         AS `proyecto`
  FROM ((`factura` `f`
    JOIN `cliente` `c`  ON (`f`.`id_cliente`  = `c`.`id_cliente`))
    JOIN `proyecto` `pr` ON (`f`.`id_proyecto` = `pr`.`id_proyecto`));

-- --------------------------------------------------------

--
-- Estructura para la vista `v_pagos_por_proyecto`
--
DROP TABLE IF EXISTS `v_pagos_por_proyecto`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pagos_por_proyecto` AS
  SELECT `p`.`id_proyecto`    AS `id_proyecto`,
         `pr`.`nombre`        AS `proyecto`,
         SUM(`p`.`monto`)     AS `total_pagado`,
         COUNT(`p`.`id_pago`) AS `num_pagos`
  FROM (`pago` `p`
    JOIN `proyecto` `pr` ON (`p`.`id_proyecto` = `pr`.`id_proyecto`))
  GROUP BY `p`.`id_proyecto`, `pr`.`nombre`;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_proyectos_clientes`
-- Actualizada en T01: usa fecha_fin en lugar de fecha_entrega;
--                     elimina anticipo_pagado (campo removido en T01).
--
DROP TABLE IF EXISTS `v_proyectos_clientes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_proyectos_clientes` AS
  SELECT `p`.`id_proyecto`  AS `id_proyecto`,
         `p`.`nombre`       AS `proyecto`,
         `p`.`estado`       AS `estado`,
         `p`.`fecha_fin`    AS `fecha_fin`,
         `c`.`nombre_empresa` AS `cliente`,
         `u`.`email`        AS `email_cliente`
  FROM ((`proyecto` `p`
    JOIN `cliente` `c` ON (`p`.`id_cliente`  = `c`.`id_cliente`))
    JOIN `usuario` `u` ON (`c`.`id_usuario`  = `u`.`id_usuario`));

-- --------------------------------------------------------
--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `fk_cliente_usuario` (`id_usuario`);

--
-- Indices de la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalle_factura` (`id_factura`),
  ADD KEY `fk_detalle_servicio` (`id_servicio`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`id_factura`),
  ADD UNIQUE KEY `numero_factura` (`numero_factura`),
  ADD KEY `fk_factura_proyecto` (`id_proyecto`),
  ADD KEY `fk_factura_cliente` (`id_cliente`),
  ADD KEY `fk_factura_usuario` (`generada_por`);

--
-- Indices de la tabla `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `fk_pago_proyecto` (`id_proyecto`),
  ADD KEY `fk_pago_usuario` (`registrado_por`);

--
-- Indices de la tabla `proyecto`
-- Nota T01: se agrega idx_proyecto_estado para filtrado eficiente por estado.
-- Nota T06: la FK sobre id_cliente se gestiona mediante sp_add_fk_cliente
--           (IF NOT EXISTS) más abajo.
--
-- PRIMARY KEY ya declarada en el CREATE TABLE (fix #1075).
-- Aquí solo se agregan los índices secundarios.
ALTER TABLE `proyecto`
  ADD KEY `fk_proyecto_cliente` (`id_cliente`),
  ADD KEY `idx_proyecto_estado` (`estado`);

--
-- Indices de la tabla `proyecto_servicio`
--
ALTER TABLE `proyecto_servicio`
  ADD PRIMARY KEY (`id_proyecto`,`id_servicio`),
  ADD KEY `fk_ps_servicio` (`id_servicio`);

--
-- Indices de la tabla `reporte`
--
ALTER TABLE `reporte`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reporte_usuario` (`generado_por`);

--
-- Indices de la tabla `servicio`
-- Nota T09: índice primario; se puede agregar KEY por categoria si se requiere.
--
-- PRIMARY KEY ya declarada en el CREATE TABLE (fix #1075).
ALTER TABLE `servicio`
  ADD KEY `idx_servicio_categoria` (`categoria`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

-- --------------------------------------------------------
--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `id_factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pago`
--
ALTER TABLE `pago`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `proyecto`
-- Nota T01: campo id_proyecto definido con AUTO_INCREMENT.
--
ALTER TABLE `proyecto`
  MODIFY `id_proyecto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `reporte`
--
ALTER TABLE `reporte`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `servicio`
-- Nota T09: campo id_servicio definido con AUTO_INCREMENT.
--
ALTER TABLE `servicio`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

-- --------------------------------------------------------
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_cliente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD CONSTRAINT `fk_detalle_factura` FOREIGN KEY (`id_factura`) REFERENCES `factura` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `fk_factura_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_usuario` FOREIGN KEY (`generada_por`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `fk_pago_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pago_usuario` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `proyecto`
-- Nota T06: la FK fk_cliente se aplica mediante procedimiento con IF NOT EXISTS
--           (ver sección Sprint 2 – T06 más abajo).
--

--
-- Filtros para la tabla `proyecto_servicio`
--
ALTER TABLE `proyecto_servicio`
  ADD CONSTRAINT `fk_ps_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ps_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `reporte`
--
ALTER TABLE `reporte`
  ADD CONSTRAINT `fk_reporte_usuario` FOREIGN KEY (`generado_por`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto_estado_log`
-- Auditoría de cambios de estado en proyectos.
-- Los ENUMs se sincronizan con los estados definidos en T01.
--

CREATE TABLE `proyecto_estado_log` (
  `id_log`          int(11)      NOT NULL AUTO_INCREMENT,
  `id_proyecto`     int(11)      NOT NULL,
  `estado_anterior` enum('Activo','Pausado','Cerrado') NOT NULL,
  `estado_nuevo`    enum('Activo','Pausado','Cerrado') NOT NULL,
  `cambiado_por`    int(11)      NOT NULL COMMENT 'FK → usuario (Administrador) que realizó el cambio',
  `cambiado_en`     datetime     NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`)  -- Fix #1075: AUTO_INCREMENT requiere PK declarada en el CREATE TABLE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auditoría de cambios de estado en proyectos';

--
-- Índices para la tabla `proyecto_estado_log`
--
-- PRIMARY KEY ya declarada en el CREATE TABLE (fix #1075).
ALTER TABLE `proyecto_estado_log`
  ADD KEY `fk_log_proyecto` (`id_proyecto`),
  ADD KEY `fk_log_usuario`  (`cambiado_por`);

--
-- AUTO_INCREMENT de la tabla `proyecto_estado_log`
--
ALTER TABLE `proyecto_estado_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- Filtros para la tabla `proyecto_estado_log`
--
ALTER TABLE `proyecto_estado_log`
  ADD CONSTRAINT `fk_log_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_log_usuario`  FOREIGN KEY (`cambiado_por`) REFERENCES `usuario`  (`id_usuario`)  ON UPDATE CASCADE;

-- --------------------------------------------------------
-- Sprint 2 – T06 | Verificar/agregar FK id_cliente en tabla `proyecto`
--   ALTER TABLE proyecto ADD CONSTRAINT fk_cliente
--   FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
--
-- MariaDB no soporta ADD CONSTRAINT IF NOT EXISTS de forma nativa,
-- por lo que se usa un procedimiento temporal que consulta
-- information_schema antes de ejecutar el ALTER TABLE.
-- Esto evita el error errno 121 si ya existe una FK distinta
-- (ej. fk_proyecto_cliente) apuntando a la misma columna.
-- --------------------------------------------------------

DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_add_fk_cliente`$$

CREATE PROCEDURE `sp_add_fk_cliente`()
BEGIN
  -- Verifica si ya existe alguna FK sobre proyecto.id_cliente → cliente
  IF NOT EXISTS (
    SELECT 1
    FROM   information_schema.KEY_COLUMN_USAGE
    WHERE  TABLE_SCHEMA          = DATABASE()
      AND  TABLE_NAME            = 'proyecto'
      AND  COLUMN_NAME           = 'id_cliente'
      AND  REFERENCED_TABLE_NAME = 'cliente'
  ) THEN
    ALTER TABLE `proyecto`
      ADD CONSTRAINT `fk_cliente`
      FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`)
      ON UPDATE CASCADE;
  END IF;
END$$

DELIMITER ;

-- Ejecutar y limpiar el procedimiento temporal
CALL `sp_add_fk_cliente`();
DROP PROCEDURE IF EXISTS `sp_add_fk_cliente`;

-- --------------------------------------------------------
-- HU-16 | Reportar incidencia post-entrega
-- Dev: David A. Urias Blanco (U20240435)
-- Sprint 3 — TechNova Servicios Tecnológicos
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `incidencia` (
  `id_incidencia`  int(11)      NOT NULL AUTO_INCREMENT,
  `id_proyecto`    int(11)      NOT NULL COMMENT 'FK → proyecto finalizado',
  `id_usuario`     int(11)      NOT NULL COMMENT 'FK → cliente que reporta',
  `titulo`         varchar(150) NOT NULL,
  `descripcion`    text         NOT NULL,
  `prioridad`      enum('Baja','Media','Alta','Crítica') NOT NULL DEFAULT 'Media',
  `estado`         enum('Abierta','En Proceso','Resuelta','Cerrada') NOT NULL DEFAULT 'Abierta',
  `creado_en`      datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` datetime     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_incidencia`),
  KEY `fk_inc_proyecto` (`id_proyecto`),
  KEY `fk_inc_usuario`  (`id_usuario`),
  CONSTRAINT `fk_inc_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE,
  CONSTRAINT `fk_inc_usuario`  FOREIGN KEY (`id_usuario`)  REFERENCES `usuario`  (`id_usuario`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='HU-16 – Incidencias post-entrega reportadas por clientes';

-- --------------------------------------------------------

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
