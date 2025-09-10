<?php
require('fpdf.php'); // Asegúrate de que la ruta a fpdf.php sea correcta
require('../config/conexion.php'); // Incluir el archivo de conexión

// Clase extendida para personalizar el encabezado y pie de página con diseño elegante
class EnhancedMedicalPDF extends FPDF
{
    // Paleta de colores profesional médica
    private $primaryColor = [25, 118, 210];      // Azul médico profesional
    private $secondaryColor = [69, 90, 100];     // Gris azulado
    private $accentColor = [76, 175, 80];        // Verde médico
    private $lightGray = [245, 245, 245];        // Gris claro para fondos
    private $darkGray = [66, 66, 66];           // Gris oscuro para texto
    private $warningColor = [255, 152, 0];       // Naranja para alertas
    private $insuranceColor = [120, 130, 140] ;// Gris azulado     

    
    function Header()
    {
        // Gradiente de fondo para el header
        $this->SetFillColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Rect(0, 0, 210, 40, 'F');
        
        // Línea decorativa superior
        $this->SetFillColor($this->accentColor[0], $this->accentColor[1], $this->accentColor[2]);
        $this->Rect(0, 0, 210, 3, 'F');
        
        // Logo de la clínica con marco elegante
        $this->SetFillColor(255, 255, 255);
        $this->Rect(12, 6, 32, 28, 'F'); // Marco blanco para el logo
        $this->SetDrawColor($this->lightGray[0], $this->lightGray[1], $this->lightGray[2]);
        $this->Rect(12, 6, 32, 28, 'D'); // Borde del marco
        
        // Logo (ajustar ruta según necesidad)
        if (file_exists('../img/logo.jpg')) {
            $this->Image('../img/logo.jpg', 14, 8, 28, 24);
        }
        
        // Información de la clínica con tipografía mejorada
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 20);
        $this->SetXY(50, 8);
        $this->Cell(0, 8, utf8_decode('CLÍNICA DOCTOR OSCAR'), 0, 1, 'L');
        
        $this->SetFont('Arial', '', 12);
        $this->SetXY(50, 18);
        $this->Cell(0, 5, utf8_decode('Centro Médico Especializado'), 0, 1, 'L');
        
        $this->SetFont('Arial', '', 10);
        $this->SetXY(50, 25);
        $this->Cell(0, 5, utf8_decode('Elanguema, Malabo - Guinea Ecuatorial'), 0, 1, 'L');
        
        // Panel de información del documento (lado derecho)
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Rect(140, 6, 65, 28, 'F');
        $this->SetDrawColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Rect(140, 6, 65, 28, 'D');
        
        $this->SetFont('Arial', 'B', 14);
        $this->SetXY(145, 10);
        $this->Cell(55, 6, utf8_decode('HISTORIAL MÉDICO'), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 9);
        $this->SetXY(145, 18);
        $this->Cell(55, 4, utf8_decode('Fecha de generación:'), 0, 1, 'C');
        $this->SetFont('Arial', 'B', 9);
        $this->SetXY(145, 22);
        $this->Cell(55, 4, date('d/m/Y H:i'), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 8);
        $this->SetXY(145, 28);
        $this->Cell(55, 4, utf8_decode('Documento confidencial'), 0, 1, 'C');
        
        $this->Ln(20);
    }
    
    function Footer()
    {
        $this->SetY(-20);
        
        // Línea decorativa
        $this->SetDrawColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor($this->darkGray[0], $this->darkGray[1], $this->darkGray[2]);
        
        // Información del pie de página
        $this->SetY(-15);
        $this->Cell(0, 5, utf8_decode('Clínica Doctor Oscar - Documento generado automáticamente'), 0, 0, 'L');
        $this->Cell(0, 5, utf8_decode('Página ') . $this->PageNo() . ' de {nb}', 0, 0, 'R');
        
        $this->SetY(-10);
        $this->SetFont('Arial', '', 7);
        $this->Cell(0, 5, utf8_decode('Este documento contiene información médica confidencial - Manténgase en lugar seguro'), 0, 0, 'C');
    }
    
    // Método para crear secciones con estilo
    function CreateSection($title, $bgColor = null)
    {
        if ($bgColor === null) {
            $bgColor = $this->lightGray;
        }
        
        $this->Ln(5);
        $this->SetFillColor($bgColor[0], $bgColor[1], $bgColor[2]);
        $this->SetTextColor($this->darkGray[0], $this->darkGray[1], $this->darkGray[2]);
        $this->SetFont('Arial', 'B', 12);
        
        // Icono decorativo
        $this->Cell(8, 8, utf8_decode('●'), 0, 0, 'C', true);
        $this->Cell(0, 8, utf8_decode($title), 0, 1, 'L', true);
        $this->Ln(2);
    }
    
    // Método para crear campos de información
    function CreateInfoField($label, $value, $width = 0)
    {
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor($this->secondaryColor[0], $this->secondaryColor[1], $this->secondaryColor[2]);
        
        if ($width > 0) {
            $this->Cell($width, 5, utf8_decode($label . ':'), 0, 0);
        } else {
            $this->Cell(45, 5, utf8_decode($label . ':'), 0, 0);
        }
        
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 5, utf8_decode($value), 0, 1);
    }
    
    // Método para crear alertas o información importante
    function CreateAlert($text, $type = 'info')
    {
        $colors = [
            'info' => $this->primaryColor,
            'warning' => $this->warningColor,
            'success' => $this->accentColor,
            'insurance' => $this->insuranceColor
        ];
        
        $color = $colors[$type] ?? $this->primaryColor;
        
        $this->SetFillColor($color[0], $color[1], $color[2]);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);
        
        $this->Cell(0, 6, utf8_decode('  ' . $text), 0, 1, 'L', true);
        $this->Ln(2);
    }
    
    // Método para crear tablas elegantes con totales
    function CreateSummaryTable($headers, $data, $widths, $totals = null)
    {
        // Encabezados
        $this->SetFillColor($this->primaryColor[0], $this->primaryColor[1], $this->primaryColor[2]);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);
        
        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($widths[$i], 7, utf8_decode($headers[$i]), 1, 0, 'C', true);
        }
        $this->Ln();
        
        // Datos
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 8);
        $fill = false;
        
        foreach ($data as $row) {
            $this->SetFillColor($fill ? 250 : 255, $fill ? 250 : 255, $fill ? 250 : 255);
            
            for ($i = 0; $i < count($row); $i++) {
                $align = ($i >= 1) ? 'R' : 'L'; // Números a la derecha
                $this->Cell($widths[$i], 6, utf8_decode($row[$i]), 1, 0, $align, true);
            }
            $this->Ln();
            $fill = !$fill;
        }
        
        // Fila de totales si se proporciona
        if ($totals) {
            $this->SetFillColor($this->accentColor[0], $this->accentColor[1], $this->accentColor[2]);
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Arial', 'B', 9);
            
            for ($i = 0; $i < count($totals); $i++) {
                $align = ($i >= 1) ? 'R' : 'L';
                $this->Cell($widths[$i], 7, utf8_decode($totals[$i]), 1, 0, $align, true);
            }
            $this->Ln();
        }
    }
    
    // Método para mostrar información del seguro
    function CreateInsuranceInfo($insuranceData)
    {
        if (!$insuranceData) return;
        
        $this->CreateSection('INFORMACIÓN DEL SEGURO MÉDICO', $this->insuranceColor);
        
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor($this->insuranceColor[0], $this->insuranceColor[1], $this->insuranceColor[2]);
        $this->Cell(0, 6, utf8_decode('🛡️ Paciente con Cobertura de Seguro'), 0, 1);
        
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(0, 0, 0);
        $this->CreateInfoField('Monto Inicial del Seguro', 'XAF ' . number_format($insuranceData['monto_inicial'], 2));
        $this->CreateInfoField('Saldo Actual Disponible', 'XAF ' . number_format($insuranceData['saldo_actual'], 2));
        $this->CreateInfoField('Fecha de Depósito', date('d/m/Y', strtotime($insuranceData['fecha_deposito'])));
        $this->CreateInfoField('Método de Pago Inicial', $insuranceData['metodo_pago']);
        
        // Indicador visual del estado del seguro
        $porcentaje_usado = (($insuranceData['monto_inicial'] - $insuranceData['saldo_actual']) / $insuranceData['monto_inicial']) * 100;
        
        if ($porcentaje_usado < 50) {
            $this->CreateAlert('Estado del Seguro: EXCELENTE (' . number_format($porcentaje_usado, 1) . '% utilizado)', 'success');
        } elseif ($porcentaje_usado < 80) {
            $this->CreateAlert('Estado del Seguro: BUENO (' . number_format($porcentaje_usado, 1) . '% utilizado)', 'warning');
        } else {
            $this->CreateAlert('Estado del Seguro: CRÍTICO (' . number_format($porcentaje_usado, 1) . '% utilizado)', 'warning');
        }
        
        $this->Ln(5);
    }
}

// Recuperar parámetros del frontend
$id_paciente = isset($_GET['id_paciente']) ? (int)$_GET['id_paciente'] : 0;
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-01-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');

if ($id_paciente == 0) {
    die("ID de paciente no proporcionado.");
}

// Consulta para obtener datos del paciente
$sql_paciente = "SELECT * FROM pacientes WHERE id = :id_paciente";
$stmt_paciente = $pdo->prepare($sql_paciente);
$stmt_paciente->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
$stmt_paciente->execute();
$paciente = $stmt_paciente->fetch(PDO::FETCH_ASSOC);

if (!$paciente) {
    die("Paciente no encontrado.");
}

// Consulta para verificar si el paciente tiene seguro
$sql_seguro = "
    SELECT s.*, sb.fecha_registro as fecha_beneficiario
    FROM seguros s
    JOIN seguros_beneficiarios sb ON s.id = sb.seguro_id
    WHERE sb.paciente_id = :id_paciente OR s.titular_id = :id_paciente
    ORDER BY s.fecha_deposito DESC
    LIMIT 1
";
$stmt_seguro = $pdo->prepare($sql_seguro);
$stmt_seguro->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
$stmt_seguro->execute();
$seguro_info = $stmt_seguro->fetch(PDO::FETCH_ASSOC);

// Consulta unificada mejorada para el historial con cálculos de totales por tipo
$sql_historial = "
    SELECT
        'consulta' AS tipo_actividad,
        c.id AS id_actividad,
        c.fecha_registro AS fecha,
        c.motivo_consulta AS detalle,
        c.precio AS monto,
        c.pagado,
        c.temperatura,
        c.control_cada_horas,
        c.frecuencia_cardiaca,
        c.frecuencia_respiratoria,
        c.tension_arterial,
        c.pulso,
        c.saturacion_oxigeno,
        c.peso_anterior,
        c.peso_actual,
        c.peso_ideal,
        c.imc,
        dc.operacion,
        dc.orina,
        dc.defeca,
        dc.defeca_dias,
        dc.duerme,
        dc.duerme_horas,
        dc.antecedentes_patologicos,
        dc.alergico,
        dc.antecedentes_familiares,
        dc.antecedentes_conyuge,
        dc.control_signos_vitales,
        CONCAT(per.nombre, ' ', per.apellidos) AS nombre_responsable,
        'CONSULTA' AS estado_pago,
        (SELECT GROUP_CONCAT(r.descripcion SEPARATOR ' | ') FROM recetas r WHERE r.id_consulta = c.id) AS recetas_detalle,
        NULL AS analitica_resultado,
        NULL AS analitica_referencia,
        'EFECTIVO' AS metodo_pago,
        NULL AS venta_detalle,
        0 AS usa_seguro
    FROM consultas c
    LEFT JOIN detalle_consulta dc ON c.id = dc.id_consulta
    JOIN usuarios u ON c.id_usuario = u.id
    JOIN personal per ON u.id_personal = per.id
    WHERE c.id_paciente = :id_paciente AND c.fecha_registro BETWEEN :fecha_inicio AND :fecha_fin
    
    UNION ALL

    SELECT
        'analitica' AS tipo_actividad,
        a.id AS id_actividad,
        a.fecha_registro AS fecha,
        tp.nombre AS detalle,
        tp.precio AS monto,
        a.pagado,
        NULL AS temperatura,
        NULL AS control_cada_horas,
        NULL AS frecuencia_cardiaca,
        NULL AS frecuencia_respiratoria,
        NULL AS tension_arterial,
        NULL AS pulso,
        NULL AS saturacion_oxigeno,
        NULL AS peso_anterior,
        NULL AS peso_actual,
        NULL AS peso_ideal,
        NULL AS imc,
        NULL AS operacion,
        NULL AS orina,
        NULL AS defeca,
        NULL AS defeca_dias,
        NULL AS duerme,
        NULL AS duerme_horas,
        NULL AS antecedentes_patologicos,
        NULL AS alergico,
        NULL AS antecedentes_familiares,
        NULL AS antecedentes_conyuge,
        NULL AS control_signos_vitales,
        '' AS nombre_responsable,
        a.estado AS estado_pago,
        NULL AS recetas_detalle,
        a.resultado AS analitica_resultado,
        a.valores_refencia AS analitica_referencia,
        a.tipo_pago AS metodo_pago,
        NULL AS venta_detalle,
        CASE WHEN a.tipo_pago = 'SEGURO' THEN 1 ELSE 0 END AS usa_seguro
    FROM analiticas a
    JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id
    WHERE a.id_consulta IN (SELECT id FROM consultas WHERE id_paciente = :id_paciente) AND a.fecha_registro BETWEEN :fecha_inicio AND :fecha_fin

    UNION ALL
    
    SELECT
        'venta' AS tipo_actividad,
        v.id AS id_actividad,
        v.fecha AS fecha,
        v.motivo_descuento AS detalle,
        v.monto_total AS monto,
        CASE WHEN v.estado_pago = 'PAGADO' THEN 1 ELSE 0 END AS pagado,
        NULL AS temperatura,
        NULL AS control_cada_horas,
        NULL AS frecuencia_cardiaca,
        NULL AS frecuencia_respiratoria,
        NULL AS tension_arterial,
        NULL AS pulso,
        NULL AS saturacion_oxigeno,
        NULL AS peso_anterior,
        NULL AS peso_actual,
        NULL AS peso_ideal,
        NULL AS imc,
        NULL AS operacion,
        NULL AS orina,
        NULL AS defeca,
        NULL AS defeca_dias,
        NULL AS duerme,
        NULL AS duerme_horas,
        NULL AS antecedentes_patologicos,
        NULL AS alergico,
        NULL AS antecedentes_familiares,
        NULL AS antecedentes_conyuge,
        NULL AS control_signos_vitales,
        CONCAT(u.nombre_usuario) AS nombre_responsable,
        v.estado_pago AS estado_pago,
        NULL AS recetas_detalle,
        NULL AS analitica_resultado,
        NULL AS analitica_referencia,
        v.metodo_pago,
        GROUP_CONCAT(CONCAT(p.nombre, ' (', vd.cantidad, 'x XAF ', vd.precio_venta, ')') SEPARATOR ' | ') AS venta_detalle,
        v.seguro AS usa_seguro
    FROM ventas v
    JOIN ventas_detalle vd ON v.id = vd.venta_id
    JOIN productos p ON vd.producto_id = p.id
    JOIN usuarios u ON v.usuario_id = u.id
    WHERE v.paciente_id = :id_paciente AND v.fecha BETWEEN :fecha_inicio AND :fecha_fin
    GROUP BY v.id

    UNION ALL

    SELECT
        'ingreso' AS tipo_actividad,
        i.id AS id_actividad,
        i.fecha_ingreso AS fecha,
        CONCAT('Ingreso en sala ', si.nombre, ' (Cama ', i.numero_cama, ')', ' | Fecha de Alta: ', IFNULL(i.fecha_alta, 'N/A')) AS detalle,
        0 AS monto,
        1 AS pagado,
        NULL AS temperatura,
        NULL AS control_cada_horas,
        NULL AS frecuencia_cardiaca,
        NULL AS frecuencia_respiratoria,
        NULL AS tension_arterial,
        NULL AS pulso,
        NULL AS saturacion_oxigeno,
        NULL AS peso_anterior,
        NULL AS peso_actual,
        NULL AS peso_ideal,
        NULL AS imc,
        NULL AS operacion,
        NULL AS orina,
        NULL AS defeca,
        NULL AS defeca_dias,
        NULL AS duerme,
        NULL AS duerme_horas,
        NULL AS antecedentes_patologicos,
        NULL AS alergico,
        NULL AS antecedentes_familiares,
        NULL AS antecedentes_conyuge,
        NULL AS control_signos_vitales,
        CONCAT(u.nombre_usuario) AS nombre_responsable,
        'HOSPITALIZACIÓN' AS estado_pago,
        NULL AS recetas_detalle,
        NULL AS analitica_resultado,
        NULL AS analitica_referencia,
        'N/A' AS metodo_pago,
        NULL AS venta_detalle,
        0 AS usa_seguro
    FROM ingresos i
    JOIN salas_ingreso si ON i.id_sala = si.id
    JOIN usuarios u ON i.id_usuario = u.id
    WHERE i.id_paciente = :id_paciente AND i.fecha_ingreso BETWEEN :fecha_inicio AND :fecha_fin

    UNION ALL

    SELECT
        'pago_prestamo' AS tipo_actividad,
        p.id AS id_actividad,
        p.fecha AS fecha,
        CONCAT('Préstamo por un total de: XAF ', p.total) AS detalle,
        p.total AS monto,
        CASE WHEN p.estado = 'PAGADO' THEN 1 ELSE 0 END AS pagado,
        NULL AS temperatura,
        NULL AS control_cada_horas,
        NULL AS frecuencia_cardiaca,
        NULL AS frecuencia_respiratoria,
        NULL AS tension_arterial,
        NULL AS pulso,
        NULL AS saturacion_oxigeno,
        NULL AS peso_anterior,
        NULL AS peso_actual,
        NULL AS peso_ideal,
        NULL AS imc,
        NULL AS operacion,
        NULL AS orina,
        NULL AS defeca,
        NULL AS defeca_dias,
        NULL AS duerme,
        NULL AS duerme_horas,
        NULL AS antecedentes_patologicos,
        NULL AS alergico,
        NULL AS antecedentes_familiares,
        NULL AS antecedentes_conyuge,
        NULL AS control_signos_vitales,
        '' AS nombre_responsable,
        p.estado AS estado_pago,
        NULL AS recetas_detalle,
        NULL AS analitica_resultado,
        NULL AS analitica_referencia,
        'PRÉSTAMO' AS metodo_pago,
        NULL AS venta_detalle,
        0 AS usa_seguro
    FROM prestamos p
    WHERE p.paciente_id = :id_paciente AND p.fecha BETWEEN :fecha_inicio AND :fecha_fin

    UNION ALL
    
    SELECT
        'movimiento_seguro' AS tipo_actividad,
        ms.id AS id_actividad,
        ms.fecha AS fecha,
        CONCAT(ms.descripcion, ' - Monto: XAF ', ms.monto) AS detalle,
        ms.monto AS monto,
        1 AS pagado,
        NULL AS temperatura,
        NULL AS control_cada_horas,
        NULL AS frecuencia_cardiaca,
        NULL AS frecuencia_respiratoria,
        NULL AS tension_arterial,
        NULL AS pulso,
        NULL AS saturacion_oxigeno,
        NULL AS peso_anterior,
        NULL AS peso_actual,
        NULL AS peso_ideal,
        NULL AS imc,
        NULL AS operacion,
        NULL AS orina,
        NULL AS defeca,
        NULL AS defeca_dias,
        NULL AS duerme,
        NULL AS duerme_horas,
        NULL AS antecedentes_patologicos,
        NULL AS alergico,
        NULL AS antecedentes_familiares,
        NULL AS antecedentes_conyuge,
        NULL AS control_signos_vitales,
        '' AS nombre_responsable,
        ms.tipo AS estado_pago,
        NULL AS recetas_detalle,
        NULL AS analitica_resultado,
        NULL AS analitica_referencia,
        'SEGURO' AS metodo_pago,
        NULL AS venta_detalle,
        1 AS usa_seguro
    FROM movimientos_seguro ms
    WHERE ms.paciente_id = :id_paciente AND ms.fecha BETWEEN :fecha_inicio AND :fecha_fin

    ORDER BY fecha ASC;
";

$stmt_historial = $pdo->prepare($sql_historial);
$stmt_historial->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
$stmt_historial->bindParam(':fecha_inicio', $fecha_inicio);
$stmt_historial->bindParam(':fecha_fin', $fecha_fin);
$stmt_historial->execute();
$historial = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales por tipo de actividad
$totales_por_tipo = [];
$total_seguro = 0;
$total_efectivo = 0;

foreach ($historial as $actividad) {
    $tipo = $actividad['tipo_actividad'];
    $monto = floatval($actividad['monto']);
    
    if (!isset($totales_por_tipo[$tipo])) {
        $totales_por_tipo[$tipo] = ['cantidad' => 0, 'total' => 0, 'pagado' => 0, 'pendiente' => 0];
    }
    
    $totales_por_tipo[$tipo]['cantidad']++;
    $totales_por_tipo[$tipo]['total'] += $monto;
    
    if ($actividad['pagado']) {
        $totales_por_tipo[$tipo]['pagado'] += $monto;
    } else {
        $totales_por_tipo[$tipo]['pendiente'] += $monto;
    }
    
    // Contabilizar uso de seguro
    if ($actividad['usa_seguro']) {
        $total_seguro += $monto;
    } else {
        $total_efectivo += $monto;
    }
}

// Generar el PDF con diseño mejorado
$pdf = new EnhancedMedicalPDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(15, 10, 15);
$pdf->SetAutoPageBreak(true, 25);

// Sección de información del paciente
$pdf->CreateSection('INFORMACIÓN DEL PACIENTE');

$pdf->SetFont('Arial', '', 10);
$pdf->CreateInfoField('Nombre Completo', $paciente['nombre'] . ' ' . $paciente['apellidos'], 50);
$pdf->CreateInfoField('Documento de Identidad', $paciente['dip'], 50);
$pdf->CreateInfoField('Período del Historial', date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)), 50);

// Información adicional del paciente si está disponible
if (isset($paciente['telefono']) && !empty($paciente['telefono'])) {
    $pdf->CreateInfoField('Teléfono', $paciente['telefono'], 50);
}
if (isset($paciente['direccion']) && !empty($paciente['direccion'])) {
    $pdf->CreateInfoField('Dirección', $paciente['direccion'], 50);
}

$pdf->Ln(5);

// Mostrar información del seguro si existe
if ($seguro_info) {
    $pdf->CreateInsuranceInfo($seguro_info);
}

// Resumen estadístico mejorado con totales por tipo
$pdf->CreateSection('RESUMEN ESTADÍSTICO DETALLADO');

// Preparar datos para la tabla de resumen
$headers = ['Tipo de Actividad', 'Cantidad', 'Total Facturado', 'Total Pagado', 'Pendiente'];
$data = [];
$gran_total_facturado = 0;
$gran_total_pagado = 0;
$gran_total_pendiente = 0;
$total_actividades = 0;

$nombres_tipos = [
    'consulta' => 'Consultas Médicas',
    'analitica' => 'Pruebas Analíticas', 
    'venta' => 'Ventas de Productos',
    'ingreso' => 'Hospitalizaciones',
    'pago_prestamo' => 'Préstamos',
    'movimiento_seguro' => 'Mov. de Seguro'
];

foreach ($totales_por_tipo as $tipo => $totales) {
    $nombre_tipo = $nombres_tipos[$tipo] ?? ucfirst($tipo);
    $data[] = [
        $nombre_tipo,
        $totales['cantidad'],
        'XAF ' . number_format($totales['total'], 2),
        'XAF ' . number_format($totales['pagado'], 2),
        'XAF ' . number_format($totales['pendiente'], 2)
    ];
    
    $gran_total_facturado += $totales['total'];
    $gran_total_pagado += $totales['pagado'];
    $gran_total_pendiente += $totales['pendiente'];
    $total_actividades += $totales['cantidad'];
}

// Fila de totales
$totales_row = [
    'TOTALES GENERALES',
    $total_actividades,
    'XAF ' . number_format($gran_total_facturado, 2),
    'XAF ' . number_format($gran_total_pagado, 2),
    'XAF ' . number_format($gran_total_pendiente, 2)
];

$widths = [50, 20, 35, 35, 35];
$pdf->CreateSummaryTable($headers, $data, $widths, $totales_row);

$pdf->Ln(5);

// Resumen de métodos de pago
if ($seguro_info) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(103, 58, 183);
    $pdf->Cell(0, 6, utf8_decode('💳 RESUMEN POR MÉTODO DE PAGO'), 0, 1);
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->CreateInfoField('Pagado con Seguro Médico', 'XAF ' . number_format($total_seguro, 2));
    $pdf->CreateInfoField('Pagado en Efectivo/Otros', 'XAF ' . number_format($total_efectivo, 2));
    
    $porcentaje_seguro = ($gran_total_facturado > 0) ? ($total_seguro / $gran_total_facturado) * 100 : 0;
    $pdf->CreateInfoField('Porcentaje cubierto por Seguro', number_format($porcentaje_seguro, 1) . '%');
    
    $pdf->Ln(5);
}

// Historial detallado por fechas (mismo código anterior pero con indicadores de seguro)
$pdf->CreateSection('HISTORIAL DETALLADO DE ACTIVIDADES');

$current_date = '';
foreach ($historial as $actividad) {
    $fecha_actividad = date('Y-m-d', strtotime($actividad['fecha']));
    
    if ($fecha_actividad != $current_date) {
        if ($current_date != '') {
            $pdf->Ln(3);
        }
        
        // Separador de fecha elegante
        $pdf->SetFillColor(240, 248, 255);
        $pdf->SetTextColor(25, 118, 210);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, utf8_decode('📅 ' . date('d/m/Y', strtotime($fecha_actividad)) . ' - ' . strftime('%A', strtotime($fecha_actividad))), 0, 1, 'L', true);
        $pdf->Ln(2);
        $current_date = $fecha_actividad;
    }

    // Tipo de actividad con icono y indicador de seguro
    $iconos = [
        'consulta' => '🏥',
        'analitica' => '🔬',
        'venta' => '💊',
        'ingreso' => '🏨',
        'pago_prestamo' => '💰',
        'movimiento_seguro' => '🛡️'
    ];
    
    $icono = $iconos[$actividad['tipo_actividad']] ?? '📋';
    $tipo_titulo = ucfirst(str_replace('_', ' ', $actividad['tipo_actividad']));
    
    // Agregar indicador de seguro si aplica
    if ($actividad['usa_seguro']) {
        $tipo_titulo .= ' 🛡️ (SEGURO)';
    }
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetTextColor(69, 90, 100);
    $pdf->Cell(0, 6, utf8_decode($icono . ' ' . $tipo_titulo), 0, 1);
    
    // Marco para el contenido de la actividad
    $y_start = $pdf->GetY();
    $pdf->SetDrawColor(230, 230, 230);
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(0, 0, 0);

    // El resto del código para mostrar detalles por tipo de actividad permanece igual...
    switch ($actividad['tipo_actividad']) {
        case 'consulta':
            // Información básica de la consulta
            $pdf->CreateInfoField('Motivo de consulta', $actividad['detalle']);
            $pdf->CreateInfoField('Médico responsable', $actividad['nombre_responsable']);
            
            // Signos vitales en formato tabla
            if ($actividad['temperatura'] || $actividad['frecuencia_cardiaca'] || $actividad['tension_arterial']) {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetTextColor(25, 118, 210);
                $pdf->Cell(0, 5, utf8_decode('Signos Vitales:'), 0, 1);
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetTextColor(0, 0, 0);
                
                $signos = [];
                if ($actividad['temperatura']) $signos[] = 'Temp: ' . $actividad['temperatura'] . '°C';
                if ($actividad['frecuencia_cardiaca']) $signos[] = 'FC: ' . $actividad['frecuencia_cardiaca'];
                if ($actividad['frecuencia_respiratoria']) $signos[] = 'FR: ' . $actividad['frecuencia_respiratoria'];
                if ($actividad['tension_arterial']) $signos[] = 'TA: ' . $actividad['tension_arterial'];
                if ($actividad['pulso']) $signos[] = 'Pulso: ' . $actividad['pulso'];
                if ($actividad['saturacion_oxigeno']) $signos[] = 'SatO2: ' . $actividad['saturacion_oxigeno'] . '%';
                
                $pdf->Cell(0, 4, utf8_decode(implode(' | ', $signos)), 0, 1);
            }
            
            // Medidas antropométricas
            if ($actividad['peso_actual'] || $actividad['imc']) {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetTextColor(25, 118, 210);
                $pdf->Cell(0, 5, utf8_decode('Medidas Antropométricas:'), 0, 1);
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetTextColor(0, 0, 0);
                
                $medidas = [];
                if ($actividad['peso_anterior']) $medidas[] = 'Peso Ant: ' . $actividad['peso_anterior'] . 'kg';
                if ($actividad['peso_actual']) $medidas[] = 'Peso Act: ' . $actividad['peso_actual'] . 'kg';
                if ($actividad['peso_ideal']) $medidas[] = 'Peso Ideal: ' . $actividad['peso_ideal'] . 'kg';
                if ($actividad['imc']) $medidas[] = 'IMC: ' . $actividad['imc'];
                
                $pdf->Cell(0, 4, utf8_decode(implode(' | ', $medidas)), 0, 1);
            }
            
            // Recetas
            if (!empty($actividad['recetas_detalle'])) {
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetTextColor(76, 175, 80);
                $pdf->Cell(0, 5, utf8_decode('💊 Prescripción Médica:'), 0, 1);
                $pdf->SetFont('Arial', 'I', 8);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->MultiCell(0, 4, utf8_decode($actividad['recetas_detalle']));
            }
            
            // Estado de pago
            $estado_pago = $actividad['pagado'] ? 'PAGADO' : 'PENDIENTE';
            $color_pago = $actividad['pagado'] ? [76, 175, 80] : [255, 152, 0];
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor($color_pago[0], $color_pago[1], $color_pago[2]);
            $pdf->Cell(0, 5, utf8_decode('Costo: XAF ' . number_format($actividad['monto'], 2) . ' - Estado: ' . $estado_pago), 0, 1);
            break;

        case 'analitica':
            $pdf->CreateInfoField('Tipo de prueba', $actividad['detalle']);
            if (!empty($actividad['analitica_resultado'])) {
                $pdf->CreateInfoField('Resultado', $actividad['analitica_resultado']);
            }
            if (!empty($actividad['analitica_referencia'])) {
                $pdf->CreateInfoField('Valores de referencia', $actividad['analitica_referencia']);
            }
            $pdf->CreateInfoField('Método de pago', $actividad['metodo_pago']);
            $pdf->CreateInfoField('Costo', 'XAF ' . number_format($actividad['monto'], 2));
            break;
            
        case 'venta':
            $pdf->CreateInfoField('Productos vendidos', $actividad['venta_detalle']);
            $pdf->CreateInfoField('Responsable', $actividad['nombre_responsable']);
            $pdf->CreateInfoField('Método de pago', $actividad['metodo_pago']);
            
            $estado_pago = $actividad['estado_pago'];
            $color_pago = ($estado_pago == 'PAGADO') ? [76, 175, 80] : [255, 152, 0];
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor($color_pago[0], $color_pago[1], $color_pago[2]);
            $pdf->Cell(0, 5, utf8_decode('Total: XAF ' . number_format($actividad['monto'], 2) . ' - Estado: ' . $estado_pago), 0, 1);
            break;
            
        case 'ingreso':
            $pdf->MultiCell(0, 4, utf8_decode($actividad['detalle']));
            $pdf->CreateInfoField('Responsable del ingreso', $actividad['nombre_responsable']);
            break;
            
        case 'pago_prestamo':
            $pdf->MultiCell(0, 4, utf8_decode($actividad['detalle']));
            $pdf->CreateInfoField('Estado del préstamo', $actividad['estado_pago']);
            break;
            
        case 'movimiento_seguro':
            $pdf->MultiCell(0, 4, utf8_decode($actividad['detalle']));
            $pdf->CreateInfoField('Tipo de movimiento', $actividad['estado_pago']);
            break;
    }
    
    // Marco alrededor de cada actividad
    $y_end = $pdf->GetY();
    $pdf->Rect(15, $y_start - 1, 180, $y_end - $y_start + 2, 'D');
    
    $pdf->Ln(5);
}

// Información adicional en el pie
if (empty($historial)) {
    $pdf->CreateAlert('No se encontraron actividades en el período seleccionado', 'warning');
}

// Salida del PDF
$nombre_archivo = 'Historial_Medico_Completo_' . $paciente['id'] . '_' . date('Y-m-d') . '.pdf';
$pdf->Output('I', $nombre_archivo);

$pdo = null;
?>