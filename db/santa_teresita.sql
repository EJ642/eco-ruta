-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-07-2026 a las 12:47:17
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `santa_teresita`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno`
--

CREATE TABLE `alumno` (
  `idAlumno` int(11) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL COMMENT 'Puede no tener CI si es menor',
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nac` date NOT NULL,
  `sexo` enum('M','F') NOT NULL,
  `direccion` varchar(250) DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Egresado','Retirado') NOT NULL DEFAULT 'Activo',
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Datos personales del alumno';

--
-- RELACIONES PARA LA TABLA `alumno`:
--

--
-- Volcado de datos para la tabla `alumno`
--

INSERT INTO `alumno` (`idAlumno`, `cedula`, `nombres`, `apellidos`, `fecha_nac`, `sexo`, `direccion`, `estado`, `creado`, `modificado`) VALUES
(1, '5001001', 'Ana Sofía', 'Rodríguez Martínez', '2009-03-12', 'F', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(2, '5001002', 'Luis Fernando', 'González Pérez', '2009-07-25', 'M', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(3, '5001003', 'María José', 'López Sánchez', '2009-11-08', 'F', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(4, '5001004', 'Diego Alexis', 'Benítez Torres', '2009-01-30', 'M', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(5, '5001005', 'Valentina', 'Cabrera Duarte', '2009-05-17', 'F', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(6, '5001006', 'Rodrigo Javier', 'Ortiz Ramírez', '2009-09-04', 'M', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(7, '5001007', 'Luciana Belen', 'Herrera Villalba', '2009-02-21', 'F', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(8, '5001008', 'Sebastián', 'Morales Acosta', '2009-06-14', 'M', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(9, '5001009', 'Camila Paola', 'Vera Aquino', '2008-04-03', 'F', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(10, '5001010', 'Matías Eduardo', 'Fernández Ríos', '2008-08-19', 'M', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(11, '5001011', 'Agustina', 'Paredes Leiva', '2008-12-27', 'F', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(12, '5001012', 'Nicolás Ariel', 'Cardozo Mendoza', '2008-03-08', 'M', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(13, '5001013', 'Florencia', 'Núñez Castro', '2008-07-22', 'F', NULL, 'Activo', '2026-05-14 10:19:55', '2026-05-14 10:19:55'),
(14, '1233256', 'Paola Patricia', 'Oviedo', '0000-00-00', 'M', NULL, 'Activo', '2026-05-14 10:23:20', '2026-05-14 10:23:20'),
(16, '4500488', 'Maria José', 'Gonzalez Perez', '2010-03-05', 'M', 'Avda. Eusebio Ayala 654', 'Activo', '2026-06-25 09:14:31', '2026-06-25 09:15:22'),
(17, '7548985', 'Antonio Cirulo', 'Ortiz Gill', '2007-06-22', 'M', '', 'Activo', '2026-06-30 15:58:59', '2026-06-30 15:58:59'),
(18, '7015487', 'Maria Serena', 'Ortiz Chavez', '2007-06-01', 'F', '', 'Activo', '2026-06-30 16:01:55', '2026-06-30 16:01:55'),
(19, '7058859', 'Alberto', 'Valenzuela', '2007-06-01', 'M', '', 'Activo', '2026-06-30 16:03:31', '2026-06-30 16:03:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno_tutor`
--

CREATE TABLE `alumno_tutor` (
  `idAlumnoTutor` int(11) NOT NULL,
  `idAlumno` int(11) NOT NULL,
  `idTutor` int(11) NOT NULL,
  `es_principal` enum('Sí','No') NOT NULL COMMENT 'Tutor principal de contacto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Relación alumno-tutor (un alumno puede tener varios tutores)';

--
-- RELACIONES PARA LA TABLA `alumno_tutor`:
--   `idAlumno`
--       `alumno` -> `idAlumno`
--   `idTutor`
--       `tutor` -> `idTutor`
--

--
-- Volcado de datos para la tabla `alumno_tutor`
--

INSERT INTO `alumno_tutor` (`idAlumnoTutor`, `idAlumno`, `idTutor`, `es_principal`) VALUES
(1, 1, 1, 'Sí'),
(2, 1, 2, 'Sí'),
(3, 2, 3, 'Sí'),
(9, 4, 1, 'No');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anio_lectivo`
--

CREATE TABLE `anio_lectivo` (
  `idAnio` int(11) NOT NULL,
  `anio` year(4) NOT NULL COMMENT 'Ej: 2026',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` enum('Sí','No') NOT NULL COMMENT 'Solo uno activo a la vez'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Años lectivos';

--
-- RELACIONES PARA LA TABLA `anio_lectivo`:
--

--
-- Volcado de datos para la tabla `anio_lectivo`
--

INSERT INTO `anio_lectivo` (`idAnio`, `anio`, `fecha_inicio`, `fecha_fin`, `activo`) VALUES
(1, '2026', '2026-02-16', '2026-11-30', 'Sí');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_detalle`
--

CREATE TABLE `asistencia_detalle` (
  `idDetalle` int(11) NOT NULL,
  `idSesion` int(11) NOT NULL COMMENT 'Sesión a la que pertenece este detalle',
  `idMatricula` int(11) NOT NULL COMMENT 'Alumno matriculado',
  `estado` enum('Presente','Ausente','Tardanza','Justificado') NOT NULL DEFAULT 'Presente',
  `observacion` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detalle de asistencia: estado de cada alumno por sesión';

--
-- RELACIONES PARA LA TABLA `asistencia_detalle`:
--   `idMatricula`
--       `matricula` -> `idMatricula`
--   `idSesion`
--       `asistencia_sesion` -> `idSesion`
--

--
-- Volcado de datos para la tabla `asistencia_detalle`
--

INSERT INTO `asistencia_detalle` (`idDetalle`, `idSesion`, `idMatricula`, `estado`, `observacion`) VALUES
(7, 3, 4, 'Presente', ''),
(8, 3, 5, 'Presente', ''),
(9, 3, 2, 'Presente', ''),
(10, 3, 3, 'Ausente', ''),
(11, 3, 14, 'Ausente', ''),
(12, 3, 1, 'Presente', ''),
(13, 4, 10, 'Presente', ''),
(14, 4, 11, 'Presente', ''),
(15, 4, 9, 'Presente', ''),
(16, 5, 4, 'Presente', ''),
(17, 5, 5, 'Presente', ''),
(18, 5, 2, 'Presente', ''),
(19, 5, 3, 'Presente', ''),
(20, 5, 14, 'Presente', ''),
(21, 5, 1, 'Presente', ''),
(28, 6, 4, 'Presente', ''),
(29, 6, 5, 'Presente', ''),
(30, 6, 2, 'Presente', ''),
(31, 6, 3, 'Presente', ''),
(32, 6, 14, 'Presente', ''),
(33, 6, 1, 'Presente', ''),
(46, 8, 4, 'Presente', ''),
(47, 8, 5, 'Presente', ''),
(48, 8, 2, 'Presente', ''),
(49, 8, 3, 'Presente', ''),
(50, 8, 14, 'Presente', ''),
(51, 8, 1, 'Presente', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_sesion`
--

CREATE TABLE `asistencia_sesion` (
  `idSesion` int(11) NOT NULL,
  `idAulaMateria` int(11) NOT NULL COMMENT 'Aula + Materia de la sesión',
  `fecha` date NOT NULL COMMENT 'Fecha de la clase',
  `cantidad_horas` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Horas dictadas en esta sesión',
  `registrado_por` int(11) NOT NULL COMMENT 'Docente que registró (FK → usuarios)',
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cabecera de asistencia: una fila por sesión de clase dictada';

--
-- RELACIONES PARA LA TABLA `asistencia_sesion`:
--   `idAulaMateria`
--       `aula_materia` -> `idAulaMateria`
--   `registrado_por`
--       `usuarios` -> `idUsuario`
--

--
-- Volcado de datos para la tabla `asistencia_sesion`
--

INSERT INTO `asistencia_sesion` (`idSesion`, `idAulaMateria`, `fecha`, `cantidad_horas`, `registrado_por`, `creado`, `modificado`) VALUES
(3, 1, '2026-06-22', 1, 4, '2026-06-22 16:24:05', '2026-06-22 16:24:43'),
(4, 7, '2026-06-18', 6, 4, '2026-06-22 17:06:26', '2026-06-22 17:06:26'),
(5, 13, '2026-06-23', 1, 4, '2026-06-23 10:31:47', '2026-06-23 10:31:47'),
(6, 13, '2026-06-25', 1, 4, '2026-06-25 15:48:36', '2026-06-25 15:57:49'),
(8, 2, '2026-07-01', 1, 4, '2026-07-01 10:01:52', '2026-07-01 10:01:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_matricula`
--

CREATE TABLE `auditoria_matricula` (
  `idAuditoria` int(11) NOT NULL,
  `idMatricula` int(11) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `estado_antes` varchar(50) DEFAULT NULL,
  `estado_despues` varchar(50) DEFAULT NULL,
  `idUsuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL,
  `detalle` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de cambios en matrículas';

--
-- RELACIONES PARA LA TABLA `auditoria_matricula`:
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_nota`
--

CREATE TABLE `auditoria_nota` (
  `idAuditoria` int(11) NOT NULL,
  `idNota` int(11) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `valor_antes` decimal(4,2) DEFAULT NULL,
  `valor_despues` decimal(4,2) DEFAULT NULL,
  `campo` varchar(50) DEFAULT NULL COMMENT 'Campo modificado',
  `idUsuario` int(11) NOT NULL COMMENT 'Quién realizó la acción',
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL COMMENT 'IP de la sesión (IPv4 o IPv6)',
  `detalle` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de cambios en calificaciones';

--
-- RELACIONES PARA LA TABLA `auditoria_nota`:
--

--
-- Volcado de datos para la tabla `auditoria_nota`
--

INSERT INTO `auditoria_nota` (`idAuditoria`, `idNota`, `accion`, `valor_antes`, `valor_despues`, `campo`, `idUsuario`, `fecha`, `ip`, `detalle`) VALUES
(1, 1, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-06-22 16:31:25', '::1', 'guardar_calificaciones_lote'),
(2, 2, 'INSERT', NULL, 12.00, 'puntos_obtenidos', 4, '2026-06-22 16:31:25', '::1', 'guardar_calificaciones_lote'),
(3, 3, 'INSERT', NULL, 11.00, 'puntos_obtenidos', 4, '2026-06-22 16:31:25', '::1', 'guardar_calificaciones_lote'),
(4, 4, 'INSERT', NULL, 9.00, 'puntos_obtenidos', 4, '2026-06-22 16:31:25', '::1', 'guardar_calificaciones_lote'),
(5, 5, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-06-22 16:31:25', '::1', 'guardar_calificaciones_lote'),
(6, 6, 'INSERT', NULL, 0.00, 'puntos_obtenidos', 4, '2026-06-22 16:31:54', '::1', 'editar_calificaciones_lote'),
(7, 2, 'UPDATE', 12.00, 14.00, 'puntos_obtenidos', 4, '2026-06-22 16:36:59', '::1', 'editar_calificaciones_lote'),
(8, 7, 'INSERT', NULL, 12.00, 'puntos_obtenidos', 4, '2026-06-22 16:54:44', '::1', 'guardar_calificaciones_lote'),
(9, 8, 'INSERT', NULL, 12.00, 'puntos_obtenidos', 4, '2026-06-22 16:54:44', '::1', 'guardar_calificaciones_lote'),
(10, 9, 'INSERT', NULL, 11.00, 'puntos_obtenidos', 4, '2026-06-22 16:54:45', '::1', 'guardar_calificaciones_lote'),
(11, 10, 'INSERT', NULL, 10.00, 'puntos_obtenidos', 4, '2026-06-22 16:54:45', '::1', 'guardar_calificaciones_lote'),
(12, 11, 'INSERT', NULL, 11.00, 'puntos_obtenidos', 4, '2026-06-22 16:54:45', '::1', 'guardar_calificaciones_lote'),
(13, 12, 'INSERT', NULL, 10.00, 'puntos_obtenidos', 4, '2026-06-22 16:54:45', '::1', 'guardar_calificaciones_lote'),
(14, 9, 'UPDATE', 11.00, 5.00, 'puntos_obtenidos', 4, '2026-06-22 16:59:43', '192.168.0.5', 'editar_calificaciones_lote'),
(15, 13, 'INSERT', NULL, 15.00, 'puntos_obtenidos', 4, '2026-06-29 15:43:19', '::1', 'guardar_calificaciones_lote'),
(16, 14, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-06-29 15:43:19', '::1', 'guardar_calificaciones_lote'),
(17, 15, 'INSERT', NULL, 14.00, 'puntos_obtenidos', 4, '2026-06-29 15:43:19', '::1', 'guardar_calificaciones_lote'),
(18, 16, 'INSERT', NULL, 12.00, 'puntos_obtenidos', 4, '2026-06-29 15:43:19', '::1', 'guardar_calificaciones_lote'),
(19, 17, 'INSERT', NULL, 10.00, 'puntos_obtenidos', 4, '2026-06-29 15:43:19', '::1', 'guardar_calificaciones_lote'),
(20, 18, 'INSERT', NULL, 7.00, 'puntos_obtenidos', 4, '2026-06-29 15:43:19', '::1', 'guardar_calificaciones_lote'),
(21, 13, 'UPDATE', 15.00, 18.00, 'puntos_obtenidos', 4, '2026-06-29 15:44:19', '::1', 'editar_calificaciones_lote'),
(22, 15, 'UPDATE', 14.00, 7.00, 'puntos_obtenidos', 4, '2026-06-29 15:44:19', '::1', 'editar_calificaciones_lote'),
(23, 19, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 09:57:25', '::1', 'guardar_calificaciones_lote'),
(24, 20, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 09:57:25', '::1', 'guardar_calificaciones_lote'),
(25, 21, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 09:57:26', '::1', 'guardar_calificaciones_lote'),
(26, 22, 'INSERT', NULL, 17.00, 'puntos_obtenidos', 4, '2026-07-01 09:57:26', '::1', 'guardar_calificaciones_lote'),
(27, 23, 'INSERT', NULL, 15.00, 'puntos_obtenidos', 4, '2026-07-01 09:57:26', '::1', 'guardar_calificaciones_lote'),
(28, 24, 'INSERT', NULL, 8.00, 'puntos_obtenidos', 4, '2026-07-01 09:57:26', '::1', 'guardar_calificaciones_lote'),
(29, 25, 'INSERT', NULL, 14.00, 'puntos_obtenidos', 4, '2026-07-01 09:59:45', '::1', 'guardar_calificaciones_lote'),
(30, 26, 'INSERT', NULL, 10.00, 'puntos_obtenidos', 4, '2026-07-01 09:59:45', '::1', 'guardar_calificaciones_lote'),
(31, 27, 'INSERT', NULL, 7.00, 'puntos_obtenidos', 4, '2026-07-01 09:59:45', '::1', 'guardar_calificaciones_lote'),
(32, 28, 'INSERT', NULL, 16.00, 'puntos_obtenidos', 4, '2026-07-01 09:59:46', '::1', 'guardar_calificaciones_lote'),
(33, 29, 'INSERT', NULL, 17.00, 'puntos_obtenidos', 4, '2026-07-01 09:59:46', '::1', 'guardar_calificaciones_lote'),
(34, 30, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 09:59:46', '::1', 'guardar_calificaciones_lote'),
(35, 31, 'INSERT', NULL, 58.00, 'puntos_obtenidos', 4, '2026-07-01 10:00:55', '::1', 'guardar_calificaciones_lote'),
(36, 32, 'INSERT', NULL, 58.00, 'puntos_obtenidos', 4, '2026-07-01 10:00:55', '::1', 'guardar_calificaciones_lote'),
(37, 33, 'INSERT', NULL, 58.00, 'puntos_obtenidos', 4, '2026-07-01 10:00:55', '::1', 'guardar_calificaciones_lote'),
(38, 34, 'INSERT', NULL, 45.00, 'puntos_obtenidos', 4, '2026-07-01 10:00:55', '::1', 'guardar_calificaciones_lote'),
(39, 35, 'INSERT', NULL, 45.00, 'puntos_obtenidos', 4, '2026-07-01 10:00:55', '::1', 'guardar_calificaciones_lote'),
(40, 36, 'INSERT', NULL, 45.00, 'puntos_obtenidos', 4, '2026-07-01 10:00:55', '::1', 'guardar_calificaciones_lote'),
(41, 37, 'INSERT', NULL, 14.00, 'puntos_obtenidos', 4, '2026-07-01 10:03:28', '::1', 'guardar_calificaciones_lote'),
(42, 38, 'INSERT', NULL, 14.00, 'puntos_obtenidos', 4, '2026-07-01 10:03:28', '::1', 'guardar_calificaciones_lote'),
(43, 39, 'INSERT', NULL, 15.00, 'puntos_obtenidos', 4, '2026-07-01 10:03:29', '::1', 'guardar_calificaciones_lote'),
(44, 40, 'INSERT', NULL, 11.00, 'puntos_obtenidos', 4, '2026-07-01 10:03:29', '::1', 'guardar_calificaciones_lote'),
(45, 41, 'INSERT', NULL, 5.00, 'puntos_obtenidos', 4, '2026-07-01 10:03:29', '::1', 'guardar_calificaciones_lote'),
(46, 42, 'INSERT', NULL, 7.00, 'puntos_obtenidos', 4, '2026-07-01 10:03:29', '::1', 'guardar_calificaciones_lote'),
(47, 43, 'INSERT', NULL, 30.00, 'puntos_obtenidos', 4, '2026-07-01 10:06:08', '::1', 'guardar_calificaciones_lote'),
(48, 44, 'INSERT', NULL, 30.00, 'puntos_obtenidos', 4, '2026-07-01 10:06:08', '::1', 'guardar_calificaciones_lote'),
(49, 45, 'INSERT', NULL, 30.00, 'puntos_obtenidos', 4, '2026-07-01 10:06:08', '::1', 'guardar_calificaciones_lote'),
(50, 46, 'INSERT', NULL, 30.00, 'puntos_obtenidos', 4, '2026-07-01 10:06:08', '::1', 'guardar_calificaciones_lote'),
(51, 47, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:06:08', '::1', 'guardar_calificaciones_lote'),
(52, 48, 'INSERT', NULL, 30.00, 'puntos_obtenidos', 4, '2026-07-01 10:06:08', '::1', 'guardar_calificaciones_lote'),
(53, 49, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:07:35', '::1', 'guardar_calificaciones_lote'),
(54, 50, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:07:36', '::1', 'guardar_calificaciones_lote'),
(55, 51, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:07:36', '::1', 'guardar_calificaciones_lote'),
(56, 52, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:07:36', '::1', 'guardar_calificaciones_lote'),
(57, 53, 'INSERT', NULL, 23.00, 'puntos_obtenidos', 4, '2026-07-01 10:07:36', '::1', 'guardar_calificaciones_lote'),
(58, 54, 'INSERT', NULL, 22.00, 'puntos_obtenidos', 4, '2026-07-01 10:07:36', '::1', 'guardar_calificaciones_lote'),
(59, 55, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:10:25', '::1', 'guardar_calificaciones_lote'),
(60, 56, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:10:25', '::1', 'guardar_calificaciones_lote'),
(61, 57, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:10:26', '::1', 'guardar_calificaciones_lote'),
(62, 58, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:10:26', '::1', 'guardar_calificaciones_lote'),
(63, 59, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:10:26', '::1', 'guardar_calificaciones_lote'),
(64, 60, 'INSERT', NULL, 11.00, 'puntos_obtenidos', 4, '2026-07-01 10:10:26', '::1', 'guardar_calificaciones_lote'),
(65, 61, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:14:30', '::1', 'guardar_calificaciones_lote'),
(66, 62, 'INSERT', NULL, 15.00, 'puntos_obtenidos', 4, '2026-07-01 10:14:30', '::1', 'guardar_calificaciones_lote'),
(67, 63, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:14:30', '::1', 'guardar_calificaciones_lote'),
(68, 64, 'INSERT', NULL, 19.00, 'puntos_obtenidos', 4, '2026-07-01 10:14:30', '::1', 'guardar_calificaciones_lote'),
(69, 65, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:14:30', '::1', 'guardar_calificaciones_lote'),
(70, 66, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:14:30', '::1', 'guardar_calificaciones_lote'),
(71, 67, 'INSERT', NULL, 30.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:05', '::1', 'guardar_calificaciones_lote'),
(72, 68, 'INSERT', NULL, 30.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:05', '::1', 'guardar_calificaciones_lote'),
(73, 69, 'INSERT', NULL, 30.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:05', '::1', 'guardar_calificaciones_lote'),
(74, 70, 'INSERT', NULL, 30.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:05', '::1', 'guardar_calificaciones_lote'),
(75, 71, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:05', '::1', 'guardar_calificaciones_lote'),
(76, 72, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:06', '::1', 'guardar_calificaciones_lote'),
(77, 73, 'INSERT', NULL, 39.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:58', '::1', 'guardar_calificaciones_lote'),
(78, 74, 'INSERT', NULL, 38.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:58', '::1', 'guardar_calificaciones_lote'),
(79, 75, 'INSERT', NULL, 36.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:58', '::1', 'guardar_calificaciones_lote'),
(80, 76, 'INSERT', NULL, 34.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:58', '::1', 'guardar_calificaciones_lote'),
(81, 77, 'INSERT', NULL, 37.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:58', '::1', 'guardar_calificaciones_lote'),
(82, 78, 'INSERT', NULL, 40.00, 'puntos_obtenidos', 4, '2026-07-01 10:19:58', '::1', 'guardar_calificaciones_lote'),
(83, 79, 'INSERT', NULL, 40.00, 'puntos_obtenidos', 4, '2026-07-01 10:25:09', '::1', 'guardar_calificaciones_lote'),
(84, 80, 'INSERT', NULL, 40.00, 'puntos_obtenidos', 4, '2026-07-01 10:25:09', '::1', 'guardar_calificaciones_lote'),
(85, 81, 'INSERT', NULL, 35.00, 'puntos_obtenidos', 4, '2026-07-01 10:25:10', '::1', 'guardar_calificaciones_lote'),
(86, 82, 'INSERT', NULL, 36.00, 'puntos_obtenidos', 4, '2026-07-01 10:25:10', '::1', 'guardar_calificaciones_lote'),
(87, 83, 'INSERT', NULL, 39.00, 'puntos_obtenidos', 4, '2026-07-01 10:25:10', '::1', 'guardar_calificaciones_lote'),
(88, 84, 'INSERT', NULL, 39.00, 'puntos_obtenidos', 4, '2026-07-01 10:25:10', '::1', 'guardar_calificaciones_lote'),
(89, 85, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:26:20', '::1', 'guardar_calificaciones_lote'),
(90, 86, 'INSERT', NULL, 21.00, 'puntos_obtenidos', 4, '2026-07-01 10:26:20', '::1', 'guardar_calificaciones_lote'),
(91, 87, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:26:20', '::1', 'guardar_calificaciones_lote'),
(92, 88, 'INSERT', NULL, 23.00, 'puntos_obtenidos', 4, '2026-07-01 10:26:20', '::1', 'guardar_calificaciones_lote'),
(93, 89, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:26:20', '::1', 'guardar_calificaciones_lote'),
(94, 90, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:26:20', '::1', 'guardar_calificaciones_lote'),
(95, 91, 'INSERT', NULL, 15.00, 'puntos_obtenidos', 4, '2026-07-01 10:27:18', '::1', 'guardar_calificaciones_lote'),
(96, 92, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:27:18', '::1', 'guardar_calificaciones_lote'),
(97, 93, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:27:18', '::1', 'guardar_calificaciones_lote'),
(98, 94, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:27:18', '::1', 'guardar_calificaciones_lote'),
(99, 95, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:27:18', '::1', 'guardar_calificaciones_lote'),
(100, 96, 'INSERT', NULL, 14.00, 'puntos_obtenidos', 4, '2026-07-01 10:27:18', '::1', 'guardar_calificaciones_lote'),
(101, 97, 'INSERT', NULL, 40.00, 'puntos_obtenidos', 4, '2026-07-01 10:28:16', '::1', 'guardar_calificaciones_lote'),
(102, 98, 'INSERT', NULL, 40.00, 'puntos_obtenidos', 4, '2026-07-01 10:28:16', '::1', 'guardar_calificaciones_lote'),
(103, 99, 'INSERT', NULL, 15.00, 'puntos_obtenidos', 4, '2026-07-01 10:28:16', '::1', 'guardar_calificaciones_lote'),
(104, 100, 'INSERT', NULL, 39.00, 'puntos_obtenidos', 4, '2026-07-01 10:28:16', '::1', 'guardar_calificaciones_lote'),
(105, 101, 'INSERT', NULL, 36.00, 'puntos_obtenidos', 4, '2026-07-01 10:28:17', '::1', 'guardar_calificaciones_lote'),
(106, 102, 'INSERT', NULL, 35.00, 'puntos_obtenidos', 4, '2026-07-01 10:28:17', '::1', 'guardar_calificaciones_lote'),
(107, 103, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:31:57', '::1', 'guardar_calificaciones_lote'),
(108, 104, 'INSERT', NULL, 15.00, 'puntos_obtenidos', 4, '2026-07-01 10:31:57', '::1', 'guardar_calificaciones_lote'),
(109, 105, 'INSERT', NULL, 15.00, 'puntos_obtenidos', 4, '2026-07-01 10:31:57', '::1', 'guardar_calificaciones_lote'),
(110, 106, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:31:57', '::1', 'guardar_calificaciones_lote'),
(111, 107, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:31:57', '::1', 'guardar_calificaciones_lote'),
(112, 108, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:31:57', '::1', 'guardar_calificaciones_lote'),
(113, 109, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:33:40', '::1', 'guardar_calificaciones_lote'),
(114, 110, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:33:41', '::1', 'guardar_calificaciones_lote'),
(115, 111, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:33:41', '::1', 'guardar_calificaciones_lote'),
(116, 112, 'INSERT', NULL, 24.00, 'puntos_obtenidos', 4, '2026-07-01 10:33:41', '::1', 'guardar_calificaciones_lote'),
(117, 113, 'INSERT', NULL, 25.00, 'puntos_obtenidos', 4, '2026-07-01 10:33:41', '::1', 'guardar_calificaciones_lote'),
(118, 114, 'INSERT', NULL, 22.00, 'puntos_obtenidos', 4, '2026-07-01 10:33:41', '::1', 'guardar_calificaciones_lote'),
(119, 115, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:34:50', '::1', 'guardar_calificaciones_lote'),
(120, 116, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:34:50', '::1', 'guardar_calificaciones_lote'),
(121, 117, 'INSERT', NULL, 19.00, 'puntos_obtenidos', 4, '2026-07-01 10:34:50', '::1', 'guardar_calificaciones_lote'),
(122, 118, 'INSERT', NULL, 20.00, 'puntos_obtenidos', 4, '2026-07-01 10:34:50', '::1', 'guardar_calificaciones_lote'),
(123, 119, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:34:50', '::1', 'guardar_calificaciones_lote'),
(124, 120, 'INSERT', NULL, 17.00, 'puntos_obtenidos', 4, '2026-07-01 10:34:50', '::1', 'guardar_calificaciones_lote'),
(125, 121, 'INSERT', NULL, 38.00, 'puntos_obtenidos', 4, '2026-07-01 10:35:39', '::1', 'guardar_calificaciones_lote'),
(126, 122, 'INSERT', NULL, 38.00, 'puntos_obtenidos', 4, '2026-07-01 10:35:39', '::1', 'guardar_calificaciones_lote'),
(127, 123, 'INSERT', NULL, 40.00, 'puntos_obtenidos', 4, '2026-07-01 10:35:39', '::1', 'guardar_calificaciones_lote'),
(128, 124, 'INSERT', NULL, 40.00, 'puntos_obtenidos', 4, '2026-07-01 10:35:39', '::1', 'guardar_calificaciones_lote'),
(129, 125, 'INSERT', NULL, 40.00, 'puntos_obtenidos', 4, '2026-07-01 10:35:39', '::1', 'guardar_calificaciones_lote'),
(130, 126, 'INSERT', NULL, 38.00, 'puntos_obtenidos', 4, '2026-07-01 10:35:39', '::1', 'guardar_calificaciones_lote'),
(131, 127, 'INSERT', NULL, 19.00, 'puntos_obtenidos', 4, '2026-07-01 10:38:10', '::1', 'guardar_calificaciones_lote'),
(132, 128, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:38:10', '::1', 'guardar_calificaciones_lote'),
(133, 129, 'INSERT', NULL, 17.00, 'puntos_obtenidos', 4, '2026-07-01 10:38:10', '::1', 'guardar_calificaciones_lote'),
(134, 130, 'INSERT', NULL, 14.00, 'puntos_obtenidos', 4, '2026-07-01 10:38:10', '::1', 'guardar_calificaciones_lote'),
(135, 131, 'INSERT', NULL, 15.00, 'puntos_obtenidos', 4, '2026-07-01 10:38:11', '::1', 'guardar_calificaciones_lote'),
(136, 132, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:38:11', '::1', 'guardar_calificaciones_lote'),
(137, 133, 'INSERT', NULL, 10.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:24', '::1', 'guardar_calificaciones_lote'),
(138, 134, 'INSERT', NULL, 10.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:24', '::1', 'guardar_calificaciones_lote'),
(139, 135, 'INSERT', NULL, 10.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:24', '::1', 'guardar_calificaciones_lote'),
(140, 136, 'INSERT', NULL, 10.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:24', '::1', 'guardar_calificaciones_lote'),
(141, 137, 'INSERT', NULL, 10.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:24', '::1', 'guardar_calificaciones_lote'),
(142, 138, 'INSERT', NULL, 10.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:24', '::1', 'guardar_calificaciones_lote'),
(143, 139, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:48', '::1', 'guardar_calificaciones_lote'),
(144, 140, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:48', '::1', 'guardar_calificaciones_lote'),
(145, 141, 'INSERT', NULL, 18.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:48', '::1', 'guardar_calificaciones_lote'),
(146, 142, 'INSERT', NULL, 12.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:49', '::1', 'guardar_calificaciones_lote'),
(147, 143, 'INSERT', NULL, 17.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:49', '::1', 'guardar_calificaciones_lote'),
(148, 144, 'INSERT', NULL, 15.00, 'puntos_obtenidos', 4, '2026-07-01 10:39:49', '::1', 'guardar_calificaciones_lote');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_usuario`
--

CREATE TABLE `auditoria_usuario` (
  `idAuditoria` int(11) NOT NULL,
  `idUsuario_afectado` int(11) NOT NULL,
  `accion` enum('CREAR','MODIFICAR','DESACTIVAR','ACTIVAR','LOGIN','LOGOUT','CAMBIO_PASS') NOT NULL,
  `idUsuario_ejecutor` int(11) NOT NULL COMMENT 'Quién realizó la acción',
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL,
  `detalle` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de acciones sobre usuarios';

--
-- RELACIONES PARA LA TABLA `auditoria_usuario`:
--

--
-- Volcado de datos para la tabla `auditoria_usuario`
--

INSERT INTO `auditoria_usuario` (`idAuditoria`, `idUsuario_afectado`, `accion`, `idUsuario_ejecutor`, `fecha`, `ip`, `detalle`) VALUES
(1, 4, 'MODIFICAR', 4, '2026-06-29 15:58:45', '::1', 'Usuario actualizado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aula`
--

CREATE TABLE `aula` (
  `idAula` int(11) NOT NULL,
  `idAnio` int(11) NOT NULL,
  `idCurso` int(11) NOT NULL,
  `idEnfasis` int(11) NOT NULL,
  `activo` enum('Sí','No') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Aula = grupo de alumnos (Año + Curso + Sección + Énfasis). Ej: 1°A Ciencias 2026';

--
-- RELACIONES PARA LA TABLA `aula`:
--   `idAnio`
--       `anio_lectivo` -> `idAnio`
--   `idCurso`
--       `curso` -> `idCurso`
--   `idEnfasis`
--       `enfasis` -> `idEnfasis`
--

--
-- Volcado de datos para la tabla `aula`
--

INSERT INTO `aula` (`idAula`, `idAnio`, `idCurso`, `idEnfasis`, `activo`) VALUES
(1, 1, 1, 1, 'Sí'),
(2, 1, 1, 2, 'Sí'),
(3, 1, 2, 1, 'Sí'),
(4, 1, 2, 2, 'Sí'),
(5, 1, 3, 1, 'Sí'),
(6, 1, 3, 2, 'Sí');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aula_materia`
--

CREATE TABLE `aula_materia` (
  `idAulaMateria` int(11) NOT NULL,
  `idAula` int(11) NOT NULL,
  `idMateria` int(11) NOT NULL,
  `activo` enum('Sí','No') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Materias asignadas a cada aula';

--
-- RELACIONES PARA LA TABLA `aula_materia`:
--   `idAula`
--       `aula` -> `idAula`
--   `idMateria`
--       `materia` -> `idMateria`
--

--
-- Volcado de datos para la tabla `aula_materia`
--

INSERT INTO `aula_materia` (`idAulaMateria`, `idAula`, `idMateria`, `activo`) VALUES
(1, 1, 1, 'Sí'),
(2, 1, 2, 'Sí'),
(3, 1, 3, 'Sí'),
(4, 2, 1, 'Sí'),
(5, 2, 2, 'Sí'),
(6, 2, 3, 'Sí'),
(7, 3, 4, 'Sí'),
(8, 3, 5, 'Sí'),
(9, 4, 6, 'Sí'),
(10, 6, 15, 'Sí'),
(11, 6, 8, 'No'),
(13, 1, 16, 'Sí'),
(15, 1, 15, 'Sí');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso`
--

CREATE TABLE `curso` (
  `idCurso` int(11) NOT NULL,
  `numero` tinyint(1) NOT NULL COMMENT '1, 2 o 3 (Primer, Segundo, Tercer curso Media)',
  `nombre` varchar(50) NOT NULL COMMENT 'Ej: Primer Curso',
  `idTurno` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cursos de la educación media (1°, 2°, 3°)';

--
-- RELACIONES PARA LA TABLA `curso`:
--   `idTurno`
--       `turno` -> `idTurno`
--

--
-- Volcado de datos para la tabla `curso`
--

INSERT INTO `curso` (`idCurso`, `numero`, `nombre`, `idTurno`) VALUES
(1, 1, 'Primer Curso', 1),
(2, 2, 'Segundo Curso', 1),
(3, 3, 'Tercer Curso', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente`
--

CREATE TABLE `docente` (
  `idDocente` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL COMMENT 'Obligatorio: se usará para crear su cuenta de usuario',
  `direccion` varchar(250) DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL,
  `titulo` varchar(200) DEFAULT NULL COMMENT 'Título profesional',
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `idUsuario` int(11) DEFAULT NULL COMMENT 'Cuenta creada en segundo paso tras registrar el docente',
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Datos personales del personal docente';

--
-- RELACIONES PARA LA TABLA `docente`:
--   `idUsuario`
--       `usuarios` -> `idUsuario`
--

--
-- Volcado de datos para la tabla `docente`
--

INSERT INTO `docente` (`idDocente`, `cedula`, `nombres`, `apellidos`, `telefono`, `correo`, `direccion`, `fecha_nac`, `titulo`, `estado`, `idUsuario`, `creado`, `modificado`) VALUES
(1, '1234567', 'Alvaro', 'Ortega', '0981-123456', 'alvaroortegadominguez11@gmail.com', NULL, NULL, NULL, 'Activo', 4, '2026-05-14 10:19:55', '2026-05-16 13:07:44'),
(3, '4500127', 'Carlos Alberto', 'Ortiz Ramirez', '0981 123 456', '', 'Avda. Eusebio Ayala 654', '1990-02-08', 'Licenciado en Matemáticas', 'Activo', 9, '2026-06-25 09:47:00', '2026-07-01 13:51:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente_aula_materia`
--

CREATE TABLE `docente_aula_materia` (
  `idAsignacion` int(11) NOT NULL,
  `idDocente` int(11) NOT NULL,
  `idAulaMateria` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Asignación: qué docente dicta qué materia en qué aula';

--
-- RELACIONES PARA LA TABLA `docente_aula_materia`:
--   `idAulaMateria`
--       `aula_materia` -> `idAulaMateria`
--   `idDocente`
--       `docente` -> `idDocente`
--

--
-- Volcado de datos para la tabla `docente_aula_materia`
--

INSERT INTO `docente_aula_materia` (`idAsignacion`, `idDocente`, `idAulaMateria`, `activo`) VALUES
(4, 1, 7, 1),
(5, 1, 9, 1),
(14, 1, 2, 1),
(16, 1, 1, 1),
(17, 1, 5, 1),
(19, 1, 13, 1),
(24, 3, 8, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enfasis`
--

CREATE TABLE `enfasis` (
  `idEnfasis` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Ciencias Básicas, Humanístico, Técnico Administrativo',
  `descripcion` varchar(250) DEFAULT NULL,
  `activo` enum('Sí','No') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Énfasis del bachillerato';

--
-- RELACIONES PARA LA TABLA `enfasis`:
--

--
-- Volcado de datos para la tabla `enfasis`
--

INSERT INTO `enfasis` (`idEnfasis`, `nombre`, `descripcion`, `activo`) VALUES
(1, 'Ciencias Básicas', 'Orientado a ciencias exactas y naturales', 'Sí'),
(2, 'Ciencias Sociales', 'Orientado a letras, historia y sociales', 'Sí'),
(3, 'Bachillerato Técnico en Ciencias Contables', 'Formación técnica en ciencias contables', 'Sí'),
(4, 'Ninguno', 'Materias comunes a todos los énfasis', 'Sí');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluacion`
--

CREATE TABLE `evaluacion` (
  `idEvaluacion` int(11) NOT NULL,
  `idAulaMateria` int(11) NOT NULL COMMENT 'Relación con el docente y la materia',
  `idPeriodo` int(11) NOT NULL COMMENT 'A qué bimestre pertenece',
  `idTipoNota` int(11) NOT NULL COMMENT 'Si es Parcial, TP, etc.',
  `nombre` varchar(150) NOT NULL COMMENT 'Ej: Primer Parcial de Matemática o TP 1 - Vectores',
  `puntos_total` int(11) NOT NULL DEFAULT 0 COMMENT 'Total de puntos de la evaluación',
  `fecha_evaluacion` date DEFAULT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Definición de evaluaciones creadas por el docente';

--
-- RELACIONES PARA LA TABLA `evaluacion`:
--   `idAulaMateria`
--       `aula_materia` -> `idAulaMateria`
--   `idPeriodo`
--       `periodo` -> `idPeriodo`
--   `idTipoNota`
--       `tipo_nota` -> `idTipoNota`
--

--
-- Volcado de datos para la tabla `evaluacion`
--

INSERT INTO `evaluacion` (`idEvaluacion`, `idAulaMateria`, `idPeriodo`, `idTipoNota`, `nombre`, `puntos_total`, `fecha_evaluacion`, `creado`, `modificado`) VALUES
(16, 1, 1, 4, 'Parcial 1', 20, '2026-06-24', '2026-06-22 16:16:59', '2026-06-22 16:16:59'),
(17, 9, 1, 3, 'Exposicion', 20, '2026-06-26', '2026-06-22 16:26:38', '2026-06-22 16:27:17'),
(19, 13, 1, 2, 'Ecuaciones', 12, '2026-06-25', '2026-06-22 16:53:03', '2026-06-22 16:53:03'),
(23, 2, 1, 4, '1er Parcial', 20, '2026-06-25', '2026-07-01 09:56:57', '2026-07-01 09:56:57'),
(24, 2, 1, 5, '2da Parcial', 20, '2026-07-01', '2026-07-01 09:58:27', '2026-07-01 09:58:27'),
(25, 2, 1, 2, 'Trabajo Final', 60, '2026-07-08', '2026-07-01 10:00:29', '2026-07-01 10:00:29'),
(26, 1, 1, 5, '2da Parcial', 15, '2026-07-01', '2026-07-01 10:02:54', '2026-07-01 10:02:54'),
(27, 1, 1, 2, 'Trabajo Final', 30, '2026-07-10', '2026-07-01 10:05:30', '2026-07-01 10:05:30'),
(28, 2, 1, 6, 'FInal examen', 25, '2026-07-10', '2026-07-01 10:07:12', '2026-07-01 10:07:12'),
(29, 1, 1, 6, 'Examen Final', 20, '2026-07-09', '2026-07-01 10:10:05', '2026-07-01 10:10:05'),
(30, 13, 1, 4, 'parcial 1', 20, '2026-07-07', '2026-07-01 10:14:12', '2026-07-01 10:14:12'),
(31, 13, 1, 5, 'SP', 30, '2026-07-09', '2026-07-01 10:15:23', '2026-07-01 10:15:23'),
(32, 13, 1, 6, 'FINAL', 40, '2026-07-10', '2026-07-01 10:19:35', '2026-07-01 10:19:35'),
(33, 13, 2, 2, 'TP', 40, '2026-07-15', '2026-07-01 10:24:47', '2026-07-01 10:24:47'),
(34, 13, 2, 4, '1P', 25, '2026-07-16', '2026-07-01 10:26:00', '2026-07-01 10:26:00'),
(35, 13, 2, 5, '2P', 20, '2026-07-31', '2026-07-01 10:26:50', '2026-07-01 10:26:50'),
(36, 13, 2, 6, 'final', 40, '2026-11-06', '2026-07-01 10:27:54', '2026-07-01 10:27:54'),
(37, 1, 2, 2, 'TP', 30, '2026-07-30', '2026-07-01 10:30:49', '2026-07-01 10:30:49'),
(38, 1, 2, 4, 'PP', 25, '2026-09-12', '2026-07-01 10:33:16', '2026-07-01 10:33:16'),
(39, 1, 2, 5, 'SP', 20, '2026-09-24', '2026-07-01 10:34:26', '2026-07-01 10:34:26'),
(40, 1, 2, 6, 'FINAL', 40, '2026-11-19', '2026-07-01 10:35:18', '2026-07-01 10:35:18'),
(41, 2, 2, 4, 'parcial 1', 20, '2026-07-31', '2026-07-01 10:37:50', '2026-07-01 10:37:50'),
(42, 2, 2, 2, 'trabajo de ecuaciones', 10, '2026-07-15', '2026-07-01 10:38:36', '2026-07-01 10:38:36'),
(43, 2, 2, 2, 'fracciones', 20, '2026-07-23', '2026-07-01 10:38:58', '2026-07-01 10:38:58'),
(44, 8, 2, 4, 'PP', 20, '2026-07-28', '2026-07-01 14:08:17', '2026-07-01 14:08:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia`
--

CREATE TABLE `materia` (
  `idMateria` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL COMMENT 'Código interno (ej: MAT-101)',
  `horas_sem` tinyint(1) NOT NULL DEFAULT 4 COMMENT 'Horas semanales',
  `idEnfasis` int(11) DEFAULT NULL COMMENT 'NULL = materia común a todos los énfasis',
  `activo` enum('Sí','No') NOT NULL,
  `plan` varchar(50) NOT NULL,
  `idCurso` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Asignaturas/materias';

--
-- RELACIONES PARA LA TABLA `materia`:
--   `idEnfasis`
--       `enfasis` -> `idEnfasis`
--   `idCurso`
--       `curso` -> `idCurso`
--

--
-- Volcado de datos para la tabla `materia`
--

INSERT INTO `materia` (`idMateria`, `nombre`, `codigo`, `horas_sem`, `idEnfasis`, `activo`, `plan`, `idCurso`) VALUES
(1, 'Lengua y Literatura Castellana', 'LEN-101', 4, 4, 'Sí', 'Plan Común', 1),
(2, 'Matemática', 'MAT-101', 5, 4, 'Sí', 'Plan Común', 1),
(3, 'Lengua Extranjera (Inglés)', 'ING-101', 3, 4, 'Sí', 'Plan Común', 1),
(4, 'Lengua Guaraní', 'GUA-101', 2, 4, 'Sí', 'Plan Común', 1),
(5, 'Historia', 'HIS-101', 3, 4, 'Sí', 'Plan Común', 1),
(6, 'Geografía', 'GEO-101', 2, 4, 'Sí', 'Plan Común', 2),
(7, 'Física', 'FIS-101', 3, 1, 'Sí', 'Plan Específico', 2),
(8, 'Química', 'QUI-101', 3, 1, 'Sí', 'Plan Específico', 2),
(9, 'Biología', 'BIO-101', 3, 1, 'Sí', 'Plan Específico', 2),
(10, 'Filosofía', 'FIL-101', 3, 2, 'Sí', 'Plan Específico', 2),
(11, 'Sociología', 'SOC-101', 3, 2, 'Sí', 'Plan Específico', 3),
(12, 'Educación Física', 'EDF-101', 2, 4, 'Sí', 'Plan Común', 3),
(13, 'Arte y Expresión', 'ART-101', 2, 4, 'Sí', 'Plan Común', 3),
(14, 'Formación Ética y Ciudadana', 'ETI-101', 2, 4, 'Sí', 'Plan Común', 3),
(15, 'Gestión Empresarial', 'GES-101', 4, 3, 'Sí', 'Plan Específico', 3),
(16, 'Contabilidad', 'CON-101', 4, 3, 'Sí', 'Plan Específico', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `matricula`
--

CREATE TABLE `matricula` (
  `idMatricula` int(11) NOT NULL,
  `idAlumno` int(11) NOT NULL,
  `idAula` int(11) NOT NULL,
  `fecha_matricula` date NOT NULL DEFAULT curdate(),
  `estado` enum('Vigente','Retirado','Trasladado','Promovido','Reprobado') NOT NULL DEFAULT 'Vigente',
  `observacion` text DEFAULT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Inscripción del alumno en un aula por año lectivo';

--
-- RELACIONES PARA LA TABLA `matricula`:
--   `idAlumno`
--       `alumno` -> `idAlumno`
--   `idAula`
--       `aula` -> `idAula`
--

--
-- Volcado de datos para la tabla `matricula`
--

INSERT INTO `matricula` (`idMatricula`, `idAlumno`, `idAula`, `fecha_matricula`, `estado`, `observacion`, `creado`) VALUES
(1, 1, 1, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(2, 2, 1, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(3, 3, 1, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(4, 4, 1, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(5, 5, 1, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(6, 6, 2, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(7, 7, 2, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(8, 8, 2, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(9, 9, 3, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(10, 10, 3, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(11, 11, 3, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(12, 12, 4, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(13, 13, 4, '2026-02-16', 'Vigente', NULL, '2026-05-14 10:19:55'),
(14, 14, 1, '2026-06-03', 'Vigente', 'LCcoa', '2026-06-03 13:58:54'),
(15, 16, 3, '2026-06-25', 'Vigente', 'Ejemplo editado hoy', '2026-06-25 09:22:57'),
(16, 17, 1, '2026-07-02', 'Vigente', '', '2026-07-02 12:35:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nota`
--

CREATE TABLE `nota` (
  `idNota` int(11) NOT NULL,
  `idMatricula` int(11) NOT NULL,
  `idEvaluacion` int(11) NOT NULL,
  `puntos_obtenidos` int(11) NOT NULL DEFAULT 0,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` varchar(300) DEFAULT NULL,
  `registrado_por` int(11) NOT NULL COMMENT 'idUsuario que registró',
  `modificado_por` int(11) DEFAULT NULL,
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `nota`:
--   `idEvaluacion`
--       `evaluacion` -> `idEvaluacion`
--   `idMatricula`
--       `matricula` -> `idMatricula`
--   `registrado_por`
--       `usuarios` -> `idUsuario`
--

--
-- Volcado de datos para la tabla `nota`
--

INSERT INTO `nota` (`idNota`, `idMatricula`, `idEvaluacion`, `puntos_obtenidos`, `fecha_registro`, `observacion`, `registrado_por`, `modificado_por`, `modificado`) VALUES
(1, 4, 16, 20, '2026-06-22 16:31:25', '', 4, NULL, '2026-06-22 16:31:25'),
(2, 5, 16, 14, '2026-06-22 16:31:25', NULL, 4, 4, '2026-06-22 16:36:58'),
(3, 2, 16, 11, '2026-06-22 16:31:25', '', 4, NULL, '2026-06-22 16:31:25'),
(4, 3, 16, 9, '2026-06-22 16:31:25', '', 4, NULL, '2026-06-22 16:31:25'),
(5, 14, 16, 20, '2026-06-22 16:31:25', '', 4, NULL, '2026-06-22 16:31:25'),
(6, 1, 16, 0, '2026-06-22 16:31:54', 'No entrego', 4, NULL, '2026-06-22 16:31:54'),
(7, 4, 19, 12, '2026-06-22 16:54:44', '', 4, NULL, '2026-06-22 16:54:44'),
(8, 5, 19, 12, '2026-06-22 16:54:44', '', 4, NULL, '2026-06-22 16:54:44'),
(9, 2, 19, 5, '2026-06-22 16:54:45', NULL, 4, 4, '2026-06-22 16:59:43'),
(10, 3, 19, 10, '2026-06-22 16:54:45', '', 4, NULL, '2026-06-22 16:54:45'),
(11, 14, 19, 11, '2026-06-22 16:54:45', '', 4, NULL, '2026-06-22 16:54:45'),
(12, 1, 19, 10, '2026-06-22 16:54:45', '', 4, NULL, '2026-06-22 16:54:45'),
(19, 4, 23, 20, '2026-07-01 09:57:25', '', 4, NULL, '2026-07-01 09:57:25'),
(20, 5, 23, 20, '2026-07-01 09:57:25', '', 4, NULL, '2026-07-01 09:57:25'),
(21, 2, 23, 18, '2026-07-01 09:57:25', '', 4, NULL, '2026-07-01 09:57:25'),
(22, 3, 23, 17, '2026-07-01 09:57:26', '', 4, NULL, '2026-07-01 09:57:26'),
(23, 14, 23, 15, '2026-07-01 09:57:26', '', 4, NULL, '2026-07-01 09:57:26'),
(24, 1, 23, 8, '2026-07-01 09:57:26', '', 4, NULL, '2026-07-01 09:57:26'),
(25, 4, 24, 14, '2026-07-01 09:59:45', '', 4, NULL, '2026-07-01 09:59:45'),
(26, 5, 24, 10, '2026-07-01 09:59:45', '', 4, NULL, '2026-07-01 09:59:45'),
(27, 2, 24, 7, '2026-07-01 09:59:45', '', 4, NULL, '2026-07-01 09:59:45'),
(28, 3, 24, 16, '2026-07-01 09:59:46', '', 4, NULL, '2026-07-01 09:59:46'),
(29, 14, 24, 17, '2026-07-01 09:59:46', '', 4, NULL, '2026-07-01 09:59:46'),
(30, 1, 24, 20, '2026-07-01 09:59:46', '', 4, NULL, '2026-07-01 09:59:46'),
(31, 4, 25, 58, '2026-07-01 10:00:55', '', 4, NULL, '2026-07-01 10:00:55'),
(32, 5, 25, 58, '2026-07-01 10:00:55', '', 4, NULL, '2026-07-01 10:00:55'),
(33, 2, 25, 58, '2026-07-01 10:00:55', '', 4, NULL, '2026-07-01 10:00:55'),
(34, 3, 25, 45, '2026-07-01 10:00:55', '', 4, NULL, '2026-07-01 10:00:55'),
(35, 14, 25, 45, '2026-07-01 10:00:55', '', 4, NULL, '2026-07-01 10:00:55'),
(36, 1, 25, 45, '2026-07-01 10:00:55', '', 4, NULL, '2026-07-01 10:00:55'),
(37, 4, 26, 14, '2026-07-01 10:03:28', '', 4, NULL, '2026-07-01 10:03:28'),
(38, 5, 26, 14, '2026-07-01 10:03:28', '', 4, NULL, '2026-07-01 10:03:28'),
(39, 2, 26, 15, '2026-07-01 10:03:29', '', 4, NULL, '2026-07-01 10:03:29'),
(40, 3, 26, 11, '2026-07-01 10:03:29', '', 4, NULL, '2026-07-01 10:03:29'),
(41, 14, 26, 5, '2026-07-01 10:03:29', '', 4, NULL, '2026-07-01 10:03:29'),
(42, 1, 26, 7, '2026-07-01 10:03:29', '', 4, NULL, '2026-07-01 10:03:29'),
(43, 4, 27, 30, '2026-07-01 10:06:08', '', 4, NULL, '2026-07-01 10:06:08'),
(44, 5, 27, 30, '2026-07-01 10:06:08', '', 4, NULL, '2026-07-01 10:06:08'),
(45, 2, 27, 30, '2026-07-01 10:06:08', '', 4, NULL, '2026-07-01 10:06:08'),
(46, 3, 27, 30, '2026-07-01 10:06:08', '', 4, NULL, '2026-07-01 10:06:08'),
(47, 14, 27, 25, '2026-07-01 10:06:08', '', 4, NULL, '2026-07-01 10:06:08'),
(48, 1, 27, 30, '2026-07-01 10:06:08', '', 4, NULL, '2026-07-01 10:06:08'),
(49, 4, 28, 25, '2026-07-01 10:07:35', '', 4, NULL, '2026-07-01 10:07:35'),
(50, 5, 28, 25, '2026-07-01 10:07:35', '', 4, NULL, '2026-07-01 10:07:35'),
(51, 2, 28, 25, '2026-07-01 10:07:36', '', 4, NULL, '2026-07-01 10:07:36'),
(52, 3, 28, 25, '2026-07-01 10:07:36', '', 4, NULL, '2026-07-01 10:07:36'),
(53, 14, 28, 23, '2026-07-01 10:07:36', '', 4, NULL, '2026-07-01 10:07:36'),
(54, 1, 28, 22, '2026-07-01 10:07:36', '', 4, NULL, '2026-07-01 10:07:36'),
(55, 4, 29, 20, '2026-07-01 10:10:25', '', 4, NULL, '2026-07-01 10:10:25'),
(56, 5, 29, 20, '2026-07-01 10:10:25', '', 4, NULL, '2026-07-01 10:10:25'),
(57, 2, 29, 20, '2026-07-01 10:10:26', '', 4, NULL, '2026-07-01 10:10:26'),
(58, 3, 29, 20, '2026-07-01 10:10:26', '', 4, NULL, '2026-07-01 10:10:26'),
(59, 14, 29, 20, '2026-07-01 10:10:26', '', 4, NULL, '2026-07-01 10:10:26'),
(60, 1, 29, 11, '2026-07-01 10:10:26', '', 4, NULL, '2026-07-01 10:10:26'),
(61, 4, 30, 20, '2026-07-01 10:14:30', '', 4, NULL, '2026-07-01 10:14:30'),
(62, 5, 30, 15, '2026-07-01 10:14:30', '', 4, NULL, '2026-07-01 10:14:30'),
(63, 2, 30, 18, '2026-07-01 10:14:30', '', 4, NULL, '2026-07-01 10:14:30'),
(64, 3, 30, 19, '2026-07-01 10:14:30', '', 4, NULL, '2026-07-01 10:14:30'),
(65, 14, 30, 18, '2026-07-01 10:14:30', '', 4, NULL, '2026-07-01 10:14:30'),
(66, 1, 30, 20, '2026-07-01 10:14:30', '', 4, NULL, '2026-07-01 10:14:30'),
(67, 4, 31, 30, '2026-07-01 10:19:05', '', 4, NULL, '2026-07-01 10:19:05'),
(68, 5, 31, 30, '2026-07-01 10:19:05', '', 4, NULL, '2026-07-01 10:19:05'),
(69, 2, 31, 30, '2026-07-01 10:19:05', '', 4, NULL, '2026-07-01 10:19:05'),
(70, 3, 31, 30, '2026-07-01 10:19:05', '', 4, NULL, '2026-07-01 10:19:05'),
(71, 14, 31, 25, '2026-07-01 10:19:05', '', 4, NULL, '2026-07-01 10:19:05'),
(72, 1, 31, 25, '2026-07-01 10:19:05', '', 4, NULL, '2026-07-01 10:19:05'),
(73, 4, 32, 39, '2026-07-01 10:19:58', '', 4, NULL, '2026-07-01 10:19:58'),
(74, 5, 32, 38, '2026-07-01 10:19:58', '', 4, NULL, '2026-07-01 10:19:58'),
(75, 2, 32, 36, '2026-07-01 10:19:58', '', 4, NULL, '2026-07-01 10:19:58'),
(76, 3, 32, 34, '2026-07-01 10:19:58', '', 4, NULL, '2026-07-01 10:19:58'),
(77, 14, 32, 37, '2026-07-01 10:19:58', '', 4, NULL, '2026-07-01 10:19:58'),
(78, 1, 32, 40, '2026-07-01 10:19:58', '', 4, NULL, '2026-07-01 10:19:58'),
(79, 4, 33, 40, '2026-07-01 10:25:09', '', 4, NULL, '2026-07-01 10:25:09'),
(80, 5, 33, 40, '2026-07-01 10:25:09', '', 4, NULL, '2026-07-01 10:25:09'),
(81, 2, 33, 35, '2026-07-01 10:25:10', '', 4, NULL, '2026-07-01 10:25:10'),
(82, 3, 33, 36, '2026-07-01 10:25:10', '', 4, NULL, '2026-07-01 10:25:10'),
(83, 14, 33, 39, '2026-07-01 10:25:10', '', 4, NULL, '2026-07-01 10:25:10'),
(84, 1, 33, 39, '2026-07-01 10:25:10', '', 4, NULL, '2026-07-01 10:25:10'),
(85, 4, 34, 25, '2026-07-01 10:26:20', '', 4, NULL, '2026-07-01 10:26:20'),
(86, 5, 34, 21, '2026-07-01 10:26:20', '', 4, NULL, '2026-07-01 10:26:20'),
(87, 2, 34, 20, '2026-07-01 10:26:20', '', 4, NULL, '2026-07-01 10:26:20'),
(88, 3, 34, 23, '2026-07-01 10:26:20', '', 4, NULL, '2026-07-01 10:26:20'),
(89, 14, 34, 25, '2026-07-01 10:26:20', '', 4, NULL, '2026-07-01 10:26:20'),
(90, 1, 34, 25, '2026-07-01 10:26:20', '', 4, NULL, '2026-07-01 10:26:20'),
(91, 4, 35, 15, '2026-07-01 10:27:18', '', 4, NULL, '2026-07-01 10:27:18'),
(92, 5, 35, 20, '2026-07-01 10:27:18', '', 4, NULL, '2026-07-01 10:27:18'),
(93, 2, 35, 20, '2026-07-01 10:27:18', '', 4, NULL, '2026-07-01 10:27:18'),
(94, 3, 35, 20, '2026-07-01 10:27:18', '', 4, NULL, '2026-07-01 10:27:18'),
(95, 14, 35, 18, '2026-07-01 10:27:18', '', 4, NULL, '2026-07-01 10:27:18'),
(96, 1, 35, 14, '2026-07-01 10:27:18', '', 4, NULL, '2026-07-01 10:27:18'),
(97, 4, 36, 40, '2026-07-01 10:28:16', '', 4, NULL, '2026-07-01 10:28:16'),
(98, 5, 36, 40, '2026-07-01 10:28:16', '', 4, NULL, '2026-07-01 10:28:16'),
(99, 2, 36, 15, '2026-07-01 10:28:16', '', 4, NULL, '2026-07-01 10:28:16'),
(100, 3, 36, 39, '2026-07-01 10:28:16', '', 4, NULL, '2026-07-01 10:28:16'),
(101, 14, 36, 36, '2026-07-01 10:28:17', '', 4, NULL, '2026-07-01 10:28:17'),
(102, 1, 36, 35, '2026-07-01 10:28:17', '', 4, NULL, '2026-07-01 10:28:17'),
(103, 4, 37, 20, '2026-07-01 10:31:57', '', 4, NULL, '2026-07-01 10:31:57'),
(104, 5, 37, 15, '2026-07-01 10:31:57', '', 4, NULL, '2026-07-01 10:31:57'),
(105, 2, 37, 15, '2026-07-01 10:31:57', '', 4, NULL, '2026-07-01 10:31:57'),
(106, 3, 37, 20, '2026-07-01 10:31:57', '', 4, NULL, '2026-07-01 10:31:57'),
(107, 14, 37, 18, '2026-07-01 10:31:57', '', 4, NULL, '2026-07-01 10:31:57'),
(108, 1, 37, 20, '2026-07-01 10:31:57', '', 4, NULL, '2026-07-01 10:31:57'),
(109, 4, 38, 25, '2026-07-01 10:33:40', '', 4, NULL, '2026-07-01 10:33:40'),
(110, 5, 38, 25, '2026-07-01 10:33:41', '', 4, NULL, '2026-07-01 10:33:41'),
(111, 2, 38, 25, '2026-07-01 10:33:41', '', 4, NULL, '2026-07-01 10:33:41'),
(112, 3, 38, 24, '2026-07-01 10:33:41', '', 4, NULL, '2026-07-01 10:33:41'),
(113, 14, 38, 25, '2026-07-01 10:33:41', '', 4, NULL, '2026-07-01 10:33:41'),
(114, 1, 38, 22, '2026-07-01 10:33:41', '', 4, NULL, '2026-07-01 10:33:41'),
(115, 4, 39, 20, '2026-07-01 10:34:50', '', 4, NULL, '2026-07-01 10:34:50'),
(116, 5, 39, 18, '2026-07-01 10:34:50', '', 4, NULL, '2026-07-01 10:34:50'),
(117, 2, 39, 19, '2026-07-01 10:34:50', '', 4, NULL, '2026-07-01 10:34:50'),
(118, 3, 39, 20, '2026-07-01 10:34:50', '', 4, NULL, '2026-07-01 10:34:50'),
(119, 14, 39, 18, '2026-07-01 10:34:50', '', 4, NULL, '2026-07-01 10:34:50'),
(120, 1, 39, 17, '2026-07-01 10:34:50', '', 4, NULL, '2026-07-01 10:34:50'),
(121, 4, 40, 38, '2026-07-01 10:35:39', '', 4, NULL, '2026-07-01 10:35:39'),
(122, 5, 40, 38, '2026-07-01 10:35:39', '', 4, NULL, '2026-07-01 10:35:39'),
(123, 2, 40, 40, '2026-07-01 10:35:39', '', 4, NULL, '2026-07-01 10:35:39'),
(124, 3, 40, 40, '2026-07-01 10:35:39', '', 4, NULL, '2026-07-01 10:35:39'),
(125, 14, 40, 40, '2026-07-01 10:35:39', '', 4, NULL, '2026-07-01 10:35:39'),
(126, 1, 40, 38, '2026-07-01 10:35:39', '', 4, NULL, '2026-07-01 10:35:39'),
(127, 4, 41, 19, '2026-07-01 10:38:10', '', 4, NULL, '2026-07-01 10:38:10'),
(128, 5, 41, 18, '2026-07-01 10:38:10', '', 4, NULL, '2026-07-01 10:38:10'),
(129, 2, 41, 17, '2026-07-01 10:38:10', '', 4, NULL, '2026-07-01 10:38:10'),
(130, 3, 41, 14, '2026-07-01 10:38:10', '', 4, NULL, '2026-07-01 10:38:10'),
(131, 14, 41, 15, '2026-07-01 10:38:11', '', 4, NULL, '2026-07-01 10:38:11'),
(132, 1, 41, 18, '2026-07-01 10:38:11', '', 4, NULL, '2026-07-01 10:38:11'),
(133, 4, 42, 10, '2026-07-01 10:39:24', '', 4, NULL, '2026-07-01 10:39:24'),
(134, 5, 42, 10, '2026-07-01 10:39:24', '', 4, NULL, '2026-07-01 10:39:24'),
(135, 2, 42, 10, '2026-07-01 10:39:24', '', 4, NULL, '2026-07-01 10:39:24'),
(136, 3, 42, 10, '2026-07-01 10:39:24', '', 4, NULL, '2026-07-01 10:39:24'),
(137, 14, 42, 10, '2026-07-01 10:39:24', '', 4, NULL, '2026-07-01 10:39:24'),
(138, 1, 42, 10, '2026-07-01 10:39:24', '', 4, NULL, '2026-07-01 10:39:24'),
(139, 4, 43, 18, '2026-07-01 10:39:48', '', 4, NULL, '2026-07-01 10:39:48'),
(140, 5, 43, 18, '2026-07-01 10:39:48', '', 4, NULL, '2026-07-01 10:39:48'),
(141, 2, 43, 18, '2026-07-01 10:39:48', '', 4, NULL, '2026-07-01 10:39:48'),
(142, 3, 43, 12, '2026-07-01 10:39:49', '', 4, NULL, '2026-07-01 10:39:49'),
(143, 14, 43, 17, '2026-07-01 10:39:49', '', 4, NULL, '2026-07-01 10:39:49'),
(144, 1, 43, 15, '2026-07-01 10:39:49', '', 4, NULL, '2026-07-01 10:39:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nota_final_materia`
--

CREATE TABLE `nota_final_materia` (
  `idNotaFinal` int(11) NOT NULL,
  `idMatricula` int(11) NOT NULL COMMENT 'Alumno matriculado',
  `idAulaMateria` int(11) NOT NULL COMMENT 'Materia del aula',
  `nota_sem1` tinyint(1) DEFAULT NULL COMMENT 'Nota 1° Semestre (1-5)',
  `nota_sem2` tinyint(1) DEFAULT NULL COMMENT 'Nota 2° Semestre (1-5)',
  `nota_final` tinyint(1) DEFAULT NULL COMMENT 'Promedio redondeado sem1+sem2',
  `nota_rec1` tinyint(1) DEFAULT NULL COMMENT '1° Recuperatorio (1-5)',
  `nota_rec2` tinyint(1) DEFAULT NULL COMMENT '2° Recuperatorio / Extraordinario (1-5)',
  `nota_definitiva` tinyint(1) DEFAULT NULL COMMENT 'Nota final que vale al cerrar el año',
  `estado` enum('Pendiente','Aprobado','Recuperatorio1','Recuperatorio2','Reprobado') NOT NULL DEFAULT 'Pendiente',
  `registrado_por` int(11) NOT NULL COMMENT 'idUsuario que cerró/registró',
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Nota final anual y recuperatorios por alumno/materia';

--
-- RELACIONES PARA LA TABLA `nota_final_materia`:
--   `idAulaMateria`
--       `aula_materia` -> `idAulaMateria`
--   `idMatricula`
--       `matricula` -> `idMatricula`
--   `registrado_por`
--       `usuarios` -> `idUsuario`
--

--
-- Volcado de datos para la tabla `nota_final_materia`
--

INSERT INTO `nota_final_materia` (`idNotaFinal`, `idMatricula`, `idAulaMateria`, `nota_sem1`, `nota_sem2`, `nota_final`, `nota_rec1`, `nota_rec2`, `nota_definitiva`, `estado`, `registrado_por`, `creado`, `modificado`) VALUES
(1, 1, 1, 1, 3, 2, NULL, NULL, 2, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(2, 1, 2, 2, 4, 3, NULL, NULL, 3, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(3, 1, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(4, 1, 13, 4, 4, 4, NULL, NULL, 4, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(5, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(6, 2, 1, 4, 4, 4, NULL, NULL, 4, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(7, 2, 2, 4, 4, 4, NULL, NULL, 4, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(8, 2, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(9, 2, 13, 4, 2, 3, NULL, NULL, 3, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(10, 2, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(11, 3, 1, 3, 4, 4, NULL, NULL, 4, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(12, 3, 2, 3, 2, 3, NULL, NULL, 3, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(13, 3, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(14, 3, 13, 4, 5, 5, NULL, NULL, 5, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(15, 3, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(16, 4, 1, 5, 4, 5, NULL, NULL, 5, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(17, 4, 2, 5, 5, 5, NULL, NULL, 5, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(18, 4, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(19, 4, 13, 5, 5, 5, NULL, NULL, 5, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(20, 4, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(21, 5, 1, 4, 3, 4, NULL, NULL, 4, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(22, 5, 2, 4, 4, 4, NULL, NULL, 4, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(23, 5, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(24, 5, 13, 4, 5, 5, NULL, NULL, 5, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(25, 5, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(26, 14, 1, 3, 4, 4, NULL, NULL, 4, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(27, 14, 2, 3, 3, 3, NULL, NULL, 3, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(28, 14, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(29, 14, 13, 4, 5, 5, NULL, NULL, 5, 'Aprobado', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(30, 14, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(31, 6, 4, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(32, 6, 5, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(33, 6, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(34, 7, 4, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(35, 7, 5, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(36, 7, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(37, 8, 4, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(38, 8, 5, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(39, 8, 6, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(40, 9, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(41, 9, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(42, 10, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(43, 10, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(44, 11, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(45, 11, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(46, 15, 7, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(47, 15, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(48, 12, 9, 1, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07'),
(49, 13, 9, 1, NULL, NULL, NULL, NULL, NULL, 'Pendiente', 3, '2026-07-01 10:41:07', '2026-07-01 10:41:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodo`
--

CREATE TABLE `periodo` (
  `idPeriodo` int(11) NOT NULL,
  `idAnio` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL COMMENT '1° Etapa, 2° Etapa...',
  `numero` tinyint(1) NOT NULL COMMENT '1, 2, 3, 4',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` enum('Sí','No') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Bimestres del año lectivo';

--
-- RELACIONES PARA LA TABLA `periodo`:
--   `idAnio`
--       `anio_lectivo` -> `idAnio`
--

--
-- Volcado de datos para la tabla `periodo`
--

INSERT INTO `periodo` (`idPeriodo`, `idAnio`, `nombre`, `numero`, `fecha_inicio`, `fecha_fin`, `activo`) VALUES
(1, 1, '1° Semestre', 1, '2026-02-16', '2026-07-10', 'No'),
(2, 1, '2° Semestre', 2, '2026-04-27', '2026-12-03', 'Sí');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodo_excepcion`
--

CREATE TABLE `periodo_excepcion` (
  `idExcepcion` int(11) NOT NULL,
  `idPeriodo` int(11) NOT NULL,
  `idAulaMateria` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `autorizado_por` int(11) NOT NULL COMMENT 'idUsuario del Director',
  `creado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Materias reabiertas individualmente tras cierre global de periodo';

--
-- RELACIONES PARA LA TABLA `periodo_excepcion`:
--   `idAulaMateria`
--       `aula_materia` -> `idAulaMateria`
--   `idPeriodo`
--       `periodo` -> `idPeriodo`
--   `autorizado_por`
--       `usuarios` -> `idUsuario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_catedra`
--

CREATE TABLE `registro_catedra` (
  `idRegCatedra` int(11) NOT NULL,
  `idAsignacion` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `horaInicio` varchar(20) NOT NULL,
  `horaFin` varchar(20) NOT NULL,
  `unidad` varchar(150) NOT NULL,
  `tema` varchar(150) NOT NULL,
  `observaciones` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- RELACIONES PARA LA TABLA `registro_catedra`:
--   `idAsignacion`
--       `docente_aula_materia` -> `idAsignacion`
--

--
-- Volcado de datos para la tabla `registro_catedra`
--

INSERT INTO `registro_catedra` (`idRegCatedra`, `idAsignacion`, `fecha`, `horaInicio`, `horaFin`, `unidad`, `tema`, `observaciones`) VALUES
(1, 19, '2026-06-29', '15:30', '18:00', 'Unidad 1- que fracasado es conta', 'una mid', 'trabajo al final');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `idRol` int(11) NOT NULL,
  `rol` varchar(50) NOT NULL COMMENT 'Director, Secretario, Evaluador, Docente, Padre',
  `descripcion` varchar(200) DEFAULT NULL,
  `activo` enum('Sí','No') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Roles del sistema';

--
-- RELACIONES PARA LA TABLA `rol`:
--

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idRol`, `rol`, `descripcion`, `activo`) VALUES
(1, 'Director', 'Acceso total al sistema', 'Sí'),
(2, 'Secretario', 'Gestión administrativa y de usuarios', 'Sí'),
(3, 'Docente', 'Carga de notas, asistencia y planificación', 'Sí'),
(4, 'Padre', 'Consulta de información de sus hijos', 'Sí'),
(5, 'Evaluador', 'Se encarga de las asignaciones', 'Sí');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_nota`
--

CREATE TABLE `tipo_nota` (
  `idTipoNota` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL COMMENT 'Ej: Trabajo Práctico, Evaluación Escrita, Proyecto',
  `porcentaje` decimal(5,2) NOT NULL DEFAULT 100.00 COMMENT 'Peso en el promedio del período (%)',
  `activo` enum('Sí','No') NOT NULL,
  `unico_por_periodo` enum('Sí','No') NOT NULL DEFAULT 'No' COMMENT 'Si = solo puede existir una evaluacion de este tipo por materia/periodo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tipos de evaluación y su peso';

--
-- RELACIONES PARA LA TABLA `tipo_nota`:
--

--
-- Volcado de datos para la tabla `tipo_nota`
--

INSERT INTO `tipo_nota` (`idTipoNota`, `nombre`, `porcentaje`, `activo`, `unico_por_periodo`) VALUES
(2, 'Trabajo Práctico', 25.00, 'Sí', 'No'),
(3, 'Participación/Oral', 25.00, 'Sí', 'No'),
(4, 'Primera Parcial', 0.00, 'Sí', 'Sí'),
(5, 'Segunda Parcial', 0.00, 'Sí', 'Sí'),
(6, 'Examen Final', 0.00, 'Sí', 'Sí');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turno`
--

CREATE TABLE `turno` (
  `idTurno` int(11) NOT NULL,
  `turno` enum('M','T','MT','N') NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Secciones (A, B, ...)';

--
-- RELACIONES PARA LA TABLA `turno`:
--

--
-- Volcado de datos para la tabla `turno`
--

INSERT INTO `turno` (`idTurno`, `turno`, `descripcion`) VALUES
(1, 'M', 'Turno Mañana'),
(2, 'T', 'Turno Tarde'),
(3, 'MT', 'Turno Mañana y Tarde');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tutor`
--

CREATE TABLE `tutor` (
  `idTutor` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `parentesco` varchar(50) NOT NULL COMMENT 'Padre, Madre, Abuelo, Tutor legal...',
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(150) DEFAULT NULL COMMENT 'Obligatorio: se usará para crear su cuenta de acceso',
  `direccion` varchar(250) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `idUsuario` int(11) DEFAULT NULL COMMENT 'Cuenta creada en segundo paso tras registrar el tutor',
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tutores / Padres de familia';

--
-- RELACIONES PARA LA TABLA `tutor`:
--   `idUsuario`
--       `usuarios` -> `idUsuario`
--

--
-- Volcado de datos para la tabla `tutor`
--

INSERT INTO `tutor` (`idTutor`, `cedula`, `nombres`, `apellidos`, `parentesco`, `telefono`, `correo`, `direccion`, `estado`, `idUsuario`, `creado`, `modificado`) VALUES
(1, '6158747', 'Charlie', 'Rodriguez Fernandez', 'Padre', '0971985659', 'charli@gmail.com', NULL, 'Activo', 10, '2026-06-07 10:27:36', '2026-07-01 14:11:08'),
(2, '6999158', 'Graciela María', 'Casco Martínez', 'Madre', '0986577123', 'gracimar@gmail.com', NULL, 'Activo', 11, '2026-06-07 10:50:54', '2026-07-01 15:26:12'),
(3, '6878458', 'Arsenio Enrique', 'Gonzales Petro', 'Padre', '0974895687', 'petro@gmail.com', NULL, 'Activo', NULL, '2026-06-07 18:06:03', '2026-06-07 18:06:03'),
(5, '4500123', 'Antonio Amadeo', 'Ortiz Ramirez', 'Tío', '0981 123 456', '', 'Avda. Eusebio Ayala 654', 'Activo', NULL, '2026-06-25 14:26:17', '2026-06-25 14:26:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idUsuario` int(11) NOT NULL,
  `usuario` varchar(150) NOT NULL,
  `password` varchar(250) NOT NULL COMMENT 'Contraseña hasheada (bcrypt)',
  `correo` varchar(150) NOT NULL COMMENT 'Obligatorio: usado para recuperación de contraseña',
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `token` varchar(100) DEFAULT NULL COMMENT 'Token para recuperación de contraseña',
  `token_expira` datetime DEFAULT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idRol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cuentas de acceso al sistema';

--
-- RELACIONES PARA LA TABLA `usuarios`:
--   `idRol`
--       `rol` -> `idRol`
--

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `usuario`, `password`, `correo`, `estado`, `token`, `token_expira`, `creado`, `modificado`, `idRol`) VALUES
(1, 'admin_director', '$2b$12$placeholder_hash_director', 'director@santateresita.edu.py', 'Activo', NULL, NULL, '2026-04-12 15:28:38', '2026-04-12 15:28:38', 1),
(2, 'secretaria01', '$2b$12$placeholder_hash_secre', 'secretaria@santateresita.edu.py', 'Activo', NULL, NULL, '2026-04-12 15:28:38', '2026-04-12 15:28:38', 2),
(3, 'Sergio137', 'admin123', 'aquinod285@gmail.com', 'Activo', NULL, NULL, '2026-04-14 10:08:16', '2026-04-14 10:08:16', 1),
(4, 'prof_Alvaro', '$2y$10$AtRXSqVzjk/5DyYPeQvjau87fUvaEI7TlR5k0wTo.UI.7auYrjN3m', 'alvaroortegadominguez11@gmail.com', 'Activo', NULL, NULL, '2026-05-14 10:19:55', '2026-07-01 22:18:34', 3),
(7, 'WilsonRene', '$2y$10$rsj9bx0PW2aBCrMDpVXzT.HhxnezDUc1NYs1wgWyv2xrueagAZDoO', 'wilsonrene@gmail.com', 'Activo', NULL, NULL, '2026-06-27 17:56:18', '2026-07-01 13:46:59', 5),
(9, 'Carlos Alberto Ortiz Ramirez', '$2y$10$V65wCF8iT2GyO7obrSSVaOIn61L2styxNSoXuPfpY4GEPfKdnRuU6', 'Carlos123@hotmail.com', 'Activo', NULL, NULL, '2026-07-01 13:51:40', '2026-07-01 13:51:40', 3),
(10, 'Charlie Rodriguez Fernandez', '$2y$10$eESdq1c4cjDAsCSJN2P4Juzu.0oEYsqQPez40tgTCTwDaGoRTnXAG', 'charlie245@gmail.com', 'Activo', NULL, NULL, '2026-07-01 14:11:08', '2026-07-01 14:15:01', 4),
(11, 'Maria_GrA', '123', 'gracimar@gmail.com', 'Activo', NULL, NULL, '2026-07-01 15:25:22', '2026-07-01 15:27:30', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `v_asistencia_resumen`
--

CREATE TABLE `v_asistencia_resumen` (
  `idAlumno` int(11) NOT NULL DEFAULT 0,
  `alumno` varchar(201) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `materia` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `aula` varchar(106) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `total_clases` bigint(21) NOT NULL DEFAULT 0,
  `presentes` decimal(23,0) DEFAULT NULL,
  `ausentes` decimal(23,0) DEFAULT NULL,
  `tardanzas` decimal(23,0) DEFAULT NULL,
  `justificados` decimal(23,0) DEFAULT NULL,
  `pct_asistencia` decimal(28,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_spanish_ci;

--
-- RELACIONES PARA LA TABLA `v_asistencia_resumen`:
--

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`idAlumno`),
  ADD KEY `idx_alumno_estado` (`estado`),
  ADD KEY `idx_alumno_apellidos` (`apellidos`);

--
-- Indices de la tabla `alumno_tutor`
--
ALTER TABLE `alumno_tutor`
  ADD PRIMARY KEY (`idAlumnoTutor`),
  ADD UNIQUE KEY `uq_alumno_tutor` (`idAlumno`,`idTutor`),
  ADD KEY `idx_at_alumno` (`idAlumno`),
  ADD KEY `idx_at_tutor` (`idTutor`);

--
-- Indices de la tabla `anio_lectivo`
--
ALTER TABLE `anio_lectivo`
  ADD PRIMARY KEY (`idAnio`),
  ADD UNIQUE KEY `anio` (`anio`);

--
-- Indices de la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
  ADD PRIMARY KEY (`idDetalle`),
  ADD UNIQUE KEY `uq_detalle` (`idSesion`,`idMatricula`) COMMENT 'Un registro por alumno por sesión',
  ADD KEY `idx_detalle_sesion` (`idSesion`),
  ADD KEY `idx_detalle_matricula` (`idMatricula`);

--
-- Indices de la tabla `asistencia_sesion`
--
ALTER TABLE `asistencia_sesion`
  ADD PRIMARY KEY (`idSesion`),
  ADD UNIQUE KEY `uq_sesion` (`idAulaMateria`,`fecha`) COMMENT 'Una sola sesión por aula-materia por día',
  ADD KEY `idx_sesion_aulamateria` (`idAulaMateria`),
  ADD KEY `idx_sesion_fecha` (`fecha`),
  ADD KEY `idx_sesion_registrado` (`registrado_por`);

--
-- Indices de la tabla `auditoria_matricula`
--
ALTER TABLE `auditoria_matricula`
  ADD PRIMARY KEY (`idAuditoria`),
  ADD KEY `idx_audmat_matricula` (`idMatricula`),
  ADD KEY `idx_audmat_fecha` (`fecha`);

--
-- Indices de la tabla `auditoria_nota`
--
ALTER TABLE `auditoria_nota`
  ADD PRIMARY KEY (`idAuditoria`),
  ADD KEY `idx_audnota_nota` (`idNota`),
  ADD KEY `idx_audnota_usuario` (`idUsuario`),
  ADD KEY `idx_audnota_fecha` (`fecha`);

--
-- Indices de la tabla `auditoria_usuario`
--
ALTER TABLE `auditoria_usuario`
  ADD PRIMARY KEY (`idAuditoria`),
  ADD KEY `idx_audusr_afectado` (`idUsuario_afectado`),
  ADD KEY `idx_audusr_ejecutor` (`idUsuario_ejecutor`),
  ADD KEY `idx_audusr_fecha` (`fecha`);

--
-- Indices de la tabla `aula`
--
ALTER TABLE `aula`
  ADD PRIMARY KEY (`idAula`),
  ADD UNIQUE KEY `uq_aula` (`idAnio`,`idCurso`,`idEnfasis`),
  ADD KEY `idx_aula_anio` (`idAnio`),
  ADD KEY `idx_aula_curso` (`idCurso`),
  ADD KEY `idx_aula_enfasis` (`idEnfasis`);

--
-- Indices de la tabla `aula_materia`
--
ALTER TABLE `aula_materia`
  ADD PRIMARY KEY (`idAulaMateria`),
  ADD UNIQUE KEY `uq_aula_mat` (`idAula`,`idMateria`),
  ADD KEY `idx_aulamat_aula` (`idAula`),
  ADD KEY `idx_aulamat_materia` (`idMateria`);

--
-- Indices de la tabla `curso`
--
ALTER TABLE `curso`
  ADD PRIMARY KEY (`idCurso`),
  ADD UNIQUE KEY `uq_curso_numero` (`numero`),
  ADD KEY `idTurno` (`idTurno`);

--
-- Indices de la tabla `docente`
--
ALTER TABLE `docente`
  ADD PRIMARY KEY (`idDocente`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idx_docente_usuario` (`idUsuario`),
  ADD KEY `idx_docente_estado` (`estado`),
  ADD KEY `idx_docente_apellidos` (`apellidos`);

--
-- Indices de la tabla `docente_aula_materia`
--
ALTER TABLE `docente_aula_materia`
  ADD PRIMARY KEY (`idAsignacion`),
  ADD UNIQUE KEY `uq_doc_aula_mat` (`idDocente`,`idAulaMateria`),
  ADD KEY `idx_dam_docente` (`idDocente`),
  ADD KEY `idx_dam_aulamateria` (`idAulaMateria`);

--
-- Indices de la tabla `enfasis`
--
ALTER TABLE `enfasis`
  ADD PRIMARY KEY (`idEnfasis`);

--
-- Indices de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD PRIMARY KEY (`idEvaluacion`),
  ADD KEY `idx_eval_aulamat` (`idAulaMateria`),
  ADD KEY `idx_eval_periodo` (`idPeriodo`),
  ADD KEY `fk_eval_tipo` (`idTipoNota`);

--
-- Indices de la tabla `materia`
--
ALTER TABLE `materia`
  ADD PRIMARY KEY (`idMateria`),
  ADD KEY `idx_materia_enfasis` (`idEnfasis`),
  ADD KEY `idx_materia_curso` (`idCurso`);

--
-- Indices de la tabla `matricula`
--
ALTER TABLE `matricula`
  ADD PRIMARY KEY (`idMatricula`),
  ADD UNIQUE KEY `uq_matricula` (`idAlumno`,`idAula`),
  ADD KEY `idx_matricula_alumno` (`idAlumno`),
  ADD KEY `idx_matricula_aula` (`idAula`),
  ADD KEY `idx_matricula_estado` (`estado`);

--
-- Indices de la tabla `nota`
--
ALTER TABLE `nota`
  ADD PRIMARY KEY (`idNota`),
  ADD UNIQUE KEY `uq_nota` (`idMatricula`,`idEvaluacion`),
  ADD KEY `idx_nota_matricula` (`idMatricula`),
  ADD KEY `idx_nota_registradopor` (`registrado_por`),
  ADD KEY `fk_nota_evaluacion` (`idEvaluacion`);

--
-- Indices de la tabla `nota_final_materia`
--
ALTER TABLE `nota_final_materia`
  ADD PRIMARY KEY (`idNotaFinal`),
  ADD UNIQUE KEY `uq_nota_final` (`idMatricula`,`idAulaMateria`),
  ADD KEY `idx_nf_matricula` (`idMatricula`),
  ADD KEY `idx_nf_aulamateria` (`idAulaMateria`),
  ADD KEY `idx_nf_estado` (`estado`),
  ADD KEY `fk_nf_registrado` (`registrado_por`);

--
-- Indices de la tabla `periodo`
--
ALTER TABLE `periodo`
  ADD PRIMARY KEY (`idPeriodo`),
  ADD UNIQUE KEY `uq_periodo` (`idAnio`,`numero`),
  ADD KEY `idx_periodo_anio` (`idAnio`);

--
-- Indices de la tabla `periodo_excepcion`
--
ALTER TABLE `periodo_excepcion`
  ADD PRIMARY KEY (`idExcepcion`),
  ADD UNIQUE KEY `uq_excepcion` (`idPeriodo`,`idAulaMateria`),
  ADD KEY `idx_periodo` (`idPeriodo`),
  ADD KEY `idx_aulamateria` (`idAulaMateria`),
  ADD KEY `fk_excep_usuario` (`autorizado_por`);

--
-- Indices de la tabla `registro_catedra`
--
ALTER TABLE `registro_catedra`
  ADD PRIMARY KEY (`idRegCatedra`),
  ADD KEY `idAsignacion` (`idAsignacion`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idRol`);

--
-- Indices de la tabla `tipo_nota`
--
ALTER TABLE `tipo_nota`
  ADD PRIMARY KEY (`idTipoNota`);

--
-- Indices de la tabla `turno`
--
ALTER TABLE `turno`
  ADD PRIMARY KEY (`idTurno`);

--
-- Indices de la tabla `tutor`
--
ALTER TABLE `tutor`
  ADD PRIMARY KEY (`idTutor`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `idx_tutor_usuario` (`idUsuario`),
  ADD KEY `idx_tutor_estado` (`estado`),
  ADD KEY `idx_tutor_apellidos` (`apellidos`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idUsuario`),
  ADD UNIQUE KEY `uq_usuario_nombre` (`usuario`),
  ADD UNIQUE KEY `uq_usuario_correo` (`correo`),
  ADD KEY `idx_usuarios_rol` (`idRol`),
  ADD KEY `idx_usuarios_estado` (`estado`),
  ADD KEY `idx_usuarios_token` (`token`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alumno`
--
ALTER TABLE `alumno`
  MODIFY `idAlumno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `alumno_tutor`
--
ALTER TABLE `alumno_tutor`
  MODIFY `idAlumnoTutor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `anio_lectivo`
--
ALTER TABLE `anio_lectivo`
  MODIFY `idAnio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
  MODIFY `idDetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `asistencia_sesion`
--
ALTER TABLE `asistencia_sesion`
  MODIFY `idSesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `auditoria_matricula`
--
ALTER TABLE `auditoria_matricula`
  MODIFY `idAuditoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `auditoria_nota`
--
ALTER TABLE `auditoria_nota`
  MODIFY `idAuditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT de la tabla `auditoria_usuario`
--
ALTER TABLE `auditoria_usuario`
  MODIFY `idAuditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `aula`
--
ALTER TABLE `aula`
  MODIFY `idAula` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `aula_materia`
--
ALTER TABLE `aula_materia`
  MODIFY `idAulaMateria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `curso`
--
ALTER TABLE `curso`
  MODIFY `idCurso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `docente`
--
ALTER TABLE `docente`
  MODIFY `idDocente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `docente_aula_materia`
--
ALTER TABLE `docente_aula_materia`
  MODIFY `idAsignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `enfasis`
--
ALTER TABLE `enfasis`
  MODIFY `idEnfasis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  MODIFY `idEvaluacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `materia`
--
ALTER TABLE `materia`
  MODIFY `idMateria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de la tabla `matricula`
--
ALTER TABLE `matricula`
  MODIFY `idMatricula` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `nota`
--
ALTER TABLE `nota`
  MODIFY `idNota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT de la tabla `nota_final_materia`
--
ALTER TABLE `nota_final_materia`
  MODIFY `idNotaFinal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `periodo`
--
ALTER TABLE `periodo`
  MODIFY `idPeriodo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `periodo_excepcion`
--
ALTER TABLE `periodo_excepcion`
  MODIFY `idExcepcion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registro_catedra`
--
ALTER TABLE `registro_catedra`
  MODIFY `idRegCatedra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `idRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipo_nota`
--
ALTER TABLE `tipo_nota`
  MODIFY `idTipoNota` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `turno`
--
ALTER TABLE `turno`
  MODIFY `idTurno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tutor`
--
ALTER TABLE `tutor`
  MODIFY `idTutor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alumno_tutor`
--
ALTER TABLE `alumno_tutor`
  ADD CONSTRAINT `fk_at_alumno` FOREIGN KEY (`idAlumno`) REFERENCES `alumno` (`idAlumno`),
  ADD CONSTRAINT `fk_at_tutor` FOREIGN KEY (`idTutor`) REFERENCES `tutor` (`idTutor`);

--
-- Filtros para la tabla `asistencia_detalle`
--
ALTER TABLE `asistencia_detalle`
  ADD CONSTRAINT `fk_detalle_matricula` FOREIGN KEY (`idMatricula`) REFERENCES `matricula` (`idMatricula`),
  ADD CONSTRAINT `fk_detalle_sesion` FOREIGN KEY (`idSesion`) REFERENCES `asistencia_sesion` (`idSesion`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asistencia_sesion`
--
ALTER TABLE `asistencia_sesion`
  ADD CONSTRAINT `fk_sesion_aulamateria` FOREIGN KEY (`idAulaMateria`) REFERENCES `aula_materia` (`idAulaMateria`),
  ADD CONSTRAINT `fk_sesion_registrado` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`idUsuario`);

--
-- Filtros para la tabla `aula`
--
ALTER TABLE `aula`
  ADD CONSTRAINT `fk_aula_anio` FOREIGN KEY (`idAnio`) REFERENCES `anio_lectivo` (`idAnio`),
  ADD CONSTRAINT `fk_aula_curso` FOREIGN KEY (`idCurso`) REFERENCES `curso` (`idCurso`),
  ADD CONSTRAINT `fk_aula_enfasis` FOREIGN KEY (`idEnfasis`) REFERENCES `enfasis` (`idEnfasis`);

--
-- Filtros para la tabla `aula_materia`
--
ALTER TABLE `aula_materia`
  ADD CONSTRAINT `fk_am_aula` FOREIGN KEY (`idAula`) REFERENCES `aula` (`idAula`),
  ADD CONSTRAINT `fk_am_materia` FOREIGN KEY (`idMateria`) REFERENCES `materia` (`idMateria`);

--
-- Filtros para la tabla `curso`
--
ALTER TABLE `curso`
  ADD CONSTRAINT `curso_ibfk_1` FOREIGN KEY (`idTurno`) REFERENCES `turno` (`idTurno`);

--
-- Filtros para la tabla `docente`
--
ALTER TABLE `docente`
  ADD CONSTRAINT `fk_doc_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`);

--
-- Filtros para la tabla `docente_aula_materia`
--
ALTER TABLE `docente_aula_materia`
  ADD CONSTRAINT `fk_dam_aulamateria` FOREIGN KEY (`idAulaMateria`) REFERENCES `aula_materia` (`idAulaMateria`),
  ADD CONSTRAINT `fk_dam_docente` FOREIGN KEY (`idDocente`) REFERENCES `docente` (`idDocente`);

--
-- Filtros para la tabla `evaluacion`
--
ALTER TABLE `evaluacion`
  ADD CONSTRAINT `fk_eval_aulamat` FOREIGN KEY (`idAulaMateria`) REFERENCES `aula_materia` (`idAulaMateria`),
  ADD CONSTRAINT `fk_eval_periodo` FOREIGN KEY (`idPeriodo`) REFERENCES `periodo` (`idPeriodo`),
  ADD CONSTRAINT `fk_eval_tipo` FOREIGN KEY (`idTipoNota`) REFERENCES `tipo_nota` (`idTipoNota`);

--
-- Filtros para la tabla `materia`
--
ALTER TABLE `materia`
  ADD CONSTRAINT `fk_mat_enfasis` FOREIGN KEY (`idEnfasis`) REFERENCES `enfasis` (`idEnfasis`),
  ADD CONSTRAINT `fk_materia_curso` FOREIGN KEY (`idCurso`) REFERENCES `curso` (`idCurso`);

--
-- Filtros para la tabla `matricula`
--
ALTER TABLE `matricula`
  ADD CONSTRAINT `fk_mat_alumno` FOREIGN KEY (`idAlumno`) REFERENCES `alumno` (`idAlumno`),
  ADD CONSTRAINT `fk_mat_aula` FOREIGN KEY (`idAula`) REFERENCES `aula` (`idAula`);

--
-- Filtros para la tabla `nota`
--
ALTER TABLE `nota`
  ADD CONSTRAINT `fk_nota_evaluacion` FOREIGN KEY (`idEvaluacion`) REFERENCES `evaluacion` (`idEvaluacion`),
  ADD CONSTRAINT `fk_nota_matricula` FOREIGN KEY (`idMatricula`) REFERENCES `matricula` (`idMatricula`),
  ADD CONSTRAINT `fk_nota_registrado` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`idUsuario`);

--
-- Filtros para la tabla `nota_final_materia`
--
ALTER TABLE `nota_final_materia`
  ADD CONSTRAINT `fk_nf_aulamateria` FOREIGN KEY (`idAulaMateria`) REFERENCES `aula_materia` (`idAulaMateria`),
  ADD CONSTRAINT `fk_nf_matricula` FOREIGN KEY (`idMatricula`) REFERENCES `matricula` (`idMatricula`),
  ADD CONSTRAINT `fk_nf_registrado` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`idUsuario`);

--
-- Filtros para la tabla `periodo`
--
ALTER TABLE `periodo`
  ADD CONSTRAINT `fk_per_anio` FOREIGN KEY (`idAnio`) REFERENCES `anio_lectivo` (`idAnio`);

--
-- Filtros para la tabla `periodo_excepcion`
--
ALTER TABLE `periodo_excepcion`
  ADD CONSTRAINT `fk_excep_aulamateria` FOREIGN KEY (`idAulaMateria`) REFERENCES `aula_materia` (`idAulaMateria`),
  ADD CONSTRAINT `fk_excep_periodo` FOREIGN KEY (`idPeriodo`) REFERENCES `periodo` (`idPeriodo`),
  ADD CONSTRAINT `fk_excep_usuario` FOREIGN KEY (`autorizado_por`) REFERENCES `usuarios` (`idUsuario`);

--
-- Filtros para la tabla `registro_catedra`
--
ALTER TABLE `registro_catedra`
  ADD CONSTRAINT `registro_catedra_ibfk_1` FOREIGN KEY (`idAsignacion`) REFERENCES `docente_aula_materia` (`idAsignacion`);

--
-- Filtros para la tabla `tutor`
--
ALTER TABLE `tutor`
  ADD CONSTRAINT `fk_tut_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`idRol`) REFERENCES `rol` (`idRol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
