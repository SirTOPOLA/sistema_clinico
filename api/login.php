<?php
if (session_status() == PHP_SESSION_NONE) {
  // Si la sesión no está iniciada, se inicia
  session_start();
}
require_once '../includes/conexion.php';
require_once '../components/auth.php';
header('Content-Type: application/json');
// Validar datos POST
$correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_STRING);
$contrasena = $_POST['contrasena'] ?? '';

if (!$correo || strlen($contrasena) < 6) {
    http_response_code(400);
    echo json_encode(['status' => true, 'menssage' => 'Datos inválidos']);
    exit;
}

// Autenticar
$usuario = autenticarUsuario($correo, $contrasena);

if ($usuario) {
    iniciarSesionSegura($usuario);
    echo json_encode(['status' => true, 'message' => 'Bienvenido '.$usuario['usuario']['usuario'],'data' => $usuario]);
} else {
    http_response_code(401);
    echo json_encode(['status' => false , 'message' => 'Credenciales incorrectas']);
}
