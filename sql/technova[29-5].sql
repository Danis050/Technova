-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 29, 2026 at 10:14 PM
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

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
