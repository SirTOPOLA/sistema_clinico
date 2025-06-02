function mostrarToast(tipo , mensaje) {
  const iconos = {
    success: 'bi-check-circle-fill',
    danger: 'bi-x-circle-fill',
    warning: 'bi-exclamation-triangle-fill',
    info: 'bi-info-circle-fill'
  };

  const colores = {
    success: 'bg-success text-white',
    danger: 'bg-danger text-white',
    warning: 'bg-warning text-dark',
    info: 'bg-primary text-white'
  };

  const toast = document.createElement('div');
  toast.className = `toast align-items-center ${colores[tipo]} border-0 show mb-2`;
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'assertive');
  toast.setAttribute('aria-atomic', 'true');

  toast.innerHTML = `
    <div class="d-flex">
      <div class="toast-body d-flex align-items-center">
        <i class="bi ${iconos[tipo]} me-2 fs-5"></i>
        <span>${mensaje}</span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" aria-label="Cerrar"></button>
    </div>
  `;

  // Botón cerrar funcional
  toast.querySelector('button').onclick = () => {
    cerrarToast(toast);
  };

  document.getElementById('toast-container').appendChild(toast);

  // Auto cerrar con animación
  setTimeout(() => {
    cerrarToast(toast);
  }, 8000);
}

function cerrarToast(toast) {
  toast.classList.remove('show');
  toast.classList.add('hide');
  toast.querySelector('button').disabled = true; // evitar clicks múltiples

  setTimeout(() => {
    toast.remove();
  }, 200); // duración animación
}


/* modal de confirm */
function mostrarConfirmacionToast(mensaje, onConfirmar, onCancelar = null) {
  const toast = document.createElement('div');
  toast.className = `toast align-items-center bg-light border shadow show mb-2`;
  toast.setAttribute('role', 'alert');
  toast.setAttribute('aria-live', 'assertive');
  toast.setAttribute('aria-atomic', 'true');

  toast.innerHTML = `
    <div class="toast-body text-center">
  <!-- Icono centrado encima -->
  <div class="mb-2">
    <i class="bi bi-question-circle-fill text-primary fs-1"></i>
  </div>

  <!-- Mensaje destacado -->
  <div class="mb-3">
    <span class="fs-5 fw-semibold">${mensaje}</span>
  </div>

  <!-- Botones alineados y separados -->
 <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
  <button class="btn btn-sm btn-outline-success px-3 w-100 w-sm-auto">
    <i class="bi bi-check-circle-fill me-1"></i> Sí
  </button>
  <button class="btn btn-sm btn-outline-secondary px-3 w-100 w-sm-auto">
    <i class="bi bi-x-circle-fill me-1"></i> No
  </button>
</div>

</div>


  `;

  const [btnSi, btnNo] = toast.querySelectorAll('button');

  btnSi.onclick = () => {
    cerrarToast(toast);
    if (typeof onConfirmar === 'function') onConfirmar();
  };

  btnNo.onclick = () => {
    cerrarToast(toast);
    if (typeof onCancelar === 'function') onCancelar();
  };

  document.getElementById('toast-container').appendChild(toast);
}
