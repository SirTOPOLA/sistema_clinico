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

?>
<div id="content" class="container-fluid py-5 px-4">
    <h2 class="mb-5 fw-bold text-dark">
        <i class="bi bi-speedometer2 text-primary me-2"></i>Resumen de Actividad
    </h2>
</div>