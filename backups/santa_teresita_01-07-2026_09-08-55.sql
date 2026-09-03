-- =====================================
-- RESPALDO AUTOMÁTICO
-- Santa Teresita
-- Fecha: 01/07/2026 09:08:55
-- =====================================



DROP TABLE IF EXISTS `alumno`;
CREATE TABLE `alumno` (
  `idAlumno` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) DEFAULT NULL COMMENT 'Puede no tener CI si es menor',
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nac` date NOT NULL,
  `sexo` enum('M','F') NOT NULL,
  `direccion` varchar(250) DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Egresado','Retirado') NOT NULL DEFAULT 'Activo',
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idAlumno`),
  KEY `idx_alumno_estado` (`estado`),
  KEY `idx_alumno_apellidos` (`apellidos`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Datos personales del alumno';

INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('1','5001001','Ana Sofía','Rodríguez Martínez','2009-03-12','F',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('2','5001002','Luis Fernando','González Pérez','2009-07-25','M',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('3','5001003','María José','López Sánchez','2009-11-08','F',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('4','5001004','Diego Alexis','Benítez Torres','2009-01-30','M',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('5','5001005','Valentina','Cabrera Duarte','2009-05-17','F',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('6','5001006','Rodrigo Javier','Ortiz Ramírez','2009-09-04','M',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('7','5001007','Luciana Belen','Herrera Villalba','2009-02-21','F',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('8','5001008','Sebastián','Morales Acosta','2009-06-14','M',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('9','5001009','Camila Paola','Vera Aquino','2008-04-03','F',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('10','5001010','Matías Eduardo','Fernández Ríos','2008-08-19','M',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('11','5001011','Agustina','Paredes Leiva','2008-12-27','F',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('12','5001012','Nicolás Ariel','Cardozo Mendoza','2008-03-08','M',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('13','5001013','Florencia','Núñez Castro','2008-07-22','F',NULL,'Activo','2026-05-14 10:19:55','2026-05-14 10:19:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('14','1233256','Paola Patricia','Oviedo','0000-00-00','M',NULL,'Activo','2026-05-14 10:23:20','2026-05-14 10:23:20');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('16','4500488','Maria José','Gonzalez Perez','2010-03-05','M','Avda. Eusebio Ayala 654','Activo','2026-06-25 09:14:31','2026-06-25 09:15:22');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('17','7548985','Antonio Cirulo','Ortiz Gill','2007-06-22','M','','Activo','2026-06-30 15:58:59','2026-06-30 15:58:59');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('18','7015487','Maria Serena','Ortiz Chavez','2007-06-01','F','','Activo','2026-06-30 16:01:55','2026-06-30 16:01:55');
INSERT INTO `alumno` (`idAlumno`,`cedula`,`nombres`,`apellidos`,`fecha_nac`,`sexo`,`direccion`,`estado`,`creado`,`modificado`) VALUES ('19','7058859','Alberto','Valenzuela','2007-06-01','M','','Activo','2026-06-30 16:03:31','2026-06-30 16:03:31');



DROP TABLE IF EXISTS `alumno_tutor`;
CREATE TABLE `alumno_tutor` (
  `idAlumnoTutor` int(11) NOT NULL AUTO_INCREMENT,
  `idAlumno` int(11) NOT NULL,
  `idTutor` int(11) NOT NULL,
  `es_principal` enum('Sí','No') NOT NULL COMMENT 'Tutor principal de contacto',
  PRIMARY KEY (`idAlumnoTutor`),
  UNIQUE KEY `uq_alumno_tutor` (`idAlumno`,`idTutor`),
  KEY `idx_at_alumno` (`idAlumno`),
  KEY `idx_at_tutor` (`idTutor`),
  CONSTRAINT `fk_at_alumno` FOREIGN KEY (`idAlumno`) REFERENCES `alumno` (`idAlumno`),
  CONSTRAINT `fk_at_tutor` FOREIGN KEY (`idTutor`) REFERENCES `tutor` (`idTutor`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Relación alumno-tutor (un alumno puede tener varios tutores)';

INSERT INTO `alumno_tutor` (`idAlumnoTutor`,`idAlumno`,`idTutor`,`es_principal`) VALUES ('1','1','1','Sí');
INSERT INTO `alumno_tutor` (`idAlumnoTutor`,`idAlumno`,`idTutor`,`es_principal`) VALUES ('2','1','2','Sí');
INSERT INTO `alumno_tutor` (`idAlumnoTutor`,`idAlumno`,`idTutor`,`es_principal`) VALUES ('3','2','3','Sí');
INSERT INTO `alumno_tutor` (`idAlumnoTutor`,`idAlumno`,`idTutor`,`es_principal`) VALUES ('9','4','1','No');



DROP TABLE IF EXISTS `anio_lectivo`;
CREATE TABLE `anio_lectivo` (
  `idAnio` int(11) NOT NULL AUTO_INCREMENT,
  `anio` year(4) NOT NULL COMMENT 'Ej: 2026',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` enum('Sí','No') NOT NULL COMMENT 'Solo uno activo a la vez',
  PRIMARY KEY (`idAnio`),
  UNIQUE KEY `anio` (`anio`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Años lectivos';

INSERT INTO `anio_lectivo` (`idAnio`,`anio`,`fecha_inicio`,`fecha_fin`,`activo`) VALUES ('1','2026','2026-02-16','2026-11-30','Sí');



DROP TABLE IF EXISTS `asistencia_detalle`;
CREATE TABLE `asistencia_detalle` (
  `idDetalle` int(11) NOT NULL AUTO_INCREMENT,
  `idSesion` int(11) NOT NULL COMMENT 'Sesión a la que pertenece este detalle',
  `idMatricula` int(11) NOT NULL COMMENT 'Alumno matriculado',
  `estado` enum('Presente','Ausente','Tardanza','Justificado') NOT NULL DEFAULT 'Presente',
  `observacion` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`idDetalle`),
  UNIQUE KEY `uq_detalle` (`idSesion`,`idMatricula`) COMMENT 'Un registro por alumno por sesión',
  KEY `idx_detalle_sesion` (`idSesion`),
  KEY `idx_detalle_matricula` (`idMatricula`),
  CONSTRAINT `fk_detalle_matricula` FOREIGN KEY (`idMatricula`) REFERENCES `matricula` (`idMatricula`),
  CONSTRAINT `fk_detalle_sesion` FOREIGN KEY (`idSesion`) REFERENCES `asistencia_sesion` (`idSesion`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detalle de asistencia: estado de cada alumno por sesión';

INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('7','3','4','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('8','3','5','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('9','3','2','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('10','3','3','Ausente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('11','3','14','Ausente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('12','3','1','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('13','4','10','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('14','4','11','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('15','4','9','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('16','5','4','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('17','5','5','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('18','5','2','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('19','5','3','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('20','5','14','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('21','5','1','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('28','6','4','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('29','6','5','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('30','6','2','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('31','6','3','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('32','6','14','Presente','');
INSERT INTO `asistencia_detalle` (`idDetalle`,`idSesion`,`idMatricula`,`estado`,`observacion`) VALUES ('33','6','1','Presente','');



DROP TABLE IF EXISTS `asistencia_sesion`;
CREATE TABLE `asistencia_sesion` (
  `idSesion` int(11) NOT NULL AUTO_INCREMENT,
  `idAulaMateria` int(11) NOT NULL COMMENT 'Aula + Materia de la sesión',
  `fecha` date NOT NULL COMMENT 'Fecha de la clase',
  `cantidad_horas` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Horas dictadas en esta sesión',
  `registrado_por` int(11) NOT NULL COMMENT 'Docente que registró (FK → usuarios)',
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idSesion`),
  UNIQUE KEY `uq_sesion` (`idAulaMateria`,`fecha`) COMMENT 'Una sola sesión por aula-materia por día',
  KEY `idx_sesion_aulamateria` (`idAulaMateria`),
  KEY `idx_sesion_fecha` (`fecha`),
  KEY `idx_sesion_registrado` (`registrado_por`),
  CONSTRAINT `fk_sesion_aulamateria` FOREIGN KEY (`idAulaMateria`) REFERENCES `aula_materia` (`idAulaMateria`),
  CONSTRAINT `fk_sesion_registrado` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cabecera de asistencia: una fila por sesión de clase dictada';

INSERT INTO `asistencia_sesion` (`idSesion`,`idAulaMateria`,`fecha`,`cantidad_horas`,`registrado_por`,`creado`,`modificado`) VALUES ('3','1','2026-06-22','1','4','2026-06-22 16:24:05','2026-06-22 16:24:43');
INSERT INTO `asistencia_sesion` (`idSesion`,`idAulaMateria`,`fecha`,`cantidad_horas`,`registrado_por`,`creado`,`modificado`) VALUES ('4','7','2026-06-18','6','4','2026-06-22 17:06:26','2026-06-22 17:06:26');
INSERT INTO `asistencia_sesion` (`idSesion`,`idAulaMateria`,`fecha`,`cantidad_horas`,`registrado_por`,`creado`,`modificado`) VALUES ('5','13','2026-06-23','1','4','2026-06-23 10:31:47','2026-06-23 10:31:47');
INSERT INTO `asistencia_sesion` (`idSesion`,`idAulaMateria`,`fecha`,`cantidad_horas`,`registrado_por`,`creado`,`modificado`) VALUES ('6','13','2026-06-25','1','4','2026-06-25 15:48:36','2026-06-25 15:57:49');



DROP TABLE IF EXISTS `auditoria_matricula`;
CREATE TABLE `auditoria_matricula` (
  `idAuditoria` int(11) NOT NULL AUTO_INCREMENT,
  `idMatricula` int(11) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `estado_antes` varchar(50) DEFAULT NULL,
  `estado_despues` varchar(50) DEFAULT NULL,
  `idUsuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  PRIMARY KEY (`idAuditoria`),
  KEY `idx_audmat_matricula` (`idMatricula`),
  KEY `idx_audmat_fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de cambios en matrículas';




DROP TABLE IF EXISTS `auditoria_nota`;
CREATE TABLE `auditoria_nota` (
  `idAuditoria` int(11) NOT NULL AUTO_INCREMENT,
  `idNota` int(11) NOT NULL,
  `accion` enum('INSERT','UPDATE','DELETE') NOT NULL,
  `valor_antes` decimal(4,2) DEFAULT NULL,
  `valor_despues` decimal(4,2) DEFAULT NULL,
  `campo` varchar(50) DEFAULT NULL COMMENT 'Campo modificado',
  `idUsuario` int(11) NOT NULL COMMENT 'Quién realizó la acción',
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL COMMENT 'IP de la sesión (IPv4 o IPv6)',
  `detalle` text DEFAULT NULL,
  PRIMARY KEY (`idAuditoria`),
  KEY `idx_audnota_nota` (`idNota`),
  KEY `idx_audnota_usuario` (`idUsuario`),
  KEY `idx_audnota_fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de cambios en calificaciones';

INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('1','1','INSERT',NULL,'20.00','puntos_obtenidos','4','2026-06-22 16:31:25','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('2','2','INSERT',NULL,'12.00','puntos_obtenidos','4','2026-06-22 16:31:25','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('3','3','INSERT',NULL,'11.00','puntos_obtenidos','4','2026-06-22 16:31:25','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('4','4','INSERT',NULL,'9.00','puntos_obtenidos','4','2026-06-22 16:31:25','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('5','5','INSERT',NULL,'20.00','puntos_obtenidos','4','2026-06-22 16:31:25','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('6','6','INSERT',NULL,'0.00','puntos_obtenidos','4','2026-06-22 16:31:54','::1','editar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('7','2','UPDATE','12.00','14.00','puntos_obtenidos','4','2026-06-22 16:36:59','::1','editar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('8','7','INSERT',NULL,'12.00','puntos_obtenidos','4','2026-06-22 16:54:44','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('9','8','INSERT',NULL,'12.00','puntos_obtenidos','4','2026-06-22 16:54:44','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('10','9','INSERT',NULL,'11.00','puntos_obtenidos','4','2026-06-22 16:54:45','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('11','10','INSERT',NULL,'10.00','puntos_obtenidos','4','2026-06-22 16:54:45','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('12','11','INSERT',NULL,'11.00','puntos_obtenidos','4','2026-06-22 16:54:45','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('13','12','INSERT',NULL,'10.00','puntos_obtenidos','4','2026-06-22 16:54:45','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('14','9','UPDATE','11.00','5.00','puntos_obtenidos','4','2026-06-22 16:59:43','192.168.0.5','editar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('15','13','INSERT',NULL,'15.00','puntos_obtenidos','4','2026-06-29 15:43:19','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('16','14','INSERT',NULL,'20.00','puntos_obtenidos','4','2026-06-29 15:43:19','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('17','15','INSERT',NULL,'14.00','puntos_obtenidos','4','2026-06-29 15:43:19','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('18','16','INSERT',NULL,'12.00','puntos_obtenidos','4','2026-06-29 15:43:19','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('19','17','INSERT',NULL,'10.00','puntos_obtenidos','4','2026-06-29 15:43:19','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('20','18','INSERT',NULL,'7.00','puntos_obtenidos','4','2026-06-29 15:43:19','::1','guardar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('21','13','UPDATE','15.00','18.00','puntos_obtenidos','4','2026-06-29 15:44:19','::1','editar_calificaciones_lote');
INSERT INTO `auditoria_nota` (`idAuditoria`,`idNota`,`accion`,`valor_antes`,`valor_despues`,`campo`,`idUsuario`,`fecha`,`ip`,`detalle`) VALUES ('22','15','UPDATE','14.00','7.00','puntos_obtenidos','4','2026-06-29 15:44:19','::1','editar_calificaciones_lote');



DROP TABLE IF EXISTS `auditoria_usuario`;
CREATE TABLE `auditoria_usuario` (
  `idAuditoria` int(11) NOT NULL AUTO_INCREMENT,
  `idUsuario_afectado` int(11) NOT NULL,
  `accion` enum('CREAR','MODIFICAR','DESACTIVAR','ACTIVAR','LOGIN','LOGOUT','CAMBIO_PASS') NOT NULL,
  `idUsuario_ejecutor` int(11) NOT NULL COMMENT 'Quién realizó la acción',
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  PRIMARY KEY (`idAuditoria`),
  KEY `idx_audusr_afectado` (`idUsuario_afectado`),
  KEY `idx_audusr_ejecutor` (`idUsuario_ejecutor`),
  KEY `idx_audusr_fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de acciones sobre usuarios';

INSERT INTO `auditoria_usuario` (`idAuditoria`,`idUsuario_afectado`,`accion`,`idUsuario_ejecutor`,`fecha`,`ip`,`detalle`) VALUES ('1','4','MODIFICAR','4','2026-06-29 15:58:45','::1','Usuario actualizado');



DROP TABLE IF EXISTS `aula`;
CREATE TABLE `aula` (
  `idAula` int(11) NOT NULL AUTO_INCREMENT,
  `idAnio` int(11) NOT NULL,
  `idCurso` int(11) NOT NULL,
  `idEnfasis` int(11) NOT NULL,
  `activo` enum('Sí','No') NOT NULL,
  PRIMARY KEY (`idAula`),
  UNIQUE KEY `uq_aula` (`idAnio`,`idCurso`,`idEnfasis`),
  KEY `idx_aula_anio` (`idAnio`),
  KEY `idx_aula_curso` (`idCurso`),
  KEY `idx_aula_enfasis` (`idEnfasis`),
  CONSTRAINT `fk_aula_anio` FOREIGN KEY (`idAnio`) REFERENCES `anio_lectivo` (`idAnio`),
  CONSTRAINT `fk_aula_curso` FOREIGN KEY (`idCurso`) REFERENCES `curso` (`idCurso`),
  CONSTRAINT `fk_aula_enfasis` FOREIGN KEY (`idEnfasis`) REFERENCES `enfasis` (`idEnfasis`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Aula = grupo de alumnos (Año + Curso + Sección + Énfasis). Ej: 1°A Ciencias 2026';

INSERT INTO `aula` (`idAula`,`idAnio`,`idCurso`,`idEnfasis`,`activo`) VALUES ('1','1','1','1','Sí');
INSERT INTO `aula` (`idAula`,`idAnio`,`idCurso`,`idEnfasis`,`activo`) VALUES ('2','1','1','2','Sí');
INSERT INTO `aula` (`idAula`,`idAnio`,`idCurso`,`idEnfasis`,`activo`) VALUES ('3','1','2','1','Sí');
INSERT INTO `aula` (`idAula`,`idAnio`,`idCurso`,`idEnfasis`,`activo`) VALUES ('4','1','2','2','Sí');
INSERT INTO `aula` (`idAula`,`idAnio`,`idCurso`,`idEnfasis`,`activo`) VALUES ('5','1','3','1','Sí');
INSERT INTO `aula` (`idAula`,`idAnio`,`idCurso`,`idEnfasis`,`activo`) VALUES ('6','1','3','2','Sí');



DROP TABLE IF EXISTS `aula_materia`;
CREATE TABLE `aula_materia` (
  `idAulaMateria` int(11) NOT NULL AUTO_INCREMENT,
  `idAula` int(11) NOT NULL,
  `idMateria` int(11) NOT NULL,
  `activo` enum('Sí','No') NOT NULL,
  PRIMARY KEY (`idAulaMateria`),
  UNIQUE KEY `uq_aula_mat` (`idAula`,`idMateria`),
  KEY `idx_aulamat_aula` (`idAula`),
  KEY `idx_aulamat_materia` (`idMateria`),
  CONSTRAINT `fk_am_aula` FOREIGN KEY (`idAula`) REFERENCES `aula` (`idAula`),
  CONSTRAINT `fk_am_materia` FOREIGN KEY (`idMateria`) REFERENCES `materia` (`idMateria`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Materias asignadas a cada aula';

INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('1','1','1','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('2','1','2','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('3','1','3','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('4','2','1','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('5','2','2','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('6','2','3','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('7','3','4','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('8','3','5','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('9','4','6','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('10','6','15','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('11','6','8','No');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('13','1','16','Sí');
INSERT INTO `aula_materia` (`idAulaMateria`,`idAula`,`idMateria`,`activo`) VALUES ('15','1','15','Sí');



DROP TABLE IF EXISTS `curso`;
CREATE TABLE `curso` (
  `idCurso` int(11) NOT NULL AUTO_INCREMENT,
  `numero` tinyint(1) NOT NULL COMMENT '1, 2 o 3 (Primer, Segundo, Tercer curso Media)',
  `nombre` varchar(50) NOT NULL COMMENT 'Ej: Primer Curso',
  `idTurno` int(11) NOT NULL,
  PRIMARY KEY (`idCurso`),
  UNIQUE KEY `uq_curso_numero` (`numero`),
  KEY `idTurno` (`idTurno`),
  CONSTRAINT `curso_ibfk_1` FOREIGN KEY (`idTurno`) REFERENCES `turno` (`idTurno`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cursos de la educación media (1°, 2°, 3°)';

INSERT INTO `curso` (`idCurso`,`numero`,`nombre`,`idTurno`) VALUES ('1','1','Primer Curso','1');
INSERT INTO `curso` (`idCurso`,`numero`,`nombre`,`idTurno`) VALUES ('2','2','Segundo Curso','1');
INSERT INTO `curso` (`idCurso`,`numero`,`nombre`,`idTurno`) VALUES ('3','3','Tercer Curso','1');



DROP TABLE IF EXISTS `docente`;
CREATE TABLE `docente` (
  `idDocente` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(150) NOT NULL COMMENT 'Obligatorio: se usará para crear su cuenta de usuario',
  `direccion` varchar(250) DEFAULT NULL,
  `fecha_nac` date DEFAULT NULL,
  `titulo` varchar(200) DEFAULT NULL COMMENT 'Título profesional',
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `idUsuario` int(11) DEFAULT NULL COMMENT 'Cuenta creada en segundo paso tras registrar el docente',
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idDocente`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `correo` (`correo`),
  KEY `idx_docente_usuario` (`idUsuario`),
  KEY `idx_docente_estado` (`estado`),
  KEY `idx_docente_apellidos` (`apellidos`),
  CONSTRAINT `fk_doc_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Datos personales del personal docente';

INSERT INTO `docente` (`idDocente`,`cedula`,`nombres`,`apellidos`,`telefono`,`correo`,`direccion`,`fecha_nac`,`titulo`,`estado`,`idUsuario`,`creado`,`modificado`) VALUES ('1','1234567','Alvaro','Ortega','0981-123456','alvaroortegadominguez11@gmail.com',NULL,NULL,NULL,'Activo','4','2026-05-14 10:19:55','2026-05-16 13:07:44');
INSERT INTO `docente` (`idDocente`,`cedula`,`nombres`,`apellidos`,`telefono`,`correo`,`direccion`,`fecha_nac`,`titulo`,`estado`,`idUsuario`,`creado`,`modificado`) VALUES ('3','4500127','Carlos Alberto','Ortiz Ramirez','0981 123 456','','Avda. Eusebio Ayala 654','1990-02-08','Licenciado en Matemáticas','Activo',NULL,'2026-06-25 09:47:00','2026-06-30 16:07:25');



DROP TABLE IF EXISTS `docente_aula_materia`;
CREATE TABLE `docente_aula_materia` (
  `idAsignacion` int(11) NOT NULL AUTO_INCREMENT,
  `idDocente` int(11) NOT NULL,
  `idAulaMateria` int(11) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`idAsignacion`),
  UNIQUE KEY `uq_doc_aula_mat` (`idDocente`,`idAulaMateria`),
  KEY `idx_dam_docente` (`idDocente`),
  KEY `idx_dam_aulamateria` (`idAulaMateria`),
  CONSTRAINT `fk_dam_aulamateria` FOREIGN KEY (`idAulaMateria`) REFERENCES `aula_materia` (`idAulaMateria`),
  CONSTRAINT `fk_dam_docente` FOREIGN KEY (`idDocente`) REFERENCES `docente` (`idDocente`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Asignación: qué docente dicta qué materia en qué aula';

INSERT INTO `docente_aula_materia` (`idAsignacion`,`idDocente`,`idAulaMateria`,`activo`) VALUES ('4','1','7','1');
INSERT INTO `docente_aula_materia` (`idAsignacion`,`idDocente`,`idAulaMateria`,`activo`) VALUES ('5','1','9','1');
INSERT INTO `docente_aula_materia` (`idAsignacion`,`idDocente`,`idAulaMateria`,`activo`) VALUES ('14','1','2','1');
INSERT INTO `docente_aula_materia` (`idAsignacion`,`idDocente`,`idAulaMateria`,`activo`) VALUES ('16','1','1','1');
INSERT INTO `docente_aula_materia` (`idAsignacion`,`idDocente`,`idAulaMateria`,`activo`) VALUES ('17','1','5','1');
INSERT INTO `docente_aula_materia` (`idAsignacion`,`idDocente`,`idAulaMateria`,`activo`) VALUES ('19','1','13','1');
INSERT INTO `docente_aula_materia` (`idAsignacion`,`idDocente`,`idAulaMateria`,`activo`) VALUES ('24','3','8','1');



DROP TABLE IF EXISTS `enfasis`;
CREATE TABLE `enfasis` (
  `idEnfasis` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Ciencias Básicas, Humanístico, Técnico Administrativo',
  `descripcion` varchar(250) DEFAULT NULL,
  `activo` enum('Sí','No') NOT NULL,
  PRIMARY KEY (`idEnfasis`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Énfasis del bachillerato';

INSERT INTO `enfasis` (`idEnfasis`,`nombre`,`descripcion`,`activo`) VALUES ('1','Ciencias Básicas','Orientado a ciencias exactas y naturales','Sí');
INSERT INTO `enfasis` (`idEnfasis`,`nombre`,`descripcion`,`activo`) VALUES ('2','Ciencias Sociales','Orientado a letras, historia y sociales','Sí');
INSERT INTO `enfasis` (`idEnfasis`,`nombre`,`descripcion`,`activo`) VALUES ('3','Bachillerato Técnico en Ciencias Contables','Formación técnica en ciencias contables','Sí');
INSERT INTO `enfasis` (`idEnfasis`,`nombre`,`descripcion`,`activo`) VALUES ('4','Ninguno','Materias comunes a todos los énfasis','Sí');



DROP TABLE IF EXISTS `evaluacion`;
CREATE TABLE `evaluacion` (
  `idEvaluacion` int(11) NOT NULL AUTO_INCREMENT,
  `idAulaMateria` int(11) NOT NULL COMMENT 'Relación con el docente y la materia',
  `idPeriodo` int(11) NOT NULL COMMENT 'A qué bimestre pertenece',
  `idTipoNota` int(11) NOT NULL COMMENT 'Si es Parcial, TP, etc.',
  `nombre` varchar(150) NOT NULL COMMENT 'Ej: Primer Parcial de Matemática o TP 1 - Vectores',
  `puntos_total` int(11) NOT NULL DEFAULT 0 COMMENT 'Total de puntos de la evaluación',
  `fecha_evaluacion` date DEFAULT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idEvaluacion`),
  KEY `idx_eval_aulamat` (`idAulaMateria`),
  KEY `idx_eval_periodo` (`idPeriodo`),
  KEY `fk_eval_tipo` (`idTipoNota`),
  CONSTRAINT `fk_eval_aulamat` FOREIGN KEY (`idAulaMateria`) REFERENCES `aula_materia` (`idAulaMateria`),
  CONSTRAINT `fk_eval_periodo` FOREIGN KEY (`idPeriodo`) REFERENCES `periodo` (`idPeriodo`),
  CONSTRAINT `fk_eval_tipo` FOREIGN KEY (`idTipoNota`) REFERENCES `tipo_nota` (`idTipoNota`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Definición de evaluaciones creadas por el docente';

INSERT INTO `evaluacion` (`idEvaluacion`,`idAulaMateria`,`idPeriodo`,`idTipoNota`,`nombre`,`puntos_total`,`fecha_evaluacion`,`creado`,`modificado`) VALUES ('16','1','1','4','Parcial 1','20','2026-06-24','2026-06-22 16:16:59','2026-06-22 16:16:59');
INSERT INTO `evaluacion` (`idEvaluacion`,`idAulaMateria`,`idPeriodo`,`idTipoNota`,`nombre`,`puntos_total`,`fecha_evaluacion`,`creado`,`modificado`) VALUES ('17','9','1','3','Exposicion','20','2026-06-26','2026-06-22 16:26:38','2026-06-22 16:27:17');
INSERT INTO `evaluacion` (`idEvaluacion`,`idAulaMateria`,`idPeriodo`,`idTipoNota`,`nombre`,`puntos_total`,`fecha_evaluacion`,`creado`,`modificado`) VALUES ('19','13','1','2','Ecuaciones','12','2026-06-25','2026-06-22 16:53:03','2026-06-22 16:53:03');



DROP TABLE IF EXISTS `materia`;
CREATE TABLE `materia` (
  `idMateria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL COMMENT 'Código interno (ej: MAT-101)',
  `horas_sem` tinyint(1) NOT NULL DEFAULT 4 COMMENT 'Horas semanales',
  `idEnfasis` int(11) DEFAULT NULL COMMENT 'NULL = materia común a todos los énfasis',
  `activo` enum('Sí','No') NOT NULL,
  `plan` varchar(50) NOT NULL,
  `idCurso` int(11) NOT NULL,
  PRIMARY KEY (`idMateria`),
  KEY `idx_materia_enfasis` (`idEnfasis`),
  KEY `idx_materia_curso` (`idCurso`),
  CONSTRAINT `fk_mat_enfasis` FOREIGN KEY (`idEnfasis`) REFERENCES `enfasis` (`idEnfasis`),
  CONSTRAINT `fk_materia_curso` FOREIGN KEY (`idCurso`) REFERENCES `curso` (`idCurso`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Asignaturas/materias';

INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('1','Lengua y Literatura Castellana','LEN-101','4','4','Sí','Plan Común','1');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('2','Matemática','MAT-101','5','4','Sí','Plan Común','1');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('3','Lengua Extranjera (Inglés)','ING-101','3','4','Sí','Plan Común','1');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('4','Lengua Guaraní','GUA-101','2','4','Sí','Plan Común','1');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('5','Historia','HIS-101','3','4','Sí','Plan Común','1');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('6','Geografía','GEO-101','2','4','Sí','Plan Común','2');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('7','Física','FIS-101','3','1','Sí','Plan Específico','2');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('8','Química','QUI-101','3','1','Sí','Plan Específico','2');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('9','Biología','BIO-101','3','1','Sí','Plan Específico','2');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('10','Filosofía','FIL-101','3','2','Sí','Plan Específico','2');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('11','Sociología','SOC-101','3','2','Sí','Plan Específico','3');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('12','Educación Física','EDF-101','2','4','Sí','Plan Común','3');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('13','Arte y Expresión','ART-101','2','4','Sí','Plan Común','3');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('14','Formación Ética y Ciudadana','ETI-101','2','4','Sí','Plan Común','3');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('15','Gestión Empresarial','GES-101','4','3','Sí','Plan Específico','3');
INSERT INTO `materia` (`idMateria`,`nombre`,`codigo`,`horas_sem`,`idEnfasis`,`activo`,`plan`,`idCurso`) VALUES ('16','Contabilidad','CON-101','4','3','Sí','Plan Específico','3');



DROP TABLE IF EXISTS `matricula`;
CREATE TABLE `matricula` (
  `idMatricula` int(11) NOT NULL AUTO_INCREMENT,
  `idAlumno` int(11) NOT NULL,
  `idAula` int(11) NOT NULL,
  `fecha_matricula` date NOT NULL DEFAULT curdate(),
  `estado` enum('Vigente','Retirado','Trasladado','Promovido','Reprobado') NOT NULL DEFAULT 'Vigente',
  `observacion` text DEFAULT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idMatricula`),
  UNIQUE KEY `uq_matricula` (`idAlumno`,`idAula`),
  KEY `idx_matricula_alumno` (`idAlumno`),
  KEY `idx_matricula_aula` (`idAula`),
  KEY `idx_matricula_estado` (`estado`),
  CONSTRAINT `fk_mat_alumno` FOREIGN KEY (`idAlumno`) REFERENCES `alumno` (`idAlumno`),
  CONSTRAINT `fk_mat_aula` FOREIGN KEY (`idAula`) REFERENCES `aula` (`idAula`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Inscripción del alumno en un aula por año lectivo';

INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('1','1','1','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('2','2','1','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('3','3','1','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('4','4','1','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('5','5','1','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('6','6','2','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('7','7','2','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('8','8','2','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('9','9','3','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('10','10','3','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('11','11','3','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('12','12','4','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('13','13','4','2026-02-16','Vigente',NULL,'2026-05-14 10:19:55');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('14','14','1','2026-06-03','Vigente','LCcoa','2026-06-03 13:58:54');
INSERT INTO `matricula` (`idMatricula`,`idAlumno`,`idAula`,`fecha_matricula`,`estado`,`observacion`,`creado`) VALUES ('15','16','3','2026-06-25','Vigente','Ejemplo editado hoy','2026-06-25 09:22:57');



DROP TABLE IF EXISTS `nota`;
CREATE TABLE `nota` (
  `idNota` int(11) NOT NULL AUTO_INCREMENT,
  `idMatricula` int(11) NOT NULL,
  `idEvaluacion` int(11) NOT NULL,
  `puntos_obtenidos` int(11) NOT NULL DEFAULT 0,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` varchar(300) DEFAULT NULL,
  `registrado_por` int(11) NOT NULL COMMENT 'idUsuario que registró',
  `modificado_por` int(11) DEFAULT NULL,
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idNota`),
  UNIQUE KEY `uq_nota` (`idMatricula`,`idEvaluacion`),
  KEY `idx_nota_matricula` (`idMatricula`),
  KEY `idx_nota_registradopor` (`registrado_por`),
  KEY `fk_nota_evaluacion` (`idEvaluacion`),
  CONSTRAINT `fk_nota_evaluacion` FOREIGN KEY (`idEvaluacion`) REFERENCES `evaluacion` (`idEvaluacion`),
  CONSTRAINT `fk_nota_matricula` FOREIGN KEY (`idMatricula`) REFERENCES `matricula` (`idMatricula`),
  CONSTRAINT `fk_nota_registrado` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('1','4','16','20','2026-06-22 16:31:25','','4',NULL,'2026-06-22 16:31:25');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('2','5','16','14','2026-06-22 16:31:25',NULL,'4','4','2026-06-22 16:36:58');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('3','2','16','11','2026-06-22 16:31:25','','4',NULL,'2026-06-22 16:31:25');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('4','3','16','9','2026-06-22 16:31:25','','4',NULL,'2026-06-22 16:31:25');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('5','14','16','20','2026-06-22 16:31:25','','4',NULL,'2026-06-22 16:31:25');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('6','1','16','0','2026-06-22 16:31:54','No entrego','4',NULL,'2026-06-22 16:31:54');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('7','4','19','12','2026-06-22 16:54:44','','4',NULL,'2026-06-22 16:54:44');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('8','5','19','12','2026-06-22 16:54:44','','4',NULL,'2026-06-22 16:54:44');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('9','2','19','5','2026-06-22 16:54:45',NULL,'4','4','2026-06-22 16:59:43');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('10','3','19','10','2026-06-22 16:54:45','','4',NULL,'2026-06-22 16:54:45');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('11','14','19','11','2026-06-22 16:54:45','','4',NULL,'2026-06-22 16:54:45');
INSERT INTO `nota` (`idNota`,`idMatricula`,`idEvaluacion`,`puntos_obtenidos`,`fecha_registro`,`observacion`,`registrado_por`,`modificado_por`,`modificado`) VALUES ('12','1','19','10','2026-06-22 16:54:45','','4',NULL,'2026-06-22 16:54:45');



DROP TABLE IF EXISTS `nota_final_materia`;
CREATE TABLE `nota_final_materia` (
  `idNotaFinal` int(11) NOT NULL AUTO_INCREMENT,
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
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idNotaFinal`),
  UNIQUE KEY `uq_nota_final` (`idMatricula`,`idAulaMateria`),
  KEY `idx_nf_matricula` (`idMatricula`),
  KEY `idx_nf_aulamateria` (`idAulaMateria`),
  KEY `idx_nf_estado` (`estado`),
  KEY `fk_nf_registrado` (`registrado_por`),
  CONSTRAINT `fk_nf_aulamateria` FOREIGN KEY (`idAulaMateria`) REFERENCES `aula_materia` (`idAulaMateria`),
  CONSTRAINT `fk_nf_matricula` FOREIGN KEY (`idMatricula`) REFERENCES `matricula` (`idMatricula`),
  CONSTRAINT `fk_nf_registrado` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Nota final anual y recuperatorios por alumno/materia';




DROP TABLE IF EXISTS `periodo`;
CREATE TABLE `periodo` (
  `idPeriodo` int(11) NOT NULL AUTO_INCREMENT,
  `idAnio` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL COMMENT '1° Etapa, 2° Etapa...',
  `numero` tinyint(1) NOT NULL COMMENT '1, 2, 3, 4',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` enum('Sí','No') NOT NULL,
  PRIMARY KEY (`idPeriodo`),
  UNIQUE KEY `uq_periodo` (`idAnio`,`numero`),
  KEY `idx_periodo_anio` (`idAnio`),
  CONSTRAINT `fk_per_anio` FOREIGN KEY (`idAnio`) REFERENCES `anio_lectivo` (`idAnio`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Bimestres del año lectivo';

INSERT INTO `periodo` (`idPeriodo`,`idAnio`,`nombre`,`numero`,`fecha_inicio`,`fecha_fin`,`activo`) VALUES ('1','1','1° Semestre','1','2026-02-16','2026-07-10','Sí');
INSERT INTO `periodo` (`idPeriodo`,`idAnio`,`nombre`,`numero`,`fecha_inicio`,`fecha_fin`,`activo`) VALUES ('2','1','2° Semestre','2','2026-04-27','2026-12-03','No');



DROP TABLE IF EXISTS `periodo_excepcion`;
CREATE TABLE `periodo_excepcion` (
  `idExcepcion` int(11) NOT NULL AUTO_INCREMENT,
  `idPeriodo` int(11) NOT NULL,
  `idAulaMateria` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `autorizado_por` int(11) NOT NULL COMMENT 'idUsuario del Director',
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idExcepcion`),
  UNIQUE KEY `uq_excepcion` (`idPeriodo`,`idAulaMateria`),
  KEY `idx_periodo` (`idPeriodo`),
  KEY `idx_aulamateria` (`idAulaMateria`),
  KEY `fk_excep_usuario` (`autorizado_por`),
  CONSTRAINT `fk_excep_aulamateria` FOREIGN KEY (`idAulaMateria`) REFERENCES `aula_materia` (`idAulaMateria`),
  CONSTRAINT `fk_excep_periodo` FOREIGN KEY (`idPeriodo`) REFERENCES `periodo` (`idPeriodo`),
  CONSTRAINT `fk_excep_usuario` FOREIGN KEY (`autorizado_por`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Materias reabiertas individualmente tras cierre global de periodo';




DROP TABLE IF EXISTS `registro_catedra`;
CREATE TABLE `registro_catedra` (
  `idRegCatedra` int(11) NOT NULL AUTO_INCREMENT,
  `idAsignacion` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `horaInicio` varchar(20) NOT NULL,
  `horaFin` varchar(20) NOT NULL,
  `unidad` varchar(150) NOT NULL,
  `tema` varchar(150) NOT NULL,
  `observaciones` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`idRegCatedra`),
  KEY `idAsignacion` (`idAsignacion`),
  CONSTRAINT `registro_catedra_ibfk_1` FOREIGN KEY (`idAsignacion`) REFERENCES `docente_aula_materia` (`idAsignacion`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `registro_catedra` (`idRegCatedra`,`idAsignacion`,`fecha`,`horaInicio`,`horaFin`,`unidad`,`tema`,`observaciones`) VALUES ('1','19','2026-06-29','15:30','18:00','Unidad 1- que fracasado es conta','una mid','trabajo al final');



DROP TABLE IF EXISTS `rol`;
CREATE TABLE `rol` (
  `idRol` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(50) NOT NULL COMMENT 'Director, Secretario, Evaluador, Docente, Padre',
  `descripcion` varchar(200) DEFAULT NULL,
  `activo` enum('Sí','No') NOT NULL,
  PRIMARY KEY (`idRol`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Roles del sistema';

INSERT INTO `rol` (`idRol`,`rol`,`descripcion`,`activo`) VALUES ('1','Director','Acceso total al sistema','Sí');
INSERT INTO `rol` (`idRol`,`rol`,`descripcion`,`activo`) VALUES ('2','Secretario','Gestión administrativa y de usuarios','Sí');
INSERT INTO `rol` (`idRol`,`rol`,`descripcion`,`activo`) VALUES ('3','Docente','Carga de notas, asistencia y planificación','Sí');
INSERT INTO `rol` (`idRol`,`rol`,`descripcion`,`activo`) VALUES ('4','Padre','Consulta de información de sus hijos','Sí');
INSERT INTO `rol` (`idRol`,`rol`,`descripcion`,`activo`) VALUES ('5','Evaluador','Se encarga de las asignaciones','Sí');



DROP TABLE IF EXISTS `tipo_nota`;
CREATE TABLE `tipo_nota` (
  `idTipoNota` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL COMMENT 'Ej: Trabajo Práctico, Evaluación Escrita, Proyecto',
  `porcentaje` decimal(5,2) NOT NULL DEFAULT 100.00 COMMENT 'Peso en el promedio del período (%)',
  `activo` enum('Sí','No') NOT NULL,
  `unico_por_periodo` enum('Sí','No') NOT NULL DEFAULT 'No' COMMENT 'Si = solo puede existir una evaluacion de este tipo por materia/periodo',
  PRIMARY KEY (`idTipoNota`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tipos de evaluación y su peso';

INSERT INTO `tipo_nota` (`idTipoNota`,`nombre`,`porcentaje`,`activo`,`unico_por_periodo`) VALUES ('2','Trabajo Práctico','25.00','Sí','No');
INSERT INTO `tipo_nota` (`idTipoNota`,`nombre`,`porcentaje`,`activo`,`unico_por_periodo`) VALUES ('3','Participación/Oral','25.00','Sí','No');
INSERT INTO `tipo_nota` (`idTipoNota`,`nombre`,`porcentaje`,`activo`,`unico_por_periodo`) VALUES ('4','Primera Parcial','0.00','Sí','Sí');
INSERT INTO `tipo_nota` (`idTipoNota`,`nombre`,`porcentaje`,`activo`,`unico_por_periodo`) VALUES ('5','Segunda Parcial','0.00','Sí','Sí');
INSERT INTO `tipo_nota` (`idTipoNota`,`nombre`,`porcentaje`,`activo`,`unico_por_periodo`) VALUES ('6','Examen Final','0.00','Sí','Sí');



DROP TABLE IF EXISTS `turno`;
CREATE TABLE `turno` (
  `idTurno` int(11) NOT NULL AUTO_INCREMENT,
  `turno` enum('M','T','MT','N') NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`idTurno`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Secciones (A, B, ...)';

INSERT INTO `turno` (`idTurno`,`turno`,`descripcion`) VALUES ('1','M','Turno Mañana');
INSERT INTO `turno` (`idTurno`,`turno`,`descripcion`) VALUES ('2','T','Turno Tarde');
INSERT INTO `turno` (`idTurno`,`turno`,`descripcion`) VALUES ('3','MT','Turno Mañana y Tarde');



DROP TABLE IF EXISTS `tutor`;
CREATE TABLE `tutor` (
  `idTutor` int(11) NOT NULL AUTO_INCREMENT,
  `cedula` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `parentesco` varchar(50) NOT NULL COMMENT 'Padre, Madre, Abuelo, Tutor legal...',
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(150) NOT NULL COMMENT 'Obligatorio: se usará para crear su cuenta de acceso',
  `direccion` varchar(250) DEFAULT NULL,
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `idUsuario` int(11) DEFAULT NULL COMMENT 'Cuenta creada en segundo paso tras registrar el tutor',
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`idTutor`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `correo` (`correo`),
  KEY `idx_tutor_usuario` (`idUsuario`),
  KEY `idx_tutor_estado` (`estado`),
  KEY `idx_tutor_apellidos` (`apellidos`),
  CONSTRAINT `fk_tut_usuario` FOREIGN KEY (`idUsuario`) REFERENCES `usuarios` (`idUsuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Tutores / Padres de familia';

INSERT INTO `tutor` (`idTutor`,`cedula`,`nombres`,`apellidos`,`parentesco`,`telefono`,`correo`,`direccion`,`estado`,`idUsuario`,`creado`,`modificado`) VALUES ('1','6158747','Charlie','Rodriguez Fernandez','Padre','0971985659','charli@gmail.com',NULL,'Activo',NULL,'2026-06-07 10:27:36','2026-06-07 10:27:36');
INSERT INTO `tutor` (`idTutor`,`cedula`,`nombres`,`apellidos`,`parentesco`,`telefono`,`correo`,`direccion`,`estado`,`idUsuario`,`creado`,`modificado`) VALUES ('2','6999158','Graciela María','Casco Martínez','Madre','0986577123','gracimar@gmail.com',NULL,'Activo',NULL,'2026-06-07 10:50:54','2026-06-07 10:50:54');
INSERT INTO `tutor` (`idTutor`,`cedula`,`nombres`,`apellidos`,`parentesco`,`telefono`,`correo`,`direccion`,`estado`,`idUsuario`,`creado`,`modificado`) VALUES ('3','6878458','Arsenio Enrique','Gonzales Petro','Padre','0974895687','petro@gmail.com',NULL,'Activo',NULL,'2026-06-07 18:06:03','2026-06-07 18:06:03');
INSERT INTO `tutor` (`idTutor`,`cedula`,`nombres`,`apellidos`,`parentesco`,`telefono`,`correo`,`direccion`,`estado`,`idUsuario`,`creado`,`modificado`) VALUES ('5','4500123','Antonio Amadeo','Ortiz Ramirez','Tío','0981 123 456','','Avda. Eusebio Ayala 654','Activo',NULL,'2026-06-25 14:26:17','2026-06-25 14:26:17');



DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `idUsuario` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(150) NOT NULL,
  `password` varchar(250) NOT NULL COMMENT 'Contraseña hasheada (bcrypt)',
  `correo` varchar(150) NOT NULL COMMENT 'Obligatorio: usado para recuperación de contraseña',
  `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo',
  `token` varchar(100) DEFAULT NULL COMMENT 'Token para recuperación de contraseña',
  `token_expira` datetime DEFAULT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `idRol` int(11) NOT NULL,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `uq_usuario_nombre` (`usuario`),
  UNIQUE KEY `uq_usuario_correo` (`correo`),
  KEY `idx_usuarios_rol` (`idRol`),
  KEY `idx_usuarios_estado` (`estado`),
  KEY `idx_usuarios_token` (`token`),
  CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`idRol`) REFERENCES `rol` (`idRol`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cuentas de acceso al sistema';

INSERT INTO `usuarios` (`idUsuario`,`usuario`,`password`,`correo`,`estado`,`token`,`token_expira`,`creado`,`modificado`,`idRol`) VALUES ('1','admin_director','$2b$12$placeholder_hash_director','director@santateresita.edu.py','Activo',NULL,NULL,'2026-04-12 15:28:38','2026-04-12 15:28:38','1');
INSERT INTO `usuarios` (`idUsuario`,`usuario`,`password`,`correo`,`estado`,`token`,`token_expira`,`creado`,`modificado`,`idRol`) VALUES ('2','secretaria01','$2b$12$placeholder_hash_secre','secretaria@santateresita.edu.py','Activo',NULL,NULL,'2026-04-12 15:28:38','2026-04-12 15:28:38','2');
INSERT INTO `usuarios` (`idUsuario`,`usuario`,`password`,`correo`,`estado`,`token`,`token_expira`,`creado`,`modificado`,`idRol`) VALUES ('3','Sergio137','admin123','aquinod285@gmail.com','Activo',NULL,NULL,'2026-04-14 10:08:16','2026-04-14 10:08:16','1');
INSERT INTO `usuarios` (`idUsuario`,`usuario`,`password`,`correo`,`estado`,`token`,`token_expira`,`creado`,`modificado`,`idRol`) VALUES ('4','prof_Alvaro','123','alvaroortegadominguez11@gmail.com','Activo',NULL,NULL,'2026-05-14 10:19:55','2026-06-29 15:59:35','3');
INSERT INTO `usuarios` (`idUsuario`,`usuario`,`password`,`correo`,`estado`,`token`,`token_expira`,`creado`,`modificado`,`idRol`) VALUES ('7','WilsonRene','wilson123','wilsonrene@gmail.com','Activo',NULL,NULL,'2026-06-27 17:56:18','2026-06-27 17:56:18','5');



DROP TABLE IF EXISTS `v_asistencia_resumen`;
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


