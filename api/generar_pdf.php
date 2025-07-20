<?php
require('../fpdf/fpdf.php');
require('../config/conexion.php');

$id_paciente = $_GET['id_paciente'];
$inicio = $_GET['inicio'] ?? null;
$fin = $_GET['fin'] ?? null;

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,"Historial Médico del Paciente",0,1,'C');
$pdf->Ln(5);

// Datos del paciente
$pac = $pdo->query("SELECT * FROM pacientes WHERE id = $id_paciente")->fetch();
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,10,"Nombre: {$pac['nombre']} {$pac['apellidos']}",0,1);
$pdf->Cell(0,10,"DIP: {$pac['dip']} - Sexo: {$pac['sexo']} - Nacimiento: {$pac['fecha_nacimiento']}",0,1);
$pdf->Ln(4);

// Consultas
$cond = "";
if ($inicio && $fin) {
  $cond = "AND fecha_registro BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
}
$consultas = $pdo->query("SELECT * FROM consultas WHERE id_paciente = $id_paciente $cond ORDER BY fecha_registro DESC");

while ($c = $consultas->fetch()) {
  $pdf->SetFont('Arial','B',11);
  $pdf->Cell(0,10,"Consulta: {$c['fecha_registro']}",0,1);
  $pdf->SetFont('Arial','',10);
  $pdf->MultiCell(0,6,"Motivo: {$c['motivo_consulta']}\nTemperatura: {$c['temperatura']}ºC\nPresión: {$c['tension_arterial']} - FC: {$c['frecuencia_cardiaca']} - FR: {$c['frecuencia_respiratoria']}",0);
  $pdf->Ln(3);
}

// Analíticas
$pdf->AddPage();
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,"Analíticas",0,1);
$analiticas = $pdo->query("SELECT a.*, t.nombre AS tipo FROM analiticas a JOIN tipo_pruebas t ON a.id_tipo_prueba = t.id WHERE a.id_paciente = $id_paciente $cond ORDER BY a.fecha_registro DESC");

while ($a = $analiticas->fetch()) {
  $pdf->SetFont('Arial','B',10);
  $pdf->Cell(0,7,"{$a['tipo']} ({$a['fecha_registro']})",0,1);
  $pdf->SetFont('Arial','',10);
  $pdf->MultiCell(0,6,"Resultado: {$a['resultado']}\nValores referencia: {$a['valores_refencia']}",0);
  $pdf->Ln(2);
}

$pdf->Output("I", "historial_paciente_$id_paciente.pdf");
