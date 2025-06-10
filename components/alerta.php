<!-- Alerta Flotante -->
<?php if (isset($_SESSION['alerta'])):
  $tipo = $_SESSION['alerta']['tipo'] ?? 'warning';
  $mensaje = $_SESSION['alerta']['mensaje'] ?? '';
  $clase = 'text-bg-' . $tipo;
  $iconos = [
    'success' => 'bi-check-circle-fill',
    'warning' => 'bi-exclamation-triangle-fill',
    'danger'  => 'bi-x-circle-fill'
  ];
  $icono = $iconos[$tipo] ?? 'bi-info-circle-fill';
?>
  <div id="alerta-flotante" class="toast align-items-center <?= $clase ?> border-0 position-fixed top-0 end-0 m-4 show" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1080; min-width: 300px;">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi <?= $icono ?> me-2"></i>
        <?= htmlspecialchars($mensaje) ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
    </div>
  </div>

  <script>
    setTimeout(() => {
      const alerta = document.getElementById('alerta-flotante');
      if (alerta) alerta.remove();
    }, 5000);
  </script>

  <?php unset($_SESSION['alerta']); ?>
<?php endif; ?>
