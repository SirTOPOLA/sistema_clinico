-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 20, 2025 at 02:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `consultorio`
--

-- --------------------------------------------------------

--
-- Table structure for table `analiticas`
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
  `valores_refencia` text DEFAULT NULL,
  `tipo_pago` enum('EFECTIVO','SEGURO','ADEUDO','SIN PAGAR') DEFAULT 'SIN PAGAR'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `analiticas`
--

INSERT INTO `analiticas` (`id`, `resultado`, `estado`, `id_tipo_prueba`, `id_consulta`, `fecha_registro`, `id_usuario`, `id_paciente`, `codigo_paciente`, `pagado`, `valores_refencia`, `tipo_pago`) VALUES
(2, 'negativo', '1', 1, 1, '2025-06-13 15:22:02', 1, 2, 'SM2007060636698143', 1, '', 'SIN PAGAR'),
(3, 'POSITIVO', '1', 2, 1, '2025-06-13 15:22:02', 1, 2, 'SM2007060636698143', 1, 'bajo de 0-30 normal 30-60 Riesgo 60-100', 'SIN PAGAR'),
(4, NULL, '0', 1, 2, '2025-09-02 10:35:24', 1, 4, 'CM100201', 0, NULL, 'ADEUDO'),
(5, NULL, '0', 1, 4, '2025-09-02 11:55:54', 1, 3, 'MC061545', 1, NULL, 'EFECTIVO'),
(6, NULL, '0', 1, 3, '2025-09-02 12:04:31', 1, 2, 'SM2007060636698143', 0, NULL, 'ADEUDO'),
(7, 'POSITIVO', '1', 1, 4, '2025-09-09 10:11:21', 1, 3, 'MC061545', 1, '', 'EFECTIVO'),
(8, NULL, '0', 2, 2, '2025-11-26 13:20:12', 1, 4, 'CM100201', 0, NULL, 'SIN PAGAR'),
(9, NULL, '0', 1, 2, '2025-11-26 13:20:12', 1, 4, 'CM100201', 0, NULL, 'SIN PAGAR'),
(10, NULL, '0', 1, 4, '2025-11-26 13:21:34', 1, 3, 'MC061545', 0, NULL, 'SIN PAGAR'),
(11, NULL, '0', 1, 5, '2025-11-26 15:44:31', 1, 9, 'RB123115', 0, NULL, 'SIN PAGAR');

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Analgésicos ', 'Medicamentos para aliviar el dolor '),
(2, 'Antibiótico', 'Medicamentos para combatir infecciones bacterianas.'),
(3, 'Antifebril', 'Medicamentos para reducir la fiebre.'),
(4, 'Vitaminas', 'Suplementos nutricionales.'),
(5, 'accesorios', 'Material clínico'),
(6, 'suplementos alimenticios', 'Los suplementos alimenticios son productos orales que complementan la dieta,pero no remplazan a los alimentos.\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `codigo_factura` varchar(100) DEFAULT NULL,
  `personal_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `monto_entregado` decimal(12,2) DEFAULT 0.00,
  `monto_gastado` decimal(12,2) DEFAULT 0.00,
  `cambio_devuelto` decimal(12,2) DEFAULT 0.00,
  `monto_pendiente` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `estado_pago` enum('PAGADO','PENDIENTE','PARCIAL') DEFAULT 'PENDIENTE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `compras`
--

INSERT INTO `compras` (`id`, `proveedor_id`, `codigo_factura`, `personal_id`, `fecha`, `monto_entregado`, `monto_gastado`, `cambio_devuelto`, `monto_pendiente`, `total`, `estado_pago`) VALUES
(1, 1, 'CDO-20250826-162920-68adc4c037301', 4, '2025-08-25', 10000.00, 10000.00, 0.00, 0.00, 10000.00, 'PAGADO'),
(2, 1, 'CDO-20250826-163613-68adc65db2fc1', 2, '2025-08-26', 15000.00, 16000.00, 0.00, 0.00, 15000.00, 'PAGADO'),
(3, 1, 'FAC-20250826-a5d4e390', 3, '2025-08-18', 12000.00, 10000.00, 2000.00, 0.00, 10000.00, 'PAGADO'),
(4, 1, 'FAC-20250826-d0563bdd', 1, '2025-08-26', 20000.00, 25000.00, 0.00, 0.00, 25000.00, 'PAGADO'),
(5, 1, 'FAC-20251206-8842fefb', 5, '2025-12-06', 10000.00, 10750.00, 0.00, 0.00, 10750.00, 'PAGADO'),
(6, 2, 'FAC-20251207-db9d03d4', 3, '2025-12-07', 25000.00, 30000.00, 0.00, 0.00, 30000.00, 'PAGADO');

-- --------------------------------------------------------

--
-- Table structure for table `compras_detalle`
--

CREATE TABLE `compras_detalle` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `compras_detalle`
--

INSERT INTO `compras_detalle` (`id`, `compra_id`, `producto_id`, `cantidad`, `precio_compra`) VALUES
(7, 3, 1, 10, 1000.00),
(8, 1, 1, 10, 1000.00),
(9, 2, 1, 15, 1000.00),
(10, 4, 1, 25, 1000.00),
(11, 5, 2, 43, 250.00),
(12, 6, 2, 100, 300.00);

-- --------------------------------------------------------

--
-- Table structure for table `consultas`
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
-- Dumping data for table `consultas`
--

INSERT INTO `consultas` (`id`, `motivo_consulta`, `temperatura`, `control_cada_horas`, `frecuencia_cardiaca`, `frecuencia_respiratoria`, `tension_arterial`, `pulso`, `saturacion_oxigeno`, `peso_anterior`, `peso_actual`, `peso_ideal`, `imc`, `id_paciente`, `id_usuario`, `fecha_registro`, `pagado`, `precio`) VALUES
(1, 'dolor desde hace 2 dias', 36, 2, 45, 65, '456', 34, 35, 69, 67, 66, 5, 2, 1, '2025-06-12 16:12:39', 1, 1000),
(2, 'dolor de cabeza', 37, 8, 75, 16, '120/80', 70, 98, 70, 68, 70, 22, 4, 1, '2025-09-02 11:29:01', 1, 1500),
(3, 'fiebre de 3 noches', 38, 8, 75, 16, '120/80', 72, 98, 70, 68, 70, 23, 2, 1, '2025-09-02 11:33:08', 1, 0),
(4, 'dolor de cuerpo', 38, 8, 75, 16, '120/80', 72, 98, 70, 68, 70, 23, 3, 1, '2025-09-02 12:54:04', 0, 0),
(5, 'fiebre', 38, 8, 75, 16, '120/90', 72, 98, 55, 50, 55, 22, 9, 1, '2025-11-26 15:42:07', 1, 0),
(6, 'dolor', 38, 8, 75, 16, '120/90', 72, 98, 70, 69, 72, 22, 3, 1, '2025-12-20 01:06:13', 1, 0),
(7, 'cabeza', 36, 8, 72, 16, '120', 72, 88, 70, 69, 72, 22, 4, 1, '2025-12-20 01:11:06', 1, 1000),
(8, 'fiebre', 38, 8, 74, 16, '120', 72, 98, 70, 69, 72, 22, 9, 1, '2025-12-20 01:12:54', 0, 1000);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_consulta`
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
-- Dumping data for table `detalle_consulta`
--

INSERT INTO `detalle_consulta` (`id`, `operacion`, `orina`, `defeca`, `defeca_dias`, `duerme`, `duerme_horas`, `antecedentes_patologicos`, `alergico`, `antecedentes_familiares`, `antecedentes_conyuge`, `control_signos_vitales`, `id_consulta`, `id_usuario`, `fecha_registro`) VALUES
(1, 'no', 'si', 'si', 4, 'si', 6, 'TB', 'NO', 'NO', 'NO', '4', 1, 1, '2025-06-12 16:12:39'),
(2, 'on', '', '', 1, 'on', 8, '', '', '', '', '', 2, 1, '2025-09-02 11:29:01'),
(3, '0', '1', '1', 1, '1', 8, '0', '1', '0', '0', '1', 3, 1, '2025-09-02 11:33:08'),
(4, '', 'on', 'on', 1, 'on', 8, '', '', '', '', '', 4, 1, '2025-09-02 12:54:04'),
(5, '', 'on', 'on', 3, 'on', 8, '', '', '', '', '', 5, 1, '2025-11-26 15:42:07'),
(6, '', 'on', 'on', 3, 'on', 8, '', '', '', '', 'on', 6, 1, '2025-12-20 01:06:13'),
(7, '', 'on', 'on', 4, 'on', 6, '', '', '', '', '', 7, 1, '2025-12-20 01:11:06'),
(8, '', 'on', 'on', 4, 'on', 8, '', '', '', '', '', 8, 1, '2025-12-20 01:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `ingresos`
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
-- Dumping data for table `ingresos`
--

INSERT INTO `ingresos` (`id`, `id_paciente`, `id_sala`, `fecha_ingreso`, `fecha_alta`, `token`, `fecha_registro`, `id_usuario`, `numero_cama`) VALUES
(1, 2, 1, '2025-06-16 15:30:00', '2025-06-17 16:10:00', '1', '2025-06-17 12:27:31', 3, 2),
(2, 2, 1, '2025-09-03 11:22:00', NULL, NULL, '2025-09-03 11:22:34', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `tipo` enum('ENTRADA','SALIDA') NOT NULL,
  `referencia` varchar(50) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `movimientos_seguro`
--

CREATE TABLE `movimientos_seguro` (
  `id` int(11) NOT NULL,
  `seguro_id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `venta_id` int(11) DEFAULT NULL,
  `tipo` enum('CREDITO','DEBITO') NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `descripcion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `movimientos_seguro`
--

INSERT INTO `movimientos_seguro` (`id`, `seguro_id`, `paciente_id`, `venta_id`, `tipo`, `monto`, `fecha`, `descripcion`) VALUES
(1, 1, 2, NULL, 'CREDITO', 100000.00, '2025-09-01 14:17:40', 'Depósito inicial del seguro'),
(2, 1, 2, NULL, 'DEBITO', 2200.00, '2025-12-08 22:45:56', 'Consumo en farmacia'),
(3, 1, 3, NULL, 'DEBITO', 700.00, '2025-12-08 22:54:46', 'Consumo en farmacia'),
(4, 1, 2, NULL, 'DEBITO', 0.00, '2025-12-20 01:07:08', 'Pago consulta con seguro');

-- --------------------------------------------------------

--
-- Table structure for table `pacientes`
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
-- Dumping data for table `pacientes`
--

INSERT INTO `pacientes` (`id`, `codigo`, `nombre`, `apellidos`, `fecha_nacimiento`, `dip`, `sexo`, `direccion`, `email`, `telefono`, `profesion`, `ocupacion`, `tutor_nombre`, `telefono_tutor`, `id_usuario`, `fecha_registro`) VALUES
(2, 'SM2007060636698143', 'salvador 2', 'mete bijeri', '2007-06-06', '3776539', 'Masculino', 'Buena esperanza I', 'salvadormete@gmail.com', '555432345', 'estudiante', 'estudiante', 'no tiene', 'no tiene', 1, '2025-06-12 11:14:13'),
(3, 'MC061545', 'Maximiliano', 'Compe', '2005-06-15', '8963542', 'Masculino', 'Ela Nguema', 'maxicomoe@gmail.com', '555667809', 'estudiante', 'estudiante', 'no tiene', 'no tiene', 1, '2025-06-17 13:16:03'),
(4, 'CM100201', 'Carlos', 'Mete Boko', '2012-10-02', '000000000', 'Masculino', 'Ela-Nguema', NULL, '222555777', 'Informático', 'informatico', 'Carlos Luis', '222555777', 1, '2025-09-02 09:57:37'),
(8, 'AT061077', 'Alba', 'tope', '2020-06-10', '0001245785', 'Femenino', 'Calle Mongomo', NULL, '222011225', 'mis labores', 'Mis labores', 'simplicia', '555147896', 1, '2025-09-10 13:46:26'),
(9, 'RB123115', 'Rufina', 'Bechiro Batapa', '2004-12-31', '000407309', 'Femenino', 'Bisinga', NULL, '555709860', 'estudiante', 'estudiante', 'Jesus crispin Topolá', '551718822', 1, '2025-11-26 15:38:20');

-- --------------------------------------------------------

--
-- Table structure for table `pagos`
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
-- Dumping data for table `pagos`
--

INSERT INTO `pagos` (`id`, `cantidad`, `id_analitica`, `id_tipo_prueba`, `fecha_registro`, `id_usuario`) VALUES
(2, 5000.00, 2, 1, '2025-06-24 15:40:58', 1),
(3, 9000.00, 3, 2, '2025-06-24 15:40:58', 1),
(4, 5000.00, 5, 1, '2025-09-02 12:22:12', 1),
(5, 3000.00, 4, 1, '2025-09-05 15:40:06', 1),
(6, 3000.00, 4, 1, '2025-09-05 15:59:27', 1),
(7, 4000.00, 6, 1, '2025-09-09 09:25:17', 1),
(8, 5000.00, 7, 1, '2025-09-09 11:31:23', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pagos_proveedores`
--

CREATE TABLE `pagos_proveedores` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `monto` decimal(12,2) NOT NULL,
  `fecha` date NOT NULL,
  `metodo_pago` enum('EFECTIVO','TRANSFERENCIA','TARJETA','OTRO') DEFAULT 'EFECTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pagos_proveedores`
--

INSERT INTO `pagos_proveedores` (`id`, `compra_id`, `proveedor_id`, `monto`, `fecha`, `metodo_pago`) VALUES
(5, 2, 1, 2500.00, '2025-08-29', 'EFECTIVO'),
(7, 2, 1, 1000.00, '2025-12-06', 'EFECTIVO'),
(8, 5, 1, 750.00, '2025-12-06', 'EFECTIVO'),
(9, 6, 2, 5000.00, '2025-12-08', 'EFECTIVO'),
(10, 4, 1, 5000.00, '2025-11-24', 'EFECTIVO');

-- --------------------------------------------------------

--
-- Table structure for table `personal`
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
-- Dumping data for table `personal`
--

INSERT INTO `personal` (`id`, `nombre`, `apellidos`, `fecha_nacimiento`, `direccion`, `correo`, `telefono`, `especialidad`, `codigo`, `fecha_registro`, `id_usuario`) VALUES
(1, 'Jesus Crispin', 'Topola Boñaho', '1997-06-30', 'Ela Nguema', 'sir@gmail.com', '551718822', 'Programador', 'fc123', '2025-06-10 17:23:31', 1),
(2, 'salvador', 'Mete Bijeri', '2000-05-09', 'calle mongomo', 'salvadormete@gmail.com', '555908732', 'Medicina Interna', 'SM250616', '2025-06-16 11:36:34', 1),
(3, 'Maximiliano', 'Compe Puye', '1990-06-13', 'CAMPO AMOR', 'maxicomoe@gmail.com', '555971145', 'Doctor', 'MC250617', '2025-06-17 11:26:31', 1),
(4, 'marisol', 'bosochi', '2001-01-09', 'buena esperanza II', 'marisolbosochi@gmail.com', '555908765', 'Medicina General', 'MB250625', '2025-06-25 13:47:10', 1),
(5, 'amadi', 'amady', '2003-02-05', 'Los ángeles', 'amadi@gmail.com', '555414141', 'Enfermería', 'AA250910', '2025-09-10 09:31:20', NULL),
(6, 'Alberto', 'Topepam', '1996-06-05', 'Bar Peaje', '', '222011225', 'Pedagogía', 'AT250910', '2025-09-10 13:27:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `prestamos`
--

CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `estado` enum('PENDIENTE','PARCIAL','PAGADO') DEFAULT 'PENDIENTE',
  `fecha` date NOT NULL,
  `origen_tipo` enum('CONSULTA','ANALITICA','VENTA','OTRO') NOT NULL,
  `origen_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prestamos`
--

INSERT INTO `prestamos` (`id`, `paciente_id`, `total`, `estado`, `fecha`, `origen_tipo`, `origen_id`) VALUES
(1, 2, 500.00, 'PENDIENTE', '2025-08-29', 'CONSULTA', 0),
(2, 3, 2000.00, 'PENDIENTE', '2025-08-29', 'CONSULTA', 0),
(3, 4, 2000.00, 'PARCIAL', '2025-09-05', 'CONSULTA', 0),
(4, 4, 2000.00, 'PARCIAL', '2025-09-05', 'CONSULTA', 0),
(5, 2, 1000.00, 'PARCIAL', '2025-09-09', 'CONSULTA', 0),
(6, 2, 500.00, 'PARCIAL', '2025-12-20', 'CONSULTA', 0),
(7, 8, 500.00, 'PARCIAL', '2025-12-20', 'CONSULTA', 0),
(8, 9, 300.00, 'PARCIAL', '2025-12-20', 'CONSULTA', 8);

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `concentracion` varchar(50) DEFAULT NULL,
  `forma_farmaceutica` varchar(50) DEFAULT NULL,
  `presentacion` varchar(100) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `unidad_id` int(11) DEFAULT NULL,
  `precio_unitario` decimal(10,2) DEFAULT NULL,
  `stock_actual` int(11) DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `concentracion`, `forma_farmaceutica`, `presentacion`, `categoria_id`, `unidad_id`, `precio_unitario`, `stock_actual`, `stock_minimo`) VALUES
(1, 'Paracetamol', '50mg', NULL, 'Tabletas', 1, 1, 1500.00, 42, 5),
(2, 'MEMOCER', '445mg', 'capsula', 'Caja con 60 cápsulas', 6, 1, 350.00, 137, 6);

-- --------------------------------------------------------

--
-- Table structure for table `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `direccion`, `telefono`, `contacto`) VALUES
(1, 'La Roca', 'Cruce Escala Uno', '222010585/551710111', 'Divina'),
(2, 'SESGO S.L.', 'Semu', '555200121', 'Lucas Mema');

-- --------------------------------------------------------

--
-- Table structure for table `recetas`
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
-- Dumping data for table `recetas`
--

INSERT INTO `recetas` (`id`, `descripcion`, `id_consulta`, `id_paciente`, `codigo_paciente`, `comentario`, `fecha_registro`, `id_usuario`) VALUES
(1, 'paracetamol(1mg): solo si hay dolor o fiebre\r\nampicilina: uno en la mañana, uno en la noche', 1, 2, 'SM2007060636698143', 'mantener fuera del alcance de los niños.', '2025-06-13 16:19:34', 1);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `fecha_registro`) VALUES
(1, 'Administrador', '2025-06-10 17:26:17'),
(2, 'laboratorio', '2025-06-16 11:53:15'),
(3, 'farmacia', '2025-06-16 11:54:02'),
(4, 'triaje', '2025-06-16 11:54:37'),
(5, 'urgencia', '2025-06-16 11:54:37'),
(6, 'doctor', '2025-06-17 11:28:47');

-- --------------------------------------------------------

--
-- Table structure for table `salas_ingreso`
--

CREATE TABLE `salas_ingreso` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `salas_ingreso`
--

INSERT INTO `salas_ingreso` (`id`, `nombre`, `fecha_registro`, `id_usuario`) VALUES
(1, 'Sala 6', '2025-06-12 11:27:23', 1),
(4, 'Sala 7', '2025-09-12 09:36:56', 1),
(5, 'Sala 1', '2025-09-12 09:37:11', 1),
(6, 'sala 3', '2025-09-12 09:39:56', 1);

-- --------------------------------------------------------

--
-- Table structure for table `seguros`
--

CREATE TABLE `seguros` (
  `id` int(11) NOT NULL,
  `titular_id` int(11) NOT NULL,
  `monto_inicial` decimal(12,2) NOT NULL,
  `saldo_actual` decimal(12,2) NOT NULL,
  `fecha_deposito` date NOT NULL,
  `metodo_pago` enum('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') DEFAULT 'EFECTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seguros`
--

INSERT INTO `seguros` (`id`, `titular_id`, `monto_inicial`, `saldo_actual`, `fecha_deposito`, `metodo_pago`) VALUES
(1, 2, 100000.00, 97100.00, '2025-09-01', 'EFECTIVO');

-- --------------------------------------------------------

--
-- Table structure for table `seguros_beneficiarios`
--

CREATE TABLE `seguros_beneficiarios` (
  `id` int(11) NOT NULL,
  `seguro_id` int(11) NOT NULL,
  `paciente_id` int(11) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seguros_beneficiarios`
--

INSERT INTO `seguros_beneficiarios` (`id`, `seguro_id`, `paciente_id`, `fecha_registro`) VALUES
(1, 1, 3, '2025-09-01 14:20:59');

-- --------------------------------------------------------

--
-- Table structure for table `tipo_pruebas`
--

CREATE TABLE `tipo_pruebas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tipo_pruebas`
--

INSERT INTO `tipo_pruebas` (`id`, `nombre`, `precio`, `fecha_registro`, `id_usuario`) VALUES
(1, 'Gota grusa', 8000.00, '2025-06-12 12:38:09', 1),
(2, 'HEPATITIS B', 9000.00, '2025-06-12 12:39:44', 1),
(3, 'Emograma', 10000.00, '2025-09-12 09:40:44', 1);

-- --------------------------------------------------------

--
-- Table structure for table `unidades_medida`
--

CREATE TABLE `unidades_medida` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `abreviatura` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unidades_medida`
--

INSERT INTO `unidades_medida` (`id`, `nombre`, `abreviatura`) VALUES
(1, 'Miligramo', 'Mg'),
(2, 'Mililitros', 'ML'),
(3, 'gramos', 'g'),
(4, 'bolso', 'cm3');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
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
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre_usuario`, `id_rol`, `password`, `id_personal`, `fecha_registro`) VALUES
(1, 'sirtopola', 1, '$2y$10$tDik4yXSE.O1bGIku8JHKe9NwJ4jZY6iL3AH.8aph/DuUjHcpoL5O', 1, '2025-06-10 17:42:47'),
(2, 'laboratorio', 2, '$2y$10$3IngK68OS2Gzb9A4LVuOMO4ngAa94N6wNv/E0p/WrBI6cQgvg6UCu', 2, '2025-06-16 11:57:31'),
(3, 'doctora', 6, '$2y$10$7RSZBKnEruBgvgrOpaMshewnXBGy2dhWkarTPAtPrb6HY/kcCSdRG', 3, '2025-06-17 11:29:09'),
(4, 'farmacia', 3, '$2y$10$zGw/coZwvIXeA7djhMsP5OM3f2hBfR0dfeesadXLNlu/rGHlpsDWq', 4, '2025-06-25 13:48:12'),
(5, 'alberto', 1, '$2y$10$Ve/JMcV44bveYjdHSeINn.9b.N5nRgnYJsMRAKPRAJvc9fCC5lLd6', 6, '2025-09-10 13:31:49');

-- --------------------------------------------------------

--
-- Table structure for table `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `paciente_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `monto_total` decimal(12,2) NOT NULL,
  `monto_recibido` decimal(12,2) DEFAULT 0.00,
  `cambio_devuelto` decimal(12,2) DEFAULT 0.00,
  `motivo_descuento` varchar(150) DEFAULT NULL,
  `descuento_global` decimal(12,2) DEFAULT 0.00,
  `seguro` tinyint(1) DEFAULT 0,
  `estado_pago` enum('PAGADO','PENDIENTE','PARCIAL') DEFAULT 'PAGADO',
  `metodo_pago` enum('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') DEFAULT 'EFECTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ventas`
--

INSERT INTO `ventas` (`id`, `paciente_id`, `usuario_id`, `fecha`, `monto_total`, `monto_recibido`, `cambio_devuelto`, `motivo_descuento`, `descuento_global`, `seguro`, `estado_pago`, `metodo_pago`) VALUES
(1, 2, 4, '2025-08-29', 1500.00, 2000.00, 500.00, NULL, 0.00, 0, 'PAGADO', 'EFECTIVO'),
(4, 3, 4, '2025-08-29', 5700.00, 6000.00, 300.00, NULL, 0.00, 0, 'PAGADO', 'EFECTIVO'),
(5, 2, 4, '2025-08-29', 6000.00, 6000.00, 0.00, NULL, 0.00, 0, 'PAGADO', 'EFECTIVO'),
(6, 3, 4, '2025-08-29', 3000.00, 4000.00, 1000.00, NULL, 0.00, 0, 'PAGADO', 'EFECTIVO'),
(9, 3, 4, '2025-08-29', 6000.00, 4000.00, 0.00, NULL, 0.00, 0, 'PENDIENTE', 'EFECTIVO'),
(10, 2, 4, '2025-08-29', 3000.00, 2500.00, 0.00, NULL, 0.00, 0, 'PENDIENTE', 'EFECTIVO'),
(16, 4, 1, '2025-12-08', 1500.00, 1500.00, 0.00, NULL, 0.00, NULL, 'PAGADO', 'EFECTIVO'),
(17, 2, 1, '2025-12-08', 700.00, 1000.00, 300.00, NULL, 0.00, NULL, 'PAGADO', 'EFECTIVO'),
(18, 2, 1, '2025-12-08', 2200.00, 0.00, 0.00, NULL, 0.00, 1, 'PAGADO', ''),
(19, 3, 1, '2025-12-08', 700.00, 0.00, 0.00, NULL, 0.00, 1, 'PAGADO', '');

-- --------------------------------------------------------

--
-- Table structure for table `ventas_detalle`
--

CREATE TABLE `ventas_detalle` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL,
  `descuento_unitario` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ventas_detalle`
--

INSERT INTO `ventas_detalle` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_venta`, `descuento_unitario`) VALUES
(1, 1, 1, 1, 1500.00, 0.00),
(2, 4, 1, 4, 0.00, 5.00),
(3, 5, 1, 4, 0.00, 0.00),
(4, 6, 1, 2, 0.00, 0.00),
(7, 9, 1, 4, 1500.00, 0.00),
(8, 10, 1, 2, 1500.00, 0.00),
(9, 16, 1, 1, 1500.00, 0.00),
(10, 17, 2, 2, 350.00, 0.00),
(11, 18, 1, 1, 1500.00, 0.00),
(12, 18, 2, 2, 350.00, 0.00),
(13, 19, 2, 2, 350.00, 0.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analiticas`
--
ALTER TABLE `analiticas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tipo_prueba` (`id_tipo_prueba`),
  ADD KEY `id_consulta` (`id_consulta`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_paciente` (`id_paciente`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `personal_id` (`personal_id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indexes for table `compras_detalle`
--
ALTER TABLE `compras_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `detalle_consulta`
--
ALTER TABLE `detalle_consulta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_consulta` (`id_consulta`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `ingresos`
--
ALTER TABLE `ingresos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_sala` (`id_sala`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indexes for table `movimientos_seguro`
--
ALTER TABLE `movimientos_seguro`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seguro_id` (`seguro_id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `venta_id` (`venta_id`);

--
-- Indexes for table `pacientes`
--
ALTER TABLE `pacientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_analitica` (`id_analitica`),
  ADD KEY `id_tipo_prueba` (`id_tipo_prueba`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `pagos_proveedores`
--
ALTER TABLE `pagos_proveedores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indexes for table `personal`
--
ALTER TABLE `personal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paciente_id` (`paciente_id`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `unidad_id` (`unidad_id`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `recetas`
--
ALTER TABLE `recetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_consulta` (`id_consulta`),
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salas_ingreso`
--
ALTER TABLE `salas_ingreso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `seguros`
--
ALTER TABLE `seguros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `titular_id` (`titular_id`);

--
-- Indexes for table `seguros_beneficiarios`
--
ALTER TABLE `seguros_beneficiarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seguro_id` (`seguro_id`),
  ADD KEY `paciente_id` (`paciente_id`);

--
-- Indexes for table `tipo_pruebas`
--
ALTER TABLE `tipo_pruebas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `unidades_medida`
--
ALTER TABLE `unidades_medida`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_personal` (`id_personal`);

--
-- Indexes for table `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `paciente_id` (`paciente_id`);

--
-- Indexes for table `ventas_detalle`
--
ALTER TABLE `ventas_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analiticas`
--
ALTER TABLE `analiticas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `compras_detalle`
--
ALTER TABLE `compras_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `detalle_consulta`
--
ALTER TABLE `detalle_consulta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `movimientos_seguro`
--
ALTER TABLE `movimientos_seguro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pagos_proveedores`
--
ALTER TABLE `pagos_proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `recetas`
--
ALTER TABLE `recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `salas_ingreso`
--
ALTER TABLE `salas_ingreso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `seguros`
--
ALTER TABLE `seguros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `seguros_beneficiarios`
--
ALTER TABLE `seguros_beneficiarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tipo_pruebas`
--
ALTER TABLE `tipo_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `unidades_medida`
--
ALTER TABLE `unidades_medida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `ventas_detalle`
--
ALTER TABLE `ventas_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analiticas`
--
ALTER TABLE `analiticas`
  ADD CONSTRAINT `analiticas_ibfk_1` FOREIGN KEY (`id_tipo_prueba`) REFERENCES `tipo_pruebas` (`id`),
  ADD CONSTRAINT `analiticas_ibfk_2` FOREIGN KEY (`id_consulta`) REFERENCES `consultas` (`id`),
  ADD CONSTRAINT `analiticas_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `analiticas_ibfk_4` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id`);

--
-- Constraints for table `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`),
  ADD CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Constraints for table `compras_detalle`
--
ALTER TABLE `compras_detalle`
  ADD CONSTRAINT `compras_detalle_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  ADD CONSTRAINT `compras_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Constraints for table `consultas`
--
ALTER TABLE `consultas`
  ADD CONSTRAINT `consultas_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id`),
  ADD CONSTRAINT `consultas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `detalle_consulta`
--
ALTER TABLE `detalle_consulta`
  ADD CONSTRAINT `detalle_consulta_ibfk_1` FOREIGN KEY (`id_consulta`) REFERENCES `consultas` (`id`),
  ADD CONSTRAINT `detalle_consulta_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `ingresos`
--
ALTER TABLE `ingresos`
  ADD CONSTRAINT `ingresos_ibfk_1` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id`),
  ADD CONSTRAINT `ingresos_ibfk_2` FOREIGN KEY (`id_sala`) REFERENCES `salas_ingreso` (`id`),
  ADD CONSTRAINT `ingresos_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Constraints for table `movimientos_seguro`
--
ALTER TABLE `movimientos_seguro`
  ADD CONSTRAINT `movimientos_seguro_ibfk_1` FOREIGN KEY (`seguro_id`) REFERENCES `seguros` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `movimientos_seguro_ibfk_2` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `movimientos_seguro_ibfk_3` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pacientes`
--
ALTER TABLE `pacientes`
  ADD CONSTRAINT `pacientes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_analitica`) REFERENCES `analiticas` (`id`),
  ADD CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`id_tipo_prueba`) REFERENCES `tipo_pruebas` (`id`),
  ADD CONSTRAINT `pagos_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `pagos_proveedores`
--
ALTER TABLE `pagos_proveedores`
  ADD CONSTRAINT `pagos_proveedores_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  ADD CONSTRAINT `pagos_proveedores_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Constraints for table `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `productos_ibfk_2` FOREIGN KEY (`unidad_id`) REFERENCES `unidades_medida` (`id`);

--
-- Constraints for table `recetas`
--
ALTER TABLE `recetas`
  ADD CONSTRAINT `recetas_ibfk_1` FOREIGN KEY (`id_consulta`) REFERENCES `consultas` (`id`),
  ADD CONSTRAINT `recetas_ibfk_2` FOREIGN KEY (`id_paciente`) REFERENCES `pacientes` (`id`),
  ADD CONSTRAINT `recetas_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `salas_ingreso`
--
ALTER TABLE `salas_ingreso`
  ADD CONSTRAINT `salas_ingreso_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `seguros`
--
ALTER TABLE `seguros`
  ADD CONSTRAINT `seguros_ibfk_1` FOREIGN KEY (`titular_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `seguros_beneficiarios`
--
ALTER TABLE `seguros_beneficiarios`
  ADD CONSTRAINT `seguros_beneficiarios_ibfk_1` FOREIGN KEY (`seguro_id`) REFERENCES `seguros` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `seguros_beneficiarios_ibfk_2` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tipo_pruebas`
--
ALTER TABLE `tipo_pruebas`
  ADD CONSTRAINT `tipo_pruebas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id`);

--
-- Constraints for table `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`paciente_id`) REFERENCES `pacientes` (`id`);

--
-- Constraints for table `ventas_detalle`
--
ALTER TABLE `ventas_detalle`
  ADD CONSTRAINT `ventas_detalle_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `ventas_detalle_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
