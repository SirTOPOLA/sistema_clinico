<?php
header('Content-Type: application/json');
require_once '../includes/conexion.php'; // ajusta según tu estructura

try {
    $resumen = [];

    // Lista de consultas SQL para las tarjetas
    $querys = [
        ['titulo' => 'Pacientes', 'sql' => 'SELECT COUNT(*) as total FROM pacientes', 'icono' => 'bi-people-fill'],
        ['titulo' => 'Consultas', 'sql' => 'SELECT COUNT(*) as total FROM consultas', 'icono' => 'bi-journal-medical'],
        ['titulo' => 'Triajes', 'sql' => 'SELECT COUNT(*) as total FROM triajes', 'icono' => 'bi-activity'],
        ['titulo' => 'Recetas', 'sql' => 'SELECT COUNT(*) as total FROM recetas', 'icono' => 'bi-prescription'],
        ['titulo' => 'Laboratorios', 'sql' => 'SELECT COUNT(*) as total FROM ordenes_laboratorio', 'icono' => 'bi-eyedropper'],
        ['titulo' => 'Pagos', 'sql' => 'SELECT COUNT(*) as total FROM pagos', 'icono' => 'bi-cash-stack'],
    ];

    foreach ($querys as $q) {
        $stmt = $pdo->query($q['sql']);
        $fila = $stmt->fetch();
        $resumen[] = [
            'titulo' => $q['titulo'],
            'total' => $fila['total'],
            'icono' => $q['icono']
        ];
    }

    echo json_encode(['status' => true, 'message' => 'EXITO', 'data' => $resumen]);

} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
