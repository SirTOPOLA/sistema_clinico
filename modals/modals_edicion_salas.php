<!-- Modal para editar Sala de Ingreso -->
<div class="modal fade" id="modalEditSala" tabindex="-1" aria-labelledby="modalEditSalaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditSalaLabel">Editar Sala de Ingreso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/actualizar_salas.php" method="post">
                    <input type="hidden" id="edit_sala_id" name="id">
                    <div class="mb-3">
                        <label for="edit_nombreSala" class="form-label">Nombre de la Sala</label>
                        <input type="text" class="form-control" id="edit_nombreSala" name="nombre" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>