<?php
session_start();
require '../config/conexion.php'; // Archivo donde creas el objeto PDO $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ingreso = $_POST['id_ingreso'] ?? null;
    $fecha_alta = $_POST['fecha_alta'] ?? null;
    $token=1;

    if (!$id_ingreso || !$fecha_alta) {
        $_SESSION['error'] = "Datos incompletos para dar de alta.";
       header('Location: ../index.php?vista=ingresos');
        exit;
    }

    try {
        $sql = "UPDATE ingresos SET fecha_alta = :fecha_alta, token=:token WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fecha_alta' => $fecha_alta,
            ':token' =>$token,
            ':id' => $id_ingreso
        ]);

        $_SESSION['success'] = "Paciente dado de alta correctamente.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al dar de alta el paciente: " . $e->getMessage();
    }

    header('Location: ../index.php?vista=ingresos');
    exit;
} else {
   header('Location: ../index.php?vista=ingresos');
    exit;
}
