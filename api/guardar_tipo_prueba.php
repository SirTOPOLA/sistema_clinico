<?php
session_start();
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);
        $id_usuario = (int) $_SESSION['usuario']['id'];

        // Validar campos obligatorios
        if (empty($nombre) || $precio === false) {
            $_SESSION['error'] = 'El nombre y un precio válido son obligatorios.';
            header('Location: ../index.php?vista=salas_pruebas');
            exit;
        }

        // (Opcional) Verificar si ya existe un tipo de prueba con ese nombre
        $stmt = $pdo->prepare("SELECT id FROM tipo_pruebas WHERE nombre = :nombre");
        $stmt->execute([':nombre' => $nombre]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Ya existe un tipo de prueba con ese nombre.';
            header('Location: ../index.php?vista=salas_pruebas');
            exit;
        }

        // Insertar tipo de prueba
        $sql = "INSERT INTO tipo_pruebas (nombre, precio, id_usuario) 
                VALUES (:nombre, :precio, :id_usuario)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':precio' => $precio,
            ':id_usuario' => $id_usuario
        ]);

        $_SESSION['success'] = 'Tipo de prueba registrado correctamente.';
        header('Location: ../index.php?vista=salas_pruebas');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al guardar el tipo de prueba: ' . $e->getMessage();
        header('Location: ../index.php?vista=salas_pruebas');
        exit;
    }
} else {
    $_SESSION['error'] = 'Método no permitido.';
    header('Location: ../index.php?vista=salas_pruebas');
    exit;
}
