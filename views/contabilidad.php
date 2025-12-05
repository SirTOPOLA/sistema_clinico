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
                            c.total, c.estado_pago, c.monto_pendiente, pr.nombre AS nombre_proveedor
                            FROM compras AS c
                            INNER JOIN proveedores AS pr
                            ORDER BY c.id DESC LIMIT 20")->fetchAll();
$productos = $pdo->query("SELECT id, nombre, precio_unitario, stock_actual FROM productos ORDER BY nombre ASC LIMIT 100")->fetchAll();
$proveedores = $pdo->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC")->fetchAll();
$pacientes = $pdo->query("SELECT id, CONCAT(COALESCE(nombre,''),' ',COALESCE(apellidos,'')) AS nom FROM pacientes ORDER BY nom ASC LIMIT 200")->fetchAll();

?>

<style>
    body {
        background: #0f172a;
    }

    /* slate-900 */
    .app-shell {
        min-height: 100vh;
    }

    .glass {
        background: rgba(255, 255, 255, .05);
        backdrop-filter: blur(8px);
    }

    .card {
        border: 0;
        border-radius: 1rem;
    }

    .card-header {
        border: 0;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .nav-pills .nav-link {
        border-radius: 0.75rem;
    }

    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #2563eb, #22c55e);
    }

    /* azul->verde */
    .kpi {
        color: #e2e8f0;
    }

    .kpi .value {
        font-size: 1.4rem;
        font-weight: 700;
    }

    .kpi .label {
        font-size: .85rem;
        color: #94a3b8;
    }

    .table thead th {
        color: #94a3b8;
    }

    .table {
        --bs-table-bg: transparent;
        color: #e2e8f0;
    }

    .table-hover tbody tr:hover {
        background: rgba(255, 255, 255, .04);
    }

    .btn-soft {
        background: rgba(255, 255, 255, .08);
        color: #e2e8f0;
        border: 0;
    }

    .btn-soft:hover {
        background: rgba(255, 255, 255, .12);
        color: #fff;
    }

    .form-control,
    .form-select {
        background: #0b1220;
        border: 1px solid #1f2937;
        color: #e5e7eb;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .25)
    }

    .badge-soft {
        background: rgba(255, 255, 255, .08);
        color: #cbd5e1
    }

    .offcanvas {
        background: #0b1220;
        color: #e5e7eb
    }
</style>
<div id="content" class="container-fluid">
    <div class="app-shell d-flex flex-column">
        <!-- Topbar -->
        <nav class="navbar navbar-dark glass sticky-top shadow-sm">
            <div class="container-fluid py-2">
                <a class="navbar-brand fw-bold" href="#"><i class="bi bi-hospital me-2"></i>Finanzas Clínica</a>
                <div class="d-flex gap-2">
                    <button class="btn btn-soft" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAcciones"><i
                            class="bi bi-plus-circle me-1"></i>Acciones rápidas</button>
                    <button class="btn btn-soft" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltros"><i
                            class="bi bi-funnel me-1"></i>Filtros</button>
                </div>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="container my-4">

            <!-- KPIs -->
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="card glass p-3 kpi text-center">
                        <div class="label"><i class="bi bi-clipboard2-pulse me-1"></i>Consultas hoy</div>
                        <div class="value"><?php echo (int) $kpi['consultas_hoy']; ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card glass p-3 kpi text-center">
                        <div class="label"><i class="bi bi-cash-coin me-1"></i>Ingresos consultas</div>
                        <div class="value">XAF <?php echo money($kpi['ingresos_consultas']); ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card glass p-3 kpi text-center">
                        <div class="label"><i class="bi bi-lab me-1"></i>Analíticas hoy</div>
                        <div class="value"><?php echo (int) $kpi['analiticas_hoy']; ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card glass p-3 kpi text-center">
                        <div class="label"><i class="bi bi-cart-check me-1"></i>Ingresos farmacia</div>
                        <div class="value">XAF <?php echo money($kpi['ingresos_farmacia']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Navegación principal (pills) -->
            <ul class="nav nav-pills glass p-2 mb-3 gap-2" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-consultas-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-consultas" type="button" role="tab" aria-controls="pills-consultas"
                        aria-selected="true">
                        <i class="bi bi-clipboard2-pulse me-1"></i>Consultas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-analiticas-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-analiticas" type="button" role="tab" aria-controls="pills-analiticas"
                        aria-selected="false">
                        <i class="bi bi-lab me-1"></i>Analíticas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-farmacia-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-farmacia" type="button" role="tab" aria-controls="pills-farmacia"
                        aria-selected="false">
                        <i class="bi bi-capsule-pill me-1"></i>Farmacia
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">

                <!-- ================= Consultas ================ -->
                <div class="tab-pane fade show active" id="pills-consultas" role="tabpanel"
                    aria-labelledby="pills-consultas-tab" tabindex="0">
                    <div class="card glass">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Consultas recientes</h5>
                            <div class="d-flex gap-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="searchConsultas"
                                        placeholder="Buscar por ID paciente...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Paciente</th>
                                            <th>Precio</th>
                                            <th>Pagado</th>
                                            <th>Fecha</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyConsultas">
                                        <?php foreach ($consultas as $c): ?>
                                            <tr>
                                                <td>#<?php echo (int) $c['id']; ?></td>
                                                <td><?= htmlspecialchars($c['nombre_paciente']); ?></td>
                                                <td>XAF <?php echo money($c['precio'] ?? 0); ?></td>
                                                <td>
                                                    <?php if ((int) $c['pagado'] === 1): ?>
                                                        <span class="badge rounded-pill bg-success"><i class="bi bi-check2"></i>
                                                            Pagado</span>
                                                    <?php else: ?>
                                                        <span class="badge rounded-pill bg-warning text-dark"><i
                                                                class="bi bi-exclamation-triangle"></i> Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($c['fecha_registro']); ?></td>
                                                <td class="text-end">
                                                    <?php if ((int) $c['pagado'] !== 1): ?>
                                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                            data-bs-target="#modalCobrarConsulta"
                                                            data-id="<?php echo (int) $c['id']; ?>"
                                                            data-monto="<?php echo (float) ($c['precio'] ?? 0); ?>">
                                                            <i class="bi bi-cash-coin"></i> Cobrar
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-soft" disabled><i
                                                                class="bi bi-receipt"></i> Factura</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= Analíticas ================ -->
                <div class="tab-pane fade" id="pills-analiticas" role="tabpanel" aria-labelledby="pills-analiticas-tab"
                    tabindex="0">
                    <div class="card glass">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-lab me-2"></i>Analíticas recientes</h5>
                            <div class="d-flex gap-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="searchAnaliticas"
                                        placeholder="Buscar por ID paciente...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Paciente</th>
                                            <th>Tipo prueba</th>
                                            <th>Estado</th>
                                            <th>Pago</th>
                                            <th>Fecha</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyAnaliticas">
                                        <?php foreach ($analiticas as $a): ?>
                                            <tr>
                                                <td>#<?php echo (int) $a['id']; ?></td>
                                                <td><?php echo htmlspecialchars($a['nombre_paciente']); ?></td>
                                                <td><?php echo htmlspecialchars($a['nombre_prueba']); ?></td>
                                                <td><span
                                                        class="badge badge-soft rounded-pill text-black"><?php echo htmlspecialchars($a['estado'] ?? ''); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ((int) $a['pagado'] === 1): ?>
                                                        <span class="badge rounded-pill bg-success"><i class="bi bi-check2"></i>
                                                            Pagado</span>
                                                    <?php else: ?>
                                                        <span class="badge rounded-pill bg-warning text-dark">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($a['fecha_registro']); ?></td>
                                                <td class="text-end">
                                                    <?php if ((int) $a['pagado'] !== 1): ?>
                                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                            data-bs-target="#modalCobrarAnalitica"
                                                            data-id="<?php echo (int) $a['id']; ?>">
                                                            <i class="bi bi-cash-coin"></i> Cobrar
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-soft" disabled><i
                                                                class="bi bi-receipt"></i> Factura</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= Farmacia ================ -->
                <div class="tab-pane fade" id="pills-farmacia" role="tabpanel" aria-labelledby="pills-farmacia-tab"
                    tabindex="0">
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="card glass">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-capsule-pill me-2"></i>Ventas recientes</h5>
                                    <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#modalNuevaVenta"><i class="bi bi-cart-plus"></i> Nueva
                                        venta</button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 align-middle">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Paciente</th>
                                                    <th>Fecha</th>
                                                    <th>Monto</th>
                                                    <th>Pago</th>
                                                    <th class="text-end">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($ventas as $v): ?>
                                                    <tr>
                                                        <td>#<?php echo (int) $v['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($v['nombre_paciente']); ?></td>
                                                        <td><?php echo htmlspecialchars($v['fecha']); ?></td>
                                                        <td>XAF <?php echo money($v['monto_total']); ?></td>
                                                        <td><span
                                                                class="badge rounded-pill bg-success"><?php echo htmlspecialchars($v['metodo_pago']); ?></span>
                                                        </td>
                                                        <td class="text-end">
                                                            <button class="btn btn-sm btn-soft"><i
                                                                    class="bi bi-printer"></i></button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="card glass h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Compras a proveedores</h5>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#modalNuevaCompra"><i class="bi bi-bag-plus"></i> Nueva
                                        compra</button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0 align-middle">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Proveedor</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                    <th>Pendiente</th>
                                                    <th class="text-end">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($compras as $c): ?>
                                                    <tr>
                                                        <td>#<?php echo (int) $c['id']; ?></td>
                                                        <td> <?php echo htmlspecialchars($c['nombre_proveedor']); ?></td>
                                                        <td>XAF <?php echo money($c['total']); ?></td>
                                                        <td><span
                                                                class="badge rounded-pill <?php echo $c['estado_pago'] === 'PAGADO' ? 'bg-success' : ($c['estado_pago'] === 'PARCIAL' ? 'bg-warning text-dark' : 'bg-secondary'); ?>"><?php echo htmlspecialchars($c['estado_pago']); ?></span>
                                                        </td>
                                                        <td>XAF <?php echo money($c['monto_pendiente']); ?></td>
                                                        <td class="text-end">
                                                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                                data-bs-target="#modalPagoProveedor"
                                                                data-id="<?php echo (int) $c['id']; ?>">
                                                                <i class="bi bi-cash-stack"></i> Pagar
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-between">
                                    <span class="text-secondary">Pendiente total</span>
                                    <strong class="text-light">XAF
                                        <?php echo money($kpi['pendientes_prov']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </main>

        <!-- Offcanvas Filtros -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltros">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title"><i class="bi bi-funnel me-2"></i>Filtros</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="mb-3">
                    <label class="form-label">Rango de fechas</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="fdesde">
                        <input type="date" class="form-control" id="fhasta">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Paciente</label>
                    <select class="form-select" id="fpaciente">
                        <option value="">Todos</option>
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?php echo (int) $p['id']; ?>"><?php echo htmlspecialchars($p['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-success w-100"><i class="bi bi-funnel"></i> Aplicar</button>
            </div>
        </div>

        <!-- Offcanvas Acciones rápidas -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAcciones">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title"><i class="bi bi-lightning-charge me-2"></i>Acciones rápidas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaVenta"><i
                            class="bi bi-cart-plus me-1"></i> Registrar venta</button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaCompra"><i
                            class="bi bi-bag-plus me-1"></i> Registrar compra</button>
                    <a href="#pills-consultas" class="btn btn-soft" data-bs-toggle="pill"><i
                            class="bi bi-clipboard2-pulse me-1"></i> Ir a consultas</a>
                    <a href="#pills-analiticas" class="btn btn-soft" data-bs-toggle="pill"><i
                            class="bi bi-lab me-1"></i> Ir a analíticas</a>
                    <a href="#pills-farmacia" class="btn btn-soft" data-bs-toggle="pill"><i
                            class="bi bi-capsule-pill me-1"></i> Ir a farmacia</a>
                </div>
            </div>
        </div>

        <!-- =================== MODALES =================== -->

        <!-- Cobrar consulta -->
        <div class="modal fade" id="modalCobrarConsulta" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Cobrar consulta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="accion" value="cobrar_consulta">
                        <input type="hidden" name="consulta_id" id="cc_consulta_id">
                        <div class="mb-3">
                            <label class="form-label">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text">XAF</span>
                                <input type="number" step="0.01" class="form-control" name="monto" id="cc_monto"
                                    required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Método de pago</label>
                            <select class="form-select" name="metodo_pago">
                                <option>EFECTIVO</option>
                                <option>TARJETA</option>
                                <option>TRANSFERENCIA</option>
                                <option>OTRO</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Confirmar
                            cobro</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Cobrar analítica -->
        <div class="modal fade" id="modalCobrarAnalitica" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Cobrar analítica</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="accion" value="cobrar_analitica">
                        <input type="hidden" name="analitica_id" id="ca_analitica_id">
                        <div class="mb-3">
                            <label class="form-label">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text">XAF</span>
                                <input type="number" step="0.01" class="form-control" name="monto" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de pago</label>
                            <select class="form-select" name="tipo_pago">
                                <option>EFECTIVO</option>
                                <option>SEGURO</option>
                                <option>ADEUDO</option>
                                <option>SIN PAGAR</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Confirmar
                            cobro</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Nueva venta (farmacia) -->
        <div class="modal fade" id="modalNuevaVenta" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="post" onsubmit="return buildVentaItemsJson()">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-cart-plus me-2"></i>Nueva venta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="accion" value="nueva_venta">
                        <input type="hidden" name="items_json" id="venta_items_json">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Paciente (opcional)</label>
                                <select name="paciente_id" class="form-select">
                                    <option value="">Sin paciente</option>
                                    <?php foreach ($pacientes as $p): ?>
                                        <option value="<?php echo (int) $p['id']; ?>">
                                            <?php echo htmlspecialchars($p['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Método de pago</label>
                                <select name="metodo_pago" class="form-select">
                                    <option>EFECTIVO</option>
                                    <option>TARJETA</option>
                                    <option>TRANSFERENCIA</option>
                                    <option>OTRO</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <div class="table-responsive">
                            <table class="table align-middle" id="tablaVentaItems">
                                <thead>
                                    <tr>
                                        <th style="min-width:220px">Producto</th>
                                        <th>Cant.</th>
                                        <th>Precio</th>
                                        <th class="text-end">Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select class="form-select form-select-sm prod-select">
                                                <option value="">Selecciona...</option>
                                                <?php foreach ($productos as $pr): ?>
                                                    <option value="<?php echo (int) $pr['id']; ?>"
                                                        data-precio="<?php echo (float) $pr['precio_unitario']; ?>">
                                                        <?php echo htmlspecialchars($pr['nombre']); ?> — XAF
                                                        <?php echo money($pr['precio_unitario'] ?? 0); ?> (Stock:
                                                        <?php echo (int) $pr['stock_actual']; ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="number" min="1" value="1"
                                                class="form-control form-control-sm cantidad"></td>
                                        <td><input type="number" step="0.01"
                                                class="form-control form-control-sm precio"></td>
                                        <td class="text-end subtotal">XAF 0,00</td>
                                        <td class="text-end"><button type="button" class="btn btn-sm btn-danger"
                                                onclick="removeRow(this)"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end" id="ventaTotal">XAF 0,00</th>
                                        <th class="text-end"><button type="button" class="btn btn-sm btn-primary"
                                                onclick="addRow()"><i class="bi bi-plus"></i></button></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Guardar
                            venta</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Nueva compra -->
        <div class="modal fade" id="modalNuevaCompra" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="post" onsubmit="return buildCompraItemsJson()">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-bag-plus me-2"></i>Nueva compra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="accion" value="nueva_compra">
                        <input type="hidden" name="items_json" id="compra_items_json">

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Proveedor</label>
                                <select name="proveedor_id" class="form-select" required>
                                    <option value="">Selecciona proveedor...</option>
                                    <?php foreach ($proveedores as $pv): ?>
                                        <option value="<?php echo (int) $pv['id']; ?>">
                                            <?php echo htmlspecialchars($pv['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Monto entregado</label>
                                <div class="input-group">
                                    <span class="input-group-text">XAF</span>
                                    <input type="number" step="0.01" name="monto_entregado" class="form-control"
                                        value="0">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="table-responsive">
                            <table class="table align-middle" id="tablaCompraItems">
                                <thead>
                                    <tr>
                                        <th style="min-width:220px">Producto</th>
                                        <th>Cant.</th>
                                        <th>Precio compra</th>
                                        <th class="text-end">Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select class="form-select form-select-sm prod-select">
                                                <option value="">Selecciona...</option>
                                                <?php foreach ($productos as $pr): ?>
                                                    <option value="<?php echo (int) $pr['id']; ?>"
                                                        data-precio="<?php echo (float) $pr['precio_unitario']; ?>">
                                                        <?php echo htmlspecialchars($pr['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="number" min="1" value="1"
                                                class="form-control form-control-sm cantidad"></td>
                                        <td><input type="number" step="0.01"
                                                class="form-control form-control-sm precio"></td>
                                        <td class="text-end subtotal">XAF 0,00</td>
                                        <td class="text-end"><button type="button" class="btn btn-sm btn-danger"
                                                onclick="removeRow(this)"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total</th>
                                        <th class="text-end" id="compraTotal">XAF 0,00</th>
                                        <th class="text-end"><button type="button" class="btn btn-sm btn-primary"
                                                onclick="addRow('tablaCompraItems')"><i class="bi bi-plus"></i></button>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Guardar
                            compra</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Pago a proveedor -->
        <div class="modal fade" id="modalPagoProveedor" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Pago a proveedor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                        <input type="hidden" name="accion" value="pago_proveedor">
                        <input type="hidden" name="compra_id" id="pp_compra_id">
                        <div class="mb-3">
                            <label class="form-label">Proveedor (opcional si se paga contra compra)</label>
                            <select name="proveedor_id" class="form-select">
                                <option value="">Selecciona...</option>
                                <?php foreach ($proveedores as $pv): ?>
                                    <option value="<?php echo (int) $pv['id']; ?>">
                                        <?php echo htmlspecialchars($pv['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text">XAF</span>
                                <input type="number" step="0.01" name="monto" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Método de pago</label>
                            <select class="form-select" name="metodo_pago">
                                <option>EFECTIVO</option>
                                <option>TRANSFERENCIA</option>
                                <option>TARJETA</option>
                                <option>OTRO</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Registrar
                            pago</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Toast de feedback -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
            <div id="appToast"
                class="toast align-items-center text-bg-<?php echo $flash['type'] ?? 'secondary'; ?> border-0"
                role="alert" aria-live="assertive" aria-atomic="true" <?php echo $flash ? 'data-bs-autohide="true"' : 'data-bs-autohide="false"'; ?>>
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo htmlspecialchars($flash['msg'] ?? 'Listo'); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>

        <footer class="container py-4 text-center text-secondary">
            <small>© <?php echo date('Y'); ?> Finanzas Clínica — Demo UI. Mejora y separa en MVC para
                producción.</small>
        </footer>
    </div>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Utilidades UI
    const money = n => new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(n || 0));

    // Toast si hay flash
    <?php if ($flash): ?>
        const t = new bootstrap.Toast(document.getElementById('appToast'), { delay: 3000 });
        t.show();
    <?php endif; ?>

    // Pasar datos a modal Cobrar Consulta
    const modalCC = document.getElementById('modalCobrarConsulta');
    modalCC?.addEventListener('show.bs.modal', ev => {
        const btn = ev.relatedTarget;
        document.getElementById('cc_consulta_id').value = btn?.dataset.id || '';
        document.getElementById('cc_monto').value = btn?.dataset.monto || '';
    });

    // Pasar datos a modal Cobrar Analítica
    const modalCA = document.getElementById('modalCobrarAnalitica');
    modalCA?.addEventListener('show.bs.modal', ev => {
        const id = ev.relatedTarget?.dataset.id || '';
        document.getElementById('ca_analitica_id').value = id;
    });

    // Pasar compra_id a Pago Proveedor
    const modalPP = document.getElementById('modalPagoProveedor');
    modalPP?.addEventListener('show.bs.modal', ev => {
        const id = ev.relatedTarget?.dataset.id || '';
        document.getElementById('pp_compra_id').value = id;
    });

    // ------------------- Construcción dinámica de items (Venta/Compra) -------------------
    function addRow(tableId) {
        const table = document.getElementById(tableId || 'tablaVentaItems');
        const tr = table.tBodies[0].rows[0].cloneNode(true);
        tr.querySelectorAll('input').forEach(i => { i.value = i.classList.contains('cantidad') ? 1 : '' });
        tr.querySelector('.subtotal').textContent = 'XAF 0,00';
        table.tBodies[0].appendChild(tr);
    }
    function removeRow(btn) {
        const tr = btn.closest('tr');
        const tbody = tr.parentElement;
        if (tbody.rows.length > 1) tr.remove();
        updateTotals();
    }

    // Auto-set precio cuando eliges producto
    document.addEventListener('change', (e) => {
        if (e.target.matches('.prod-select')) {
            const opt = e.target.selectedOptions[0];
            const precio = opt?.dataset.precio || 0;
            const tr = e.target.closest('tr');
            tr.querySelector('.precio').value = precio;
            updateTotals();
        }
        if (e.target.matches('.cantidad, .precio')) updateTotals();
    });

    function updateTotals() {
        // Ventas
        const tv = document.getElementById('tablaVentaItems');
        if (tv) {
            let total = 0;
            tv.tBodies[0].querySelectorAll('tr').forEach(tr => {
                const cant = Number(tr.querySelector('.cantidad')?.value || 0);
                const precio = Number(tr.querySelector('.precio')?.value || 0);
                const sub = cant * precio;
                tr.querySelector('.subtotal').textContent = 'XAF ' + money(sub);
                total += sub;
            });
            document.getElementById('ventaTotal').textContent = 'XAF ' + money(total);
        }
        // Compras
        const tc = document.getElementById('tablaCompraItems');
        if (tc) {
            let total = 0;
            tc.tBodies[0].querySelectorAll('tr').forEach(tr => {
                const cant = Number(tr.querySelector('.cantidad')?.value || 0);
                const precio = Number(tr.querySelector('.precio')?.value || 0);
                const sub = cant * precio;
                tr.querySelector('.subtotal').textContent = 'XAF ' + money(sub);
                total += sub;
            });
            document.getElementById('compraTotal').textContent = 'XAF ' + money(total);
        }
    }

    function buildVentaItemsJson() {
        const rows = document.querySelectorAll('#tablaVentaItems tbody tr');
        const items = [];
        for (const tr of rows) {
            const prod = tr.querySelector('.prod-select')?.value;
            const cant = Number(tr.querySelector('.cantidad')?.value || 0);
            const precio = Number(tr.querySelector('.precio')?.value || 0);
            if (!prod || cant <= 0 || precio <= 0) {
                alert('Verifica producto, cantidad y precio en todos los renglones');
                return false;
            }
            items.push({ producto_id: Number(prod), cantidad: cant, precio: precio });
        }
        document.getElementById('venta_items_json').value = JSON.stringify(items);
        return true;
    }

    function buildCompraItemsJson() {
        const rows = document.querySelectorAll('#tablaCompraItems tbody tr');
        const items = [];
        for (const tr of rows) {
            const prod = tr.querySelector('.prod-select')?.value;
            const cant = Number(tr.querySelector('.cantidad')?.value || 0);
            const precio = Number(tr.querySelector('.precio')?.value || 0);
            if (!prod || cant <= 0 || precio <= 0) {
                alert('Verifica producto, cantidad y precio en todos los renglones');
                return false;
            }
            items.push({ producto_id: Number(prod), cantidad: cant, precio_compra: precio });
        }
        document.getElementById('compra_items_json').value = JSON.stringify(items);
        return true;
    }

    // Búsquedas rápidas (sólo cliente)
    document.getElementById('searchConsultas')?.addEventListener('input', function () {
        const val = this.value.trim();
        document.querySelectorAll('#tbodyConsultas tr').forEach(tr => {
            const pac = tr.children[1]?.textContent || '';
            tr.style.display = pac.includes(val) ? '' : 'none';
        });
    });
    document.getElementById('searchAnaliticas')?.addEventListener('input', function () {
        const val = this.value.trim();
        document.querySelectorAll('#tbodyAnaliticas tr').forEach(tr => {
            const pac = tr.children[1]?.textContent || '';
            tr.style.display = pac.includes(val) ? '' : 'none';
        });
    });
</script>