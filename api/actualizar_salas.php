<?php
session_start();
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = (int) $_POST['id'];
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $id_usuario = (int) $_POST['id_usuario'];

        // Validar campos
        if (empty($nombre)) {
            $_SESSION['error'] = 'El nombre de la sala es obligatorio.';
            header('Location: ../index.php?vista=salas_ingreso');
            exit;
        }

        // Verificar que exista la sala
        $stmt = $pdo->prepare("SELECT id FROM salas_ingreso WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'La sala no existe.';
            header('Location: ../index.php?vista=salas');
            exit;
        }

        // Actualizar sala
        $sql = "UPDATE salas_ingreso SET nombre = :nombre, id_usuario = :id_usuario WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':id_usuario' => $id_usuario,
            ':id' => $id
        ]);

        $_SESSION['success'] = 'Sala actualizada correctamente.';
        header('Location: ../index.php?vista=salas');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al actualizar la sala: ' . $e->getMessage();
        header('Location: ../index.php?vista=salas');
        exit;
    }
} else {
    $_SESSION['error'] = 'Solicitud no válida.';
    header('Location: ../index.php?vista=salas');
    exit;
}
