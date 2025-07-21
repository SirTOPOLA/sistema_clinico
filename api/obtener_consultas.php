<?php
// session_start();
// if (!isset($_SESSION['usuario'])) {
//     http_response_code(401); // Unauthorized
//     echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
//     exit();
// }

require_once '../config/conexion.php'; // ¡Asegúrate de que esta ruta sea correcta!

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$offset = ($page - 1) * $recordsPerPage;

// Consulta base para obtener consultas
$sql = "SELECT c.*, p.nombre, p.apellidos
        FROM consultas c
        LEFT JOIN pacientes p ON c.id_paciente = p.id";

// Consulta para contar el total de registros
$countSql = "SELECT COUNT(*) AS total 
             FROM consultas c
             LEFT JOIN pacientes p ON c.id_paciente = p.id";

$params = [];
$whereClauses = [];

if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    // Buscar en motivo_consulta, nombre y apellidos del paciente
    $whereClauses[] = "(c.motivo_consulta LIKE :search OR 
                         p.nombre LIKE :search OR 
                         p.apellidos LIKE :search)";
    $params[':search'] = $searchParam;
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
    $countSql .= " WHERE " . implode(" AND ", $whereClauses);
}

$sql .= " ORDER BY c.fecha_registro DESC LIMIT :limit OFFSET :offset";

try {
    // Obtener el total de registros para la paginación
    $stmtCount = $pdo->prepare($countSql);
    foreach ($params as $key => &$val) {
        $stmtCount->bindParam($key, $val);
    }
    $stmtCount->execute();
    $totalRecords = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    // Obtener los registros para la página actual
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => &$val) {
        $stmt->bindParam($key, $val);
    }
    $stmt->bindParam(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalPages = ceil($totalRecords / $recordsPerPage);

    echo json_encode([
        'success' => true,
        'consultas' => $consultas,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalRecords' => $totalRecords
    ]);

} catch (PDOException $e) {
    error_log("Error al obtener consultas: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Error en el servidor al obtener los datos de consultas.']);
}
?>