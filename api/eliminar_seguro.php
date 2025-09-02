<?php
require_once '../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($_GET['id'])) {
    header('Location: ../index.php?vista=seguros');
    exit;
}

try {
    $pdo->beginTransaction();

    $seguro_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    // 1. Eliminar movimientos del seguro
    $sql_movimientos = "DELETE FROM movimientos_seguro WHERE seguro_id = ?";
    $stmt_movimientos = $pdo->prepare($sql_movimientos);
    $stmt_movimientos->execute([$seguro_id]);

    // 2. Eliminar beneficiarios del seguro
    $sql_beneficiarios = "DELETE FROM seguros_beneficiarios WHERE seguro_id = ?";
    $stmt_beneficiarios = $pdo->prepare($sql_beneficiarios);
    $stmt_beneficiarios->execute([$seguro_id]);

    // 3. Eliminar el seguro
    $sql_seguro = "DELETE FROM seguros WHERE id = ?";
    $stmt_seguro = $pdo->prepare($sql_seguro);
    $stmt_seguro->execute([$seguro_id]);

    $pdo->commit();
    $_SESSION['success'] = "Seguro y sus registros asociados eliminados exitosamente.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al eliminar el seguro: " . $e->getMessage();
}

header('Location: ../index.php?vista=seguros');
exit;
?>