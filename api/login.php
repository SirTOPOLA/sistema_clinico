<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once('../config/conexion.php');
include_once('../helpers/auth.php');

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alerta'] = ['tipo' => 'danger', 'mensaje' => 'Método no permitido.'];
    header('Location: ../index.php?vista=login');
    exit;
}

// Obtener y limpiar datos del formulario
$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
$contrasena = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';

// Validaciones
if (empty($usuario)) {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'Se necesita un nombre de usuario.'];
    header('Location: ../index.php?vista=login');
    exit;
}
if (empty($contrasena)) {
    $_SESSION['alerta'] = ['tipo' => 'warning', 'mensaje' => 'Se necesita una contraseña.'];
    header('Location: ../index.php?vista=login');
    exit;
}


// Intentar login
if (login($pdo, $usuario, $contrasena)) {
    header('Location: ../index.php?vista=dashboard');
    exit;
} else {
    header('Location: ../index.php?vista=login');
    exit;
}
?>