<?php
require_once '../config/conexion.php';

header('Content-Type: application/json');

$response = [
    'existeSeguro' => false,
    'dataSeguro' => []
];

if (isset($_GET['paciente_id'])) {
    $id_paciente = $_GET['paciente_id'];


    try {
        // Verificar si el paciente es titular de un seguro
        $stmt_titular = $pdo->prepare("SELECT COUNT(*) FROM seguros WHERE titular_id = ?");
        $stmt_titular->execute([$id_paciente]);
        // Verificar si el paciente es beneficiario de un seguro
        $stmt_beneficiario = $pdo->prepare("SELECT COUNT(*) FROM seguros_beneficiarios WHERE paciente_id = ?");
        $stmt_beneficiario->execute([$id_paciente]);
        $es_titular = ($stmt_titular->fetchColumn() > 0);
        $es_beneficiario = ($stmt_beneficiario->fetchColumn() > 0);

        if ($es_titular) {

             $stmt = $pdo->prepare("SELECT s.*, CONCAT(p.nombre, ' ',p.apellidos) AS nombre FROM seguros s INNER JOIN pacientes p ON p.id = s.titular_id WHERE s.titular_id = ?");
             $stmt->execute([$id_paciente]);
             $response[  'dataSeguro' ] = $stmt->fetch(PDO::FETCH_ASSOC);
             
        }
        if ($es_beneficiario) {

             $stmt = $pdo->prepare("SELECT  sb.seguro_id, s.saldo_actual, CONCAT(p.nombre,' ',p.apellidos) AS nombre 
             FROM seguros_beneficiarios sb 
             INNER JOIN seguros s ON s.id = sb.seguro_id 
             INNER JOIN pacientes p ON p.id = s.titular_id    
             WHERE sb.paciente_id = ?");
             $stmt->execute([$id_paciente]);
             $response[  'dataSeguro' ] = $stmt->fetch(PDO::FETCH_ASSOC);
        }



        $response['existeSeguro'] = ($es_titular || $es_beneficiario) ? true : false;


        //  $response['dataSeguro'] = $dataSeguro;

        echo json_encode($response);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID de paciente no proporcionado.']);
}




