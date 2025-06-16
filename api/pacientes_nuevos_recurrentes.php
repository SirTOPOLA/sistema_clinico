<?php
require '../config/conexion.php';

$sql = "
    SELECT 
        CASE WHEN c1.num_consultas = 1 THEN 'Nuevos' ELSE 'Recurrentes' END AS tipo_paciente,
        COUNT(*) AS total
    FROM (
        SELECT id_paciente, COUNT(*) AS num_consultas
        FROM consultas
        GROUP BY id_paciente
    ) c1
    GROUP BY tipo_paciente
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
