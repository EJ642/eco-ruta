-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-08-2026 a las 14:39:49
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
-- Base de datos: `ecoruta_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comercios`
--

CREATE TABLE `comercios` (
  `id_comercio` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `razon_social` varchar(150) NOT NULL,
  `ruc` varchar(20) NOT NULL,
  `direccion_fiscal` varchar(200) DEFAULT NULL,
  `rubro` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `comercios`:
--   `id_usuario`
--       `usuarios` -> `id_usuario`
--

--
-- Volcado de datos para la tabla `comercios`
--

INSERT INTO `comercios` (`id_comercio`, `id_usuario`, `razon_social`, `ruc`, `direccion_fiscal`, `rubro`) VALUES
(1, 1, 'Panadería La Espiga S.R.L.', '80012345-6', 'Av. Mariscal López 1234, Asunción', 'Panadería');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `confirmaciones_entrega`
--

CREATE TABLE `confirmaciones_entrega` (
  `id_confirmacion` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `tipo_confirmacion` enum('firma_digital','codigo_qr') NOT NULL,
  `evidencia` varchar(255) NOT NULL COMMENT 'Ruta de la imagen de firma o valor del código QR',
  `nota_observacion` text DEFAULT NULL,
  `fecha_confirmacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `confirmaciones_entrega`:
--   `id_pedido`
--       `pedidos` -> `id_pedido`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `id_direccion` int(11) NOT NULL,
  `calle` varchar(150) NOT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `ciudad` varchar(80) NOT NULL,
  `referencia` varchar(200) DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `direcciones`:
--

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`id_direccion`, `calle`, `numero`, `ciudad`, `referencia`, `latitud`, `longitud`) VALUES
(1, 'Av. Mariscal López', '1234', 'Asunción', 'Frente a la plaza', -25.2900000, -57.5800000),
(2, 'Calle Palma', '567', 'Asunción', 'Al lado del banco', -25.2820000, -57.6350000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_estados_pedido`
--

CREATE TABLE `historial_estados_pedido` (
  `id_historial` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `estado_anterior` enum('pendiente','en_camino','entregado','cancelado') DEFAULT NULL,
  `estado_nuevo` enum('pendiente','en_camino','entregado','cancelado') NOT NULL,
  `id_usuario_responsable` int(11) DEFAULT NULL,
  `fecha_cambio` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `historial_estados_pedido`:
--   `id_pedido`
--       `pedidos` -> `id_pedido`
--   `id_usuario_responsable`
--       `usuarios` -> `id_usuario`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_comercio` int(11) NOT NULL,
  `id_direccion_origen` int(11) NOT NULL,
  `id_direccion_destino` int(11) NOT NULL,
  `descripcion_paquete` varchar(255) NOT NULL,
  `peso_kg` decimal(6,2) DEFAULT NULL,
  `id_tarifa` int(11) DEFAULT NULL,
  `distancia_km` decimal(6,2) DEFAULT NULL,
  `tarifa_calculada` decimal(10,2) DEFAULT NULL,
  `co2_estimado_ahorrado_kg` decimal(6,3) DEFAULT NULL,
  `id_repartidor` int(11) DEFAULT NULL,
  `id_turno` int(11) DEFAULT NULL,
  `estado` enum('pendiente','en_camino','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `pedidos`:
--   `id_comercio`
--       `comercios` -> `id_comercio`
--   `id_direccion_destino`
--       `direcciones` -> `id_direccion`
--   `id_direccion_origen`
--       `direcciones` -> `id_direccion`
--   `id_repartidor`
--       `repartidores` -> `id_repartidor`
--   `id_tarifa`
--       `tarifas_ecologicas` -> `id_tarifa`
--   `id_turno`
--       `turnos` -> `id_turno`
--

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_comercio`, `id_direccion_origen`, `id_direccion_destino`, `descripcion_paquete`, `peso_kg`, `id_tarifa`, `distancia_km`, `tarifa_calculada`, `co2_estimado_ahorrado_kg`, `id_repartidor`, `id_turno`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 1, 2, 'Caja de pasteles - 20 unidades', 3.50, 1, 4.20, 8000.00, 0.756, 1, 1, 'pendiente', '2026-08-27 13:35:24', '2026-08-27 13:35:24');

--
-- Disparadores `pedidos`
--
DELIMITER $$
CREATE TRIGGER `trg_pedidos_after_update` AFTER UPDATE ON `pedidos` FOR EACH ROW BEGIN
    IF OLD.estado <> NEW.estado THEN
        INSERT INTO historial_estados_pedido (id_pedido, estado_anterior, estado_nuevo)
        VALUES (NEW.id_pedido, OLD.estado, NEW.estado);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repartidores`
--

CREATE TABLE `repartidores` (
  `id_repartidor` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_vehiculo` enum('bicicleta','vehiculo_electrico') NOT NULL,
  `placa_identificacion` varchar(20) DEFAULT NULL,
  `disponible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `repartidores`:
--   `id_usuario`
--       `usuarios` -> `id_usuario`
--

--
-- Volcado de datos para la tabla `repartidores`
--

INSERT INTO `repartidores` (`id_repartidor`, `id_usuario`, `tipo_vehiculo`, `placa_identificacion`, `disponible`) VALUES
(1, 2, 'bicicleta', 'BICI-045', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `roles`:
--

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(3, 'administrador'),
(1, 'comerciante'),
(2, 'repartidor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas_ecologicas`
--

CREATE TABLE `tarifas_ecologicas` (
  `id_tarifa` int(11) NOT NULL,
  `tipo_vehiculo` enum('bicicleta','vehiculo_electrico') NOT NULL,
  `distancia_min_km` decimal(6,2) NOT NULL,
  `distancia_max_km` decimal(6,2) NOT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `precio_por_km` decimal(10,2) NOT NULL,
  `factor_co2_kg_por_km` decimal(6,3) NOT NULL COMMENT 'CO2 evitado por km frente a un vehículo a combustión'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `tarifas_ecologicas`:
--

--
-- Volcado de datos para la tabla `tarifas_ecologicas`
--

INSERT INTO `tarifas_ecologicas` (`id_tarifa`, `tipo_vehiculo`, `distancia_min_km`, `distancia_max_km`, `precio_base`, `precio_por_km`, `factor_co2_kg_por_km`) VALUES
(1, 'bicicleta', 0.00, 5.00, 8000.00, 1000.00, 0.180),
(2, 'bicicleta', 5.01, 12.00, 12000.00, 1200.00, 0.180),
(3, 'vehiculo_electrico', 0.00, 8.00, 10000.00, 900.00, 0.120),
(4, 'vehiculo_electrico', 8.01, 20.00, 15000.00, 1100.00, 0.120);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id_turno` int(11) NOT NULL,
  `id_repartidor` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `turnos`:
--   `id_repartidor`
--       `repartidores` -> `id_repartidor`
--

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id_turno`, `id_repartidor`, `fecha`, `hora_inicio`, `hora_fin`) VALUES
(1, 1, '2026-08-27', '08:00:00', '14:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `nombre_completo` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- RELACIONES PARA LA TABLA `usuarios`:
--   `id_rol`
--       `roles` -> `id_rol`
--

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre_completo`, `email`, `password_hash`, `telefono`, `fecha_registro`, `activo`) VALUES
(1, 1, 'Panadería La Espiga', 'contacto@laespiga.com.py', '$2b$12$N2o1IxgGZfBfUd.09KFVr.oTJnPGM3iv1meTpnCl2m8cRAnUM6/Jy', '0981123456', '2026-08-27 13:35:24', 1),
(2, 2, 'Carlos Benítez', 'carlos.benitez@ecoruta.com.py', '$2b$12$NXOx5xUsRwBHWsjYngYKku/at4ay7z98hsPWR94.zhKN2VOrtVDTy', '0981654321', '2026-08-27 13:35:24', 1),
(3, 3, 'Alvaro Ortega', 'alvaroortegadominguez11@gmail.com', '$2b$12$DXpyoMqQP8OrMOwLy.BBJudC3bwSeLrk4IFSdTL7/nu.0Ryb9eEQy', '0981000000', '2026-08-27 13:35:24', 1);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vw_metricas_diarias`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vw_metricas_diarias` (
`fecha` date
,`total_entregas` bigint(21)
,`co2_ahorrado_total_kg` decimal(28,2)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `vw_metricas_diarias`
--
DROP TABLE IF EXISTS `vw_metricas_diarias`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_metricas_diarias`  AS SELECT cast(`ce`.`fecha_confirmacion` as date) AS `fecha`, count(0) AS `total_entregas`, round(sum(`p`.`co2_estimado_ahorrado_kg`),2) AS `co2_ahorrado_total_kg` FROM (`confirmaciones_entrega` `ce` join `pedidos` `p` on(`p`.`id_pedido` = `ce`.`id_pedido`)) GROUP BY cast(`ce`.`fecha_confirmacion` as date) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `comercios`
--
ALTER TABLE `comercios`
  ADD PRIMARY KEY (`id_comercio`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD UNIQUE KEY `ruc` (`ruc`);

--
-- Indices de la tabla `confirmaciones_entrega`
--
ALTER TABLE `confirmaciones_entrega`
  ADD PRIMARY KEY (`id_confirmacion`),
  ADD UNIQUE KEY `id_pedido` (`id_pedido`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`id_direccion`);

--
-- Indices de la tabla `historial_estados_pedido`
--
ALTER TABLE `historial_estados_pedido`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_historial_pedido` (`id_pedido`),
  ADD KEY `fk_historial_usuario` (`id_usuario_responsable`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `fk_pedidos_comercio` (`id_comercio`),
  ADD KEY `fk_pedidos_origen` (`id_direccion_origen`),
  ADD KEY `fk_pedidos_destino` (`id_direccion_destino`),
  ADD KEY `fk_pedidos_tarifa` (`id_tarifa`),
  ADD KEY `fk_pedidos_repartidor` (`id_repartidor`),
  ADD KEY `fk_pedidos_turno` (`id_turno`),
  ADD KEY `idx_pedidos_estado` (`estado`);

--
-- Indices de la tabla `repartidores`
--
ALTER TABLE `repartidores`
  ADD PRIMARY KEY (`id_repartidor`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `tarifas_ecologicas`
--
ALTER TABLE `tarifas_ecologicas`
  ADD PRIMARY KEY (`id_tarifa`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id_turno`),
  ADD KEY `fk_turnos_repartidor` (`id_repartidor`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_usuarios_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `comercios`
--
ALTER TABLE `comercios`
  MODIFY `id_comercio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `confirmaciones_entrega`
--
ALTER TABLE `confirmaciones_entrega`
  MODIFY `id_confirmacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `historial_estados_pedido`
--
ALTER TABLE `historial_estados_pedido`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `repartidores`
--
ALTER TABLE `repartidores`
  MODIFY `id_repartidor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tarifas_ecologicas`
--
ALTER TABLE `tarifas_ecologicas`
  MODIFY `id_tarifa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id_turno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `comercios`
--
ALTER TABLE `comercios`
  ADD CONSTRAINT `fk_comercios_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `confirmaciones_entrega`
--
ALTER TABLE `confirmaciones_entrega`
  ADD CONSTRAINT `fk_confirmacion_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historial_estados_pedido`
--
ALTER TABLE `historial_estados_pedido`
  ADD CONSTRAINT `fk_historial_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`id_usuario_responsable`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_comercio` FOREIGN KEY (`id_comercio`) REFERENCES `comercios` (`id_comercio`),
  ADD CONSTRAINT `fk_pedidos_destino` FOREIGN KEY (`id_direccion_destino`) REFERENCES `direcciones` (`id_direccion`),
  ADD CONSTRAINT `fk_pedidos_origen` FOREIGN KEY (`id_direccion_origen`) REFERENCES `direcciones` (`id_direccion`),
  ADD CONSTRAINT `fk_pedidos_repartidor` FOREIGN KEY (`id_repartidor`) REFERENCES `repartidores` (`id_repartidor`),
  ADD CONSTRAINT `fk_pedidos_tarifa` FOREIGN KEY (`id_tarifa`) REFERENCES `tarifas_ecologicas` (`id_tarifa`),
  ADD CONSTRAINT `fk_pedidos_turno` FOREIGN KEY (`id_turno`) REFERENCES `turnos` (`id_turno`);

--
-- Filtros para la tabla `repartidores`
--
ALTER TABLE `repartidores`
  ADD CONSTRAINT `fk_repartidores_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD CONSTRAINT `fk_turnos_repartidor` FOREIGN KEY (`id_repartidor`) REFERENCES `repartidores` (`id_repartidor`) ON DELETE CASCADE;

ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recuperacion_claves`
--

CREATE TABLE IF NOT EXISTS `recuperacion_claves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiracion` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


