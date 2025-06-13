<?php 
if (session_status() == PHP_SESSION_NONE) { 
    session_start();
}


function verificarAcceso($vista)
{
   
    
    // Si no hay sesión → redirige a login
    if (!isset($_SESSION['usuario'])) {
        header("Location: index.php?vista=login");
        exit;
    }
    // Validar que el usuario esté activo
  /*   if (isset($_SESSION['usuario']['estado']) && $_SESSION['usuario']['estado'] !== 'activo') {
        $_SESSION['alerta'] = "Tu cuenta no está activa. Contacta al administrador.";
        header('Location: index.php?vista=login');
        exit;
    } */
    // Normalizar el rol a minúsculas
    $rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

    // Estructura de permisos (todos en minúsculas)
    $permisos = [
        'administrador' => [
            'dashboard' ,  
            'usuarios' ,  
            'pacientes' ,  
            'recetas' ,  
            'consultas' ,  
            'tipo_prueba' ,  
            'salas' ,  
            'pagos' ,  
            'analiticas' ,  
            'ingresos' ,  
            'detalles_consultas' ,  
            'empleados' ,  
        ],
        'secretaria' => [
            'dashboard' 
        ],  
        'triaje' => [
            'dashboard'
        ],
        'laboratorio' => [
            'dashboard'
        ],
        'urgencias' => [
            'dashboard'
        ],

    ];


    // Validación del nombre de la vista para prevenir rutas maliciosas
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $vista)) {
   
        $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'La vista solicitada no es válida.'];
        header("Location: index.php?vista=dashboard");
        exit;
    }
    
    // Si el rol no tiene permiso para esta vista → redirige a dashboard
    if (!in_array($vista, $permisos[$rol] ?? [])) { 
        $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'No tienes permiso para acceder a la vista solicitada.'];
        header("Location: index.php?vista=dashboard");
        exit;
    }
}



/**
 * Verifica el acceso del usuario autenticado, su rol y conexión a la base de datos.
 *
 * @param array $rolesPermitidos Lista de roles válidos (ej: ['admin', 'archivista'])
 * @param PDO $pdo Instancia de la conexión PDO
 */
function ValidarAcceso(array $rolesPermitidos, PDO $pdo)
{
    // Iniciar sesión si aún no está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Validar existencia de sesión de usuario
    if (empty($_SESSION['usuario'])) {
        $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'Debes iniciar sesión para continuar.'];
       
        header('Location: index.php?vista=login');
        exit;
    }



    // Validar rol del usuario
    if (!in_array($_SESSION['usuario']['rol'], $rolesPermitidos)) {
        $_SESSION['alerta'] = "No tienes permisos para acceder a esta sección.";
        header('Location: index.php?vista=login');
        exit;
    }

    // Validar que la conexión PDO exista y sea válida
    if (!$pdo instanceof PDO) {
        $_SESSION['alerta'] = "Error interno de conexión. Intenta más tarde.";
        header('Location: index.php?vista=login');
        exit;
    }
}

