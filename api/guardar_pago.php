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
            
                foreach ($analiticas_a_procesar as $analitica) {
            
                    $precio = $analitica['precio'];
                    $idAnalitica = $analitica['id_analitica'];
            
                    $pagoAplicado = min($precio, $monto_pagado);
                    $pendiente = $precio - $pagoAplicado;
                    $monto_pagado -= $pagoAplicado;
            
                    // Registrar pago si existe
                    if ($pagoAplicado > 0) {
                        $pdo->prepare("
                            INSERT INTO pagos (cantidad, id_analitica, id_tipo_prueba, fecha_registro, id_usuario)
                            VALUES (?, ?, ?, NOW(), ?)
                        ")->execute([
                            $pagoAplicado,
                            $idAnalitica,
                            $analitica['id_tipo_prueba'],
                            $id_usuario
                        ]);
                    }
            
                    // Crear o actualizar préstamo
                    if ($pendiente > 0) {
                        $pdo->prepare("
                            INSERT INTO prestamos 
                            (paciente_id, total, estado, fecha, origen_tipo, origen_id)
                            VALUES (?, ?, ?, CURDATE(), 'ANALITICA', ?)
                        ")->execute([
                            $id_paciente,
                            $pendiente,
                            $pagoAplicado > 0 ? 'PARCIAL' : 'PENDIENTE',
                            $idAnalitica
                        ]);
            
                        $pdo->prepare("
                            UPDATE analiticas SET pagado = 0, tipo_pago = 'ADEUDO'
                            WHERE id = ?
                        ")->execute([$idAnalitica]);
            
                    } else {
                        $pdo->prepare("
                            UPDATE analiticas SET pagado = 1, tipo_pago = 'ADEUDO'
                            WHERE id = ?
                        ")->execute([$idAnalitica]);
                    }
                }
                break;
            

                case 'SEGURO':

                    // Obtener seguro
                    $stmt = $pdo->prepare("SELECT saldo_actual FROM seguros WHERE id = ? FOR UPDATE");
                    $stmt->execute([$_POST['id_seguro']]);
                    $seguro = $stmt->fetch(PDO::FETCH_ASSOC);
                
                    if (!$seguro) throw new Exception("Seguro no encontrado");
                
                    $saldo = (float)$seguro['saldo_actual'];
                
                    foreach ($analiticas_a_procesar as $analitica) {
                
                        $precio = $analitica['precio'];
                        $idAnalitica = $analitica['id_analitica'];
                
                        $cubierto = min($precio, $saldo);
                        $pendiente = $precio - $cubierto;
                        $saldo -= $cubierto;
                
                        // Débito del seguro
                        if ($cubierto > 0) {
                            $pdo->prepare("
                                INSERT INTO movimientos_seguro 
                                (seguro_id, paciente_id, tipo, monto, descripcion)
                                VALUES (?, ?, 'DEBITO', ?, 'Pago analítica')
                            ")->execute([
                                $_POST['id_seguro'],
                                $id_paciente,
                                $cubierto
                            ]);
                
                            $pdo->prepare("
                                UPDATE seguros SET saldo_actual = saldo_actual - ?
                                WHERE id = ?
                            ")->execute([$cubierto, $_POST['id_seguro']]);
                        }
                
                        // Préstamo si falta
                        if ($pendiente > 0) {
                            $pdo->prepare("
                                INSERT INTO prestamos 
                                (paciente_id, total, estado, fecha, origen_tipo, origen_id)
                                VALUES (?, ?, 'PENDIENTE', CURDATE(), 'ANALITICA', ?)
                            ")->execute([
                                $id_paciente,
                                $pendiente,
                                $idAnalitica
                            ]);
                
                            $pdo->prepare("
                                UPDATE analiticas SET pagado = 0, tipo_pago = 'SEGURO'
                                WHERE id = ?
                            ")->execute([$idAnalitica]);
                        } else {
                            $pdo->prepare("
                                UPDATE analiticas SET pagado = 1, tipo_pago = 'SEGURO'
                                WHERE id = ?
                            ")->execute([$idAnalitica]);
                        }
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