<?php


$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

// Si no es administrador, redirige al dashboard correspondiente
if ($rol !== 'laboratorio') {
    switch ($rol) {
        case 'secretaria':
            header("Location: index.php?vista=dashboard_secretaria");
            exit;
        case 'triaje':
            header("Location: index.php?vista=dashboard_triaje");
            exit;
        case 'administrador':
            header("Location: index.php?vista=dashboard_administador");
            exit;
        case 'urgencia':
            header("Location: index.php?vista=dashboard_urgencia");
            exit;
        default:
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => "Acceso denegado. No tienes permisos para ver esta vista."
            ];
            header("Location: index.php");
            exit;
    }
}
 
// Asegúrate de tener tu conexión PDO ya creada antes (por ejemplo: $pdo = new PDO(...))

try {
    // Total de tipos de pruebas
    $stmt = $pdo->query("SELECT COUNT(*) FROM tipo_pruebas");
    $total_pruebas = (int) $stmt->fetchColumn();

    // Precio promedio
    $stmt = $pdo->query("SELECT AVG(precio) FROM tipo_pruebas");
    $precio_promedio = (float) $stmt->fetchColumn();

    // Pruebas registradas hoy
    $stmt = $pdo->query("SELECT COUNT(*) FROM tipo_pruebas WHERE DATE(fecha_registro) = CURDATE()");
    $pruebas_hoy = (int) $stmt->fetchColumn();

    // Pendientes (si no tienes estado, lo dejamos en 0)
    $pendientes = 0;

    // Lista de pruebas (opcional si quieres mostrar en tabla o actividad reciente)
    $stmt = $pdo->query("SELECT * FROM tipo_pruebas ORDER BY fecha_registro DESC");
    $tipos_pruebas = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
     
    // Obtener todas las pruebas
    $stmt = $pdo->query("
        SELECT 
            id, 
            nombre, 
            precio, 
            fecha_registro, 
            id_usuario 
        FROM tipo_pruebas
        ORDER BY fecha_registro DESC
    ");
    $tipos_pruebas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totales
    $total_pruebas = count($tipos_pruebas);
    $precio_promedio = $total_pruebas > 0
        ? array_sum(array_column($tipos_pruebas, 'precio')) / $total_pruebas
        : 0;

} catch (PDOException $e) {
    die("Error al obtener las pruebas: " . $e->getMessage());
}


?>

<div id="content"> 
    <!-- Page Header -->
    <div class="page-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-microscope me-3"></i>
                        Dashboard de Laboratorio Clínico
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Panel de control y análisis de pruebas médicas</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex justify-content-end align-items-center">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <span id="currentDate"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>



<!-- HTML -->
<div class="container-fluid px-4 mt-4">
    <div class="row mb-4">
        <!-- Total Tipos de Pruebas -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="metric-label">Total Tipos de Pruebas</div>
                            <div class="metric-value"><?= $total_pruebas ?></div>
                            <div class="trend-indicator trend-up">
                                <i class="fas fa-arrow-up me-1"></i>12% vs mes anterior
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon primary">
                                <i class="fas fa-vials"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Precio Promedio -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="metric-label">Precio Promedio</div>
                            <div class="metric-value">CFA <?= number_format($precio_promedio, 2) ?></div>
                            <div class="trend-indicator trend-up">
                                <i class="fas fa-arrow-up me-1"></i>5.2% vs mes anterior
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon success">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pruebas Procesadas Hoy -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="metric-label">Pruebas Hoy</div>
                            <div class="metric-value"><?= $pruebas_hoy ?></div>
                            <div class="trend-indicator trend-up">
                                <i class="fas fa-arrow-up me-1"></i>Más que ayer
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon info">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pendientes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-stats warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="metric-label">Pruebas Pendientes</div>
                            <div class="metric-value"><?= $pendientes ?></div>
                            <div class="trend-indicator trend-down">
                                <i class="fas fa-arrow-down me-1"></i>Sin cambios
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="stat-icon warning">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OPCIONAL: Tabla de listado de pruebas -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i> Últimas Pruebas Registradas
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Precio (CFA)</th>
                        <th>Fecha Registro</th>
                        <th>ID Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tipos_pruebas as $prueba): ?>
                        <tr>
                            <td><?= $prueba['id'] ?></td>
                            <td><?= htmlspecialchars($prueba['nombre']) ?></td>
                            <td><?= number_format($prueba['precio'], 2) ?></td>
                            <td><?= date("d/m/Y H:i", strtotime($prueba['fecha_registro'])) ?></td>
                            <td><?= $prueba['id_usuario'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
    // Configuración de fecha actual
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Configuración de Chart.js
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";
    Chart.defaults.color = '#6c757d';

    // Gráfico de Pruebas por Tipo
    const ctx1 = document.getElementById('pruebasTipoChart').getContext('2d');
    new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Hematología', 'Bioquímica', 'Microbiología', 'Inmunología', 'Otros'],
            datasets: [{
                data: [35, 25, 20, 15, 5],
                backgroundColor: [
                    '#3498db',
                    '#27ae60',
                    '#f39c12',
                    '#e74c3c',
                    '#9b59b6'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Gráfico de Tendencia Semanal
    const ctx2 = document.getElementById('tendenciaSemanalChart').getContext('2d');
    new Chart(ctx2, {
        type: 'line',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            datasets: [{
                label: 'Pruebas Realizadas',
                data: [12, 19, 15, 25, 22, 8, 5],
                borderColor: '#27ae60',
                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#27ae60',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Gráfico de Ingresos Mensuales
    const ctx3 = document.getElementById('ingresosMensualesChart').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            datasets: [{
                label: 'Ingresos (CFA)',
                data: [850000, 920000, 780000, 1100000, 960000, 1200000],
                backgroundColor: 'rgba(52, 152, 219, 0.8)',
                borderColor: '#3498db',
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return 'CFA ' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return 'CFA ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Animación de contadores
    function animateCounter(elementId, finalValue, duration = 2000) {
        const element = document.getElementById(elementId);
        const startValue = 0;
        const increment = finalValue / (duration / 16);
        let currentValue = startValue;

        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                element.textContent = finalValue.toLocaleString();
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(currentValue).toLocaleString();
            }
        }, 16);
    }

    // Animar contadores al cargar la página
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(() => {
            animateCounter('totalPruebas', 47);
            animateCounter('precioPromedio', 15750);
            animateCounter('pruebasHoy', 23);
            animateCounter('pendientes', 7);
        }, 500);
    });

    // Efectos hover para las tarjetas
    document.querySelectorAll('.card').forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.style.transform = 'translateY(-5px)';
        });

        card.addEventListener('mouseleave', function () {
            this.style.transform = 'translateY(0)';
        });
    });
</script>

 <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.3);
        }

        .card-stats {
            background: linear-gradient(45deg, #fff 0%, #f8f9fa 100%);
            position: relative;
        }

        .card-stats::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--primary-color);
        }

        .card-stats.primary::before { background: var(--primary-color); }
        .card-stats.success::before { background: var(--success-color); }
        .card-stats.info::before { background: var(--info-color); }
        .card-stats.warning::before { background: var(--warning-color); }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.primary { background: linear-gradient(45deg, var(--primary-color), var(--secondary-color)); }
        .stat-icon.success { background: linear-gradient(45deg, var(--success-color), #2ecc71); }
        .stat-icon.info { background: linear-gradient(45deg, var(--info-color), #3498db); }
        .stat-icon.warning { background: linear-gradient(45deg, var(--warning-color), #e67e22); }

        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 10px 0;
        }

        .metric-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .trend-indicator {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 20px;
            font-weight: 600;
        }

        .trend-up {
            background-color: rgba(39, 174, 96, 0.1);
            color: var(--success-color);
        }

        .trend-down {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
        }

        .activity-item {
            border-left: 3px solid var(--info-color);
            padding-left: 15px;
            margin-bottom: 15px;
            background: white;
            border-radius: 0 10px 10px 0;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
        }

        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: var(--card-shadow);
        }

        .btn-action {
            border-radius: 25px;
            padding: 10px 20px;
            font-weight: 600;
            margin: 5px;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .metric-value { font-size: 2rem; }
            .chart-container { height: 250px; }
        }
    </style>
 