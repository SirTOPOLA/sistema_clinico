<?php
session_start();
require_once '../config/conexion.php'; // Asegúrate de tener tu conexión PDO aquí

try {
    // Validar existencia de ID
    if (empty($_POST['id'])) {
        $_SESSION['error'] = 'ID de personal no proporcionado.';
        header("Location: ../index.php?vista=usuarios");
        exit;
    }

    // Obtener datos del formulario
    $id               = $_POST['id'];
    $id_usuario       = $_POST['id_usuario'] ?? null;
    $nombre           = trim($_POST['nombre']);
    $apellidos        = trim($_POST['apellidos']);
    $correo           = trim($_POST['correo']);
    $telefono         = trim($_POST['telefono']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
    $direccion        = trim($_POST['direccion']);
    $especialidad     = trim($_POST['especialidad']);

    // Preparar sentencia UPDATE
    $stmt = $pdo->prepare("UPDATE personal SET 
        id_usuario = :id_usuario,
        nombre = :nombre,
        apellidos = :apellidos,
        correo = :correo,
        telefono = :telefono,
        fecha_nacimiento = :fecha_nacimiento,
        direccion = :direccion,
        especialidad = :especialidad
        WHERE id = :id");

    $stmt->execute([
        ':id'               => $id,
        ':id_usuario'       => $id_usuario,
        ':nombre'           => $nombre,
        ':apellidos'        => $apellidos,
        ':correo'           => $correo,
        ':telefono'         => $telefono,
        ':fecha_nacimiento' => $fecha_nacimiento,
        ':direccion'        => $direccion,
        ':especialidad'     => $especialidad
    ]);

    $_SESSION['success'] = 'Datos del personal actualizados correctamente.';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error al actualizar: ' . $e->getMessage();
}

header("Location: ../index.php?vista=usuarios");
exit;
