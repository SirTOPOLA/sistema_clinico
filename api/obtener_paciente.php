<?php
require_once '../config/conexion.php';

header('Content-Type: application/json');

$response = [];
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) > 1) {
    try {
       /*  CREATE TABLE `seguros` (
            `id` int(11) NOT NULL,
            `titular_id` int(11) NOT NULL,
            `monto_inicial` decimal(12,2) NOT NULL,
            `saldo_actual` decimal(12,2) NOT NULL,
            `fecha_deposito` date NOT NULL,
            `metodo_pago` enum('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') DEFAULT 'EFECTIVO'
          )
          CREATE TABLE `seguros_beneficiarios` (
            `id` int(11) NOT NULL,
            `seguro_id` int(11) NOT NULL,
            `paciente_id` int(11) NOT NULL,
            `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
          ) */
        // Buscar pacientes por nombre, apellidos o código
        $sql = "SELECT 
        p.id,
        CONCAT(p.nombre, ' ', p.apellidos) AS nombre,
        p.apellidos,
        p.dip,
        p.codigo,
    
        -- Determinar si tiene seguro como titular o como beneficiario
        CASE
            WHEN s.id IS NOT NULL OR sb.id IS NOT NULL THEN 1
            ELSE 0
        END AS tiene_seguro
    
    FROM pacientes p
    
    -- Seguro como titular
    LEFT JOIN seguros s ON s.titular_id = p.id
    
    -- Seguro como beneficiario
    LEFT JOIN seguros_beneficiarios sb ON sb.paciente_id = p.id
    
    WHERE 
        p.nombre LIKE ? 
        OR p.apellidos LIKE ?
        OR p.codigo LIKE ?
    
    GROUP BY p.id
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
