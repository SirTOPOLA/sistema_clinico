<?php
require '../config/conexion.php';
$stmt = $pdo->query("SELECT id, nombre, apellidos FROM pacientes ORDER BY nombre");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
