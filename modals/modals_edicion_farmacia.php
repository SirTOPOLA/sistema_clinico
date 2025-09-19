<!-- Modal para editar Producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
  
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditProductoLabel">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/actualizar_producto.php" method="post">
                    <input type="hidden" id="edit_producto_id" name="id">
                    <div class="mb-3">
                        <label for="edit_nombreProducto" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="edit_nombreProducto" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_concentracionProducto" class="form-label">Concentración</label>
                        <input type="text" class="form-control" id="edit_concentracionProducto" name="concentracion">
                    </div>
                    <div class="mb-3">
                        <label for="edit_formaFarmaceutica" class="form-label">Forma Farmacéutica</label>
                        <input type="text" class="form-control" id="edit_formaFarmaceutica" name="forma_farmaceutica">
                    </div>
                    <div class="mb-3">
                        <label for="edit_presentacionProducto" class="form-label">Presentación</label>
                        <input type="text" class="form-control" id="edit_presentacionProducto" name="presentacion">
                    </div>
                    <div class="mb-3">
                        <label for="edit_categoriaProducto" class="form-label">Categoría</label>
                        <select class="form-select" id="edit_categoriaProducto" name="categoria_id" required>
                            <option value="">Seleccione una categoría</option>
                            <!-- Opciones se cargarían dinámicamente -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_unidadProducto" class="form-label">Unidad de Medida</label>
                        <select class="form-select" id="edit_unidadProducto" name="unidad_id" required>
                            <option value="">Seleccione una unidad</option>
                            <!-- Opciones se cargarían dinámicamente -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_precioProducto" class="form-label">Precio Unitario</label>
                        <input type="number" step="0.01" class="form-control" id="edit_precioProducto"
                            name="precio_unitario" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_stockActual" class="form-label">Stock Actual</label>
                        <input type="number" class="form-control" id="edit_stockActual" name="stock_actual" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_stockMinimo" class="form-label">Stock Mínimo</label>
                        <input type="number" class="form-control" id="edit_stockMinimo" name="stock_minimo" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar Producto</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Categoría -->
<div class="modal fade" id="modalEditCategoria" tabindex="-1" aria-labelledby="modalEditCategoriaLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditCategoriaLabel">Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/actualizar_categoria.php" method="post">
                    <input type="hidden" id="edit_categoria_id" name="id">
                    <div class="mb-3">
                        <label for="edit_nombreCategoria" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="edit_nombreCategoria" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_descripcionCategoria" class="form-label">Descripción</label>
                        <textarea class="form-control" id="edit_descripcionCategoria" name="descripcion"
                            rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar Categoría</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Unidad de Medida -->
<div class="modal fade" id="modalEditUnidad" tabindex="-1" aria-labelledby="modalEditUnidadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditUnidadLabel">Editar Unidad de Medida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/actualizar_unidad.php" method="post">
                    <input type="hidden" id="edit_unidad_id" name="id">
                    <div class="mb-3">
                        <label for="edit_nombreUnidad" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="edit_nombreUnidad" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_abreviaturaUnidad" class="form-label">Abreviatura</label>
                        <input type="text" class="form-control" id="edit_abreviaturaUnidad" name="abreviatura" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar Unidad</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Proveedor -->
<div class="modal fade" id="modalEditProveedor" tabindex="-1" aria-labelledby="modalEditProveedorLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditProveedorLabel">Editar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/actualizar_proveedor.php" method="post">
                    <input type="hidden" id="edit_proveedor_id" name="id">
                    <div class="mb-3">
                        <label for="edit_nombreProveedor" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="edit_nombreProveedor" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_direccionProveedor" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="edit_direccionProveedor" name="direccion">
                    </div>
                    <div class="mb-3">
                        <label for="edit_telefonoProveedor" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="edit_telefonoProveedor" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="edit_contactoProveedor" class="form-label">Contacto</label>
                        <input type="text" class="form-control" id="edit_contactoProveedor" name="contacto">
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar Proveedor</button>
                </form>
            </div>
        </div>
    </div>
</div>