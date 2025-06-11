<?php 
 if (session_status() == PHP_SESSION_NONE) { 
     session_start();
 }  

require_once 'config/conexion.php';
require_once 'helpers/permisos.php'; 
require_once 'helpers/auth.php'; 
require_once 'components/alerta.php';   
 


$publicas = ['login'];  // vistas públicas

$vista = $_GET['vista'] ?? 'dashboard';

if (in_array($vista, $publicas)) {
    // --- PÚBLICO ---
    include 'layout/headerLogin.php';
    include "{$vista}.php";
    include 'layout/footerLogin.php';
} else {
    // --- PRIVADO ---
    verificarAcceso($vista);
    include 'layout/header.php'; 
    include 'layout/sidebar.php';
    include 'layout/navbar.php';
    include "views/{$vista}.php";
    include 'layout/footer.php';
}


