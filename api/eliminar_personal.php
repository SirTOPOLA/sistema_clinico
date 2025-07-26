<?php
// session_start();
// if (!isset($_SESSION['usuario'])) {
//     header("Location: ../login.php");
//     exit();
// }

require_once '../config/conexion.php'; // Asegúrate de que esta ruta sea correcta

header('Content-Type: application/json'); // Indicar respuesta JSON

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de personal no válido.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM personal WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Personal eliminado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el personal.']);
        }
    } catch (PDOException $e) {
        error_log("Error al eliminar personal: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de personal no proporcionado.']);
}
?>