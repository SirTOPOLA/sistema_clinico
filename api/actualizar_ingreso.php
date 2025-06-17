<?php
require '../config/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_ingreso'] ?? null;
    $id_sala = $_POST['id_sala'] ?? null;
    $numero_cama = trim($_POST['numero_cama'] ?? '');
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';

    if ($id && $id_sala && $fecha_ingreso) {
        $stmt = $pdo->prepare("UPDATE ingresos SET id_sala = ?, numero_cama = ?, fecha_ingreso = ? WHERE id = ?");
        $exito = $stmt->execute([$id_sala, $numero_cama, $fecha_ingreso, $id]);

        if ($exito) {
            $_SESSION['success'] = "Ingreso actualizado correctamente.";
        } else {
            $_SESSION['error'] = "Error al actualizar el ingreso.";
        }
    } else {
        $_SESSION['error'] = "Datos incompletos para actualizar.";
    }

   header('Location: ../index.php?vista=ingresos');
    exit;
} else {
    $_SESSION['error'] = "Acceso no permitido.";
   header('Location: ../index.php?vista=ingresos');
    exit;
}
