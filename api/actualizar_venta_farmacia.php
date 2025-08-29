<?php
require_once '../config/conexion.php';
session_start();

// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?vista=ventas_farmacia');
    exit;
}

try {
    $pdo->beginTransaction();

    $venta_id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $paciente_id = filter_input(INPUT_POST, 'paciente_id', FILTER_SANITIZE_NUMBER_INT);
    $usuario_id = filter_input(INPUT_POST, 'id_usuario', FILTER_SANITIZE_NUMBER_INT);
    $fecha = filter_input(INPUT_POST, 'fecha', FILTER_SANITIZE_STRING);
    $monto_total_nuevo = filter_input(INPUT_POST, 'monto_total', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $monto_recibido_nuevo = filter_input(INPUT_POST, 'monto_recibido', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $metodo_pago_nuevo = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_STRING);
    $estado_pago_nuevo = filter_input(INPUT_POST, 'estado_pago', FILTER_SANITIZE_STRING);
    $seguro_nuevo = filter_input(INPUT_POST, 'seguro', FILTER_SANITIZE_NUMBER_INT) ?? 0;
    $productos_nuevos = $_POST['productos'] ?? [];

    if (!$venta_id || !$paciente_id || !$usuario_id || !$fecha || !$monto_total_nuevo) {
        throw new Exception("Datos de venta incompletos para actualizar.");
    }

    // 1. Obtener los datos de la venta original
    $sql_original = "SELECT monto_total, seguro FROM ventas WHERE id = ?";
    $stmt_original = $pdo->prepare($sql_original);
    $stmt_original->execute([$venta_id]);
    $venta_original = $stmt_original->fetch(PDO::FETCH_ASSOC);

    if (!$venta_original) {
        throw new Exception("Venta no encontrada.");
    }
    $monto_total_original = $venta_original['monto_total'];
    $seguro_original = $venta_original['seguro'];

    // 2. Lógica para manejar cambios en el seguro
    if ($seguro_original == 1) {
        // Reembolsar el monto original al seguro antes de aplicar el nuevo
        $sql_seguro_reembolso = "SELECT s.id, s.saldo_actual
                               FROM seguros s LEFT JOIN seguros_beneficiarios sb ON s.id = sb.seguro_id
                               WHERE s.titular_id = ? OR sb.paciente_id = ?";
        $stmt_seguro_reembolso = $pdo->prepare($sql_seguro_reembolso);
        $stmt_seguro_reembolso->execute([$paciente_id, $paciente_id]);
        $seguro_data = $stmt_seguro_reembolso->fetch(PDO::FETCH_ASSOC);

        if ($seguro_data) {
            $nuevo_saldo = $seguro_data['saldo_actual'] + $monto_total_original;
            $sql_update_reembolso = "UPDATE seguros SET saldo_actual = ? WHERE id = ?";
            $stmt_update_reembolso = $pdo->prepare($sql_update_reembolso);
            $stmt_update_reembolso->execute([$nuevo_saldo, $seguro_data['id']]);

            // Registrar el movimiento de reembolso
            $sql_movimiento_reembolso = "INSERT INTO movimientos_seguro (seguro_id, paciente_id, venta_id, tipo, monto, descripcion) VALUES (?, ?, ?, 'CREDITO', ?, 'Reembolso por edición de venta')";
            $stmt_movimiento_reembolso = $pdo->prepare($sql_movimiento_reembolso);
            $stmt_movimiento_reembolso->execute([$seguro_data['id'], $paciente_id, $venta_id, $monto_total_original]);
        }
    }

    // 3. Lógica para aplicar el nuevo estado del seguro
    $metodo_pago_final = $metodo_pago_nuevo;
    $monto_recibido_final = $monto_recibido_nuevo;
    $cambio_devuelto_final = $monto_recibido_nuevo - $monto_total_nuevo;
    $estado_pago_final = $estado_pago_nuevo;

    if ($seguro_nuevo == 1) {
        // Verificar y debitar el nuevo monto del seguro
        $sql_seguro_nuevo = "SELECT s.id, s.saldo_actual
                            FROM seguros s LEFT JOIN seguros_beneficiarios sb ON s.id = sb.seguro_id
                            WHERE s.titular_id = ? OR sb.paciente_id = ?";
        $stmt_seguro_nuevo = $pdo->prepare($sql_seguro_nuevo);
        $stmt_seguro_nuevo->execute([$paciente_id, $paciente_id]);
        $seguro_data_nuevo = $stmt_seguro_nuevo->fetch(PDO::FETCH_ASSOC);

        if (!$seguro_data_nuevo || $seguro_data_nuevo['saldo_actual'] < $monto_total_nuevo) {
            $_SESSION['error'] = "Saldo insuficiente en el seguro del paciente o paciente sin seguro.";
            throw new Exception("No hay saldo suficiente para la nueva venta.");
        }

        $nuevo_saldo = $seguro_data_nuevo['saldo_actual'] - $monto_total_nuevo;
        $sql_update_saldo = "UPDATE seguros SET saldo_actual = ? WHERE id = ?";
        $stmt_update_saldo = $pdo->prepare($sql_update_saldo);
        $stmt_update_saldo->execute([$nuevo_saldo, $seguro_data_nuevo['id']]);

        // Registrar el nuevo movimiento
        $sql_movimiento = "INSERT INTO movimientos_seguro (seguro_id, paciente_id, venta_id, tipo, monto, descripcion) VALUES (?, ?, ?, 'DEBITO', ?, 'Consumo por edición de venta')";
        $stmt_movimiento = $pdo->prepare($sql_movimiento);
        $stmt_movimiento->execute([$seguro_data_nuevo['id'], $paciente_id, $venta_id, $monto_total_nuevo]);

        $metodo_pago_final = 'SEGURO';
        $estado_pago_final = 'PAGADO';
        $monto_recibido_final = 0;
        $cambio_devuelto_final = 0;
    }
    
    // 4. Actualizar la venta principal
    $sql_update_venta = "UPDATE ventas SET paciente_id = ?, usuario_id = ?, fecha = ?, monto_total = ?, monto_recibido = ?, cambio_devuelto = ?, seguro = ?, estado_pago = ?, metodo_pago = ? WHERE id = ?";
    $stmt_update_venta = $pdo->prepare($sql_update_venta);
    $stmt_update_venta->execute([
        $paciente_id,
        $usuario_id,
        $fecha,
        $monto_total_nuevo,
        $monto_recibido_final,
        $cambio_devuelto_final,
        $seguro_nuevo,
        $estado_pago_final,
        $metodo_pago_final,
        $venta_id
    ]);

    // 5. Eliminar los detalles de venta anteriores
    $sql_delete_detalle = "DELETE FROM ventas_detalle WHERE venta_id = ?";
    $stmt_delete_detalle = $pdo->prepare($sql_delete_detalle);
    $stmt_delete_detalle->execute([$venta_id]);

    // 6. Insertar los nuevos detalles de la venta
    $sql_detalle = "INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_venta, descuento_unitario) VALUES (?, ?, ?, ?, ?)";
    $stmt_detalle = $pdo->prepare($sql_detalle);
    foreach ($productos_nuevos as $producto) {
        $stmt_detalle->execute([
            $venta_id,
            $producto['id'],
            $producto['cantidad'],
            $producto['precio'],
            $producto['descuento'] ?? 0
        ]);
    }

    $pdo->commit();
    $_SESSION['success'] = "Venta actualizada exitosamente.";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = $_SESSION['error'] ?? "Error al actualizar la venta: " . $e->getMessage();
}

header('Location: ../index.php?vista=ventas_farmacia');
exit;
