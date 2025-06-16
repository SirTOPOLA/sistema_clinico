<?php
require '../config/conexion.php';

$sql = "
    SELECT descripcion, COUNT(*) AS total
    FROM recetas
    GROUP BY descripcion
    ORDER BY total DESC
    LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
