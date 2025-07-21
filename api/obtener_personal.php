<?php
// Asegurar que la sesión esté iniciada (si es necesario para permisos, etc.)
// session_start();
// if (!isset($_SESSION['usuario'])) {
//     http_response_code(401); // Unauthorized
//     echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
//     exit();
// }

// Incluye tu archivo de conexión a la base de datos
// Asegúrate de que $pdo esté disponible
require_once '../config/conexion.php'; 

header('Content-Type: application/json'); // Indicar que la respuesta es JSON

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$search = isset($_GET['search']) ? $_GET['search'] : '';

$offset = ($page - 1) * $recordsPerPage;

// Construir la consulta SQL
$sql = "SELECT p.*, u.nombre_usuario 
        FROM personal p 
        LEFT JOIN usuarios u ON p.id = u.id_personal";

$countSql = "SELECT COUNT(*) AS total FROM personal p LEFT JOIN usuarios u ON p.id = u.id_personal";

$params = [];
$whereClauses = [];

if (!empty($search)) {
    $searchParam = '%' . $search . '%';
    $whereClauses[] = "(p.nombre LIKE :search OR 
                         p.apellidos LIKE :search OR 
                         p.correo LIKE :search OR 
                         p.telefono LIKE :search OR 
                         p.especialidad LIKE :search OR
                         u.nombre_usuario LIKE :search)";
    $params[':search'] = $searchParam;
}

if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
    $countSql .= " WHERE " . implode(" AND ", $whereClauses);
}

$sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";

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
    $personal = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalPages = ceil($totalRecords / $recordsPerPage);

    echo json_encode([
        'success' => true,
        'personal' => $personal,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalRecords' => $totalRecords
    ]);

} catch (PDOException $e) {
    // Logear el error para depuración, pero no mostrar detalles sensibles al usuario
    error_log("Error al obtener personal: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Error en el servidor al obtener los datos.']);
}
?>