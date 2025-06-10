<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? 'sin_permiso'));

$resumen = [];


// Traducción manual para asegurar compatibilidad
$dias = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];

$meses = [
    'January' => 'Enero',
    'February' => 'Febrero',
    'March' => 'Marzo',
    'April' => 'Abril',
    'May' => 'Mayo',
    'June' => 'Junio',
    'July' => 'Julio',
    'August' => 'Agosto',
    'September' => 'Septiembre',
    'October' => 'Octubre',
    'November' => 'Noviembre',
    'December' => 'Diciembre'
];

// Obtener fecha actual
$dia_nombre = $dias[date('l')];       // Martes
$dia_numero = date('j');              // 10
$mes_nombre = $meses[date('F')];      // Junio




function contar($pdo, $sql)
{
    $stmt = $pdo->query($sql);
    return (int) $stmt->fetchColumn();
}

switch ($rol) {
    case 'administrador':
        // Solo este rol necesita los datos completos
        $ingresos_totales = contar($pdo, "SELECT SUM(cantidad) FROM pagos");
        $ingresos_mes = contar($pdo, "SELECT SUM(cantidad) FROM pagos WHERE MONTH(fecha_registro) = MONTH(CURDATE()) AND YEAR(fecha_registro) = YEAR(CURDATE())");
        $ingresos_dia = contar($pdo, "SELECT SUM(cantidad) FROM pagos WHERE DATE(fecha_registro) = CURDATE()");

        $stmt_ingresos_mensuales = $pdo->query("
            SELECT DATE_FORMAT(fecha_registro, '%Y-%m') AS mes, SUM(cantidad) AS total 
            FROM pagos 
            GROUP BY mes 
            ORDER BY mes DESC 
            LIMIT 6
        ");
        $datos_grafico = array_reverse($stmt_ingresos_mensuales->fetchAll(PDO::FETCH_ASSOC));

        $stmt_consultas = $pdo->query("
            SELECT DATE_FORMAT(fecha_registro, '%Y-%m') AS mes, COUNT(*) AS total 
            FROM consultas 
            GROUP BY mes 
            ORDER BY mes DESC 
            LIMIT 6
        ");
        $datos_consultas = array_reverse($stmt_consultas->fetchAll(PDO::FETCH_ASSOC));

        $stmt_pacientes = $pdo->query("
            SELECT DATE_FORMAT(fecha_registro, '%Y-%m') AS mes, COUNT(*) AS total 
            FROM pacientes 
            GROUP BY mes 
            ORDER BY mes DESC 
            LIMIT 6
        ");
        $datos_pacientes = array_reverse($stmt_pacientes->fetchAll(PDO::FETCH_ASSOC));

        $stmt_analisis = $pdo->query("
            SELECT tp.nombre AS tipo_analisis, COUNT(*) AS total 
            FROM analiticas a
            JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id
            GROUP BY tp.nombre
            ORDER BY total DESC
            LIMIT 5
        ");
        $datos_analisis = $stmt_analisis->fetchAll(PDO::FETCH_ASSOC);

        $stmt_horarios = $pdo->query("
            SELECT HOUR(fecha_registro) AS hora, COUNT(*) AS total 
            FROM consultas 
            GROUP BY hora 
            ORDER BY hora ASC
        ");
        $datos_horarios = $stmt_horarios->fetchAll(PDO::FETCH_ASSOC);

        $stmt_retenidos = $pdo->query("
            SELECT COUNT(*) AS retenidos 
            FROM (
                SELECT id_paciente 
                FROM consultas 
                GROUP BY id_paciente 
                HAVING COUNT(*) >= 2
            ) AS pacientes_fieles
        ");
        $retenidos = $stmt_retenidos->fetchColumn();

        $stmt_total = $pdo->query("SELECT COUNT(*) FROM pacientes");
        $total_pacientes = $stmt_total->fetchColumn();

        $tasa_retencion = $total_pacientes > 0 ? round(($retenidos / $total_pacientes) * 100, 2) : 0;

        $stmt_total_consultas = $pdo->query("SELECT COUNT(*) FROM consultas");
        $total_consultas = $stmt_total_consultas->fetchColumn();

        $stmt_con_analisis = $pdo->query("
            SELECT COUNT(DISTINCT id_consulta) 
            FROM analiticas 
            WHERE id_consulta IS NOT NULL
        ");
        $consultas_con_analisis = $stmt_con_analisis->fetchColumn();

        $porcentaje_analisis = $total_consultas > 0 ? round(($consultas_con_analisis / $total_consultas) * 100, 2) : 0;

        $resumen = [
            'Usuarios' => contar($pdo, "SELECT COUNT(*) FROM usuarios"),
            'Empleados' => contar($pdo, "SELECT COUNT(*) FROM personal"),
            'Pacientes' => contar($pdo, "SELECT COUNT(*) FROM pacientes"),
            'Consultas' => contar($pdo, "SELECT COUNT(*) FROM consultas"),
            'Analíticas' => contar($pdo, "SELECT COUNT(*) FROM analiticas"),
            'Roles' => contar($pdo, "SELECT COUNT(*) FROM roles"),
            'Tasa Retención' => $tasa_retencion . '%',
            'Porcentaje con Analíticas' => $porcentaje_analisis . '%'
        ];
        break;

    case 'secretaria':
        $resumen = [
            'Pacientes hoy' => contar($pdo, "SELECT COUNT(*) FROM pacientes WHERE DATE(fecha_registro) = CURDATE()"),
            'Consultas hoy' => contar($pdo, "SELECT COUNT(*) FROM consultas WHERE DATE(fecha_registro) = CURDATE()"),
            'Ingresos activos' => contar($pdo, "SELECT COUNT(*) FROM ingresos WHERE fecha_alta IS NULL"),
        ];
        break;

    case 'triaje':
        $resumen = [
            'Consultas registradas' => contar($pdo, "SELECT COUNT(*) FROM consultas"),
            'Signos vitales tomados' => contar($pdo, "SELECT COUNT(*) FROM detalle_consulta"),
        ];
        break;

    case 'laboratorio':
        $resumen = [
            'Analíticas pendientes' => contar($pdo, "SELECT COUNT(*) FROM analiticas WHERE estado = 'pendiente'"),
            'Analíticas completadas' => contar($pdo, "SELECT COUNT(*) FROM analiticas WHERE estado = 'completado'"),
        ];
        break;

    case 'urgencia':
        $resumen = [
            'Pacientes ingresados' => contar($pdo, "SELECT COUNT(*) FROM ingresos WHERE fecha_alta IS NULL"),
            'Salas activas' => contar($pdo, "SELECT COUNT(*) FROM salas_ingreso"),
        ];
        break;

    default:
        $resumen = [
            'Mensaje' => 'No hay estadísticas disponibles para este rol.'
        ];
}
?>

<!-- Main Content -->
<div id="content" class="container-fluid py-5 px-4">
    <h2 class="mb-5 fw-bold text-dark">
        <i class="bi bi-speedometer2 text-primary me-2"></i>Resumen de Actividad
    </h2>

    <!-- Cards resumen -->
    <div class="row g-4">
        <?php foreach ($resumen as $titulo => $valor): ?>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                style="width: 40px; height: 40px;">
                                <i class="bi bi-bar-chart-line-fill fs-5"></i>
                            </div>
                            <h6 class="text-uppercase text-muted mb-0"><?= htmlspecialchars($titulo) ?></h6>
                        </div>
                        <h3 class="fw-bold text-dark">
                            <?= is_numeric($valor) ? number_format($valor) : htmlspecialchars($valor) ?>
                        </h3>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Tasa de retención -->
    <div class="card shadow-sm mt-5 mb-5 border-0 rounded-4">
        <div class="card-body text-center">
            <h5 class="text-muted mb-2">❤️ Tasa de Retención de Pacientes</h5>
            <h1 class="display-4 fw-bold text-success"><?= $tasa_retencion ?>%</h1>
            <p class="text-muted">Pacientes que han vuelto al menos 2 veces</p>
        </div>
    </div>

    <!-- Ingresos -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body text-success d-flex align-items-center">
                    <i class="bi bi-cash-stack fs-1 me-3"></i>
                    <div>
                        <h6 class="text-muted mb-1">Total Ingresos</h6>
                        <h5 class="fw-bold">XAF <?= number_format($ingresos_totales, 2) ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body text-info d-flex align-items-center">
                    <i class="bi bi-calendar-event fs-1 me-3"></i>
                    <div>
                        <h6 class="text-muted mb-1">Ingresos de <?= $mes_nombre ?></h6>
                        <h5 class="fw-bold">XAF <?= number_format($ingresos_mes, 2) ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-body text-warning d-flex align-items-center">
                    <i class="bi bi-calendar-day fs-1 me-3"></i>
                    <div>
                        <h6 class="text-muted mb-1">Ingresos del <?= "$dia_nombre $dia_numero de $mes_nombre" ?></h6>
                        <h5 class="fw-bold">XAF <?= number_format($ingresos_dia, 2) ?></h5>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- Gráficos -->
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Flujo de Ingresos
                    </h6>
                    <canvas id="graficoIngresos"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-calendar-check me-2 text-success"></i>Consultas por Mes
                    </h6>
                    <canvas id="graficoConsultas"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-person-plus-fill me-2 text-info"></i>Pacientes Nuevos
                        por Mes</h6>
                    <canvas id="graficoPacientes"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-droplet-half me-2 text-danger"></i>Tipos de Análisis Más
                        Frecuentes</h6>
                    <canvas id="graficoAnalisis"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body">
                    <h6 class="text-muted mb-3"><i class="bi bi-clock-history me-2 text-warning"></i>Distribución por
                        Horario</h6>
                    <canvas id="graficoHorario"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>





<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>
    /*  ----------- horarios frecuentes --------- */
    const ctx5 = document.getElementById('graficoHorario').getContext('2d');
    new Chart(ctx5, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map(fn($h) => $h['hora'] . ':00', $datos_horarios)) ?>,
            datasets: [{
                label: 'Consultas',
                data: <?= json_encode(array_map('intval', array_column($datos_horarios, 'total'))) ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.3)',
                borderColor: 'rgba(153, 102, 255, 1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    /* ---------- Analisis de laboratotio ---------- */
    const ctx4 = document.getElementById('graficoAnalisis').getContext('2d');
    new Chart(ctx4, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($datos_analisis, 'tipo_analisis')) ?>,
            datasets: [{
                label: 'Análisis',
                data: <?= json_encode(array_map('intval', array_column($datos_analisis, 'total'))) ?>,
                backgroundColor: [
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(201, 203, 207, 0.7)',
                    'rgba(255, 205, 86, 0.7)'
                ],
                borderColor: '#fff',
                borderWidth: 1
            }]
        }
    });

    /* ------------- grafico de pacientes ------------  */
    const ctx3 = document.getElementById('graficoPacientes').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($datos_pacientes, 'mes')) ?>,
            datasets: [{
                label: 'Pacientes Nuevos',
                data: <?= json_encode(array_map('intval', array_column($datos_pacientes, 'total'))) ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });


    /* -------- consulta por mes ----------- */
    const ctx2 = document.getElementById('graficoConsultas').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($datos_consultas, 'mes')) ?>,
            datasets: [{
                label: 'Consultas',
                data: <?= json_encode(array_map('intval', array_column($datos_consultas, 'total'))) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    /*  -------------     */
    const ctx = document.getElementById('graficoIngresos').getContext('2d');
    const graficoIngresos = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($datos_grafico, 'mes')) ?>,
            datasets: [{
                label: 'Ingresos XAF ',
                data: <?= json_encode(array_map('floatval', array_column($datos_grafico, 'total'))) ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.2)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return 'XAF ' + value;
                        }
                    }
                }
            }
        }
    });
</script>