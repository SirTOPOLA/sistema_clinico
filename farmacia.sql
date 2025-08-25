-- Crear la base de datos
CREATE DATABASE farmacia_db;
USE farmacia_db;

-- ========================
-- TABLAS DE CATÁLOGOS
-- ========================

-- Unidades de medida
CREATE TABLE unidades_medida (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,      -- Ej: Mililitro, Tableta
    abreviatura VARCHAR(10) NOT NULL  -- Ej: ml, tab
);

-- Categorías de productos
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,      -- Ej: Antibiótico, Analgésico
    descripcion TEXT
);

-- ========================
-- TABLA DE PRODUCTOS
-- ========================
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,          -- Ej: Paracetamol
    concentracion VARCHAR(50),             -- Ej: 500 mg
    forma_farmaceutica VARCHAR(50),        -- Ej: Tableta, Jarabe
    presentacion VARCHAR(100),             -- Ej: Caja con 20 tabletas
    categoria_id INT,
    unidad_id INT,
    precio_unitario DECIMAL(10,2) NOT NULL,
    stock_actual INT DEFAULT 0,
    stock_minimo INT DEFAULT 0,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id),
    FOREIGN KEY (unidad_id) REFERENCES unidades_medida(id)
);

-- ========================
-- PROVEEDORES Y COMPRAS
-- ========================
CREATE TABLE proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(150),
    telefono VARCHAR(30),
    contacto VARCHAR(100)
);

-- Cabecera de compras
CREATE TABLE compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proveedor_id INT,
    fecha DATE NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
);

-- Detalle de compras
CREATE TABLE compras_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compra_id INT,
    producto_id INT,
    cantidad INT NOT NULL,
    precio_compra DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (compra_id) REFERENCES compras(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- ========================
-- pacienteS Y VENTAS
-- ========================
--CREATE TABLE pacientes (
--    id INT AUTO_INCREMENT PRIMARY KEY,
--    nombre VARCHAR(100) NOT NULL,
--    direccion VARCHAR(150),
--    telefono VARCHAR(30)
--);

-- Cabecera de ventas
CREATE TABLE ventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    fecha DATE NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id)
);

-- Detalle de ventas
CREATE TABLE ventas_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    venta_id INT,
    producto_id INT,
    cantidad INT NOT NULL,
    precio_venta DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (venta_id) REFERENCES ventas(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- ========================
-- MOVIMIENTOS DE INVENTARIO
-- ========================
CREATE TABLE movimientos_inventario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT,
    tipo ENUM('ENTRADA','SALIDA') NOT NULL,
    referencia VARCHAR(50),    -- ID compra, venta, regalo o préstamo
    cantidad INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- ========================
-- FINANZAS
-- ========================
CREATE TABLE movimientos_financieros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('INGRESO','EGRESO') NOT NULL,
    concepto VARCHAR(100) NOT NULL,      -- Ej: Venta, Compra, Préstamo, Regalo
    referencia VARCHAR(50),              -- ID de venta, compra, préstamo
    monto DECIMAL(12,2) NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    metodo_pago ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') DEFAULT 'EFECTIVO'
);

-- ========================
-- REGALOS
-- ========================
CREATE TABLE regalos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT,
    cantidad INT NOT NULL,
    motivo VARCHAR(150),              -- Ej: Promoción, Muestra médica
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    --entregado_a VARCHAR(100),         -- Persona o paciente
    paciente_id INT,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id)
);

-- ========================
-- PRÉSTAMOS
-- ========================
CREATE TABLE prestamos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT,
    fecha DATE NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    estado ENUM('PENDIENTE','PAGADO') DEFAULT 'PENDIENTE',
    observacion VARCHAR(150),              -- Ej: detalles 
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id)
);

CREATE TABLE prestamos_detalle (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prestamo_id INT,
    producto_id INT,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (prestamo_id) REFERENCES prestamos(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);
