<?php
/**
 * Script para procesar y guardar una nueva venta de farmacia.
 *
 * Este script se encarga de:
 * 1. Validar la solicitud y los datos de entrada.
 * 2. Iniciar una transacción de base de datos para garantizar la atomicidad.
 * 3. Procesar la lógica de pago (efectivo vs. seguro).
 * 4. Actualizar el saldo del seguro si aplica.
 * 5. Verificar y decrementar el stock de cada producto.
 * 6. Insertar la venta principal y sus detalles en la base de datos.
 * 7. Manejar errores con un rollback de la transacción.
 */

// Se requiere el archivo de conexión a la base de datos
require_once '../config/conexion.php';
// Se inicia la sesión para manejar mensajes de éxito o error
session_start();

// Habilitar la visualización de errores para depuración (deshabilitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirigir si la solicitud no es POST para evitar accesos directos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?vista=ventas_farmacia');
    exit;
}

try {
    // Iniciar una transacción para asegurar que todas las operaciones se completen
    // o se reviertan si algo falla.
    $pdo->beginTransaction();

    // Sanitizar y validar los datos de entrada
    // Se utiliza FILTER_SANITIZE_NUMBER_INT y FILTER_SANITIZE_NUMBER_FLOAT
    // para asegurar que los datos numéricos sean seguros.
    $paciente_id = filter_input(INPUT_POST, 'paciente_id', FILTER_SANITIZE_NUMBER_INT);
    $usuario_id = filter_input(INPUT_POST, 'id_usuario', FILTER_SANITIZE_NUMBER_INT);
    $fecha = filter_input(INPUT_POST, 'fecha', FILTER_SANITIZE_STRING);
    $monto_total = filter_input(INPUT_POST, 'monto_total', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $monto_recibido = filter_input(INPUT_POST, 'monto_recibido', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $metodo_pago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_STRING);
    $estado_pago = filter_input(INPUT_POST, 'estado_pago', FILTER_SANITIZE_STRING);
    $seguro = filter_input(INPUT_POST, 'seguro', FILTER_SANITIZE_NUMBER_INT) ?? 0;
    $productos = $_POST['productos'] ?? [];

    // Validar que los datos mínimos de la venta estén presentes
    if (empty($paciente_id) || empty($usuario_id) || empty($fecha) || empty($monto_total) || empty($productos)) {
        throw new Exception("Datos de venta incompletos. Verifique paciente, usuario, fecha, monto o productos.");
    }

    // Convertir el monto total a un número flotante para evitar problemas de precisión.
    $monto_total = (float) $monto_total;

    // Lógica para el seguro o pago regular
    if ($seguro == 1) {
        $metodo_pago_final = 'SEGURO';
        $estado_pago_final = 'PAGADO';
        $monto_recibido_final = 0.0;
        $cambio_devuelto_final = 0.0;

        // 1. Verificar si el paciente tiene un seguro y obtener el saldo actual
        $sql_seguro = "SELECT s.id, s.saldo_actual
                       FROM seguros s
                       LEFT JOIN seguros_beneficiarios sb ON s.id = sb.seguro_id
                       WHERE s.titular_id = :paciente_id OR sb.paciente_id = :paciente_id_beneficiario";
        $stmt_seguro = $pdo->prepare($sql_seguro);
        // Usar parámetros con nombre para evitar duplicar el mismo valor en execute
        $stmt_seguro->execute([':paciente_id' => $paciente_id, ':paciente_id_beneficiario' => $paciente_id]);
        $seguro_data = $stmt_seguro->fetch(PDO::FETCH_ASSOC);

        if (!$seguro_data) {
            $_SESSION['error'] = "El paciente seleccionado no tiene un seguro asociado.";
            throw new Exception("Paciente sin seguro.");
        }

        $saldo_actual = (float) $seguro_data['saldo_actual'];
        $seguro_id = (int) $seguro_data['id'];

        // 2. Verificar si el saldo es suficiente
        if ($saldo_actual < $monto_total) {
            $_SESSION['error'] = "Saldo insuficiente en el seguro del paciente.";
            throw new Exception("Saldo insuficiente.");
        }

        // 3. Actualizar el saldo del seguro
        $nuevo_saldo = $saldo_actual - $monto_total;
        $sql_update_saldo = "UPDATE seguros SET saldo_actual = :nuevo_saldo WHERE id = :seguro_id";
        $stmt_update_saldo = $pdo->prepare($sql_update_saldo);
        $stmt_update_saldo->execute([':nuevo_saldo' => $nuevo_saldo, ':seguro_id' => $seguro_id]);

        // 4. Registrar el movimiento de débito
        $sql_movimiento = "INSERT INTO movimientos_seguro (seguro_id, paciente_id, tipo, monto, descripcion) 
                           VALUES (:seguro_id, :paciente_id, 'DEBITO', :monto, 'Consumo en farmacia')";
        $stmt_movimiento = $pdo->prepare($sql_movimiento);
        $stmt_movimiento->execute([':seguro_id' => $seguro_id, ':paciente_id' => $paciente_id, ':monto' => $monto_total]);

    } else {
        // Lógica para ventas sin seguro (pago regular)
        // Se añaden validaciones para los montos de pago.
        $monto_recibido = (float) $monto_recibido;
        if ($monto_recibido < $monto_total) {
            $estado_pago_final = 'PENDIENTE';
        } else {
            // Se asegura que si el monto recibido es igual o mayor, el estado sea PAGADO
            $estado_pago_final = 'PAGADO';
        }
        $metodo_pago_final = $metodo_pago;
        $cambio_devuelto_final = $monto_recibido - $monto_total;
        $monto_recibido_final = $monto_recibido;
    }

    // --- Lógica de inventario (NUEVO) ---
    // Recorrer los productos para verificar el stock y preparar la actualización.
    $sql_stock_update = "UPDATE productos SET stock_actual = stock_actual - :cantidad WHERE id = :producto_id AND stock_actual >= :cantidad_check";
    $stmt_stock = $pdo->prepare($sql_stock_update);

    foreach ($productos as $producto) {
        $producto_id = filter_var($producto['id'], FILTER_SANITIZE_NUMBER_INT);
        $cantidad = filter_var($producto['cantidad'], FILTER_SANITIZE_NUMBER_INT);

        if (!$producto_id || !$cantidad) {
            throw new Exception("Datos de producto incompletos o inválidos.");
        }

        // Ejecutar la actualización con una condición para evitar vender más de lo que hay en stock.
        // La consulta de actualización usa `stock_actual >= :cantidad_check` para prevenir overselling.
        $stmt_stock->execute([':cantidad' => $cantidad, ':producto_id' => $producto_id, ':cantidad_check' => $cantidad]);

        // Si la actualización no afectó a ninguna fila, significa que no había suficiente stock.
        if ($stmt_stock->rowCount() === 0) {
            // Lanzar una excepción para que se active el rollback.
            throw new Exception("No hay suficiente stock para el producto con ID: {$producto_id}.");
        }
    }

    // 1. Insertar la venta principal
    $sql_venta = "INSERT INTO ventas (paciente_id, usuario_id, fecha, monto_total, monto_recibido, cambio_devuelto, seguro, estado_pago, metodo_pago)
                  VALUES (:paciente_id, :usuario_id, :fecha, :monto_total, :monto_recibido, :cambio_devuelto, :seguro, :estado_pago, :metodo_pago)";
    $stmt_venta = $pdo->prepare($sql_venta);
    $stmt_venta->execute([
        ':paciente_id' => $paciente_id,
        ':usuario_id' => $usuario_id,
        ':fecha' => $fecha,
        ':monto_total' => $monto_total,
        ':monto_recibido' => $monto_recibido_final,
        ':cambio_devuelto' => $cambio_devuelto_final,
        ':seguro' => $seguro,
        ':estado_pago' => $estado_pago_final,
        ':metodo_pago' => $metodo_pago_final
    ]);
    $venta_id = $pdo->lastInsertId();



    // 2. Insertar los detalles de la venta
    $sql_detalle = "INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_venta, descuento_unitario) VALUES (?, ?, ?, ?, ?)";
    $stmt_detalle = $pdo->prepare($sql_detalle);

    foreach ($productos as $producto) {
        $producto_id = filter_var($producto['id'], FILTER_SANITIZE_NUMBER_INT);
        $cantidad = filter_var($producto['cantidad'], FILTER_SANITIZE_NUMBER_INT);
        $precio_venta = filter_var($producto['precio'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $descuento = filter_var($producto['descuento'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Validar que el precio de venta sea mayor que cero

        if ($precio_venta <= 0) {
            $_SESSION['error'] = "El precio de venta del producto con ID {$producto_id} no puede ser menor o igual a cero.";
            throw new Exception($_SESSION['error']);
        }


        $stmt_detalle->execute([
            $venta_id,
            $producto_id,
            $cantidad,
            $precio_venta,
            $descuento
        ]);
    }


    // Si todo ha ido bien, se confirma la transacción.
    $pdo->commit();
    $_SESSION['success'] = "Venta registrada exitosamente.";

} catch (Exception $e) {
    // Si algo falla, se revierte la transacción.
    $pdo->rollBack();
    // Se guarda un mensaje de error en la sesión. Se da prioridad al mensaje
    // ya establecido por la lógica del seguro o stock, si existe.
    $_SESSION['error'] = $_SESSION['error'] ?? 'Error al registrar la venta: ' . $e->getMessage();
}

// Redirigir al usuario de vuelta a la página de ventas.
header('Location: ../index.php?vista=ventas_farmacia');
exit;

?>