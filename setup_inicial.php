<?php
require_once 'config/conexion.php';

// Verificar si ya hay usuarios
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
if ($stmt->fetchColumn() > 0) {
    header("Location: index.php?vista=login");
    exit;
}

// Insertar rol "Administrador" si no existe
$stmt = $pdo->prepare("SELECT COUNT(*) FROM roles WHERE nombre = 'Administrador'");
$stmt->execute();
if ($stmt->fetchColumn() == 0) {
    $pdo->prepare("INSERT INTO roles (nombre) VALUES ('Administrador')")->execute();
}

// Lógica de pasos
$paso_actual = 1;
$errores = [];
$id_personal = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["paso1"])) {
        // Paso 1: Registro de personal
        $nombre = trim($_POST["nombre"]);
        $apellidos = trim($_POST["apellidos"]);
        $fecha_nacimiento = $_POST["fecha_nacimiento"];
        $direccion = trim($_POST["direccion"]);
        $correo = trim($_POST["correo"]);
        $telefono = trim($_POST["telefono"]);
        $especialidad = trim($_POST["especialidad"]);
        $codigo = strtoupper(uniqid("EMP"));

        if (empty($nombre) || empty($apellidos) || empty($correo)) {
            $errores[] = "Nombre, apellidos y correo son obligatorios.";
        }

        if (empty($errores)) {
            $stmt = $pdo->prepare("INSERT INTO personal (nombre, apellidos, fecha_nacimiento, direccion, correo, telefono, especialidad, codigo) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellidos, $fecha_nacimiento, $direccion, $correo, $telefono, $especialidad, $codigo]);
            $id_personal = $pdo->lastInsertId();
            $paso_actual = 2;
        }
    } elseif (isset($_POST["paso2"])) {
        // Paso 2: Registro de usuario
        $usuario = trim($_POST["usuario"]);
        $password = $_POST["password"];
        $id_personal = $_POST["personal_id"];

        if (empty($usuario) || empty($password)) {
            $errores[] = "Usuario y contraseña son obligatorios.";
            $paso_actual = 2;
        } else {
            $stmt = $pdo->query("SELECT id FROM roles WHERE nombre = 'Administrador'");
            $id_rol = $stmt->fetchColumn();

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_usuario, id_rol, password, id_personal) VALUES (?, ?, ?, ?)");
            $stmt->execute([$usuario, $id_rol, $password_hash, $id_personal]);

            $id_usuario = $pdo->lastInsertId();
            $pdo->prepare("UPDATE personal SET id_usuario = ? WHERE id = ?")
                ->execute([$id_usuario, $id_personal]);

            header("Location: index.php?vista=login");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración Inicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="bi bi-tools me-2"></i>
            <h5 class="mb-0">Configuración Inicial del Sistema</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger"><?= implode("<br>", $errores) ?></div>
            <?php endif; ?>

            <?php if ($paso_actual === 1): ?>
                <h5 class="mb-3"><i class="bi bi-person-plus-fill text-primary me-1"></i> Paso 1: Registrar Primer Empleado</h5>
                <form method="POST" novalidate>
                    <input type="hidden" name="paso1" value="1">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre *</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Apellidos *</label>
                            <input type="text" name="apellidos" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Fecha de nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Dirección</label>
                        <textarea name="direccion" class="form-control"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Correo *</label>
                            <input type="email" name="correo" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Especialidad</label>
                        <input type="text" name="especialidad" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-arrow-right-circle me-2"></i>Continuar al Paso 2
                    </button>
                </form>
            <?php elseif ($paso_actual === 2): ?>
                <h5 class="mb-3"><i class="bi bi-person-lock text-success me-1"></i> Paso 2: Crear Usuario del Sistema</h5>
                <form method="POST" novalidate>
                    <input type="hidden" name="paso2" value="1">
                    <input type="hidden" name="personal_id" value="<?= htmlspecialchars($id_personal) ?>">
                    <div class="mb-3">
                        <label>Nombre de Usuario *</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Contraseña *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle-fill me-2"></i>Finalizar Registro e Ingresar
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
