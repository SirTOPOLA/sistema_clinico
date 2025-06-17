<?php
session_start();
require '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_analitica = $_POST['id_analitica'] ?? null;
    $resultado = trim($_POST['resultado'] ?? '');
    $valores_referencia = trim($_POST['valores_referencia'] ?? '');
    $estado=1;

    if (!$id_analitica || $resultado === '') {
        $_SESSION['error'] = "Todos los campos obligatorios deben ser rellenados.";
        header("Location: ../index.php?vista=analiticas");
        exit;
    }

    try {
        $sql = "UPDATE analiticas SET resultado = :resultado, valores_refencia = :valores_referencia, estado= :estado WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':resultado' => $resultado,
            ':valores_referencia' => $valores_referencia,
            ':estado'=>$estado,
            ':id' => $id_analitica
        ]);

        $_SESSION['success'] = "Resultado guardado correctamente.";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error al guardar resultado: " . $e->getMessage();
    }

    header("Location: ../index.php?vista=analiticas");
    exit;
}
