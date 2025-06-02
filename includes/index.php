<?php
if (session_status() == PHP_SESSION_NONE) {
  // Si la sesión no está iniciada, se inicia
  session_start();
}
// Si no hay sesión → redirige a login
if (!isset($_SESSION['usuario']) ) {
   
    header("Location: ../index.php");
    exit;
}else{
    header("Location: ../admin/index.php");
    exit;

}
 
?>