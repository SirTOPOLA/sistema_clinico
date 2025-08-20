<?php
session_start(); // Inicia la sesión para usar mensajes de éxito/error
 
require_once '../config/conexion.php';  


if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $response = ['success' => false, 'message' => ''];

    try {
        switch ($action) {
            case 'crear':
                // Validar y sanear la entrada
                $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
                $contacto = filter_input(INPUT_POST, 'contacto', FILTER_SANITIZE_STRING);
                $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
                $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);

                // Validación básica de campos requeridos
                if (empty($nombre)) {
                    $_SESSION['error_proveedor'] = 'El nombre del proveedor es obligatorio.';
                    header('Location: ../index.php?vista=proveedores'); // Redirige a la vista
                    exit();
                }

                $stmt = $pdo->prepare("INSERT INTO proveedores (nombre, contacto, telefono, direccion) VALUES (:nombre, :contacto, :telefono, :direccion)");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':contacto', $contacto);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':direccion', $direccion);

                if ($stmt->execute()) {
                    $_SESSION['success_proveedor'] = 'Proveedor registrado exitosamente.';
                } else {
                    $_SESSION['error_proveedor'] = 'Error al registrar el proveedor.';
                }
                break;

            case 'editar':
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
                $contacto = filter_input(INPUT_POST, 'contacto', FILTER_SANITIZE_STRING);
                $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
                $direccion = filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_STRING);

                if (empty($id) || empty($nombre)) {
                    $_SESSION['error_proveedor'] = 'Datos incompletos para editar el proveedor.';
                    header('Location: ../index.php?vista=proveedores'); // Redirige a la vista
                    exit();
                }

                $stmt = $pdo->prepare("UPDATE proveedores SET nombre = :nombre, contacto = :contacto, telefono = :telefono, direccion = :direccion WHERE id = :id");
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':contacto', $contacto);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':direccion', $direccion);
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    $_SESSION['success_proveedor'] = 'Proveedor actualizado exitosamente.';
                } else {
                    $_SESSION['error_proveedor'] = 'Error al actualizar el proveedor.';
                }
                break;

            case 'eliminar':
                $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

                if (empty($id)) {
                    $_SESSION['error_proveedor'] = 'ID del proveedor no proporcionado para eliminar.';
                    header('Location: ../index.php?vista=proveedores'); // Redirige a la vista
                    exit();
                }

                $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id = :id");
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    $_SESSION['success_proveedor'] = 'Proveedor eliminado exitosamente.';
                } else {
                    $_SESSION['error_proveedor'] = 'Error al eliminar el proveedor. Puede que tenga registros relacionados.';
                }
                break;

            default:
                $_SESSION['error_proveedor'] = 'Acción no válida.';
                break;
        }
    } catch (PDOException $e) {
        $_SESSION['error_proveedor'] = 'Error de base de datos: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_proveedor'] = 'No se especificó ninguna acción.';
}

// Redirige de vuelta a la página principal de proveedores
 header('Location: ../index.php?vista=proveedores'); // Redirige a la vista

exit();
?>