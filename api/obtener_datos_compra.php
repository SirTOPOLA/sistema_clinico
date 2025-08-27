<?php
// Asegúrate de tener la conexión a la base de datos
require_once '../config/conexion.php'; 

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'ID de compra no proporcionado o inválido.'
];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $compra_id = $_GET['id'];

    try {
        $sql = "SELECT c.*,
                        p.nombre as nombre_proveedor 
                FROM compras  c
                LEFT JOIN proveedores p ON p.id = c.proveedor_id
                WHERE c.id = :compra_id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':compra_id', $compra_id, PDO::PARAM_INT);
        $stmt->execute();
        $compra = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($compra) {
            $response['success'] = true;
            $response['message'] = 'Compra encontrada.';
            $response['compra'] = $compra;
        } else {
            $response['message'] = 'ID de compra no encontrado en la base de datos.';
        }

    } catch (PDOException $e) {
        $response['message'] = 'Error de base de datos: ' . $e->getMessage();
    }
}

echo json_encode($response);
?>