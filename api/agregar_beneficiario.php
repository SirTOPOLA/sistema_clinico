<?php
require_once '../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?vista=seguros');
    exit;
}

try {
    $pdo->beginTransaction();

    $seguro_id = filter_input(INPUT_POST, 'seguro_id', FILTER_SANITIZE_NUMBER_INT);
    $paciente_id = filter_input(INPUT_POST, 'paciente_id', FILTER_SANITIZE_NUMBER_INT);

    if (empty($seguro_id) || empty($paciente_id)) {
        throw new Exception("Datos de beneficiario incompletos.");
    }

    // Verificar si el paciente ya es titular o beneficiario del seguro
    $sql_check = "SELECT 1 FROM seguros WHERE id = ? AND titular_id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$seguro_id, $paciente_id]);
    if ($stmt_check->fetch()) {
        throw new Exception("El paciente ya es el titular de este seguro.");
    }
    
    $sql_check_beneficiario = "SELECT 1 FROM seguros_beneficiarios WHERE seguro_id = ? AND paciente_id = ?";
    $stmt_check_beneficiario = $pdo->prepare($sql_check_beneficiario);
    $stmt_check_beneficiario->execute([$seguro_id, $paciente_id]);
    if ($stmt_check_beneficiario->fetch()) {
        throw new Exception("El paciente ya es beneficiario de este seguro.");
    }
    
    // Insertar el nuevo beneficiario
    $sql_insert = "INSERT INTO seguros_beneficiarios (seguro_id, paciente_id) VALUES (?, ?)";
    $stmt_insert = $pdo->prepare($sql_insert);
    $stmt_insert->execute([$seguro_id, $paciente_id]);

    $pdo->commit();
    $_SESSION['success'] = "Beneficiario agregado exitosamente.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al agregar beneficiario: " . $e->getMessage();
}

header('Location: ../index.php?vista=seguros');
exit;
?>