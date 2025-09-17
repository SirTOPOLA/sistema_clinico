<?php
session_start();
// Se incluye el archivo de conexión a la base de datos
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar los datos de entrada
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $abreviatura = filter_input(INPUT_POST, 'abreviatura', FILTER_SANITIZE_STRING);

    if (empty($nombre) || empty($abreviatura)) {
        $_SESSION['error'] = "El nombre y la abreviatura no pueden estar vacíos.";
        header("Location: ../index.php?vista=farmacia");
        exit();
    }

    try {
        // Preparar la consulta SQL para evitar inyección
        $sql = "INSERT INTO unidades_medida (nombre, abreviatura) VALUES (:nombre, :abreviatura)";
        $stmt = $pdo->prepare($sql);
        
        // Asignar los valores a los parámetros
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':abreviatura', $abreviatura, PDO::PARAM_STR);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $_SESSION['success'] = "Unidad de medida guardada con éxito.";
        } else {
            $_SESSION['error'] = "Error al guardar la unidad de medida.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Método de solicitud no válido.";
}

// Redireccionar a la vista principal
header("Location: ../index.php?vista=farmacia");
exit();
?>