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
        return '0,00';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        check_csrf();
        $accion = $_POST['accion'] ?? '';

        // Agrupa operaciones en transacciones para consistencia
        $pdo->beginTransaction();

        switch ($accion) {
            case 'cobrar_consulta': {
                // Campos mínimos
                $consulta_id = (int) ($_POST['consulta_id'] ?? 0);
                $monto = (float) ($_POST['monto'] ?? 0);
                $metodo = clean($_POST['metodo_pago'] ?? 'EFECTIVO');

                // Valida existencia y estado
                $st = $pdo->prepare("SELECT id, precio, pagado FROM consultas WHERE id = ? LIMIT 1");
                $st->execute([$consulta_id]);
                $row = $st->fetch();
                if (!$row)
                    throw new RuntimeException('Consulta no encontrada');
                if ((int) $row['pagado'] === 1)
                    throw new RuntimeException('Consulta ya pagada');
                if ($monto <= 0)
                    throw new RuntimeException('Monto inválido');

                // Marca pagado y guarda un asiento simple (puedes crear tabla asientos)
                $upd = $pdo->prepare("UPDATE consultas SET pagado = 1 WHERE id = ?");
                $upd->execute([$consulta_id]);

                // (Opcional) Inserta en tabla ventas para dejar rastro contable global
                $ins = $pdo->prepare("INSERT INTO ventas (paciente_id, usuario_id, fecha, monto_total, monto_recibido, cambio_devuelto, motivo_descuento, descuento_global, seguro, estado_pago, metodo_pago)
                                       VALUES (NULL, NULL, CURDATE(), ?, ?, 0.00, NULL, 0.00, 0, 'PAGADO', ?)");
                $ins->execute([$monto, $monto, $metodo]);

                $flash = ['type' => 'success', 'msg' => 'Consulta cobrada correctamente'];
                break;
            }

            case 'cobrar_analitica': {
                $analitica_id = (int) ($_POST['analitica_id'] ?? 0);
                $tipo_pago = clean($_POST['tipo_pago'] ?? 'EFECTIVO');
                $monto = (float) ($_POST['monto'] ?? 0);

                $st = $pdo->prepare("SELECT id, pagado FROM analiticas WHERE id = ? LIMIT 1");
                $st->execute([$analitica_id]);
                $an = $st->fetch();
                if (!$an)
                    throw new RuntimeException('Analítica no encontrada');
                if ((int) $an['pagado'] === 1)
                    throw new RuntimeException('Analítica ya pagada');
                if ($monto <= 0)
                    throw new RuntimeException('Monto inválido');

                // Marca pagada y tipo de pago
                $upd = $pdo->prepare("UPDATE analiticas SET pagado = 1, tipo_pago = ? WHERE id = ?");
                $upd->execute([$tipo_pago, $analitica_id]);

                // Registrar en pagos (si deseas)
                $ins = $pdo->prepare("INSERT INTO pagos (cantidad, id_analitica, id_tipo_prueba) VALUES (?, ?, 0)");
                $ins->execute([$monto, $analitica_id]);

                $flash = ['type' => 'success', 'msg' => 'Analítica cobrada correctamente'];
                break;
            }

            case 'nueva_venta': {
                // Venta Farmacia con 1..n items (simplificado)
                $paciente_id = (int) ($_POST['paciente_id'] ?? 0);
                $metodo_pago = clean($_POST['metodo_pago'] ?? 'EFECTIVO');
                $items = json_decode($_POST['items_json'] ?? '[]', true);
                if (!is_array($items) || empty($items))
                    throw new RuntimeException('Agrega productos');

                // Calcula totales
                $total = 0.0;
                foreach ($items as $it) {
                    $cant = max(1, (int) ($it['cantidad'] ?? 1));
                    $precio = (float) ($it['precio'] ?? 0);
                    $total += $cant * $precio;
                }

                // Insert venta
                $iv = $pdo->prepare("INSERT INTO ventas (paciente_id, usuario_id, fecha, monto_total, monto_recibido, cambio_devuelto, descuento_global, estado_pago, metodo_pago) VALUES (?, NULL, CURDATE(), ?, ?, 0.00, 0.00, 'PAGADO', ?)");
                $iv->execute([$paciente_id ?: null, $total, $total, $metodo_pago]);
                $venta_id = (int) $pdo->lastInsertId();

                // Inserta detalle + descuenta stock
                $idv = $pdo->prepare("INSERT INTO ventas_detalle (venta_id, producto_id, cantidad, precio_venta, descuento_unitario) VALUES (?, ?, ?, ?, 0.00)");
                $upd = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ? AND stock_actual >= ?");
                foreach ($items as $it) {
                    $pid = (int) $it['producto_id'];
                    $cant = max(1, (int) $it['cantidad']);
                    $precio = (float) $it['precio'];
                    $idv->execute([$venta_id, $pid, $cant, $precio]);
                    $upd->execute([$cant, $pid, $cant]);
                }

                $flash = ['type' => 'success', 'msg' => 'Venta registrada'];
                break;
            }

            case 'nueva_compra': {
                $proveedor_id = (int) ($_POST['proveedor_id'] ?? 0);
                $monto_entregado = (float) ($_POST['monto_entregado'] ?? 0);
                $items = json_decode($_POST['items_json'] ?? '[]', true);
                if (!$proveedor_id)
                    throw new RuntimeException('Proveedor requerido');
                if (!is_array($items) || empty($items))
                    throw new RuntimeException('Agrega productos');

                $total = 0.0;
                foreach ($items as $it) {
                    $cant = max(1, (int) ($it['cantidad'] ?? 1));
                    $precio = (float) ($it['precio_compra'] ?? 0);
                    $total += $cant * $precio;
                }

                $monto_gastado = min($monto_entregado, $total);
                $monto_pend = max(0, $total - $monto_entregado);
                $estado = $monto_pend <= 0 ? 'PAGADO' : ($monto_gastado > 0 ? 'PARCIAL' : 'PENDIENTE');

                $ic = $pdo->prepare("INSERT INTO compras (proveedor_id, codigo_factura, personal_id, fecha, monto_entregado, monto_gastado, cambio_devuelto, monto_pendiente, total, estado_pago) VALUES (?, ?, NULL, CURDATE(), ?, ?, 0.00, ?, ?, ?)");
                $ic->execute([$proveedor_id, null, $monto_entregado, $monto_gastado, $monto_pend, $total, $estado]);
                $compra_id = (int) $pdo->lastInsertId();

                $idc = $pdo->prepare("INSERT INTO compras_detalle (compra_id, producto_id, cantidad, precio_compra) VALUES (?, ?, ?, ?)");
                $stk = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?");

                foreach ($items as $it) {
                    $pid = (int) $it['producto_id'];
                    $cant = max(1, (int) $it['cantidad']);
                    $precio = (float) $it['precio_compra'];
                    $idc->execute([$compra_id, $pid, $cant, $precio]);
                    $stk->execute([$cant, $pid]);
                }

                $flash = ['type' => 'success', 'msg' => 'Compra registrada'];
                break;
            }

            case 'pago_proveedor': {
                $compra_id = (int) ($_POST['compra_id'] ?? 0);
                $proveedor_id = (int) ($_POST['proveedor_id'] ?? 0);
                $monto = (float) ($_POST['monto'] ?? 0);
                $metodo = clean($_POST['metodo_pago'] ?? 'EFECTIVO');
                if ($monto <= 0)
                    throw new RuntimeException('Monto inválido');

                $pdo->prepare("INSERT INTO pagos_proveedores (compra_id, proveedor_id, monto, fecha, metodo_pago) VALUES (?, ?, ?, CURDATE(), ?)")
                    ->execute([$compra_id ?: null, $proveedor_id ?: null, $monto, $metodo]);

                // Actualiza estado de compra
                if ($compra_id) {
                    $st = $pdo->prepare("SELECT total, monto_entregado FROM compras WHERE id = ?");
                    $st->execute([$compra_id]);
                    $c = $st->fetch();
                    if ($c) {
                        $nuevo = (float) $c['monto_entregado'] + $monto;
                        $pend = max(0, (float) $c['total'] - $nuevo);
                        $estado = $pend <= 0 ? 'PAGADO' : ($nuevo > 0 ? 'PARCIAL' : 'PENDIENTE');
                        $pdo->prepare("UPDATE compras SET monto_entregado = ?, monto_pendiente = ?, estado_pago = ? WHERE id = ?")
                            ->execute([$nuevo, $pend, $estado, $compra_id]);
                    }
                }

                $flash = ['type' => 'success', 'msg' => 'Pago a proveedor registrado'];
                break;
            }

            default:
                throw new RuntimeException('Acción no soportada');
        }

        $pdo->commit();

    } catch (Throwable $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        $flash = ['type' => 'danger', 'msg' => $e->getMessage()];
    }
}

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
$compras = $pdo->query("SELECT c.id, c.proveedor_id, c.fecha, 
                            c.total, c.estado_pago, c.monto_pendiente, 
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
   
?>