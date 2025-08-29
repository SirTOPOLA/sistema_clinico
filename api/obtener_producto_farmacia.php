<?php
require_once '../config/conexion.php';

// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$response = [];
$query = isset($_GET['q']) ? $_GET['q'] : '';

if (strlen($query) > 1) {
    try {
        // Buscar productos por nombre
        $sql = "SELECT id, nombre, precio_unitario AS precio_venta, stock_actual FROM productos WHERE nombre LIKE ? LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $search_query = '%' . $query . '%';
        $stmt->execute([$search_query]);
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // En un entorno de producción, loguear el error y no mostrarlo al usuario.
        error_log('Error al buscar productos: ' . $e->getMessage());
        $response = ['error' => 'Error de base de datos'];
    }
}

echo json_encode($response);