<?php
require_once '../config/conexion.php'; // Asegúrate de que la ruta sea correcta
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID de venta no proporcionado.']);
    exit;
}

$idVenta = intval($_GET['id']);

try {
    // Consulta para obtener los datos principales de la venta
    $sqlVenta = "SELECT 
                    v.id, 
                    v.fecha, 
                    v.monto_total, 
                    v.monto_recibido, 
                    v.cambio_devuelto, 
                    v.motivo_descuento,
                    v.descuento_global,
                    v.estado_pago, 
                    v.metodo_pago, 
                    v.seguro,                    
                    p.id AS paciente_id,
                    p.nombre AS paciente_nombre,
                    u.nombre_usuario AS usuario_nombre
                 FROM ventas v
                 LEFT JOIN pacientes p ON v.paciente_id = p.id
                 LEFT JOIN usuarios u ON v.usuario_id = u.id
                 WHERE v.id = ?";
    
    $stmtVenta = $pdo->prepare($sqlVenta);
    $stmtVenta->execute([$idVenta]);
    $venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        echo json_encode(['error' => 'Venta no encontrada.']);
        exit;
    }

    // Consulta para obtener los detalles de los productos de esa venta
    $sqlProductos = "SELECT 
                        vd.producto_id, 
                        pr.nombre AS nombre,
                        vd.cantidad, 
                        vd.precio_venta,
                        pr.precio_unitario,
                        vd.descuento_unitario as descuento
                     FROM ventas_detalle vd
                     JOIN productos pr ON vd.producto_id = pr.id
                     WHERE vd.venta_id = ?";
    
    $stmtProductos = $pdo->prepare($sqlProductos);
    $stmtProductos->execute([$idVenta]);
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

    // Agregar los productos a los datos de la venta
    $venta['productos'] = $productos;

    echo json_encode($venta);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}
?>
