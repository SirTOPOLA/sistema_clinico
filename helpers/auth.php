<?php
// Verificar si la sesión ya está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


/**
 * Login específico para Internos.
 * Busca en la tabla `usuarios` usando un campo `usuario` y `password_hash`.
 */
function login($pdo, $usuario, $contrasena)
{
    // Consulta para obtener datos del usuario, su rol y datos del personal
    $sql = "SELECT 
                u.id,
                u.nombre_usuario AS username,
                u.password,
                u.fecha_registro AS ingreso,
                CONCAT(p.nombre, ' ', p.apellidos) AS nombre_completo,
                r.nombre AS rol
            FROM usuarios u
            LEFT JOIN personal p ON u.id_personal = p.id
            LEFT JOIN roles r ON u.id_rol = r.id
            WHERE u.nombre_usuario = ?
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contrasena, $user['password'])) {
        // Sesión iniciada correctamente
        $_SESSION['usuario'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'nombre' => $user['nombre_completo'] ?? 'Sin nombre asignado',
            'rol' => $user['rol'] ?? 'Sin rol asignado',
            'ingreso' => $user['ingreso']
        ];

        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensaje' => 'Inicio de sesión exitoso.'
        ];

        return true;
    }

    // Usuario no encontrado o contraseña inválida
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Usuario o contraseña incorrectos.'
    ];

    return false;
}


/**
 * Destruccion del login.
 *    .
 */
function logout()
{
    session_unset();
    session_destroy();

    header('Location: index.php?vista=login');
    exit;
}


