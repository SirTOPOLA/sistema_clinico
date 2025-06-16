<?php
require '../config/conexion.php';

$paciente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($paciente_id <= 0) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

try {
    $sql = "
        SELECT 
            fecha_registro,
            temperatura,
            pulso,
            frecuencia_cardiaca,
            saturacion_oxigeno,
            imc
        FROM consultas
        WHERE id_paciente = :paciente_id
        ORDER BY fecha_registro ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['paciente_id' => $paciente_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode(['mensaje' => 'Sin datos para este paciente']);
    } else {
        echo json_encode($data);
    }

} catch (PDOException $e) {
    echo json_encode(['error_sql' => $e->getMessage()]);
}
