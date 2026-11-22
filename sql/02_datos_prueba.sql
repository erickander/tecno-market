-- ============================================================
-- TECNO-MARKET | Datos de prueba
-- Importar DESPUES de 01_esquema.sql
-- ============================================================

USE `tecno_market`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `detalle_pedido`;
TRUNCATE TABLE `pedidos`;
TRUNCATE TABLE `usuarios`;
TRUNCATE TABLE `productos`;
TRUNCATE TABLE `clientes`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Clientes
-- ------------------------------------------------------------
INSERT INTO `clientes` (`id_cliente`, `nombre`, `correo`, `telefono`, `direccion`) VALUES
(1,  'Carlos Mendoza',  'carlos@gmail.com',  '0991234567', 'Av. Amazonas 123, Quito'),
(2,  'Maria Lopez',     'maria@gmail.com',   '0987654321', 'Calle Bolivar 456, Guayaquil'),
(3,  'Andres Torres',   'andres@gmail.com',  '0971122334', 'Av. 6 de Diciembre 789, Quito'),
(4,  'Sofia Ramirez',   'sofia@gmail.com',   '0934573573', 'Calle Sucre 321, Cuenca'),
(5,  'Daniel Castro',   'daniel@gmail.com',  '0913690760', 'Av. Naciones Unidas 654, Quito'),
(6,  'Valeria Flores',  'valeria@gmail.com', '0925743361', 'Calle 9 de Octubre 987, Guayaquil'),
(7,  'Jose Herrera',    'jose@gmail.com',    '0936842432', 'Av. America 147, Ambato'),
(8,  'Camila Sanchez',  'camila@gmail.com',  '0987524211', 'Calle Colon 258, Manta'),
(9,  'Miguel Vargas',   'miguel@gmail.com',  '0989775649', 'Av. Amazonas 369, Quito'),
(10, 'Andrea Morales',  'andrea@gmail.com',  '0901208590', 'Calle Garcia Moreno 741, Loja');

-- ------------------------------------------------------------
-- Productos (imagen = archivo en public/img/productos/)
-- ------------------------------------------------------------
INSERT INTO `productos` (`id_producto`, `nombre`, `descripcion`, `precio`, `stock`, `imagen`) VALUES
(1,  'Laptop Lenovo IdeaPad',   'Laptop Intel Core i5, 8 GB RAM, 512 GB SSD, pantalla 15.6"', 650.00, 12, 'laptop-lenovo.svg'),
(2,  'Laptop HP Pavilion',      'Laptop Intel Core i7, 16 GB RAM, 1 TB SSD, pantalla 14"',      950.00,  8, 'laptop-hp.svg'),
(3,  'Mouse Logitech',          'Mouse inalambrico USB, sensor optico de 1000 dpi',               18.50, 30, 'mouse-logitech.svg'),
(4,  'Teclado Gamer RGB',       'Teclado mecanico con iluminacion RGB y reposamunecas',            45.00, 20, 'teclado-gamer.svg'),
(5,  'Monitor Samsung 24"',     'Monitor LED Full HD de 24 pulgadas, 75 Hz, HDMI',                180.00, 10, 'monitor-samsung.svg'),
(6,  'Audifonos Bluetooth',     'Audifonos inalambricos con microfono y estuche de carga',         32.00, 25, 'audifonos-bluetooth.svg'),
(7,  'Impresora Epson',         'Impresora multifuncion de tinta continua, WiFi',                 220.00,  6, 'impresora-epson.svg'),
(8,  'Memoria USB Kingston',    'Memoria USB de 64 GB, USB 3.0',                                   12.00, 40, 'usb-kingston.svg'),
(9,  'Disco SSD Kingston',      'Unidad SSD de 1 TB, SATA III, 550 MB/s',                          85.00, 15, 'ssd-kingston.svg'),
(10, 'Webcam Logitech',         'Camara web Full HD 1080p con microfono integrado',                55.00, 12, 'webcam-logitech.svg'),
(11, 'Tablet Samsung',          'Tablet de 10 pulgadas, 128 GB, WiFi',                             280.00,  7, 'tablet-samsung.svg'),
(12, 'Parlante Bluetooth',      'Parlante portatil inalambrico resistente al agua',                40.00, 18, 'parlante-bluetooth.svg'),
(13, 'Router TP-Link',          'Router inalambrico de doble banda, 4 antenas',                    65.00, 14, 'router-tplink.svg'),
(14, 'Silla Gamer',             'Silla ergonomica reclinable para videojuegos',                   190.00,  5, 'silla-gamer.svg'),
(15, 'Cable HDMI',              'Cable HDMI 2.0 de 2 metros, alta definicion',                      8.00, 50, 'cable-hdmi.svg');

-- ------------------------------------------------------------
-- Usuarios (contrasenas con bcrypt)
-- admin   / Admin123*
-- cliente / Cliente123*
-- ------------------------------------------------------------
INSERT INTO `usuarios` (`id_usuario`, `nombre_usuario`, `contrasena`, `rol`, `id_cliente`) VALUES
(1, 'admin',   '$2y$10$6IyvMFHnQbn7eXsW2KeHDe/koe2Q6TjlTgNZPUa09J82ZeGMj0IM2', 'administrador', NULL),
(2, 'cliente', '$2y$10$ioVZrzq/CzAn6o5ru2ds6ux6f9249TKZ0XyeUBzNbOlY1pxvjUKMe', 'cliente', 1);

-- ------------------------------------------------------------
-- Pedidos (el total lo calculan los triggers)
-- ------------------------------------------------------------
INSERT INTO `pedidos` (`id_pedido`, `id_cliente`, `fecha`, `estado`) VALUES
(1, 1, '2026-11-21 23:58:31', 'pendiente'),
(2, 2, '2026-11-21 23:58:31', 'procesando'),
(3, 3, '2026-11-21 23:58:31', 'enviado'),
(4, 4, '2026-11-21 23:58:31', 'entregado'),
(5, 5, '2026-11-21 23:58:31', 'pendiente');

-- ------------------------------------------------------------
-- Detalle de pedidos (precio historico de cada venta)
-- ------------------------------------------------------------
INSERT INTO `detalle_pedido` (`id_pedido`, `id_producto`, `cantidad`, `precio_unitario`) VALUES
(1, 1,  1, 650.00),
(1, 3,  2,  18.50),
(2, 5,  1, 180.00),
(2, 6,  2,  32.00),
(3, 4,  1,  45.00),
(3, 8,  3,  12.00),
(4, 7,  1, 220.00),
(4, 10, 1,  55.00),
(5, 11, 1, 280.00),
(5, 12, 2,  40.00);
