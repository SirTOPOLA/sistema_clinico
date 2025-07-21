<?php
require('../fpdf/fpdf.php');
require '../config/conexion.php';

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

// Obtener analíticas pagadas
$sqlPruebas = "SELECT tp.nombre AS tipo_prueba, tp.precio 
               FROM analiticas a
               JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id
               WHERE a.id_paciente = ? AND a.pagado = 1 AND DATE(a.fecha_registro) = ?";
$stmt = $pdo->prepare($sqlPruebas);
$stmt->execute([$id_paciente, $fecha]);
$pruebas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total
$total = array_sum(array_column($pruebas, 'precio'));

// Crear PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Logo
$pdf->Image('../img/logo.jpg', 10, 10, 25);

// Encabezado Institucional
$pdf->SetXY(40, 10);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 7, utf8_decode("CONSULTORIO MÉDICO DOCTOR OSCAR SL"), 0, 1);
$pdf->SetFont('Arial', 'I', 11);
$pdf->Cell(0, 6, utf8_decode("'SALUD PARA TODOS'"), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode('Promotor: Doctor Oscar Bioko'), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Dirección: Ela Nguema, C/Francisco Esono'), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Tel: 222 213694 / 555 53 41 11 - WhatsApp: +240 222 21 36 94'), 0, 1);

// Línea separadora
$pdf->Ln(5);
$pdf->SetDrawColor(0);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(10);

// Título
$pdf->SetFont('Arial', 'B', 13);
$pdf->Cell(0, 10, utf8_decode('FACTURA DE PAGO DE PRUEBAS MÉDICAS'), 0, 1, 'C');

// Datos del paciente
$pdf->Ln(3);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, utf8_decode('Nombre del paciente: ') . utf8_decode($paciente['nombre'] . ' ' . $paciente['apellidos']), 0, 1);
$pdf->Cell(0, 7, 'Código del paciente: ' . $paciente['codigo'], 0, 1);
$pdf->Cell(0, 7, 'Fecha: ' . date('d/m/Y', strtotime($fecha)), 0, 1);
$pdf->Ln(5);

// Tabla de pruebas
$pdf->SetFillColor(200, 220, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(140, 9, utf8_decode('Tipo de Prueba'), 1, 0, 'C', true);
$pdf->Cell(40, 9, utf8_decode('Precio (FCFA)'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 11);
if (count($pruebas) === 0) {
    $pdf->Cell(180, 10, utf8_decode('No hay pruebas pagadas registradas para esta fecha.'), 1, 1, 'C');
} else {
    foreach ($pruebas as $p) {
        $pdf->Cell(140, 8, utf8_decode($p['tipo_prueba']), 1);
        $pdf->Cell(40, 8, number_format($p['precio'], 0, ',', '.'), 1, 1, 'R');
    }

    // Total
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(140, 9, 'TOTAL A PAGAR', 1);
    $pdf->Cell(40, 9, number_format($total, 0, ',', '.') . ' FCFA', 1, 1, 'R');
}

// Pie de página
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 8, utf8_decode('Factura generada el ') . date('d/m/Y H:i'), 0, 1, 'R');

$pdf->Output('I', 'factura_paciente.pdf');
?>
