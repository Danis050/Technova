-- ============================================================
-- phpMyAdmin SQL Dump — COMBINADO
-- Base de datos: technova_db
-- Servidor: localhost (MariaDB 10.4.28)
-- Generado: May 28, 2026
-- Sprint 4 · Equipo Aevum · TechNova
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================
-- TABLAS BASE
-- ============================================================

-- --------------------------------------------------------
-- Tabla: usuario
-- --------------------------------------------------------
CREATE TABLE `usuario` (
  `id_usuario`    int(11)      NOT NULL,
  `nombre`        varchar(100) NOT NULL,
  `apellido`      varchar(100) NOT NULL,
  `email`         varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'Contraseña encriptada con bcrypt',
  `rol`           enum('Administrador','Empleado','Cliente') DEFAULT 'Cliente',
  `puesto`        varchar(50)  DEFAULT '',
  `estado`        tinyint(1)   NOT NULL DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
  `creado_en`     datetime     NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Gestión de acceso con roles diferenciados';

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `email`, `password_hash`, `rol`, `puesto`, `estado`, `creado_en`) VALUES
(1,  'Carlos',     'Lobos Soriano',  'carlos.lobos@technova.com', '$2b$10$ejemplo_hash_admin', 'Administrador', '',          1, '2026-03-05 21:11:07'),
(2,  'Misael',     'Juárez Reyes',   'misael.juarez@technova.com','$2b$10$ejemplo_hash_dev1',  '',              '',          1, '2026-03-05 21:11:07'),
(3,  'Kevin',      'Benavides Ruiz', 'kevin.benavides@technova.com','$2b$10$ejemplo_hash_dev2','',             '',          1, '2026-03-05 21:11:07'),
(4,  'Luisa',      'Alvarez Tejada', 'luisa.alvarez@technova.com','$2b$10$ejemplo_hash_dis',   '',              '',          1, '2026-03-05 21:11:07'),
(5,  'Juan',       'Pérez García',   'juan.perez@cliente.com',    '$2b$10$ejemplo_hash_cli',   'Cliente',       '',          1, '2026-03-05 21:11:07'),
(6,  'Juan',       'Ramirez',        'jure@gmail.com',            '$2y$10$5f2QdQ1feQUeZ8fEBxXFe.KOGpIjcYN8oAsSscVzLKhSlda.qWCxm', 'Administrador', '', 1, '2026-03-05 21:31:08'),
(7,  'Juan',       'Ramirez',        'bidto@gmail.com',           '$2y$10$gQ9OUDDqL59QvTgJf4ZuN.69j6bbMI45ehQrkZcjuJxpTIgYmjtzu',  'Cliente',       '', 1, '2026-03-06 10:22:38'),
(8,  'juan',       'perez',          'juanpere@gmail.com',        '123',                        'Administrador', '',          1, '2026-03-06 11:30:10'),
(9,  'ju',         'perez',          'perez@gmail.com',           '$2y$10$7Fjt/dvHYCPFo.oQYZR4wO2tDDLJJzdeWfbaOtb2iVEpbLM7kk/dS',  'Empleado',      'Diseñador', 1, '2026-03-06 11:31:13'),
(10, 'juan perez', 'perez',          'ysya@gmail.com',            '$2y$10$NkrkvzPRG0BBQ/5xoCV0Le8vVD2vn/YjMBujl9nTns05Pp620MOjK',   'Administrador', '',          1, '2026-03-06 11:32:01');

-- --------------------------------------------------------
-- Tabla: cliente
-- --------------------------------------------------------
CREATE TABLE `cliente` (
  `id_cliente`    int(11)      NOT NULL,
  `id_usuario`    int(11)      NOT NULL COMMENT 'FK → usuario con rol Cliente',
  `nombre_empresa`varchar(150) NOT NULL,
  `telefono`      varchar(20)  DEFAULT NULL,
  `direccion`     varchar(255) DEFAULT NULL,
  `nit`           varchar(30)  DEFAULT NULL COMMENT 'Para facturación electrónica',
  `nrc`           varchar(30)  DEFAULT NULL COMMENT 'Para facturación electrónica',
  `registrado_en` datetime     NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de clientes de TechNova';

INSERT INTO `cliente` (`id_cliente`, `id_usuario`, `nombre_empresa`, `telefono`, `direccion`, `nit`, `nrc`, `registrado_en`) VALUES
(1, 5,  'Empresa Pérez S.A. de C.V.', '7890-1234', 'San Miguel, El Salvador', '0614-190185-101-6', '12345-6', '2026-03-05 21:11:07'),
(2, 6,  'Juan Ramirez',    NULL, NULL, NULL, NULL, '2026-03-05 21:31:08'),
(3, 7,  'Juan Ramirez',    NULL, NULL, NULL, NULL, '2026-03-06 10:22:38'),
(4, 10, 'juan perez perez',NULL, NULL, NULL, NULL, '2026-03-06 11:32:01');

-- --------------------------------------------------------
-- Tabla: servicio
-- --------------------------------------------------------
CREATE TABLE `servicio` (
  `id_servicio`  int(11)      NOT NULL,
  `nombre`       varchar(100) NOT NULL,
  `descripcion`  text         DEFAULT NULL,
  `precio_base`  decimal(10,2)NOT NULL,
  `activo`       tinyint(1)   NOT NULL DEFAULT 1,
  `categoria`    varchar(50)  NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Catálogo de servicios ofrecidos por TechNova';

INSERT INTO `servicio` (`id_servicio`, `nombre`, `descripcion`, `precio_base`, `activo`, `categoria`) VALUES
(1, 'Desarrollo de Software a Medida', 'Sistema web personalizado según requerimientos del cliente', 2500.00, 1, ''),
(2, 'Diseño Gráfico',                  'Diseño de identidad visual, logos y material publicitario',  800.00,  0, ''),
(3, 'Venta de Demo (Software Base)',   'Software base adaptable a distintos rubros',                 1200.00, 1, ''),
(4, 'Curso de JavaScript II',          'Curso presencial/virtual de JavaScript desde cero',          350.00,  1, 'Desarrollo'),
(5, 'sitio de prueba',                 'EEEEEEEEEEEE',                                               66.00,   0, 'Soporte'),
(6, 'sitio de prueba',                 'uyuuyuy',                                                    56.00,   0, 'Otro'),
(7, 'sitio de prueba34',               'Curso presencial/virtual de JavaScript desde cero',          11.00,   0, 'Diseño'),
(8, 'Desarrollo de js',                '',                                                           9.01,    0, 'Desarrollo');

-- --------------------------------------------------------
-- Tabla: proyecto
-- --------------------------------------------------------
CREATE TABLE `proyecto` (
  `id_proyecto`    int(11)       NOT NULL,
  `id_cliente`     int(11)       NOT NULL,
  `nombre`         varchar(150)  NOT NULL,
  `descripcion`    text          DEFAULT NULL,
  `fecha_inicio`   date          DEFAULT NULL,
  `fecha_entrega`  date          DEFAULT NULL,
  `estado`         enum('Pendiente','En Proceso','Completado','Cancelado') NOT NULL DEFAULT 'Pendiente',
  `anticipo_pagado`tinyint(1)    NOT NULL DEFAULT 0 COMMENT 'RB-01: El proyecto inicia solo si anticipo=1',
  `creado_en`      datetime      NOT NULL DEFAULT current_timestamp(),
  `monto`          decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='RN: Proyecto no inicia sin anticipo registrado';

INSERT INTO `proyecto` (`id_proyecto`, `id_cliente`, `nombre`, `descripcion`, `fecha_inicio`, `fecha_entrega`, `estado`, `anticipo_pagado`, `creado_en`, `monto`) VALUES
(1,  4, 'Sistema de Facturación',           'Desarrollo de sistema de facturación electrónica para la empresa', '2026-02-04', '2026-05-20', 'Pendiente',  1, '2026-03-05 21:11:07', 0.00),
(6,  2, 'Sistema de Facturación Web',        NULL, '2026-04-17', '2026-05-06', 'En Proceso', 0, '2026-04-17 10:19:44', 0.00),
(9,  2, 'Sistema de gestion de citas',       NULL, '2026-05-03', '2026-05-10', 'En Proceso', 0, '2026-05-03 11:46:02', 0.00),
(10, 3, 'aAAAAAA',                           NULL, '2026-05-03', '2026-05-19', 'En Proceso', 0, '2026-05-03 16:39:08', 0.00),
(11, 1, 'sitio web de venta de allgo',       NULL, '2026-05-03', '2026-05-11', 'En Proceso', 1, '2026-05-03 20:23:25', 0.00),
(12, 3, 'sitio web corporativo',             NULL, '2026-05-04', '2026-05-19', 'En Proceso', 1, '2026-05-04 19:47:44', 1000.00),
(13, 1, 'sitio de medico',                   NULL, '2026-05-04', '2026-05-18', 'Pendiente',  1, '2026-05-04 19:57:25', 1000.00),
(14, 1, 'klllllllllll',                      NULL, '2026-05-04', '2026-05-20', 'Pendiente',  1, '2026-05-04 20:18:41', 1300.00),
(15, 1, 'asasa',                             NULL, '2026-05-05', '2026-05-18', 'Pendiente',  1, '2026-05-05 13:20:34', 12.00),
(16, 1, 'sitio de tatuaje',                  NULL, '2026-05-05', '2026-05-20', 'Pendiente',  1, '2026-05-05 13:23:27', 10.00),
(17, 1, 'sitio web 1',                       NULL, '2026-05-05', '2026-05-05', 'Pendiente',  1, '2026-05-05 14:14:59', 12.00),
(18, 1, 'sitio web de pago',                 NULL, '2026-05-05', '2026-05-26', 'En Proceso', 1, '2026-05-05 18:57:12', 100.00),
(19, 1, 'sitio de creacion de proyectos',    NULL, '2026-05-05', '2026-05-18', 'Pendiente',  1, '2026-05-05 19:04:44', 26.00),
(20, 1, 'proyecto prueba',                   NULL, '2026-05-05', '2026-05-19', 'Pendiente',  1, '2026-05-05 19:11:54', 50.00),
(21, 1, 'siktio pureba2',                    NULL, '2026-05-08', '2026-05-11', 'Pendiente',  1, '2026-05-08 09:03:01', 12.00),
(22, 1, 'sitio de prueba',                   NULL, '2026-05-08', '2026-05-10', 'Pendiente',  1, '2026-05-08 09:26:48', 10.00);

-- --------------------------------------------------------
-- Tabla: proyecto_servicio
-- --------------------------------------------------------
CREATE TABLE `proyecto_servicio` (
  `id_proyecto`    int(11)       NOT NULL,
  `id_servicio`    int(11)       NOT NULL,
  `cantidad`       int(11)       NOT NULL DEFAULT 1,
  `precio_acordado`decimal(10,2) NOT NULL COMMENT 'Precio pactado puede diferir del precio base'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Relación N:M entre proyectos y servicios';

INSERT INTO `proyecto_servicio` (`id_proyecto`, `id_servicio`, `cantidad`, `precio_acordado`) VALUES
(1, 1, 1, 2500.00),
(1, 2, 1, 700.00);

-- --------------------------------------------------------
-- Tabla: proyecto_usuario
-- --------------------------------------------------------
CREATE TABLE `proyecto_usuario` (
  `id_proyecto`      int(11)  NOT NULL,
  `id_usuario`       int(11)  NOT NULL,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de miembros del equipo asignados a proyectos';

-- --------------------------------------------------------
-- Tabla: pago
-- --------------------------------------------------------
CREATE TABLE `pago` (
  `id_pago`        int(11)       NOT NULL,
  `id_proyecto`    int(11)       NOT NULL,
  `tipo_pago`      enum('Anticipo','Parcial','Saldo Final') NOT NULL,
  `monto`          decimal(10,2) NOT NULL,
  `fecha_pago`     date          NOT NULL,
  `metodo_pago`    enum('Efectivo','Transferencia','Cheque') NOT NULL,
  `comprobante`    varchar(255)  DEFAULT NULL COMMENT 'Número o ruta del comprobante',
  `registrado_por` int(11)       NOT NULL COMMENT 'FK → usuario que registra el pago',
  `creado_en`      datetime      NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Control de pagos y anticipos por proyecto';

INSERT INTO `pago` (`id_pago`, `id_proyecto`, `tipo_pago`, `monto`, `fecha_pago`, `metodo_pago`, `comprobante`, `registrado_por`, `creado_en`) VALUES
(1,  1,  'Anticipo',    1600.00, '2026-02-14', 'Transferencia', 'TRF-2026-0001', 1, '2026-03-05 21:11:07'),
(2,  12, 'Anticipo',    1.38,    '2026-05-05', 'Efectivo',      '',              6, '2026-05-04 19:47:44'),
(3,  13, 'Anticipo',    400.00,  '2026-05-05', 'Efectivo',      '',              6, '2026-05-04 19:57:25'),
(4,  14, 'Anticipo',    600.00,  '2026-05-05', 'Efectivo',      '',              6, '2026-05-04 20:18:41'),
(5,  11, 'Anticipo',    124.00,  '2026-05-05', 'Efectivo',      '',              6, '2026-05-04 23:43:53'),
(6,  14, 'Saldo Final', 700.00,  '2026-05-05', 'Efectivo',      '',              6, '2026-05-05 00:13:01'),
(7,  15, 'Anticipo',    6.00,    '2026-05-05', 'Efectivo',      '',              6, '2026-05-05 13:20:34'),
(8,  16, 'Anticipo',    5.00,    '2026-05-05', 'Efectivo',      '',              6, '2026-05-05 13:23:27'),
(9,  16, 'Saldo Final', 5.00,    '2026-05-05', 'Efectivo',      '',              6, '2026-05-05 13:30:54'),
(10, 17, 'Anticipo',    6.00,    '2026-05-05', 'Efectivo',      '',              6, '2026-05-05 14:14:59'),
(11, 18, 'Anticipo',    50.00,   '2026-05-06', 'Efectivo',      '',              6, '2026-05-05 18:57:12'),
(12, 18, 'Parcial',     25.00,   '2026-05-06', 'Transferencia', '',              6, '2026-05-05 19:04:05'),
(13, 19, 'Anticipo',    12.50,   '2026-05-06', 'Efectivo',      '',              6, '2026-05-05 19:04:44'),
(14, 20, 'Anticipo',    25.00,   '2026-05-06', 'Efectivo',      '',              6, '2026-05-05 19:11:54'),
(15, 20, 'Parcial',     25.00,   '2026-05-06', 'Efectivo',      '',              6, '2026-05-05 19:12:41'),
(16, 21, 'Anticipo',    6.00,    '2026-05-08', 'Efectivo',      '',              6, '2026-05-08 09:03:01'),
(17, 21, 'Parcial',     6.00,    '2026-05-08', 'Efectivo',      '',              6, '2026-05-08 09:03:15'),
(18, 22, 'Anticipo',    5.00,    '2026-05-08', 'Efectivo',      '',              6, '2026-05-08 09:26:48'),
(19, 22, 'Saldo Final', 5.00,    '2026-05-08', 'Efectivo',      '',              6, '2026-05-08 09:27:14'),
(20, 13, 'Parcial',     12.00,   '2026-05-08', 'Efectivo',      '',              6, '2026-05-08 09:31:28');

-- --------------------------------------------------------
-- Tabla: factura
-- --------------------------------------------------------
CREATE TABLE `factura` (
  `id_factura`     int(11)       NOT NULL,
  `id_proyecto`    int(11)       NOT NULL,
  `id_cliente`     int(11)       NOT NULL,
  `numero_factura` varchar(50)   NOT NULL COMMENT 'Correlativo electrónico único',
  `fecha_emision`  date          NOT NULL,
  `subtotal`       decimal(10,2) NOT NULL,
  `iva`            decimal(10,2) NOT NULL COMMENT 'IVA 13% El Salvador',
  `total`          decimal(10,2) NOT NULL,
  `estado`         enum('Pendiente','Pagada','Anulada') NOT NULL DEFAULT 'Pendiente',
  `generada_por`   int(11)       NOT NULL COMMENT 'FK → usuario que genera la factura',
  `creado_en`      datetime      NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Módulo de facturación electrónica obligatorio';

INSERT INTO `factura` (`id_factura`, `id_proyecto`, `id_cliente`, `numero_factura`, `fecha_emision`, `subtotal`, `iva`, `total`, `estado`, `generada_por`, `creado_en`) VALUES
(1,  1,  1, 'FAC-2026-0001',   '2026-02-14', 3200.00, 416.00, 3616.00, 'Pagada',   1, '2026-03-05 21:11:07'),
(2,  18, 1, 'TN-2026-000002',  '2026-05-06', 44.25,   5.75,   50.00,   'Pagada',   6, '2026-05-05 18:57:12'),
(3,  18, 1, 'TN-2026-000003',  '2026-05-06', 22.12,   2.88,   25.00,   'Pagada',   6, '2026-05-05 19:04:05'),
(4,  19, 1, 'TN-2026-000004',  '2026-05-06', 11.06,   1.44,   12.50,   'Pagada',   6, '2026-05-05 19:04:44'),
(5,  20, 1, 'TN-2026-000005',  '2026-05-06', 22.12,   2.88,   25.00,   'Pagada',   6, '2026-05-05 19:11:54'),
(6,  20, 1, 'TN-2026-000006',  '2026-05-06', 22.12,   2.88,   25.00,   'Pagada',   6, '2026-05-05 19:12:42'),
(7,  21, 1, 'TN-2026-000007',  '2026-05-08', 5.31,    0.69,   6.00,    'Pagada',   6, '2026-05-08 09:03:01'),
(8,  21, 1, 'TN-2026-000008',  '2026-05-08', 5.31,    0.69,   6.00,    'Pagada',   6, '2026-05-08 09:03:15'),
(9,  22, 1, 'TN-2026-000009',  '2026-05-08', 4.42,    0.58,   5.00,    'Pagada',   6, '2026-05-08 09:26:48'),
(10, 22, 1, 'TN-2026-000010',  '2026-05-08', 4.42,    0.58,   5.00,    'Pagada',   6, '2026-05-08 09:27:14'),
(11, 13, 1, 'TN-2026-000011',  '2026-05-08', 10.62,   1.38,   12.00,   'Pendiente',6, '2026-05-08 09:31:28');

-- --------------------------------------------------------
-- Tabla: detalle_factura
-- --------------------------------------------------------
CREATE TABLE `detalle_factura` (
  `id_detalle`     int(11)       NOT NULL,
  `id_factura`     int(11)       NOT NULL,
  `id_servicio`    int(11)       NOT NULL,
  `descripcion`    varchar(255)  NOT NULL,
  `cantidad`       int(11)       NOT NULL DEFAULT 1,
  `precio_unitario`decimal(10,2) NOT NULL,
  `subtotal`       decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Líneas de detalle de cada factura';

INSERT INTO `detalle_factura` (`id_detalle`, `id_factura`, `id_servicio`, `descripcion`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 1, 'Desarrollo de Sistema de Facturación Web', 1, 2500.00, 2500.00),
(2, 1, 2, 'Diseño de identidad visual del sistema',  1,  700.00,  700.00);

-- --------------------------------------------------------
-- Tabla: entregable
-- --------------------------------------------------------
CREATE TABLE `entregable` (
  `id_entregable` int(11)      NOT NULL,
  `id_proyecto`   int(11)      NOT NULL,
  `nombre`        varchar(150) NOT NULL,
  `descripcion`   text         DEFAULT NULL,
  `fecha_entrega` date         DEFAULT NULL,
  `estado`        enum('Pendiente','En Proceso','Entregado','Retrasado') NOT NULL DEFAULT 'Pendiente',
  `archivo_url`   varchar(255) DEFAULT NULL,
  `creado_en`     datetime     NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: incidencia
-- --------------------------------------------------------
CREATE TABLE `incidencia` (
  `id_incidencia`       int(11)      NOT NULL,
  `id_proyecto`         int(11)      NOT NULL,
  `id_usuario_reporta`  int(11)      NOT NULL COMMENT 'FK → usuario (Cliente o miembro del equipo)',
  `titulo`              varchar(150) NOT NULL,
  `descripcion`         text         NOT NULL,
  `prioridad`           enum('Baja','Media','Alta') NOT NULL DEFAULT 'Media',
  `estado`              enum('Pendiente','En atención','Resuelto','Cerrado','Abierta','En Proceso','Resuelta','Cerrada') NOT NULL DEFAULT 'Pendiente',
  `comentario_resolucion` text       DEFAULT NULL,
  `actualizado_por`     int(11)      DEFAULT NULL,
  `notificacion_alta`   tinyint(1)   NOT NULL DEFAULT 0,
  `creado_en`           datetime     NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: solicitud
-- --------------------------------------------------------
CREATE TABLE `solicitud` (
  `id_solicitud` int(11)      NOT NULL,
  `id_proyecto`  int(11)      NOT NULL,
  `id_usuario`   int(11)      NOT NULL COMMENT 'Usuario/Cliente que hace la solicitud',
  `asunto`       varchar(150) NOT NULL,
  `descripcion`  text         NOT NULL,
  `estado`       enum('Pendiente','En Revisión','Atendida','Cerrada') NOT NULL DEFAULT 'Pendiente',
  `creado_en`    datetime     NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Solicitudes de soporte para proyectos finalizados';

-- --------------------------------------------------------
-- Tabla: reporte
-- --------------------------------------------------------
CREATE TABLE `reporte` (
  `id_reporte`       int(11)  NOT NULL,
  `tipo`             enum('Pagos','Proyectos','Facturación') NOT NULL COMMENT 'Mínimo 3 tipos de reporte requeridos',
  `generado_por`     int(11)  NOT NULL,
  `fecha_generacion` datetime NOT NULL DEFAULT current_timestamp(),
  `parametros`       text     DEFAULT NULL COMMENT 'Filtros usados, almacenados en JSON',
  `archivo_url`      varchar(255) DEFAULT NULL COMMENT 'Ruta del reporte exportado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de los 3 reportes administrativos obligatorios';

INSERT INTO `reporte` (`id_reporte`, `tipo`, `generado_por`, `fecha_generacion`, `parametros`, `archivo_url`) VALUES
(1, 'Proyectos',   1, '2026-03-05 21:11:07', '{"estado":"En Proceso","fecha_desde":"2026-01-01"}', NULL),
(2, 'Pagos',       1, '2026-03-05 21:11:07', '{"tipo_pago":"Anticipo","mes":"02","anio":"2026"}',  NULL),
(3, 'Facturación', 1, '2026-03-05 21:11:07', '{"estado":"Pendiente","mes":"02","anio":"2026"}',    NULL);

-- --------------------------------------------------------
-- Tabla: notificaciones
-- --------------------------------------------------------
CREATE TABLE `notificaciones` (
  `id`              int(11)      NOT NULL,
  `id_usuario`      int(11)      NOT NULL,
  `mensaje`         varchar(255) NOT NULL,
  `url_referencia`  varchar(255) DEFAULT NULL,
  `leida`           tinyint(1)   NOT NULL DEFAULT 0,
  `fecha_creacion`  datetime     NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Notificaciones internas del sistema';

-- --------------------------------------------------------
-- Tabla: soporte_notificacion
-- --------------------------------------------------------
CREATE TABLE `soporte_notificacion` (
  `id_notificacion` int(11)      NOT NULL,
  `id_incidencia`   int(11)      NOT NULL,
  `tipo`            enum('Prioridad Alta') NOT NULL DEFAULT 'Prioridad Alta',
  `mensaje`         varchar(255) NOT NULL,
  `leida`           tinyint(1)   NOT NULL DEFAULT 0,
  `creado_en`       datetime     NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabla: password_reset_token
-- --------------------------------------------------------
CREATE TABLE `password_reset_token` (
  `id`         int(11)      NOT NULL,
  `email`      varchar(150) NOT NULL,
  `token`      varchar(255) NOT NULL,
  `expires_at` datetime     NOT NULL,
  `created_at` datetime     DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- SPRINT 4 — HU-24: Registro de Auditoría
-- Responsable: David Alexander Urias Blanco (U20240435)
-- ⚠️ PRIORITARIA — bloquea HU24-02, HU24-03, HU17-04
-- ============================================================
CREATE TABLE IF NOT EXISTS `auditoria` (
  `id`             int(11)      NOT NULL AUTO_INCREMENT,
  `id_usuario`     int(11)      DEFAULT NULL COMMENT 'NULL si el usuario fue eliminado',
  `nombre_usuario` varchar(100) DEFAULT NULL COMMENT 'Respaldo del nombre al momento de la acción',
  `accion`         varchar(100) NOT NULL   COMMENT 'login | logout | cambio_rol | cambio_estado_proyecto | registro_pago | crear_cliente | editar_cliente | eliminar_cliente',
  `entidad`        varchar(100) DEFAULT NULL COMMENT 'Nombre de la tabla/entidad afectada',
  `id_entidad`     int(11)      DEFAULT NULL COMMENT 'PK del registro afectado',
  `descripcion`    text         DEFAULT NULL COMMENT 'Detalle legible de la acción realizada',
  `ip_origen`      varchar(45)  DEFAULT NULL COMMENT 'IPv4 o IPv6 del cliente',
  `fecha`          datetime     NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fecha`   (`fecha`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_accion`  (`accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='HU-24 · Registro inmutable de acciones críticas del sistema';

-- ============================================================
-- SPRINT 4 — HU-25: Calificar Servicio Recibido
-- Responsable: David Alexander Urias Blanco (U20240435)
-- ============================================================
CREATE TABLE IF NOT EXISTS `calificaciones` (
  `id`          int(11)    NOT NULL AUTO_INCREMENT,
  `id_proyecto` int(11)    NOT NULL,
  `id_cliente`  int(11)    NOT NULL,
  `puntuacion`  tinyint(1) NOT NULL COMMENT '1 a 5 estrellas',
  `comentario`  text       DEFAULT NULL,
  `fecha`       datetime   NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unica_calificacion` (`id_proyecto`,`id_cliente`),
  KEY `idx_calif_proyecto` (`id_proyecto`),
  KEY `idx_calif_cliente`  (`id_cliente`),
  CONSTRAINT `chk_puntuacion` CHECK (`puntuacion` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='HU-25 · Calificaciones de servicio por proyecto y cliente';

-- ============================================================
-- VISTAS
-- ============================================================

CREATE OR REPLACE VIEW `v_facturas_detalle` AS
  SELECT f.numero_factura, f.fecha_emision, f.subtotal, f.iva, f.total,
         f.estado AS estado_factura, c.nombre_empresa AS cliente, pr.nombre AS proyecto
  FROM factura f
  JOIN cliente  c  ON f.id_cliente  = c.id_cliente
  JOIN proyecto pr ON f.id_proyecto = pr.id_proyecto;

CREATE OR REPLACE VIEW `v_pagos_por_proyecto` AS
  SELECT p.id_proyecto, pr.nombre AS proyecto,
         SUM(p.monto) AS total_pagado, COUNT(p.id_pago) AS num_pagos
  FROM pago p
  JOIN proyecto pr ON p.id_proyecto = pr.id_proyecto
  GROUP BY p.id_proyecto, pr.nombre;

CREATE OR REPLACE VIEW `v_proyectos_clientes` AS
  SELECT p.id_proyecto, p.nombre AS proyecto, p.estado, p.fecha_entrega,
         p.anticipo_pagado, c.nombre_empresa AS cliente, u.email AS email_cliente
  FROM proyecto p
  JOIN cliente  c ON p.id_cliente   = c.id_cliente
  JOIN usuario  u ON c.id_usuario   = u.id_usuario;

-- ============================================================
-- PRIMARY KEYS & ÍNDICES
-- ============================================================

ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD KEY `fk_cliente_usuario` (`id_usuario`);

ALTER TABLE `servicio`
  ADD PRIMARY KEY (`id_servicio`);

ALTER TABLE `proyecto`
  ADD PRIMARY KEY (`id_proyecto`),
  ADD KEY `fk_proyecto_cliente` (`id_cliente`);

ALTER TABLE `proyecto_servicio`
  ADD PRIMARY KEY (`id_proyecto`,`id_servicio`),
  ADD KEY `fk_ps_servicio` (`id_servicio`);

ALTER TABLE `proyecto_usuario`
  ADD PRIMARY KEY (`id_proyecto`,`id_usuario`),
  ADD KEY `fk_pu_usuario` (`id_usuario`);

ALTER TABLE `pago`
  ADD PRIMARY KEY (`id_pago`),
  ADD KEY `fk_pago_proyecto`  (`id_proyecto`),
  ADD KEY `fk_pago_usuario`   (`registrado_por`);

ALTER TABLE `factura`
  ADD PRIMARY KEY (`id_factura`),
  ADD UNIQUE KEY `numero_factura` (`numero_factura`),
  ADD KEY `fk_factura_proyecto` (`id_proyecto`),
  ADD KEY `fk_factura_cliente`  (`id_cliente`),
  ADD KEY `fk_factura_usuario`  (`generada_por`);

ALTER TABLE `detalle_factura`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalle_factura`  (`id_factura`),
  ADD KEY `fk_detalle_servicio` (`id_servicio`);

ALTER TABLE `entregable`
  ADD PRIMARY KEY (`id_entregable`),
  ADD KEY `id_proyecto` (`id_proyecto`);

ALTER TABLE `incidencia`
  ADD PRIMARY KEY (`id_incidencia`),
  ADD KEY `id_proyecto`            (`id_proyecto`),
  ADD KEY `id_usuario_reporta`     (`id_usuario_reporta`),
  ADD KEY `idx_inc_actualizado_por`(`actualizado_por`);

ALTER TABLE `solicitud`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `fk_solicitud_proyecto` (`id_proyecto`),
  ADD KEY `fk_solicitud_usuario`  (`id_usuario`);

ALTER TABLE `reporte`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `fk_reporte_usuario` (`generado_por`);

ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notificaciones_usuario` (`id_usuario`),
  ADD KEY `idx_notificaciones_leida`   (`leida`);

ALTER TABLE `soporte_notificacion`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `idx_soporte_notificacion_incidencia` (`id_incidencia`),
  ADD KEY `idx_soporte_notificacion_leida`      (`leida`);

ALTER TABLE `password_reset_token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

-- ============================================================
-- AUTO_INCREMENT
-- ============================================================

ALTER TABLE `usuario`              MODIFY `id_usuario`      int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
ALTER TABLE `cliente`              MODIFY `id_cliente`      int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `servicio`             MODIFY `id_servicio`     int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
ALTER TABLE `proyecto`             MODIFY `id_proyecto`     int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
ALTER TABLE `pago`                 MODIFY `id_pago`         int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
ALTER TABLE `factura`              MODIFY `id_factura`      int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
ALTER TABLE `detalle_factura`      MODIFY `id_detalle`      int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `entregable`           MODIFY `id_entregable`   int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `incidencia`           MODIFY `id_incidencia`   int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `solicitud`            MODIFY `id_solicitud`    int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `reporte`              MODIFY `id_reporte`      int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `notificaciones`       MODIFY `id`              int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `soporte_notificacion` MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `password_reset_token` MODIFY `id`              int(11) NOT NULL AUTO_INCREMENT;

-- ============================================================
-- FOREIGN KEYS
-- ============================================================

ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_cliente_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

ALTER TABLE `proyecto`
  ADD CONSTRAINT `fk_proyecto_cliente`
    FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON UPDATE CASCADE;

ALTER TABLE `proyecto_servicio`
  ADD CONSTRAINT `fk_ps_proyecto`
    FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ps_servicio`
    FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`) ON UPDATE CASCADE;

ALTER TABLE `proyecto_usuario`
  ADD CONSTRAINT `fk_pu_proyecto`
    FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pu_usuario`
    FOREIGN KEY (`id_usuario`)  REFERENCES `usuario`  (`id_usuario`)  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `pago`
  ADD CONSTRAINT `fk_pago_proyecto`
    FOREIGN KEY (`id_proyecto`)    REFERENCES `proyecto` (`id_proyecto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pago_usuario`
    FOREIGN KEY (`registrado_por`) REFERENCES `usuario`  (`id_usuario`)  ON UPDATE CASCADE;

ALTER TABLE `factura`
  ADD CONSTRAINT `fk_factura_proyecto`
    FOREIGN KEY (`id_proyecto`)  REFERENCES `proyecto` (`id_proyecto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_cliente`
    FOREIGN KEY (`id_cliente`)   REFERENCES `cliente`  (`id_cliente`)  ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_factura_usuario`
    FOREIGN KEY (`generada_por`) REFERENCES `usuario`  (`id_usuario`)  ON UPDATE CASCADE;

ALTER TABLE `detalle_factura`
  ADD CONSTRAINT `fk_detalle_factura`
    FOREIGN KEY (`id_factura`)  REFERENCES `factura`  (`id_factura`)  ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_servicio`
    FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`) ON UPDATE CASCADE;

ALTER TABLE `entregable`
  ADD CONSTRAINT `fk_entregable_proyecto`
    FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `incidencia`
  ADD CONSTRAINT `fk_incidencia_proyecto`
    FOREIGN KEY (`id_proyecto`)        REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_incidencia_usuario`
    FOREIGN KEY (`id_usuario_reporta`) REFERENCES `usuario`  (`id_usuario`)  ON UPDATE CASCADE;

ALTER TABLE `solicitud`
  ADD CONSTRAINT `fk_solicitud_proyecto`
    FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_solicitud_usuario`
    FOREIGN KEY (`id_usuario`)  REFERENCES `usuario`  (`id_usuario`)  ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `reporte`
  ADD CONSTRAINT `fk_reporte_usuario`
    FOREIGN KEY (`generado_por`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificaciones_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `soporte_notificacion`
  ADD CONSTRAINT `fk_soporte_notificacion_incidencia`
    FOREIGN KEY (`id_incidencia`) REFERENCES `incidencia` (`id_incidencia`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `auditoria`
  ADD CONSTRAINT `fk_auditoria_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `calificaciones`
  ADD CONSTRAINT `fk_calif_proyecto`
    FOREIGN KEY (`id_proyecto`) REFERENCES `proyecto` (`id_proyecto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_calif_cliente`
    FOREIGN KEY (`id_cliente`)  REFERENCES `cliente`  (`id_cliente`)  ON DELETE CASCADE ON UPDATE CASCADE;



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;