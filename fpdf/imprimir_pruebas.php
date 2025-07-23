<?php
require('fpdf.php');
require('../phpqrcode/qrlib.php');
require '../config/conexion.php';

if (!isset($_GET['id']) || !isset($_GET['fecha'])) {
    die("Parámetros inválidos.");
}

$idPaciente = intval($_GET['id']);
$fecha = $_GET['fecha'];

// Datos del paciente
$stmt = $pdo->prepare("SELECT nombre, apellidos, codigo, fecha_nacimiento, direccion FROM pacientes WHERE id = ?");
$stmt->execute([$idPaciente]);
$paciente = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$paciente) die("Paciente no encontrado.");

// Pruebas
$stmt = $pdo->prepare("
    SELECT tp.nombre AS tipo_prueba, a.resultado, a.valores_refencia
    FROM analiticas a
    JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id
    WHERE a.id_paciente = ? AND DATE(a.fecha_registro) = ?
    ORDER BY tp.nombre
");
$stmt->execute([$idPaciente, $fecha]);
$pruebas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// QR Code
$qrTempFile = tempnam(sys_get_temp_dir(), 'qr') . '.png';
$qrText = "Paciente: {$paciente['nombre']} {$paciente['apellidos']}\nFecha: $fecha\nCódigo: {$paciente['codigo']}";
QRcode::png($qrText, $qrTempFile, QR_ECLEVEL_L, 4);

// PDF Moderno con diseño actualizado
class ModernPDF extends FPDF {
    private $primaryColor = [41, 128, 185];      // Azul profesional
    private $secondaryColor = [52, 73, 94];      // Gris oscuro
    private $accentColor = [46, 204, 113];       // Verde médico
    private $lightGray = [236, 240, 241];        // Gris claro
    private $darkGray = [127, 140, 141];         // Gris medio
    function Header() {
    // Fondo del header con color
    $this->SetFillColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
    $this->Rect(0, 0, 210, 35, 'F');

    // Logo a la izquierda
    $this->Image('../img/logo.jpg', 15, 7, 20);

    // Color y fuente del texto
    $this->SetTextColor(255, 255, 255);
    $this->SetFont('Arial', 'B', 14);
    
    // Coordenadas iniciales para los textos
    $x = 45;
    $y = 8;

    // Nombre de la clínica
    $this->SetXY($x, $y);
    $this->Cell(0, 6, utf8_decode('CONSULTORIO MÉDICO DOCTOR OSCAR'), 0, 1, 'L');

    // Lema
    $this->SetFont('Arial', 'I', 11);
    $this->SetXY($x, $y += 7);
    $this->Cell(0, 5, utf8_decode('"SALUD PARA TODOS"'), 0, 1, 'L');

    // Dirección
    $this->SetFont('Arial', '', 10);
    $this->SetXY($x, $y += 6);
    $this->Cell(0, 5, utf8_decode('Dirección: Ela Nguema, C/Francisco Esono'), 0, 1, 'L');

    // Teléfono
    $this->SetXY($x, $y += 5);
    $this->Cell(0, 5, utf8_decode('Tel: 222 213694 / 555 534111 - WhatsApp: +240 222 21 36 94'), 0, 1, 'L');

    // Título de reporte
    $this->SetFont('Arial', 'B', 10);
    $this->SetXY(150, 10);
    $this->Cell(0, 5, utf8_decode('REPORTE MÉDICO'), 0, 1, 'R');

    // Fecha
    $this->SetFont('Arial', '', 9);
    $this->SetXY(150, 16);
    $this->Cell(0, 5, 'Fecha: ' . date('d/m/Y H:i'), 0, 1, 'R');

    // Espacio después del header
    $this->Ln(12);
}

    function Footer() {
        $this->SetY(-25);
        
        // Línea divisoria
        $this->SetDrawColor($this->darkGray[0], $this->darkGray[1], $this->darkGray[2]);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->Ln(5);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor($this->darkGray[0], $this->darkGray[1], $this->darkGray[2]);
        
        // Información del footer
        $this->Cell(0, 4, 'Este documento es confidencial y esta protegido por el secreto medico', 0, 1, 'C');
        $this->Cell(0, 4, 'Pagina ' . $this->PageNo() . ' de {nb} | Generado el ' . date('d/m/Y H:i'), 0, 0, 'C');
    }
    
    function SectionTitle($title, $icon = '') {
        $this->Ln(5);
        $this->SetFillColor($this->lightGray[0], $this->lightGray[1], $this->lightGray[2]);
        $this->SetTextColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
        $this->SetFont('Arial', 'B', 12);
        
        $this->Rect(15, $this->GetY(), 180, 10, 'F');
        $this->SetXY(20, $this->GetY() + 2);
        $this->Cell(0, 6, $icon . ' ' . strtoupper($title), 0, 1, 'L');
        $this->Ln(3);
    }
    
    function InfoRow($label, $value, $important = false) {
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
        $this->SetX(20);
        $this->Cell(50, 7, $label . ':', 0, 0, 'L');
        
        if ($important) {
            $this->SetFont('Arial', 'B', 10);
            $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        } else {
            $this->SetFont('Arial', '', 10);
            $this->SetTextColor(0, 0, 0);
        }
        
        $this->Cell(0, 7, $value, 0, 1, 'L');
    }
    
    function CreateTable($headers, $data) {
        // Headers de la tabla
        $this->SetFillColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 10);
        
        $widths = [70, 50, 60];
        $x = 20;
        
        foreach ($headers as $i => $header) {
            $this->SetXY($x, $this->GetY());
            $this->Cell($widths[$i], 12, $header, 1, 0, 'C', true);
            $x += $widths[$i];
        }
        $this->Ln(12);
        
        // Datos de la tabla
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0, 0, 0);
        $fill = false;
        
        foreach ($data as $row) {
            $this->SetFillColor($fill ? 248 : 255, $fill ? 249 : 255, $fill ? 250 : 255);
            
            $x = 20;
            $maxHeight = 8;
            
            // Calcular altura necesaria para texto multilínea
            foreach ($row as $i => $cell) {
                $cellHeight = $this->GetStringWidth($cell) > $widths[$i] - 4 ? 16 : 8;
                $maxHeight = max($maxHeight, $cellHeight);
            }
            
            foreach ($row as $i => $cell) {
                $this->SetXY($x, $this->GetY());
                
                if ($i == 1) { // Columna de resultado
                    $this->SetFont('Arial', 'B', 9);
                    $this->SetTextColor($this->accentColor[0], $this->accentColor[1], $this->accentColor[2]);
                } else {
                    $this->SetFont('Arial', '', 9);
                    $this->SetTextColor(0, 0, 0);
                }
                
                $this->Cell($widths[$i], $maxHeight, utf8_decode($cell ?: 'N/A'), 1, 0, 'C', $fill);
                $x += $widths[$i];
            }
            
            $this->Ln($maxHeight);
            $fill = !$fill;
        }
    }
}

// Crear el PDF
$pdf = new ModernPDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Información del Paciente
$pdf->SectionTitle('INFORMACION DEL PACIENTE', '');

$pdf->InfoRow('Nombre Completo', $paciente['nombre'] . ' ' . $paciente['apellidos'], true);
$pdf->InfoRow('Fecha de Nacimiento', date('d/m/Y', strtotime($paciente['fecha_nacimiento'])));
$pdf->InfoRow(utf8_decode('Dirección'), $paciente['direccion']);
$pdf->InfoRow('Fecha del Examen', date('d/m/Y', strtotime($fecha)));

$pdf->Ln(8);

// Resultados de Análisis
$pdf->SectionTitle('RESULTADOS DE ANALISIS CLINICOS', '');

if (!empty($pruebas)) {
    $headers = ['PRUEBA MEDICA', 'RESULTADO', 'VALORES DE REFERENCIA'];
    $tableData = [];
    
    foreach ($pruebas as $prueba) {
        $tableData[] = [
            $prueba['tipo_prueba'],
            $prueba['resultado'] ?: 'Pendiente',
            $prueba['valores_refencia'] ?: 'Ver laboratorio'
        ];
    }
    
    $pdf->CreateTable($headers, $tableData);
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(127, 140, 141);
    $pdf->SetX(20);
    $pdf->Cell(0, 8, 'No se encontraron resultados para la fecha especificada.', 0, 1, 'L');
}

$pdf->Ln(10);

// Código QR y Verificación
$pdf->SectionTitle('CODIGO DE VERIFICACION', '');

$pdf->SetX(20);
$qrY = $pdf->GetY();

// Información del QR
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(120, 6, 'Escanee el codigo QR para verificar la autenticidad del documento:', 0, 1, 'L');
$pdf->SetX(20);
$pdf->Ln(2);

$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(127, 140, 141);
$pdf->SetX(20);
$pdf->Cell(120, 5, 'Contiene informacion del paciente y fecha del examen', 0, 1, 'L');
$pdf->SetX(20);
$pdf->Cell(120, 5, 'Permite verificar la integridad del documento', 0, 1, 'L');
$pdf->SetX(20);
$pdf->Cell(120, 5, 'Generado automaticamente por el sistema', 0, 1, 'L');

// Insertar QR
$pdf->Image($qrTempFile, 150, $qrY, 35, 35);

// Firma y sello (espacio reservado)
$pdf->Ln(10);
$pdf->SectionTitle('VALIDACION MEDICA', '');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetX(20);
$pdf->Cell(0, 8, 'Espacio reservado para firma y sello del mEdico responsable:', 0, 1, 'L');

// Línea para firma
$pdf->Ln(10);
$pdf->SetDrawColor(127, 140, 141);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 100, $pdf->GetY());
$pdf->SetX(20);
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(127, 140, 141);
$pdf->Cell(80, 4, 'Firma del Medico Responsable', 0, 0, 'C');

$pdf->Line(120, $pdf->GetY() - 5, 195, $pdf->GetY() - 5);
$pdf->SetX(120);
$pdf->Cell(75, 4, 'Fecha y Sello', 0, 1, 'C');

// Limpiar archivo temporal
unlink($qrTempFile);

// Generar PDF
$pdf->Output('I', 'informe_medico_' . $paciente['codigo'] . '_' . date('Ymd') . '.pdf');
?>
