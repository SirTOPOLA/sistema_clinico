<?php
/**
 * Vista unificada de operaciones contables de clínica
 * Secciones: Consultas, Analíticas, Farmacia (Ventas/Compras/Presupuestos)
 * Stack: PHP 8+, MySQL (PDO), Bootstrap 5.3+, Bootstrap Icons
 * Autor: (ajusta a tu nombre)
 * Nota: Este archivo es autocontenido para DEMO. En producción separa vistas/controladores.
 */

// =============================
//  CONFIGURACIÓN BÁSICA / PDO
// =============================

declare(strict_types=1);

// Habilita errores en desarrollo
ini_set('display_errors', '1');
error_reporting(E_ALL);

// ---- Ajusta tus credenciales ----
const DB_DSN = 'mysql:host=localhost;dbname=clinica;charset=utf8mb4';
const DB_USER = 'root';
const DB_PASS = '';

/* try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    die('Error de conexión a la BD: ' . htmlspecialchars($e->getMessage()));
} */

// =============================
//  UTILIDADES BÁSICAS
// =============================

function money(float|string|null $n): string
{
    if ($n === null || $n === '')
        return '0';
    return number_format((float) $n, 2, ',', '.');
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE)
        session_start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function check_csrf(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE)
        session_start();
    $ok = isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf']);
    if (!$ok) {
        http_response_code(403);
        die('CSRF inválido');
    }
}

function clean(string $s): string
{
    return trim(filter_var($s, FILTER_SANITIZE_STRING));
}

// =============================
//  CONTROLADOR LIGERO (POST)
// =============================

$flash = null; // Para mostrar toast
 
// =============================
//  CONSULTAS RÁPIDAS PARA LISTADOS/KPIs
// =============================

// KPIs del día (puedes optimizar con vistas/materialized)
$kpi = [
    'consultas_hoy' => 0,
    'ingresos_consultas' => 0.0,
    'analiticas_hoy' => 0,
    'ingresos_analiticas' => 0.0,
    'ventas_hoy' => 0,
    'ingresos_farmacia' => 0.0,
    'pendientes_prov' => 0.0,
];

try {
    $kpi['consultas_hoy'] = (int) $pdo->query("SELECT COUNT(*) FROM consultas WHERE DATE(fecha_registro)=CURDATE()")->fetchColumn();
    $kpi['ingresos_consultas'] = (float) $pdo->query("SELECT COALESCE(SUM(precio),0) FROM consultas WHERE pagado=1 AND DATE(fecha_registro)=CURDATE()")->fetchColumn();
    $kpi['analiticas_hoy'] = (int) $pdo->query("SELECT COUNT(*) FROM analiticas WHERE DATE(fecha_registro)=CURDATE()")->fetchColumn();
    $kpi['ingresos_analiticas'] = (float) $pdo->query("SELECT COALESCE(SUM(p.cantidad),0) FROM pagos p WHERE DATE(p.fecha_registro)=CURDATE()")->fetchColumn();
    $kpi['ventas_hoy'] = (int) $pdo->query("SELECT COUNT(*) FROM ventas WHERE fecha=CURDATE()")->fetchColumn();
    $kpi['ingresos_farmacia'] = (float) $pdo->query("SELECT COALESCE(SUM(monto_total),0) FROM ventas WHERE fecha=CURDATE()")->fetchColumn();
    $kpi['pendientes_prov'] = (float) $pdo->query("SELECT COALESCE(SUM(monto_pendiente),0) FROM compras WHERE estado_pago IN ('PENDIENTE','PARCIAL')")->fetchColumn();
} catch (Throwable $e) {
    // Silencia en demo
}

// Listados cortos (últimos 20)
/* $consultas = $pdo->query("SELECT id, id_paciente, precio, pagado, fecha_registro FROM consultas
             ORDER BY id DESC LIMIT 20")->fetchAll(); */
$consultas = $pdo->query("SELECT c.id, c.id_paciente, 
                CONCAT(p.nombre,' ',p.apellidos)  AS nombre_paciente, c.precio, 
                c.pagado, c.fecha_registro
             FROM consultas AS c
             INNER JOIN pacientes AS p ON c.id_paciente = p.id
             ORDER BY c.id DESC
             LIMIT 20
         ")->fetchAll();

$analiticas = $pdo->query("SELECT a.id, 
                a.id_paciente, a.id_tipo_prueba, a.estado, a.pagado, a.tipo_pago, a.fecha_registro,
                tp.nombre AS nombre_prueba, tp.precio, CONCAT(p.nombre,' ',p.apellidos)  AS nombre_paciente
                        FROM analiticas AS a
                         INNER JOIN pacientes AS p ON a.id_paciente = p.id
                          INNER JOIN tipo_pruebas AS tp ON a.id_tipo_prueba = tp.id
                         ORDER BY a.id DESC LIMIT 20")->fetchAll();
$ventas = $pdo->query("SELECT v.id, v.paciente_id, v.fecha, v.monto_total, 
                v.estado_pago, v.metodo_pago, CONCAT(p.nombre,' ',p.apellidos)  AS nombre_paciente
                    FROM ventas AS v
                    INNER JOIN pacientes AS p ON v.paciente_id = p.id
                     ORDER BY v.id DESC LIMIT 20")->fetchAll();
$compras = $pdo->query("SELECT c.id, c.proveedor_id, c.fecha, c.personal_id, c.monto_entregado,
                            c.total, c.estado_pago, c.monto_pendiente, c.monto_gastado, c.cambio_devuelto,
                            c.codigo_factura AS factura, pr.nombre AS nombre_proveedor,
                            CONCAT(pe.nombre, ' ', pe.apellidos) AS personal_nombre
                            FROM compras AS c
                            INNER JOIN proveedores AS pr ON c.proveedor_id = pr.id
                             LEFT JOIN personal pe ON c.personal_id = pe.id
                            ORDER BY c.id DESC LIMIT 20")->fetchAll();
 $productos = $pdo->query("SELECT * FROM productos ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$proveedores = $pdo->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll();
$pacientes = $pdo->query("SELECT id, CONCAT(COALESCE(nombre,''),' ',COALESCE(apellidos,'')) AS nom FROM pacientes ORDER BY nom ASC LIMIT 200")->fetchAll();
 
    $personal = $pdo->query("SELECT id, nombre, apellidos FROM personal ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
   


try {


// === Consultar compras con nombre de proveedor y personal ===
$sql_compras = "SELECT c.*, 
                       p.nombre AS proveedor_nombre, 
                       CONCAT(pe.nombre, ' ', pe.apellidos) AS personal_nombre
                FROM compras c
                LEFT JOIN proveedores p ON c.proveedor_id = p.id
                LEFT JOIN personal pe ON c.personal_id = pe.id
                ORDER BY c.fecha DESC";
$stmt_compras = $pdo->query($sql_compras);
$compras = $stmt_compras->fetchAll(PDO::FETCH_ASSOC);

// === Consultar detalles de compras agrupados por compra_id ===
$sql_detalles = "SELECT * FROM compras_detalle";
$stmt_detalles = $pdo->query($sql_detalles);
$comprasDetalle = [];
while ($row = $stmt_detalles->fetch(PDO::FETCH_ASSOC)) {
    $comprasDetalle[$row['compra_id']][] = $row;
}

// === Consultar proveedores ===
$sql_proveedores = "SELECT id, nombre FROM proveedores ORDER BY nombre";
$stmt_proveedores = $pdo->query($sql_proveedores);
$proveedores = $stmt_proveedores->fetchAll(PDO::FETCH_ASSOC);

// === Consultar personal ===
$sql_personal = "SELECT id, nombre, apellidos FROM personal ORDER BY nombre";
$stmt_personal = $pdo->query($sql_personal);
$personal = $stmt_personal->fetchAll(PDO::FETCH_ASSOC);

// === Consultar productos ===

$productos = $pdo->query("SELECT * FROM productos ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
$compras = [];
$comprasDetalle = [];
$proveedores = [];
$personal = [];
$productos = [];
$mensaje_error = "Error al consultar la base de datos: " . $e->getMessage();

} catch (Exception $e) {
$compras = [];
$comprasDetalle = [];
$proveedores = [];
$personal = [];
$productos = [];
$mensaje_error = "Error general: " . $e->getMessage();
}
