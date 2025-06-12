<?php
session_start();
require '../config/conexion.php';

function validar_campos($datos) {
    foreach ($datos as $campo => $valor) {
        if (empty(trim($valor))) {
            return "El campo $campo es obligatorio.";
        }
    }
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_paciente = (int) $_POST['id'];
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $apellidos = htmlspecialchars(trim($_POST['apellidos']));
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $dip = htmlspecialchars(trim($_POST['dip']));
        $sexo = htmlspecialchars(trim($_POST['sexo']));
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $telefono = htmlspecialchars(trim($_POST['telefono']));
        $profesion = htmlspecialchars(trim($_POST['profesion']));
        $ocupacion = htmlspecialchars(trim($_POST['ocupacion']));
        $tutor = htmlspecialchars(trim($_POST['tutor_nombre']));
        $telefono_tutor = htmlspecialchars(trim($_POST['telefono_tutor']));
        $residencia = htmlspecialchars(trim($_POST['direccion']));
        $id_usuario = (int) $_POST['id_usuario'];

        // Validar campos obligatorios
        $validacion = validar_campos([
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'fecha_nacimiento' => $fecha_nacimiento,
            'telefono' => $telefono,
            'profesion' => $profesion,
            'ocupacion' => $ocupacion,
            'tutor_nombre' => $tutor,
            'telefono_tutor' => $telefono_tutor,
            'direccion' => $residencia,
        ]);

        if ($validacion !== true) {
            $_SESSION['error'] = $validacion;
            header('Location: ../index.php?vista=pacientes');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El correo electrónico no es válido.';
            header('Location: ../index.php?vista=pacientes');
            exit;
        }

        $sql = "UPDATE pacientes SET
                    nombre = :nombre,
                    apellidos = :apellidos,
                    fecha_nacimiento = :fecha_nacimiento,
                    dip = :dip,
                    sexo = :sexo,
                    direccion = :direccion,
                    email = :email,
                    telefono = :telefono,
                    profesion = :profesion,
                    ocupacion = :ocupacion,
                    tutor_nombre = :tutor_nombre,
                    telefono_tutor = :telefono_tutor,
                    id_usuario = :id_usuario
                WHERE id = :id_paciente";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':fecha_nacimiento' => $fecha_nacimiento,
            ':dip' => $dip,
            ':sexo' => $sexo,
            ':direccion' => $residencia,
            ':email' => $email,
            ':telefono' => $telefono,
            ':profesion' => $profesion,
            ':ocupacion' => $ocupacion,
            ':tutor_nombre' => $tutor,
            ':telefono_tutor' => $telefono_tutor,
            ':id_usuario' => $id_usuario,
            ':id_paciente' => $id_paciente
        ]);

        $_SESSION['success'] = 'Paciente actualizado correctamente.';
        header('Location: ../index.php?vista=pacientes');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al actualizar paciente: ' . $e->getMessage();
        header('Location: ../index.php?vista=pacientes');
        exit;
    }
} else {
    $_SESSION['error'] = 'Método de solicitud no permitido.';
    header('Location: ../index.php?vista=pacientes');
    exit;
}
?>
