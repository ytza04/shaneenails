-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-06-2026 a las 23:52:50
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
-- Base de datos: `shaneenails`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `citas`
--

CREATE TABLE `citas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `turno_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `modalidad` enum('presencial','domicilio') NOT NULL DEFAULT 'presencial',
  `dirección_domicilio` varchar(255) DEFAULT NULL,
  `imagen_referencia` varchar(255) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `estado` enum('pendiente','confirmada','cotizada','cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `citas`
--

INSERT INTO `citas` (`id`, `usuario_id`, `servicio_id`, `turno_id`, `fecha`, `modalidad`, `dirección_domicilio`, `imagen_referencia`, `notas`, `estado`, `fecha_creacion`) VALUES
(1, 3, 1, 3, '2026-05-15', 'presencial', '', NULL, 'Deseo realizarme este diseño de uñas.', 'confirmada', '2026-05-03 19:09:37'),
(2, 3, 1, 2, '2026-05-31', 'domicilio', 'Los Teques. Carretera Panamericana, Sector Corralito', 'ref_6a134d6bda43f.jpg', 'Quiero hacerme este lindo diseño.', 'pendiente', '2026-05-24 15:11:51'),
(3, 6, 3, 1, '2026-05-26', 'presencial', '', 'ref_6a13516365c40.jpg', 'Hola! Quiero este diseño.', 'pendiente', '2026-05-24 15:28:44'),
(4, 6, 3, 3, '2026-05-28', 'domicilio', 'Los Teques. Carretera Panamericana, Sector Corralito', 'ref_6a136f7f8cb23.jpg', 'Quiero hacerme este diseño de uñas', 'pendiente', '2026-05-24 17:37:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizaciones`
--

CREATE TABLE `cotizaciones` (
  `id` int(11) NOT NULL,
  `cita_id` int(11) NOT NULL,
  `precio_final` decimal(10,2) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `estado` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha_envio` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cotizaciones`
--

INSERT INTO `cotizaciones` (`id`, `cita_id`, `precio_final`, `mensaje`, `estado`, `fecha_envio`) VALUES
(1, 1, 15.00, 'Hola, gracias por contactarte con nosotros.', 'aceptada', '2026-05-10 18:57:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `disponibilidad`
--

CREATE TABLE `disponibilidad` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `turno_id` int(11) NOT NULL,
  `disponible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `disponibilidad`
--

INSERT INTO `disponibilidad` (`id`, `fecha`, `turno_id`, `disponible`) VALUES
(1, '2026-05-10', 1, 1),
(2, '2026-05-10', 2, 1),
(3, '2026-05-15', 1, 1),
(4, '2026-05-15', 3, 1),
(5, '2026-05-20', 2, 1),
(6, '2026-05-28', 1, 1),
(7, '2026-05-28', 2, 1),
(8, '2026-05-28', 3, 1),
(9, '2026-05-26', 1, 1),
(10, '2026-05-26', 2, 1),
(11, '2026-05-26', 3, 1),
(12, '2026-05-31', 1, 1),
(13, '2026-05-31', 2, 1),
(14, '2026-05-31', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `etiquetas`
--

INSERT INTO `etiquetas` (`id`, `nombre`) VALUES
(2, 'Francés'),
(5, 'Glitter'),
(4, 'Minimalista'),
(1, 'Nail Art'),
(3, 'Temporada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `galeria`
--

CREATE TABLE `galeria` (
  `id` int(11) NOT NULL,
  `imagen_url` varchar(255) NOT NULL,
  `servicio_id` int(11) DEFAULT NULL,
  `descripción` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `galeria`
--

INSERT INTO `galeria` (`id`, `imagen_url`, `servicio_id`, `descripción`, `fecha_subida`) VALUES
(2, 'gal_6a13789cd5baa.jpg', 1, 'Diseño artístico', '2026-05-24 18:15:57'),
(3, 'gal_6a1378ca478c1.jpg', 3, 'Vida y luz', '2026-05-24 18:16:42'),
(4, 'gal_6a1378fee51e6.jpg', 1, 'Noche oscura', '2026-05-24 18:17:34'),
(5, 'gal_6a13791c0ad61.jpg', 2, 'Vida alterna', '2026-05-24 18:18:04'),
(6, 'gal_6a13859846869.jpg', 2, 'Noche de fiests', '2026-05-24 19:11:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `galeria_etiquetas`
--

CREATE TABLE `galeria_etiquetas` (
  `galeria_id` int(11) NOT NULL,
  `etiqueta_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensaje` varchar(255) NOT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id`, `usuario_id`, `mensaje`, `leida`, `fecha`) VALUES
(1, 1, 'Nueva cita agendada por Ana Maria para el 2026-05-15', 0, '2026-05-03 19:09:37'),
(2, 3, 'Shaneel te ha enviado una cotización. Revísala en tu portal.', 0, '2026-05-10 18:57:59'),
(3, 1, 'Nueva cita agendada por Ana Maria para el 2026-05-31', 0, '2026-05-24 15:11:52'),
(4, 5, 'Nueva cita agendada por Ana Maria para el 2026-05-31', 0, '2026-05-24 15:11:52'),
(6, 1, 'Nueva cita agendada por Ytzali para el 2026-05-26', 0, '2026-05-24 15:28:44'),
(7, 5, 'Nueva cita agendada por Ytzali para el 2026-05-26', 0, '2026-05-24 15:28:44'),
(9, 1, 'Nueva cita agendada por Ytzali para el 2026-05-28', 0, '2026-05-24 17:37:29'),
(10, 5, 'Nueva cita agendada por Ytzali para el 2026-05-28', 0, '2026-05-24 17:37:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reseñas`
--

CREATE TABLE `reseñas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cita_id` int(11) DEFAULT NULL,
  `puntuación` tinyint(4) NOT NULL CHECK (`puntuación` between 1 and 5),
  `comentario` text DEFAULT NULL,
  `aprobada` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripción` text DEFAULT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `descripción`, `precio_base`, `imagen`, `activo`) VALUES
(1, 'Semipermanente', 'Esmalte de larga duración, hasta 3 semanas sin astillarse.', 15.00, 'semipermanente.jpg', 1),
(2, 'Gel', 'Uñas de gel para mayor resistencia y durabilidad.', 20.00, 'gel.jpg', 1),
(3, 'Jelly Tips', 'Extensiones con efecto transparente gelatinoso, muy trendy.', 25.00, 'jelly.jpg', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id` int(11) NOT NULL,
  `hora` time NOT NULL,
  `etiqueta` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id`, `hora`, `etiqueta`, `activo`) VALUES
(1, '10:00:00', 'Mañana', 1),
(2, '13:00:00', 'Mediodía', 1),
(3, '16:00:00', 'Tarde', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `teléfono` varchar(20) DEFAULT NULL,
  `rol` enum('admin','cliente') NOT NULL DEFAULT 'cliente',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `contraseña`, `teléfono`, `rol`, `activo`, `fecha_registro`) VALUES
(1, 'Shaneel', 'admin@shaneenails.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', 1, '2026-04-26 19:15:23'),
(3, 'Ana Maria', 'am@gmail.com', '$2y$10$UCYjEOt.DMD04sgjdtbqguCf.neuXpG5Ix9VdH/tTTa7oFt2PFhC6', '03492939203', 'cliente', 1, '2026-05-03 18:48:59'),
(5, 'Gersiris', 'ge@gmail.com', '$2y$10$MtMnqDntH4ItqLP8qCt1neekxcXb11aT1nBlER4jrMpGgj7bwxcTK', '03492939203', 'admin', 1, '2026-05-24 15:03:41'),
(6, 'Ytzali', 'rodriguezytzali@gmail.com', '$2y$10$iNWW3mIOKvr.KbnarQ2vF.jOfzQUxCDmBqwj1eAOi/Ppge1v3CEge', '0212-7409840', 'cliente', 1, '2026-05-24 15:27:43');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `citas`
--
ALTER TABLE `citas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `servicio_id` (`servicio_id`),
  ADD KEY `turno_id` (`turno_id`);

--
-- Indices de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cita_id` (`cita_id`);

--
-- Indices de la tabla `disponibilidad`
--
ALTER TABLE `disponibilidad`
  ADD PRIMARY KEY (`id`),
  ADD KEY `turno_id` (`turno_id`);

--
-- Indices de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `galeria`
--
ALTER TABLE `galeria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servicio_id` (`servicio_id`);

--
-- Indices de la tabla `galeria_etiquetas`
--
ALTER TABLE `galeria_etiquetas`
  ADD PRIMARY KEY (`galeria_id`,`etiqueta_id`),
  ADD KEY `etiqueta_id` (`etiqueta_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `reseñas`
--
ALTER TABLE `reseñas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `cita_id` (`cita_id`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `citas`
--
ALTER TABLE `citas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `disponibilidad`
--
ALTER TABLE `disponibilidad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `galeria`
--
ALTER TABLE `galeria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `reseñas`
--
ALTER TABLE `reseñas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `citas`
--
ALTER TABLE `citas`
  ADD CONSTRAINT `citas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `citas_ibfk_2` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`),
  ADD CONSTRAINT `citas_ibfk_3` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`);

--
-- Filtros para la tabla `cotizaciones`
--
ALTER TABLE `cotizaciones`
  ADD CONSTRAINT `cotizaciones_ibfk_1` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`);

--
-- Filtros para la tabla `disponibilidad`
--
ALTER TABLE `disponibilidad`
  ADD CONSTRAINT `disponibilidad_ibfk_1` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id`);

--
-- Filtros para la tabla `galeria`
--
ALTER TABLE `galeria`
  ADD CONSTRAINT `galeria_ibfk_1` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`);

--
-- Filtros para la tabla `galeria_etiquetas`
--
ALTER TABLE `galeria_etiquetas`
  ADD CONSTRAINT `galeria_etiquetas_ibfk_1` FOREIGN KEY (`galeria_id`) REFERENCES `galeria` (`id`),
  ADD CONSTRAINT `galeria_etiquetas_ibfk_2` FOREIGN KEY (`etiqueta_id`) REFERENCES `etiquetas` (`id`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `reseñas`
--
ALTER TABLE `reseñas`
  ADD CONSTRAINT `reseñas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `reseñas_ibfk_2` FOREIGN KEY (`cita_id`) REFERENCES `citas` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
