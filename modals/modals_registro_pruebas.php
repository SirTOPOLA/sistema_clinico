<!-- Modal para registrar Tipo de Prueba -->
<div class="modal fade" id="modalPrueba" tabindex="-1" aria-labelledby="modalPruebaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPruebaLabel">Registrar Tipo de Prueba</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/guardar_tipo_prueba.php" method="post">
                    <div class="mb-3">
                        <label for="nombrePrueba" class="form-label">Nombre de la Prueba</label>
                        <input type="text" class="form-control" id="nombrePrueba" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="precioPrueba" class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control" id="precioPrueba" name="precio" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>