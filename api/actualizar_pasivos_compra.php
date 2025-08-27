<?php
require_once '../config/conexion.php';
session_start();

// Redireccionar si no es POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: ../index.php?vista=pasivos_farmacia');
    exit;
}

try {
    // Sanitización y validación de los datos
    $pago_id      = isset($_POST['pago_id']) ? (int) $_POST['pago_id'] : null;
    $monto_nuevo  = isset($_POST['monto']) ? floatval($_POST['monto']) : null;
    $fecha_pago   = $_POST['fecha'] ?? null;
    $metodo_pago  = trim($_POST['metodo_pago'] ?? '');

    if (!$pago_id || !$monto_nuevo || !$fecha_pago || empty($metodo_pago)) {
        $_SESSION['error'] = 'Todos los campos son obligatorios.';
        header('Location: ../index.php?vista=pasivos_farmacia');
        exit;
    }

    if ($monto_nuevo <= 0) {
        $_SESSION['error'] = 'El monto debe ser mayor que cero.';
        header('Location: ../index.php?vista=pasivos_farmacia');
        exit;
    }

    $pdo->beginTransaction();

    // Obtener el pago original
    $sql_pago = "SELECT * FROM pagos_proveedores WHERE id = :pago_id FOR UPDATE";
    $stmt_pago = $pdo->prepare($sql_pago);
    $stmt_pago->bindParam(':pago_id', $pago_id, PDO::PARAM_INT);
    $stmt_pago->execute();
    $pago_original = $stmt_pago->fetch(PDO::FETCH_ASSOC);

    if (!$pago_original) {
        throw new Exception('Pago no encontrado.');
    }

    $compra_id = $pago_original['compra_id'];
    $monto_anterior = floatval($pago_original['monto']);

    // Obtener la compra asociada
    $sql_compra = "SELECT monto_pendiente FROM compras WHERE id = :compra_id FOR UPDATE";
    $stmt_compra = $pdo->prepare($sql_compra);
    $stmt_compra->bindParam(':compra_id', $compra_id, PDO::PARAM_INT);
    $stmt_compra->execute();
    $compra = $stmt_compra->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
        throw new Exception('Compra asociada no encontrada.');
    }

    $monto_pendiente_actual = floatval($compra['monto_pendiente']);

    // Revertir el pago anterior y aplicar el nuevo
    $nuevo_monto_pendiente = $monto_pendiente_actual + $monto_anterior - $monto_nuevo;

    if ($nuevo_monto_pendiente < 0) {
        throw new Exception('El nuevo monto excede el monto permitido.');
    }

    $nuevo_estado_pago = $nuevo_monto_pendiente <= 0 ? 'PAGADO' : 'PARCIAL';

    // Actualizar el pago
    $sql_update_pago = "UPDATE pagos_proveedores 
                        SET monto = :monto, fecha = :fecha, metodo_pago = :metodo 
                        WHERE id = :pago_id";

    $stmt_update_pago = $pdo->prepare($sql_update_pago);
    $stmt_update_pago->bindParam(':monto', $monto_nuevo);
    $stmt_update_pago->bindParam(':fecha', $fecha_pago);
    $stmt_update_pago->bindParam(':metodo', $metodo_pago);
    $stmt_update_pago->bindParam(':pago_id', $pago_id, PDO::PARAM_INT);
    $stmt_update_pago->execute();

    // Actualizar la compra
    $sql_update_compra = "UPDATE compras 
                          SET monto_pendiente = :monto_pendiente, estado_pago = :estado_pago 
                          WHERE id = :compra_id";
    
    $stmt_update_compra = $pdo->prepare($sql_update_compra);
    $stmt_update_compra->bindParam(':monto_pendiente', $nuevo_monto_pendiente);
    $stmt_update_compra->bindParam(':estado_pago', $nuevo_estado_pago);
    $stmt_update_compra->bindParam(':compra_id', $compra_id, PDO::PARAM_INT);
    $stmt_update_compra->execute();

    $pdo->commit();

    $_SESSION['success'] = 'Pago actualizado correctamente.';

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = 'Error al actualizar el pago: ' . $e->getMessage();
}

// Redirigir a la vista principal
header('Location: ../index.php?vista=pasivos_farmacia');
exit;
?>
