-- Base de datos para Sistema POS Restaurante
CREATE DATABASE IF NOT EXISTS pos_restaurant CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pos_restaurant;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('mesero', 'cocina', 'gerente') NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB;

-- Tabla de categorías
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Tabla de productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_usd DECIMAL(10,2) NOT NULL,
    precio_bs DECIMAL(15,2) NOT NULL,
    categoria_id INT,
    imagen VARCHAR(255),
    disponible TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_categoria (categoria_id),
    INDEX idx_disponible (disponible)
) ENGINE=InnoDB;

-- Tabla de clientes
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_telefono (telefono)
) ENGINE=InnoDB;

-- Tabla de órdenes
CREATE TABLE ordenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    mesero_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Pendiente', 'En Preparación', 'Lista', 'Entregada', 'Pagada') DEFAULT 'Pendiente',
    total_usd DECIMAL(10,2) NOT NULL,
    total_bs DECIMAL(15,2) NOT NULL,
    moneda_principal ENUM('USD', 'BS') DEFAULT 'USD',
    notas TEXT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (mesero_id) REFERENCES usuarios(id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha),
    INDEX idx_mesero (mesero_id)
) ENGINE=InnoDB;

-- Tabla de detalles de orden
CREATE TABLE orden_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario_usd DECIMAL(10,2) NOT NULL,
    precio_unitario_bs DECIMAL(15,2) NOT NULL,
    subtotal_usd DECIMAL(10,2) NOT NULL,
    subtotal_bs DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (orden_id) REFERENCES ordenes(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    INDEX idx_orden (orden_id)
) ENGINE=InnoDB;

-- Tabla de pagos
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT NOT NULL,
    metodo_pago ENUM('Efectivo BS', 'Efectivo USD', 'Pago Móvil', 'Tarjeta') NOT NULL,
    monto DECIMAL(15,2) NOT NULL,
    moneda ENUM('USD', 'BS') NOT NULL,
    referencia VARCHAR(100),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orden_id) REFERENCES ordenes(id) ON DELETE CASCADE,
    INDEX idx_orden (orden_id),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB;

-- Tabla de configuración
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    descripcion TEXT,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de auditoría
CREATE TABLE auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    tabla VARCHAR(50) NOT NULL,
    registro_id INT,
    datos_anteriores TEXT,
    datos_nuevos TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_fecha (fecha),
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB;

-- Insertar configuración inicial
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('tasa_cambio', '36.50', 'Tasa de cambio USD a BS'),
('nombre_restaurante', 'Fast Food Restaurant', 'Nombre del restaurante'),
('telefono_restaurante', '0424-1234567', 'Teléfono del restaurante'),
('direccion_restaurante', 'Av. Principal, Local 123', 'Dirección del restaurante');

-- Insertar usuario gerente por defecto (password: admin123)
INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES
('Administrador', 'admin@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'gerente'),
('Juan Pérez', 'mesero@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mesero'),
('María García', 'cocina@restaurant.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cocina');

-- Insertar categorías de ejemplo
INSERT INTO categorias (nombre, descripcion) VALUES
('Hamburguesas', 'Hamburguesas y combos'),
('Bebidas', 'Refrescos y jugos'),
('Postres', 'Helados y postres'),
('Extras', 'Papas fritas y adicionales');

-- Insertar productos de ejemplo
INSERT INTO productos (nombre, descripcion, precio_usd, precio_bs, categoria_id) VALUES
('Hamburguesa Clásica', 'Hamburguesa de carne con queso, lechuga y tomate', 3.50, 127.75, 1),
('Hamburguesa Especial', 'Doble carne, queso, tocino y vegetales', 5.00, 182.50, 1),
('Papas Fritas', 'Porción mediana de papas fritas', 1.50, 54.75, 4),
('Refresco Grande', 'Bebida gaseosa de 500ml', 1.00, 36.50, 2),
('Helado', 'Helado de vainilla o chocolate', 2.00, 73.00, 3);

-- Insertar cliente de ejemplo
INSERT INTO clientes (nombre, telefono, direccion) VALUES
('Cliente General', '0000-0000000', 'Sin dirección'),
('Carlos Rodríguez', '0414-5551234', 'Calle Principal #45'),
('Ana Martínez', '0424-7778899', 'Av. Libertador #123');