<?php
require '../config/conexion.php'; // Asegúrate de que aquí se use PDO correctamente
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');

if (strlen($q) < 3) {
    echo json_encode([]);
    exit;
}

try {
    // Consulta preparada para evitar inyecciones
    $sql = "
        SELECT c.id, p.nombre, p.apellidos, p.codigo, id_paciente, DATE(c.fecha_registro) AS fecha
FROM consultas c
INNER JOIN pacientes p ON c.id_paciente = p.id
WHERE p.nombre LIKE :q
ORDER BY c.fecha_registro DESC
LIMIT 5

    ";

    $stmt = $pdo->prepare($sql);
    $likeQ = '%' . $q . '%';
    $stmt->bindParam(':q', $likeQ);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultados);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error en la búsqueda: " . $e->getMessage()]);
}
