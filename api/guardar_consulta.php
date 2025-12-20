<?php
session_start();
require '../config/conexion.php';

function validar_campos_requeridos($datos) {
    foreach ($datos as $campo => $valor) {
        if (empty(trim($valor))) {
            return "El campo '$campo' es obligatorio.";
        }
    }
    return true;
}

function validar_rangos($datos) {
    if ($datos['temperatura'] < 30 || $datos['temperatura'] > 45) return "Temperatura fuera de rango.";
    if ($datos['frecuencia_cardiaca'] < 30 || $datos['frecuencia_cardiaca'] > 200) return "Frecuencia cardíaca fuera de rango.";
    if ($datos['frecuencia_respiratoria'] < 5 || $datos['frecuencia_respiratoria'] > 60) return "Frecuencia respiratoria fuera de rango.";
    if ($datos['pulso'] < 30 || $datos['pulso'] > 200) return "Pulso fuera de rango.";
    if ($datos['saturacion_oxigeno'] < 50 || $datos['saturacion_oxigeno'] > 100) return "Saturación de oxígeno fuera de rango.";
    if ($datos['peso_anterior'] < 1 || $datos['peso_anterior'] > 300) return "Peso anterior fuera de rango.";
    if ($datos['peso_actual'] < 1 || $datos['peso_actual'] > 300) return "Peso actual fuera de rango.";
    if ($datos['peso_ideal'] < 1 || $datos['peso_ideal'] > 300) return "Peso ideal fuera de rango.";
    if ($datos['imc'] < 5 || $datos['imc'] > 80) return "IMC fuera de rango."; 
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_usuario = (int) $_POST['id_usuario'];
        $id_paciente = (int) $_POST['id_paciente'];
        $precio = (int) $_POST['precio'];
        $motivo_consulta = trim($_POST['motivo_consulta']);

        // Datos clínicos
        $datos = [
            'temperatura' => floatval($_POST['temperatura']),
            'control_cada_horas' => intval($_POST['control_cada_horas']),
            'frecuencia_cardiaca' => intval($_POST['frecuencia_cardiaca']),
            'frecuencia_respiratoria' => intval($_POST['frecuencia_respiratoria']),
            'tension_arterial' => trim($_POST['tension_arterial']),
            'pulso' => intval($_POST['pulso']),
            'saturacion_oxigeno' => floatval($_POST['saturacion_oxigeno']),
            'peso_anterior' => floatval($_POST['peso_anterior']),
            'peso_actual' => floatval($_POST['peso_actual']),
            'peso_ideal' => floatval($_POST['peso_ideal']),
            'imc' => floatval($_POST['imc']),
          
        ];

        $validacion = validar_campos_requeridos([
            'Paciente' => $id_paciente,
            'Motivo de consulta' => $motivo_consulta,
        ]);

        if ($validacion !== true) {
            $_SESSION['error'] = $validacion;
            header('Location: ../index.php?vista=consultas');
            exit;
        }

        // Solo validar rangos si los campos tienen valor
        foreach ($datos as $k => $v) {
            if (!empty($v) && is_numeric($v)) {
                $validacion_rangos = validar_rangos($datos);
                if ($validacion_rangos !== true) {
                    $_SESSION['error'] = $validacion_rangos;
                    header('Location: ../index.php?vista=consultas');
                    exit;
                }
            }
        }

        $fecha_registro = date('Y-m-d H:i:s');

        // Insertar en la tabla consultas
        $sqlConsulta = "INSERT INTO consultas (
            motivo_consulta, temperatura, control_cada_horas, frecuencia_cardiaca,
            frecuencia_respiratoria, tension_arterial, pulso, saturacion_oxigeno,
            peso_anterior, peso_actual, peso_ideal, imc,
            id_paciente, id_usuario, fecha_registro, precio
        ) VALUES (
            :motivo, :temp, :control, :fc, :fr, :ta, :pulso, :so,
            :peso_ant, :peso_act, :peso_ideal, :imc,
            :paciente, :usuario, :fecha, :precio
        )";

        $stmtConsulta = $pdo->prepare($sqlConsulta);
        $stmtConsulta->execute([
            ':motivo' => $motivo_consulta,
            ':temp' => $datos['temperatura'],
            ':control' => $datos['control_cada_horas'],
            ':fc' => $datos['frecuencia_cardiaca'],
            ':fr' => $datos['frecuencia_respiratoria'],
            ':ta' => $datos['tension_arterial'],
            ':pulso' => $datos['pulso'],
            ':so' => $datos['saturacion_oxigeno'],
            ':peso_ant' => $datos['peso_anterior'],
            ':peso_act' => $datos['peso_actual'],
            ':peso_ideal' => $datos['peso_ideal'],
            ':imc' => $datos['imc'],
            ':paciente' => $id_paciente,
            ':usuario' => $id_usuario,
            ':fecha' => $fecha_registro,
            ':precio' => $precio
        ]);

        $id_consulta = $pdo->lastInsertId();

        // Insertar en la tabla detalle_consulta
        $sqlDetalle = "INSERT INTO detalle_consulta (
            operacion, orina, defeca, defeca_dias, duerme, duerme_horas,
            antecedentes_patologicos, alergico, antecedentes_familiares, antecedentes_conyuge,
            control_signos_vitales, id_consulta, id_usuario, fecha_registro
        ) VALUES (
            :operacion, :orina, :defeca, :defeca_dias, :duerme, :duerme_horas,
            :antecedentes_patologicos, :alergico, :antecedentes_familiares, :antecedentes_conyuge,
            :control_signos_vitales, :id_consulta, :usuario, :fecha
        )";

        $stmtDetalle = $pdo->prepare($sqlDetalle);
        $stmtDetalle->execute([
            ':operacion' => trim($_POST['operacion']),
            ':orina' => trim($_POST['orina']),
            ':defeca' => trim($_POST['defeca']),
            ':defeca_dias' => intval($_POST['defeca_dias']),
            ':duerme' => trim($_POST['duerme']),
            ':duerme_horas' => intval($_POST['duerme_horas']),
            ':antecedentes_patologicos' => trim($_POST['antecedentes_patologicos']),
            ':alergico' => trim($_POST['alergico']),
            ':antecedentes_familiares' => trim($_POST['antecedentes_familiares']),
            ':antecedentes_conyuge' => trim($_POST['antecedentes_conyuge']),
            ':control_signos_vitales' => trim($_POST['control_signos_vitales']),
            ':id_consulta' => $id_consulta,
            ':usuario' => $id_usuario,
            ':fecha' => $fecha_registro
        ]);

        $_SESSION['success'] = "Consulta registrada correctamente.";
        header('Location: ../index.php?vista=consultas');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Error al registrar consulta: " . $e->getMessage();
        header('Location: ../index.php?vista=consultas');
        exit;
    }
} else {
    $_SESSION['error'] = "Solicitud no válida.";
    header('Location: ../index.php?vista=consultas');
    exit;
}
