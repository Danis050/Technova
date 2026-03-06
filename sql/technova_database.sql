-- ============================================================
-- BASE DE DATOS: TechNova Servicios Tecnológicos
-- Equipo: Aevum | Grupo 9 | AMDS ciclo I-2026
-- Motor: MySQL 8.0+
-- ============================================================

-- Crear y seleccionar la base de datos
CREATE DATABASE IF NOT EXISTS technova_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE technova_db;

-- ============================================================
-- TABLA: usuario
-- Control de acceso con roles diferenciados
-- Roles: Administrador, Diseñador, Desarrollador, Cliente
-- ============================================================
CREATE TABLE usuario (
    id_usuario    INT             NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(100)    NOT NULL,
    apellido      VARCHAR(100)    NOT NULL,
    email         VARCHAR(150)    NOT NULL UNIQUE,
    password_hash VARCHAR(255)    NOT NULL COMMENT 'Contraseña encriptada con bcrypt',
    rol           ENUM('Administrador','Diseñador','Desarrollador','Cliente') NOT NULL,
    estado        TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
    creado_en     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario)
) ENGINE=InnoDB COMMENT='Gestión de acceso con roles diferenciados';


-- ============================================================
-- TABLA: cliente
-- Registro de clientes de TechNova (con datos para facturación)
-- ============================================================
CREATE TABLE cliente (
    id_cliente      INT             NOT NULL AUTO_INCREMENT,
    id_usuario      INT             NOT NULL COMMENT 'FK → usuario con rol Cliente',
    nombre_empresa  VARCHAR(150)    NOT NULL,
    telefono        VARCHAR(20),
    direccion       VARCHAR(255),
    nit             VARCHAR(30)     COMMENT 'Para facturación electrónica',
    nrc             VARCHAR(30)     COMMENT 'Para facturación electrónica',
    registrado_en   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_cliente),
    CONSTRAINT fk_cliente_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Registro de clientes de TechNova';


-- ============================================================
-- TABLA: servicio
-- Catálogo de servicios ofrecidos por TechNova
-- ============================================================
CREATE TABLE servicio (
    id_servicio   INT             NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(100)    NOT NULL,
    descripcion   TEXT,
    precio_base   DECIMAL(10,2)   NOT NULL,
    activo        TINYINT(1)      NOT NULL DEFAULT 1,
    PRIMARY KEY (id_servicio)
) ENGINE=InnoDB COMMENT='Catálogo de servicios ofrecidos por TechNova';


-- ============================================================
-- TABLA: proyecto
-- Gestión de proyectos (RN: inicia solo con anticipo registrado)
-- ============================================================
CREATE TABLE proyecto (
    id_proyecto     INT             NOT NULL AUTO_INCREMENT,
    id_cliente      INT             NOT NULL,
    nombre          VARCHAR(150)    NOT NULL,
    descripcion     TEXT,
    fecha_inicio    DATE,
    fecha_entrega   DATE,
    estado          ENUM('Pendiente','En Proceso','Completado','Cancelado')
                                    NOT NULL DEFAULT 'Pendiente',
    anticipo_pagado TINYINT(1)      NOT NULL DEFAULT 0
                    COMMENT 'RB-01: El proyecto inicia solo si anticipo=1',
    creado_en       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_proyecto),
    CONSTRAINT fk_proyecto_cliente
        FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='RN: Proyecto no inicia sin anticipo registrado';


-- ============================================================
-- TABLA: proyecto_servicio  (relación N:M)
-- Un proyecto puede incluir múltiples servicios
-- ============================================================
CREATE TABLE proyecto_servicio (
    id_proyecto         INT             NOT NULL,
    id_servicio         INT             NOT NULL,
    cantidad            INT             NOT NULL DEFAULT 1,
    precio_acordado     DECIMAL(10,2)   NOT NULL
                        COMMENT 'Precio pactado puede diferir del precio base',
    PRIMARY KEY (id_proyecto, id_servicio),
    CONSTRAINT fk_ps_proyecto
        FOREIGN KEY (id_proyecto) REFERENCES proyecto(id_proyecto)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_ps_servicio
        FOREIGN KEY (id_servicio) REFERENCES servicio(id_servicio)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Relación N:M entre proyectos y servicios';


-- ============================================================
-- TABLA: pago
-- Control de anticipos y pagos por proyecto
-- ============================================================
CREATE TABLE pago (
    id_pago         INT             NOT NULL AUTO_INCREMENT,
    id_proyecto     INT             NOT NULL,
    tipo_pago       ENUM('Anticipo','Parcial','Saldo Final') NOT NULL,
    monto           DECIMAL(10,2)   NOT NULL,
    fecha_pago      DATE            NOT NULL,
    metodo_pago     ENUM('Efectivo','Transferencia','Cheque') NOT NULL,
    comprobante     VARCHAR(255)    COMMENT 'Número o ruta del comprobante',
    registrado_por  INT             NOT NULL COMMENT 'FK → usuario que registra el pago',
    creado_en       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_pago),
    CONSTRAINT fk_pago_proyecto
        FOREIGN KEY (id_proyecto) REFERENCES proyecto(id_proyecto)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_pago_usuario
        FOREIGN KEY (registrado_por) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Control de pagos y anticipos por proyecto';


-- ============================================================
-- TABLA: factura  (Facturación Electrónica — OBLIGATORIA)
-- ============================================================
CREATE TABLE factura (
    id_factura      INT             NOT NULL AUTO_INCREMENT,
    id_proyecto     INT             NOT NULL,
    id_cliente      INT             NOT NULL,
    numero_factura  VARCHAR(50)     NOT NULL UNIQUE COMMENT 'Correlativo electrónico único',
    fecha_emision   DATE            NOT NULL,
    subtotal        DECIMAL(10,2)   NOT NULL,
    iva             DECIMAL(10,2)   NOT NULL COMMENT 'IVA 13% El Salvador',
    total           DECIMAL(10,2)   NOT NULL,
    estado          ENUM('Pendiente','Pagada','Anulada') NOT NULL DEFAULT 'Pendiente',
    generada_por    INT             NOT NULL COMMENT 'FK → usuario que genera la factura',
    creado_en       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_factura),
    CONSTRAINT fk_factura_proyecto
        FOREIGN KEY (id_proyecto) REFERENCES proyecto(id_proyecto)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_factura_cliente
        FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_factura_usuario
        FOREIGN KEY (generada_por) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Módulo de facturación electrónica obligatorio';


-- ============================================================
-- TABLA: detalle_factura
-- Líneas de detalle de cada factura
-- ============================================================
CREATE TABLE detalle_factura (
    id_detalle      INT             NOT NULL AUTO_INCREMENT,
    id_factura      INT             NOT NULL,
    id_servicio     INT             NOT NULL,
    descripcion     VARCHAR(255)    NOT NULL,
    cantidad        INT             NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2)   NOT NULL,
    subtotal        DECIMAL(10,2)   NOT NULL,
    PRIMARY KEY (id_detalle),
    CONSTRAINT fk_detalle_factura
        FOREIGN KEY (id_factura) REFERENCES factura(id_factura)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detalle_servicio
        FOREIGN KEY (id_servicio) REFERENCES servicio(id_servicio)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Líneas de detalle de cada factura';


-- ============================================================
-- TABLA: reporte
-- Trazabilidad de los 3 reportes administrativos requeridos
-- ============================================================
CREATE TABLE reporte (
    id_reporte          INT         NOT NULL AUTO_INCREMENT,
    tipo                ENUM('Pagos','Proyectos','Facturación') NOT NULL
                        COMMENT 'Mínimo 3 tipos de reporte requeridos',
    generado_por        INT         NOT NULL,
    fecha_generacion    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    parametros          TEXT        COMMENT 'Filtros usados, almacenados en JSON',
    archivo_url         VARCHAR(255) COMMENT 'Ruta del reporte exportado',
    PRIMARY KEY (id_reporte),
    CONSTRAINT fk_reporte_usuario
        FOREIGN KEY (generado_por) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB COMMENT='Registro de los 3 reportes administrativos obligatorios';


-- ============================================================
-- DATOS DE PRUEBA (INSERT)
-- ============================================================

-- Usuarios
INSERT INTO usuario (nombre, apellido, email, password_hash, rol) VALUES
('Carlos',   'Lobos Soriano',    'carlos.lobos@technova.com',    '$2b$10$ejemplo_hash_admin',  'Administrador'),
('Misael',   'Juárez Reyes',     'misael.juarez@technova.com',   '$2b$10$ejemplo_hash_dev1',   'Desarrollador'),
('Kevin',    'Benavides Ruiz',   'kevin.benavides@technova.com', '$2b$10$ejemplo_hash_dev2',   'Desarrollador'),
('Luisa',    'Alvarez Tejada',   'luisa.alvarez@technova.com',   '$2b$10$ejemplo_hash_dis',    'Diseñador'),
('Juan',     'Pérez García',     'juan.perez@cliente.com',       '$2b$10$ejemplo_hash_cli',    'Cliente');

-- Cliente
INSERT INTO cliente (id_usuario, nombre_empresa, telefono, direccion, nit, nrc) VALUES
(5, 'Empresa Pérez S.A. de C.V.', '7890-1234', 'San Miguel, El Salvador', '0614-190185-101-6', '12345-6');

-- Servicios
INSERT INTO servicio (nombre, descripcion, precio_base) VALUES
('Desarrollo de Software a Medida', 'Sistema web personalizado según requerimientos del cliente', 2500.00),
('Diseño Gráfico',                  'Diseño de identidad visual, logos y material publicitario',  800.00),
('Venta de Demo (Software Base)',    'Software base adaptable a distintos rubros',                1200.00),
('Curso de JavaScript',             'Curso presencial/virtual de JavaScript desde cero',          350.00);

-- Proyecto
INSERT INTO proyecto (id_cliente, nombre, descripcion, fecha_inicio, fecha_entrega, estado, anticipo_pagado) VALUES
(1, 'Sistema de Facturación Web', 'Desarrollo de sistema de facturación electrónica para la empresa', '2026-02-14', '2026-05-14', 'En Proceso', 1);

-- Proyecto_Servicio
INSERT INTO proyecto_servicio (id_proyecto, id_servicio, cantidad, precio_acordado) VALUES
(1, 1, 1, 2500.00),
(1, 2, 1,  700.00);

-- Pago (anticipo)
INSERT INTO pago (id_proyecto, tipo_pago, monto, fecha_pago, metodo_pago, comprobante, registrado_por) VALUES
(1, 'Anticipo', 1600.00, '2026-02-14', 'Transferencia', 'TRF-2026-0001', 1);

-- Factura
INSERT INTO factura (id_proyecto, id_cliente, numero_factura, fecha_emision, subtotal, iva, total, estado, generada_por) VALUES
(1, 1, 'FAC-2026-0001', '2026-02-14', 3200.00, 416.00, 3616.00, 'Pendiente', 1);

-- Detalle Factura
INSERT INTO detalle_factura (id_factura, id_servicio, descripcion, cantidad, precio_unitario, subtotal) VALUES
(1, 1, 'Desarrollo de Sistema de Facturación Web', 1, 2500.00, 2500.00),
(1, 2, 'Diseño de identidad visual del sistema',   1,  700.00,  700.00);

-- Reporte
INSERT INTO reporte (tipo, generado_por, parametros) VALUES
('Proyectos',    1, '{"estado":"En Proceso","fecha_desde":"2026-01-01"}'),
('Pagos',        1, '{"tipo_pago":"Anticipo","mes":"02","anio":"2026"}'),
('Facturación',  1, '{"estado":"Pendiente","mes":"02","anio":"2026"}');


-- ============================================================
-- VISTAS ÚTILES
-- ============================================================

-- Vista: resumen de proyectos con cliente
CREATE OR REPLACE VIEW v_proyectos_clientes AS
SELECT
    p.id_proyecto,
    p.nombre            AS proyecto,
    p.estado,
    p.fecha_entrega,
    p.anticipo_pagado,
    c.nombre_empresa    AS cliente,
    u.email             AS email_cliente
FROM proyecto p
JOIN cliente c  ON p.id_cliente  = c.id_cliente
JOIN usuario u  ON c.id_usuario  = u.id_usuario;

-- Vista: total pagado por proyecto
CREATE OR REPLACE VIEW v_pagos_por_proyecto AS
SELECT
    p.id_proyecto,
    pr.nombre           AS proyecto,
    SUM(p.monto)        AS total_pagado,
    COUNT(p.id_pago)    AS num_pagos
FROM pago p
JOIN proyecto pr ON p.id_proyecto = pr.id_proyecto
GROUP BY p.id_proyecto, pr.nombre;

-- Vista: facturas con datos del cliente
CREATE OR REPLACE VIEW v_facturas_detalle AS
SELECT
    f.numero_factura,
    f.fecha_emision,
    f.subtotal,
    f.iva,
    f.total,
    f.estado            AS estado_factura,
    c.nombre_empresa    AS cliente,
    pr.nombre           AS proyecto
FROM factura f
JOIN cliente c   ON f.id_cliente  = c.id_cliente
JOIN proyecto pr ON f.id_proyecto = pr.id_proyecto;
