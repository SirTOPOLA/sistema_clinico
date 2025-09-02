<?php
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));
$current = $_GET['vista'] ?? 'dashboard_administrador';

// Clase CSS adicional según el rol del usuario
$claseRol = match ($rol) {
  'administrador' => 'sidebar-admin',
  'secretaria' => 'sidebar-secretaria',
  'laboratorio' => 'sidebar-laboratorio',
  'doctor' => 'sidebar-doctor',
  'farmacia' => 'sidebar-doctor',
  default => 'sidebar-generico',
};

$iconos_por_rol = [
  'administrador' => [
    'dashboard_administrador' => 'bi-speedometer2',
    'usuarios' => 'bi-person-badge',
    'pacientes' => 'bi-people-fill',
    'tipo_prueba' => 'bi-activity',
    'analiticas' => 'bi-clipboard-pulse',
    'recetas' => 'bi-journal-text',
    'salas' => 'bi-hospital',
    'ingresos' => 'bi-door-open',
    'pagos' => 'bi-cash-coin',
    'consultas' => 'bi-clipboard-check',
    'reportes' => 'bi-bar-chart-line',
    'detalles_consultas' => 'bi-file-earmark-medical',
    'empleados' => 'bi-person-workspace',
  ],
  'secretaria' => [
    'dashboard_secretaria' => 'bi-clipboard2-heart-fill',
    'pacientes' => 'bi-people-fill',
    'ingresos' => 'bi-door-open',
    'consultas' => 'bi-clipboard-check',
  ],
  'doctor' => [
    'dashboard_doctor' => 'bi-activity',
    'recetas' => 'bi-journal-text',
    'tipo_prueba' => 'bi-beaker',
    'ingresos' => 'bi-door-open',
    'consultas' => 'bi-clipboard-check',
    'analiticas' => 'bi-clipboard-pulse',
  ],
  'laboratorio' => [
    'dashboard_laboratorio' => 'bi-eyedropper',
    'analiticas' => 'bi-clipboard-pulse',
  ],
  'farmacia' => [
    'dashboard_farmacia' => 'bi-eyedropper',
    'productos_farmacia' => 'bi-capsule',
    'proveedores_farmacia' => 'bi-truck',
    'compras_farmacia' => 'bi-cart-check',
    'unidadesMedida_farmacia' => 'bi-bounding-box',
    'categorias_farmacia' => 'bi-tags',
    'pasivos_farmacia' => 'bi-journal-minus',
    'ventas_farmacia' => 'bi-basket',
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
    'Salas' => 'salas',
    'Pruebas' => 'tipo_prueba',
    'Ingresos' => 'ingresos',
    'Analíticas' => 'analiticas',
    'Pagos de Pruebas' => 'pagos',
    'Consultas' => 'consultas',
    'Reportes' => 'reportes',
     'Asegurado' => 'seguros',
    'Empleados' => 'empleados',
  ],
  'secretaria' => [
    'Dashboard' => 'dashboard_secretaria',
    'Pacientes' => 'pacientes',
    'Ingresos' => 'ingresos',
    'Consultas' => 'consultas',
  ],
  'doctor' => [
    'Dashboard' => 'dashboard_doctor',
    'Recetas' => 'recetas',
    'Ingresos' => 'ingresos',
    'Consultas' => 'consultas',
    'Analíticas' => 'analiticas',
  ],
  'laboratorio' => [
    'Dashboard' => 'dashboard_laboratorio',
    'Analíticas' => 'analiticas',
  ],
  'farmacia' => [
    'Dashboard' => 'dashboard_farmacia',
    'Productos' => 'productos_farmacia',
    'Proveedores' => 'proveedores_farmacia',
    'Compras' => 'compras_farmacia',
    'Unidades' => 'unidadesMedida_farmacia',
    'Categorías' => 'categorias_farmacia',
    'Acreedores' => 'pasivos_farmacia',
    'Ventas' => 'ventas_farmacia',
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