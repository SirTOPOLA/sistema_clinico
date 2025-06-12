-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2025 at 10:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

drop Database if exists consultorio;
CREATE Database consultorio;
use consultorio;

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
  `pagado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(1, 'Jesus Crispin', 'Topola Boñaho', '1997-06-30', 'Ela Nguema', 'sir@gmail.com', '551718822', 'Programador', 'fc123', '2025-06-10 17:23:31', 1);

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
(1, 'Administrador', '2025-06-10 17:26:17');

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
(1, 'admin', 1, '$2y$10$tDik4yXSE.O1bGIku8JHKe9NwJ4jZY6iL3AH.8aph/DuUjHcpoL5O', 1, '2025-06-10 17:42:47');

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
-- Indexes for table `personal`
--
ALTER TABLE `personal`
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
-- Indexes for table `tipo_pruebas`
--
ALTER TABLE `tipo_pruebas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_personal` (`id_personal`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analiticas`
--
ALTER TABLE `analiticas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consultas`
--
ALTER TABLE `consultas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detalle_consulta`
--
ALTER TABLE `detalle_consulta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pacientes`
--
ALTER TABLE `pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal`
--
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `recetas`
--
ALTER TABLE `recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `salas_ingreso`
--
ALTER TABLE `salas_ingreso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tipo_pruebas`
--
ALTER TABLE `tipo_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
