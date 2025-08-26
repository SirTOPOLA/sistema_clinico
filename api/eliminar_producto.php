<?php
// Inicia la sesión.
session_start();

// Incluye el archivo de conexión a la base de datos.
require_once '../config/conexion.php';

// Redirecciona al usuario a la vista de productos.
$redirect_url = "../index.php?vista=productos_farmacia";

// Verifica que se haya proporcionado el ID del producto en la URL.
if (isset($_GET['id'])) {
    try {
        // Obtiene y sanitiza el ID del producto de la URL.
        $id = $_GET['id'];
        
        // Prepara la consulta SQL para eliminar el producto.
        $sql = "DELETE FROM productos WHERE id = ?";
        
        // Prepara la sentencia.
        $stmt = $pdo->prepare($sql);
        
        // Ejecuta la sentencia.
        $stmt->execute([$id]);
        
        // Establece un mensaje de éxito en la sesión.
        $_SESSION['success'] = "Producto eliminado correctamente.";

    } catch (PDOException $e) {
        // En caso de error, guarda el mensaje de error en la sesión.
        $_SESSION['error'] = "Error al eliminar el producto: " . $e->getMessage();
    } finally {
        // Redirecciona al usuario.
        header("Location: $redirect_url");
        exit();
    }
} else {
    // Si no se proporcionó el ID, se redirige con un mensaje de error.
    $_SESSION['error'] = "ID de producto no proporcionado para la eliminación.";
    header("Location: $redirect_url");
    exit();
}