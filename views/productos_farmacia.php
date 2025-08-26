<?php 
 
try {
    // Consulta de productos con JOIN y campos adicionales
    $sql_productos = "SELECT p.*, 
                             c.nombre AS categoria_nombre, 
                             u.nombre AS unidad_nombre, 
                             u.abreviatura AS unidad_abreviatura
                      FROM productos p
                      LEFT JOIN categorias c ON p.categoria_id = c.id
                      LEFT JOIN unidades_medida u ON p.unidad_id = u.id
                      ORDER BY p.nombre";

    $stmt_productos = $pdo->query($sql_productos);
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

    // Consulta de categorías
    $sql_categorias = "SELECT id, nombre FROM categorias ORDER BY nombre";
    $stmt_categorias = $pdo->prepare($sql_categorias);
    $stmt_categorias->execute();
    $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

    // Consulta de unidades de medida
    $sql_unidadesMedida = "SELECT id, nombre, abreviatura FROM unidades_medida ORDER BY nombre";
    $stmt_unidadesMedida = $pdo->prepare($sql_unidadesMedida);
    $stmt_unidadesMedida->execute();
    $unidadesMedida = $stmt_unidadesMedida->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $productos = [];
    $categorias = [];
    $unidadesMedida = [];
    $mensaje_error = "Error al conectar a la base de datos: " . $e->getMessage();
}

// Mensajes de alerta simulados, normalmente se obtendrían de la sesión.
$mensaje_error = $_SESSION['error'] ?? null;
$mensaje_exito = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>
<div class="container-fluid" id="content">

    <!-- Encabezado y buscador -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-box-seam me-2"></i>Gestión de Productos</h3>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearProducto">
                <i class="bi bi-plus-circle me-1"></i>Crear Producto
            </button>
        </div>
        <div class="col-md-4 offset-md-2">
            <input type="text" id="buscador" class="form-control" placeholder="Buscar producto...">
        </div>
    </div>

    <!-- Contenedor para las alertas flotantes -->
    <div id="alert-container"></div>

    <!-- Mensajes de alerta -->
    <?php if ($mensaje_error): ?>
        <div id="mensaje" class="alert alert-danger"><?= htmlspecialchars($mensaje_error) ?></div>
    <?php endif; ?>
    <?php if ($mensaje_exito): ?>
        <div id="mensaje" class="alert alert-success"><?= htmlspecialchars($mensaje_exito) ?></div>
    <?php endif; ?>

    <!-- Tabla de Productos -->
    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table id="tablaProductos" class="table table-hover table-bordered table-sm align-middle">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Concentración</th>
                        <th>Forma Farmacéutica</th>
                        <th>Presentación</th>
                        <th>Precio Unitario</th>
                        <th>Stock Actual</th>
                        <th>Stock Mínimo</th>
                        <th>Categoría</th>
                        <th>Unidad Medida</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto['id']) ?></td>
                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td>
                                <?php if ($producto['concentracion']): ?>
                                    <?= htmlspecialchars($producto['concentracion']) ?>
                                <?php else: ?>
                                    <i class="bi bi-lock-fill text-muted" title="Sin concentración"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($producto['forma_farmaceutica']): ?>
                                    <?= htmlspecialchars($producto['forma_farmaceutica']) ?>
                                <?php else: ?>
                                    <i class="bi bi-lock-fill text-muted" title="Sin forma farmacéutica"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($producto['presentacion']): ?>
                                    <?= htmlspecialchars($producto['presentacion']) ?>
                                <?php else: ?>
                                    <i class="bi bi-lock-fill text-muted" title="Sin presentación"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($producto['precio_unitario'] !== NULL): ?>
                                    $<?= number_format($producto['precio_unitario'], 2) ?>
                                <?php else: ?>
                                    <i class="bi bi-lock-fill text-muted" title="Sin precio unitario"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $stock_actual = (int)$producto['stock_actual'];
                                $stock_minimo = (int)$producto['stock_minimo'];
                                $porcentaje = ($stock_minimo > 0) ? min(100, ($stock_actual / $stock_minimo) * 100) : 100;
                                $color = '';
                                if ($porcentaje <= 25) {
                                    $color = 'bg-danger';
                                } elseif ($porcentaje <= 50) {
                                    $color = 'bg-warning';
                                } else {
                                    $color = 'bg-success';
                                }
                                ?>
                                <?= htmlspecialchars($stock_actual) ?>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar <?= $color ?>" role="progressbar" style="width: <?= $porcentaje ?>%;" aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($stock_minimo) ?></td>
                            <td><?= htmlspecialchars($producto['categoria_nombre']) ?></td>
                            <td><?= htmlspecialchars($producto['unidad_nombre']) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-primary btn-editar-producto"
                                    data-id="<?= htmlspecialchars($producto['id']) ?>"
                                    data-nombre="<?= htmlspecialchars($producto['nombre']) ?>"
                                    data-concentracion="<?= htmlspecialchars($producto['concentracion']) ?>"
                                    data-forma-farmaceutica="<?= htmlspecialchars($producto['forma_farmaceutica']) ?>"
                                    data-presentacion="<?= htmlspecialchars($producto['presentacion']) ?>"
                                    data-precio-unitario="<?= htmlspecialchars($producto['precio_unitario']) ?>"
                                    data-stock-minimo="<?= htmlspecialchars($producto['stock_minimo']) ?>"
                                    data-categoria-id="<?= htmlspecialchars($producto['categoria_id']) ?>"
                                    data-unidad-id="<?= htmlspecialchars($producto['unidad_id']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarProducto">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="api/eliminar_producto.php?id=<?= htmlspecialchars($producto['id']) ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Está seguro de eliminar este producto? Esta acción no se puede deshacer.')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Crear Producto -->
<div class="modal fade" id="modalCrearProducto" tabindex="-1" aria-labelledby="modalCrearProductoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="api/guardar_producto.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCrearProductoLabel"><i class="bi bi-plus-circle me-2"></i>Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-12">
                    <label for="nombre_crear" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre_crear" class="form-control" required>
                </div>

                <!-- Concentración -->
                <div class="col-md-12">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="checkConcentracionCrear" checked>
                        <label class="form-check-label" for="checkConcentracionCrear">Usar Concentración</label>
                    </div>
                    <div id="wrapperConcentracionCrear">
                        <label for="concentracion_crear" class="form-label">Concentración</label>
                        <input type="text" name="concentracion" id="concentracion_crear" class="form-control" required>
                    </div>
                </div>

                <!-- Forma Farmacéutica -->
                <div class="col-md-12">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="checkFormaCrear" checked>
                        <label class="form-check-label" for="checkFormaCrear">Usar Forma Farmacéutica</label>
                    </div>
                    <div id="wrapperFormaCrear">
                        <label for="forma_farmaceutica_crear" class="form-label">Forma Farmacéutica</label>
                        <input type="text" name="forma_farmaceutica" id="forma_farmaceutica_crear" class="form-control" required>
                    </div>
                </div>

                <!-- Presentación -->
                <div class="col-md-12">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="checkPresentacionCrear" checked>
                        <label class="form-check-label" for="checkPresentacionCrear">Usar Presentación</label>
                    </div>
                    <div id="wrapperPresentacionCrear">
                        <label for="presentacion_crear" class="form-label">Presentación</label>
                        <input type="text" name="presentacion" id="presentacion_crear" class="form-control" required>
                    </div>
                </div>

                <!-- Precio Unitario (campo opcional) -->
                <div class="col-md-12">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="checkPrecioCrear" checked>
                        <label class="form-check-label" for="checkPrecioCrear">Usar Precio Unitario</label>
                    </div>
                    <div id="wrapperPrecioCrear">
                        <label for="precio_unitario_crear" class="form-label">Precio Unitario</label>
                        <input type="number" step="0.01" name="precio_unitario" id="precio_unitario_crear" class="form-control" required>
                    </div>
                </div>

                <!-- Stock Mínimo -->
                <div class="col-md-12">
                    <label for="stock_minimo_crear" class="form-label">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" id="stock_minimo_crear" class="form-control" value="0" required>
                </div>

                <div class="col-md-12">
                    <label for="categoria_crear" class="form-label">Categoría</label>
                    <select name="categoria_id" id="categoria_crear" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <label for="unidad_crear" class="form-label">Unidad de Medida</label>
                    <select name="unidad_id" id="unidad_crear" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($unidadesMedida as $uni): ?>
                            <option value="<?= htmlspecialchars($uni['id']) ?>"><?= htmlspecialchars($uni['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="api/actualizar_producto.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarProductoLabel"><i class="bi bi-pencil-square me-2"></i>Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="edit-id">
                <div class="col-md-12">
                    <label for="edit-nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                </div>

                <!-- Concentración -->
                <div class="col-md-12">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="checkConcentracionEditar" checked>
                        <label class="form-check-label" for="checkConcentracionEditar">Usar Concentración</label>
                    </div>
                    <div id="wrapperConcentracionEditar">
                        <label for="edit-concentracion" class="form-label">Concentración</label>
                        <input type="text" name="concentracion" id="edit-concentracion" class="form-control" required>
                    </div>
                </div>
                
                <!-- Forma Farmacéutica -->
                <div class="col-md-12">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="checkFormaEditar" checked>
                        <label class="form-check-label" for="checkFormaEditar">Usar Forma Farmacéutica</label>
                    </div>
                    <div id="wrapperFormaEditar">
                        <label for="edit-forma-farmaceutica" class="form-label">Forma Farmacéutica</label>
                        <input type="text" name="forma_farmaceutica" id="edit-forma-farmaceutica" class="form-control" required>
                    </div>
                </div>

                <!-- Presentación -->
                <div class="col-md-12">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="checkPresentacionEditar" checked>
                        <label class="form-check-label" for="checkPresentacionEditar">Usar Presentación</label>
                    </div>
                    <div id="wrapperPresentacionEditar">
                        <label for="edit-presentacion" class="form-label">Presentación</label>
                        <input type="text" name="presentacion" id="edit-presentacion" class="form-control" required>
                    </div>
                </div>

                <!-- Precio Unitario (campo opcional) -->
                <div class="col-md-12">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="checkPrecioEditar" checked>
                        <label class="form-check-label" for="checkPrecioEditar">Usar Precio Unitario</label>
                    </div>
                    <div id="wrapperPrecioEditar">
                        <label for="edit-precio-unitario" class="form-label">Precio Unitario</label>
                        <input type="number" step="0.01" name="precio_unitario" id="edit-precio-unitario" class="form-control" required>
                    </div>
                </div>
                
                <!-- Stock Mínimo -->
                <div class="col-md-12">
                    <label for="edit-stock-minimo" class="form-label">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" id="edit-stock-minimo" class="form-control" required>
                </div>
                
                <div class="col-md-12">
                    <label for="edit-categoria" class="form-label">Categoría</label>
                    <select name="categoria_id" id="edit-categoria" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12">
                    <label for="edit-unidad" class="form-label">Unidad de Medida</label>
                    <select name="unidad_id" id="edit-unidad" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($unidadesMedida as $uni): ?>
                            <option value="<?= htmlspecialchars($uni['id']) ?>"><?= htmlspecialchars($uni['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts de JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Función para manejar la visibilidad de los campos según el checkbox
        function toggleInputVisibility(checkboxId, wrapperId, inputId) {
            const checkbox = document.getElementById(checkboxId);
            const wrapper = document.getElementById(wrapperId);
            const input = document.getElementById(inputId);

            if (checkbox && wrapper && input) {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        wrapper.style.display = 'block';
                        input.setAttribute('required', 'required');
                    } else {
                        wrapper.style.display = 'none';
                        input.removeAttribute('required');
                        input.value = ''; // Limpiar el valor para enviar NULL
                    }
                });
            }
        }
        
        // Inicializar la lógica para los campos opcionales en el modal de Crear
        toggleInputVisibility('checkConcentracionCrear', 'wrapperConcentracionCrear', 'concentracion_crear');
        toggleInputVisibility('checkFormaCrear', 'wrapperFormaCrear', 'forma_farmaceutica_crear');
        toggleInputVisibility('checkPresentacionCrear', 'wrapperPresentacionCrear', 'presentacion_crear');
        toggleInputVisibility('checkPrecioCrear', 'wrapperPrecioCrear', 'precio_unitario_crear');

        // Inicializar la lógica para los campos opcionales en el modal de Editar
        toggleInputVisibility('checkConcentracionEditar', 'wrapperConcentracionEditar', 'edit-concentracion');
        toggleInputVisibility('checkFormaEditar', 'wrapperFormaEditar', 'edit-forma-farmaceutica');
        toggleInputVisibility('checkPresentacionEditar', 'wrapperPresentacionEditar', 'edit-presentacion');
        toggleInputVisibility('checkPrecioEditar', 'wrapperPrecioEditar', 'edit-precio-unitario');

        // Lógica para llenar el modal de edición
        const botonesEditar = document.querySelectorAll('.btn-editar-producto');
        botonesEditar.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const concentracion = this.getAttribute('data-concentracion');
                const formaFarmaceutica = this.getAttribute('data-forma-farmaceutica');
                const presentacion = this.getAttribute('data-presentacion');
                const precioUnitario = this.getAttribute('data-precio-unitario');
                const stockMinimo = this.getAttribute('data-stock-minimo');
                const categoriaId = this.getAttribute('data-categoria-id');
                const unidadId = this.getAttribute('data-unidad-id');
                
                // Función para inicializar los campos opcionales en la edición
                function initOptionalField(checkboxId, wrapperId, inputId, value) {
                    const checkbox = document.getElementById(checkboxId);
                    const wrapper = document.getElementById(wrapperId);
                    const input = document.getElementById(inputId);
                    if (value === 'NULL' || value === '') {
                        checkbox.checked = false;
                        wrapper.style.display = 'none';
                        input.removeAttribute('required');
                    } else {
                        checkbox.checked = true;
                        wrapper.style.display = 'block';
                        input.setAttribute('required', 'required');
                        input.value = value;
                    }
                }

                initOptionalField('checkConcentracionEditar', 'wrapperConcentracionEditar', 'edit-concentracion', concentracion);
                initOptionalField('checkFormaEditar', 'wrapperFormaEditar', 'edit-forma-farmaceutica', formaFarmaceutica);
                initOptionalField('checkPresentacionEditar', 'wrapperPresentacionEditar', 'edit-presentacion', presentacion);
                initOptionalField('checkPrecioEditar', 'wrapperPrecioEditar', 'edit-precio-unitario', precioUnitario);


                document.getElementById('edit-id').value = id;
                document.getElementById('edit-nombre').value = nombre;
                document.getElementById('edit-stock-minimo').value = stockMinimo;
                document.getElementById('edit-categoria').value = categoriaId;
                document.getElementById('edit-unidad').value = unidadId;
            });
        });

        // Lógica para el buscador de la tabla
        const buscador = document.getElementById('buscador');
        const tabla = document.getElementById('tablaProductos');
        const filas = tabla.getElementsByTagName('tr');

        buscador.addEventListener('keyup', function () {
            const filtro = buscador.value.toLowerCase();
            for (let i = 1; i < filas.length; i++) { // Empezamos en 1 para saltar el thead
                const fila = filas[i];
                const textoFila = fila.textContent.toLowerCase();
                if (textoFila.indexOf(filtro) > -1) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            }
        });

        // Lógica para ocultar los mensajes de alerta automáticamente
        setTimeout(() => {
            const mensaje = document.getElementById('mensaje');
            if (mensaje) {
                mensaje.style.transition = 'opacity 1s ease';
                mensaje.style.opacity = '0';
                setTimeout(() => mensaje.remove(), 1000);
            }
        }, 10000); // 10 segundos

        // Lógica para mostrar las alertas flotantes
        function showCriticalStockAlerts() {
            const productos = <?= json_encode($productos); ?>;
            const alertContainer = document.getElementById('alert-container');
            let hasCriticalStock = false;
            
            productos.forEach(producto => {
                if (producto.stock_actual <= producto.stock_minimo) {
                    hasCriticalStock = true;
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show text-center mb-2';
                    alertDiv.style.position = 'fixed';
                    alertDiv.style.top = '20px';
                    alertDiv.style.right = '20px';
                    alertDiv.style.width = '300px';
                    alertDiv.style.zIndex = '1050';
                    alertDiv.innerHTML = `
                        <div class="d-flex align-items-center justify-content-center">
                            <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
                            <div>
                                <strong>¡Stock Crítico!</strong><br>
                                El producto **${producto.nombre}** necesita ser resurtido.
                            </div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    alertContainer.appendChild(alertDiv);
                }
            });

            // Si hay stock crítico, programar para que se muestren de nuevo en un tiempo.
            if (hasCriticalStock) {
                setTimeout(showCriticalStockAlerts, 600000); // Re-muestra las alertas cada 10 minutos
            }
        }

        // Mostrar las alertas al cargar la página
        showCriticalStockAlerts();
    });
</script>

<style>
    /* Estilos para las alertas flotantes */
    #alert-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1050;
    }
</style>