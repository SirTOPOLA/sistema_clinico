<?php
session_start();
require '../config/conexion.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['error'] = "Método no permitido";
    header("Location: ../index.php?vista=consultas");
    exit;
}

// Validar campos obligatorios
$id = $_POST["id"] ?? null;
$id_usuario = $_POST["id_usuario"] ?? null;
$id_paciente = $_POST["id_paciente"] ?? null;
$motivo = trim($_POST["motivo_consulta"] ?? '');

if (!$id || !$id_usuario || !$id_paciente || empty($motivo)) {
    $_SESSION['error'] = "Faltan campos obligatorios.";
    header("Location: ../index.php?vista=consultas");
    exit;
}

$camposConsulta = [
    "temperatura", "control_cada_horas", "frecuencia_cardiaca",
    "frecuencia_respiratoria", "tension_arterial", "pulso",
    "saturacion_oxigeno", "peso_anterior", "peso_actual",
    "peso_ideal", "imc"
];

$datos = [];
foreach ($camposConsulta as $campo) {
    $datos[$campo] = $_POST[$campo] ?? null;
}

try {
    // Actualizar tabla consultas
    $sql = "UPDATE consultas SET 
        motivo_consulta = :motivo_consulta,
        temperatura = :temperatura,
        control_cada_horas = :control_cada_horas,
        frecuencia_cardiaca = :frecuencia_cardiaca,
        frecuencia_respiratoria = :frecuencia_respiratoria,
        tension_arterial = :tension_arterial,
        pulso = :pulso,
        saturacion_oxigeno = :saturacion_oxigeno,
        peso_anterior = :peso_anterior,
        peso_actual = :peso_actual,
        peso_ideal = :peso_ideal,
        imc = :imc,
        id_paciente = :id_paciente,
        id_usuario = :id_usuario
        WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":motivo_consulta" => $motivo,
        ":temperatura" => $datos['temperatura'],
        ":control_cada_horas" => $datos['control_cada_horas'],
        ":frecuencia_cardiaca" => $datos['frecuencia_cardiaca'],
        ":frecuencia_respiratoria" => $datos['frecuencia_respiratoria'],
        ":tension_arterial" => $datos['tension_arterial'],
        ":pulso" => $datos['pulso'],
        ":saturacion_oxigeno" => $datos['saturacion_oxigeno'],
        ":peso_anterior" => $datos['peso_anterior'],
        ":peso_actual" => $datos['peso_actual'],
        ":peso_ideal" => $datos['peso_ideal'],
        ":imc" => $datos['imc'],
        ":id_paciente" => $id_paciente,
        ":id_usuario" => $id_usuario,
        ":id" => $id
    ]);

    // Detalles clínicos
    $detalles = [
        'operacion', 'orina', 'defeca', 'defeca_dias', 'duerme', 'duerme_horas',
        'antecedentes_patologicos', 'alergico', 'antecedentes_familiares',
        'antecedentes_conyuge', 'control_signos_vitales'
    ];

    $valores = [];
    foreach ($detalles as $campo) {
        $valores[$campo] = $_POST[$campo] ?? null;
    }

    // Verificar si ya existe
    $stmtCheck = $pdo->prepare("SELECT id FROM detalle_consulta WHERE id_consulta = ?");
    $stmtCheck->execute([$id]);
    $existe = $stmtCheck->fetchColumn();

    if ($existe) {
        $sql2 = "UPDATE detalle_consulta SET 
            operacion = :operacion,
            orina = :orina,
            defeca = :defeca,
            defeca_dias = :defeca_dias,
            duerme = :duerme,
            duerme_horas = :duerme_horas,
            antecedentes_patologicos = :antecedentes_patologicos,
            alergico = :alergico,
            antecedentes_familiares = :antecedentes_familiares,
            antecedentes_conyuge = :antecedentes_conyuge,
            control_signos_vitales = :control_signos_vitales,
            id_usuario = :id_usuario
            WHERE id_consulta = :id_consulta";
    } else {
        $sql2 = "INSERT INTO detalle_consulta (
            operacion, orina, defeca, defeca_dias, duerme, duerme_horas,
            antecedentes_patologicos, alergico, antecedentes_familiares,
            antecedentes_conyuge, control_signos_vitales, id_usuario, id_consulta
        ) VALUES (
            :operacion, :orina, :defeca, :defeca_dias, :duerme, :duerme_horas,
            :antecedentes_patologicos, :alergico, :antecedentes_familiares,
            :antecedentes_conyuge, :control_signos_vitales, :id_usuario, :id_consulta
        )";
    }

    $stmt2 = $pdo->prepare($sql2);
    $valores['id_usuario'] = $id_usuario;
    $valores['id_consulta'] = $id;
    $stmt2->execute($valores);

    $_SESSION['success'] = "Consulta actualizada correctamente.";
    header("Location: ../index.php?vista=consultas");
    exit;

} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: ../index.php?vista=consultas");
    exit;
}
