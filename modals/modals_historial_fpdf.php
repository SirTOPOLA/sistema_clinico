

<!-- Modal para elegir fechas -->
<div class="modal fade" id="modalFechas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Seleccionar intervalo de fechas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formFechas">
          <input type="hidden" id="idPaciente">
          <div class="mb-3">
            <label for="fechaInicio" class="form-label">Fecha inicio</label>
            <input type="date" class="form-control" id="fechaInicio" required>
          </div>
          <div class="mb-3">
            <label for="fechaFin" class="form-label">Fecha fin</label>
            <input type="date" class="form-control" id="fechaFin" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-info" onclick="generarHistorial()">Generar PDF</button>
      </div>
    </div>
  </div>
</div>

<script>
// Guardamos el id del paciente en el modal
function seleccionarFechas(idPaciente) {
    document.getElementById("idPaciente").value = idPaciente;
    let modal = new bootstrap.Modal(document.getElementById("modalFechas"));
    modal.show();
}

// Generar la URL con las fechas seleccionadas
function generarHistorial() {
    let id = document.getElementById("idPaciente").value;
    let inicio = document.getElementById("fechaInicio").value;
    let fin = document.getElementById("fechaFin").value;

    if (!inicio || !fin) {
        alert("Por favor selecciona ambas fechas.");
        return;
    }

    if (inicio > fin) {
        alert("La fecha de inicio no puede ser mayor que la fecha fin.");
        return;
    }

    // Abrimos el PDF en nueva pestaña con los parámetros
    let url = `fpdf/historial_clinico_pacientes.php?id_paciente=${id}&fecha_inicio=${inicio}&fecha_fin=${fin}`;
    window.open(url, "_blank");

    // Cerrar el modal
    bootstrap.Modal.getInstance(document.getElementById("modalFechas")).hide();
}
</script>
