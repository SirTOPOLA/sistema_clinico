<?php
require "../config/conexion.php";

header("Content-Type: application/json");

$id = intval($_GET['id']);

$sql = $pdo->prepare("SELECT * FROM compras WHERE id = ?");
$sql->execute([$id]);
$compra = $sql->fetch(PDO::FETCH_ASSOC);

$sql2 = $pdo->prepare("SELECT * FROM compra_detalles WHERE compra_id = ?");
$sql2->execute([$id]);
$detalles = $sql2->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "id" => $compra["id"],
    "proveedor_id" => $compra["proveedor_id"],
    "personal_id" => $compra["personal_id"],
    "fecha" => $compra["fecha"],
    "estado_pago" => $compra["estado_pago"],
    "monto_entregado" => $compra["monto_entregado"],
    "codigo_factura" => $compra["codigo_factura"],
    "productos" => $detalles
]);
