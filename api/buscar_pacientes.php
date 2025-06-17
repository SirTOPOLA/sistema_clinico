<?php
require '../config/conexion.php';

$q = $_GET['q'] ?? '';
$q = "%$q%";

$sql = "SELECT id, nombre, apellidos, codigo FROM pacientes 
        WHERE nombre LIKE ? OR apellidos LIKE ? OR codigo LIKE ? 
        ORDER BY id DESC LIMIT 5";
$stmt = $pdo->prepare($sql);
$stmt->execute([$q, $q, $q]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
