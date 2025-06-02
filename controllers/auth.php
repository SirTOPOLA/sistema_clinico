
<?php
// Verificar si la sesión ya está iniciada
if (session_status() == PHP_SESSION_NONE) {
    // Si la sesión no está iniciada, se inicia
    session_start();
}
 
 
 
/**
 * Login específico para Internos.
 * Busca en la tabla `usuarios` usando un campo `usuario` y `password_hash`.
 */



 function login($pdo, $email, $password)
 {
     // Consulta preparada
     $stmt = $pdo->prepare("
         SELECT 
             u.*, 
             r.nombre AS rol, 
             e.nombre AS nombre_empleado
         FROM usuarios u
         INNER JOIN empleados e ON u.empleado_id = e.id
         INNER JOIN roles r ON u.rol_id = r.id
         WHERE u.usuario = ?
     ");
   
     $stmt->execute([$email]);
     $user = $stmt->fetch(PDO::FETCH_ASSOC);
 
     // Verifica si se encontró el usuario
     if ($user) {
         if (password_verify($password, $user['password'])) {
             if ($user['activo'] == 1) {
                 // Usuario válido y activo
                 $_SESSION['usuario'] = [
                     'id'      => $user['id'],
                     'nombre'  => $user['nombre_empleado'],
                     'usuario' => $user['usuario'], 
                     'rol'     => $user['rol']
                 ];
                 $_SESSION['alerta'] = "Inicio de sesión exitoso.";
                 return true;
                } else {
                 // Usuario con credenciales correctas pero no activo
                 $_SESSION['alerta'] = "Tu cuenta está inactiva. Contacta al administrador.";
                 return false;
                }
            }
        }
        
        // Credenciales incorrectas
        $_SESSION['alerta'] = "Usuario o contraseña incorrectos.";
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
        header('Location: index.php?vista=inicio');
        exit;
    }
    
    
    /**
     * Login específico para clientes.
     * Busca en la tabla `clientes` usando un campo `codigo_acceso`  .
     */
    function loginCliente($pdo, $codigo): bool
    {
        // Suponiendo que $pdo es un objeto PDO ya conectado
        $stmt = $pdo->prepare("
        SELECT id, codigo, telefono, direccion, nombre, email, codigo_acceso 
        FROM clientes 
        WHERE codigo_acceso = ?
        ");
        
        $stmt->execute([$codigo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $_SESSION['usuario'] = [
                'id' => $user['id'],
            'codigo' => $user['codigo'],
            'telefono' => $user['telefono'],
            'direccion' => $user['direccion'],
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'codigo_acceso' => $user['codigo_acceso'],
            'rol' => 'cliente'
        ];
        $_SESSION['alerta'] = "Inicio de sesión exitoso.";
        return true;
    }
    
    // Usuario con credenciales correctas pero no activo
    $_SESSION['alerta'] = "Credenciales de cliente incorrectas.";
    return false;
    
    
    
    
}
