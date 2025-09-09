-- --------------------------------------------------------
-- Esquema de base de datos normalizado para el consultorio.
-- Se han corregido las relaciones entre tablas y se han añadido
-- las cláusulas ON DELETE y ON UPDATE para asegurar la integridad referencial.
-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Estructura de tabla para la tabla `roles`
--
CREATE TABLE `roles` (
  `id` INT(11) NOT NULL,
  `nombre` VARCHAR(50) NOT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP()
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `personal`
--
CREATE TABLE `personal` (
  `id` INT(11) NOT NULL,
  `nombre` VARCHAR(100) DEFAULT NULL,
  `apellidos` VARCHAR(100) DEFAULT NULL,
  `fecha_nacimiento` DATE DEFAULT NULL,
  `direccion` TEXT DEFAULT NULL,
  `correo` VARCHAR(100) DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `especialidad` VARCHAR(100) DEFAULT NULL,
  `codigo` VARCHAR(50) DEFAULT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP()
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `usuarios`
--
-- Relación: Un usuario se asocia con un registro de personal.
-- ON DELETE RESTRICT: No se permite borrar un registro de personal si hay un usuario asociado.
-- ON UPDATE CASCADE: Si el ID del personal cambia, se actualiza en la tabla de usuarios.
CREATE TABLE `usuarios` (
  `id` INT(11) NOT NULL,
  `nombre_usuario` VARCHAR(50) NOT NULL,
  `id_rol` INT(11) DEFAULT NULL,
  `password` VARCHAR(255) DEFAULT NULL,
  `id_personal` INT(11) DEFAULT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_rol`) REFERENCES `roles`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  FOREIGN KEY (`id_personal`) REFERENCES `personal`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `pacientes`
--
-- Relación: Un paciente es registrado por un usuario.
-- ON DELETE SET NULL: Si un usuario es eliminado, el ID de usuario en los pacientes que registró se establece en NULL.
-- ON UPDATE CASCADE: Si el ID del usuario cambia, se actualiza en la tabla de pacientes.
CREATE TABLE `pacientes` (
  `id` INT(11) NOT NULL,
  `codigo` VARCHAR(50) DEFAULT NULL,
  `nombre` VARCHAR(100) DEFAULT NULL,
  `apellidos` VARCHAR(100) DEFAULT NULL,
  `fecha_nacimiento` DATE DEFAULT NULL,
  `dip` VARCHAR(50) DEFAULT NULL,
  `sexo` VARCHAR(10) DEFAULT NULL,
  `direccion` TEXT DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `telefono` VARCHAR(20) DEFAULT NULL,
  `profesion` VARCHAR(100) DEFAULT NULL,
  `ocupacion` VARCHAR(100) DEFAULT NULL,
  `tutor_nombre` VARCHAR(100) DEFAULT NULL,
  `telefono_tutor` VARCHAR(20) DEFAULT NULL,
  `id_usuario` INT(11) DEFAULT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `consultas`
--
-- Relaciones: Una consulta se asocia a un paciente y a un usuario.
-- ON DELETE RESTRICT: No se puede borrar un paciente si tiene consultas asociadas.
-- ON DELETE SET NULL: Si un usuario es borrado, sus consultas se mantienen pero se pierde el registro de quién la creó.
-- ON UPDATE CASCADE: Si los IDs de paciente o usuario cambian, se actualizan en las consultas.
CREATE TABLE `consultas` (
  `id` INT(11) NOT NULL,
  `motivo_consulta` TEXT DEFAULT NULL,
  `temperatura` FLOAT DEFAULT NULL,
  `control_cada_horas` INT(11) DEFAULT NULL,
  `frecuencia_cardiaca` INT(11) DEFAULT NULL,
  `frecuencia_respiratoria` INT(11) DEFAULT NULL,
  `tension_arterial` VARCHAR(20) DEFAULT NULL,
  `pulso` INT(11) DEFAULT NULL,
  `saturacion_oxigeno` FLOAT DEFAULT NULL,
  `peso_anterior` FLOAT DEFAULT NULL,
  `peso_actual` FLOAT DEFAULT NULL,
  `peso_ideal` FLOAT DEFAULT NULL,
  `imc` FLOAT DEFAULT NULL,
  `id_paciente` INT(11) DEFAULT NULL,
  `id_usuario` INT(11) DEFAULT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `pagado` INT(1) NOT NULL,
  `precio` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_paciente`) REFERENCES `pacientes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `detalle_consulta`
--
-- Relaciones: Los detalles de consulta dependen de una consulta principal y de un usuario.
-- ON DELETE CASCADE: Si una consulta es eliminada, sus detalles también deben serlo.
-- ON UPDATE CASCADE: Si el ID de consulta cambia, se actualiza en los detalles.
-- ON DELETE SET NULL: Si el usuario que registró los detalles es borrado, el campo se establece en NULL.
CREATE TABLE `detalle_consulta` (
  `id` INT(11) NOT NULL,
  `operacion` TEXT DEFAULT NULL,
  `orina` VARCHAR(50) DEFAULT NULL,
  `defeca` VARCHAR(50) DEFAULT NULL,
  `defeca_dias` INT(11) DEFAULT NULL,
  `duerme` VARCHAR(50) DEFAULT NULL,
  `duerme_horas` INT(11) DEFAULT NULL,
  `antecedentes_patologicos` TEXT DEFAULT NULL,
  `alergico` TEXT DEFAULT NULL,
  `antecedentes_familiares` TEXT DEFAULT NULL,
  `antecedentes_conyuge` TEXT DEFAULT NULL,
  `control_signos_vitales` TEXT DEFAULT NULL,
  `id_consulta` INT(11) DEFAULT NULL,
  `id_usuario` INT(11) DEFAULT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_consulta`) REFERENCES `consultas`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `tipo_pruebas`
--
-- Relación: El tipo de prueba es registrado por un usuario.
-- ON DELETE SET NULL: Si un usuario es eliminado, el ID de usuario en los tipos de prueba que registró se establece en NULL.
-- ON UPDATE CASCADE: Si el ID del usuario cambia, se actualiza en la tabla de tipos de prueba.
CREATE TABLE `tipo_pruebas` (
  `id` INT(11) NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `id_usuario` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `analiticas`
--
-- Relaciones: Una analítica se asocia a un tipo de prueba, una consulta y un usuario.
-- NOTA: Se ha eliminado `id_paciente` y `codigo_paciente` ya que esta información se obtiene de la tabla `consultas`.
-- ON DELETE RESTRICT: No se puede borrar una consulta si tiene analíticas asociadas.
-- ON DELETE RESTRICT: No se puede borrar un tipo de prueba si hay analíticas asociadas a ella.
-- ON DELETE SET NULL: Si un usuario es eliminado, el ID de usuario en las analíticas se establece en NULL.
CREATE TABLE `analiticas` (
  `id` INT(11) NOT NULL,
  `resultado` TEXT DEFAULT NULL,
  `estado` VARCHAR(50) DEFAULT NULL,
  `id_tipo_prueba` INT(11) NOT NULL,
  `id_consulta` INT(11) NOT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `id_usuario` INT(11) NOT NULL,
  `pagado` TINYINT(1) DEFAULT 0,
  `valores_refencia` TEXT DEFAULT NULL,
  `tipo_pago` ENUM('EFECTIVO','SEGURO','ADEUDO','SIN PAGAR') DEFAULT 'SIN PAGAR',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_tipo_prueba`) REFERENCES `tipo_pruebas`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`id_consulta`) REFERENCES `consultas`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `pagos`
--
-- Relaciones: Un pago se asocia a una analítica y a un usuario.
-- NOTA: Se ha eliminado `id_tipo_prueba` ya que es redundante, la información se puede obtener a través de `id_analitica`.
-- ON DELETE CASCADE: Si una analítica es eliminada, el registro de pago asociado también se elimina.
-- ON DELETE SET NULL: Si el usuario es borrado, el registro de pago se mantiene pero el campo de usuario se establece en NULL.
CREATE TABLE `pagos` (
  `id` INT(11) NOT NULL,
  `cantidad` DECIMAL(10,2) NOT NULL,
  `id_analitica` INT(11) NOT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `id_usuario` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_analitica`) REFERENCES `analiticas`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `recetas`
--
-- Relaciones: Una receta se asocia a una consulta y a un usuario.
-- NOTA: Se ha eliminado `id_paciente` y `codigo_paciente` ya que esta información se puede obtener de la tabla `consultas`.
-- ON DELETE CASCADE: Si una consulta es eliminada, la receta asociada también se elimina.
-- ON DELETE SET NULL: Si el usuario es borrado, el campo de usuario en la receta se establece en NULL.
CREATE TABLE `recetas` (
  `id` INT(11) NOT NULL,
  `descripcion` TEXT NOT NULL,
  `id_consulta` INT(11) NOT NULL,
  `comentario` TEXT DEFAULT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `id_usuario` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_consulta`) REFERENCES `consultas`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `prestamos`
--
-- Relación: Un préstamo se asocia a un paciente.
-- ON DELETE RESTRICT: No se puede borrar un paciente si tiene préstamos asociados.
CREATE TABLE `prestamos` (
  `id` INT(11) NOT NULL,
  `paciente_id` INT(11) NOT NULL,
  `total` DECIMAL(12,2) NOT NULL,
  `estado` ENUM('PENDIENTE','PARCIAL','PAGADO') DEFAULT 'PENDIENTE',
  `fecha` DATE NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`paciente_id`) REFERENCES `pacientes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `proveedores`
--
CREATE TABLE `proveedores` (
  `id` INT(11) NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `direccion` VARCHAR(150) DEFAULT NULL,
  `telefono` VARCHAR(30) DEFAULT NULL,
  `contacto` VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `compras`
--
-- Relaciones: Una compra se asocia a un proveedor y a un personal.
-- ON DELETE RESTRICT: No se puede borrar un proveedor si tiene compras asociadas.
-- ON DELETE RESTRICT: No se puede borrar un registro de personal si tiene compras asociadas.
CREATE TABLE `compras` (
  `id` INT(11) NOT NULL,
  `proveedor_id` INT(11) DEFAULT NULL,
  `codigo_factura` VARCHAR(100) DEFAULT NULL,
  `personal_id` INT(11) DEFAULT NULL,
  `fecha` DATE NOT NULL,
  `monto_entregado` DECIMAL(12,2) DEFAULT 0.00,
  `monto_gastado` DECIMAL(12,2) DEFAULT 0.00,
  `cambio_devuelto` DECIMAL(12,2) DEFAULT 0.00,
  `monto_pendiente` DECIMAL(12,2) DEFAULT 0.00,
  `total` DECIMAL(12,2) NOT NULL,
  `estado_pago` ENUM('PAGADO','PENDIENTE','PARCIAL') DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`personal_id`) REFERENCES `personal`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `pagos_proveedores`
--
-- Relaciones: Un pago a proveedor se asocia a una compra y a un proveedor.
-- ON DELETE CASCADE: Si una compra es eliminada, sus pagos asociados también deben serlo.
-- ON DELETE RESTRICT: No se puede borrar un proveedor si tiene pagos a su nombre.
CREATE TABLE `pagos_proveedores` (
  `id` INT(11) NOT NULL,
  `compra_id` INT(11) DEFAULT NULL,
  `proveedor_id` INT(11) DEFAULT NULL,
  `monto` DECIMAL(12,2) NOT NULL,
  `fecha` DATE NOT NULL,
  `metodo_pago` ENUM('EFECTIVO','TRANSFERENCIA','TARJETA','OTRO') DEFAULT 'EFECTIVO',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`compra_id`) REFERENCES `compras`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `categorias`
--
CREATE TABLE `categorias` (
  `id` INT(11) NOT NULL,
  `nombre` VARCHAR(50) NOT NULL,
  `descripcion` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `unidades_medida`
--
CREATE TABLE `unidades_medida` (
  `id` INT(11) NOT NULL,
  `nombre` VARCHAR(50) NOT NULL,
  `abreviatura` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `productos`
--
-- Relaciones: Un producto se asocia a una categoría y a una unidad de medida.
-- ON DELETE RESTRICT: No se puede borrar una categoría si tiene productos asociados.
-- ON DELETE RESTRICT: No se puede borrar una unidad de medida si tiene productos asociados.
CREATE TABLE `productos` (
  `id` INT(11) NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `concentracion` VARCHAR(50) DEFAULT NULL,
  `forma_farmaceutica` VARCHAR(50) DEFAULT NULL,
  `presentacion` VARCHAR(100) DEFAULT NULL,
  `categoria_id` INT(11) DEFAULT NULL,
  `unidad_id` INT(11) DEFAULT NULL,
  `precio_unitario` DECIMAL(10,2) DEFAULT NULL,
  `stock_actual` INT(11) DEFAULT 0,
  `stock_minimo` INT(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`categoria_id`) REFERENCES `categorias`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`unidad_id`) REFERENCES `unidades_medida`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `compras_detalle`
--
-- Relaciones: Los detalles de compra dependen de una compra y de un producto.
-- ON DELETE CASCADE: Si una compra se elimina, sus detalles se borran automáticamente.
-- ON DELETE RESTRICT: No se puede borrar un producto si hay un registro de su compra.
CREATE TABLE `compras_detalle` (
  `id` INT(11) NOT NULL,
  `compra_id` INT(11) DEFAULT NULL,
  `producto_id` INT(11) DEFAULT NULL,
  `cantidad` INT(11) NOT NULL,
  `precio_compra` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`compra_id`) REFERENCES `compras`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `ventas`
--
-- Relaciones: Una venta se asocia a un paciente y a un usuario.
-- ON DELETE RESTRICT: No se puede borrar un paciente si tiene ventas asociadas.
-- ON DELETE SET NULL: Si un usuario es eliminado, el ID de usuario en las ventas que realizó se establece en NULL.
CREATE TABLE `ventas` (
  `id` INT(11) NOT NULL,
  `paciente_id` INT(11) DEFAULT NULL,
  `usuario_id` INT(11) DEFAULT NULL,
  `fecha` DATE NOT NULL,
  `monto_total` DECIMAL(12,2) NOT NULL,
  `monto_recibido` DECIMAL(12,2) DEFAULT 0.00,
  `cambio_devuelto` DECIMAL(12,2) DEFAULT 0.00,
  `motivo_descuento` VARCHAR(150) DEFAULT NULL,
  `descuento_global` DECIMAL(12,2) DEFAULT 0.00,
  `seguro` TINYINT(1) DEFAULT 0,
  `estado_pago` ENUM('PAGADO','PENDIENTE','PARCIAL') DEFAULT 'PAGADO',
  `metodo_pago` ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') DEFAULT 'EFECTIVO',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`paciente_id`) REFERENCES `pacientes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `ventas_detalle`
--
-- Relaciones: Los detalles de venta dependen de una venta y de un producto.
-- ON DELETE CASCADE: Si una venta es eliminada, sus detalles se borran automáticamente.
-- ON DELETE RESTRICT: No se puede borrar un producto si hay un registro de su venta.
CREATE TABLE `ventas_detalle` (
  `id` INT(11) NOT NULL,
  `venta_id` INT(11) DEFAULT NULL,
  `producto_id` INT(11) DEFAULT NULL,
  `cantidad` INT(11) NOT NULL,
  `precio_venta` DECIMAL(10,2) NOT NULL,
  `descuento_unitario` DECIMAL(12,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`venta_id`) REFERENCES `ventas`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `salas_ingreso`
--
-- Relación: Una sala de ingreso es registrada por un usuario.
-- ON DELETE SET NULL: Si un usuario es eliminado, se pierde el registro de quién creó la sala, pero la sala se mantiene.
CREATE TABLE `salas_ingreso` (
  `id` INT(11) NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `id_usuario` INT(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `ingresos`
--
-- Relaciones: Un ingreso se asocia a un paciente, una sala de ingreso y un usuario.
-- ON DELETE RESTRICT: No se puede borrar un paciente si tiene registros de ingreso.
-- ON DELETE RESTRICT: No se puede borrar una sala si tiene ingresos asociados.
-- ON DELETE SET NULL: Si el usuario es borrado, se mantiene el ingreso pero se pierde el registro de quién lo registró.
CREATE TABLE `ingresos` (
  `id` INT(11) NOT NULL,
  `id_paciente` INT(11) NOT NULL,
  `id_sala` INT(11) NOT NULL,
  `fecha_ingreso` DATETIME NOT NULL,
  `fecha_alta` DATETIME DEFAULT NULL,
  `token` VARCHAR(100) DEFAULT NULL,
  `fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `id_usuario` INT(11) NOT NULL,
  `numero_cama` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  FOREIGN KEY (`id_paciente`) REFERENCES `pacientes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`id_sala`) REFERENCES `salas_ingreso`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`id_usuario`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Estructura de tabla para la tabla `seguros`
--
-- Relación: Un seguro se asocia a un titular (paciente).
-- ON DELETE RESTRICT: No se puede borrar un paciente si tiene un seguro asociado.
CREATE TABLE `seguros` (
  `id` INT(11) NOT NULL,
  `titular_id` INT(11) NOT NULL,
  `monto_inicial` DECIMAL(12,2) NOT NULL,
  `saldo_actual` DECIMAL(12,2) NOT NULL,
  `fecha_deposito` DATE NOT NULL,
  `metodo_pago` ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') DEFAULT 'EFECTIVO',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`titular_id`) REFERENCES `pacientes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `seguros_beneficiarios`
--
-- Relaciones: Los beneficiarios del seguro dependen de un seguro y de un paciente.
-- ON DELETE CASCADE: Si el seguro es eliminado, sus beneficiarios también se eliminan.
-- ON DELETE RESTRICT: No se puede borrar un paciente si es beneficiario de un seguro.
CREATE TABLE `seguros_beneficiarios` (
  `id` INT(11) NOT NULL,
  `seguro_id` INT(11) NOT NULL,
  `paciente_id` INT(11) NOT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`seguro_id`) REFERENCES `seguros`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`paciente_id`) REFERENCES `pacientes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `movimientos_seguro`
--
-- Relaciones: Los movimientos de seguro se asocian a un seguro, a un paciente y opcionalmente a una venta.
-- ON DELETE CASCADE: Si un seguro es eliminado, sus movimientos se eliminan.
-- ON DELETE RESTRICT: No se puede borrar un paciente si tiene movimientos de seguro.
-- ON DELETE SET NULL: Si una venta es eliminada, el registro de movimiento de seguro se mantiene, pero el ID de venta se establece en NULL.
CREATE TABLE `movimientos_seguro` (
  `id` INT(11) NOT NULL,
  `seguro_id` INT(11) NOT NULL,
  `paciente_id` INT(11) NOT NULL,
  `venta_id` INT(11) DEFAULT NULL,
  `tipo` ENUM('CREDITO','DEBITO') NOT NULL,
  `monto` DECIMAL(12,2) NOT NULL,
  `fecha` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `descripcion` VARCHAR(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`seguro_id`) REFERENCES `seguros`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`paciente_id`) REFERENCES `pacientes`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (`venta_id`) REFERENCES `ventas`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--
-- Relación: Un movimiento de inventario se asocia a un producto.
-- ON DELETE RESTRICT: No se puede borrar un producto si tiene movimientos de inventario.
CREATE TABLE `movimientos_inventario` (
  `id` INT(11) NOT NULL,
  `producto_id` INT(11) DEFAULT NULL,
  `tipo` ENUM('ENTRADA','SALIDA') NOT NULL,
  `referencia` VARCHAR(50) DEFAULT NULL,
  `cantidad` INT(11) NOT NULL,
  `fecha` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=INNODB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--
ALTER TABLE `analiticas`
  ADD KEY `id_tipo_prueba` (`id_tipo_prueba`),
  ADD KEY `id_consulta` (`id_consulta`),
  ADD KEY `id_usuario` (`id_usuario`);

ALTER TABLE `compras`
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `personal_id` (`personal_id`);

ALTER TABLE `compras_detalle`
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `producto_id` (`producto_id`);

ALTER TABLE `consultas`
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_usuario` (`id_usuario`);

ALTER TABLE `detalle_consulta`
  ADD KEY `id_consulta` (`id_consulta`),
  ADD KEY `id_usuario` (`id_usuario`);

ALTER TABLE `ingresos`
  ADD KEY `id_paciente` (`id_paciente`),
  ADD KEY `id_sala` (`id_sala`),
  ADD KEY `id_usuario` (`id_usuario`);

ALTER TABLE `movimientos_inventario`
  ADD KEY `producto_id` (`producto_id`);

ALTER TABLE `movimientos_seguro`
  ADD KEY `seguro_id` (`seguro_id`),
  ADD KEY `paciente_id` (`paciente_id`),
  ADD KEY `venta_id` (`venta_id`);

ALTER TABLE `pacientes`
  ADD KEY `id_usuario` (`id_usuario`);

ALTER TABLE `pagos`
  ADD KEY `id_analitica` (`id_analitica`),
  ADD KEY `id_usuario` (`id_usuario`);

ALTER TABLE `pagos_proveedores`
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

ALTER TABLE `prestamos`
  ADD KEY `paciente_id` (`paciente_id`);

ALTER TABLE `productos`
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `unidad_id` (`unidad_id`);

ALTER TABLE `recetas`
  ADD KEY `id_consulta` (`id_consulta`),
  ADD KEY `id_usuario` (`id_usuario`);

ALTER TABLE `salas_ingreso`
  ADD KEY `id_usuario` (`id_usuario`);

ALTER TABLE `seguros`
  ADD KEY `titular_id` (`titular_id`);

ALTER TABLE `seguros_beneficiarios`
  ADD KEY `seguro_id` (`seguro_id`),
  ADD KEY `paciente_id` (`paciente_id`);

ALTER TABLE `tipo_pruebas`
  ADD KEY `id_usuario` (`id_usuario`);

ALTER TABLE `usuarios`
  ADD KEY `id_rol` (`id_rol`),
  ADD KEY `id_personal` (`id_personal`);

ALTER TABLE `ventas`
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `paciente_id` (`paciente_id`);

ALTER TABLE `ventas_detalle`
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--
ALTER TABLE `analiticas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `compras_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `consultas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `detalle_consulta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ingresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `movimientos_seguro`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `pacientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `pagos_proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `personal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `recetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `salas_ingreso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `seguros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `seguros_beneficiarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tipo_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `unidades_medida`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ventas_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;