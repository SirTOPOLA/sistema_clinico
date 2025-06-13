<?php
session_start();
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_receta = $_POST['id'] ?? null;
    $codigo_paciente = $_POST['codigo_paciente'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $comentario = $_POST['comentario'] ?? '';
     $id_usuario = $_POST['id_usuario'] ?? '';



    if ($id_receta && $id_usuario) {
        try {
            $stmt = $pdo->prepare("UPDATE recetas SET descripcion = ?, id_usuario=?, codigo_paciente = ?,  comentario = ? WHERE id = ?");
            $stmt->execute([$descripcion, $id_usuario, $codigo_paciente,  $comentario, $id_receta]);

            $_SESSION['success'] = "Receta actualizada correctamente.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al actualizar la receta: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Faltan datos obligatorios para actualizar la receta.";
    }

    header("Location: ../index.php?vista=recetas");
    exit();
} else {
    $_SESSION['error'] = "Acceso no permitido.";
    header("Location: ../index.php?vista=recetas");
    exit();
}
?>
