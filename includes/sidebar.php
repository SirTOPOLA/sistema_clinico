<?php
 

// Rol en sesión
$_SESSION['usuario']['rol'] = 'administrador'; // Ejemplo. Esto lo asignas al loguear.
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? 'sin_permiso'));
$current = $_GET['vista'] ?? 'index';

// Íconos asociados a cada módulo
$iconos = [
    'index'         => 'bi-speedometer2',
    'usuarios'          => 'bi-person-circle',
    'empleados'         => 'bi-person-badge-fill',
    'pacientes'         => 'bi-people-fill',
    'triajes'           => 'bi-heart-pulse',
    'consultas'         => 'bi-clipboard2-pulse',
    'recetas'           => 'bi-file-medical',
    'resultados'        => 'bi-file-earmark-medical',
    'ordenes'           => 'bi-flask',
    'pagos'             => 'bi-cash-coin',
    'reportes'          => 'bi-bar-chart-line',
    'perfil'            => 'bi-person-square',
    'configuracion'     => 'bi-gear-fill',
];

// Menús por rol del sistema clínico
$menu = [
    'administrador' => [
        'Dashboard'     => 'index',
        'Usuarios'      => 'usuarios',
        'Empleados'     => 'empleados',
        'Pacientes'     => 'pacientes',
        'Consultas'     => 'consultas',
        'Triajes'       => 'triajes',
        'Órdenes Lab.'  => 'ordenes',
        'Resultados'    => 'resultados',
        'Recetas'       => 'recetas',
        'Pagos'         => 'pagos',
        'Reportes'      => 'reportes',
        'Configuración' => 'configuracion',
    ],
    'doctor' => [
        'Dashboard'     => 'index',
        'Pacientes'     => 'pacientes',
        'Consultas'     => 'consultas',
        'Recetas'       => 'recetas',
        'Órdenes Lab.'  => 'ordenes',
        
    ],
    'enfermera' => [
        'Dashboard'     => 'index',
        'Pacientes'     => 'pacientes',
        'Triajes'       => 'triajes',
        
    ],
    'laboratorista' => [
        'Dashboard'     => 'index',
        'Resultados'    => 'resultados',
        
    ],
];
?>
<!-- Sidebar -->
<div class="wrapper">
  <div id="sidebar" class="sidebar position-fixed scroll-box overflow-auto h-100 p-3">
    <h5 class="mb-4"><i class="bi bi-hospital me-2"></i>Menú</h5>
    <ul class="nav nav-pills flex-column">
      <?php foreach ($menu[$rol] as $label => $vistaName): 
        $active = ($current === $vistaName) ? 'active' : '';
        $icon = $iconos[$vistaName] ?? 'bi-chevron-right';
      ?>
        <li class="nav-item mb-1">
          <a href="<?= $vistaName ?>.php" class="nav-link <?= $active ?>">
            <i class="bi <?= $icon ?> me-2"></i>
            <span class="link-text"><?= $label ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>

