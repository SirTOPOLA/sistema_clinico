<?php
require '../config/conexion.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id = $_GET['id'];

try {
    $sql = "SELECT analiticas.id, pacientes.nombre, pacientes.apellidos, tipo_pruebas.nombre AS tipo_prueba, analiticas.fecha_registro AS fecha
            FROM pacientes
            LEFT JOIN analiticas ON analiticas.id_paciente = pacientes.id
            LEFT JOIN tipo_pruebas ON analiticas.id_tipo_prueba = tipo_pruebas.id
            WHERE analiticas.id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        echo json_encode(['success' => true] + $resultado); // ← ✅ CORREGIDO
    } else {
        echo json_encode(['success' => false, 'message' => 'No encontrado']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
