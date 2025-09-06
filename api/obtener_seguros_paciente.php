<?php
require_once '../config/conexion.php';

header('Content-Type: application/json');

if (isset($_GET['id_paciente'])) {
    $id_paciente = $_GET['id_paciente'];

    try {
        // Unir los resultados de titular y beneficiario en un solo array
        $seguros_totales = [];

        // Obtener seguros donde el paciente es el titular
        $stmt_titular = $pdo->prepare("SELECT s.id, s.saldo_actual, CONCAT( p.nombre , ' ' , p.apellidos) AS titular
                                        FROM seguros s
                                        LEFT JOIN pacientes p ON p.id = s.titular_id
                                        WHERE titular_id = ?");
        $stmt_titular->execute([$id_paciente]);
        $seguros_titular = $stmt_titular->fetchAll(PDO::FETCH_ASSOC);

        $seguros_totales = array_merge($seguros_totales, $seguros_titular);
        // Obtener seguros donde el paciente es un beneficiario
        $stmt_beneficiario = $pdo->prepare("
            SELECT s.id, s.saldo_actual 
            FROM seguros s
            JOIN seguros_beneficiarios sb ON s.id = sb.seguro_id
            WHERE sb.paciente_id = ?
        ");
        $stmt_beneficiario->execute([$id_paciente]);
        $seguros_beneficiario = $stmt_beneficiario->fetchAll(PDO::FETCH_ASSOC);
        $seguros_totales = array_merge($seguros_totales, $seguros_beneficiario);

        // Eliminar duplicados si un paciente es titular y beneficiario del mismo seguro
        $seguros_unicos = array_map("unserialize", array_unique(array_map("serialize", $seguros_totales)));

        echo json_encode(array_values($seguros_unicos));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID de paciente no proporcionado.']);
}
