<?php
// Asegúrate de tener la conexión a la base de datos
require_once '../config/conexion.php'; 

header('Content-Type: application/json');

$response = [
    'success' => false,
    'deudas' => [],
    'message' => 'No se encontraron deudas pendientes.'
];

try {
    $sql = "SELECT c.id, c.monto_pendiente, c.estado_pago, c.fecha, p.nombre AS nombre_proveedor
            FROM compras c
            INNER JOIN proveedores p ON c.proveedor_id = p.id
            WHERE c.estado_pago IN ('PENDIENTE', 'PARCIAL')
            ORDER BY c.fecha DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $deudas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($deudas) {
        $response['success'] = true;
        $response['deudas'] = $deudas;
        $response['message'] = 'Deudas encontradas.';
    }

} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
}

echo json_encode($response);
?>
