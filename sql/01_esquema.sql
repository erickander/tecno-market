-- ============================================================
-- TECNO-MARKET | Sistemas Distribuidos - Unidad 4
-- Esquema de base de datos v2
-- Motor: MariaDB 10.4 / MySQL 8
-- ============================================================

CREATE DATABASE IF NOT EXISTS `tecno_market`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE `tecno_market`;

-- ------------------------------------------------------------
-- Limpieza (permite reimportar sin errores)
-- ------------------------------------------------------------
DROP TRIGGER IF EXISTS `trg_detalle_ai`;
DROP TRIGGER IF EXISTS `trg_detalle_au`;
DROP TRIGGER IF EXISTS `trg_detalle_ad`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `detalle_pedido`;
DROP TABLE IF EXISTS `pedidos`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `productos`;
DROP TABLE IF EXISTS `clientes`;
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Tabla: clientes
-- ------------------------------------------------------------
CREATE TABLE `clientes` (
    `id_cliente` INT(11) NOT NULL AUTO_INCREMENT,
    `nombre`     VARCHAR(100) NOT NULL,
    `correo`     VARCHAR(100) NOT NULL,
    `telefono`   VARCHAR(15)  NOT NULL,
    `direccion`  VARCHAR(150) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_cliente`),
    UNIQUE KEY `uk_clientes_correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Tabla: productos
-- ------------------------------------------------------------
CREATE TABLE `productos` (
    `id_producto` INT(11) NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(100) NOT NULL,
    `descripcion` TEXT DEFAULT NULL,
    `precio`      DECIMAL(10,2) NOT NULL,
    `stock`       INT(11) NOT NULL DEFAULT 0,
    `imagen`      VARCHAR(512) DEFAULT NULL,
    `activo`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_producto`),
    CONSTRAINT `chk_productos_precio` CHECK (`precio` >= 0),
    CONSTRAINT `chk_productos_stock`  CHECK (`stock` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Tabla: usuarios
-- Contrasenas almacenadas con password_hash() (bcrypt)
-- ------------------------------------------------------------
CREATE TABLE `usuarios` (
    `id_usuario`     INT(11) NOT NULL AUTO_INCREMENT,
    `nombre_usuario` VARCHAR(50)  NOT NULL,
    `contrasena`     VARCHAR(255) NOT NULL,
    `rol`            ENUM('administrador','cliente') NOT NULL,
    `id_cliente`     INT(11) DEFAULT NULL,
    `activo`         TINYINT(1) NOT NULL DEFAULT 1,
    `ultimo_acceso`  DATETIME DEFAULT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario`),
    UNIQUE KEY `uk_usuarios_nombre` (`nombre_usuario`),
    UNIQUE KEY `uk_usuarios_cliente` (`id_cliente`),
    CONSTRAINT `fk_usuarios_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Tabla: pedidos
-- ------------------------------------------------------------
CREATE TABLE `pedidos` (
    `id_pedido`  INT(11) NOT NULL AUTO_INCREMENT,
    `id_cliente` INT(11) NOT NULL,
    `fecha`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `estado`     ENUM('pendiente','procesando','enviado','entregado','cancelado')
                 NOT NULL DEFAULT 'pendiente',
    `total`      DECIMAL(12,2) NOT NULL DEFAULT 0,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_pedido`),
    KEY `idx_pedidos_cliente` (`id_cliente`),
    KEY `idx_pedidos_estado` (`estado`),
    CONSTRAINT `fk_pedidos_cliente`
        FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Tabla: detalle_pedido
-- precio_unitario conserva el precio historico de la venta
-- subtotal es columna generada (cantidad * precio_unitario)
-- ------------------------------------------------------------
CREATE TABLE `detalle_pedido` (
    `id_detalle`      INT(11) NOT NULL AUTO_INCREMENT,
    `id_pedido`       INT(11) NOT NULL,
    `id_producto`     INT(11) NOT NULL,
    `cantidad`        INT(11) NOT NULL,
    `precio_unitario` DECIMAL(10,2) NOT NULL,
    `subtotal`        DECIMAL(12,2) AS (`cantidad` * `precio_unitario`) STORED,
    PRIMARY KEY (`id_detalle`),
    KEY `idx_detalle_pedido` (`id_pedido`),
    KEY `idx_detalle_producto` (`id_producto`),
    CONSTRAINT `fk_detalle_pedido`
        FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_detalle_producto`
        FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `chk_detalle_cantidad` CHECK (`cantidad` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- Triggers: mantienen pedidos.total sincronizado
-- ------------------------------------------------------------
DELIMITER $$

CREATE TRIGGER `trg_detalle_ai` AFTER INSERT ON `detalle_pedido`
FOR EACH ROW
BEGIN
    UPDATE `pedidos` p
       SET p.`total` = (SELECT COALESCE(SUM(d.`subtotal`), 0)
                          FROM `detalle_pedido` d
                         WHERE d.`id_pedido` = NEW.`id_pedido`)
     WHERE p.`id_pedido` = NEW.`id_pedido`;
END$$

CREATE TRIGGER `trg_detalle_au` AFTER UPDATE ON `detalle_pedido`
FOR EACH ROW
BEGIN
    UPDATE `pedidos` p
       SET p.`total` = (SELECT COALESCE(SUM(d.`subtotal`), 0)
                          FROM `detalle_pedido` d
                         WHERE d.`id_pedido` = NEW.`id_pedido`)
     WHERE p.`id_pedido` = NEW.`id_pedido`;
END$$

CREATE TRIGGER `trg_detalle_ad` AFTER DELETE ON `detalle_pedido`
FOR EACH ROW
BEGIN
    UPDATE `pedidos` p
       SET p.`total` = (SELECT COALESCE(SUM(d.`subtotal`), 0)
                          FROM `detalle_pedido` d
                         WHERE d.`id_pedido` = OLD.`id_pedido`)
     WHERE p.`id_pedido` = OLD.`id_pedido`;
END$$

DELIMITER ;
