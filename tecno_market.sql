-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-11-2026 a las 06:05:38
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
-- Base de datos: `tecno_market`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `correo`, `telefono`) VALUES
(1, 'Carlos Mendoza', 'carlos@gmail.com', '0991234567'),
(2, 'Maria Lopez', 'maria@gmail.com', '0987654321'),
(3, 'Andres Torres', 'andres@gmail.com', '0971122334'),
(4, 'Sofia Ramirez', 'sofia@gmail.com', '0934573573'),
(5, 'Daniel Castro', 'daniel@gmail.com', '09136907609'),
(6, 'Valeria Flores', 'valeria@gmail.com', '0925743361'),
(7, 'Jose Herrera', 'jose@gmail.com', '0936842432'),
(8, 'Camila Sanchez', 'camila@gmail.com', '0987524211'),
(9, 'Miguel Vargas', 'miguel@gmail.com', '0989775649'),
(10, 'Andrea Morales', 'andrea@gmail.com', '0901208590');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `cantidad`, `subtotal`) VALUES
(1, 1, 1, 1, 650.00),
(2, 1, 3, 2, 37.00),
(3, 2, 5, 1, 180.00),
(4, 2, 6, 2, 64.00),
(5, 3, 4, 1, 45.00),
(6, 3, 8, 3, 36.00),
(7, 4, 7, 1, 220.00),
(8, 4, 10, 1, 55.00),
(9, 5, 11, 1, 280.00),
(10, 5, 12, 2, 80.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `estado` enum('pendiente','procesando','enviado','entregado','cancelado') DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_cliente`, `fecha`, `estado`) VALUES
(1, 1, '2026-11-21 23:58:31', 'pendiente'),
(2, 2, '2026-11-21 23:58:31', 'procesando'),
(3, 3, '2026-11-21 23:58:31', 'enviado'),
(4, 4, '2026-11-21 23:58:31', 'entregado'),
(5, 5, '2026-11-21 23:58:31', 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0
) ;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `precio`, `stock`) VALUES
(1, 'Laptop Lenovo IdeaPad', 'Laptop Intel Core i5, 8GB RAM, 512GB SSD', 650.00, 12),
(2, 'Laptop HP Pavilion', 'Laptop Intel Core i7, 16GB RAM, 1TB SSD', 950.00, 8),
(3, 'Mouse Logitech', 'Mouse inalámbrico USB', 18.50, 30),
(4, 'Teclado Gamer RGB', 'Teclado mecánico con iluminación RGB', 45.00, 20),
(5, 'Monitor Samsung 24', 'Monitor LED Full HD de 24 pulgadas', 180.00, 10),
(6, 'Audifonos Bluetooth', 'Audífonos inalámbricos con micrófono', 32.00, 25),
(7, 'Impresora Epson', 'Impresora multifunción de tinta continua', 220.00, 6),
(8, 'Memoria USB Kingston', 'Memoria USB de 64 GB', 12.00, 40),
(9, 'Disco SSD Kingston', 'Unidad de almacenamiento SSD de 1 TB', 85.00, 15),
(10, 'Webcam Logitech', 'Cámara web Full HD', 55.00, 12),
(11, 'Tablet Samsung', 'Tablet de 10 pulgadas, 128 GB', 280.00, 7),
(12, 'Parlante Bluetooth', 'Parlante portátil inalámbrico', 40.00, 18),
(13, 'Router TP-Link', 'Router inalámbrico de doble banda', 65.00, 14),
(14, 'Silla Gamer', 'Silla ergonómica para videojuegos', 190.00, 5),
(15, 'Cable HDMI', 'Cable HDMI de alta definición', 8.00, 50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` enum('administrador','cliente') NOT NULL,
  `id_cliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id_producto`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`),
  ADD UNIQUE KEY `id_cliente` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`),
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
