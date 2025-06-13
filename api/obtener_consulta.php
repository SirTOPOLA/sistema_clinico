<?php
require '../config/conexion.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
  http_response_code(400);
  echo json_encode(["error" => "ID inválido"]);
  exit;
}

try {
  // Obtener datos de la tabla consultas
  $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id = ?");
  $stmt->execute([$id]);
  $consulta = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$consulta) {
    http_response_code(404);
    echo json_encode(["error" => "Consulta no encontrada"]);
    exit;
  }

  // Obtener datos de la tabla detalle_consulta
  $stmt2 = $pdo->prepare("SELECT * FROM detalle_consulta WHERE id_consulta = ?");
  $stmt2->execute([$id]);
  $detalle = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];

  echo json_encode([
    "consulta" => $consulta,
     "id" => $id,
    "detalle" => $detalle
  ]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["error" => "Error interno"]);
}
?>
