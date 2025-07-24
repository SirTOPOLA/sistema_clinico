<?php
require('../fpdf/fpdf.php');
require('../config/conexion.php');

$id_paciente = isset($_GET['id_paciente']) ? (int) $_GET['id_paciente'] : 0;
$inicio = isset($_GET['inicio']) ? $_GET['inicio'] : null;
$fin = isset($_GET['fin']) ? $_GET['fin'] : null;

function validar_fecha($fecha) {
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha);
}
if (($inicio && !validar_fecha($inicio)) || ($fin && !validar_fecha($fin))) {
    die("Formato de fecha inválido. Use YYYY-MM-DD.");
}

$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// --------- ENCABEZADO INSTITUCIONAL ---------
if (file_exists('../img/logo.jpg')) {
    $pdf->Image('../img/logo.jpg', 10, 10, 30);
}
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,utf8_decode("CONSULTORIO MÉDICO 'DOCTOR OSCAR SL'"),0,1,'C');
$pdf->SetFont('Arial','I',11);
$pdf->Cell(0,7,utf8_decode("'SALUD PARA TODOS'"),0,1,'C');
$pdf->SetFont('Arial','',10); 
$pdf->Cell(0,5,utf8_decode("Dirección: ELA NGUEMA C/ Francisco Esono"),0,1,'C');
$pdf->Cell(0,5,utf8_decode("Tel: 222 213694 / 555 534111  -  WhatsApp: +240 222 21 36 94"),0,1,'C');
$pdf->Ln(10);

// --------- TÍTULO GENERAL ---------
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,10,utf8_decode("Historial Médico del Paciente"),0,1,'C');
$pdf->Ln(5);

// --------- DATOS DEL PACIENTE ---------
$stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = :id");
$stmt->bindValue(':id', $id_paciente, PDO::PARAM_INT);
$stmt->execute();
$pac = $stmt->fetch();

if (!$pac) {
    die("Paciente no encontrado.");
}

$pdf->SetFont('Arial','B',11);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(190,8,utf8_decode("Datos del paciente"),1,1,'L', true);
$pdf->SetFont('Arial','',10);

$pdf->Cell(38,7,utf8_decode("Nombre completo:"),1,0,'L');
$pdf->Cell(62,7,utf8_decode("{$pac['nombre']} {$pac['apellidos']}"),1,0,'L');
$pdf->Cell(30,7,utf8_decode("DIP:"),1,0,'L');
$pdf->Cell(60,7,utf8_decode($pac['dip']),1,1,'L');

$pdf->Cell(38,7,utf8_decode("Sexo:"),1,0,'L');
$pdf->Cell(62,7,utf8_decode($pac['sexo']),1,0,'L');
$pdf->Cell(30,7,utf8_decode("Nacimiento:"),1,0,'L');
$pdf->Cell(60,7,utf8_decode($pac['fecha_nacimiento']),1,1,'L');
$pdf->Ln(6);

// --------- CONSULTAS MÉDICAS ---------
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,utf8_decode("Consultas médicas"),0,1);
$pdf->Ln(2);

$cond_consultas = "";
if ($inicio && $fin) {
    $cond_consultas = "AND fecha_registro BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
}
$consultas = $pdo->query("SELECT * FROM consultas WHERE id_paciente = $id_paciente $cond_consultas ORDER BY fecha_registro DESC");

if ($consultas->rowCount() === 0) {
    $pdf->SetFont('Arial','I',10);
    $pdf->Cell(0,7,utf8_decode("No se encontraron registros de consultas en el período indicado."),0,1);
} else {
    while ($c = $consultas->fetch()) {
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(0,7,utf8_decode("Consulta del día: " . $c['fecha_registro']),0,1);
        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(0,6,utf8_decode(
            "Motivo: {$c['motivo_consulta']}\n" .
            "Temperatura: {$c['temperatura']}ºC\n" .
            "Presión arterial: {$c['tension_arterial']} - FC: {$c['frecuencia_cardiaca']} - FR: {$c['frecuencia_respiratoria']}"
        ),0);
        $pdf->Ln(3);
    }
}

// --------- ANALÍTICAS MÉDICAS ---------
$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,utf8_decode("Pruebas analíticas realizadas"),0,1);

$cond_analiticas = "";
if ($inicio && $fin) {
    $cond_analiticas = "AND a.fecha_registro BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
}

$sql_analiticas = "
    SELECT a.*, t.nombre AS tipo
    FROM analiticas a
    JOIN tipo_pruebas t ON a.id_tipo_prueba = t.id
    WHERE a.id_paciente = $id_paciente $cond_analiticas
    ORDER BY a.fecha_registro DESC";

$analiticas = $pdo->query($sql_analiticas);

if ($analiticas->rowCount() === 0) {
    $pdf->SetFont('Arial','I',10);
    $pdf->Cell(0,7,utf8_decode("No hay analíticas registradas para el período indicado."),0,1);
} else {
    while ($a = $analiticas->fetch()) {
        // Cuadro elegante
        $pdf->SetFillColor(245, 245, 245);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(0,7,utf8_decode("{$a['tipo']} ({$a['fecha_registro']})"),0,1,'L', true);
        $pdf->SetFont('Arial','',10);

        // Resultado
        $resultado = trim($a['resultado']);
        $referencia = trim($a['valores_refencia']);

        if (empty($resultado)) {
            $pdf->SetTextColor(100, 100, 100); // gris oscuro
            $pdf->MultiCell(0,6,utf8_decode("Resultado pendiente de laboratorio."),0);
        } else {
            // Colores para resultados
            if (stripos($resultado, 'positivo') !== false) {
                $pdf->SetTextColor(200, 0, 0); // rojo
            } elseif (stripos($resultado, 'negativo') !== false) {
                $pdf->SetTextColor(0, 150, 0); // verde
            } else {
                $pdf->SetTextColor(0, 0, 0); // normal
            }

            $pdf->MultiCell(0,6,utf8_decode("Resultado: $resultado"),0);
            $pdf->SetTextColor(0, 0, 0); // restaurar color
            $pdf->MultiCell(0,6,utf8_decode("Valores de referencia: $referencia"),0);
        }

        $pdf->Ln(3);
    }
}

$pdf->Output("I", "historial_paciente_$id_paciente.pdf");
