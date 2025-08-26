<?php
session_start();
// Se incluye el archivo de conexión a la base de datos
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar los datos de entrada
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $abreviatura = filter_input(INPUT_POST, 'abreviatura', FILTER_SANITIZE_STRING);

    if (empty($id) || empty($nombre) || empty($abreviatura)) {
        $_SESSION['error'] = "Todos los campos son obligatorios.";
        header("Location: ../index.php?vista=unidadesMedida_farmacia");
        exit();
    }

    try {
        // Preparar la consulta SQL para evitar inyección
        $sql = "UPDATE unidades_medida SET nombre = :nombre, abreviatura = :abreviatura WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        // Asignar los valores a los parámetros
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':abreviatura', $abreviatura, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $_SESSION['success'] = "Unidad de medida actualizada con éxito.";
        } else {
            $_SESSION['error'] = "Error al actualizar la unidad de medida.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Método de solicitud no válido.";
}

// Redireccionar a la vista principal
header("Location: ../index.php?vista=unidadesMedida_farmacia");
exit();
?>
