<?php
session_start();
require '../config/conexion.php'; // Incluye tu archivo de conexión a la base de datos

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $compra_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$compra_id) {
        $_SESSION['error'] = "ID de compra inválido para eliminar.";
        header("Location: ../index.php?vista=compras_farmacia");
        exit();
    }

    try {
        $pdo->beginTransaction(); // Inicia una transacción

        // 1. Obtener detalles de la compra para revertir el stock
        $stmt_detalles = $pdo->prepare("SELECT producto_id, cantidad FROM compras_detalle WHERE compra_id = ?");
        $stmt_detalles->execute([$compra_id]);
        $detalles_a_revertir = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

        // 2. Revertir el stock de los productos
        $stmt_revert_stock = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
        foreach ($detalles_a_revertir as $detalle) {
            $stmt_revert_stock->execute([$detalle['cantidad'], $detalle['producto_id']]);
        }

        // 3. Eliminar los detalles de la compra
        $stmt_delete_detalle = $pdo->prepare("DELETE FROM compras_detalle WHERE compra_id = ?");
        $stmt_delete_detalle->execute([$compra_id]);

        // 4. Eliminar la compra principal
        $stmt_delete_compra = $pdo->prepare("DELETE FROM compras WHERE id = ?");
        $stmt_delete_compra->execute([$compra_id]);

        $pdo->commit(); // Confirma la transacción
        $_SESSION['success'] = "Compra ID: " . $compra_id . " eliminada exitosamente.";

    } catch (PDOException $e) {
        $pdo->rollBack(); // Revierte la transacción
        $_SESSION['error'] = "Error al eliminar la compra: " . $e->getMessage();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error inesperado: " . $e->getMessage();
    }

    header("Location: ../index.php?vista=compras_farmacia");
    exit();
} else {
    $_SESSION['error'] = "Acceso no autorizado.";
    header("Location: ../index.php?vista=compras_farmacia");
    exit();
}
