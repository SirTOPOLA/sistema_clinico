<?php
session_start();
require '../config/conexion.php';



try {
    // Validar existencia del usuario y de las pruebas enviadas
    if (!isset($_POST['id_usuario'], $_POST['pagos']) || !is_array($_POST['pagos'])) {
        throw new Exception("Datos incompletos para procesar el pago.");
    }

    $id_usuario = $_POST['id_usuario'];
    $pagos = $_POST['pagos'];

    // Preparar sentencias
    $sqlActualizar = "UPDATE analiticas SET pagado = 1 WHERE id = ?";
    $stmtActualizar = $pdo->prepare($sqlActualizar);

    $sqlInsertar = "INSERT INTO pagos (cantidad, id_analitica, id_tipo_prueba, fecha_registro, id_usuario)
                    VALUES (?, ?, ?, NOW(), ?)";
    $stmtInsertar = $pdo->prepare($sqlInsertar);

    // Iniciar transacción
    $pdo->beginTransaction();

    foreach ($pagos as $id_analitica => $datos) {
        // Solo procesar si fue seleccionado
        if (isset($datos['seleccionado'])) {
            $precio = floatval($datos['precio'] ?? 0);
            $id_tipo_prueba = intval($datos['id_tipo_prueba']);

            // 1. Marcar como pagado
            $stmtActualizar->execute([$id_analitica]);

            // 2. Insertar en tabla pagos
            $stmtInsertar->execute([$precio, $id_analitica, $id_tipo_prueba, $id_usuario]);
        }
    }

    $pdo->commit();

    $_SESSION['success'] = "Pago registrado correctamente.";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['error'] = "Error al procesar el pago: " . $e->getMessage();
}

header("Location: ../index.php?vista=pagos");
exit;
