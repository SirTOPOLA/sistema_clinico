<?php
require '../config/conexion.php'; // Asegúrate de que este archivo define $pdo correctamente

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, nombre FROM tipo_pruebas WHERE nombre LIKE :q LIMIT 10");
    $stmt->execute(['q' => '%' . $q . '%']);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la búsqueda: ' . $e->getMessage()]);
}
