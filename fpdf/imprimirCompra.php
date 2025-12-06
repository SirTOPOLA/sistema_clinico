<?php
// ajax/imprimir_comprobante.php
// Requiere: FPDF (libs/fpdf.php), pdo MySQLi en ../config/pdo.php
// Logo esperado: /assets/logo.png

require '../config/conexion.php';
require 'fpdf.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die("ID inválido.");
}

// ------------------------
// Consultar compra
// ------------------------
$stmt = $pdo->prepare("
    SELECT c.*, 
           p.nombre AS proveedor_nombre, 
           p.telefono AS proveedor_telefono, 
           p.direccion AS proveedor_direccion,
           per.nombre AS personal_nombre, 
           per.apellidos AS personal_apellidos
    FROM compras c
    LEFT JOIN proveedores p ON p.id = c.proveedor_id
    LEFT JOIN personal per ON per.id = c.personal_id
    WHERE c.id = :id
");

$stmt->bindParam(":id", $id, PDO::PARAM_INT);
$stmt->execute();

$compra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compra) {
    die("Compra no encontrada.");
}

/* ---------------------------------------------------------
   OBTENER DETALLE DE LA COMPRA
--------------------------------------------------------- */
$stmtDetalle = $pdo->prepare("
    SELECT cd.*, pr.nombre AS producto
    FROM compras_detalle cd
    LEFT JOIN productos pr ON pr.id = cd.producto_id
    WHERE cd.compra_id = :id
");

$stmtDetalle->bindParam(":id", $id, PDO::PARAM_INT);
$stmtDetalle->execute();

$detalle = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
// ------------------------
// Configuración de colores
// ------------------------
// Azul Bootstrap #0d6efd => RGB (13,110,253)
$AZ_R = 13;
$AZ_G = 110;
$AZ_B = 253;

// ------------------------
// Clase PDF personalizada
// ------------------------
class PDF_Comprobante extends FPDF {
    public $logoPath;
    public $az_r; public $az_g; public $az_b;
    public $compraInfo;

    function Header() {
        // Logo (centrado)
        if ($this->logoPath && file_exists($this->logoPath)) {
            // ancho logo 40mm
            $this->Image($this->logoPath, ($this->w - 40) / 2, 8, 40);
            $this->Ln(22);
        } else {
            $this->Ln(12);
        }

        // Nombre / línea decorativa
        $this->SetFont('Arial','B',14);
        $this->Cell(0,6,utf8_decode('Comprobante de Compra'),0,1,'C');
        $this->Ln(2);

        // Línea decorativa en azul
        $this->SetDrawColor($this->az_r, $this->az_g, $this->az_b);
        $this->SetLineWidth(1);
        $margin = 15;
        $this->Line($margin, $this->GetY(), $this->w - $margin, $this->GetY());
        $this->Ln(6);
    }

    function Footer() {
        $this->SetY(-25);
        // Línea fina
        $this->SetDrawColor(200,200,200);
        $this->SetLineWidth(0.3);
        $this->Line(15, $this->GetY(), $this->w - 15, $this->GetY());
        $this->Ln(4);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,5,utf8_decode('Documento generado automáticamente — Página ') . $this->PageNo(),0,0,'C');
    }
}

// ------------------------
// Generar PDF
// ------------------------
$pdf = new PDF_Comprobante('P','mm','A4');
$pdf->logoPath = __DIR__ . '/../img/logo.jpg'; // ruta al logo (ajusta si hace falta)
$pdf->az_r = $AZ_R; $pdf->az_g = $AZ_G; $pdf->az_b = $AZ_B;
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 30);

// =========================
// DATOS DE LA COMPRA (encabezado informativo)
// =========================
$pdf->SetFont('Arial','B',10);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,6,utf8_decode('DATOS DE LA COMPRA'),0,1);
$pdf->Ln(2);

$pdf->SetFont('Arial','',10);
$leftColW = 95;
$rightColW = 95;

// Primera fila
$pdf->Cell($leftColW,6,utf8_decode('Factura: ' . $compra['codigo_factura']),0,0);
$pdf->Cell($rightColW,6,utf8_decode('Fecha: ' . $compra['fecha']),0,1);

// Segunda fila
$pdf->Cell($leftColW,6,utf8_decode('Proveedor: ' . ($compra['proveedor_nombre'] ?? '-')),0,0);
$pdf->Cell($rightColW,6,utf8_decode('Teléfono: ' . ($compra['proveedor_telefono'] ?? '-')),0,1);

// Tercera fila (direccion)
$pdf->MultiCell(0,6,utf8_decode('Dirección: ' . ($compra['proveedor_direccion'] ?? '-')),0,1);

// Registrado por
$registradoPor = trim(($compra['personal_nombre'] ?? '') . ' ' . ($compra['personal_apellidos'] ?? ''));
$pdf->Cell(0,6,utf8_decode('Registrado por: ' . ($registradoPor ?: '-')),0,1);
$pdf->Ln(4);

// =========================
// TABLA DETALLE (encabezado estilizado con azul)
// =========================
$pdf->SetFont('Arial','B',10);
$pdf->SetDrawColor($AZ_R, $AZ_G, $AZ_B);
$pdf->SetFillColor($AZ_R, $AZ_G, $AZ_B);
$pdf->SetTextColor(255,255,255);

// Columnas
$colProducto = 90;
$colCantidad = 25;
$colPrecio = 40;
$colSubtotal = 35;

$pdf->Cell($colProducto,8,utf8_decode('Producto'),1,0,'L',true);
$pdf->Cell($colCantidad,8,utf8_decode('Cant.'),1,0,'C',true);
$pdf->Cell($colPrecio,8,utf8_decode('Precio'),1,0,'R',true);
$pdf->Cell($colSubtotal,8,utf8_decode('Subtotal'),1,1,'R',true);

// Reset color para filas
$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);

// Alternar gris claro en filas
$fill = false;
$fillColor = [245,245,245];

$totalCalculado = 0;

if (!empty($detalle)) {

    foreach ($detalle as $row) {

        $producto = utf8_decode($row['producto'] ?? '—');

        // Limitar nombre largo
        if (mb_strlen($producto) > 60) {
            $producto = mb_substr($producto, 0, 57) . '...';
        }

        $cantidad = (int)$row['cantidad'];
        $precio = (float)$row['precio_compra'];
        $subtotal = $cantidad * $precio;
        $totalCalculado += $subtotal;

        if ($fill) {
            $pdf->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
        } else {
            $pdf->SetFillColor(255,255,255);
        }

        $pdf->Cell($colProducto,7,$producto,'LR',0,'L',$fill);
        $pdf->Cell($colCantidad,7,$cantidad,'LR',0,'C',$fill);
        $pdf->Cell($colPrecio,7,number_format($precio,2),'LR',0,'R',$fill);
        $pdf->Cell($colSubtotal,7,number_format($subtotal,2),'LR',1,'R',$fill);

        $fill = !$fill;
    }

    // Línea final de la tabla
    $pdf->Cell($colProducto,0,'','T',0);
    $pdf->Cell($colCantidad,0,'','T',0);
    $pdf->Cell($colPrecio,0,'','T',0);
    $pdf->Cell($colSubtotal,0,'','T',1);

} else {

    $pdf->Cell(0,7,utf8_decode('No hay productos para esta compra.'),1,1,'C');

}


$pdf->Ln(6);

// =========================
// RESUMEN ECONÓMICO (barra lateral derecha con fondo suave)
// =========================
$pdf->SetFont('Arial','B',10);
$pdf->Cell(0,6,utf8_decode('RESUMEN ECONÓMICO'),0,1);
$pdf->Ln(2);
$pdf->SetFont('Arial','',10);

$labelW = 70;
$valueW = 40;
$xRight = $pdf->GetX();

// Mostrar filas (alineadas a la derecha)
$startX = $pdf->GetX();
$curY = $pdf->GetY();

$fields = [
    ['Total Compra', number_format($compra['total'],2)],
    ['Monto Entregado', number_format($compra['monto_entregado'],2)],
    ['Gastado', number_format($compra['monto_gastado'],2)],
    ['Cambio Devuelto', number_format($compra['cambio_devuelto'],2)],
    ['Monto Pendiente', number_format($compra['monto_pendiente'],2)],
    ['Estado de Pago', utf8_decode($compra['estado_pago'] ?? '')]
];

foreach ($fields as $f) {
    $pdf->Cell($labelW,6,utf8_decode($f[0].':'));
    $pdf->Cell($valueW,6,$f[1],0,1,'R');
}

$pdf->Ln(10);

// =========================
// FIRMAS (estilo elegante)
// =========================
$pdf->SetFont('Arial','',10);
$firmWidth = 80;
$gap = 30;

$yBefore = $pdf->GetY();

$pdf->Cell($firmWidth,6,'______________________________',0,0,'C');
$pdf->Cell($gap,6,'',0,0);
$pdf->Cell($firmWidth,6,'______________________________',0,1,'C');

$pdf->Cell($firmWidth,6,'Firma del Responsable',0,0,'C');
$pdf->Cell($gap,6,'',0,0);
$pdf->Cell($firmWidth,6,'Firma del Proveedor',0,1,'C');

// =========================
// PIE: nota
// =========================
$pdf->Ln(8);
$pdf->SetFont('Arial','I',8);
$pdf->SetTextColor(100,100,100);
$pdf->MultiCell(0,5,utf8_decode('Nota: Este comprobante es una representación impresa de la transacción registrada en el sistema. Conserva este documento para efectos contables.'),0,'L');

// =========================
// Salida
// =========================
$filename = 'Comprobante_' . $compra['id'] . '.pdf';
$pdf->Output('I', $filename);
exit;
