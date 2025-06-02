<?php
// api/auth.php

require_once '../includes/conexion.php'; // PDO $pdo

/**
 * Valida las credenciales del usuario.
 * @param string $usuario
 * @param string $contrasena
 * @return array|null Retorna los datos del usuario o null si falla.
 */
function autenticarUsuario($usuario, $contrasena)
{
    global $pdo;

    $sql = "SELECT id,  usuario, contrasena FROM usuarios WHERE usuario = :usuario AND estado = 1 LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica la contraseña usando password_verify
        if (password_verify($contrasena, $usuario['contrasena'])) {
            unset($usuario['contrasena']); // Nunca devolver contraseñas
            
            return $usuario;
        }
        
    }

    return null;
}

/**
 * Inicia sesión de forma segura.
 * @param array $usuario Datos del usuario autenticado
 */
function iniciarSesionSegura(array $usuario)
{
    // Previene fijación de sesión
    session_regenerate_id(true);

    // Datos mínimos en sesión
    $_SESSION['usuario'] = [
        'id' => $usuario['id'],        
        'usuario' => $usuario['usuario']
    ];
}

/**
 * Verifica si el usuario está autenticado
 * @return bool
 */
function estaAutenticado()
{
    return isset($_SESSION['usuario']);
}

/**
 * Termina la sesión del usuario.
 */
function cerrarSesion()
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
