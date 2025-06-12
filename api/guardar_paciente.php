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
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $apellidos = htmlspecialchars(trim($_POST['apellidos']));
        $fecha_nacimiento = $_POST['fecha_nacimiento'];
        $dip = htmlspecialchars(trim($_POST['dip']));
        $sexo = htmlspecialchars (trim($_POST['sexo']));
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $telefono = htmlspecialchars(trim($_POST['telefono']));
        $profesion = htmlspecialchars(trim($_POST['profesion']));
      
        $ocupacion = htmlspecialchars(trim($_POST['ocupacion']));
        $tutor = htmlspecialchars(trim($_POST['tutor_nombre']));
        $telefono_tutor = htmlspecialchars(trim($_POST['telefono_tutor']));
        $residencia = htmlspecialchars(trim($_POST['direccion']));
        $id_usuario = (int) $_POST['id_usuario'];

        $fecha_registro= date('Y-m-d H:i:s');

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




        print_r($_POST);

        echo $residencia;

      

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

        // Generar código único del paciente
        $fecha = str_replace('-', '', $fecha_nacimiento);
        $base = strtoupper(substr($nombre, 0, 1) . substr($apellidos, 0, 1));
        $codigo_paciente = $base . $fecha . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);




        $sql = "INSERT INTO pacientes (
                    codigo, nombre, apellidos, fecha_nacimiento,dip, sexo, direccion, email, telefono, 
                    profesion, ocupacion, tutor_nombre, telefono_tutor, id_usuario
                ) VALUES (
                    :codigo, :nombre, :apellidos, :fecha_nacimiento, :dip, :sexo, :direccion, :email, :telefono,
                    :profesion, :ocupacion, :tutor_nombre,  :telefono_tutor, :id_usuario
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':codigo' => $codigo_paciente,
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':fecha_nacimiento' => $fecha_nacimiento,
            ':dip' =>$dip,
            'sexo'=> $sexo,
            ':direccion' =>$residencia,
            ':email' => $email,
            ':telefono' => $telefono,
            ':profesion' => $profesion,
            ':ocupacion' => $ocupacion,
            ':tutor_nombre' => $tutor,
            ':telefono_tutor' => $telefono_tutor,
            ':id_usuario' => $id_usuario
           
        ]);

        $_SESSION['success'] = 'Paciente registrado correctamente.';
        header('Location: ../index.php?vista=pacientes');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error al registrar paciente: ' . $e->getMessage();
        header('Location: ../index.php?vista=pacientes');
        exit;
    }
} else {
    $_SESSION['error'] = 'Método de solicitud no permitido.';
    header('Location: ../index.php?vista=pacientes');
    exit;
}
?>
