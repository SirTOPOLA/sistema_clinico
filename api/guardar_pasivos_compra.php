<?php
// Incluir el archivo de conexión a la base de datos y la sesión
require_once '../config/conexion.php'; 
session_start();

// Redireccionar si no es una solicitud POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header('Location: ../index.php?vista=pasivos_farmacia');
    exit;
}

try {
    // Validar y obtener los datos del formulario
    $compra_id = $_POST['compra_id'] ?? null;
    $proveedor_id = $_POST['proveedor_id'] ?? null;
    $monto_pago = isset($_POST['monto']) ? (float) $_POST['monto'] : null;
    $fecha_pago = $_POST['fecha'] ?? null;
    $metodo_pago = $_POST['metodo_pago'] ?? null;

    // Verificar que los datos obligatorios estén presentes
    if (!$compra_id || !$proveedor_id || !$monto_pago || !$fecha_pago || !$metodo_pago) {
        $_SESSION['error'] = 'Todos los campos son obligatorios.';
        header('Location: ../index.php?vista=pasivos_farmacia');
        exit;
    }

    $pdo->beginTransaction();

    // 1. Obtener el monto pendiente actual de la compra
    $sql_compra = "SELECT monto_pendiente FROM compras WHERE id = :compra_id FOR UPDATE";
    $stmt_compra = $pdo->prepare($sql_compra);
    $stmt_compra->bindParam(':compra_id', $compra_id, PDO::PARAM_INT);
    $stmt_compra->execute();
    $compra = $stmt_compra->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
        throw new Exception('ID de compra no encontrado.');
    }

    $monto_pendiente_actual = (float) $compra['monto_pendiente'];

    // 2. Calcular el nuevo monto pendiente y el estado de la compra
    $nuevo_monto_pendiente = $monto_pendiente_actual - $monto_pago;
    $nuevo_monto_pendiente = max(0, $nuevo_monto_pendiente); // Evita valores negativos

    if ($nuevo_monto_pendiente == 0) {
        $nuevo_estado_pago = 'PAGADO';
    } elseif ($nuevo_monto_pendiente < $monto_pendiente_actual) {
        $nuevo_estado_pago = 'PARCIAL';
    } else {
        $nuevo_estado_pago = 'PENDIENTE';
    }

    // 3. Registrar el pago
    $sql_insert_pago = "INSERT INTO pagos_proveedores (compra_id, proveedor_id, monto, fecha, metodo_pago) 
                        VALUES (:compra_id, :proveedor_id, :monto, :fecha, :metodo_pago)";
    
    $stmt_insert = $pdo->prepare($sql_insert_pago);
    $stmt_insert->bindParam(':compra_id', $compra_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':proveedor_id', $proveedor_id, PDO::PARAM_INT);
    $stmt_insert->bindParam(':monto', $monto_pago, PDO::PARAM_STR);
    $stmt_insert->bindParam(':fecha', $fecha_pago, PDO::PARAM_STR);
    $stmt_insert->bindParam(':metodo_pago', $metodo_pago, PDO::PARAM_STR);
    $stmt_insert->execute();

    // 4. Actualizar la compra
    $sql_update_compra = "UPDATE compras 
                          SET monto_pendiente = :monto_pendiente, 
                              estado_pago = :estado_pago,
                              monto_entregado = monto_entregado + :monto
                          WHERE id = :compra_id";

    $stmt_update = $pdo->prepare($sql_update_compra);
    $stmt_update->bindParam(':monto_pendiente', $nuevo_monto_pendiente, PDO::PARAM_STR);
    $stmt_update->bindParam(':estado_pago', $nuevo_estado_pago, PDO::PARAM_STR);
    $stmt_update->bindParam(':monto', $monto_pago, PDO::PARAM_STR);
    $stmt_update->bindParam(':compra_id', $compra_id, PDO::PARAM_INT);
    $stmt_update->execute();

    $pdo->commit();

    $_SESSION['success'] = 'Pago registrado y compra actualizada exitosamente.';

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error al registrar el pago: ' . $e->getMessage();
}

// Redireccionar a la vista principal
header('Location: ../index.php?vista=pasivos_farmacia');
exit;
?>
