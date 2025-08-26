<?php
session_start();
// Se incluye el archivo de conexión a la base de datos
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar los datos de entrada
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
    $contacto = filter_input(INPUT_POST, 'contacto', FILTER_SANITIZE_STRING);

    if (empty($nombre)) {
        $_SESSION['error'] = "El nombre del proveedor no puede estar vacío.";
        header("Location: ../index.php?vista=proveedores_farmacia");
        exit();
    }

    try {
        // Preparar la consulta SQL para evitar inyección
        $sql = "INSERT INTO proveedores (nombre, direccion, telefono, contacto) VALUES (:nombre, :direccion, :telefono, :contacto)";
        $stmt = $pdo->prepare($sql);

        // Asignar los valores a los parámetros
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':direccion', $direccion, PDO::PARAM_STR);
        $stmt->bindParam(':telefono', $telefono, PDO::PARAM_STR);
        $stmt->bindParam(':contacto', $contacto, PDO::PARAM_STR);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $_SESSION['success'] = "Proveedor guardado con éxito.";
        } else {
            $_SESSION['error'] = "Error al guardar el proveedor.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Método de solicitud no válido.";
}

// Redireccionar a la vista principal
header("Location: ../index.php?vista=proveedores_farmacia");
exit();
?>