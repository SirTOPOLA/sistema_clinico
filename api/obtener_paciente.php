<?php
require_once '../config/conexion.php';

header('Content-Type: application/json');

$response = [];
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) > 1) {
    try {
        // Buscar pacientes por nombre, apellidos o código
        $sql = "SELECT id, nombre, apellidos, dip, codigo 
                FROM pacientes 
                WHERE nombre LIKE ? OR apellidos LIKE ? OR codigo LIKE ?
                LIMIT 10";
        
        $stmt = $pdo->prepare($sql);
        $search_query = '%' . $query . '%';
        $stmt->execute([$search_query, $search_query, $search_query]);

        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log('Error al buscar pacientes: ' . $e->getMessage());
        $response = ['error' => 'Error al procesar la solicitud.'];
    }
} else {
    $response = ['error' => 'Consulta demasiado corta'];
}

echo json_encode($response);
