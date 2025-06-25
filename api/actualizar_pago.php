<?php
session_start();
require '../config/conexion.php'; // Ajusta la ruta según tu estructura

header('Content-Type: application/json'); // Indicamos que la respuesta será JSON

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $consulta_id = isset($_POST['consulta_id']) ? intval($_POST['consulta_id']) : 0;
    $monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;

    if ($consulta_id > 0 && $monto > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE consultas SET pagado = 1, precio = :monto WHERE id = :id");
            $stmt->execute([
                ':monto' => $monto,
                ':id' => $consulta_id
            ]);

            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Consulta pagada correctamente.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró la consulta o no se realizaron cambios.'
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error en la base de datos: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos enviados.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido.'
    ]);
}

