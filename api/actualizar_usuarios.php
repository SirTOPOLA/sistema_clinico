<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/conexion.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Acceso no permitido.');
    }

    // ID del usuario a actualizar (debe venir del formulario)
    $usuario_id = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
    $empleado_id = filter_input(INPUT_POST, 'id_personal', FILTER_VALIDATE_INT);
    $rol_id      = filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT);
    $nombre      = trim($_POST['nombre_usuario'] ?? '');
    $contrasena  = trim($_POST['contrasena'] ?? '');

    if (!$usuario_id || !$empleado_id || !$rol_id) {
        throw new Exception('Faltan datos obligatorios.');
    }

    if (strlen($nombre) < 3 || strlen($nombre) > 25) {
        throw new Exception('El nombre de usuario debe tener entre 3 y 25 caracteres.');
    }

    // Validar que no se repita el nombre de usuario (excepto el mismo usuario)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = :nombre AND id != :id");
    $stmt->execute([
        ':nombre' => $nombre,
        ':id' => $usuario_id
    ]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('El nombre de usuario ya está en uso por otro usuario.');
    }

    // Construir SQL dinámicamente si la contraseña se va a actualizar
    if (!empty($contrasena)) {
        if (strlen($contrasena) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres.');
        }

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET id_personal = :empleado_id,
                id_rol = :rol_id,
                nombre_usuario = :nombre,
                password = :password
            WHERE id = :id
        ");
        $stmt->execute([
            ':empleado_id' => $empleado_id,
            ':rol_id'      => $rol_id,
            ':nombre'      => $nombre,
            ':password'    => $hash,
            ':id'          => $usuario_id
        ]);
    } else {
        // Actualizar sin cambiar la contraseña
        $stmt = $pdo->prepare("
            UPDATE usuarios 
            SET id_personal = :empleado_id,
                id_rol = :rol_id,
                nombre_usuario = :nombre
            WHERE id = :id
        ");
        $stmt->execute([
            ':empleado_id' => $empleado_id,
            ':rol_id'      => $rol_id,
            ':nombre'      => $nombre,
            ':id'          => $usuario_id
        ]);
    }

    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'mensaje' => 'Usuario actualizado correctamente.'
    ];

} catch (Exception $e) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Error al actualizar usuario: ' . $e->getMessage()
    ];
}

header('Location: ../index.php?vista=usuarios');
exit;
