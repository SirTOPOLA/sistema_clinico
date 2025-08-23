<?php
session_start();
require_once '../config/conexion.php'; // Ajusta la ruta a tu archivo de conexión

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'compra' => null, 'detalles' => []];

if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $id_compra = $_GET['id'];

    try {
        // Obtener detalles de la compra principal
        $stmt_compra = $pdo->prepare("
            SELECT
                cp.id,
                cp.fecha_compra,
                cp.monto_total,
                cp.adelanto,
                cp.estado_pago,
                cp.fecha_registro,
                cp.id_proveedor, -- <-- Asegúrate de seleccionar el ID del proveedor
                cp.id_personal,   -- <-- Asegúrate de seleccionar el ID del personal
                p.nombre AS nombre_proveedor,
                per.nombre AS nombre_personal,
                per.apellidos AS apellidos_personal
            FROM
                compras_proveedores AS cp
            JOIN
                proveedores AS p ON cp.id_proveedor = p.id
            JOIN
                personal AS per ON cp.id_personal = per.id
            WHERE
                cp.id = :id_compra
        ");
        $stmt_compra->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
        $stmt_compra->execute();
        $compra = $stmt_compra->fetch(PDO::FETCH_ASSOC);

        if ($compra) {
            // Obtener detalles de los productos de la compra
            $stmt_detalles = $pdo->prepare("
                SELECT
                    dcp.id AS id_detalle,      -- <-- ID del detalle de compra para edición
                    dcp.id_producto,           -- <-- ID del producto para seleccionar en el dropdown
                    dcp.cantidad,
                    dcp.unidad,
                    dcp.precio_unitario,
                    dcp.precio_venta,
                    pf.nombre AS nombre_producto,
                    -- Los siguientes campos se obtienen de productos_farmacia para rellenar
                    -- los inputs en el modal de edición, aunque no se guarden en detalle_compra_proveedores.
                    COALESCE(pf.tiras_por_caja, 0) AS tiras_por_caja, 
                    COALESCE(pf.pastillas_por_tira, 0) AS pastillas_por_tira,
                    COALESCE(pf.pastillas_por_frasco, 0) AS pastillas_por_frasco,
                    pf.fecha_vencimiento
                FROM
                    detalle_compra_proveedores AS dcp
                JOIN
                    productos_farmacia AS pf ON dcp.id_producto = pf.id
                WHERE
                    dcp.id_compra = :id_compra
            ");
            $stmt_detalles->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
            $stmt_detalles->execute();
            $detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

            $response['success'] = true;
            $response['compra'] = $compra;
            $response['detalles'] = $detalles;
        } else {
            $response['message'] = 'Compra no encontrada.';
        }

    } catch (PDOException $e) {
        $response['message'] = 'Error de base de datos: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'ID de compra no válido.';
}

echo json_encode($response);
exit();
?>