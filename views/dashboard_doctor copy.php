<?php


$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

// Si no es administrador, redirige al dashboard correspondiente
if ($rol !== 'doctor') {
    switch ($rol) {
        case 'secretaria':
            header("Location: index.php?vista=dashboard_secretaria");
            exit;
        case 'administrador':
            header("Location: index.php?vista=dashboard_administador");
            exit;
        case 'laboratorio':
            header("Location: index.php?vista=dashboard_laboratorio");
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



$doctor_id = $_SESSION['usuario']['id'];

// CONSULTAS
function contar($pdo, $query, $params = [])
{
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

$consultas = contar($pdo, "SELECT COUNT(*) FROM consultas WHERE id_usuario = :id", ['id' => $doctor_id]);
$pacientes = contar($pdo, "SELECT COUNT(DISTINCT id_paciente) FROM consultas WHERE id_usuario = :id", ['id' => $doctor_id]);
$analiticas = contar($pdo, "SELECT COUNT(*) FROM analiticas WHERE id_usuario = :id", ['id' => $doctor_id]);
$recetas = contar($pdo, "SELECT COUNT(*) FROM recetas WHERE id_usuario = :id", ['id' => $doctor_id]);

// CONSULTAS POR MES
$stmt = $pdo->prepare("SELECT DATE_FORMAT(fecha_registro, '%Y-%m') AS mes, COUNT(*) AS total FROM consultas WHERE id_usuario = :id GROUP BY mes ORDER BY mes DESC LIMIT 6");
$stmt->execute(['id' => $doctor_id]);
$consultas_mes = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

// ANALÍTICAS POR TIPO
$stmt = $pdo->prepare("SELECT tp.nombre AS tipo, COUNT(*) AS total FROM analiticas a JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id WHERE a.id_usuario = :id GROUP BY tp.nombre ORDER BY total DESC LIMIT 5");
$stmt->execute(['id' => $doctor_id]);
$analisis_tipo = $stmt->fetchAll(PDO::FETCH_ASSOC);

// INGRESOS ACTIVOS
$ingresados = contar($pdo, "SELECT COUNT(*) FROM ingresos WHERE id_usuario = :id AND fecha_alta IS NULL", ['id' => $doctor_id]);

// ÚLTIMAS RECETAS
$stmt = $pdo->prepare("SELECT r.descripcion, p.nombre, p.apellidos, DATE(r.fecha_registro) AS fecha FROM recetas r JOIN pacientes p ON r.id_paciente = p.id WHERE r.id_usuario = :id ORDER BY r.fecha_registro DESC LIMIT 5");
$stmt->execute(['id' => $doctor_id]);
$ultimas_recetas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<div id="content" class="container-fluid py-5 px-2">
    <h2 class="mb-5 fw-bold text-dark">
        <i class="bi bi-speedometer2 text-primary me-2"></i>Resumen de Actividad
    </h2> 
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-primary border-5">
                <div class="card-body">
                    <h6 class="card-title text-muted">Consultas</h6>
                    <h3><?= $consultas ?></h3>
                    <i class="bi bi-heart-pulse fs-2 text-primary"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-success border-5">
                <div class="card-body">
                    <h6 class="card-title text-muted">Pacientes</h6>
                    <h3><?= $pacientes ?></h3>
                    <i class="bi bi-person-vcard fs-2 text-success"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-info border-5">
                <div class="card-body">
                    <h6 class="card-title text-muted">Analíticas</h6>
                    <h3><?= $analiticas ?></h3>
                    <i class="bi bi-clipboard2-pulse fs-2 text-info"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-start border-danger border-5">
                <div class="card-body">
                    <h6 class="card-title text-muted">Recetas</h6>
                    <h3><?= $recetas ?></h3>
                    <i class="bi bi-capsule-pill fs-2 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="card col-12 col-md-8">
            <h3 class="text-center my-4">Evolución de signos vitales</h3>
            <div class="form-group mb-3">
                <label for="pacienteSelect">Seleccionar paciente:</label>
                <select id="pacienteSelect" class="form-control">
                    <!-- Se llenará dinámicamente -->
                </select>
            </div>
            <canvas id="signosChart" height="100"></canvas>

        </div>
        <!-- Tarjeta elegante para Analíticas por Estado -->
        <div class="col-12 col-md-4"
            style="max-width: 450px; margin: 10px auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <div style="
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            padding: 25px 30px;
            text-align: center;
          ">
                <h3 style="margin-bottom: 15px; color: #2c3e50; font-weight: 700; font-size: 1.5rem;">
                    🧪 Analíticas por Estado
                </h3>
                <canvas id="analiticasEstadoChart" style="max-width: 100%; height: 280px;"></canvas>
                <p style="margin-top: 18px; color: #7f8c8d; font-size: 0.95rem;">
                    Visualiza la carga actual de trabajo y los resultados pendientes o finalizados.
                </p>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12 col-md-6" style="max-width: 600px; margin: 30px auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <div style="
                        background: white;
                        border-radius: 12px;
                        box-shadow: 0 6px 18px rgba(0,0,0,0.1);
                        padding: 25px 30px;
                    ">
                <h3
                    style="margin-bottom: 15px; color: #2c3e50; font-weight: 700; font-size: 1.5rem; text-align: center;">
                    🧬 Frecuencia de Diagnósticos
                </h3>
                <canvas id="frecuenciaDiagnosticosChart" style="max-width: 100%; height: 400px;"></canvas>
                <p style="margin-top: 18px; color: #7f8c8d; font-size: 0.95rem; text-align: center;">
                    Muestra las enfermedades más frecuentes diagnosticadas.
                </p>
            </div>
        </div>
        <div class="col-12 col-md-6" style="max-width: 700px; margin: 30px auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            <div style="
    background: white;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    padding: 25px 30px;
  ">
                <h3
                    style="margin-bottom: 15px; color: #34495e; font-weight: 700; font-size: 1.5rem; text-align: center;">
                    🕒 Consultas por hora del día
                </h3>
                <canvas id="consultasPorHoraChart" style="max-width: 100%; height: 400px;"></canvas>
                <p style="margin-top: 18px; color: #7f8c8d; font-size: 0.95rem; text-align: center;">
                    Identifica los picos de trabajo y ayuda a planificar turnos.
                </p>
            </div>
        </div>
    </div>

<div class="row">
    
<div class="col-12 col-md-6" style="max-width: 750px; margin: 30px auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
  <div style="
    background: white;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    padding: 25px 30px;
  ">
    <h3 style="margin-bottom: 15px; color: #34495e; font-weight: 700; font-size: 1.5rem; text-align: center;">
      📅 Ingresos hospitalarios por mes
    </h3>
    <canvas id="ingresosPorMesChart" style="max-width: 100%; height: 400px;"></canvas>
    <p style="margin-top: 18px; color: #7f8c8d; font-size: 0.95rem; text-align: center;">
      Permite observar la presión hospitalaria y estacionalidad.
    </p>
  </div>
</div>
<div class="col-12 col-md-6" style="max-width: 450px; margin: 30px auto; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
  <div style="
    background: white;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
    padding: 20px 25px;
    text-align: center;
  ">
    <h3 style="margin-bottom: 15px; color: #34495e; font-weight: 700; font-size: 1.4rem;">
      👤 Pacientes nuevos vs recurrentes
    </h3>
    <canvas id="pacientesDonaChart" style="max-width: 100%; height: 350px;"></canvas>
    <p style="margin-top: 18px; color: #7f8c8d; font-size: 0.95rem;">
      Mide fidelización, seguimiento y retención.
    </p>
  </div>
</div>
</div>

    <div class="row mt-5 g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">Consultas por Mes</div>
                <div class="card-body">
                    <canvas id="graficoConsultasMes"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">Tipos de Analíticas</div>
                <div class="card-body">
                    <canvas id="graficoAnalisis"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">Últimas Recetas Emitidas</div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($ultimas_recetas as $receta): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($receta['nombre']) . ' ' . htmlspecialchars($receta['apellidos']) ?></strong>
                                    <small
                                        class="d-block text-muted"><?= substr($receta['descripcion'], 0, 60) ?>...</small>
                                </div>
                                <span class="badge bg-secondary"><?= $receta['fecha'] ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

 
</div>




<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


 
<script>
async function cargarPacientesNuevosRecurrentes() {
  try {
    const res = await fetch('api/pacientes_nuevos_recurrentes.php');
    const data = await res.json();

    const etiquetas = data.map(item => item.tipo_paciente);
    const valores = data.map(item => parseInt(item.total));

    const ctx = document.getElementById('pacientesDonaChart').getContext('2d');

    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: etiquetas,
        datasets: [{
          data: valores,
          backgroundColor: ['#3498db', '#2ecc71'],
          hoverOffset: 30,
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom', labels: { font: { size: 14 } } },
          tooltip: {
            callbacks: {
              label: ctx => `${ctx.label}: ${ctx.parsed} pacientes`
            }
          }
        }
      }
    });

  } catch (error) {
    console.error('Error cargando pacientes nuevos vs recurrentes:', error);
  }
}

cargarPacientesNuevosRecurrentes();
 
async function cargarIngresosPorMes() {
  try {
    const res = await fetch('api/ingresos_por_mes.php');
    const data = await res.json();

    const etiquetas = data.map(item => item.periodo);
    const valores = data.map(item => parseInt(item.total));

    const ctx = document.getElementById('ingresosPorMesChart').getContext('2d');

    new Chart(ctx, {
      type: 'line', // Puedes cambiar a 'bar' si prefieres barras
      data: {
        labels: etiquetas,
        datasets: [{
          label: 'Ingresos',
          data: valores,
          fill: false,
          borderColor: 'rgba(52, 152, 219, 0.8)',
          backgroundColor: 'rgba(41, 128, 185, 0.7)',
          tension: 0.3,
          pointRadius: 5,
          pointHoverRadius: 7,
          borderWidth: 3,
          hoverBorderWidth: 4
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: { precision: 0 }
          },
          x: {
            ticks: { font: { size: 13 } }
          }
        },
        plugins: {
          legend: { display: true },
          tooltip: {
            callbacks: {
              label: ctx => `${ctx.parsed.y} ingresos`
            }
          }
        }
      }
    });

  } catch (error) {
    console.error('Error cargando ingresos por mes:', error);
  }
}

cargarIngresosPorMes();
 
    async function cargarConsultasPorHora() {
        try {
            const res = await fetch('api/consultas_por_hora.php');
            const data = await res.json();

            // Crear array con 24 posiciones (0 a 23) inicializadas en 0 para asegurar que todas las horas aparezcan
            const totalPorHora = Array(24).fill(0);

            data.forEach(item => {
                totalPorHora[item.hora] = parseInt(item.total);
            });

            const etiquetasHoras = Array.from({ length: 24 }, (_, i) => `${i}:00`);

            const ctx = document.getElementById('consultasPorHoraChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: etiquetasHoras,
                    datasets: [{
                        label: 'Consultas',
                        data: totalPorHora,
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                        borderColor: 'rgba(39, 174, 96, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                        barPercentage: 0.7
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 13
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.parsed.y} consultas`
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('Error cargando consultas por hora:', error);
        }
    }

    cargarConsultasPorHora();

    async function cargarDiagnosticos() {
        try {
            const res = await fetch('api/frecuencia_diagnosticos.php');
            const data = await res.json();

            const etiquetas = data.map(item => item.descripcion);
            const valores = data.map(item => parseInt(item.total));

            const ctx = document.getElementById('frecuenciaDiagnosticosChart').getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        label: 'Número de diagnósticos',
                        data: valores,
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(41, 128, 185, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    indexAxis: 'y', // barras horizontales
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    size: 13
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.parsed.x} diagnósticos`
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('Error cargando frecuencia diagnósticos:', error);
        }
    }

    cargarDiagnosticos();

    // Función para cargar datos y dibujar gráfico
    async function cargarGrafico() {
        try {
            const res = await fetch('api/analiticas_por_estado.php'); // Cambia ruta si es necesario
            const data = await res.json();

            const etiquetas = data.map(item => item.estado.charAt(0).toUpperCase() + item.estado.slice(1));
            const valores = data.map(item => item.total);

            const colores = ['#3498db', '#e74c3c', '#f39c12']; // Azul, rojo y amarillo para más estados si hay

            const ctx = document.getElementById('analiticasEstadoChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        data: valores,
                        backgroundColor: colores.slice(0, valores.length),
                        hoverOffset: 30,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { size: 14 }, color: '#34495e' }
                        },
                        title: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    let label = context.label || '';
                                    let value = context.parsed || 0;
                                    return label + ': ' + value;
                                }
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('Error cargando datos de analíticas:', error);
        }
    }

    // Cargar gráfico al iniciar
    cargarGrafico();

    let signosChart;

    function cargarPacientes() {
        fetch('api/listar_pacientes.php') // crea esta API si no la tienes
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('pacienteSelect');
                select.innerHTML = '';
                data.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p.id;
                    option.textContent = `${p.nombre} ${p.apellidos}`;
                    select.appendChild(option);
                });
                if (data.length) {
                    cargarSignos(data[0].id); // primer paciente
                }
            });
    }

    function cargarSignos(pacienteId) {
        fetch(`api/signos_vitales_paciente.php?id=${pacienteId}`)
            .then(res => res.json())
            .then(datos => {
                const labels = datos.map(d => d.fecha_registro);
                const temperatura = datos.map(d => d.temperatura);
                const pulso = datos.map(d => d.pulso);
                const saturacion = datos.map(d => d.saturacion_oxigeno);
                const imc = datos.map(d => d.imc);

                if (signosChart) signosChart.destroy();

                signosChart = new Chart(document.getElementById('signosChart'), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Temperatura (°C)',
                                data: temperatura,
                                borderColor: 'red',
                                fill: false
                            },
                            {
                                label: 'Pulso (bpm)',
                                data: pulso,
                                borderColor: 'blue',
                                fill: false
                            },
                            {
                                label: 'Saturación O₂ (%)',
                                data: saturacion,
                                borderColor: 'green',
                                fill: false
                            },
                            {
                                label: 'IMC',
                                data: imc,
                                borderColor: 'orange',
                                fill: false
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Histórico de signos vitales del paciente'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false
                            }
                        }
                    }
                });
            });
    }

    document.getElementById('pacienteSelect').addEventListener('change', e => {
        cargarSignos(e.target.value);
    });

    cargarPacientes();

    const consultasMes = <?= json_encode(array_column($consultas_mes, 'total')) ?>;
    const etiquetasMes = <?= json_encode(array_column($consultas_mes, 'mes')) ?>;
    new Chart(document.getElementById('graficoConsultasMes'), {
        type: 'bar',
        data: {
            labels: etiquetasMes,
            datasets: [{
                label: 'Consultas',
                data: consultasMes,
                backgroundColor: 'rgba(13,110,253,0.6)'
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    const analisisDatos = <?= json_encode(array_column($analisis_tipo, 'total')) ?>;
    const analisisLabels = <?= json_encode(array_column($analisis_tipo, 'tipo')) ?>;
    new Chart(document.getElementById('graficoAnalisis'), {
        type: 'doughnut',
        data: {
            labels: analisisLabels,
            datasets: [{
                data: analisisDatos,
                backgroundColor: ['#0dcaf0', '#198754', '#ffc107', '#dc3545', '#6f42c1']
            }]
        }
    });
</script>