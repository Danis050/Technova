-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 06-03-2026 a las 18:37:43
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
-- Structure de tabla para la tabla `factura`
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
--

CREATE TABLE `proyecto` (
  `id_proyecto` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `estado` enum('Pendiente','En Proceso','Completado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  `anticipo_pagado` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'RB-01: El proyecto inicia solo si anticipo=1',
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='RN: Proyecto no inicia sin anticipo registrado';

--
-- Volcado de datos para la tabla `proyecto`
--

INSERT INTO `proyecto` (`id_proyecto`, `id_cliente`, `nombre`, `descripcion`, `fecha_inicio`, `fecha_entrega`, `estado`, `anticipo_pagado`, `creado_en`) VALUES
(1, 1, 'Sistema de Facturación Web', 'Desarrollo de sistema de facturación electrónica para la empresa', '2026-02-14', '2026-05-14', 'En Proceso', 1, '2026-03-05 21:11:07');

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
(1, 'Proyectos', 1, '2026-03-05 21:11:07', '{\"estado\":\"En Proceso\",\"fecha_desde\":\"2026-01-01\"}', NULL),
(2, 'Pagos', 1, '2026-03-05 21:11:07', '{\"tipo_pago\":\"Anticipo\",\"mes\":\"02\",\"anio\":\"2026\"}', NULL),
(3, 'Facturación', 1, '2026-03-05 21:11:07', '{\"estado\":\"Pendiente\",\"mes\":\"02\",\"anio\":\"2026\"}', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio`
--

CREATE TABLE `servicio` (
  `id_servicio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de servicios ofrecidos por TechNova';

--
-- Volcado de datos para la tabla `servicio`
--

INSERT INTO `servicio` (`id_servicio`, `nombre`, `descripcion`, `precio_base`, `activo`) VALUES
(1, 'Desarrollo de Software a Medida', 'Sistema web personalizado según requerimientos del cliente', 2500.00, 1),
(2, 'Diseño Gráfico', 'Diseño de identidad visual, logos y material publicitario', 800.00, 1),
(3, 'Venta de Demo (Software Base)', 'Software base adaptable a distintos rubros', 1200.00, 1),
(4, 'Curso de JavaScript', 'Curso presencial/virtual de JavaScript desde cero', 350.00, 1);

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
--
CREATE TABLE `v_proyectos_clientes` (
`id_proyecto` int(11)
,`proyecto` varchar(150)
,`estado` enum('Pendiente','En Proceso','Completado','Cancelado')
,`fecha_entrega` date
,`anticipo_pagado` tinyint(1)
,`cliente` varchar(150)
,`email_cliente` varchar(150)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_facturas_detalle`
--
DROP TABLE IF EXISTS `v_facturas_detalle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_facturas_detalle`  AS SELECT `f`.`numero_factura` AS `numero_factura`, `f`.`fecha_emision` AS `fecha_emision`, `f`.`subtotal` AS `subtotal`, `f`.`iva` AS `iva`, `f`.`total` AS `total`, `f`.`estado` AS `estado_factura`, `c`.`nombre_empresa` AS `cliente`, `pr`.`nombre` AS `proyecto` FROM ((`factura` `f` join `cliente` `c` on(`f`.`id_cliente` = `c`.`id_cliente`)) join `proyecto` `pr` on(`f`.`id_proyecto` = `pr`.`id_proyecto`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_pagos_por_proyecto`
--
DROP TABLE IF EXISTS `v_pagos_por_proyecto`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pagos_por_proyecto`  AS SELECT `p`.`id_proyecto` AS `id_proyecto`, `pr`.`nombre` AS `proyecto`, sum(`p`.`monto`) AS `total_pagado`, count(`p`.`id_pago`) AS `num_pagos` FROM (`pago` `p` join `proyecto` `pr` on(`p`.`id_proyecto` = `pr`.`id_proyecto`)) GROUP BY `p`.`id_proyecto`, `pr`.`nombre` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_proyectos_clientes`
--
DROP TABLE IF EXISTS `v_proyectos_clientes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_proyectos_clientes`  AS SELECT `p`.`id_proyecto` AS `id_proyecto`, `p`.`nombre` AS `proyecto`, `p`.`estado` AS `estado`, `p`.`fecha_entrega` AS `fecha_entrega`, `p`.`anticipo_pagado` AS `anticipo_pagado`, `c`.`nombre_empresa` AS `cliente`, `u`.`email` AS `email_cliente` FROM ((`proyecto` `p` join `cliente` `c` on(`p`.`id_cliente` = `c`.`id_cliente`)) join `usuario` `u` on(`c`.`id_usuario` = `u`.`id_usuario`)) ;

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
--
ALTER TABLE `proyecto`
  ADD PRIMARY KEY (`id_proyecto`),
  ADD KEY `fk_proyecto_cliente` (`id_cliente`);

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
--
ALTER TABLE `servicio`
  ADD PRIMARY KEY (`id_servicio`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

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
--
ALTER TABLE `servicio`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
--
ALTER TABLE `proyecto`
  ADD CONSTRAINT `fk_proyecto_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON UPDATE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
