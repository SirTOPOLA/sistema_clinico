-- Crear la base de datos
CREATE DATABASE farmacia_db;

USE farmacia_db;

-- ========================
-- TABLAS DE CATÁLOGOS
-- ========================
-- Unidades de medida
CREATE TABLE
    unidades_medida (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL, -- Ej: Mililitro, Tableta
        abreviatura VARCHAR(10) NOT NULL -- Ej: ml, tab
    );

-- Categorías de productos
CREATE TABLE
    categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL, -- Ej: Antibiótico, Analgésico
        descripcion TEXT
    );

-- ========================
-- TABLA DE PRODUCTOS
-- ========================
CREATE TABLE
    productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL, -- Ej: Paracetamol
        concentracion VARCHAR(50), -- Ej: 500 mg
        forma_farmaceutica VARCHAR(50), -- Ej: Tableta, Jarabe
        presentacion VARCHAR(100), -- Ej: Caja con 20 tabletas
        categoria_id INT,
        unidad_id INT,
        precio_unitario DECIMAL(10, 2) NOT NULL,
        stock_actual INT DEFAULT 0,
        stock_minimo INT DEFAULT 0,
        FOREIGN KEY (categoria_id) REFERENCES categorias (id),
        FOREIGN KEY (unidad_id) REFERENCES unidades_medida (id)
    );

-- ========================
-- PROVEEDORES Y COMPRAS
-- ========================
CREATE TABLE
    proveedores (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        direccion VARCHAR(150),
        telefono VARCHAR(30),
        contacto VARCHAR(100)
    );

 
-- Cabecera de compras
CREATE TABLE
    compras (
        id INT AUTO_INCREMENT PRIMARY KEY,
        proveedor_id INT,
        personal_id INT,
        fecha DATE NOT NULL,
        monto_entregado DECIMAL(12, 2) DEFAULT 0, -- dinero que llevó el empleado
        monto_gastado DECIMAL(12, 2) DEFAULT 0, -- lo que realmente costó la compra
        cambio_devuelto DECIMAL(12, 2) DEFAULT 0, -- si sobró dinero
        monto_pendiente DECIMAL(12, 2) DEFAULT 0, -- si quedó a deber
        total DECIMAL(12, 2) NOT NULL,
        estado_pago ENUM ('PAGADO', 'PENDIENTE', 'PARCIAL') DEFAULT 'PENDIENTE',
        FOREIGN KEY (personal_id) REFERENCES personal (id),
        FOREIGN KEY (proveedor_id) REFERENCES proveedores (id)
    );

-- Detalle de compras
CREATE TABLE
    compras_detalle (
        id INT AUTO_INCREMENT PRIMARY KEY,
        compra_id INT,
        producto_id INT,
        cantidad INT NOT NULL,
        precio_compra DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (compra_id) REFERENCES compras (id),
        FOREIGN KEY (producto_id) REFERENCES productos (id)
    );

CREATE TABLE pagos_proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compra_id INT,
    proveedor_id INT,
    monto DECIMAL(12,2) NOT NULL,
    fecha DATE NOT NULL,
    metodo_pago ENUM('EFECTIVO','TRANSFERENCIA','TARJETA','OTRO') DEFAULT 'EFECTIVO',
    FOREIGN KEY (compra_id) REFERENCES compras(id),
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id)
);

-- ========================
-- pacienteS Y VENTAS
-- ========================
--CREATE TABLE pacientes (
--   Paciente ya esta en el modulo de la clinica
--);

ALTER TABLE ventas
ADD empleado_id INT AFTER cliente_id,         -- Quién realizó la venta

ALTER TABLE ventas
ADD FOREIGN KEY (empleado_id) REFERENCES empleados(id);


-- Cabecera de ventas
CREATE TABLE
    ventas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        paciente_id INT,
        usuario_id INT, -- quien atendio 
        fecha DATE NOT NULL, 
        monto_total DECIMAL(12,2) NOT NULL,       -- Precio total calculado
        monto_recibido DECIMAL(12,2) DEFAULT 0,   -- Dinero entregado por el cliente
        cambio_devuelto DECIMAL(12,2) DEFAULT 0,  -- Vuelto al cliente
        estado_pago ENUM('PAGADO','PENDIENTE','PARCIAL') DEFAULT 'PAGADO',
        metodo_pago ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') DEFAULT 'EFECTIVO',
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id),
        FOREIGN KEY (paciente_id) REFERENCES pacientes (id)
    );

-- Detalle de ventas
CREATE TABLE
    ventas_detalle (
        id INT AUTO_INCREMENT PRIMARY KEY,
        venta_id INT,
        producto_id INT,
        cantidad INT NOT NULL,
        precio_venta DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (venta_id) REFERENCES ventas (id),
        FOREIGN KEY (producto_id) REFERENCES productos (id)
    );

-- ========================
-- MOVIMIENTOS DE INVENTARIO
-- ========================
CREATE TABLE
    movimientos_inventario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        producto_id INT,
        tipo ENUM ('ENTRADA', 'SALIDA') NOT NULL,
        referencia VARCHAR(50), -- ID compra, venta, regalo o préstamo
        cantidad INT NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (producto_id) REFERENCES productos (id)
    );

-- ========================
-- FINANZAS
-- ========================
CREATE TABLE
    movimientos_financieros (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo ENUM ('INGRESO', 'EGRESO') NOT NULL,
        concepto VARCHAR(100) NOT NULL, -- Ej: Venta, Compra, Préstamo, Regalo
        referencia_id INT, -- ID de venta, compra, préstamo
        monto DECIMAL(12, 2) NOT NULL,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        metodo_pago ENUM ('EFECTIVO', 'TARJETA', 'TRANSFERENCIA', 'OTRO') DEFAULT 'EFECTIVO'
    );

-- ========================
-- REGALOS
-- ========================
CREATE TABLE
    regalos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        producto_id INT,
        cantidad INT NOT NULL,
        motivo VARCHAR(150), -- Ej: Promoción, Muestra médica
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        paciente_id INT, --entregado_a  Persona o paciente
        FOREIGN KEY (producto_id) REFERENCES productos (id),
        FOREIGN KEY (paciente_id) REFERENCES pacientes (id)
    );

