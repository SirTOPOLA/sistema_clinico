<?php
session_start();
require '../config/conexion.php';

try {
    // Validar existencia de datos
    if (!isset($_POST['id_usuario'], $_POST['id_paciente'], $_POST['tipo_pago']) || !is_array($_POST['pagos'])) {
        throw new Exception("Datos incompletos para procesar el pago.");
    }

    $id_usuario = $_POST['id_usuario'];
    $id_paciente = $_POST['id_paciente'];
    $tipo_pago = $_POST['tipo_pago'];
    $pagos = $_POST['pagos'];

    // Iniciar transacción para garantizar la atomicidad de las operaciones
    $pdo->beginTransaction();

    // Calcular el total de las pruebas seleccionadas
    $total_a_pagar = 0;
    $analiticas_a_procesar = [];
    foreach ($pagos as $id_analitica => $datos) {
        if (isset($datos['seleccionado'])) {
            $precio = floatval($datos['precio'] ?? 0);
            $total_a_pagar += $precio;
            $analiticas_a_procesar[] = [
                'id_analitica' => $id_analitica,
                'precio' => $precio,
                'id_tipo_prueba' => intval($datos['id_tipo_prueba'])
            ];
        }
    }

    // Lógica principal basada en el tipo de pago
    switch ($tipo_pago) {
        case 'EFECTIVO':
            // 1. Marcar analíticas como pagadas
            $sqlActualizar = "UPDATE analiticas SET pagado = 1, tipo_pago = 'EFECTIVO' WHERE id = ?";
            $stmtActualizar = $pdo->prepare($sqlActualizar);

            // 2. Registrar cada pago en la tabla 'pagos'
            $sqlInsertarPago = "INSERT INTO pagos (cantidad, id_analitica, id_tipo_prueba, fecha_registro, id_usuario)
                                VALUES (?, ?, ?, NOW(), ?)";
            $stmtInsertarPago = $pdo->prepare($sqlInsertarPago);

            foreach ($analiticas_a_procesar as $analitica) {
                $stmtActualizar->execute([$analitica['id_analitica']]);
                $stmtInsertarPago->execute([$analitica['precio'], $analitica['id_analitica'], $analitica['id_tipo_prueba'], $id_usuario]);
            }
            break;

        case 'ADEBER':
            $monto_pagado = floatval($_POST['monto_pagar'] ?? 0);
            $monto_pendiente = $total_a_pagar - $monto_pagado;

            // 1. Actualizar analíticas: las pagadas parcialmente se marcan como 1, las pendientes con 0 y se les asigna el tipo de pago 'ADEUDO'
            $sqlActualizarAnalitica = "UPDATE analiticas SET pagado = ?, tipo_pago = 'ADEUDO' WHERE id = ?";
            $stmtActualizarAnalitica = $pdo->prepare($sqlActualizarAnalitica);
            foreach ($analiticas_a_procesar as $analitica) {
                // Si el monto pagado es igual al total, se marca como pagado. De lo contrario, no.
                $pagado_status = ($monto_pagado >= $total_a_pagar) ? 1 : 0;
                $stmtActualizarAnalitica->execute([$pagado_status, $analitica['id_analitica']]);
            }

            // 2. Insertar/actualizar la deuda en la tabla 'prestamos'
            $sqlInsertarPrestamo = "INSERT INTO prestamos (paciente_id, total, estado, fecha) VALUES (?, ?, ?, NOW())";
            $stmtInsertarPrestamo = $pdo->prepare($sqlInsertarPrestamo);
            $estado_prestamo = ($monto_pagado > 0 && $monto_pendiente > 0) ? 'PARCIAL' : 'PENDIENTE';
            $stmtInsertarPrestamo->execute([$id_paciente, $monto_pendiente, $estado_prestamo]);
            
            // Si hay un monto pagado, registrarlo en la tabla 'pagos'
            if ($monto_pagado > 0) {
                $sqlInsertarPago = "INSERT INTO pagos (cantidad, id_analitica, id_tipo_prueba, fecha_registro, id_usuario)
                                    VALUES (?, ?, ?, NOW(), ?)";
                $stmtInsertarPago = $pdo->prepare($sqlInsertarPago);
                $stmtInsertarPago->execute([$monto_pagado, $analiticas_a_procesar[0]['id_analitica'], $analiticas_a_procesar[0]['id_tipo_prueba'], $id_usuario]);
            }
            break;

        case 'SEGURO':
            $id_seguro = $_POST['id_seguro'];
            
            // 1. Obtener datos del seguro
            $sqlSeguro = "SELECT monto_inicial, saldo_actual FROM seguros WHERE id = ?";
            $stmtSeguro = $pdo->prepare($sqlSeguro);
            $stmtSeguro->execute([$id_seguro]);
            $seguro = $stmtSeguro->fetch(PDO::FETCH_ASSOC);

            if (!$seguro) {
                throw new Exception("Seguro no encontrado.");
            }

            $saldo_actual = floatval($seguro['saldo_actual']);
            $monto_inicial = floatval($seguro['monto_inicial']);

            // 2. Verificar si el saldo es suficiente
            $monto_pagado_con_seguro = 0;
            $monto_pendiente_por_seguro = 0;

            if ($saldo_actual >= $total_a_pagar) {
                // Saldo suficiente: Pagar el total con seguro
                $monto_pagado_con_seguro = $total_a_pagar;
            } else {
                // Saldo insuficiente: Reducir con el saldo disponible
                $monto_pagado_con_seguro = $saldo_actual;
                $monto_pendiente_por_seguro = $total_a_pagar - $saldo_actual;
            }

            // 3. Registrar el débito en la tabla 'movimientos_seguro' y actualizar saldo del seguro
            $sqlDebitoSeguro = "INSERT INTO movimientos_seguro (seguro_id, paciente_id, tipo, monto, descripcion) 
                                 VALUES (?, ?, 'DEBITO', ?, 'Pago de pruebas analíticas')";
            $stmtDebitoSeguro = $pdo->prepare($sqlDebitoSeguro);
            $stmtDebitoSeguro->execute([$id_seguro, $id_paciente, $monto_pagado_con_seguro]);

            $sqlActualizarSaldo = "UPDATE seguros SET saldo_actual = saldo_actual - ? WHERE id = ?";
            $stmtActualizarSaldo = $pdo->prepare($sqlActualizarSaldo);
            $stmtActualizarSaldo->execute([$monto_pagado_con_seguro, $id_seguro]);

            // 4. Marcar analíticas como pagadas con el tipo de pago 'SEGURO'
            $sqlActualizar = "UPDATE analiticas SET pagado = 1, tipo_pago = 'SEGURO' WHERE id = ?";
            $stmtActualizar = $pdo->prepare($sqlActualizar);

            $sqlInsertarPago = "INSERT INTO pagos (cantidad, id_analitica, id_tipo_prueba, fecha_registro, id_usuario)
                                VALUES (?, ?, ?, NOW(), ?)";
            $stmtInsertarPago = $pdo->prepare($sqlInsertarPago);

            foreach ($analiticas_a_procesar as $analitica) {
                 $stmtActualizar->execute([$analitica['id_analitica']]);
                 $stmtInsertarPago->execute([$analitica['precio'], $analitica['id_analitica'], $analitica['id_tipo_prueba'], $id_usuario]);
            }
            
            // 5. Lógica para el monto pendiente si el saldo del seguro no fue suficiente
            if ($monto_pendiente_por_seguro > 0) {
                // Obtener el total de deudas pendientes del paciente
                $sqlDeudas = "SELECT SUM(total) AS total_deuda FROM prestamos WHERE paciente_id = ? AND estado IN ('PENDIENTE', 'PARCIAL')";
                $stmtDeudas = $pdo->prepare($sqlDeudas);
                $stmtDeudas->execute([$id_paciente]);
                $deuda_actual = floatval($stmtDeudas->fetchColumn() ?? 0);

                // Calcular el 50% del monto inicial del seguro
                $limite_deuda = $monto_inicial * 0.50;

                // Verificar si la nueva deuda supera el límite del 50%
                if (($deuda_actual + $monto_pendiente_por_seguro) > $limite_deuda) {
                    throw new Exception("El monto pendiente ($monto_pendiente_por_seguro FCFA) más la deuda actual del paciente ($deuda_actual FCFA) superan el 50% del monto inicial del seguro ($limite_deuda FCFA).");
                }

                // Si no supera, registrar el monto pendiente en la tabla 'prestamos'
                $sqlInsertarPrestamo = "INSERT INTO prestamos (paciente_id, total, estado, fecha) VALUES (?, ?, 'PENDIENTE', NOW())";
                $stmtInsertarPrestamo = $pdo->prepare($sqlInsertarPrestamo);
                $stmtInsertarPrestamo->execute([$id_paciente, $monto_pendiente_por_seguro]);

                $_SESSION['success'] = "Pago procesado con seguro. Un monto de $monto_pendiente_por_seguro FCFA ha sido registrado como deuda.";
            } else {
                 $_SESSION['success'] = "Pago procesado exitosamente con seguro.";
            }

            break;

        default:
            throw new Exception("Tipo de pago no válido.");
    }

    $pdo->commit();
    if (!isset($_SESSION['success'])) {
        $_SESSION['success'] = "Pago registrado correctamente.";
    }

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al procesar el pago: " . $e->getMessage();
}

header("Location: ../index.php?vista=pagos");
exit;