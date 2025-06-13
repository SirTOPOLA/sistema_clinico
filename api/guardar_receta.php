<?php
session_start();
require '../config/conexion.php'; // Ajusta la ruta si es necesario

try {
    // Verificamos que los datos requeridos existan
    if (
        !isset($_POST['id_usuario'], $_POST['id_consulta'], $_POST['id_paciente'],
        $_POST['descripcion'])
    ) {
        throw new Exception("Faltan datos obligatorios.");
    }

    $id_usuario = $_POST['id_usuario'];
    $id_consulta = $_POST['id_consulta'];
    $id_paciente = $_POST['id_paciente'];
    $codigo_paciente = $_POST['codigo_paciente'];
    $descripcion = $_POST['descripcion'];
    $comentario = $_POST['comentario'] ;

    // Insertar la receta
    $stmt = $pdo->prepare("INSERT INTO recetas (descripcion,id_consulta, id_paciente, codigo_paciente, comentario, id_usuario) 
                           VALUES (:descripcion, :id_consulta, :id_paciente, :codigo_paciente, :descripcion, :id_usuario)");
    $stmt->execute([
        ':descripcion' => $descripcion,
        ':id_consulta' => $id_consulta,
        ':id_paciente' => $id_paciente,
        ':codigo_paciente' => $codigo_paciente,
            ':comentario' => $comentario,
        ':id_usuario' => $id_usuario, 
    
    ]);

    $_SESSION['success'] = "Receta guardada correctamente.";
} catch (Exception $e) {
    $_SESSION['error'] = "Error al guardar la receta: " . $e->getMessage();
}

// Redireccionar
header("Location: ../index.php?vista=recetas");
exit;
