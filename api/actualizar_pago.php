<?php
session_start();
require '../config/conexion.php'; // Ajusta la ruta según tu estructura

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validar y sanear los datos de entrada
    $id_usuario = isset($_SESSION['usuario']['id']) ? intval($_SESSION['usuario']['id']) : 0;
    $id_paciente = isset($_POST['id_paciente']) ? intval($_POST['id_paciente']) : 0;
    $tipo_pago = isset($_POST['tipo_pago']) ? htmlspecialchars($_POST['tipo_pago']) : '';
    $pagos = isset($_POST['pagos']) ? $_POST['pagos'] : [];

    if ($id_usuario <= 0 || $id_paciente <= 0 || empty($pagos) || empty($tipo_pago)) {
        $_SESSION['error'] = 'Datos inválidos o incompletos.';
        header("Location: ../index.php?vista=pagos");
        exit;
    }

    try {
        $pdo->beginTransaction();

        $total_pagado = 0;
        $monto_prestamo = 0;
        $id_seguro = null;

        // Lógica para tipo de pago
        if ($tipo_pago === 'ADEUDO') {
            $monto_a_pagar = isset($_POST['monto_pagar']) ? floatval($_POST['monto_pagar']) : 0;
            $total_pruebas = 0;
            foreach ($pagos as $id_analitica => $pago_data) {
                $total_pruebas += floatval($pago_data['precio']);
            }

            $total_pagado = $monto_a_pagar;
            $monto_prestamo = $total_pruebas - $total_pagado;

            if ($monto_prestamo < 0) {
                $pdo->rollBack();
                $_SESSION['error'] = 'El monto a pagar no puede ser mayor que el total de las pruebas.';
                header("Location: ../index.php?vista=pagos");
                exit;
            }

            // Registrar el préstamo
            if ($monto_prestamo > 0) {
                $stmt_prestamo = $pdo->prepare("INSERT INTO prestamos (paciente_id, total, estado, fecha) VALUES (?, ?, ?, CURDATE())");
                $stmt_prestamo->execute([$id_paciente, $monto_prestamo, 'PARCIAL']);
            }
        } elseif ($tipo_pago === 'SEGURO') {
            $id_seguro = isset($_POST['id_seguro']) ? intval($_POST['id_seguro']) : null;
            if (!$id_seguro) {
                $pdo->rollBack();
                $_SESSION['error'] = 'Debe seleccionar un seguro.';
                header("Location: ../index.php?vista=pagos");
                exit;
            }

            $total_pruebas = 0;
            foreach ($pagos as $id_analitica => $pago_data) {
                $total_pruebas += floatval($pago_data['precio']);
            }

            // Verificar y actualizar saldo del seguro
            $stmt_seguro = $pdo->prepare("SELECT saldo_actual FROM seguros WHERE id = ?");
            $stmt_seguro->execute([$id_seguro]);
            $seguro = $stmt_seguro->fetch(PDO::FETCH_ASSOC);

            if (!$seguro || $seguro['saldo_actual'] < $total_pruebas) {
                $pdo->rollBack();
                $_SESSION['error'] = 'Saldo insuficiente en el seguro.';
                header("Location: ../index.php?vista=pagos");
                exit;
            }

            $nuevo_saldo = $seguro['saldo_actual'] - $total_pruebas;
            $stmt_update_seguro = $pdo->prepare("UPDATE seguros SET saldo_actual = ? WHERE id = ?");
            $stmt_update_seguro->execute([$nuevo_saldo, $id_seguro]);

            // Registrar movimiento de seguro
            $stmt_mov_seguro = $pdo->prepare("INSERT INTO movimientos_seguro (seguro_id, paciente_id, tipo, monto, descripcion) VALUES (?, ?, 'DEBITO', ?, 'Pago de analíticas')");
            $stmt_mov_seguro->execute([$id_seguro, $id_paciente, $total_pruebas]);

            $total_pagado = $total_pruebas;
        } elseif ($tipo_pago === 'EFECTIVO') {
            foreach ($pagos as $pago_data) {
                $total_pagado += floatval($pago_data['precio']);
            }
        }
        
        // Actualizar el estado y tipo de pago de las analíticas seleccionadas
        foreach ($pagos as $id_analitica => $pago_data) {
            $stmt_analitica = $pdo->prepare("UPDATE analiticas SET pagado = 1, tipo_pago = ? WHERE id = ?");
            $stmt_analitica->execute([$tipo_pago, $id_analitica]);

            // Si el pago es parcial (adeudo), registrar el pago
            if ($tipo_pago === 'ADEUDO' && $total_pagado > 0) {
                $stmt_pago = $pdo->prepare("INSERT INTO pagos (cantidad, id_analitica, id_tipo_prueba, id_usuario) VALUES (?, ?, ?, ?)");
                $stmt_pago->execute([$total_pagado, $id_analitica, $pago_data['id_tipo_prueba'], $id_usuario]);
                $total_pagado = 0; // Se registra el pago en la primera analítica para evitar duplicar el monto
            } elseif ($tipo_pago === 'EFECTIVO') {
                $stmt_pago = $pdo->prepare("INSERT INTO pagos (cantidad, id_analitica, id_tipo_prueba, id_usuario) VALUES (?, ?, ?, ?)");
                $stmt_pago->execute([$pago_data['precio'], $id_analitica, $pago_data['id_tipo_prueba'], $id_usuario]);
            }
        }
        
        $pdo->commit();
        $_SESSION['success'] = 'Pago registrado correctamente.';
        header("Location: ../index.php?vista=pagos");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = 'Error en la base de datos: ' . $e->getMessage();
        header("Location: ../index.php?vista=pagos");
        exit;
    }
} else {
    $_SESSION['error'] = 'Método de solicitud no permitido.';
    header("Location: ../index.php?vista=pagos");
    exit;
}