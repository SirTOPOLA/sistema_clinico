<?php
require_once "../config/conexion.php";
header("Content-Type: application/json; charset=UTF-8");

// Validar ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(["error" => "ID inválido"]);
    exit;
}

try {

    // ==========================
    // 1. DETALLES DE LA COMPRA
    // ==========================
    $sql = "
        SELECT c.*, 
               p.nombre AS proveedor_nombre,
               p.telefono AS proveedor_telefono,
               p.direccion AS proveedor_direccion,
               per.nombre AS personal_nombre,
               per.apellidos AS personal_apellidos
        FROM compras c
        LEFT JOIN proveedores p ON p.id = c.proveedor_id
        LEFT JOIN personal per ON per.id = c.personal_id
        WHERE c.id = :id
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id" => $id]);
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
        echo json_encode(["error" => "Compra no encontrada"]);
        exit;
    }

    // ==========================
    // 2. DETALLE DE PRODUCTOS
    // ==========================
    $sql_detalle = "
        SELECT cd.*, pr.nombre AS producto_nombre
        FROM compras_detalle cd
        LEFT JOIN productos pr ON pr.id = cd.producto_id
        WHERE cd.compra_id = :id
    ";

    $stmt_det = $pdo->prepare($sql_detalle);
    $stmt_det->execute(["id" => $id]);
    $detalle = $stmt_det->fetchAll(PDO::FETCH_ASSOC);

    // ==========================
    // 3. HISTORIAL DE PAGOS
    // ==========================
    $sql_pagos = "
        SELECT pp.*, 
               per.nombre AS personal_nombre, 
               per.apellidos AS personal_apellidos
        FROM pagos_proveedores pp
        LEFT JOIN personal per ON per.id = pp.proveedor_id
        WHERE pp.compra_id = :id
        ORDER BY pp.fecha DESC
    ";

    $stmt_pagos = $pdo->prepare($sql_pagos);
    $stmt_pagos->execute(["id" => $id]);
    $pagos = $stmt_pagos->fetchAll(PDO::FETCH_ASSOC);

    // ==========================
    // RESPUESTA FINAL
    // ==========================
    echo json_encode([
        "success" => true,
        "compra"  => $compra,
        "detalle" => $detalle,
        "pagos"   => $pagos
    ]);

} catch (Exception $e) {
    echo json_encode([
        "error" => "Error interno: " . $e->getMessage()
    ]);
}
