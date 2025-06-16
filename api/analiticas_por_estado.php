<?php
require '../config/conexion.php';

try {
    $sql = "SELECT estado, COUNT(*) AS total FROM analiticas GROUP BY estado";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
