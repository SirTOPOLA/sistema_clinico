<?php
session_start();
// Se incluye el archivo de conexión a la base de datos
require '../config/conexion.php';

// Sanitizar y validar el ID de la URL
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (empty($id)) {
    $_SESSION['error'] = "ID de proveedor no especificado.";
    header("Location: ../index.php?vista=proveedores_farmacia");
    exit();
}

try {
    // Preparar la consulta SQL para evitar inyección
    $sql = "DELETE FROM proveedores WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    // Asignar el valor al parámetro
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        $_SESSION['success'] = "Proveedor eliminado con éxito.";
    } else {
        $_SESSION['error'] = "Error al eliminar el proveedor.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
}

// Redireccionar a la vista principal
header("Location: ../index.php?vista=proveedores_farmacia");
exit();
?>