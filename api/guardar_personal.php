<?php
session_start();
require '../config/conexion.php'; // Asegúrate que la conexión PDO esté aquí

try {
    // Validar campos obligatorios
    if (empty($_POST['nombre']) || empty($_POST['apellidos'])) {
        $_SESSION['error'] = 'El nombre y los apellidos son obligatorios.';
        header("Location: ../index.php?vista=empleados");
        exit;
    }

    // Recoger y sanitizar datos
    $id_usuario       = $_POST['id_usuario'] ?? null;
    $nombre           = trim($_POST['nombre']);
    $apellidos        = trim($_POST['apellidos']);
    $correo           = trim($_POST['correo']);
    $telefono         = trim($_POST['telefono']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
    $direccion        = trim($_POST['direccion']);
    $especialidad     = trim($_POST['especialidad']);

    // Generar código automático de 8 caracteres
    $siglasNombre  = strtoupper(substr($nombre, 0, 1));
    $siglasApellido = strtoupper(substr($apellidos, 0, 1));
    $fecha = date('ymd'); // AñoMesDía
    $stmt = $pdo->query("SELECT MAX(id) AS ultimo_id FROM personal");
    $ultimoId = ($stmt->fetch(PDO::FETCH_ASSOC)['ultimo_id'] ?? 0) + 1;

    // Asegurar longitud máxima de 8 caracteres
    $codigo = substr($siglasNombre . $siglasApellido . $fecha . str_pad($ultimoId, 2, '0', STR_PAD_LEFT), 0, 8);

    // Insertar en la base de datos
    $stmt = $pdo->prepare("INSERT INTO personal 
        (nombre, apellidos, fecha_nacimiento, direccion, correo, telefono, especialidad, codigo, id_usuario) 
        VALUES 
        (:nombre, :apellidos, :fecha_nacimiento, :direccion, :correo, :telefono, :especialidad, :codigo, :id_usuario)");

    $stmt->execute([
        ':nombre'           => $nombre,
        ':apellidos'       => $apellidos,
        ':fecha_nacimiento'           => $fecha_nacimiento,
        ':direccion'        => $direccion,
        ':correo'           => $correo,
        ':telefono'         => $telefono,
        ':especialidad' => $especialidad,
        ':codigo'        => $codigo,
        ':id_usuario'     => $id_usuario,
    ]);

    $_SESSION['success'] = "Personal registrado con éxito. Código: $codigo";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error al guardar: " . $e->getMessage();
}

header("Location: ../index.php?vista=empleados");
exit;
