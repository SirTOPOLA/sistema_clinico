<?php
session_start();
require '../config/conexion.php'; // Incluye tu archivo de conexión a la base de datos
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction(); // Inicia una transacción

        // 1. Validar y sanitizar datos principales
        $compra_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $proveedor_id = filter_input(INPUT_POST, 'proveedor_id', FILTER_VALIDATE_INT);
        $personal_id = filter_input(INPUT_POST, 'personal_id', FILTER_VALIDATE_INT);
        $fecha = filter_input(INPUT_POST, 'fecha', FILTER_SANITIZE_STRING);

        $codigo_factura = null;
        if (isset($_POST['codigo_factura']) && !empty($_POST['codigo_factura'])) {
            $codigo_factura = filter_input(INPUT_POST, 'codigo_factura', FILTER_SANITIZE_STRING);
        } else {
            // Nuevo: Generar un código de factura profesional si está vacío o es nulo
            $codigo_factura = 'FAC-' . date('Ymd') . '-' . substr(uniqid(), -8); // Ejemplo: FAC-20250826-f7a3f5a0e9
        }


        $estado_pago = filter_input(INPUT_POST, 'estado_pago', FILTER_SANITIZE_STRING);
        $monto_entregado = filter_input(INPUT_POST, 'monto_entregado', FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

        if (!$compra_id || !$proveedor_id || !$personal_id || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha) || !in_array($estado_pago, ['PAGADO', 'PENDIENTE', 'PARCIAL'])) {
            $_SESSION['error'] = "Datos de compra inválidos o incompletos para actualizar.";
            $pdo->rollBack();
            header("Location: ../index.php?vista=compras_farmacia");
            exit();
        }

        // 2. Revertir el stock de los productos de la compra original (antes de eliminar detalles)
        $stmt_old_details = $pdo->prepare("SELECT producto_id, cantidad FROM compras_detalle WHERE compra_id = ?");
        $stmt_old_details->execute([$compra_id]);
        $old_details = $stmt_old_details->fetchAll(PDO::FETCH_ASSOC);

        $stmt_revert_stock = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
        foreach ($old_details as $detail) {
            $stmt_revert_stock->execute([$detail['cantidad'], $detail['producto_id']]);
        }

        // 3. Eliminar los detalles de la compra antiguos
        $stmt_delete_details = $pdo->prepare("DELETE FROM compras_detalle WHERE compra_id = ?");
        $stmt_delete_details->execute([$compra_id]);

        // 4. Procesar los nuevos productos y calcular el nuevo total
        $productos_post = $_POST['productos'] ?? [];
        $total_compra = 0;
        $detalles_productos = [];

        if (empty($productos_post)) {
            $_SESSION['error'] = "Debe agregar al menos un producto a la compra.";
            $pdo->rollBack();
            header("Location: ../index.php?vista=compras_farmacia");
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
                header("Location: ../index.php?vista=compras_farmacia");
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

        // 5. Calcular los montos financieros actualizados
        $monto_gastado = $total_compra;
        $cambio_devuelto = 0;
        $monto_pendiente = 0;

        if ($estado_pago === 'PAGADO') {
            $monto_entregado = $monto_entregado ?? $total_compra;
            if ($monto_entregado < $total_compra) {
                $_SESSION['error'] = "El monto entregado es menor que el total de la compra para un estado 'PAGADO'.";
                $pdo->rollBack();
                header("Location: ../index.php?vista=compras_farmacia");
                exit();
            }
            $cambio_devuelto = $monto_entregado - $total_compra;
        } elseif ($estado_pago === 'PARCIAL') {
            $monto_entregado = $monto_entregado ?? 0;
            if ($monto_entregado >= $total_compra) {
                $_SESSION['error'] = "El monto entregado es igual o mayor que el total de la compra para un estado 'PARCIAL'.";
                $pdo->rollBack();
                header("Location: ../index.php?vista=compras_farmacia");
                exit();
            }
            $monto_pendiente = $total_compra - $monto_entregado;
        } else { // PENDIENTE
            $monto_entregado = 0;
            $monto_pendiente = $total_compra;
        }

        // 6. Actualizar la compra en la tabla `compras`
        $stmt_update_compra = $pdo->prepare("UPDATE compras SET codigo_factura = ?, proveedor_id = ?, personal_id = ?, fecha = ?, monto_entregado = ?, monto_gastado = ?, cambio_devuelto = ?, monto_pendiente = ?, total = ?, estado_pago = ? WHERE id = ?");
        $stmt_update_compra->execute([
            $codigo_factura,
            $proveedor_id,
            $personal_id,
            $fecha,
            $monto_entregado,
            $monto_gastado,
            $cambio_devuelto,
            $monto_pendiente,
            $total_compra,
            $estado_pago,
            $compra_id
        ]);

        // 7. Insertar los nuevos detalles de la compra, actualizar el stock y el precio_unitario del producto
        $stmt_insert_detalle = $pdo->prepare("INSERT INTO compras_detalle (compra_id, producto_id, cantidad, precio_compra) VALUES (?, ?, ?, ?)");
        $stmt_update_stock = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?");
        $stmt_update_precio_unitario = $pdo->prepare("UPDATE productos SET precio_unitario = ? WHERE id = ?");


        foreach ($detalles_productos as $detalle) {
            $stmt_insert_detalle->execute([
                $compra_id,
                $detalle['producto_id'],
                $detalle['cantidad'],
                $detalle['precio_compra']
            ]);
            $stmt_update_stock->execute([$detalle['cantidad'], $detalle['producto_id']]);
            $stmt_update_precio_unitario->execute([$detalle['precio_venta_unitario'], $detalle['producto_id']]);
        }

        $pdo->commit(); // Confirma la transacción
        $_SESSION['success'] = "Compra ID: " . $compra_id . " actualizada exitosamente.";

    } catch (PDOException $e) {
        $pdo->rollBack(); // Revierte la transacción
        $_SESSION['error'] = "Error al actualizar la compra: " . $e->getMessage();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error inesperado: " . $e->getMessage();
    }

    header("Location: ../index.php?vista=compras_farmacia");
    exit();
} else {
    $_SESSION['error'] = "Acceso no autorizado.";
    header("Location: ../index.php?vista=compras_farmacia");
    exit();
}
