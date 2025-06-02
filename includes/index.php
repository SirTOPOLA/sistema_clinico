<?php

// Si no hay sesión → redirige a login
if (!isset($_SESSION['usuario']) || isset($_SESSION['usuario'])) {
    $_SESSION['alerta'] = "Debes registrarte para continuar con esta petición.";
    header("Location: ../../login.php");
    exit;
}
 
?>