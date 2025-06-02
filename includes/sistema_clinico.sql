-- TABLA: Roles del sistema
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- TABLA: Empleados de la clínica
CREATE TABLE empleados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    correo VARCHAR(150) UNIQUE,
    fecha_ingreso DATE NOT NULL,
    rol_id INT NOT NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- TABLA: Usuarios del sistema
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    empleado_id INT NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    estado tinyint(1) DEFAULT 1,
    ultima_sesion DATETIME,
    FOREIGN KEY (empleado_id) REFERENCES empleados(id)
);

-- TABLA: Pacientes
CREATE TABLE pacientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    telefono VARCHAR(25) not NULL,
   edad TINYINT UNSIGNED NOT NULL,
    residencia VARCHAR(100) NOT NULL,
    profesion VARCHAR(150),
    ocupacion VARCHAR(100),
    telefono_emergencia VARCHAR(25),
    telefono_auxi VARCHAR(25),
    usuario_id INT,
    fecha_registro DATE DEFAULT CURRENT_DATE,
     FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);



CREATE TABLE triajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    enfermera_id INT NOT NULL,
    motivo TEXT,
    temperatura DECIMAL(4,2),
    frecuencia_cardiaca DECIMAL(5,2),
    frecuencia_respiratoria DECIMAL(5,2),
    tension_arterial VARCHAR(10),
    presion_arterial VARCHAR(20),
    pulso TINYINT UNSIGNED,
    saturacion_oxigeno TINYINT UNSIGNED,
    peso_anterior DECIMAL(5,2),
    peso_actual DECIMAL(5,2),
    peso_ideal DECIMAL(5,2),
    imc DECIMAL(5,2), -- índice de masa corporal
    orina BOOLEAN,
    defeca BOOLEAN,
    defeca_dias TINYINT UNSIGNED,
    operacion BOOLEAN,
    descanso_estado ENUM('bien','mal'),
    horas_descanso DECIMAL(4,2),
    observaciones TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    control_c VARCHAR(10) NOT NULL,

    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (enfermera_id) REFERENCES empleados(id)
);


CREATE TABLE patologias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('personal','familiar','conyuge') NOT NULL,
    descripcion TEXT
);
CREATE TABLE triaje_patologias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    triaje_id INT NOT NULL,
    patologia_id INT NOT NULL,
    FOREIGN KEY (triaje_id) REFERENCES triajes(id) ON DELETE CASCADE,
    FOREIGN KEY (patologia_id) REFERENCES patologias(id) ON DELETE CASCADE
);
CREATE TABLE alergias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);
CREATE TABLE paciente_alergias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    alergia_id INT NOT NULL,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id) ON DELETE CASCADE,
    FOREIGN KEY (alergia_id) REFERENCES alergias(id) ON DELETE CASCADE
);

-- TABLA: Consultas médicas
CREATE TABLE consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    paciente_id INT NOT NULL,
    doctor_id INT NOT NULL,
    triaje_id INT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    diagnostico TEXT,
    requiere_laboratorio BOOLEAN DEFAULT FALSE,
    receta_directa BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (paciente_id) REFERENCES pacientes(id),
    FOREIGN KEY (doctor_id) REFERENCES empleados(id),
    FOREIGN KEY (triaje_id) REFERENCES triajes(id)
);

-- TABLA: Pagos realizados
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consulta_id INT NOT NULL,
    empleado_id INT NOT NULL, -- administrativo
    fecha_pago DATETIME DEFAULT CURRENT_TIMESTAMP,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL,
    FOREIGN KEY (consulta_id) REFERENCES consultas(id),
    FOREIGN KEY (empleado_id) REFERENCES empleados(id)
);

-- TABLA: Órdenes de laboratorio
CREATE TABLE ordenes_laboratorio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consulta_id INT NOT NULL,
    doctor_id INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipo_examen VARCHAR(100) NOT NULL,
    observaciones TEXT,
    FOREIGN KEY (consulta_id) REFERENCES consultas(id),
    FOREIGN KEY (doctor_id) REFERENCES empleados(id)
);

-- TABLA: Resultados de laboratorio
CREATE TABLE resultados_laboratorio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT NOT NULL,
    laboratorista_id INT NOT NULL,
    fecha_resultado DATETIME DEFAULT CURRENT_TIMESTAMP,
    resultado TEXT NOT NULL,
    observaciones TEXT,
    FOREIGN KEY (orden_id) REFERENCES ordenes_laboratorio(id),
    FOREIGN KEY (laboratorista_id) REFERENCES empleados(id)
);

-- TABLA: Recetas emitidas por el doctor
CREATE TABLE recetas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consulta_id INT NOT NULL,
    doctor_id INT NOT NULL,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    medicamentos TEXT NOT NULL, -- puedes normalizar esto si se requiere detalle por medicamento
    instrucciones TEXT,
    FOREIGN KEY (consulta_id) REFERENCES consultas(id),
    FOREIGN KEY (doctor_id) REFERENCES empleados(id)
);
