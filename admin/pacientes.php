





<!-- modal registro paciente -->
<div class="modal fade" id="modalCrearPaciente" tabindex="-1" aria-labelledby="modalCrearPacienteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form id="formNuevoPaciente" method="POST" class="modal-content shadow-lg border-0 rounded-4">
      <div class="modal-header bg-success text-white rounded-top-4">
        <h5 class="modal-title d-flex align-items-center gap-2" id="modalCrearPacienteLabel">
          <i class="bi bi-person-plus-fill"></i> Nuevo Paciente
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body px-4 pt-4 pb-0">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-person-badge-fill me-1 text-success"></i> Nombre
            </label>
            <input type="text" name="nombre" class="form-control rounded-pill" required placeholder="Nombre del paciente">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-person-badge-fill me-1 text-success"></i> Apellido
            </label>
            <input type="text" name="apellido" class="form-control rounded-pill" required placeholder="Apellido del paciente">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">
              <i class="bi bi-123 me-1 text-success"></i> Edad
            </label>
            <input type="number" name="edad" class="form-control rounded-pill" min="0" max="120" required placeholder="Edad">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">
              <i class="bi bi-telephone-fill me-1 text-success"></i> Teléfono
            </label>
            <input type="text" name="telefono" class="form-control rounded-pill" required placeholder="Número principal">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">
              <i class="bi bi-house-door-fill me-1 text-success"></i> Residencia
            </label>
            <input type="text" name="residencia" class="form-control rounded-pill" required placeholder="Ciudad o dirección">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-person-workspace me-1 text-success"></i> Profesión
            </label>
            <input type="text" name="profesion" class="form-control rounded-pill" placeholder="Ej. Ingeniero, Docente...">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-briefcase-fill me-1 text-success"></i> Ocupación
            </label>
            <input type="text" name="ocupacion" class="form-control rounded-pill" placeholder="Ej. Freelance, Empleado...">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-phone-vibrate-fill me-1 text-success"></i> Teléfono Emergencia
            </label>
            <input type="text" name="telefono_emergencia" class="form-control rounded-pill" placeholder="Teléfono de contacto de emergencia">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-phone me-1 text-success"></i> Teléfono Auxiliar
            </label>
            <input type="text" name="telefono_auxi" class="form-control rounded-pill" placeholder="Otro número opcional">
          </div>

          <div class="col-md-12">
            <label class="form-label fw-semibold">
              <i class="bi bi-person-fill-gear me-1 text-success"></i> Registrado por Usuario
            </label>
            <select name="usuario_id" class="form-select rounded-pill" required>
              <option value="">Seleccione un usuario</option>
              <?php foreach ($usuarios as $u): ?>
                <option value="<?= $u['id'] ?>"><?= $u['usuario'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-light rounded-bottom-4 px-4 py-3 mt-3">
        <button type="submit" class="btn btn-success rounded-pill px-4">
          <i class="bi bi-check-circle-fill me-1"></i> Guardar
        </button>
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cancelar
        </button>
      </div>
    </form>
  </div>
</div>