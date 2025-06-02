<!-- Alerta Flotante -->
<?php if (isset($_SESSION['alerta'])): ?>
  <div id="alerta-flotante" class="toast align-items-center text-bg-warning border-0 position-fixed bottom-0 end-0 m-4 show" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 1080; min-width: 300px;">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= htmlspecialchars($_SESSION['alerta']) ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
    </div>
  </div>

  <script>
    setTimeout(() => {
      const alerta = document.getElementById('alerta-flotante');
      if (alerta) alerta.remove();
    }, 5000); // 5 segundos
  </script>

  <?php unset($_SESSION['alerta']); ?>
<?php endif; ?>
