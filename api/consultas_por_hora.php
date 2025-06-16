<?php
require '../config/conexion.php';

$sql = "
    SELECT
        HOUR(fecha_registro) AS hora,
        COUNT(*) AS total
    FROM consultas
    GROUP BY hora
    ORDER BY hora ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
