<?php
session_start();
require '../config/conexion.php';  // Asegúrate de tener la conexión PDO en esta ruta

try {
    // Recoger datos del formulario
    $id_usuario = $_POST['id_usuario'];
    $id_consulta = $_POST['id_consulta'];
    $id_paciente = $_POST['id_paciente'];
    $tipos_prueba = explode(',', $_POST['tipos_prueba_seleccionados']);
    $codigo_paciente = $_POST['codigo_paciente'];
    $estado = 0;
 

    // Validación rápida
    if (!$id_consulta || !$id_paciente || count($tipos_prueba) == 0) {
        $_SESSION['error'] = "Faltan datos obligatorios.";
        header("Location: ../index.php?vista=analiticas");
        exit;
    }

    // Insertar una fila por cada tipo de prueba
    $stmt = $pdo->prepare("INSERT INTO analiticas (estado,id_tipo_prueba, id_consulta, id_usuario, id_paciente, codigo_paciente) 
                           VALUES (:estado,:id_tipo_prueba, :id_consulta, :id_usuario, :id_paciente, :codigo_paciente)");

    foreach ($tipos_prueba as $tipo) {
        $stmt->execute([
            ':estado' => $estado,
            ':id_tipo_prueba' => $tipo,
            ':id_consulta' => $id_consulta,
            ':id_usuario' => $id_usuario,
            ':id_paciente' => $id_paciente,
            ':codigo_paciente' => $codigo_paciente
           
        ]);
    }

    $_SESSION['success'] = "Analítica guardada correctamente.";
} catch (Exception $e) {
    $_SESSION['error'] = "Error al guardar: " . $e->getMessage();
}

header('Location: ../index.php?vista=analiticas');
exit;
