<?php
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? 'sin_permiso'));
$current = $_GET['vista'] ?? 'dashboard';
 
// Clase CSS adicional según el rol del usuario
$claseRol = match ($rol) {
    'administrador' => 'sidebar-admin',
    'secretaria'    => 'sidebar-secretaria',
    'triaje'        => 'sidebar-triaje',
    'laboratorio'   => 'sidebar-laboratorio',
    'urgencia'      => 'sidebar-urgencia',
    default         => 'sidebar-generico',
};
 
$iconos_por_rol = [
  'administrador' => [
    'dashboard' => 'bi-speedometer2',
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
    'dashboard' => 'bi-clipboard2-heart-fill',
  ],
  'triaje' => [
    'dashboard' => 'bi-activity',
  ],
  'laboratorio' => [
    'dashboard' => 'bi-eyedropper',
  ],
  'urgencia' => [
    'dashboard' => 'bi-exclamation-triangle-fill',
  ],
];

// Escoge los íconos adecuados según el rol actual
$iconos = $iconos_por_rol[$rol] ?? [];

// Menú por rol
$menu = [
  'administrador' => [
    'Dashboard' => 'dashboard', 
    'Usuarios' => 'usuarios',
    'Pacientes' => 'pacientes',
    'Recetas' => 'recetas',
    'salas' => 'salas',
    'Pruebas' => 'tipo_prueba',
    'ingresos' => 'ingresos',
    'analiticas' => 'analiticas',
    'pagos' => 'pagos',
    'Consultas' => 'consultas',
    'Detalles_consultas' => 'detalles_consultas',
    'Empleados' => 'empleados',
   // 'Configuración' => 'configuracion',
  ],
  'laboratorio' => [
    'Dashboard' => 'dashboard', 
  ],
  'triaje' => [
    'Dashboard' => 'dashboard', 
  ],
  'secretaria' => [
    'Dashboard' => 'dashboard', 
  ],
  'urgencia' => [
    'Dashboard' => 'dashboard', 
  ],
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