<?php
session_start();
require '../config/conexion.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id_usuario = $_POST['id_usuario'] ?? null;
        $id_paciente = $_POST['id_paciente'] ?? null;
        $id_sala = $_POST['id_sala'] ?? null;
        $fecha_ingreso = $_POST['fecha_ingreso'] ?? null;
        $cama = $_POST['cama'] ?? null;

        if (!$id_usuario || !$id_paciente || !$id_sala || !$fecha_ingreso) {
            throw new Exception("Todos los campos obligatorios deben ser completados.");
        }

        // Insertar ingreso en la base de datos
        $stmt = $pdo->prepare("
            INSERT INTO ingresos (id_usuario, id_paciente, id_sala, fecha_ingreso, numero_cama)
            VALUES (:id_usuario, :id_paciente, :id_sala, :fecha_ingreso, :numero_cama)
        ");

        $stmt->execute([
            ':id_usuario' => $id_usuario,
            ':id_paciente' => $id_paciente,
            ':id_sala' => $id_sala,
            ':fecha_ingreso' => $fecha_ingreso,
            ':numero_cama' => $cama
        ]);

        header("Location: ../index.php?vista=ingresos&success=1");
        exit;
    } else {
        throw new Exception("Método no permitido.");
    }
} catch (Exception $e) {
    // Opcional: guardar errores en log
    // error_log($e->getMessage());
    header("Location: ../index.php?vista=ingresos&error=" . urlencode($e->getMessage()));
    exit;
}
