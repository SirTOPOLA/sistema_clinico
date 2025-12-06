<?php
session_start();
require '../config/conexion.php'; // conexión PDO

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Método no permitido.'
    ];
    header("Location: ../index.php?vista=contabilidad");
    exit;
}

if (!isset($_POST['accion']) || $_POST['accion'] !== 'pago_proveedor') {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'Acción inválida.'
    ];
    header("Location: ../index.php?vista=contabilidad");
    exit;
}

// Sanitizar
$compra_id  = isset($_POST['compra_id']) ? intval($_POST['compra_id']) : 0;
$monto      = isset($_POST['monto']) ? floatval($_POST['monto']) : 0.00;
$metodo     = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : "EFECTIVO";

if ($compra_id <= 0 || $monto <= 0) {
    $_SESSION['alerta'] = [
        'tipo' => 'warning',
        'mensaje' => 'Debe introducir un monto válido.'
    ];
    header("Location: ../index.php?vista=contabilidad");
    exit;
}


/* =====================================================
   1️⃣ OBTENER COMPRA
===================================================== */
$stmt = $pdo->prepare("
    SELECT proveedor_id, monto_pendiente, total
    FROM compras
    WHERE id = :id
");
$stmt->bindParam(":id", $compra_id, PDO::PARAM_INT);
$stmt->execute();

$compra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compra) {
    $_SESSION['alerta'] = [
        'tipo' => 'danger',
        'mensaje' => 'La compra seleccionada no existe.'
    ];
    header("Location: ../index.php?vista=contabilidad");
    exit;
}

$proveedor_id   = $compra['proveedor_id'];
$pendiente      = floatval($compra['monto_pendiente']);
$total          = floatval($compra['total']);

if ($monto > $pendiente) {
    $_SESSION['alerta'] = [
        'tipo' => 'warning',
        'mensaje' => 'No puedes pagar más del monto pendiente.'
    ];
    header("Location: ../index.php?vista=contabilidad");
    exit;
}


/* =====================================================
   2️⃣ REGISTRAR PAGO
===================================================== */
$stmtPago = $pdo->prepare("
    INSERT INTO pagos_proveedores (compra_id, proveedor_id, monto, fecha, metodo_pago)
    VALUES (:compra_id, :proveedor_id, :monto, CURDATE(), :metodo_pago)
");

$stmtPago->execute([
    ":compra_id"    => $compra_id,
    ":proveedor_id" => $proveedor_id,
    ":monto"        => $monto,
    ":metodo_pago"  => $metodo
]);


/* =====================================================
   3️⃣ ACTUALIZAR COMPRA
===================================================== */
$nuevo_pendiente = $pendiente - $monto;

// Definir estado
if ($nuevo_pendiente <= 0) {
    $estado = "PAGADO";
    $nuevo_pendiente = 0;
} elseif ($nuevo_pendiente < $total) {
    $estado = "PARCIAL";
} else {
    $estado = "PENDIENTE";
}

$stmtUpdate = $pdo->prepare("
    UPDATE compras
    SET monto_pendiente = :pendiente,
        estado_pago = :estado
    WHERE id = :id
");

$stmtUpdate->execute([
    ":pendiente" => $nuevo_pendiente,
    ":estado"    => $estado,
    ":id"        => $compra_id
]);


/* =====================================================
   4️⃣ MENSAJE DE ÉXITO
===================================================== */
$_SESSION['alerta'] = [
    'tipo' => 'success',
    'mensaje' => 'Pago registrado correctamente.'
];

header("Location: ../index.php?vista=contabilidad");
exit;
