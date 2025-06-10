<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 

$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? 'sin_permiso'));
 
 
$resumen = [];

function contar($pdo, $sql)
{
    $stmt = $pdo->query($sql);
    return (int) $stmt->fetchColumn();
}

switch ($rol) {
    case 'administrador':
        $resumen = [
            'Usuarios' => contar($pdo, "SELECT COUNT(*) FROM usuarios"),
            'Empleados' => contar($pdo, "SELECT COUNT(*) FROM personal"),
            /* 'Pacientes' => contar($pdo, "SELECT COUNT(*) FROM pacientes"),
            'Citas pendientes' => contar($pdo, "SELECT COUNT(*) FROM citas WHERE estado = 'pendiente'"),
            'Reportes generados' => contar($pdo, "SELECT COUNT(*) FROM reportes") */
        ];
        break;

    case 'secretaria':
        $resumen = [
           /*  'Citas hoy' => contar($pdo, "SELECT COUNT(*) FROM citas WHERE fecha = CURDATE()"),
            'Nuevos pacientes' => contar($pdo, "SELECT COUNT(*) FROM pacientes WHERE DATE(fecha_registro) = CURDATE()"),
            'Llamadas pendientes' => contar($pdo, "SELECT COUNT(*) FROM llamadas WHERE estado = 'pendiente'") */
        ];
        break;

    case 'triaje':
        $resumen = [
          /*   'En espera' => contar($pdo, "SELECT COUNT(*) FROM pacientes WHERE estado = 'en_espera'"),
            'Urgentes' => contar($pdo, "SELECT COUNT(*) FROM pacientes WHERE prioridad = 'alta'") */
        ];
        break;

    case 'laboratorio':
        $resumen = [/* 
            'Muestras pendientes' => contar($pdo, "SELECT COUNT(*) FROM examenes WHERE estado = 'pendiente'"),
            'Resultados listos' => contar($pdo, "SELECT COUNT(*) FROM examenes WHERE estado = 'listo'")
         */];
        break;

    case 'urgencia':
        $resumen = [
            /* 'Pacientes en urgencia' => contar($pdo, "SELECT COUNT(*) FROM urgencias WHERE estado = 'activo'"),
            'Camillas ocupadas' => contar($pdo, "SELECT COUNT(*) FROM camillas WHERE estado = 'ocupada'"),
            'Turnos pendientes' => contar($pdo, "SELECT COUNT(*) FROM turnos WHERE estado = 'pendiente'")
        */ ];
        break;

    default:
        $resumen = [
            'Mensaje' => 'No hay estadísticas disponibles para este rol.'
        ];
}
?>
 
<!-- Main Content -->
<div id="content" class="container-fluid py-4 mt-4">

    <h2 class="mb-4 mt-4">Resumen de Actividad</h2>
    <div class="row">
        <?php foreach ($resumen as $titulo => $valor): ?>
            <div class="col-md-2 mb-3">
                <div class="card shadow-sm border-left-primary h-100 py-2">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            <?= htmlspecialchars($titulo) ?>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= is_numeric($valor) ? $valor : htmlspecialchars($valor) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>