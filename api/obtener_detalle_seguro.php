<?php
require_once '../config/conexion.php';

if (empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID de seguro no proporcionado.']);
    exit;
}

try {
    $seguro_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
    $response = [];

    // 1. Obtener detalles del seguro y el titular
    $sql_seguro = "
        SELECT 
            s.id,
            s.titular_id,
            CONCAT(p.nombre, ' ', p.apellidos) AS nombre_titular,
            s.monto_inicial,
            s.saldo_actual,
            s.fecha_deposito,
            s.metodo_pago
        FROM 
            seguros s
        JOIN 
            pacientes p ON s.titular_id = p.id
        WHERE 
            s.id = ?
    ";
    $stmt_seguro = $pdo->prepare($sql_seguro);
    $stmt_seguro->execute([$seguro_id]);
    $response['seguro'] = $stmt_seguro->fetch(PDO::FETCH_ASSOC);

    if (!$response['seguro']) {
        throw new Exception("Seguro no encontrado.");
    }

    // 2. Obtener beneficiarios del seguro
    $sql_beneficiarios = "
        SELECT 
            sb.paciente_id,
            CONCAT(p.nombre, ' ', p.apellidos) AS nombre_paciente
        FROM 
            seguros_beneficiarios sb
        JOIN 
            pacientes p ON sb.paciente_id = p.id
        WHERE 
            sb.seguro_id = ?
    ";
    $stmt_beneficiarios = $pdo->prepare($sql_beneficiarios);
    $stmt_beneficiarios->execute([$seguro_id]);
    $response['beneficiarios'] = $stmt_beneficiarios->fetchAll(PDO::FETCH_ASSOC);

    // 3. Obtener movimientos del seguro
    $sql_movimientos = "
        SELECT 
            ms.tipo,
            ms.monto,
            ms.fecha,
            ms.descripcion,
            CONCAT(p.nombre, ' ', p.apellidos) AS nombre_paciente
        FROM 
            movimientos_seguro ms
        JOIN 
            pacientes p ON ms.paciente_id = p.id
        WHERE 
            ms.seguro_id = ?
        ORDER BY 
            ms.fecha DESC
    ";
    $stmt_movimientos = $pdo->prepare($sql_movimientos);
    $stmt_movimientos->execute([$seguro_id]);
    $response['movimientos'] = $stmt_movimientos->fetchAll(PDO::FETCH_ASSOC);

    // 4. Obtener préstamos (deuda) del titular
    $sql_prestamos = "
        SELECT 
            total,
            estado,
            fecha
        FROM 
            prestamos 
        WHERE 
            paciente_id = ? AND total > 0
        ORDER BY
            fecha DESC
    ";
    $stmt_prestamos = $pdo->prepare($sql_prestamos);
    $stmt_prestamos->execute([$response['seguro']['titular_id']]);
    $response['prestamos'] = $stmt_prestamos->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>