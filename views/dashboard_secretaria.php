<?php


$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

// Si no es administrador, redirige al dashboard correspondiente
if ($rol !== 'secretaria') {
    switch ($rol) {
        case 'laboratorio':
            header("Location: index.php?vista=dashboard_laboratorio");
            exit;
        case 'administrador':
            header("Location: index.php?vista=dashboard_administador");
            exit;
        case 'doctor':
            header("Location: index.php?vista=dashboard_doctor");
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

 
// Cargar estadísticas generales
$stats = [
    'total_pacientes' => 0,
    'consultas_hoy' => 0,
    'ingresos_activos' => 0,
    'salas_disponibles' => 0
];

try {
    // Total de pacientes
    $stats['total_pacientes'] = $pdo->query("SELECT COUNT(*) FROM pacientes")->fetchColumn();

    // Consultas de hoy
    $stats['consultas_hoy'] = $pdo->query("
        SELECT COUNT(*) FROM consultas 
        WHERE DATE(fecha_registro) = CURDATE()
    ")->fetchColumn();

    // Ingresos activos (sin fecha de alta)
    $stats['ingresos_activos'] = $pdo->query("
        SELECT COUNT(*) FROM ingresos 
        WHERE fecha_alta IS NULL
    ")->fetchColumn();

    // Salas disponibles (salas sin pacientes actualmente)
    $stats['salas_disponibles'] = $pdo->query("
        SELECT COUNT(*) FROM salas_ingreso s
        WHERE NOT EXISTS (
            SELECT 1 FROM ingresos i
            WHERE i.id_sala = s.id AND i.fecha_alta IS NULL
        )
    ")->fetchColumn();

    // Consultas de hoy: detalles
    $stmt = $pdo->query("
        SELECT 
            c.id,
            CONCAT(p.nombre, ' ', p.apellidos) AS paciente,
            TIME(c.fecha_registro) AS hora,
            c.motivo_consulta AS motivo
        FROM consultas c
        JOIN pacientes p ON p.id = c.id_paciente
        WHERE DATE(c.fecha_registro) = CURDATE()
        ORDER BY c.fecha_registro ASC
        LIMIT 10
    ");
    $consultas_hoy = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ingresos activos: detalles
    $stmt = $pdo->query("
        SELECT 
            i.id,
            CONCAT(p.nombre, ' ', p.apellidos) AS paciente,
            s.nombre AS sala,
            i.fecha_ingreso,
            DATEDIFF(CURDATE(), DATE(i.fecha_ingreso)) AS dias
        FROM ingresos i
        JOIN pacientes p ON p.id = i.id_paciente
        JOIN salas_ingreso s ON s.id = i.id_sala
        WHERE i.fecha_alta IS NULL
        ORDER BY i.fecha_ingreso DESC
        LIMIT 10
    ");
    $ingresos_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al cargar estadísticas: " . $e->getMessage());
}
 

?>

<div id="content" class="container-fluid px-4 py-3">
    <!-- Título del Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-primary mb-1">Dashboard Secretaría</h2>
                    <p class="text-muted mb-0">Panel de control - Consultorio Clínico</p>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-users text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="fw-bold mb-0"><?php echo number_format($stats['total_pacientes']); ?></h3>
                            <p class="text-muted mb-0">Total Pacientes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-stethoscope text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="fw-bold mb-0"><?php echo $stats['consultas_hoy']; ?></h3>
                            <p class="text-muted mb-0">Consultas Hoy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-bed text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="fw-bold mb-0"><?php echo $stats['ingresos_activos']; ?></h3>
                            <p class="text-muted mb-0">Ingresos Activos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-door-open text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="fw-bold mb-0"><?php echo $stats['salas_disponibles']; ?></h3>
                            <p class="text-muted mb-0">Salas Disponibles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido Principal -->
    <div class="row">
        <!-- Consultas del Día -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Consultas de Hoy</h5>
                        <button class="btn btn-sm btn-outline-primary">Ver Todas</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Hora</th>
                                    <th>Paciente</th>
                                    <th>Motivo</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($consultas_hoy as $consulta): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary"><?php echo $consulta['hora']; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-user text-muted"></i>
                                            </div>
                                            <strong><?php echo $consulta['paciente']; ?></strong>
                                        </div>
                                    </td>
                                    <td><?php echo $consulta['motivo']; ?></td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success">Programada</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ingresos Activos -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Ingresos Activos</h5>
                        <button class="btn btn-sm btn-outline-primary">Gestionar</button>
                    </div>
                </div>
                <div class="card-body">
                    <?php foreach($ingresos_activos as $ingreso): ?>
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-gradient rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-bed text-white"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1"><?php echo $ingreso['paciente']; ?></h6>
                            <small class="text-muted"><?php echo $ingreso['sala']; ?> • <?php echo $ingreso['dias']; ?> días</small>
                        </div>
                        <div class="flex-shrink-0">
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-info-circle"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">Acciones Rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-outline-primary h-100 py-3" data-bs-toggle="modal" data-bs-target="#buscarPacienteModal">
                                    <i class="fas fa-search fa-2x mb-2"></i>
                                    <br>
                                    <span>Buscar Paciente</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-outline-success h-100 py-3" data-bs-toggle="modal" data-bs-target="#agendarConsultaModal">
                                    <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                                    <br>
                                    <span>Agendar Consulta</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-outline-warning h-100 py-3" data-bs-toggle="modal" data-bs-target="#gestionarSalasModal">
                                    <i class="fas fa-door-open fa-2x mb-2"></i>
                                    <br>
                                    <span>Gestionar Salas</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <button class="btn btn-outline-info h-100 py-3" onclick="window.print()">
                                    <i class="fas fa-print fa-2x mb-2"></i>
                                    <br>
                                    <span>Imprimir Reporte</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

 

<!-- Modal Buscar Paciente -->
<div class="modal fade" id="buscarPacienteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar Paciente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control form-control-lg" placeholder="Buscar por nombre, apellido o DIP..." id="searchPaciente">
                </div>
                <div id="resultadosBusqueda">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p>Ingresa un término de búsqueda</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para buscar pacientes
    const searchInput = document.getElementById('searchPaciente');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.trim();
            if (term.length >= 2) {
                // Aquí iría la llamada AJAX para buscar pacientes
                console.log('Buscando:', term);
            }
        });
    }

    // Función para el formulario de nuevo paciente
    const formNuevoPaciente = document.getElementById('formNuevoPaciente');
    if (formNuevoPaciente) {
        formNuevoPaciente.addEventListener('submit', function(e) {
            e.preventDefault();
            // Aquí iría la lógica para guardar el nuevo paciente
            console.log('Guardando nuevo paciente...');
        });
    }
});
</script>

<style>
.avatar-sm {
    width: 35px;
    height: 35px;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.btn-group .btn {
    border-radius: 0.375rem !important;
    margin-right: 2px;
}

.table th {
    font-weight: 600;
    color: #6c757d;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}

@media print {
    .btn, .modal {
        display: none !important;
    }
    
    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }
}
</style>

 