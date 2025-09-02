<?php
require_once '../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?vista=seguros');
    exit;
}

try {
    $pdo->beginTransaction();

    $titular_id = filter_input(INPUT_POST, 'titular_id', FILTER_SANITIZE_NUMBER_INT);
    $monto_inicial = filter_input(INPUT_POST, 'monto_inicial', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $metodo_pago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_STRING);
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_SANITIZE_NUMBER_INT);

    if (empty($titular_id) || $monto_inicial <= 0 || empty($metodo_pago)) {
        throw new Exception("Datos de seguro incompletos o inválidos.");
    }
    
    // 1. Verificar si el paciente ya tiene un seguro
    $sql_check = "SELECT id FROM seguros WHERE titular_id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$titular_id]);
    if ($stmt_check->fetch()) {
        throw new Exception("El paciente ya tiene un seguro registrado como titular.");
    }

    // 2. Insertar el nuevo seguro
    $sql_seguro = "INSERT INTO seguros (titular_id, monto_inicial, saldo_actual, fecha_deposito, metodo_pago) VALUES (?, ?, ?, CURDATE(), ?)";
    $stmt_seguro = $pdo->prepare($sql_seguro);
    $stmt_seguro->execute([$titular_id, $monto_inicial, $monto_inicial, $metodo_pago]);
    $seguro_id = $pdo->lastInsertId();

    // 3. Registrar el movimiento de crédito inicial
    $sql_movimiento = "INSERT INTO movimientos_seguro (seguro_id, paciente_id, tipo, monto, descripcion) VALUES (?, ?, 'CREDITO', ?, 'Depósito inicial del seguro')";
    $stmt_movimiento = $pdo->prepare($sql_movimiento);
    $stmt_movimiento->execute([$seguro_id, $titular_id, $monto_inicial]);

    $pdo->commit();
    $_SESSION['success'] = "Seguro creado exitosamente.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al crear el seguro: " . $e->getMessage();
}

header('Location: ../index.php?vista=seguros');
exit;
?>