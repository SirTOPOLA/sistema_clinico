<?php
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));
$current = $_GET['vista'] ?? 'dashboard_administrador';
 
// Clase CSS adicional según el rol del usuario
$claseRol = match ($rol) {
    'administrador' => 'sidebar-admin',
    'secretaria'    => 'sidebar-secretaria', 
    'laboratorio'   => 'sidebar-laboratorio',
    'doctor'      => 'sidebar-doctor',
    default         => 'sidebar-generico',
};
 
$iconos_por_rol = [
  'administrador' => [
    'dashboard_administrador' => 'bi-speedometer2',
    'usuarios' => 'bi-person-badge',
    'pacientes' => 'bi-people-fill',
    'pruebas' => 'bi-clipboard-pulse',
    'analiticas' => 'bi-clipboard-pulse',
    'recetas' => 'bi-journal-text',
    'salas' => 'bi-hospital',
    'ingresos' => 'bi-door-open',
    'pagos' => 'bi-cash-coin',  
    'consultas' => 'bi-clipboard-check',
    'detalles_consultas' => 'bi-file-earmark-medical',
    'empleados' => 'bi-person-workspace',
  ],
  'secretaria' => [
    'dashboard_secretaria' => 'bi-clipboard2-heart-fill',
  ],
  'doctor' => [
    'dashboard_doctor' => 'bi-activity',
  ],
  'laboratorio' => [
    'dashboard_laboratorio' => 'bi-eyedropper',
  ], 
];

// Escoge los íconos adecuados según el rol actual
$iconos = $iconos_por_rol[$rol] ?? [];

// Menú por rol
$menu = [
  'administrador' => [
    'Dashboard' => 'dashboard_administrador', 
    'Usuarios' => 'usuarios',
    'Pacientes' => 'pacientes',
    'Recetas' => 'recetas',
    'salas' => 'salas',
    'Pruebas' => 'tipo_prueba',
    'ingresos' => 'ingresos',
    'analiticas' => 'analiticas',
    'pagos de Pruebas' => 'pagos',
    'Consultas' => 'consultas',
    /* 'Detalles_consultas' => 'detalles_consultas', */
    'Empleados' => 'empleados',
    // 'Configuración' => 'configuracion',
  ],
  'laboratorio' => [
    'Dashboard' => 'dashboard_laboratorio', 
    'analiticas' => 'analiticas', 
  ],
  'secretaria' => [
    'Dashboard' => 'dashboard_secretaria', 
    'Pacientes' => 'pacientes',
    'ingresos' => 'ingresos',
    'Consultas' => 'consultas',
  ],
  'doctor' => [
    'Dashboard' => 'dashboard_doctor', 
    'Recetas' => 'recetas',
    'Pruebas' => 'tipo_prueba',
    'ingresos' => 'ingresos',
    'Consultas' => 'consultas',
    'analiticas' => 'analiticas', 
  ],
  /* 'triaje' => [
    'Dashboard' => 'dashboard_enfermera', 
    'Pacientes' => 'pacientes',
    'Consultas' => 'consultas',
    ], */
    /* 'urgencia' => [
      'Dashboard' => 'dashboard_urgencia', 
      'ingresos' => 'ingresos', 
    ], */
    /* 'triaje' => [
    'Dashboard' => 'dashboard_triaje', 
  ], */
];
?>
<div class="wrapper">
<div id="sidebar" class="sidebar <?= $claseRol ?> position-fixed scroll-box overflow-auto h-100 p-3">

  
    <h5 class="mb-4"><i class="bi bi-journal-bookmark-fill me-2"></i>Menú</h5>
    <ul class="nav nav-pills flex-column">
      <?php foreach ($menu[$rol] as $label => $vistaName):
        $active = ($current === $vistaName) ? 'active' : '';
        $icon = $iconos[$vistaName] ?? 'bi-chevron-right';
        ?>
        <li class="nav-item mb-1">
          <a href="index.php?vista=<?= $vistaName ?>" class="nav-link <?= $active ?>">
            <i class="bi <?= $icon ?> me-2"></i>
            <span class="link-text"><?= $label ?></span>
          </a>
        </li>
      <?php endforeach; ?>
      <li class="nav-item mb-1">
        <a href="" id="cerrarSession" class="nav-link">
          <i class="bi bi-box-arrow-right me-1"></i> Salir
        </a>
      </li>
    </ul>
  </div>