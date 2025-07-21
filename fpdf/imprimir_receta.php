<?php
require('fpdf.php');
require '../config/conexion.php';

// Obtener ID de receta
$id_receta = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Consulta
$stmt = $pdo->prepare("SELECT pacientes.*, recetas.* 
                       FROM pacientes 
                       LEFT JOIN recetas ON recetas.id_paciente = pacientes.id 
                       WHERE recetas.id = ?");
$stmt->execute([$id_receta]);
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$datos) {
    die("Receta no encontrada.");
}

// Datos
$nombre       = $datos['nombre'];
$fecha_nac    = $datos['fecha_nacimiento'];
$genero       = $datos['sexo'];
$direccion    = $datos['direccion'];
$fecha_impresion = date("d/m/Y");
$medicamentos = $datos['descripcion'];
$observaciones = $datos['comentario'];

// Calcular edad
$edad = date_diff(date_create($fecha_nac), date_create('today'))->y;

// Clase personalizada
class PDF extends FPDF {
    function Header() {
        // Logo
        $this->Image('../img/logo.jpg', 10, 8, 25);
        // Título
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, utf8_decode('CONSULTORIO MÉDICO “DOCTOR OSCAR SL”'), 0, 1, 'C');
        $this->SetFont('Arial', 'I', 11);
        $this->Cell(0, 6, utf8_decode('"SALUD PARA TODOS"'), 0, 1, 'C');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, utf8_decode('PROMOTOR: DOCTOR OSCAR BIOKO'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('DIRECCIÓN: ELA NGUEMA C/FRANCISCO ESONO'), 0, 1, 'C');
        $this->Cell(0, 5, utf8_decode('CONTACTOS: 222 213694 / 555 534111   WHATSAPP: +240 222 21 36 94'), 0, 1, 'C');
        $this->Ln(5);
        // Línea
        $this->SetDrawColor(0, 102, 204);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Fecha de impresión: ') . date("d/m/Y"), 0, 0, 'L');
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'R');
    }
}

// Crear PDF
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();

// Título receta
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(0, 102, 204);
$pdf->Cell(0, 10, utf8_decode('RECETA MÉDICA'), 0, 1, 'C');
$pdf->Ln(5);

// Datos personales
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0);
$pdf->Cell(0, 8, utf8_decode("Paciente: $nombre"), 0, 1);
$pdf->Cell(0, 8, utf8_decode("Edad: $edad años"), 0, 1);
$pdf->Cell(0, 8, utf8_decode( "Género: $genero"), 0, 1);
$pdf->Cell(0, 8, utf8_decode("Dirección: $direccion"), 0, 1);
$pdf->Ln(5);

// Prescripción
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(0, 0, 128);
$pdf->Cell(0, 8, utf8_decode('Prescripción:'), 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(0);
$pdf->MultiCell(0, 8, utf8_decode($medicamentos), 0, 'L');
$pdf->Ln(5);

// Observaciones
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(0, 0, 128);
$pdf->Cell(0, 8, 'Observaciones:', 0, 1);
$pdf->SetFont('Arial', '', 11);
$pdf->SetTextColor(50, 50, 50);

if (!empty(trim($observaciones))) {
    $pdf->MultiCell(0, 8, utf8_decode($observaciones), 0, 'L');
} else {
    $pdf->SetTextColor(150, 150, 150);
    $pdf->MultiCell(0, 8, utf8_decode("Sin observaciones adicionales registradas."), 0, 'L');
}
$pdf->Ln(10);

// Firma
$pdf->Cell(0, 8, '________________________', 0, 1, 'R');
$pdf->Cell(0, 8, utf8_decode('Firma del Médico'), 0, 1, 'R');

// Salida
$pdf->Output('I', "receta_$id_receta.pdf");
