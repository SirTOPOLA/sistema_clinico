<?php
session_start();
require '../config/conexion.php'; // Ensure this path is correct.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Solicitud no válida.";
    header('Location: ../index.php?vista=pagos');
    exit;
}

try {
    $pdo->beginTransaction();

    // Validate the existence of required POST data
    if (!isset($_POST['id_usuario'], $_POST['pagos'], $_POST['metodo_pago']) || !is_array($_POST['pagos'])) {
        throw new Exception("Datos incompletos para procesar el pago.");
    }

    $id_usuario = (int) $_POST['id_usuario'];
    $metodo_pago = $_POST['metodo_pago'];
    $pagos_seleccionados = $_POST['pagos'];
    $descuento = ($metodo_pago === 'SEGURO') ? floatval($_POST['descuento']) : 0;
    
    if (empty($pagos_seleccionados)) {
        throw new Exception("No se ha seleccionado ninguna prueba para pagar.");
    }
    
    // Calculate total cost and get patient ID
    $ids_analiticas = [];
    $total_bruto = 0;
    $id_paciente = null;

    foreach ($pagos_seleccionados as $id_analitica => $pago) {
        if (isset($pago['seleccionado'])) {
            $ids_analiticas[] = (int)$id_analitica;
            $total_bruto += floatval($pago['precio']);
            
            if ($id_paciente === null) {
                $stmt_paciente = $pdo->prepare("SELECT id_paciente FROM analiticas WHERE id = ?");
                $stmt_paciente->execute([$id_analitica]);
                $id_paciente = $stmt_paciente->fetchColumn();
            }
        }
    }

    if (empty($ids_analiticas)) {
        throw new Exception("No se ha seleccionado ninguna prueba válida para pagar.");
    }
    
    $monto_final = $total_bruto;
    if ($metodo_pago === 'SEGURO') {
        $monto_final = $total_bruto - (($total_bruto * $descuento) / 100);
    }

    // Prepare a dynamic IN clause for the UPDATE query
    $in_clause = implode(',', array_fill(0, count($ids_analiticas), '?'));
    
    switch ($metodo_pago) {
        case 'EFECTIVO':
            // Update the 'analiticas' table
            $sql_update_analitica = "UPDATE analiticas SET pagado = 1, tipo_pago = 'EFECTIVO' WHERE id IN ($in_clause)";
            $stmt_update_analitica = $pdo->prepare($sql_update_analitica);
            $stmt_update_analitica->execute($ids_analiticas);

            // Insert into the 'pagos' table for each selected test
            foreach ($pagos_seleccionados as $id_analitica => $datos) {
                if (isset($datos['seleccionado'])) {
                    $sql_insert_pago = "INSERT INTO pagos (cantidad, id_analitica, id_tipo_prueba, fecha_registro, id_usuario) VALUES (?, ?, ?, NOW(), ?)";
                    $stmt_insert_pago = $pdo->prepare($sql_insert_pago);
                    $stmt_insert_pago->execute([$datos['precio'], $id_analitica, $datos['id_tipo_prueba'], $id_usuario]);
                }
            }
            break;

        case 'SEGURO':
            // Check patient's insurance balance
            $sql_seguro = "SELECT id, saldo_actual FROM seguros WHERE titular_id = ?";
            $stmt_seguro = $pdo->prepare($sql_seguro);
            $stmt_seguro->execute([$id_paciente]);
            $seguro = $stmt_seguro->fetch(PDO::FETCH_ASSOC);

            if (!$seguro) {
                throw new Exception("El paciente no tiene seguro registrado.");
            }
            if ($seguro['saldo_actual'] < $monto_final) {
                throw new Exception("Saldo insuficiente en el seguro del paciente.");
            }

            // Update insurance balance
            $nuevo_saldo = $seguro['saldo_actual'] - $monto_final;
            $sql_update_seguro = "UPDATE seguros SET saldo_actual = ? WHERE id = ?";
            $stmt_update_seguro = $pdo->prepare($sql_update_seguro);
            $stmt_update_seguro->execute([$nuevo_saldo, $seguro['id']]);

            // Register the movement in the 'movimientos_seguro' table
            $sql_movimiento = "INSERT INTO movimientos_seguro (seguro_id, paciente_id, tipo, monto, descripcion) VALUES (?, ?, 'DEBITO', ?, ?)";
            $stmt_movimiento = $pdo->prepare($sql_movimiento);
            $descripcion = "Pago por analíticas con seguro (Descuento: {$descuento}%)";
            $stmt_movimiento->execute([$seguro['id'], $id_paciente, $monto_final, $descripcion]);

            // Update the 'analiticas' table
            $sql_update_analitica = "UPDATE analiticas SET pagado = 1, tipo_pago = 'SEGURO' WHERE id IN ($in_clause)";
            $stmt_update_analitica = $pdo->prepare($sql_update_analitica);
            $stmt_update_analitica->execute($ids_analiticas);
            break;

        case 'DEBE':
            // Insert a new record in the 'prestamos' table
            $sql_prestamo = "INSERT INTO prestamos (paciente_id, total, estado, fecha) VALUES (?, ?, 'PENDIENTE', CURDATE())";
            $stmt_prestamo = $pdo->prepare($sql_prestamo);
            $stmt_prestamo->execute([$id_paciente, $total_bruto]);
            
            // Update the 'analiticas' table
            $sql_update_analitica = "UPDATE analiticas SET pagado = 0, tipo_pago = 'ADEUDO' WHERE id IN ($in_clause)";
            $stmt_update_analitica = $pdo->prepare($sql_update_analitica);
            $stmt_update_analitica->execute($ids_analiticas);
            break;

        default:
            throw new Exception("Método de pago no válido.");
    }
    
    $pdo->commit();
    $_SESSION['success'] = "Pago registrado correctamente.";

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al procesar el pago: " . $e->getMessage();
}

header("Location: ../index.php?vista=pagos");
exit;
?>