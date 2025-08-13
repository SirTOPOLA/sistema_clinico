-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-08-2025 a las 13:58:26
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `consultorio`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `analiticas`
--

CREATE TABLE `analiticas` (
  `id` int(11) NOT NULL,
  `resultado` text DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `id_tipo_prueba` int(11) NOT NULL,
  `id_consulta` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `codigo_paciente` varchar(50) DEFAULT NULL,
  `pagado` tinyint(1) DEFAULT 0,
  `valores_refencia` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `analiticas`
--

INSERT INTO `analiticas` (`id`, `resultado`, `estado`, `id_tipo_prueba`, `id_consulta`, `fecha_registro`, `id_usuario`, `id_paciente`, `codigo_paciente`, `pagado`, `valores_refencia`) VALUES
(2, 'Negativo', '1', 1, 1, '2025-06-13 15:22:02', 1, 2, 'SM2007060636698143', 1, ''),
(3, 'POSITIVO', '1', 2, 1, '2025-06-13 15:22:02', 1, 2, 'SM2007060636698143', 1, 'bajo de 0-30 normal 30-60 Riesgo 60-100'),
(4, 'Positivo', '1', 1, 2, '2025-07-23 11:53:20', 3, 4, 'GS052264', 1, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras_proveedores`
--

CREATE TABLE `compras_proveedores` (
  `id` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `id_personal` int(11) NOT NULL,
  `fecha_compra` date NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `adelanto` decimal(10,2) DEFAULT 0.00,
  `estado_pago` varchar(50) DEFAULT 'pendiente',
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas`
--

CREATE TABLE `consultas` (
  `id` int(11) NOT NULL,
  `motivo_consulta` text DEFAULT NULL,
  `temperatura` float DEFAULT NULL,
  `control_cada_horas` int(11) DEFAULT NULL,
  `frecuencia_cardiaca` int(11) DEFAULT NULL,
  `frecuencia_respiratoria` int(11) DEFAULT NULL,
  `tension_arterial` varchar(20) DEFAULT NULL,
  `pulso` int(11) DEFAULT NULL,
  `saturacion_oxigeno` float DEFAULT NULL,
  `peso_anterior` float DEFAULT NULL,
  `peso_actual` float DEFAULT NULL,
  `peso_ideal` float DEFAULT NULL,
  `imc` float DEFAULT NULL,
  `id_paciente` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `pagado` int(1) NOT NULL,
  `precio` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `consultas`
--

INSERT INTO `consultas` (`id`, `motivo_consulta`, `temperatura`, `control_cada_horas`, `frecuencia_cardiaca`, `frecuencia_respiratoria`, `tension_arterial`, `pulso`, `saturacion_oxigeno`, `peso_anterior`, `peso_actual`, `peso_ideal`, `imc`, `id_paciente`, `id_usuario`, `fecha_registro`, `pagado`, `precio`) VALUES
(1, 'dolor desde hace 2 dias', 36, 2, 45, 65, '456', 34, 35, 69, 67, 66, 5, 2, 1, '2025-06-12 16:12:39', 1, 1000),
(2, 'fiebre amarilla', 38, 3, 90, 19, '120/80', 80, 98, 65, 62, 70, 24.8, 4, 1, '2025-07-23 12:43:39', 1, 15000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `depositos`
--

CREATE TABLE `depositos` (
  `id` int(11) NOT NULL,
  `poliza_id` int(11) DEFAULT NULL,
  `saldo` decimal(10,2) DEFAULT NULL,
  `respaldo` decimal(10,2) DEFAULT NULL,
  `tiempo_devolucion` varchar(10) DEFAULT NULL,
  `fecha_devolucion_respaldo` date DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compra_proveedores`
--

CREATE TABLE `detalle_compra_proveedores` (
  `id` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `unidad` varchar(50) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_consulta`
--

CREATE TABLE `detalle_consulta` (
  `id` int(11) NOT NULL,
  `operacion` text DEFAULT NULL,
  `orina` varchar(50) DEFAULT NULL,
  `defeca` varchar(50) DEFAULT NULL,
  `defeca_dias` int(11) DEFAULT NULL,
  `duerme` varchar(50) DEFAULT NULL,
  `duerme_horas` int(11) DEFAULT NULL,
  `antecedentes_patologicos` text DEFAULT NULL,
  `alergico` text DEFAULT NULL,
  `antecedentes_familiares` text DEFAULT NULL,
  `antecedentes_conyuge` text DEFAULT NULL,
  `control_signos_vitales` text DEFAULT NULL,
  `id_consulta` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `detalle_consulta`
--

INSERT INTO `detalle_consulta` (`id`, `operacion`, `orina`, `defeca`, `defeca_dias`, `duerme`, `duerme_horas`, `antecedentes_patologicos`, `alergico`, `antecedentes_familiares`, `antecedentes_conyuge`, `control_signos_vitales`, `id_consulta`, `id_usuario`, `fecha_registro`) VALUES
(1, 'no', 'si', 'si', 4, 'si', 6, 'TB', 'NO', 'NO', 'NO', '4', 1, 1, '2025-06-12 16:12:39'),
(2, 'no', 'si', 'si', 1, 'si', 8, 'no', 'no', 'no', 'no', 'no', 2, 1, '2025-07-23 12:43:39');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos`
--

CREATE TABLE `ingresos` (
  `id` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `id_sala` int(11) NOT NULL,
  `fecha_ingreso` datetime NOT NULL,
  `fecha_alta` datetime DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL,
  `numero_cama` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `ingresos`
--

INSERT INTO `ingresos` (`id`, `id_paciente`, `id_sala`, `fecha_ingreso`, `fecha_alta`, `token`, `fecha_registro`, `id_usuario`, `numero_cama`) VALUES
(1, 2, 1, '2025-06-16 15:30:00', '2025-06-17 16:10:00', '1', '2025-06-17 12:27:31', 3, 2),
(2, 4, 1, '2025-07-24 15:10:00', NULL, NULL, '2025-07-24 15:10:46', 3, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pacientes`
--

CREATE TABLE `pacientes` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `dip` varchar(50) DEFAULT NULL,
  `sexo` varchar(10) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `profesion` varchar(100) DEFAULT NULL,
  `ocupacion` varchar(100) DEFAULT NULL,
  `tutor_nombre` varchar(100) DEFAULT NULL,
  `telefono_tutor` varchar(20) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `pacientes`
--

INSERT INTO `pacientes` (`id`, `codigo`, `nombre`, `apellidos`, `fecha_nacimiento`, `dip`, `sexo`, `direccion`, `email`, `telefono`, `profesion`, `ocupacion`, `tutor_nombre`, `telefono_tutor`, `id_usuario`, `fecha_registro`) VALUES
(2, 'SM2007060636698143', 'salvador 2', 'mete bijeri', '2007-06-06', '3776539', 'Masculino', 'Buena esperanza I', 'salvadormete@gmail.com', '555432345', 'estudiante', 'estudiante', 'no tiene', 'no tiene', 1, '2025-06-12 11:14:13'),
(3, 'MC061545', 'Maximiliano', 'Compe Puye', '2005-06-15', '8963542', 'Masculino', 'Ela Nguema', 'maxicomoe@gmail.com', '555667809', 'estudiante', 'estudiante', 'no tiene', 'no tiene', 1, '2025-06-17 13:16:03'),
(4, 'GS052264', 'Gerónimo', 'saka Bepa', '2025-05-22', '000147852', 'Masculino', 'Sumko', NULL, '555101214', 'secretariada', 'dependienta', 'Marta Beta', '555101214', 1, '2025-07-23 11:15:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente_poliza`
--

CREATE TABLE `paciente_poliza` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) DEFAULT NULL,
  `poliza_id` int(11) DEFAULT NULL,
  `titular` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `id_analitica` int(11) NOT NULL,
  `id_tipo_prueba` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `cantidad`, `id_analitica`, `id_tipo_prueba`, `fecha_registro`, `id_usuario`) VALUES
(2, 5000.00, 2, 1, '2025-06-24 15:40:58', 1),
(3, 9000.00, 3, 2, '2025-06-24 15:40:58', 1),
(4, 5000.00, 4, 1, '2025-07-23 12:01:52', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE `personal` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellidos` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `especialidad` varchar(100) DEFAULT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id`, `nombre`, `apellidos`, `fecha_nacimiento`, `direccion`, `correo`, `telefono`, `especialidad`, `codigo`, `fecha_registro`, `id_usuario`) VALUES
(1, 'Jesus Crispin', 'Topola Boñaho', '1997-06-30', 'Ela Nguema', 'sir@gmail.com', '551718822', 'Programador', 'fc123', '2025-06-10 17:23:31', 1),
(2, 'salvador', 'Mete Bijeri', '2000-05-09', 'calle mongomo', 'salvadormete@gmail.com', '555908732', 'Medicina Interna', 'SM250616', '2025-06-16 11:36:34', 1),
(3, 'Maximiliano', 'Compe Puye', '1990-06-13', 'CAMPO AMOR', 'maxicomoe@gmail.com', '555971145', 'Doctor', 'MC250617', '2025-06-17 11:26:31', 1),
(4, 'Gerónimo', 'saka Bepa', '2000-09-06', 'Sumko', 'geronimo@gmail.com', '555101214', 'Enfermeria', 'GS250721', '2025-07-21 13:50:59', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `polizas`
--

CREATE TABLE `polizas` (
  `id` int(11) NOT NULL,
  `numero_poliza` varchar(50) DEFAULT NULL,
  `tipo` enum('familiar','individual') DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_farmacia`
--

CREATE TABLE `productos_farmacia` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `stock_caja` int(11) DEFAULT 0,
  `stock_frasco` int(11) DEFAULT 0,
  `stock_tira` int(11) DEFAULT 0,
  `stock_pastilla` int(11) DEFAULT 0,
  `precio_caja` decimal(10,2) NOT NULL,
  `precio_frasco` decimal(10,2) NOT NULL,
  `precio_tira` decimal(10,2) NOT NULL,
  `precio_pastilla` decimal(10,2) NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `contacto` varchar(255) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recetas`
--

CREATE TABLE `recetas` (
  `id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `id_consulta` int(11) NOT NULL,
  `id_paciente` int(11) NOT NULL,
  `codigo_paciente` varchar(50) DEFAULT NULL,
  `comentario` text DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `recetas`
--

INSERT INTO `recetas` (`id`, `descripcion`, `id_consulta`, `id_paciente`, `codigo_paciente`, `comentario`, `fecha_registro`, `id_usuario`) VALUES
(1, 'paracetamol(1mg): solo si hay dolor o fiebre\r\nampicilina: uno en la mañana, uno en la noche', 1, 2, 'SM2007060636698143', 'mantener fuera del alcance de los niños.', '2025-06-13 16:19:34', 1),
(2, 'paracetamol: tomar mañana y tarde\r\nVitamina C: una vez por día', 2, 4, 'GS052264', 'dejar fuera del alcance', '2025-07-24 14:12:35', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `fecha_registro`) VALUES
(1, 'Administrador', '2025-06-10 17:26:17'),
(2, 'laboratorio', '2025-06-16 11:53:15'),
(6, 'doctor', '2025-06-17 11:28:47'),
(7, 'farmacia', '2025-08-12 10:04:55');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salas_ingreso`
--

CREATE TABLE `salas_ingreso` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `salas_ingreso`
--

INSERT INTO `salas_ingreso` (`id`, `nombre`, `fecha_registro`, `id_usuario`) VALUES
(1, 'Sala 5', '2025-06-12 11:27:23', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_pruebas`
--

CREATE TABLE `tipo_pruebas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tipo_pruebas`
--

INSERT INTO `tipo_pruebas` (`id`, `nombre`, `precio`, `fecha_registro`, `id_usuario`) VALUES
(1, 'PALUDISMO', 5000.00, '2025-06-12 12:38:09', 1),
(2, 'HEPATITIS B', 9000.00, '2025-06-12 12:39:44', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usos_deposito`
--

CREATE TABLE `usos_deposito` (
  `id` int(11) NOT NULL,
  `deposito_id` int(11) DEFAULT NULL,
  `paciente_id` int(11) DEFAULT NULL,
  `concepto` varchar(225) DEFAULT NULL,
  `monto_usado` decimal(10,2) DEFAULT NULL,
  `descuento` decimal(10,2) DEFAULT NULL,
  `fecha` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `id_personal` int(11) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `id_rol`, `password`, `id_personal`, `fecha_registro`) VALUES
(1, 'admin', 1, '$2y$10$tDik4yXSE.O1bGIku8JHKe9NwJ4jZY6iL3AH.8aph/DuUjHcpoL5O', 1, '2025-06-10 17:42:47'),
(2, 'laboratorio', 2, '$2y$10$3IngK68OS2Gzb9A4LVuOMO4ngAa94N6wNv/E0p/WrBI6cQgvg6UCu', 2, '2025-06-16 11:57:31'),
(3, 'doctor', 6, '$2y$10$7RSZBKnEruBgvgrOpaMshewnXBGy2dhWkarTPAtPrb6HY/kcCSdRG', 3, '2025-06-17 11:29:09'),
(4, 'laboratorio1', 2, '$2y$10$lWePJ1KBrM8vzac8lW11pesrNGdcsERFz55XUHl23DKKTJxzz4PKe', 4, '2025-07-21 14:46:43');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `analiticas`
--
ALTER TABLE `analiticas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tipo_prueba` (`id_tipo_prueba`),
  ADD KEY `id_consulta` (`id_consulta`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_paciente` (`id_paciente`);

--
-- Indices de la tabla `compras_proveedores`
--
ALTER TABLE `compras_proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `id_personal` (`id_personal`);

--
-- Indices de la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `depositos`
--
ALTER TABLE `depositos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poliza_id` (`poliza_id`);

--
-- Indices de la tabla `detalle_compra_proveedores`
--
ALTER TABLE `detalle_compra_proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_compra` (`id_compra`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `detalle_consulta`
--
ALTER TABLE `detalle_consulta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_consulta` (`id_consulta`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_sala` (`id_sala`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `paciente_poliza`
--
ALTER TABLE `paciente_poliza`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `poliza_id` (`poliza_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_analitica` (`id_analitica`),
  ADD KEY `id_tipo_prueba` (`id_tipo_prueba`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `polizas`
--
ALTER TABLE `polizas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_poliza` (`numero_poliza`);

--
-- Indices de la tabla `productos_farmacia`
--
ALTER TABLE `productos_farmacia`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_consulta` (`id_consulta`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `salas_ingreso`
--
ALTER TABLE `salas_ingreso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `tipo_pruebas`
--
ALTER TABLE `tipo_pruebas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usos_deposito`
--
ALTER TABLE `usos_deposito`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deposito_id` (`deposito_id`),
  ADD KEY `paciente_id` (`paciente_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_personal` (`id_personal`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `analiticas`
--
ALTER TABLE `analiticas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `compras_proveedores`
--
ALTER TABLE `compras_proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `depositos`
--
ALTER TABLE `depositos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_compra_proveedores`
--
ALTER TABLE `detalle_compra_proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_consulta`
--
ALTER TABLE `detalle_consulta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `paciente_poliza`
--
ALTER TABLE `paciente_poliza`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `polizas`
--
ALTER TABLE `polizas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos_farmacia`
--
ALTER TABLE `productos_farmacia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recetas`
--
ALTER TABLE `recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `salas_ingreso`
--
ALTER TABLE `salas_ingreso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipo_pruebas`
--
ALTER TABLE `tipo_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usos_deposito`
--
ALTER TABLE `usos_deposito`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `analiticas`
--
ALTER TABLE `analiticas`
  ADD CONSTRAINT `analiticas_ibfk_1` FOREIGN KEY (`id_tipo_prueba`) REFERENCES `tipo_pruebas` (`id`),
  ADD CONSTRAINT `analiticas_ibfk_2` FOREIGN KEY (`id_consulta`) REFERENCES `consultas` (`id`),
  ADD CONSTRAINT `analiticas_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `analiticas_ibfk_4` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id`);

--
-- Filtros para la tabla `compras_proveedores`
--
ALTER TABLE `compras_proveedores`
  ADD CONSTRAINT `compras_proveedores_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `compras_proveedores_ibfk_2` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD CONSTRAINT `consultas_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id`),
  ADD CONSTRAINT `consultas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `depositos`
--
ALTER TABLE `depositos`
  ADD CONSTRAINT `depositos_ibfk_1` FOREIGN KEY (`poliza_id`) REFERENCES `polizas` (`id`);

--
-- Filtros para la tabla `detalle_compra_proveedores`
--
ALTER TABLE `detalle_compra_proveedores`
  ADD CONSTRAINT `detalle_compra_proveedores_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras_proveedores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_compra_proveedores_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos_farmacia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_consulta`
--
ALTER TABLE `detalle_consulta`
  ADD CONSTRAINT `detalle_consulta_ibfk_1` FOREIGN KEY (`id_consulta`) REFERENCES `consultas` (`id`),
  ADD CONSTRAINT `detalle_consulta_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD CONSTRAINT `ingresos_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id`),
  ADD CONSTRAINT `ingresos_ibfk_2` FOREIGN KEY (`id_sala`) REFERENCES `salas_ingreso` (`id`),
  ADD CONSTRAINT `ingresos_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `pacientes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `paciente_poliza`
--
ALTER TABLE `paciente_poliza`
  ADD CONSTRAINT `paciente_poliza_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`),
  ADD CONSTRAINT `paciente_poliza_ibfk_2` FOREIGN KEY (`poliza_id`) REFERENCES `polizas` (`id`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_analitica`) REFERENCES `analiticas` (`id`),
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`id_tipo_prueba`) REFERENCES `tipo_pruebas` (`id`),
  ADD CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `recetas`
--
ALTER TABLE `recetas`
  ADD CONSTRAINT `recetas_ibfk_1` FOREIGN KEY (`id_consulta`) REFERENCES `consultas` (`id`),
  ADD CONSTRAINT `recetas_ibfk_2` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id`),
  ADD CONSTRAINT `recetas_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `salas_ingreso`
--
ALTER TABLE `salas_ingreso`
  ADD CONSTRAINT `salas_ingreso_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `tipo_pruebas`
--
ALTER TABLE `tipo_pruebas`
  ADD CONSTRAINT `tipo_pruebas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `usos_deposito`
--
ALTER TABLE `usos_deposito`
  ADD CONSTRAINT `usos_deposito_ibfk_1` FOREIGN KEY (`deposito_id`) REFERENCES `depositos` (`id`),
  ADD CONSTRAINT `usos_deposito_ibfk_2` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
