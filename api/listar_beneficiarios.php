<?php
require_once '../config/conexion.php';

if (empty($_GET['seguro_id'])) {
    echo json_encode([]);
    exit;
}

try {
    $seguro_id = filter_input(INPUT_GET, 'seguro_id', FILTER_SANITIZE_NUMBER_INT);
    $sql = "
        SELECT 
            sb.id,
            sb.paciente_id,
            CONCAT(p.nombre, ' ', p.apellidos) AS nombre_paciente
        FROM 
            seguros_beneficiarios sb
        JOIN 
            pacientes p ON sb.paciente_id = p.id
        WHERE 
            sb.seguro_id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$seguro_id]);
    $beneficiarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($beneficiarios);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>