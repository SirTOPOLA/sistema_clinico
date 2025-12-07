<?php
require "../config/conexion.php";

header("Content-Type: application/json; charset=UTF-8");

$sql = $pdo->query("SELECT id, nombre, precio_unitario FROM productos ORDER BY nombre ASC");
$productos = $sql->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($productos);
