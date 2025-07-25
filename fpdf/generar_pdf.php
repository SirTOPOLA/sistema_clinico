<?php
// Asegúrate de que estas rutas sean correctas para tu entorno
require('fpdf.php');
require('../phpqrcode/qrlib.php');
require '../config/conexion.php';

// Obtener el ID del paciente de la URL
$id_paciente = $_GET['id_paciente'];
// Obtener las fechas de inicio y fin para filtrar, si están presentes
$inicio = $_GET['inicio'] ?? null;
$fin = $_GET['fin'] ?? null;

// --- Clase FPDF personalizada para el diseño moderno ---
class ModernPDF extends FPDF {
    // Definición de la paleta de colores para el reporte
    // Cambiadas de private a public para permitir el acceso externo
    public $primaryColor = [41, 128, 185];     // Azul profesional
    public $secondaryColor = [52, 73, 94];     // Gris oscuro para textos principales
    public $accentColor = [46, 204, 113];      // Verde para resaltar resultados
    public $lightGray = [236, 240, 241];       // Gris claro para fondos de sección
    public $darkGray = [127, 140, 141];        // Gris medio para líneas y texto secundario

    // --- Encabezado del documento ---
    function Header() {
        // Fondo del header con color primario
        $this->SetFillColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Rect(0, 0, 210, 35, 'F'); // Un rectángulo que cubre la parte superior de la página

        // Logo de la clínica
        // Asegúrate de que la ruta '../img/logo2.jpg' sea correcta en tu servidor
        $this->Image('../img/logo2.jpg', 15, 8, 25); // Posición (x, y) y tamaño (ancho, alto)

        // Información de la clínica
        $this->SetTextColor(255, 255, 255); // Color de texto blanco
        $this->SetFont('Arial', 'B', 18); // Fuente para el nombre de la clínica
        $this->SetXY(50, 12); // Posición para el nombre
        $this->Cell(0, 8, 'CLINICA DOCTOR OSCAR', 0, 1, 'L');

        $this->SetFont('Arial', '', 11); // Fuente para la dirección
        $this->SetXY(50, 22); // Posición para la dirección
        $this->Cell(0, 5, 'Centro Medico Especializado - Elanguema, Malabo', 0, 1, 'L');

        // Título del reporte y fecha actual
        $this->SetFont('Arial', 'B', 10);
        $this->SetXY(150, 12);
        $this->Cell(0, 5, 'REPORTE MEDICO', 0, 1, 'R');
        $this->SetFont('Arial', '', 9);
        $this->SetXY(150, 18);
        $this->Cell(0, 5, 'Fecha: ' . date('d/m/Y H:i'), 0, 1, 'R');

        $this->Ln(20); // Espacio después del encabezado
    }

    // --- Pie de página del documento ---
    function Footer() {
        $this->SetY(-25); // Posiciona 25mm desde el final de la página

        // Línea divisoria en el pie de página
        $this->SetDrawColor($this->darkGray[0], $this->darkGray[1], $this->darkGray[2]);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY()); // Dibuja la línea

        $this->Ln(5);
        $this->SetFont('Arial', 'I', 8); // Fuente cursiva y pequeña
        $this->SetTextColor($this->darkGray[0], $this->darkGray[1], $this->darkGray[2]); // Color gris medio

        // Información de confidencialidad
        $this->Cell(0, 4, 'Este documento es confidencial y esta protegido por el secreto medico', 0, 1, 'C');
        // Paginación
        $this->Cell(0, 4, 'Pagina ' . $this->PageNo() . ' de {nb} | Generado el ' . date('d/m/Y H:i'), 0, 0, 'C');
    }

    // --- Método para títulos de sección ---
    function SectionTitle($title, $icon = '') {
        $this->Ln(5); // Espacio antes del título
        $this->SetFillColor($this->lightGray[0], $this->lightGray[1], $this->lightGray[2]); // Fondo gris claro
        $this->SetTextColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]); // Texto gris oscuro
        $this->SetFont('Arial', 'B', 12); // Fuente negrita

        $this->Rect(15, $this->GetY(), 180, 10, 'F'); // Rectángulo de fondo para el título
        $this->SetXY(20, $this->GetY() + 2); // Posición del texto dentro del rectángulo
        $this->Cell(0, 6, $icon . ' ' . strtoupper($title), 0, 1, 'L');
        $this->Ln(3); // Espacio después del título
    }

    // --- Método para filas de información (etiqueta: valor) ---
    function InfoRow($label, $value, $important = false) {
        $this->SetFont('Arial', 'B', 10); // Etiqueta en negrita
        $this->SetTextColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]); // Color de etiqueta
        $this->SetX(20);
        $this->Cell(50, 7, $label . ':', 0, 0, 'L'); // Celda para la etiqueta

        if ($important) {
            $this->SetFont('Arial', 'B', 10);
            $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]); // Valor importante en color primario
        } else {
            $this->SetFont('Arial', '', 10);
            $this->SetTextColor(0, 0, 0); // Valor normal en negro
        }

        $this->Cell(0, 7, $value, 0, 1, 'L'); // Celda para el valor
    }

    // --- Método para crear tablas ---
    function CreateTable($headers, $data) {
        // Encabezados de la tabla
        $this->SetFillColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]); // Fondo color primario
        $this->SetTextColor(255, 255, 255); // Texto blanco
        $this->SetFont('Arial', 'B', 10); // Fuente negrita

        // Anchos de las columnas de la tabla de analíticas
        $widths = [70, 50, 60]; // Ajusta estos anchos según tus necesidades
        $x = 20; // Posición inicial de la tabla

        foreach ($headers as $i => $header) {
            $this->SetXY($x, $this->GetY());
            $this->Cell($widths[$i], 12, $header, 1, 0, 'C', true); // Celda de encabezado con borde y fondo
            $x += $widths[$i];
        }
        $this->Ln(12); // Salto de línea después de los encabezados

        // Datos de la tabla
        $this->SetFont('Arial', '', 9); // Fuente para los datos
        $this->SetTextColor(0, 0, 0); // Texto negro
        $fill = false; // Alternar color de fila

        foreach ($data as $row) {
            // Alternar color de fondo de las filas
            $this->SetFillColor($fill ? $this->lightGray[0] : 255, $fill ? $this->lightGray[1] : 255, $fill ? $this->lightGray[2] : 255);

            $x = 20;
            $maxHeight = 8; // Altura mínima de la fila

            // Calcular la altura máxima necesaria para la fila debido a texto multilínea
            foreach ($row as $i => $cell) {
                // Estimar si el texto requiere más de una línea
                $cellHeight = $this->GetStringWidth($cell) > ($widths[$i] - 4) ? 16 : 8; // 4mm de padding
                $maxHeight = max($maxHeight, $cellHeight);
            }

            foreach ($row as $i => $cell) {
                $this->SetXY($x, $this->GetY());

                // Estilo especial para la columna de 'resultado'
                if ($i == 1) { // Columna de resultado
                    $this->SetFont('Arial', 'B', 9);
                    $this->SetTextColor($this->accentColor[0], $this->accentColor[1], $this->accentColor[2]);
                } else {
                    $this->SetFont('Arial', '', 9);
                    $this->SetTextColor(0, 0, 0);
                }

                // Usar MultiCell si el texto es largo, o Cell si es corto
                if ($maxHeight > 8) { // Si la celda es multilínea
                    $this->MultiCell($widths[$i], $maxHeight / 2, utf8_decode($cell ?: 'N/A'), 1, 'C', $fill);
                } else {
                    $this->Cell($widths[$i], $maxHeight, utf8_decode($cell ?: 'N/A'), 1, 0, 'C', $fill);
                }
                $x += $widths[$i];
            }
            if ($maxHeight <= 8) { // Si no se usó MultiCell para la última celda, avanzar la línea
                $this->Ln($maxHeight);
            }
            $fill = !$fill; // Cambiar color de fila para la siguiente iteración
        }
    }
}

// --- Lógica principal para generar el PDF ---
$pdf = new ModernPDF();
$pdf->AliasNbPages(); // Necesario para 'Pagina X de {nb}'
$pdf->AddPage();

// --- Obtener datos del paciente ---
$stmt_pac = $pdo->prepare("SELECT nombre, apellidos, dip, sexo, fecha_nacimiento, direccion FROM pacientes WHERE id = :id_paciente");
$stmt_pac->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
$stmt_pac->execute();
$paciente = $stmt_pac->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    // Si no se encuentra el paciente, generar un PDF de error
    $pdf = new FPDF(); // Usa FPDF estándar para el mensaje de error
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(255, 0, 0);
    $pdf->Cell(0, 10, "Error: Paciente no encontrado.", 0, 1, 'C');
    $pdf->Output("I", "historial_paciente_error.pdf");
    exit;
}

// --- Información del Paciente ---
$pdf->SectionTitle('INFORMACION DEL PACIENTE'); // Icono se puede añadir si lo deseas
$pdf->InfoRow('Nombre Completo', $paciente['nombre'] . ' ' . $paciente['apellidos'], true);
$pdf->InfoRow('DIP', $paciente['dip']);
$pdf->InfoRow('Sexo', $paciente['sexo']);
$pdf->InfoRow('Fecha de Nacimiento', date('d/m/Y', strtotime($paciente['fecha_nacimiento'])));
$pdf->InfoRow('Dirección', $paciente['direccion'] ?: 'N/A'); // Mostrar 'N/A' si la dirección está vacía

$pdf->Ln(8);

// --- Historial de Consultas ---
$pdf->SectionTitle('HISTORIAL DE CONSULTAS');







$cond_consultas = "";
if ($inicio && $fin) {
    $cond_consultas = "AND DATE(c.fecha_registro) BETWEEN :inicio_date AND :fin_date";
}

$sql_consultas = "SELECT c.*, dc.operacion, dc.orina, dc.defeca, dc.defeca_dias, dc.duerme, dc.duerme_horas, 
                         dc.antecedentes_patologicos, dc.alergico, dc.antecedentes_familiares, 
                         dc.antecedentes_conyuge, dc.control_signos_vitales
                  FROM consultas c
                  LEFT JOIN detalle_consulta dc ON c.id = dc.id_consulta
                  WHERE c.id_paciente = :id_paciente $cond_consultas
                  ORDER BY c.fecha_registro DESC";

$stmt_consultas = $pdo->prepare($sql_consultas);
$stmt_consultas->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
if ($inicio && $fin) {
    $stmt_consultas->bindValue(':inicio_date', $inicio, PDO::PARAM_STR);
    $stmt_consultas->bindValue(':fin_date', $fin, PDO::PARAM_STR);
}
$stmt_consultas->execute();
$consultas_data = $stmt_consultas->fetchAll(PDO::FETCH_ASSOC);











// Verifica que hay datos de consulta
if (!empty($consultas_data)) {
    foreach ($consultas_data as $c) {
        // Añadir nueva página si el contenido se acerca al final
        if ($pdf->GetY() > $pdf->GetPageHeight() - 60) {
            $pdf->AddPage();
            $pdf->SectionTitle('HISTORIAL DE CONSULTAS (CONTINUACIÓN)');
        }

        // Línea superior divisoria
        $pdf->SetDrawColor(180, 180, 180);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(6);

        // --- TÍTULO DE LA CONSULTA ---
        $pdf->SetFont('Arial','B',12);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetX(20);
        $pdf->Cell(0, 8, "Consulta del " . date('d/m/Y H:i', strtotime($c['fecha_registro'])), 0, 1, 'L');
        $pdf->Ln(2);

        // --- BLOQUE DE DATOS GENERALES Y DETALLES ADICIONALES ---
        $pdf->SetFont('Arial','',10);
        $pdf->SetTextColor(50, 50, 50);

        $info_lines = [];
        $info_lines[] = "Motivo de consulta: " . ($c['motivo_consulta'] ?: 'N/A');
        $info_lines[] = "Temperatura: " . ($c['temperatura'] ? "{$c['temperatura']} ºC" : 'N/A');
        $info_lines[] = "Presión arterial: " . ($c['tension_arterial'] ?: 'N/A');
        $info_lines[] = "Frecuencia cardíaca: " . ($c['frecuencia_cardiaca'] ?: 'N/A');
        $info_lines[] = "Frecuencia respiratoria: " . ($c['frecuencia_respiratoria'] ?: 'N/A');
        $info_lines[] = "Pulso: " . ($c['pulso'] ?: 'N/A');
        $info_lines[] = "Saturación de oxígeno: " . ($c['saturacion_oxigeno'] ? "{$c['saturacion_oxigeno']} %" : 'N/A');
        $info_lines[] = "Peso anterior: " . ($c['peso_anterior'] ? "{$c['peso_anterior']} kg" : 'N/A');
        $info_lines[] = "Peso actual: " . ($c['peso_actual'] ? "{$c['peso_actual']} kg" : 'N/A');
        $info_lines[] = "Peso ideal: " . ($c['peso_ideal'] ? "{$c['peso_ideal']} kg" : 'N/A');
        $info_lines[] = "IMC: " . ($c['imc'] ?: 'N/A');

        foreach ($info_lines as $line) {
            $pdf->SetX(25);
            $pdf->MultiCell(170, 6, utf8_decode($line), 0, 'L');
        }

        // --- DETALLES ADICIONALES ---
        if ($c['operacion'] || $c['orina'] || $c['defeca'] || $c['duerme'] || $c['antecedentes_patologicos'] || $c['alergico'] || $c['antecedentes_familiares'] || $c['antecedentes_conyuge'] || $c['control_signos_vitales']) {
            $pdf->Ln(2);
            $pdf->SetFont('Arial','B',10);
            $pdf->SetTextColor(20, 20, 20);
            $pdf->SetX(20);
            $pdf->Cell(0, 7, "Detalles adicionales", 0, 1, 'L');

            $pdf->SetFont('Arial','',9);
            $pdf->SetTextColor(60, 60, 60);

            $detalle_lines = [];
            if (!empty($c['operacion'])) $detalle_lines[] = "Operación: {$c['operacion']}";
            if (!empty($c['orina'])) $detalle_lines[] = "Orina: {$c['orina']}";
            if (!empty($c['defeca'])) $detalle_lines[] = "Defeca: {$c['defeca']}" . (!empty($c['defeca_dias']) ? " ({$c['defeca_dias']} días)" : "");
            if (!empty($c['duerme'])) $detalle_lines[] = "Duerme: {$c['duerme']}" . (!empty($c['duerme_horas']) ? " ({$c['duerme_horas']} h)" : "");
            if (!empty($c['antecedentes_patologicos'])) $detalle_lines[] = "Antecedentes patológicos: {$c['antecedentes_patologicos']}";
            if (!empty($c['alergico'])) $detalle_lines[] = "Alérgico a: {$c['alergico']}";
            if (!empty($c['antecedentes_familiares'])) $detalle_lines[] = "Antecedentes familiares: {$c['antecedentes_familiares']}";
            if (!empty($c['antecedentes_conyuge'])) $detalle_lines[] = "Antecedentes del cónyuge: {$c['antecedentes_conyuge']}";
            if (!empty($c['control_signos_vitales'])) $detalle_lines[] = "Control de signos vitales: {$c['control_signos_vitales']}";

            foreach ($detalle_lines as $line) {
                $pdf->SetX(25);
                $pdf->MultiCell(170, 5.5, utf8_decode($line), 0, 'L');
            }
        }

        // Línea inferior divisoria
        $pdf->Ln(4);
        $pdf->SetDrawColor(230, 230, 230);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(6);
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetX(20);
    $pdf->Cell(0, 8, 'No se encontraron consultas para la fecha o periodo especificado.', 0, 1, 'L');
    $pdf->Ln(5);
}


















// --- Resultados de Análisis ---
// Añadir nueva página si el contenido anterior ha llenado la página
if ($pdf->GetY() > $pdf->GetPageHeight() - 60) {
     $pdf->AddPage();
}
$pdf->SectionTitle('RESULTADOS DE ANALISIS CLINICOS');

$cond_analiticas = "";
if ($inicio && $fin) {
    $cond_analiticas = "AND DATE(a.fecha_registro) BETWEEN :inicio_date AND :fin_date";
}

$analiticas = $pdo->prepare("SELECT a.*, t.nombre AS tipo 
                              FROM analiticas a 
                              JOIN tipo_pruebas t ON a.id_tipo_prueba = t.id 
                              WHERE a.id_paciente = :id_paciente $cond_analiticas 
                              ORDER BY a.fecha_registro DESC");
$analiticas->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
if ($inicio && $fin) {
    $analiticas->bindValue(':inicio_date', $inicio, PDO::PARAM_STR);
    $analiticas->bindValue(':fin_date', $fin, PDO::PARAM_STR);
}
$analiticas->execute();
$pruebas = $analiticas->fetchAll(PDO::FETCH_ASSOC);

if (!empty($pruebas)) {
    $headers = ['PRUEBA MEDICA', 'RESULTADO', 'VALORES DE REFERENCIA'];
    $tableData = [];
    foreach ($pruebas as $prueba) {
        $tableData[] = [
            $prueba['tipo'], // 'tipo_prueba' en la tabla anterior, ahora 'tipo'
            $prueba['resultado'] ?: 'Pendiente',
            $prueba['valores_refencia'] ?: 'Ver laboratorio'
        ];
    }
    $pdf->CreateTable($headers, $tableData);
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor($pdf->darkGray[0], $pdf->darkGray[1], $pdf->darkGray[2]);
    $pdf->SetX(20);
    $pdf->Cell(0, 8, 'No se encontraron resultados de análisis para el periodo especificado.', 0, 1, 'L');
}

$pdf->Ln(10);




// --- Recetas ---
// Añadir nueva página si el contenido anterior ha llenado la página
if ($pdf->GetY() > $pdf->GetPageHeight() - 60) {
     $pdf->AddPage();
}
$pdf->SectionTitle('RECETAS MEDICAS');

$cond_recetas = "";
if ($inicio && $fin) {
    $cond_recetas = "AND DATE(fecha_registro) BETWEEN :inicio_date AND :fin_date";
}

$recetas = $pdo->prepare("SELECT * FROM recetas 
                          WHERE id_paciente = :id_paciente $cond_recetas 
                          ORDER BY fecha_registro DESC");
$recetas->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
if ($inicio && $fin) {
    $recetas->bindValue(':inicio_date', $inicio, PDO::PARAM_STR);
    $recetas->bindValue(':fin_date', $fin, PDO::PARAM_STR);
}
$recetas->execute();
$recetas_data = $recetas->fetchAll(PDO::FETCH_ASSOC);

if (!empty($recetas_data)) {
    foreach ($recetas_data as $r) {
        // Añadir página si el contenido se acerca al final
        if ($pdf->GetY() > $pdf->GetPageHeight() - 40) {
            $pdf->AddPage();
            $pdf->SectionTitle('RECETAS MEDICAS (CONTINUACION)');
        }

        $pdf->SetFillColor(245, 245, 245);
        $current_y = $pdf->GetY();
        $pdf->Rect(15, $current_y, 180, 0.1, 'F'); // Línea separadora
        $pdf->Ln(3);

        $pdf->SetFont('Arial','B',11);
        $pdf->SetTextColor($pdf->secondaryColor[0], $pdf->secondaryColor[1], $pdf->secondaryColor[2]);
        $pdf->Cell(0,7,"Receta: " . date('d/m/Y H:i', strtotime($r['fecha_registro'])),0,1,'L');
        $pdf->SetFont('Arial','',10);
        $pdf->SetTextColor(0,0,0);
        $pdf->SetX(20);
        
        $receta_info = "Descripción: " . ($r['descripcion'] ?: 'N/A') . "\n";
        if (!empty($r['comentario'])) {
            $receta_info .= "Comentario: " . $r['comentario'] . "\n";
        }
        $pdf->MultiCell(175, 6, utf8_decode($receta_info), 0, 'L');
        $pdf->Ln(5); // Espacio entre cada receta
    }
} else {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor($pdf->darkGray[0], $pdf->darkGray[1], $pdf->darkGray[2]);
    $pdf->SetX(20);
    $pdf->Cell(0, 8, 'No se encontraron recetas para el periodo especificado.', 0, 1, 'L');
    $pdf->Ln(5);
}

// --- Código QR y Verificación ---
// Añadir nueva página si el contenido anterior ha llenado la página
if ($pdf->GetY() > $pdf->GetPageHeight() - 50) {
     $pdf->AddPage();
}
$pdf->SectionTitle('CODIGO DE VERIFICACION');

$qr_data_content = "Paciente ID: $id_paciente"; // Contenido base del QR
if ($inicio && $fin) {
    $qr_data_content .= " | Periodo: $inicio a $fin";
} else if ($paciente['codigo']) {
    // Si no hay filtro de fecha, pero hay código de paciente, úsalo
    $qr_data_content = "Paciente: {$paciente['nombre']} {$paciente['apellidos']} | Código: {$paciente['codigo']}";
} else {
    $qr_data_content .= " | Fecha de Generación: " . date('Y-m-d');
}


$qrTempFile = tempnam(sys_get_temp_dir(), 'qr') . '.png';
QRcode::png($qr_data_content, $qrTempFile, QR_ECLEVEL_L, 4);

$pdf->SetX(20);
$qrY = $pdf->GetY(); // Guardar posición Y para alinear el QR

// Información del QR
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(120, 6, utf8_decode('Escanee el código QR para verificar la autenticidad del documento:'), 0, 1, 'L');
$pdf->SetX(20);
$pdf->Ln(2);

$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor($pdf->darkGray[0], $pdf->darkGray[1], $pdf->darkGray[2]);
$pdf->SetX(20);
$pdf->Cell(120, 5, utf8_decode('Contiene información del paciente y el periodo del historial.'), 0, 1, 'L');
$pdf->SetX(20);
$pdf->Cell(120, 5, utf8_decode('Permite verificar la integridad del documento.'), 0, 1, 'L');
$pdf->SetX(20);
$pdf->Cell(120, 5, utf8_decode('Generado automáticamente por el sistema.'), 0, 1, 'L');

// Insertar QR
$pdf->Image($qrTempFile, 150, $qrY, 35, 35); // Posicionar QR a la derecha de la información

$pdf->Ln(10);

// --- Firma y sello (espacio reservado) ---
// Añadir nueva página si el contenido anterior ha llenado la página
if ($pdf->GetY() > $pdf->GetPageHeight() - 40) {
     $pdf->AddPage();
}
$pdf->SectionTitle('VALIDACION MEDICA');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetX(20);
$pdf->Cell(0, 8, utf8_decode('Espacio reservado para firma y sello del médico responsable:'), 0, 1, 'L');

// Línea para firma
$pdf->Ln(10);
$pdf->SetDrawColor($pdf->darkGray[0], $pdf->darkGray[1], $pdf->darkGray[2]);
$pdf->SetLineWidth(0.5);
$pdf->Line(20, $pdf->GetY(), 100, $pdf->GetY()); // Línea para la firma
$pdf->SetX(20);
$pdf->Ln(5);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor($pdf->darkGray[0], $pdf->darkGray[1], $pdf->darkGray[2]);
$pdf->Cell(80, 4, utf8_decode('Firma del Médico Responsable'), 0, 0, 'C');

// Línea para fecha y sello
$pdf->Line(120, $pdf->GetY() - 5, 195, $pdf->GetY() - 5);
$pdf->SetX(120);
$pdf->Cell(75, 4, utf8_decode('Fecha y Sello'), 0, 1, 'C');

// Limpiar archivo temporal del QR
unlink($qrTempFile);

// Generar PDF
// El nombre del archivo ahora incluye el código del paciente si está disponible y la fecha de generación
$pdf->Output('I', 'informe_medico_' . ($paciente['codigo'] ?? 'N_A') . '_' . date('Ymd') . '.pdf');
?>