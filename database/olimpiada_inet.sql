-- phpMyAdmin SQL Dump
-- version 5.2.1

CREATE DATABASE IF NOT EXISTS `olimpiada_inet`;
USE `olimpiada_inet`;
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-06-2025 a las 00:38:25
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
-- Base de datos: `olimpiada_inet`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id`, `usuario_id`, `producto_id`, `cantidad`, `precio_unitario`, `fecha_agregado`) VALUES
(6, 2, 1, 1, 15000.00, '2025-06-12 06:42:11'),
(8, 1, 3, 1, 25000.00, '2025-06-12 22:37:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `icono`, `activo`, `created_at`) VALUES
(1, 'Hoteles', 'Alojamientos y hospedajes', '🏨', 1, '2025-06-12 04:55:02'),
(2, 'Paquetes Turísticos', 'Paquetes completos de viaje', '🎒', 1, '2025-06-12 04:55:02'),
(3, 'Transporte', 'Servicios de transporte turístico', '🚌', 1, '2025-06-12 04:55:02'),
(4, 'Actividades', 'Actividades y excursiones', '🏃‍♂️', 1, '2025-06-12 04:55:02'),
(5, 'Gastronomía', 'Experiencias gastronómicas', '🍽️', 1, '2025-06-12 04:55:02'),
(6, 'Aventura', 'Turismo de aventura y deportes', '🧗‍♂️', 1, '2025-06-12 04:55:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_reservas`
--

CREATE TABLE `detalle_reservas` (
  `id` int(11) NOT NULL,
  `reserva_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `nombre_producto` varchar(200) NOT NULL,
  `descripcion_producto` text DEFAULT NULL,
  `destino_producto` varchar(100) DEFAULT NULL,
  `duracion_dias` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_reserva`
--

CREATE TABLE `estados_reserva` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estados_reserva`
--

INSERT INTO `estados_reserva` (`id`, `nombre`, `descripcion`, `color`) VALUES
(1, 'Pendiente', 'Reserva creada, esperando confirmación', '#FFA500'),
(2, 'Confirmada', 'Reserva confirmada y pagada', '#4CAF50'),
(3, 'En Proceso', 'Reserva en proceso de preparación', '#2196F3'),
(4, 'Completada', 'Servicio completado', '#9C27B0'),
(5, 'Cancelada', 'Reserva cancelada', '#f44336');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `icono` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id`, `nombre`, `descripcion`, `activo`, `icono`) VALUES
(1, 'Transferencia Bancaria', 'Pago por transferencia bancaria', 1, '🏦'),
(2, 'Tarjeta de Crédito', 'Pago con tarjeta de crédito', 1, '💳'),
(3, 'Tarjeta de Débito', 'Pago con tarjeta de débito', 1, '💳'),
(4, 'Efectivo', 'Pago en efectivo al momento del servicio', 1, '💵'),
(5, 'MercadoPago', 'Pago a través de MercadoPago', 1, '💰');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `reserva_id` int(11) NOT NULL,
  `metodo_pago_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_pago` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado_pago` enum('Pendiente','Completado','Fallido','Reembolsado') DEFAULT 'Pendiente',
  `referencia_pago` varchar(100) DEFAULT NULL,
  `comprobante_pago` varchar(255) DEFAULT NULL,
  `observaciones_pago` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text NOT NULL,
  `imagen_principal` varchar(255) DEFAULT NULL,

  `precio` decimal(10,2) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `tipo_paquete_id` int(11) DEFAULT NULL,
  `destino` varchar(100) NOT NULL,
  `duracion_dias` int(11) DEFAULT NULL,

  `servicios_incluidos` text DEFAULT NULL,
  `servicios_no_incluidos` text DEFAULT NULL,

  `disponible` tinyint(1) DEFAULT 1,
  `destacado` tinyint(1) DEFAULT 0,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `imagen_principal`, `precio`, `categoria_id`, `tipo_paquete_id`, `destino`, `duracion_dias`, `servicios_incluidos`, `servicios_no_incluidos`, `disponible`, `destacado`, `fecha_inicio`, `fecha_fin`, `created_at`, `updated_at`) VALUES
(1, 'Hotel Boutique Villa Carlos Paz', 'Exclusivo hotel boutique en el corazón de Villa Carlos Paz con vista al lago San Roque. Habitaciones de lujo completamente equipadas, spa, piscina climatizada y restaurante gourmet.', NULL, 15000.00, 1, 2, 'Villa Carlos Paz', 2, 'Desayuno buffet, WiFi, Spa, Piscina, Estacionamiento', 'Almuerzo, Cena, Excursiones', 1, 1, NULL, NULL, '2025-06-12 04:55:02', '2025-06-12 04:55:02'),
(2, 'Cabañas Familiares Sierras', 'Cómodas cabañas familiares ubicadas en las sierras cordobesas. Ideales para descansar en familia, con amplios espacios verdes, parrilla y todas las comodidades.', NULL, 8500.00, 1, 3, 'Villa General Belgrano', 3, 'Ropa de cama, Parrilla, WiFi, Estacionamiento, Limpieza final', 'Comidas, Leña, Actividades', 1, 0, NULL, NULL, '2025-06-12 04:55:02', '2025-06-12 04:55:02'),
(3, 'Córdoba Clásica - 4 Días', 'Recorrido completo por los principales atractivos de Córdoba capital y alrededores. Incluye city tour, visita a la Manzana Jesuítica, Capilla del Monte y La Cumbrecita.', NULL, 25000.00, 2, 3, 'Córdoba Capital', 4, 'Transporte, Guía, Alojamiento 3 estrellas, Desayunos, Entradas', 'Almuerzo, Cena, Gastos personales', 1, 1, NULL, NULL, '2025-06-12 04:55:02', '2025-06-12 04:55:02'),
(4, 'Aventura en Calamuchita', 'Paquete de aventura en el Valle de Calamuchita con actividades de trekking, tirolesa, rappel y navegación. Para los más aventureros.', NULL, 18500.00, 2, 4, 'Valle de Calamuchita', 3, 'Transporte, Guía especializado, Equipamiento, Seguro, Alojamiento', 'Comidas, Bebidas, Equipo personal', 1, 0, NULL, NULL, '2025-06-12 04:55:02', '2025-06-12 04:55:02'),
(5, 'Traslado Aeropuerto - Villa Carlos Paz', 'Servicio de traslado cómodo y seguro desde el aeropuerto de Córdoba hasta Villa Carlos Paz. Vehículos modernos con aire acondicionado.', NULL, 3500.00, 3, 1, 'Villa Carlos Paz', 1, 'Conductor profesional, Seguro, WiFi en vehículo', 'Peajes, Propinas', 1, 0, NULL, NULL, '2025-06-12 04:55:02', '2025-06-12 04:55:02'),
(6, 'Bus Turístico Sierras de Córdoba', 'Recorrido en bus turístico por las principales sierras cordobesas con paradas en miradores y pueblos pintorescos. Incluye guía bilingüe.', NULL, 4200.00, 3, 4, 'Sierras de Córdoba', 1, 'Transporte, Guía bilingüe, Seguro, Paradas programadas', 'Comidas, Entradas a museos, Souvenirs', 1, 0, NULL, NULL, '2025-06-12 04:55:02', '2025-06-12 04:55:02'),
(7, 'Trekking Cerro Uritorco', 'Ascensión guiada al místico Cerro Uritorco en Capilla del Monte. Una experiencia única con vistas panorámicas y energías especiales.', NULL, 2800.00, 4, 2, 'Capilla del Monte', 1, 'Guía especializado, Seguro, Kit de primeros auxilios', 'Transporte, Comidas, Equipo de trekking', 1, 1, NULL, NULL, '2025-06-12 04:55:02', '2025-06-12 04:55:02'),
(8, 'Parapente en La Cumbre', 'Vuelo en parapente biplaza sobre los hermosos paisajes de La Cumbre. Una experiencia inolvidable para sentir la libertad del vuelo.', NULL, 8500.00, 4, 1, 'La Cumbre', 1, 'Instructor certificado, Equipo completo, Seguro, Video del vuelo', 'Transporte, Comidas', 1, 0, NULL, NULL, '2025-06-12 04:55:02', '2025-06-12 04:55:02'),
(9, 'Tour Gastronómico Alemán', 'Recorrido gastronómico por Villa General Belgrano degustando las mejores cervezas artesanales, chocolates y comida alemana tradicional.', NULL, 5500.00, 5, 2, 'Villa General Belgrano', 1, 'Degustaciones, Guía gastronómico, Transporte local', 'Comida completa, Bebidas adicionales, Souvenirs', 1, 0, NULL, NULL, '2025-06-12 04:55:02', '2025-06-12 04:55:02'),
(10, 'Rafting en Río Mina Clavero', 'Descenso en rafting por las aguas cristalinas del río Mina Clavero. Aventura familiar apta para principiantes con guías experimentados.', NULL, 4500.00, 6, 3, 'Mina Clavero', 1, 'Equipo completo, Guías certificados, Seguro, Transporte al río', 'Comidas, Cambio de ropa, Fotografías', 1, 1, NULL, NULL, '2025-06-12 04:55:02', '2025-06-12 04:55:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `numero_reserva` varchar(20) NOT NULL,
  `fecha_reserva` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado_id` int(11) DEFAULT 1,
  `nombre_cliente` varchar(100) NOT NULL,
  `apellido_cliente` varchar(100) NOT NULL,
  `email_cliente` varchar(100) NOT NULL,
  `telefono_cliente` varchar(20) DEFAULT NULL,
  `documento_cliente` varchar(20) DEFAULT NULL,
  `direccion_facturacion` text DEFAULT NULL,
  `ciudad_facturacion` varchar(100) DEFAULT NULL,
  `provincia_facturacion` varchar(100) DEFAULT NULL,
  `codigo_postal_facturacion` varchar(10) DEFAULT NULL,
  `numero_personas` int(11) DEFAULT 1,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `created_at`) VALUES
(1, 'user', 'Usuario normal', '2025-06-12 04:49:55'),
(2, 'ventas', 'Usuario de ventas', '2025-06-12 04:49:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_paquete`
--

CREATE TABLE `tipos_paquete` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipos_paquete`
--

INSERT INTO `tipos_paquete` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Individual', 'Paquete para una persona'),
(2, 'Pareja', 'Paquete para dos personas'),
(3, 'Familiar', 'Paquete para familias con niños'),
(4, 'Grupo', 'Paquete para grupos grandes'),
(5, 'Corporativo', 'Paquete para empresas y eventos corporativos'),
(6, 'Luna de Miel', 'Paquete especial para parejas recién casadas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol_id` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `password`, `rol_id`, `created_at`) VALUES
(1, 'Thomas', 'fricker', 'hola@gmail.com', '$2y$10$5wnmgxZgVGdiqCsJgGCq3ug9Ogt/4CWpK85.XqObAHFlI9LDxyMn6', 1, '2025-06-12 05:15:55'),
(2, 'tata', 'TATA2', 'hola3@gmail.com', '$2y$10$ZyURccalxRyJH77fngJq0OCTdo64nyMnEajHBAu/hUibG/mBKI4i2', 1, '2025-06-12 05:46:46');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_producto` (`usuario_id`,`producto_id`),
  ADD KEY `idx_carrito_usuario` (`usuario_id`),
  ADD KEY `idx_carrito_producto` (`producto_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_reservas`
--
ALTER TABLE `detalle_reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `idx_detalle_reserva` (`reserva_id`);

--
-- Indices de la tabla `estados_reserva`
--
ALTER TABLE `estados_reserva`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `metodo_pago_id` (`metodo_pago_id`),
  ADD KEY `idx_pagos_reserva` (`reserva_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `tipo_paquete_id` (`tipo_paquete_id`),
  ADD KEY `disponible` (`disponible`),
  ADD KEY `destacado` (`destacado`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_reserva` (`numero_reserva`),
  ADD KEY `estado_id` (`estado_id`),
  ADD KEY `idx_reservas_usuario` (`usuario_id`),
  ADD KEY `idx_reservas_fecha` (`fecha_reserva`),
  ADD KEY `idx_reservas_numero` (`numero_reserva`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipos_paquete`
--
ALTER TABLE `tipos_paquete`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `rol_id` (`rol_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `detalle_reservas`
--
ALTER TABLE `detalle_reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados_reserva`
--
ALTER TABLE `estados_reserva`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipos_paquete`
--
ALTER TABLE `tipos_paquete`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `carrito_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carrito_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_reservas`
--
ALTER TABLE `detalle_reservas`
  ADD CONSTRAINT `detalle_reservas_ibfk_1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_reservas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`metodo_pago_id`) REFERENCES `metodos_pago` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`tipo_paquete_id`) REFERENCES `tipos_paquete` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`estado_id`) REFERENCES `estados_reserva` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
