<?php
require_once '../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?vista=seguros');
    exit;
}

try {
    $pdo->beginTransaction();

    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $monto_inicial = filter_input(INPUT_POST, 'monto_inicial', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $metodo_pago = filter_input(INPUT_POST, 'metodo_pago', FILTER_SANITIZE_STRING);

    if (empty($id) || $monto_inicial <= 0 || empty($metodo_pago)) {
        throw new Exception("Datos de actualización incompletos o inválidos.");
    }

    $sql_update = "UPDATE seguros SET monto_inicial = ?, metodo_pago = ? WHERE id = ?";
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->execute([$monto_inicial, $metodo_pago, $id]);

    $pdo->commit();
    $_SESSION['success'] = "Seguro actualizado exitosamente.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al actualizar el seguro: " . $e->getMessage();
}

header('Location: ../index.php?vista=seguros');
exit;
?>