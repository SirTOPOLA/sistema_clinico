<?php
session_start();
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int) $_POST['id'];
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);
        $id_usuario = (int) $_POST['id_usuario'];

        // Validar campos
        if ($id <= 0 || empty($nombre) || $precio === false) {
            $_SESSION['error'] = 'Todos los campos son obligatorios y deben ser válidos.';
            header('Location: ../index.php?vista=tipo_prueba');
            exit;
        }

        // Verificar si el tipo de prueba existe
        $stmt = $pdo->prepare("SELECT id FROM tipo_pruebas WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'Tipo de prueba no encontrado.';
            header('Location: ../index.php?vista=tipo_prueba');
            exit;
        }

        // Actualizar datos
        $sql = "UPDATE tipo_pruebas
                SET nombre = :nombre, precio = :precio, id_usuario = :id_usuario 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':precio' => $precio,
            ':id_usuario' => $id_usuario,
            ':id' => $id
        ]);

        $_SESSION['success'] = 'Tipo de prueba actualizado correctamente.';
        header('Location: ../index.php?vista=tipo_prueba');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al actualizar: ' . $e->getMessage();
        header('Location: ../index.php?vista=tipo_prueba');
        exit;
    }
} else {
    $_SESSION['error'] = 'Método de solicitud no permitido.';
    header('Location: ../index.php?vista=tipo_prueba');
    exit;
}
