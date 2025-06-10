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
    $empleado_id = filter_input(INPUT_POST, 'empleado_id', FILTER_VALIDATE_INT);
    $nombre      = trim($_POST['nombre'] ?? '');
    $dni         = trim($_POST['dni'] ?? '');
    $direccion   = trim($_POST['direccion'] ?? null);
    $telefono    = trim($_POST['telefono'] ?? null);

    // Validaciones básicas
    if (!$empleado_id) {
        throw new Exception('ID de empleado inválido.');
    }

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

    // Verificar que el empleado exista
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE id = :id");
    $stmt->execute([':id' => $empleado_id]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('Empleado no encontrado.');
    }

    // Verificar que no exista otro empleado con el mismo DNI (excepto este empleado)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE dni = :dni AND id != :id");
    $stmt->execute([':dni' => $dni, ':id' => $empleado_id]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Otro empleado ya está registrado con ese DNI.');
    }

    // Actualizar empleado
    $stmt = $pdo->prepare("UPDATE empleados SET nombre = :nombre, dni = :dni, direccion = :direccion, telefono = :telefono WHERE id = :id");
    $stmt->execute([
        ':nombre'    => $nombre,
        ':dni'       => $dni,
        ':direccion' => $direccion ?: null,
        ':telefono'  => $telefono ?: null,
        ':id'        => $empleado_id
    ]);

    // Mensaje de éxito
    $_SESSION['alerta'] = [
        'tipo' => 'success',
        'mensaje' => 'Empleado actualizado correctamente.'
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
