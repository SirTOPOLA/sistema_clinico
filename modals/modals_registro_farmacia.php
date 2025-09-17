<!-- Modal para registrar Producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalProductoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductoLabel">Registrar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/guardar_producto.php" method="post">
                    <div class="mb-3">
                        <label for="nombreProducto" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombreProducto" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="concentracionProducto" class="form-label">Concentración</label>
                        <input type="text" class="form-control" id="concentracionProducto" name="concentracion">
                    </div>
                    <div class="mb-3">
                        <label for="formaFarmaceutica" class="form-label">Forma Farmacéutica</label>
                        <input type="text" class="form-control" id="formaFarmaceutica" name="forma_farmaceutica">
                    </div>
                    <div class="mb-3">
                        <label for="presentacionProducto" class="form-label">Presentación</label>
                        <input type="text" class="form-control" id="presentacionProducto" name="presentacion">
                    </div>
                    <div class="mb-3">
                        <label for="categoriaProducto" class="form-label">Categoría</label>
                        <select class="form-select" id="categoriaProducto" name="categoria_id" required>
                            <option value="">Seleccione una categoría</option>
                           <?php 
                            foreach ($categoriasData as $r): ?>
                                <option value="<?php echo htmlspecialchars($r['id']); ?>">
                                    <?php echo htmlspecialchars($r['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="unidadProducto" class="form-label">Unidad de Medida</label>
                        <select class="form-select" id="unidadProducto" name="unidad_id" required>
                            <option value="">Seleccione una unidad</option>
                            <?php 
                            foreach ($unidadesData as $r): ?>
                                <option value="<?php echo htmlspecialchars($r['id']); ?>">
                                    <?php echo htmlspecialchars($r['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="precioProducto" class="form-label">Precio Unitario</label>
                        <input type="number" step="0.01" class="form-control" id="precioProducto" name="precio_unitario"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="stockActual" class="form-label">Stock Actual</label>
                        <input type="number" class="form-control" id="stockActual" name="stock_actual" required>
                    </div>
                    <div class="mb-3">
                        <label for="stockMinimo" class="form-label">Stock Mínimo</label>
                        <input type="number" class="form-control" id="stockMinimo" name="stock_minimo" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para registrar Categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCategoriaLabel">Registrar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/guardar_categoria.php" method="post">
                    <div class="mb-3">
                        <label for="nombreCategoria" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombreCategoria" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcionCategoria" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcionCategoria" name="descripcion" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Categoría</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para registrar Unidad de Medida -->
<div class="modal fade" id="modalUnidad" tabindex="-1" aria-labelledby="modalUnidadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUnidadLabel">Registrar Unidad de Medida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/guardar_unidad.php" method="post">
                    <div class="mb-3">
                        <label for="nombreUnidad" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombreUnidad" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="abreviaturaUnidad" class="form-label">Abreviatura</label>
                        <input type="text" class="form-control" id="abreviaturaUnidad" name="abreviatura" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Unidad</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para registrar Proveedor -->
<div class="modal fade" id="modalProveedor" tabindex="-1" aria-labelledby="modalProveedorLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProveedorLabel">Registrar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="api/proveedor.php" method="post">
                    <div class="mb-3">
                        <label for="nombreProveedor" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombreProveedor" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="direccionProveedor" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccionProveedor" name="direccion">
                    </div>
                    <div class="mb-3">
                        <label for="telefonoProveedor" class="form-label">Teléfono</label>
                        <input type="text" class="form-control" id="telefonoProveedor" name="telefono">
                    </div>
                    <div class="mb-3">
                        <label for="contactoProveedor" class="form-label">Contacto</label>
                        <input type="text" class="form-control" id="contactoProveedor" name="contacto">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
                </form>
            </div>
        </div>
    </div>
</div>