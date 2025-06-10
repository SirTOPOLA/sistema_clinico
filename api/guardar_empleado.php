<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
require_once '../config/conexion.php'; // PDO con excepciones activadas

try {
    // Sólo aceptar método POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Acceso no permitido.');
    }

    // Validar y sanear campos
    $nombre    = trim($_POST['nombre'] ?? '');
    $dni       = trim($_POST['dni'] ?? '');
    $direccion = trim($_POST['direccion'] ?? null);
    $telefono  = trim($_POST['telefono'] ?? null);

    // Validaciones obligatorias
    if (empty($nombre)) {
        throw new Exception('El nombre es obligatorio.');
    }
    if (mb_strlen($nombre) > 100) {
        throw new Exception('El nombre no debe superar los 100 caracteres.');
    }

    if (empty($dni)) {
        throw new Exception('El DNI es obligatorio.');
    }
    if (mb_strlen($dni) > 15) {
        throw new Exception('El DNI no debe superar los 15 caracteres.');
    }

    if ($direccion !== null && mb_strlen($direccion) > 150) {
        throw new Exception('La dirección no debe superar los 150 caracteres.');
    }

    if ($telefono !== null && mb_strlen($telefono) > 20) {
        throw new Exception('El teléfono no debe superar los 20 caracteres.');
    }

    // Verificar que no exista otro empleado con el mismo DNI
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE dni = :dni");
    $stmt->execute([':dni' => $dni]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Ya existe un empleado registrado con ese DNI.');
    }

    // Insertar empleado
    $stmt = $pdo->prepare("INSERT INTO empleados (nombre, dni, direccion, telefono)
                           VALUES (:nombre, :dni, :direccion, :telefono)");
    $stmt->execute([
        ':nombre'    => $nombre,
        ':dni'       => $dni,
        ':direccion' => $direccion ?: null,
        ':telefono'  => $telefono ?: null,
    ]);

    // Mensaje de éxito
    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'mensaje' => 'Empleado registrado correctamente.'
    ];

} catch (Exception $e) {
    // Mensaje de error
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => $e->getMessage()
    ];
}

// Redirigir al listado de empleados
header('Location: ../index.php?vista=empleados');
exit;
