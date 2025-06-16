<?php
require '../config/conexion.php';

$sql = "
    SELECT
        YEAR(fecha_ingreso) AS anio,
        MONTH(fecha_ingreso) AS mes,
        COUNT(*) AS total
    FROM ingresos
    GROUP BY anio, mes
    ORDER BY anio, mes
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Opcional: transformar el mes a texto (Ej: '2025-06')
foreach ($data as &$row) {
    $row['periodo'] = $row['anio'] . '-' . str_pad($row['mes'], 2, '0', STR_PAD_LEFT);
}
unset($row);

echo json_encode($data);
