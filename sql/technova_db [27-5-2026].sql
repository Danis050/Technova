-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 28, 2026 at 06:47 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `technova_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cliente`
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
-- Dumping data for table `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `id_usuario`, `nombre_empresa`, `telefono`, `direccion`, `nit`, `nrc`, `registrado_en`) VALUES
(1, 5, 'Empresa Pérez S.A. de C.V.', '7890-1234', 'San Miguel, El Salvador', '0614-190185-101-6', '12345-6', '2026-03-05 21:11:07'),
(2, 6, 'Juan Ramirez', NULL, NULL, NULL, NULL, '2026-03-05 21:31:08'),
(3, 7, 'Juan Ramirez', NULL, NULL, NULL, NULL, '2026-03-06 10:22:38'),
(4, 10, 'juan perez perez', NULL, NULL, NULL, NULL, '2026-03-06 11:32:01');

-- --------------------------------------------------------

--
-- Table structure for table `detalle_factura`
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
-- Dumping data for table `detalle_factura`
--

INSERT INTO `detalle_factura` (`id_detalle`, `id_factura`, `id_servicio`, `descripcion`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 'Desarrollo de Sistema de Facturación Web', 1, 2500.00, 2500.00),
(2, 1, 2, 'Diseño de identidad visual del sistema', 1, 700.00, 700.00);

-- --------------------------------------------------------

--
-- Table structure for table `entregable`
--

CREATE TABLE `entregable` (
  `id_entregable` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `estado` enum('Pendiente','En Proceso','Entregado','Retrasado') NOT NULL DEFAULT 'Pendiente',
  `archivo_url` varchar(255) DEFAULT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `factura`
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
-- Dumping data for table `factura`
--

INSERT INTO `factura` (`id_factura`, `id_proyecto`, `id_cliente`, `numero_factura`, `fecha_emision`, `subtotal`, `iva`, `total`, `estado`, `generada_por`, `creado_en`) VALUES
(1, 1, 1, 'FAC-2026-0001', '2026-02-14', 3200.00, 416.00, 3616.00, 'Pagada', 1, '2026-03-05 21:11:07'),
(2, 18, 1, 'TN-2026-000002', '2026-05-06', 44.25, 5.75, 50.00, 'Pagada', 6, '2026-05-05 18:57:12'),
(3, 18, 1, 'TN-2026-000003', '2026-05-06', 22.12, 2.88, 25.00, 'Pagada', 6, '2026-05-05 19:04:05'),
(4, 19, 1, 'TN-2026-000004', '2026-05-06', 11.06, 1.44, 12.50, 'Pagada', 6, '2026-05-05 19:04:44'),
(5, 20, 1, 'TN-2026-000005', '2026-05-06', 22.12, 2.88, 25.00, 'Pagada', 6, '2026-05-05 19:11:54'),
(6, 20, 1, 'TN-2026-000006', '2026-05-06', 22.12, 2.88, 25.00, 'Pagada', 6, '2026-05-05 19:12:42'),
(7, 21, 1, 'TN-2026-000007', '2026-05-08', 5.31, 0.69, 6.00, 'Pagada', 6, '2026-05-08 09:03:01'),
(8, 21, 1, 'TN-2026-000008', '2026-05-08', 5.31, 0.69, 6.00, 'Pagada', 6, '2026-05-08 09:03:15'),
(9, 22, 1, 'TN-2026-000009', '2026-05-08', 4.42, 0.58, 5.00, 'Pagada', 6, '2026-05-08 09:26:48'),
(10, 22, 1, 'TN-2026-000010', '2026-05-08', 4.42, 0.58, 5.00, 'Pagada', 6, '2026-05-08 09:27:14'),
(11, 13, 1, 'TN-2026-000011', '2026-05-08', 10.62, 1.38, 12.00, 'Pendiente', 6, '2026-05-08 09:31:28');

-- --------------------------------------------------------

--
-- Table structure for table `incidencia`
--

CREATE TABLE `incidencia` (
  `id_incidencia` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `id_usuario_reporta` int(11) NOT NULL COMMENT 'FK → usuario (Cliente o miembro del equipo)',
  `titulo` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `prioridad` enum('Baja','Media','Alta') NOT NULL DEFAULT 'Media',
  `estado` enum('Pendiente','En atención','Resuelto','Cerrado','Abierta','En Proceso','Resuelta','Cerrada') NOT NULL DEFAULT 'Pendiente',
  `comentario_resolucion` text DEFAULT NULL,
  `actualizado_por` int(11) DEFAULT NULL,
  `notificacion_alta` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pago`
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
-- Dumping data for table `pago`
--

INSERT INTO `pago` (`id_pago`, `id_proyecto`, `tipo_pago`, `monto`, `fecha_pago`, `metodo_pago`, `comprobante`, `registrado_por`, `creado_en`) VALUES
(1, 1, 'Anticipo', 1600.00, '2026-02-14', 'Transferencia', 'TRF-2026-0001', 1, '2026-03-05 21:11:07'),
(2, 12, 'Anticipo', 1.38, '2026-05-05', 'Efectivo', '', 6, '2026-05-04 19:47:44'),
(3, 13, 'Anticipo', 400.00, '2026-05-05', 'Efectivo', '', 6, '2026-05-04 19:57:25'),
(4, 14, 'Anticipo', 600.00, '2026-05-05', 'Efectivo', '', 6, '2026-05-04 20:18:41'),
(5, 11, 'Anticipo', 124.00, '2026-05-05', 'Efectivo', '', 6, '2026-05-04 23:43:53'),
(6, 14, 'Saldo Final', 700.00, '2026-05-05', 'Efectivo', '', 6, '2026-05-05 00:13:01'),
(7, 15, 'Anticipo', 6.00, '2026-05-05', 'Efectivo', '', 6, '2026-05-05 13:20:34'),
(8, 16, 'Anticipo', 5.00, '2026-05-05', 'Efectivo', '', 6, '2026-05-05 13:23:27'),
(9, 16, 'Saldo Final', 5.00, '2026-05-05', 'Efectivo', '', 6, '2026-05-05 13:30:54'),
(10, 17, 'Anticipo', 6.00, '2026-05-05', 'Efectivo', '', 6, '2026-05-05 14:14:59'),
(11, 18, 'Anticipo', 50.00, '2026-05-06', 'Efectivo', '', 6, '2026-05-05 18:57:12'),
(12, 18, 'Parcial', 25.00, '2026-05-06', 'Transferencia', '', 6, '2026-05-05 19:04:05'),
(13, 19, 'Anticipo', 12.50, '2026-05-06', 'Efectivo', '', 6, '2026-05-05 19:04:44'),
(14, 20, 'Anticipo', 25.00, '2026-05-06', 'Efectivo', '', 6, '2026-05-05 19:11:54'),
(15, 20, 'Parcial', 25.00, '2026-05-06', 'Efectivo', '', 6, '2026-05-05 19:12:41'),
(16, 21, 'Anticipo', 6.00, '2026-05-08', 'Efectivo', '', 6, '2026-05-08 09:03:01'),
(17, 21, 'Parcial', 6.00, '2026-05-08', 'Efectivo', '', 6, '2026-05-08 09:03:15'),
(18, 22, 'Anticipo', 5.00, '2026-05-08', 'Efectivo', '', 6, '2026-05-08 09:26:48'),
(19, 22, 'Saldo Final', 5.00, '2026-05-08', 'Efectivo', '', 6, '2026-05-08 09:27:14'),
(20, 13, 'Parcial', 12.00, '2026-05-08', 'Efectivo', '', 6, '2026-05-08 09:31:28');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_token`
--

CREATE TABLE `password_reset_token` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proyecto`
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
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `monto` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='RN: Proyecto no inicia sin anticipo registrado';

--
-- Dumping data for table `proyecto`
--

INSERT INTO `proyecto` (`id_proyecto`, `id_cliente`, `nombre`, `descripcion`, `fecha_inicio`, `fecha_entrega`, `estado`, `anticipo_pagado`, `creado_en`, `monto`) VALUES
(1, 4, 'Sistema de Facturación', 'Desarrollo de sistema de facturación electrónica para la empresa', '2026-02-04', '2026-05-20', 'Pendiente', 1, '2026-03-05 21:11:07', 0.00),
(6, 2, 'Sistema de Facturación Web', NULL, '2026-04-17', '2026-05-06', 'En Proceso', 0, '2026-04-17 10:19:44', 0.00),
(9, 2, 'Sistema de gestion de citas', NULL, '2026-05-03', '2026-05-10', 'En Proceso', 0, '2026-05-03 11:46:02', 0.00),
(10, 3, 'aAAAAAA', NULL, '2026-05-03', '2026-05-19', 'En Proceso', 0, '2026-05-03 16:39:08', 0.00),
(11, 1, 'sitio web de venta de allgo', NULL, '2026-05-03', '2026-05-11', 'En Proceso', 1, '2026-05-03 20:23:25', 0.00),
(12, 3, 'sitio web corporativo', NULL, '2026-05-04', '2026-05-19', 'En Proceso', 1, '2026-05-04 19:47:44', 1000.00),
(13, 1, 'sitio de medico', NULL, '2026-05-04', '2026-05-18', 'Pendiente', 1, '2026-05-04 19:57:25', 1000.00),
(14, 1, 'klllllllllll', NULL, '2026-05-04', '2026-05-20', 'Pendiente', 1, '2026-05-04 20:18:41', 1300.00),
(15, 1, 'asasa', NULL, '2026-05-05', '2026-05-18', 'Pendiente', 1, '2026-05-05 13:20:34', 12.00),
(16, 1, 'sitio de tatuaje', NULL, '2026-05-05', '2026-05-20', 'Pendiente', 1, '2026-05-05 13:23:27', 10.00),
(17, 1, 'sitio web 1', NULL, '2026-05-05', '2026-05-05', 'Pendiente', 1, '2026-05-05 14:14:59', 12.00),
(18, 1, 'sitio web de pago', NULL, '2026-05-05', '2026-05-26', 'En Proceso', 1, '2026-05-05 18:57:12', 100.00),
(19, 1, 'sitio de creacion de proyectos', NULL, '2026-05-05', '2026-05-18', 'Pendiente', 1, '2026-05-05 19:04:44', 26.00),
(20, 1, 'proyecto prueba', NULL, '2026-05-05', '2026-05-19', 'Pendiente', 1, '2026-05-05 19:11:54', 50.00),
(21, 1, 'siktio pureba2', NULL, '2026-05-08', '2026-05-11', 'Pendiente', 1, '2026-05-08 09:03:01', 12.00),
(22, 1, 'sitio de prueba', NULL, '2026-05-08', '2026-05-10', 'Pendiente', 1, '2026-05-08 09:26:48', 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `proyecto_servicio`
--

CREATE TABLE `proyecto_servicio` (
  `id_proyecto` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_acordado` decimal(10,2) NOT NULL COMMENT 'Precio pactado puede diferir del precio base'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relación N:M entre proyectos y servicios';

--
-- Dumping data for table `proyecto_servicio`
--

INSERT INTO `proyecto_servicio` (`id_proyecto`, `id_servicio`, `cantidad`, `precio_acordado`) VALUES
(1, 1, 1, 2500.00),
(1, 2, 1, 700.00);

-- --------------------------------------------------------

--
-- Table structure for table `proyecto_usuario`
--

CREATE TABLE `proyecto_usuario` (
  `id_proyecto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de miembros del equipo asignados a proyectos';

-- --------------------------------------------------------

--
-- Table structure for table `reporte`
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
-- Dumping data for table `reporte`
--

INSERT INTO `reporte` (`id_reporte`, `tipo`, `generado_por`, `fecha_generacion`, `parametros`, `archivo_url`) VALUES
(1, 'Proyectos', 1, '2026-03-05 21:11:07', '{\"estado\":\"En Proceso\",\"fecha_desde\":\"2026-01-01\"}', NULL),
(2, 'Pagos', 1, '2026-03-05 21:11:07', '{\"tipo_pago\":\"Anticipo\",\"mes\":\"02\",\"anio\":\"2026\"}', NULL),
(3, 'Facturación', 1, '2026-03-05 21:11:07', '{\"estado\":\"Pendiente\",\"mes\":\"02\",\"anio\":\"2026\"}', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `servicio`
--

CREATE TABLE `servicio` (
  `id_servicio` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `categoria` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de servicios ofrecidos por TechNova';

--
-- Dumping data for table `servicio`
--

INSERT INTO `servicio` (`id_servicio`, `nombre`, `descripcion`, `precio_base`, `activo`, `categoria`) VALUES
(1, 'Desarrollo de Software a Medida', 'Sistema web personalizado según requerimientos del cliente', 2500.00, 1, ''),
(2, 'Diseño Gráfico', 'Diseño de identidad visual, logos y material publicitario', 800.00, 0, ''),
(3, 'Venta de Demo (Software Base)', 'Software base adaptable a distintos rubros', 1200.00, 1, ''),
(4, 'Curso de JavaScript II', 'Curso presencial/virtual de JavaScript desde cero', 350.00, 1, 'Desarrollo'),
(5, 'sitio de prueba', 'EEEEEEEEEEEE', 66.00, 0, 'Soporte'),
(6, 'sitio de prueba', 'uyuuyuy', 56.00, 0, 'Otro'),
(7, 'sitio de prueba34', 'Curso presencial/virtual de JavaScript desde cero', 11.00, 0, 'Diseño'),
(8, 'Desarrollo de js', '', 9.01, 0, 'Desarrollo');

-- --------------------------------------------------------

--
-- Table structure for table `solicitud`
--

CREATE TABLE `solicitud` (
  `id_solicitud` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL COMMENT 'Usuario/Cliente que hace la solicitud',
  `asunto` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('Pendiente','En Revisión','Atendida','Cerrada') NOT NULL DEFAULT 'Pendiente',
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes de soporte para proyectos finalizados';

-- --------------------------------------------------------

--
-- Table structure for table `soporte_notificacion`
--

CREATE TABLE `soporte_notificacion` (
  `id_notificacion` int(11) NOT NULL,
  `id_incidencia` int(11) NOT NULL,
  `tipo` enum('Prioridad Alta') NOT NULL DEFAULT 'Prioridad Alta',
  `mensaje` varchar(255) NOT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
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
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `email`, `password_hash`, `rol`, `puesto`, `estado`, `creado_en`) VALUES
(1, 'Carlos', 'Lobos Soriano', 'carlos.lobos@technova.com', '$2b$10$ejemplo_hash_admin', 'Administrador', '', 1, '2026-03-05 21:11:07'),
(2, 'Misael', 'Juárez Reyes', 'misael.juarez@technova.com', '$2b$10$ejemplo_hash_dev1', '', '', 1, '2026-03-05 21:11:07'),
(3, 'Kevin', 'Benavides Ruiz', 'kevin.benavides@technova.com', '$2b$10$ejemplo_hash_dev2', '', '', 1, '2026-03-05 21:11:07'),
(4, 'Luisa', 'Alvarez Tejada', 'luisa.alvarez@technova.com', '$2b$10$ejemplo_hash_dis', '', '', 1, '2026-03-05 21:11:07'),
(5, 'Juan', 'Pérez García', 'juan.perez@cliente.com', '$2b$10$ejemplo_hash_cli', 'Cliente', '', 1, '2026-03-05 21:11:07'),
(6, 'Juan', 'Ramirez', 'jure@gmail.com', '$2y$10$5f2QdQ1feQUeZ8fEBxXFe.KOGpIjcYN8oAsSscVzLKhSlda.qWCxm', 'Administrador', '', 1, '2026-03-05 21:31:08'),
(7, 'Juan', 'Ramirez', 'bidto@gmail.com', '$2y$10$gQ9OUDDqL59QvTgJf4ZuN.69j6bbMI45ehQrkZcjuJxpTIgYmjtzu', 'Cliente', '', 1, '2026-03-06 10:22:38'),
(8, 'juan', 'perez', 'juanpere@gmail.com', '123', 'Administrador', '', 1, '2026-03-06 11:30:10'),
(9, 'ju', 'perez', 'perez@gmail.com', '$2y$10$7Fjt/dvHYCPFo.oQYZR4wO2tDDLJJzdeWfbaOtb2iVEpbLM7kk/dS', 'Empleado', 'Diseñador', 1, '2026-03-06 11:31:13'),
(10, 'juan perez', 'perez', 'ysya@gmail.com', '$2y$10$NkrkvzPRG0BBQ/5xoCV0Le8vVD2vn/YjMBujl9nTns05Pp620MOjK', 'Administrador', '', 1, '2026-03-06 11:32:01');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_facturas_detalle`
-- (See below for the actual view)
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
-- Stand-in structure for view `v_pagos_por_proyecto`
-- (See below for the actual view)
--
CREATE TABLE `v_pagos_por_proyecto` (
`id_proyecto` int(11)
,`proyecto` varchar(150)
,`total_pagado` decimal(32,2)
,`num_pagos` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_proyectos_clientes`
-- (See below for the actual view)
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
-- Structure for view `v_facturas_detalle`
--
DROP TABLE IF EXISTS `v_facturas_detalle`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_facturas_detalle`  AS SELECT `f`.`numero_factura` AS `numero_factura`, `f`.`fecha_emision` AS `fecha_emision`, `f`.`subtotal` AS `subtotal`, `f`.`iva` AS `iva`, `f`.`total` AS `total`, `f`.`estado` AS `estado_factura`, `c`.`nombre_empresa` AS `cliente`, `pr`.`nombre` AS `proyecto` FROM ((`factura` `f` join `cliente` `c` on(`f`.`id_cliente` = `c`.`id_cliente`)) join `proyecto` `pr` on(`f`.`id_proyecto` = `pr`.`id_proyecto`)) ;

-- --------------------------------------------------------

--
-- Structure for view `v_pagos_por_proyecto`
--
DROP TABLE IF EXISTS `v_pagos_por_proyecto`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pagos_por_proyecto`  AS SELECT `p`.`id_proyecto` AS `id_proyecto`, `pr`.`nombre` AS `proyecto`, sum(`p`.`monto`) AS `total_pagado`, count(`p`.`id_pago`) AS `num_pagos` FROM (`pago` `p` join `proyecto` `pr` on(`p`.`id_proyecto` = `pr`.`id_proyecto`)) GROUP BY `p`.`id_proyecto`, `pr`.`nombre` ;

-- --------------------------------------------------------

--
-- Structure for view `v_proyectos_clientes`
--
DROP TABLE IF EXISTS `v_proyectos_clientes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_proyectos_clientes`  AS SELECT `p`.`id_proyecto` AS `id_proyecto`, `p`.`nombre` AS `proyecto`, `p`.`estado` AS `estado`, `p`.`fecha_entrega` AS `fecha_entrega`, `p`.`anticipo_pagado` AS `anticipo_pagado`, `c`.`nombre_empresa` AS `cliente`, `u`.`email` AS `email_cliente` FROM ((`proyecto` `p` join `cliente` `c` on(`p`.`id_cliente` = `c`.`id_cliente`)) join `usuario` `u` on(`c`.`id_usuario` = `u`.`id_usuario`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `fk_cliente_usuario` (`id_usuario`);

--
-- Indexes for table `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalle_factura` (`id_factura`),
  ADD KEY `fk_detalle_servicio` (`id_servicio`);

--
-- Indexes for table `entregable`
--
ALTER TABLE `entregable`
  ADD PRIMARY KEY (`id_entregable`),
  ADD KEY `id_proyecto` (`id_proyecto`);

--
-- Indexes for table `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`id_factura`),
  ADD UNIQUE KEY `numero_factura` (`numero_factura`),
  ADD KEY `fk_factura_proyecto` (`id_proyecto`),
  ADD KEY `fk_factura_cliente` (`id_cliente`),
  ADD KEY `fk_factura_usuario` (`generada_por`);

--
-- Indexes for table `incidencia`
--
ALTER TABLE `incidencia`
  ADD PRIMARY KEY (`id_incidencia`),
  ADD KEY `id_proyecto` (`id_proyecto`),
  ADD KEY `id_usuario_reporta` (`id_usuario_reporta`),
  ADD KEY `idx_inc_actualizado_por` (`actualizado_por`);

--
-- Indexes for table `pago`
--
ALTER TABLE `pago`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `fk_pago_proyecto` (`id_proyecto`),
  ADD KEY `fk_pago_usuario` (`registrado_por`);

--
-- Indexes for table `password_reset_token`
--
ALTER TABLE `password_reset_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `proyecto`
--
ALTER TABLE `proyecto`
  ADD PRIMARY KEY (`id_proyecto`),
  ADD KEY `fk_proyecto_cliente` (`id_cliente`);

--
-- Indexes for table `proyecto_servicio`
--
ALTER TABLE `proyecto_servicio`
  ADD PRIMARY KEY (`id_proyecto`,`id_servicio`),
  ADD KEY `fk_ps_servicio` (`id_servicio`);

--
-- Indexes for table `proyecto_usuario`
--
ALTER TABLE `proyecto_usuario`
  ADD PRIMARY KEY (`id_proyecto`,`id_usuario`),
  ADD KEY `fk_pu_usuario` (`id_usuario`);

--
-- Indexes for table `reporte`
--
ALTER TABLE `reporte`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reporte_usuario` (`generado_por`);

--
-- Indexes for table `servicio`
--
ALTER TABLE `servicio`
  ADD PRIMARY KEY (`id_servicio`);

--
-- Indexes for table `solicitud`
--
ALTER TABLE `solicitud`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `fk_solicitud_proyecto` (`id_proyecto`),
  ADD KEY `fk_solicitud_usuario` (`id_usuario`);

--
-- Indexes for table `soporte_notificacion`
--
ALTER TABLE `soporte_notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `idx_soporte_notificacion_incidencia` (`id_incidencia`),
  ADD KEY `idx_soporte_notificacion_leida` (`leida`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `detalle_factura`
--
ALTER TABLE `detalle_factura`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `entregable`
--
ALTER TABLE `entregable`
  MODIFY `id_entregable` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `factura`
--
ALTER TABLE `factura`
  MODIFY `id_factura` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `incidencia`
--
ALTER TABLE `incidencia`
  MODIFY `id_incidencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pago`
--
ALTER TABLE `pago`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `password_reset_token`
--
ALTER TABLE `password_reset_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proyecto`
--
ALTER TABLE `proyecto`
  MODIFY `id_proyecto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `reporte`
--
ALTER TABLE `reporte`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `servicio`
--
ALTER TABLE `servicio`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `solicitud`
--
ALTER TABLE `solicitud`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `soporte_notificacion`
--
ALTER TABLE `soporte_notificacion`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_cliente_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Constraints for table `detalle_factura`
--
ALTER TABLE `detalle_factura`
  ADD CONSTRAINT `fk_detalle_factura` FOREIGN KEY (`id_factura`) REFERENCES `factura` (`id_factura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`) ON UPDATE CASCADE;

--
-- Constraints for table `entregable`
--
ALTER TABLE `entregable`
  ADD CONSTRAINT `fk_entregable_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `fk_factura_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_usuario` FOREIGN KEY (`generada_por`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Constraints for table `incidencia`
--
ALTER TABLE `incidencia`
  ADD CONSTRAINT `fk_incidencia_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_incidencia_usuario` FOREIGN KEY (`id_usuario_reporta`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Constraints for table `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `fk_pago_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pago_usuario` FOREIGN KEY (`registrado_por`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Constraints for table `proyecto`
--
ALTER TABLE `proyecto`
  ADD CONSTRAINT `fk_proyecto_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON UPDATE CASCADE;

--
-- Constraints for table `proyecto_servicio`
--
ALTER TABLE `proyecto_servicio`
  ADD CONSTRAINT `fk_ps_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ps_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`) ON UPDATE CASCADE;

--
-- Constraints for table `proyecto_usuario`
--
ALTER TABLE `proyecto_usuario`
  ADD CONSTRAINT `fk_pu_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pu_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reporte`
--
ALTER TABLE `reporte`
  ADD CONSTRAINT `fk_reporte_usuario` FOREIGN KEY (`generado_por`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

--
-- Constraints for table `solicitud`
--
ALTER TABLE `solicitud`
  ADD CONSTRAINT `fk_solicitud_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_solicitud_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `soporte_notificacion`
--
ALTER TABLE `soporte_notificacion`
  ADD CONSTRAINT `fk_soporte_notificacion_incidencia` FOREIGN KEY (`id_incidencia`) REFERENCES `incidencia` (`id_incidencia`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
