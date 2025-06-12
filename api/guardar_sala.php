<?php
session_start();
require '../config/conexion.php'; // Asegúrate de que este archivo contiene la conexión $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitizar y validar entrada
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $id_usuario = (int) $_POST['id_usuario'];

        if (empty($nombre)) {
            $_SESSION['error'] = "El nombre de la sala es obligatorio.";
            header('Location: ../index.php?vista=salas');
            exit;
        }

        // Insertar sala
        $sql = "INSERT INTO salas_ingreso (nombre, id_usuario) VALUES (:nombre, :id_usuario)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':id_usuario' => $id_usuario
        ]);

        $_SESSION['success'] = "Sala registrada correctamente.";
        header('Location: ../index.php?vista=salas');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = "Error al registrar la sala: " . $e->getMessage();
        header('Location: ../index.php?vista=salas');
        exit;
    }
} else {
    $_SESSION['error'] = "Solicitud no válida.";
    header('Location: ../index.php?vista=salas');
    exit;
}
