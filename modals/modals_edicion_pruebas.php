<!-- Modal para editar Tipo de Prueba -->
<div class="modal fade" id="modalEditPrueba" tabindex="-1" aria-labelledby="modalEditPruebaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditPruebaLabel">Editar Tipo de Prueba</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/actualizar_tipo_prueba.php" method="post">
                    <input type="hidden" id="edit_prueba_id" name="id">
                    <div class="mb-3">
                        <label for="edit_nombrePrueba" class="form-label">Nombre de la Prueba</label>
                        <input type="text" class="form-control" id="edit_nombrePrueba" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_precioPrueba" class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control" id="edit_precioPrueba" name="precio" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>
