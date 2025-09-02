<?php
require_once '../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($_GET['id'])) {
    header('Location: ../index.php?vista=seguros');
    exit;
}

try {
    $pdo->beginTransaction();
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    $sql_delete = "DELETE FROM seguros_beneficiarios WHERE id = ?";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->execute([$id]);

    $pdo->commit();
    $_SESSION['success'] = "Beneficiario eliminado exitosamente.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al eliminar beneficiario: " . $e->getMessage();
}

header('Location: ../index.php?vista=seguros');
exit;
?>