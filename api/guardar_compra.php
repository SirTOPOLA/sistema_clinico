<?php
session_start();
require '../config/conexion.php'; // Incluye tu archivo de conexión a la base de datos
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction(); // Inicia una transacción para asegurar la integridad de los datos

        // 1. Validar y sanitizar los datos de la compra principal
        $proveedor_id = filter_input(INPUT_POST, 'proveedor_id', FILTER_VALIDATE_INT);
        $personal_id = filter_input(INPUT_POST, 'personal_id', FILTER_VALIDATE_INT);
        $fecha = filter_input(INPUT_POST, 'fecha', FILTER_SANITIZE_STRING); // Validar formato de fecha después

        $codigo_factura = null;
        if (isset($_POST['codigo_factura']) && !empty($_POST['codigo_factura'])) {
            $codigo_factura = filter_input(INPUT_POST, 'codigo_factura', FILTER_SANITIZE_STRING);
            // Considerar una validación más estricta para el formato del código de factura
        } else {
            // Generar un código de factura profesional si está vacío o es nulo
            $codigo_factura = 'FAC-' . date('Ymd') . '-' . substr(uniqid(), -8); // Ejemplo: FAC-20250826-f7a3f5a0e9
        }

        $estado_pago = filter_input(INPUT_POST, 'estado_pago', FILTER_SANITIZE_STRING);
        $monto_entregado = filter_input(INPUT_POST, 'monto_entregado', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

        // Validación básica
        if (!$proveedor_id || !$personal_id || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha) || !in_array($estado_pago, ['PAGADO', 'PENDIENTE', 'PARCIAL'])) {
            $_SESSION['error'] = "Datos de compra inválidos o incompletos.";
            $pdo->rollBack();
            header("Location: ../index.php?vista=contabilidad");
            exit();
        }

        // 2. Procesar los productos y calcular el total de la compra
        $productos_post = $_POST['productos'] ?? [];
        $total_compra = 0;
        $detalles_productos = [];

        if (empty($productos_post)) {
            $_SESSION['error'] = "Debe agregar al menos un producto a la compra.";
            $pdo->rollBack();
            header("Location: ../index.php?vista=contabilidad");
            exit();
        }

        foreach ($productos_post as $index => $prod) {
            $producto_id = filter_var($prod['id'], FILTER_VALIDATE_INT);
            $cantidad = filter_var($prod['cantidad'], FILTER_VALIDATE_INT);
            $precio_compra = filter_var($prod['precio'], FILTER_VALIDATE_FLOAT);
            $precio_venta_unitario = filter_var($prod['precio_venta'], FILTER_VALIDATE_FLOAT); 

            if (!$producto_id || $cantidad <= 0 || $precio_compra < 0 || $precio_venta_unitario < 0) { 
                $_SESSION['error'] = "Datos de producto inválidos en la fila " . ($index + 1);
                $pdo->rollBack();
                header("Location: ../index.php?vista=contabilidad");
                exit();
            }

            $total_compra += ($cantidad * $precio_compra);
            $detalles_productos[] = [
                'producto_id' => $producto_id,
                'cantidad' => $cantidad,
                'precio_compra' => $precio_compra,
                'precio_venta_unitario' => $precio_venta_unitario 
            ];
        }

        // 3. Calcular montos financieros
        $monto_gastado = $total_compra;
        $cambio_devuelto = 0;
        $monto_pendiente = 0;

        if ($estado_pago === 'PAGADO') {
            $monto_entregado = $monto_entregado ?? $total_compra; // Si es pagado, y no se dio monto, asumir total
            if ($monto_entregado < $total_compra) {
                $_SESSION['error'] = "El monto entregado es menor que el total de la compra para un estado 'PAGADO'.";
                $pdo->rollBack();
                header("Location: ../index.php?vista=contabilidad");
                exit();
            }
            $cambio_devuelto = $monto_entregado - $total_compra;
        } elseif ($estado_pago === 'PARCIAL') {
            $monto_entregado = $monto_entregado ?? 0;
            if ($monto_entregado >= $total_compra) {
                $_SESSION['error'] = "El monto entregado es igual o mayor que el total de la compra para un estado 'PARCIAL'.";
                $pdo->rollBack();
                header("Location: ../index.php?vista=contabilidad");
                exit();
            }
            $monto_pendiente = $total_compra - $monto_entregado;
        } else { // PENDIENTE
            $monto_entregado = 0;
            $monto_pendiente = $total_compra;
        }


        // 4. Insertar la compra en la tabla `compras`
        $stmt_compra = $pdo->prepare("INSERT INTO compras (codigo_factura, proveedor_id, personal_id, fecha, monto_entregado, monto_gastado, cambio_devuelto, monto_pendiente, total, estado_pago) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_compra->execute([
            $codigo_factura,
            $proveedor_id,
            $personal_id,
            $fecha,
            $monto_entregado,
            $monto_gastado,
            $cambio_devuelto,
            $monto_pendiente,
            $total_compra,
            $estado_pago
        ]);
        $compra_id = $pdo->lastInsertId(); // Obtiene el ID de la compra recién insertada

        // 5. Insertar los detalles de la compra, actualizar el stock y el precio_unitario del producto
        $stmt_detalle = $pdo->prepare("INSERT INTO compras_detalle (compra_id, producto_id, cantidad, precio_compra) VALUES (?, ?, ?, ?)");
        $stmt_stock = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?");
        $stmt_update_precio_unitario = $pdo->prepare("UPDATE productos SET precio_unitario = ? WHERE id = ?");


        foreach ($detalles_productos as $detalle) {
            $stmt_detalle->execute([
                $compra_id,
                $detalle['producto_id'],
                $detalle['cantidad'],
                $detalle['precio_compra']
            ]);
            $stmt_stock->execute([$detalle['cantidad'], $detalle['producto_id']]);
            $stmt_update_precio_unitario->execute([$detalle['precio_venta_unitario'], $detalle['producto_id']]);
        }

        $pdo->commit(); // Confirma la transacción
        $_SESSION['success'] = "Compra registrada exitosamente con ID: " . $compra_id;

    } catch (PDOException $e) {
        $pdo->rollBack(); // Revierte la transacción en caso de error
        $_SESSION['error'] = "Error al registrar la compra: " . $e->getMessage();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error inesperado: " . $e->getMessage();
    }

    header("Location: ../index.php?vista=contabilidad");
    exit();
} else {
    // Si no es una solicitud POST, redirigir o mostrar un error
    $_SESSION['error'] = "Acceso no autorizado.";
    header("Location: ../index.php?vista=contabilidad");
    exit();
}