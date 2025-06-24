<?php
require('../fpdf/fpdf.php');
require '../config/conexion.php'; // Incluye tu conexión PDO

$id_paciente = $_GET['id'] ?? null;
$fecha = $_GET['fecha'] ?? null;

if (!$id_paciente || !$fecha) {
    die("Datos inválidos");
}

// Obtener datos del paciente
$sqlPaciente = "SELECT nombre, apellidos, codigo FROM pacientes WHERE id = ?";
$stmt = $pdo->prepare($sqlPaciente);
$stmt->execute([$id_paciente]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no encontrado");
}

// Obtener analíticas pagadas para ese paciente y fecha
$sqlPruebas = "SELECT tp.nombre AS tipo_prueba, tp.precio 
               FROM analiticas a
               JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id
               WHERE a.id_paciente = ? AND a.pagado = 1 AND DATE(a.fecha_registro) = ?";
$stmt = $pdo->prepare($sqlPruebas);
$stmt->execute([$id_paciente, $fecha]);
$pruebas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular total
$total = array_sum(array_column($pruebas, 'precio'));

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// LOGO
$pdf->Image('../img/logo2.jpg', 10, 10, 30);

// Nombre Clínica
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'CLINICA DOCTOR OSCAR', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Elanguema, Malabo', 0, 1, 'C');
$pdf->Ln(10);

// Título
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Factura de Pago de Pruebas Medicas', 0, 1, 'C');
$pdf->Ln(5);

// Datos del paciente
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 10, 'Paciente: ' . $paciente['nombre'] . ' ' . $paciente['apellidos']);
$pdf->Cell(0, 10, 'Código: ' . $paciente['codigo'], 0, 1);
$pdf->Cell(100, 10, 'Fecha: ' . date('d/m/Y', strtotime($fecha)), 0, 1);
$pdf->Ln(5);

// Tabla
$pdf->SetFillColor(230, 230, 230);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(140, 10, 'Tipo de Prueba', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Precio (FCFA)', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 12);
foreach ($pruebas as $p) {
    $pdf->Cell(140, 10, utf8_decode($p['tipo_prueba']), 1);
    $pdf->Cell(40, 10, number_format($p['precio'], 0, ',', '.'), 1, 1, 'R');
}

// Total
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(140, 10, 'Total', 1);
$pdf->Cell(40, 10, number_format($total, 0, ',', '.') . ' FCFA', 1, 1, 'R');

// Pie
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Factura generada el ' . date('d/m/Y H:i'), 0, 1, 'R');

// Salida
$pdf->Output('I', 'factura.pdf');
