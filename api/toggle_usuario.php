<?php
require_once '../config/conexion.php'; // ajusta según tu ruta

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['estado'])) {
    $id = intval($_POST['id']);
    $estadoActual = intval($_POST['estado']);
    $nuevoEstado = $estadoActual ? 0 : 1;

    try {
        $stmt = $pdo->prepare("UPDATE usuarios SET estado = :estado WHERE id = :id");
        $stmt->execute(['estado' => $nuevoEstado, 'id' => $id]);

        echo json_encode(['success' => true, 'nuevo_estado' => $nuevoEstado]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Petición inválida']);
}
