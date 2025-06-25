<?php
// api/reports.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Establece el encabezado para indicar que la respuesta será en formato JSON
header('Content-Type: application/json');


include_once('../config/conexion.php');
// Inicializa la respuesta por defecto en caso de error o solicitud inválida
$response = ['status' => 'error', 'message' => 'Solicitud inválida'];

// Verifica si la solicitud es de tipo POST y si se ha enviado la acción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Recupera las fechas de inicio y fin, si están presentes en la solicitud
    $startDate = $_POST['startDate'] ?? null;
    $endDate = $_POST['endDate'] ?? null;

    try {
        // Utiliza un switch para manejar diferentes acciones de reporte
        switch ($action) {
            case 'getTotalIncome':
                // Llama a la función para obtener el ingreso total
                $response = getTotalIncome($pdo, $startDate, $endDate);
                break;
            case 'getIncomeByType':
                // Llama a la función para obtener el ingreso por tipo de servicio
                $response = getIncomeByType($pdo, $startDate, $endDate);
                break;
            case 'getIncomeByPatient':
                // Llama a la función para obtener el ingreso por paciente
                $response = getIncomeByPatient($pdo, $startDate, $endDate);
                break;
            case 'getOutstandingConsultations':
                // Llama a la función para obtener consultas pendientes de pago
                $response = getOutstandingConsultations($pdo);
                break;
            case 'getOutstandingAnalytics':
                // Llama a la función para obtener analíticas pendientes de pago
                $response = getOutstandingAnalytics($pdo);
                break;
            default:
                // Si la acción no es reconocida, establece un mensaje de error
                $response = ['status' => 'error', 'message' => 'Acción desconocida'];
                break;
        }
    } catch (PDOException $e) {
        // Captura cualquier excepción PDO que ocurra durante las consultas
        $response = ['status' => 'error', 'message' => 'Error en la base de datos: ' . $e->getMessage()];
    }
}

// Envía la respuesta JSON al cliente
echo json_encode($response);

// Ya no es necesario cerrar la conexión explícitamente con PDO,
// se cerrará automáticamente cuando el script finalice o la variable $pdo se destruya.


// --- Funciones para Generar Reportes ---

/**
 * Obtiene el ingreso total de consultas y analíticas pagadas dentro de un rango de fechas.
 * @param PDO $pdo Objeto de conexión a la base de datos PDO.
 * @param string|null $startDate Fecha de inicio del rango (formato YYYY-MM-DD).
 * @param string|null $endDate Fecha de fin del rango (formato YYYY-MM-DD).
 * @return array Un array con el estado y los datos del reporte.
 */
function getTotalIncome($pdo, $startDate, $endDate) {
    $totalIncome = 0;

    // Consulta para obtener el total de ingresos de consultas pagadas
    $queryConsultas = "SELECT SUM(precio) AS total_consultas FROM consultas WHERE pagado = 1";
    $paramsConsultas = [];
    if ($startDate && $endDate) {
        $queryConsultas .= " AND fecha_registro BETWEEN ? AND ?";
        $paramsConsultas = [$startDate, $endDate];
    }
    // Prepara y ejecuta la consulta usando PDO
    $stmtConsultas = $pdo->prepare($queryConsultas);
    $stmtConsultas->execute($paramsConsultas);
    $totalConsultas = $stmtConsultas->fetchColumn() ?? 0; // fetchColumn() obtiene el valor de la primera columna

    // Consulta para obtener el total de ingresos de pagos de analíticas
    $queryPagos = "SELECT SUM(cantidad) AS total_pagos FROM pagos";
    $paramsPagos = [];
    if ($startDate && $endDate) {
        $queryPagos .= " WHERE fecha_registro BETWEEN ? AND ?";
        $paramsPagos = [$startDate, $endDate];
    }
    $stmtPagos = $pdo->prepare($queryPagos);
    $stmtPagos->execute($paramsPagos);
    $totalPagos = $stmtPagos->fetchColumn() ?? 0;

    // Suma ambos totales para obtener el ingreso total general
    $totalIncome = $totalConsultas + $totalPagos;

    return ['status' => 'success', 'data' => ['totalIncome' => (float)$totalIncome]];
}

/**
 * Obtiene el ingreso desglosado por tipo de servicio (consultas vs. analíticas).
 * @param PDO $pdo Objeto de conexión a la base de datos PDO.
 * @param string|null $startDate Fecha de inicio del rango.
 * @param string|null $endDate Fecha de fin del rango.
 * @return array Un array con el estado y los datos del reporte.
 */
function getIncomeByType($pdo, $startDate, $endDate) {
    $incomeData = ['consultations' => 0, 'analytics' => 0];

    // Ingresos de consultas
    $queryConsultas = "SELECT SUM(precio) AS total_consultas FROM consultas WHERE pagado = 1";
    $paramsConsultas = [];
    if ($startDate && $endDate) {
        $queryConsultas .= " AND fecha_registro BETWEEN ? AND ?";
        $paramsConsultas = [$startDate, $endDate];
    }
    $stmtConsultas = $pdo->prepare($queryConsultas);
    $stmtConsultas->execute($paramsConsultas);
    $incomeData['consultations'] = $stmtConsultas->fetchColumn() ?? 0;

    // Ingresos de analíticas
    $queryPagos = "SELECT SUM(cantidad) AS total_pagos FROM pagos";
    $paramsPagos = [];
    if ($startDate && $endDate) {
        $queryPagos .= " WHERE fecha_registro BETWEEN ? AND ?";
        $paramsPagos = [$startDate, $endDate];
    }
    $stmtPagos = $pdo->prepare($queryPagos);
    $stmtPagos->execute($paramsPagos);
    $incomeData['analytics'] = $stmtPagos->fetchColumn() ?? 0;

    return ['status' => 'success', 'data' => $incomeData];
}

/**
 * Obtiene el ingreso total generado por cada paciente.
 * @param PDO $pdo Objeto de conexión a la base de datos PDO.
 * @param string|null $startDate Fecha de inicio del rango.
 * @param string|null $endDate Fecha de fin del rango.
 * @return array Un array con el estado y los datos del reporte (lista de pacientes y sus ingresos).
 */
function getIncomeByPatient($pdo, $startDate, $endDate) {
    $patientIncome = [];

    // Ingresos de consultas por paciente
    $queryConsultas = "
        SELECT
            p.id,
            p.nombre,
            p.apellidos,
            SUM(c.precio) AS total_consultas_paid
        FROM consultas c
        JOIN pacientes p ON c.id_paciente = p.id
        WHERE c.pagado = 1
    ";
    $paramsConsultas = [];
    if ($startDate && $endDate) {
        $queryConsultas .= " AND c.fecha_registro BETWEEN ? AND ?";
        $paramsConsultas = [$startDate, $endDate];
    }
    $queryConsultas .= " GROUP BY p.id, p.nombre, p.apellidos";

    $stmtConsultas = $pdo->prepare($queryConsultas);
    $stmtConsultas->execute($paramsConsultas);
    while ($row = $stmtConsultas->fetch(PDO::FETCH_ASSOC)) {
        $patientId = $row['id'];
        $fullName = $row['nombre'] . ' ' . $row['apellidos'];
        $patientIncome[$patientId] = [
            'id' => $patientId,
            'name' => $fullName,
            'total_income' => (float)$row['total_consultas_paid'],
            'consultation_income' => (float)$row['total_consultas_paid'],
            'analytic_income' => 0
        ];
    }

    // Ingresos de pagos de analíticas por paciente
    $queryPagos = "
        SELECT
            pa.id,
            pa.nombre,
            pa.apellidos,
            SUM(pg.cantidad) AS total_pagos
        FROM pagos pg
        JOIN analiticas a ON pg.id_analitica = a.id
        JOIN pacientes pa ON a.id_paciente = pa.id
    ";
    $paramsPagos = [];
    if ($startDate && $endDate) {
        $queryPagos .= " WHERE pg.fecha_registro BETWEEN ? AND ?";
        $paramsPagos = [$startDate, $endDate];
    }
    $queryPagos .= " GROUP BY pa.id, pa.nombre, pa.apellidos";

    $stmtPagos = $pdo->prepare($queryPagos);
    $stmtPagos->execute($paramsPagos);
    while ($row = $stmtPagos->fetch(PDO::FETCH_ASSOC)) {
        $patientId = $row['id'];
        $fullName = $row['nombre'] . ' ' . $row['apellidos'];
        if (isset($patientIncome[$patientId])) {
            $patientIncome[$patientId]['total_income'] += (float)$row['total_pagos'];
            $patientIncome[$patientId]['analytic_income'] += (float)$row['total_pagos'];
        } else {
            $patientIncome[$patientId] = [
                'id' => $patientId,
                'name' => $fullName,
                'total_income' => (float)$row['total_pagos'],
                'consultation_income' => 0,
                'analytic_income' => (float)$row['total_pagos']
            ];
        }
    }

    $data = array_values($patientIncome);
    return ['status' => 'success', 'data' => $data];
}

/**
 * Obtiene una lista de consultas que están pendientes de pago.
 * @param PDO $pdo Objeto de conexión a la base de datos PDO.
 * @return array Un array con el estado y los datos del reporte (lista de consultas pendientes).
 */
function getOutstandingConsultations($pdo) {
    $outstandingConsultations = [];

    $query = "
        SELECT
            c.id AS consulta_id,
            p.nombre,
            p.apellidos,
            c.precio,
            c.fecha_registro,
            c.motivo_consulta
        FROM consultas c
        JOIN pacientes p ON c.id_paciente = p.id
        WHERE c.pagado = 0 AND c.precio > 0
        ORDER BY c.fecha_registro ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $outstandingConsultations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return ['status' => 'success', 'data' => $outstandingConsultations];
}

/**
 * Obtiene una lista de analíticas que están pendientes de pago.
 * @param PDO $pdo Objeto de conexión a la base de datos PDO.
 * @return array Un array con el estado y los datos del reporte (lista de analíticas pendientes).
 */
function getOutstandingAnalytics($pdo) {
    $outstandingAnalytics = [];

    $query = "
        SELECT
            a.id AS analitica_id,
            p.nombre,
            p.apellidos,
            tp.nombre AS tipo_prueba,
            tp.precio,
            a.fecha_registro
        FROM analiticas a
        JOIN pacientes p ON a.id_paciente = p.id
        JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id
        WHERE a.pagado = 0
        ORDER BY a.fecha_registro ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $outstandingAnalytics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return ['status' => 'success', 'data' => $outstandingAnalytics];
}

?>
