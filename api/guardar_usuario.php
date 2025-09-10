<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
require_once '../config/conexion.php'; // Asegúrate de que esta conexión use PDO con excepciones activadas

try {
    // Verificar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Acceso no permitido.');
    }
   
    // Validar y sanear campos
    $empleado_id = filter_input(INPUT_POST, 'id_personal', FILTER_VALIDATE_INT);
    $rol_id      = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);
    $nombre      = trim($_POST['nombre_usuario'] ?? '');
    $contrasena  = trim($_POST['contrasena'] ?? '');

    // Validaciones manuales
    if (!$empleado_id || !$rol_id) {
        throw new Exception('Empleado y rol son obligatorios.');
    }

    if (strlen($nombre) < 3 || strlen($nombre) > 25) {
        throw new Exception('El nombre de usuario debe tener entre 3 y 25 caracteres.');
    }

    if (strlen($contrasena) < 5) {
        throw new Exception('La contraseña debe tener al menos 6 caracteres.');
    }

    // Validar existencia de nombre de usuario
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = :nombre");
    $stmt->execute([':nombre' => $nombre]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('El nombre de usuario ya está en uso.');
    }

    // Hashear contraseña
    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
 
    // Insertar usuario
    $stmt = $pdo->prepare("INSERT INTO usuarios (id_personal, id_rol, nombre_usuario, password)
                           VALUES (:empleado_id, :rol_id, :nombre, :contrasena)");
    $stmt->execute([
        ':empleado_id' => $empleado_id,
        ':rol_id'      => $rol_id,
        ':nombre'      => $nombre,
        ':contrasena'  => $hash
    ]);

    // Mensaje de éxito
    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'mensaje' => 'Usuario registrado correctamente.'
    ];

} catch (Exception $e) {
    // Mensaje de error
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => $e->getMessage()
    ];
}

// Redirigir siempre
header('Location: ../index.php?vista=usuarios');
exit;
