


CREATE TABLE prepagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titular_id INT NOT NULL,                 -- cliente que hace el prepago
    monto_inicial DECIMAL(12,2) NOT NULL,   -- cantidad total depositada
    saldo_actual DECIMAL(12,2) NOT NULL,    -- saldo disponible
    fecha_deposito DATE NOT NULL,
    metodo_pago ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') DEFAULT 'EFECTIVO',
    FOREIGN KEY (titular_id) REFERENCES clientes(id)
);


CREATE TABLE prepagos_beneficiarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prepago_id INT NOT NULL,
    cliente_id INT NOT NULL,                  -- beneficiario autorizado
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prepago_id) REFERENCES prepagos(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

CREATE TABLE movimientos_prepago (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prepago_id INT NOT NULL,
    cliente_id INT NOT NULL,                  -- cliente que consume el saldo
    venta_id INT NULL,                        -- si se utilizó en una venta
    tipo ENUM('CREDITO','DEBITO') NOT NULL,  -- CREDITO = recarga, DEBITO = consumo
    monto DECIMAL(12,2) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    descripcion VARCHAR(150),
    FOREIGN KEY (prepago_id) REFERENCES prepagos(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (venta_id) REFERENCES ventas(id)
);
