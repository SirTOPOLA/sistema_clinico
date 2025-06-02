<?php
// ----------------------------------------------
// conexión.php
// Clase para manejar la conexión a la base de datos
// con PDO (orientado a objetos, seguro y reutilizable)
// ----------------------------------------------

// Parámetros de conexión
$host = 'localhost';           // o 127.0.0.1
$dbname = 'carpinteria_tfg'; // cambia esto por tu base
$usuario = 'root';             // usuario MySQL
$contrasena = '';              // contraseña MySQL (vacía por defecto en XAMPP)

try {
    // Opciones para PDO
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Modo de errores con excepciones
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Modo de fetch por defecto
        PDO::ATTR_EMULATE_PREPARES => false, // Desactiva la emulación de prepares (mejor seguridad)
    ];

    // Instancia PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $usuario, $contrasena, $opciones);

    // Puedes usar $pdo en otros archivos con include
    // Ejemplo: include 'conexion.php';

} catch (PDOException $e) {
    // Error al conectar
    die("Error de conexión: " . $e->getMessage());
}
?>
