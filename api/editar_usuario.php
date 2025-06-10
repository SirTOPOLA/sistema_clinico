<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
require_once '../config/conexion.php'; // Asegúrate de que esta conexión use PDO con excepciones activadas

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Acceso no permitido.');
    }

    // Validar y sanear entradas
    $usuario_id  = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
    $empleado_id = filter_input(INPUT_POST, 'empleado_id', FILTER_VALIDATE_INT);
    $rol_id      = filter_input(INPUT_POST, 'rol_id', FILTER_VALIDATE_INT);
    $nombre      = trim($_POST['nombre'] ?? '');
    $contrasena  = trim($_POST['contrasena'] ?? '');

    if (!$usuario_id || !$empleado_id || !$rol_id) {
        throw new Exception('Faltan campos obligatorios.');
    }

    if (strlen($nombre) < 3 || strlen($nombre) > 25) {
        throw new Exception('El nombre de usuario debe tener entre 3 y 25 caracteres.');
    }

    // Verificar si el nombre de usuario ya existe (excepto el mismo usuario)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre = :nombre AND id != :id");
    $stmt->execute([
        ':nombre' => $nombre,
        ':id' => $usuario_id
    ]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('El nombre de usuario ya está en uso por otro usuario.');
    }

    // Construir SQL dinámico
    if (!empty($contrasena)) {
        // Validar la nueva contraseña
        if (strlen($contrasena) < 6) {
            throw new Exception('La nueva contraseña debe tener al menos 6 caracteres.');
        }
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET empleado_id = :empleado_id, rol_id = :rol_id, nombre = :nombre, contrasena = :contrasena WHERE id = :id";
        $params = [
            ':empleado_id' => $empleado_id,
            ':rol_id'      => $rol_id,
            ':nombre'      => $nombre,
            ':contrasena'  => $hash,
            ':id'          => $usuario_id
        ];
    } else {
        // Sin cambiar contraseña
        $sql = "UPDATE usuarios SET empleado_id = :empleado_id, rol_id = :rol_id, nombre = :nombre WHERE id = :id";
        $params = [
            ':empleado_id' => $empleado_id,
            ':rol_id'      => $rol_id,
            ':nombre'      => $nombre,
            ':id'          => $usuario_id
        ];
    }

    // Ejecutar actualización
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'mensaje' => 'Usuario actualizado correctamente.'
    ];

} catch (Exception $e) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => $e->getMessage()
    ];
}

// Redirigir siempre
header('Location: ../index.php?vista=usuarios');
exit;