<?php
require_once '../config/conexion.php';

header('Content-Type: application/json');

$response = ['has_insurance' => false];

if (isset($_GET['id_paciente'])) {
    $id_paciente = $_GET['id_paciente'];

    try {
        // Verificar si el paciente es titular de un seguro
        $stmt_titular = $pdo->prepare("SELECT COUNT(*) FROM seguros WHERE titular_id = ?");
        $stmt_titular->execute([$id_paciente]);
        $es_titular = $stmt_titular->fetchColumn() > 0;

        // Verificar si el paciente es beneficiario de un seguro
        $stmt_beneficiario = $pdo->prepare("SELECT COUNT(*) FROM seguros_beneficiarios WHERE paciente_id = ?");
        $stmt_beneficiario->execute([$id_paciente]);
        $es_beneficiario = $stmt_beneficiario->fetchColumn() > 0;

        $response['has_insurance'] = $es_titular || $es_beneficiario;

        echo json_encode($response);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID de paciente no proporcionado.']);
}