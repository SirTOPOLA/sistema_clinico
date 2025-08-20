<?php
session_start(); // Inicia la sesión para usar mensajes de éxito/error

// Asume que tu archivo de conexión a la base de datos se llama 'conexion.php'
// y que define una variable $pdo para la conexión PDO.
require_once '../config/conexion.php'; 

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $response = ['success' => false, 'message' => ''];

    try {
        switch ($action) {
            case 'crear':
                // Validar y sanear la entrada
                $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
                $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
                $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

                // Validación básica de campos requeridos
                if (empty($nombre) || empty($idUsuario)) {
                    $_SESSION['error_producto'] = 'El nombre del producto y el usuario son obligatorios.';
           header('Location: ../index.php?vista=medicamentos'); // Redirige a la vista
                    exit();
                }

                // Los campos de stock, precios y fecha de vencimiento se inicializan a 0/NULL
                // y se actualizarán a través del módulo de compras.
                $stock_caja = 0;
                $stock_frasco = 0;
                $stock_tira = 0;
                $stock_pastilla = 0;
                $precio_caja = 0.00;
                $precio_frasco = 0.00;
                $precio_tira = 0.00;
                $precio_pastilla = 0.00;
                $fecha_vencimiento = null; // O '0000-00-00' si tu DB no soporta NULL fechas

                $stmt = $pdo->prepare("INSERT INTO productos_farmacia 
                    (nombre, descripcion, stock_caja, stock_frasco, stock_tira, stock_pastilla, 
                    precio_caja, precio_frasco, precio_tira, precio_pastilla, fecha_vencimiento, id_usuario) 
                    VALUES (:nombre, :descripcion, :stock_caja, :stock_frasco, :stock_tira, :stock_pastilla, 
                    :precio_caja, :precio_frasco, :precio_tira, :precio_pastilla, :fecha_vencimiento, :id_usuario)");
                
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':stock_caja', $stock_caja);
                $stmt->bindParam(':stock_frasco', $stock_frasco);
                $stmt->bindParam(':stock_tira', $stock_tira);
                $stmt->bindParam(':stock_pastilla', $stock_pastilla);
                $stmt->bindParam(':precio_caja', $precio_caja);
                $stmt->bindParam(':precio_frasco', $precio_frasco);
                $stmt->bindParam(':precio_tira', $precio_tira);
                $stmt->bindParam(':precio_pastilla', $precio_pastilla);
                $stmt->bindParam(':fecha_vencimiento', $fecha_vencimiento);
                $stmt->bindParam(':id_usuario', $idUsuario);

                if ($stmt->execute()) {
                    $_SESSION['success_producto'] = 'Producto registrado exitosamente. Stock, precios y vencimiento se gestionan en Compras.';
                } else {
                    $_SESSION['error_producto'] = 'Error al registrar el producto.';
                }
                break;

            case 'editar':
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
                $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
                $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

                if (empty($id) || empty($nombre) || empty($idUsuario)) {
                    $_SESSION['error_producto'] = 'Datos incompletos para editar el producto.';
                   header('Location: ../index.php?vista=medicamentos'); // Redirige a la vista
                    exit();
                }

                // IMPORTANTE: Solo se actualizan nombre y descripción desde esta vista.
                // Stock, precios y fecha de vencimiento se gestionan desde el módulo de compras.
                $stmt = $pdo->prepare("UPDATE productos_farmacia SET nombre = :nombre, descripcion = :descripcion, id_usuario = :id_usuario WHERE id = :id");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':id_usuario', $idUsuario);
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    $_SESSION['success_producto'] = 'Producto actualizado exitosamente (nombre y descripción).';
                } else {
                    $_SESSION['error_producto'] = 'Error al actualizar el producto.';
                }
                break;

            case 'eliminar':
                $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

                if (empty($id)) {
                    $_SESSION['error_producto'] = 'ID del producto no proporcionado para eliminar.';
                 header('Location: ../index.php?vista=medicamentos'); // Redirige a la vista
                    exit();
                }

                $stmt = $pdo->prepare("DELETE FROM productos_farmacia WHERE id = :id");
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    $_SESSION['success_producto'] = 'Producto eliminado exitosamente.';
                } else {
                    $_SESSION['error_producto'] = 'Error al eliminar el producto. Puede que tenga registros relacionados (ej. en compras).';
                }
                break;

            default:
                $_SESSION['error_producto'] = 'Acción no válida.';
                break;
        }
    } catch (PDOException $e) {
        $_SESSION['error_producto'] = 'Error de base de datos: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_producto'] = 'No se especificó ninguna acción.';
}

// Redirige de vuelta a la página principal de productos
 header('Location: ../index.php?vista=medicamentos'); // Redirige a la vista
exit();
?>