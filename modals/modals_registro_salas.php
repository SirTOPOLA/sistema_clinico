


<!-- Modal para registrar Sala de Ingreso -->
<div class="modal fade" id="modalSala" tabindex="-1" aria-labelledby="modalSalaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSalaLabel">Registrar Sala de Ingreso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/guardar_sala.php" method="post">
                    <div class="mb-3">
                        <label for="nombreSala" class="form-label">Nombre de la Sala</label>
                        <input type="text" class="form-control" id="nombreSala" name="nombre" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
