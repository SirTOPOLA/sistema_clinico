<?php
require('fpdf.php');

// Conexión a base de datos
require '../config/conexion.php';





// Obtener ID de receta
$id_receta = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consulta con JOIN
$stmt = $pdo->prepare("SELECT pacientes.*, recetas.* 
                       FROM pacientes 
                       LEFT JOIN recetas ON recetas.id_paciente = pacientes.id 
                       WHERE recetas.id = ?");
$stmt->execute([$id_receta]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos) {
    die("Receta no encontrada.");
}

// Extraer datos
$nombre = $datos['nombre'];
$edad = $datos['fecha_nacimiento'];
$genero = $datos['sexo'];
$direccion = $datos['direccion'];
$fecha_impresion = date("d/m/Y");
$medicamentos = $datos['descripcion'];
$observaciones = $datos['comentario'];

// Clase personalizada PDF
class PDF extends FPDF {
    function Header() {
        $this->Image('../img/logo2.jpg', 10, 10, 30);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('CLÍNICA DOCTOR OSCAR'), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 10);
        $this->Cell(0, 5, 'Ubicación: Elanguema - Malabo', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Fecha de impresión: ' . date("d/m/Y"), 0, 0, 'L');
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'R');
    }
}

// Crear PDF
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 10, utf8_decode('RECETA MÉDICA'), 0, 1, 'C');
$pdf->SetDrawColor(0, 102, 204);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0);
$pdf->Cell(0, 8, utf8_decode("Paciente: $nombre"), 0, 1);
$pdf->Cell(0, 8, "Edad: $edad años   Género: $genero", 0, 1);
$pdf->Cell(0, 8, utf8_decode("Dirección: $direccion"), 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(0, 0, 128);
$pdf->Cell(0, 8, 'Prescripción:', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0);
$pdf->MultiCell(0, 8, utf8_decode($medicamentos), 0, 'L');
$pdf->Ln(5);

if (!empty($observaciones)) {
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor(0, 0, 128);
    $pdf->Cell(0, 8, 'Observaciones:', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(0);
    $pdf->MultiCell(0, 8, utf8_decode($observaciones), 0, 'L');
    $pdf->Ln(5);
}

$pdf->Ln(20);
$pdf->Cell(0, 8, '________________________', 0, 1, 'R');
$pdf->Cell(0, 8, utf8_decode('Firma del Médico'), 0, 1, 'R');

$pdf->Output('I', "receta_$id_receta.pdf");
