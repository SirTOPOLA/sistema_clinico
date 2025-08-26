<?php 
$mensaje_error = null;
$mensaje_exito = null;

try {
    

    // === Consultar compras con nombre de proveedor y personal ===
    $sql_compras = "SELECT c.*, 
                           p.nombre AS proveedor_nombre, 
                           CONCAT(pe.nombre, ' ', pe.apellidos) AS personal_nombre
                    FROM compras c
                    LEFT JOIN proveedores p ON c.proveedor_id = p.id
                    LEFT JOIN personal pe ON c.personal_id = pe.id
                    ORDER BY c.fecha DESC";
    $stmt_compras = $pdo->query($sql_compras);
    $compras = $stmt_compras->fetchAll(PDO::FETCH_ASSOC);

    // === Consultar detalles de compras agrupados por compra_id ===
    $sql_detalles = "SELECT * FROM compras_detalle";
    $stmt_detalles = $pdo->query($sql_detalles);
    $comprasDetalle = [];
    while ($row = $stmt_detalles->fetch(PDO::FETCH_ASSOC)) {
        $comprasDetalle[$row['compra_id']][] = $row;
    }

    // === Consultar proveedores ===
    $sql_proveedores = "SELECT id, nombre FROM proveedores ORDER BY nombre";
    $stmt_proveedores = $pdo->query($sql_proveedores);
    $proveedores = $stmt_proveedores->fetchAll(PDO::FETCH_ASSOC);

    // === Consultar personal ===
    $sql_personal = "SELECT id, nombre, apellidos FROM personal ORDER BY nombre";
    $stmt_personal = $pdo->query($sql_personal);
    $personal = $stmt_personal->fetchAll(PDO::FETCH_ASSOC);

    // === Consultar productos ===
    $sql_productos = "SELECT * FROM productos ORDER BY nombre";
    $stmt_productos = $pdo->query($sql_productos);
    $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $compras = [];
    $comprasDetalle = [];
    $proveedores = [];
    $personal = [];
    $productos = [];
    $mensaje_error = "Error al consultar la base de datos: " . $e->getMessage();

} catch (Exception $e) {
    $compras = [];
    $comprasDetalle = [];
    $proveedores = [];
    $personal = [];
    $productos = [];
    $mensaje_error = "Error general: " . $e->getMessage();
}

// Mensajes desde sesión (flash messages)
if (isset($_SESSION['error'])) {
    $mensaje_error = $_SESSION['error'];
}
if (isset($_SESSION['success'])) {
    $mensaje_exito = $_SESSION['success'];
}
unset($_SESSION['error'], $_SESSION['success']);
?>

<div class="container-fluid" id="content">

    <!-- Encabezado y buscador -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-cart me-2"></i>Gestión de Compras</h3>
            <!-- Botón para crear nueva compra, si fuera necesario -->
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearCompra">
                <i class="bi bi-plus-circle me-1"></i>Registrar Compra
            </button>
        </div>
        <div class="col-md-4 offset-md-2">
            <input type="text" id="buscador" class="form-control" placeholder="Buscar compra...">
        </div>
    </div>

    <!-- Mensajes de alerta -->
    <?php if ($mensaje_error): ?>
        <div id="mensaje" class="alert alert-danger"><?= htmlspecialchars($mensaje_error) ?></div>
    <?php endif; ?>
    <?php if ($mensaje_exito): ?>
        <div id="mensaje" class="alert alert-success"><?= htmlspecialchars($mensaje_exito) ?></div>
    <?php endif; ?>

    <!-- Tabla de Compras -->
    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table id="tablaCompras" class="table table-hover table-bordered table-sm align-middle">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th>ID</th>
                        <th>Código Factura</th>
                        <th>Proveedor</th>
                        <th>Personal</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado de Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($compras as $compra): ?>
                        <tr>
                            <td><?= htmlspecialchars($compra['id']) ?></td>
                            <td>
                                <?php if ($compra['codigo_factura']): ?>
                                    <?= htmlspecialchars($compra['codigo_factura']) ?>
                                <?php else: ?>
                                    <i class="bi bi-lock-fill text-muted" title="Sin código de factura"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($proveedores[array_search($compra['proveedor_id'], array_column($proveedores, 'id'))]['nombre']) ?></td>
                            <td><?= htmlspecialchars($personal[array_search($compra['personal_id'], array_column($personal, 'id'))]['nombre']) ?></td>
                            <td><?= htmlspecialchars($compra['fecha']) ?></td>
                            <td><?= 'XAF' . number_format($compra['total'], 2) ?></td>
                            <td>
                                <?php
                                $badge_class = '';
                                switch ($compra['estado_pago']) {
                                    case 'PAGADO':
                                        $badge_class = 'bg-success';
                                        break;
                                    case 'PENDIENTE':
                                        $badge_class = 'bg-danger';
                                        break;
                                    case 'PARCIAL':
                                        $badge_class = 'bg-warning';
                                        break;
                                }
                                ?>
                                <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($compra['estado_pago']) ?></span>
                            </td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-info btn-ver-detalles"
                                    data-id="<?= htmlspecialchars($compra['id']) ?>"
                                    data-codigo-factura="<?= htmlspecialchars($compra['codigo_factura']) ?>"
                                    data-proveedor="<?= htmlspecialchars($proveedores[array_search($compra['proveedor_id'], array_column($proveedores, 'id'))]['nombre']) ?>"
                                    data-personal="<?= htmlspecialchars($personal[array_search($compra['personal_id'], array_column($personal, 'id'))]['nombre']) ?>"
                                    data-fecha="<?= htmlspecialchars($compra['fecha']) ?>"
                                    data-total="<?= htmlspecialchars($compra['total']) ?>"
                                    data-estado-pago="<?= htmlspecialchars($compra['estado_pago']) ?>"
                                    data-monto-entregado="<?= htmlspecialchars($compra['monto_entregado']) ?>"
                                    data-monto-gastado="<?= htmlspecialchars($compra['monto_gastado']) ?>"
                                    data-cambio-devuelto="<?= htmlspecialchars($compra['cambio_devuelto']) ?>"
                                    data-monto-pendiente="<?= htmlspecialchars($compra['monto_pendiente']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalVerDetalles">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary btn-editar-compra"
                                    data-id="<?= htmlspecialchars($compra['id']) ?>"
                                    data-codigo-factura="<?= htmlspecialchars($compra['codigo_factura']) ?>"
                                    data-proveedor-id="<?= htmlspecialchars($compra['proveedor_id']) ?>"
                                    data-personal-id="<?= htmlspecialchars($compra['personal_id']) ?>"
                                    data-fecha="<?= htmlspecialchars($compra['fecha']) ?>"
                                    data-monto-entregado="<?= htmlspecialchars($compra['monto_entregado']) ?>"
                                    data-monto-gastado="<?= htmlspecialchars($compra['monto_gastado']) ?>"
                                    data-cambio-devuelto="<?= htmlspecialchars($compra['cambio_devuelto']) ?>"
                                    data-monto-pendiente="<?= htmlspecialchars($compra['monto_pendiente']) ?>"
                                    data-total="<?= htmlspecialchars($compra['total']) ?>"
                                    data-estado-pago="<?= htmlspecialchars($compra['estado_pago']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalActualizarCompra">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="api/eliminar_compra.php?id=<?= htmlspecialchars($compra['id']) ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Está seguro de eliminar esta compra? Esta acción no se puede deshacer.')">
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

<!-- Modal Registrar Compra -->
<div class="modal fade" id="modalCrearCompra" tabindex="-1" aria-labelledby="modalCrearCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCrearCompraLabel"><i class="bi bi-plus-circle me-2"></i>Registrar Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCrearCompra" action="api/guardar_compra.php" method="POST"> 
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="proveedor_crear" class="form-label">Proveedor</label>
                            <select class="form-select" id="proveedor_crear" name="proveedor_id" required>
                                <option value="" disabled selected>Seleccione un proveedor</option>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= htmlspecialchars($prov['id']) ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="personal_crear" class="form-label">Personal</label>
                            <select class="form-select" id="personal_crear" name="personal_id" required>
                                <option value="" disabled selected>Seleccione el personal</option>
                                <?php foreach ($personal as $pers): ?>
                                    <option value="<?= htmlspecialchars($pers['id']) ?>"><?= htmlspecialchars($pers['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_crear" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha_crear" name="fecha" required>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="checkFacturaCrear">
                                <label class="form-check-label" for="checkFacturaCrear">Agregar Código de Factura</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="wrapperFacturaCrear" style="display: none;">
                            <label for="codigo_factura_crear" class="form-label">Código de Factura</label>
                            <input type="text" class="form-control" id="codigo_factura_crear" name="codigo_factura">
                        </div>
                    </div>

                    <h6>Productos</h6>
                    <div id="productos-container-crear">
                        <div class="row g-3 mb-2 producto-item">
                            <div class="col-md-3">
                                <label class="form-label">Producto</label>
                                <select class="form-select producto-select" name="productos[0][id]" required>
                                    <option value="" disabled selected>Seleccione un producto</option>
                                    <?php foreach ($productos as $prod): ?>
                                        <option value="<?= htmlspecialchars($prod['id']) ?>"><?= htmlspecialchars($prod['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Cantidad</label>
                                <input type="number" class="form-control producto-cantidad" name="productos[0][cantidad]" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Precio Compra</label>
                                <input type="number" class="form-control producto-precio" name="productos[0][precio]" step="0.01" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Precio Venta</label>
                                <input type="number" class="form-control producto-precio-venta" name="productos[0][precio_venta]" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Detalles</label>
                                <p class="form-control-plaintext mb-0">
                                    <strong class="text-muted">Compra:</strong> <span class="total-compra">XAF 0.00</span><br>
                                    <strong class="text-success">Venta:</strong> <span class="total-venta">XAF 0.00</span><br>
                                    <strong class="text-primary">Beneficio:</strong> <span class="beneficio">XAF 0.00</span>
                                </p>
                            </div>
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-danger btn-sm remove-producto"><i class="bi bi-x-circle"></i> Eliminar</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mb-3" id="add-producto-crear"><i class="bi bi-plus-circle me-1"></i>Agregar otro producto</button>

                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="estado_pago_crear" class="form-label">Estado de Pago</label>
                            <select class="form-select" id="estado_pago_crear" name="estado_pago" required>
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="PARCIAL">PARCIAL</option>
                                <option value="PAGADO">PAGADO</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="monto_entregado_crear" class="form-label">Monto Entregado (XAF)</label>
                            <input type="number" class="form-control" id="monto_entregado_crear" name="monto_entregado" step="0.01" disabled>
                        </div>
                        <div class="col-md-6">
                            <p class="mt-3"><strong>Total a Pagar:</strong> <span id="total_crear">XAF 0.00</span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mt-3"><strong>Cambio / Pendiente:</strong> <span id="cambio_pendiente_crear">XAF 0.00</span></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="formCrearCompra" class="btn btn-primary"><i class="bi bi-save me-1"></i>Guardar Compra</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Actualizar Compra -->
<div class="modal fade" id="modalActualizarCompra" tabindex="-1" aria-labelledby="modalActualizarCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalActualizarCompraLabel"><i class="bi bi-pencil me-2"></i>Actualizar Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formActualizarCompra" action="api/actualizar_compra.php" method="POST">
                    <input type="hidden" id="compra_id_actualizar" name="id">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="proveedor_actualizar" class="form-label">Proveedor</label>
                            <select class="form-select" id="proveedor_actualizar" name="proveedor_id" required>
                                <option value="" disabled selected>Seleccione un proveedor</option>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= htmlspecialchars($prov['id']) ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="personal_actualizar" class="form-label">Personal</label>
                            <select class="form-select" id="personal_actualizar" name="personal_id" required>
                                <option value="" disabled selected>Seleccione el personal</option>
                                <?php foreach ($personal as $pers): ?>
                                    <option value="<?= htmlspecialchars($pers['id']) ?>"><?= htmlspecialchars($pers['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fecha_actualizar" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha_actualizar" name="fecha" required>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="checkFacturaActualizar">
                                <label class="form-check-label" for="checkFacturaActualizar">Agregar Código de Factura</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="wrapperFacturaActualizar" style="display: none;">
                            <label for="codigo_factura_actualizar" class="form-label">Código de Factura</label>
                            <input type="text" class="form-control" id="codigo_factura_actualizar" name="codigo_factura">
                        </div>
                    </div>

                    <h6>Productos</h6>
                    <div id="productos-container-actualizar">
                        <!-- Los productos se cargarán aquí con JS -->
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mb-3" id="add-producto-actualizar"><i class="bi bi-plus-circle me-1"></i>Agregar otro producto</button>

                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="estado_pago_actualizar" class="form-label">Estado de Pago</label>
                            <select class="form-select" id="estado_pago_actualizar" name="estado_pago" required>
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="PARCIAL">PARCIAL</option>
                                <option value="PAGADO">PAGADO</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="monto_entregado_actualizar" class="form-label">Monto Entregado (XAF)</label>
                            <input type="number" class="form-control" id="monto_entregado_actualizar" name="monto_entregado" step="0.01" disabled>
                        </div>
                        <div class="col-md-6">
                            <p class="mt-3"><strong>Total a Pagar:</strong> <span id="total_actualizar">XAF 0.00</span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mt-3"><strong>Cambio / Pendiente:</strong> <span id="cambio_pendiente_actualizar">XAF 0.00</span></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="formActualizarCompra" class="btn btn-primary"><i class="bi bi-save me-1"></i>Actualizar Compra</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalles de Compra -->
<div class="modal fade" id="modalVerDetalles" tabindex="-1" aria-labelledby="modalVerDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalVerDetallesLabel"><i class="bi bi-receipt me-2"></i>Detalles de Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p><strong>ID de Compra:</strong> <span id="detalle-id"></span></p>
                        <p><strong>Proveedor:</strong> <span id="detalle-proveedor"></span></p>
                        <p><strong>Personal:</strong> <span id="detalle-personal"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fecha:</strong> <span id="detalle-fecha"></span></p>
                        <p><strong>Total:</strong> <span id="detalle-total"></span></p>
                        <p><strong>Estado de Pago:</strong> <span id="detalle-estado-pago"></span></p>
                    </div>

                    <!-- Código de Factura (Opcional) con interruptor -->
                    <div class="col-md-12">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="checkFacturaDetalle" checked>
                            <label class="form-check-label" for="checkFacturaDetalle">Ver Código de Factura</label>
                        </div>
                        <div id="wrapperFacturaDetalle" class="mb-3">
                            <label for="detalle-codigo-factura" class="form-label">Código de Factura</label>
                            <input type="text" id="detalle-codigo-factura" class="form-control" readonly>
                        </div>
                    </div>

                    <!-- Detalles financieros adicionales -->
                    <div class="col-md-12">
                        <h6>Resumen Financiero</h6>
                        <table class="table table-bordered table-sm">
                            <tbody>
                                <tr>
                                    <td>Monto Entregado:</td>
                                    <td><span id="detalle-monto-entregado"></span></td>
                                </tr>
                                <tr>
                                    <td>Monto Gastado:</td>
                                    <td><span id="detalle-monto-gastado"></span></td>
                                </tr>
                                <tr>
                                    <td>Cambio Devuelto:</td>
                                    <td><span id="detalle-cambio-devuelto"></span></td>
                                </tr>
                                <tr>
                                    <td>Monto Pendiente:</td>
                                    <td><span id="detalle-monto-pendiente"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="col-md-12">
                        <h6>Productos en la Compra</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="detalles-compra-tabla">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio de Compra</th>
                                        <th>Precio de Venta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Aquí se llenará el detalle de la compra con JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts de JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Lógica de los Modales de Registro y Actualización ---

        // Función para recalcular los totales de compra, venta y beneficio por producto
        function recalcularProductoTotales(item) {
            const cantidad = parseFloat(item.querySelector('.producto-cantidad').value) || 0;
            const precioCompra = parseFloat(item.querySelector('.producto-precio').value) || 0;
            const precioVenta = parseFloat(item.querySelector('.producto-precio-venta').value) || 0;

            const totalCompra = cantidad * precioCompra;
            const totalVenta = cantidad * precioVenta;
            const beneficio = totalVenta - totalCompra;

            item.querySelector('.total-compra').textContent = `XAF${totalCompra.toFixed(2)}`;
            item.querySelector('.total-venta').textContent = `XAF${totalVenta.toFixed(2)}`;
            item.querySelector('.beneficio').textContent = `XAF${beneficio.toFixed(2)}`;
        }

        // Función para recalcular el total de la compra y la diferencia de pago
        function recalcularTotalCompra(modalId) {
            const container = document.getElementById(`productos-container-${modalId}`);
            let totalGeneralCompra = 0;
            const items = container.querySelectorAll('.producto-item');
            
            items.forEach(item => {
                const cantidadInput = item.querySelector('.producto-cantidad');
                const precioInput = item.querySelector('.producto-precio');

                const cantidad = parseFloat(cantidadInput.value) || 0;
                const precio = parseFloat(precioInput.value) || 0;
                totalGeneralCompra += cantidad * precio;
            });
            
            const totalSpan = document.getElementById(`total_${modalId}`);
            totalSpan.textContent = `XAF${totalGeneralCompra.toFixed(2)}`;

            const montoEntregadoInput = document.getElementById(`monto_entregado_${modalId}`);
            const cambioPendienteSpan = document.getElementById(`cambio_pendiente_${modalId}`);
            
            // Habilita/Deshabilita el campo de monto entregado
            const estadoPagoSelect = document.getElementById(`estado_pago_${modalId}`);
            const estado = estadoPagoSelect.value;
            
            if (estado === "PENDIENTE") {
                montoEntregadoInput.value = '';
                montoEntregadoInput.disabled = true;
            } else {
                montoEntregadoInput.disabled = false;
            }

            const montoEntregado = parseFloat(montoEntregadoInput.value) || 0;
            let cambioPendiente = montoEntregado - totalGeneralCompra;
            
            // Alternar color y símbolo
            if (cambioPendiente >= 0) {
                cambioPendienteSpan.style.color = 'blue';
                cambioPendienteSpan.textContent = `+XAF${cambioPendiente.toFixed(2)}`;
            } else {
                cambioPendienteSpan.style.color = 'red';
                cambioPendienteSpan.textContent = `-XAF${Math.abs(cambioPendiente).toFixed(2)}`;
            }
        }

        // Función para agregar una fila de producto
        function agregarFilaProducto(containerId, data = {}) {
            const container = document.getElementById(containerId);
            // Asegúrate de que los datos de productos estén disponibles
            const productosData = <?= json_encode($productos); ?>;
            const newIndex = container.children.length;
            
            // Buscar el precio unitario del producto si se proporciona un ID
            let precioUnitarioProducto = null;
            if (data.producto_id) {
                const producto = productosData.find(p => p.id == data.producto_id);
                if (producto && producto.precio_unitario !== null) {
                    precioUnitarioProducto = producto.precio_unitario;
                }
            }

            const newItemHtml = `
                <div class="row g-3 mb-2 producto-item border-bottom pb-2">
                    <div class="col-md-3">
                        <label class="form-label">Producto</label>
                        <select class="form-select producto-select" name="productos[${newIndex}][id]" required>
                            <option value="" disabled selected>Seleccione un producto</option>
                            <?php foreach ($productos as $prod): ?>
                                <option value="<?= htmlspecialchars($prod['id']) ?>"><?= htmlspecialchars($prod['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control producto-cantidad" name="productos[${newIndex}][cantidad]" min="1" value="${data.cantidad || ''}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio Compra</label>
                        <input type="number" class="form-control producto-precio" name="productos[${newIndex}][precio]" step="0.01" value="${data.precio_compra || ''}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio Venta</label>
                        <input type="number" class="form-control producto-precio-venta" name="productos[${newIndex}][precio_venta]" step="0.01" value="${data.precio_venta || (precioUnitarioProducto !== null ? precioUnitarioProducto : '')}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Detalles</label>
                        <p class="form-control-plaintext mb-0">
                            <strong class="text-muted">Compra:</strong> <span class="total-compra">XAF 0.00</span><br>
                            <strong class="text-success">Venta:</strong> <span class="total-venta">XAF 0.00</span><br>
                            <strong class="text-primary">Beneficio:</strong> <span class="beneficio">XAF 0.00</span>
                        </p>
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="button" class="btn btn-danger btn-sm remove-producto"><i class="bi bi-x-circle"></i> Eliminar</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newItemHtml);
            
            const newElement = container.lastElementChild;
            // Después de agregar el elemento, establecer el valor seleccionado y recalcular
            if (data.producto_id) {
                const selectElement = newElement.querySelector('.producto-select');
                selectElement.value = data.producto_id;
            }
            recalcularProductoTotales(newElement);
        }
        
        // Helper para buscar el precio unitario de un producto
        function getPrecioUnitarioById(productId, productosData) {
            const producto = productosData.find(p => p.id == productId);
            return producto ? producto.precio_unitario : null;
        }

        // Lógica para el modal de Creación
        const modalCrearCompra = document.getElementById('modalCrearCompra');
        const productosContainerCrear = document.getElementById('productos-container-crear');
        const addProductoCrearBtn = document.getElementById('add-producto-crear');
        const checkFacturaCrear = document.getElementById('checkFacturaCrear');
        const wrapperFacturaCrear = document.getElementById('wrapperFacturaCrear');
        const productosData = <?= json_encode($productos); ?>;
        
        // Listener para agregar productos
        addProductoCrearBtn.addEventListener('click', () => agregarFilaProducto('productos-container-crear'));
        
        // Manejar el toggle del campo de factura en el modal de creación
        checkFacturaCrear.addEventListener('change', function() {
            wrapperFacturaCrear.style.display = this.checked ? 'block' : 'none';
        });

        // Event listener para los cambios en el contenedor de productos (delegación de eventos)
        productosContainerCrear.addEventListener('change', (e) => {
            if (e.target.matches('.producto-select')) {
                const selectedProductId = e.target.value;
                const item = e.target.closest('.producto-item');
                const precioVentaInput = item.querySelector('.producto-precio-venta');
                const precioUnitario = getPrecioUnitarioById(selectedProductId, productosData);
                
                if (precioUnitario !== null) {
                    precioVentaInput.value = precioUnitario;
                } else {
                    precioVentaInput.value = '';
                }
                
                recalcularProductoTotales(item);
                recalcularTotalCompra('crear');
            }
        });
        
        productosContainerCrear.addEventListener('input', (e) => {
            if (e.target.matches('.producto-cantidad') || e.target.matches('.producto-precio') || e.target.matches('.producto-precio-venta')) {
                const item = e.target.closest('.producto-item');
                recalcularProductoTotales(item);
                recalcularTotalCompra('crear');
            }
        });

        productosContainerCrear.addEventListener('click', (e) => {
            if (e.target.matches('.remove-producto') || e.target.closest('.remove-producto')) {
                e.target.closest('.producto-item').remove();
                recalcularTotalCompra('crear');
            }
        });
        
        // Listener para el campo de monto entregado y estado de pago en el modal de Creación
        document.getElementById('monto_entregado_crear').addEventListener('input', () => recalcularTotalCompra('crear'));
        document.getElementById('estado_pago_crear').addEventListener('change', () => recalcularTotalCompra('crear'));
        
        // Al abrir el modal, asegurar el cálculo inicial
        modalCrearCompra.addEventListener('show.bs.modal', function () {
            recalcularTotalCompra('crear');
            checkFacturaCrear.checked = false;
            wrapperFacturaCrear.style.display = 'none';
        });

        // Lógica para el modal de Actualización
        const modalActualizarCompra = document.getElementById('modalActualizarCompra');
        const productosContainerActualizar = document.getElementById('productos-container-actualizar');
        const addProductoActualizarBtn = document.getElementById('add-producto-actualizar');
        const checkFacturaActualizar = document.getElementById('checkFacturaActualizar');
        const wrapperFacturaActualizar = document.getElementById('wrapperFacturaActualizar');

        // Listener para agregar productos en el modal de actualización
        addProductoActualizarBtn.addEventListener('click', () => agregarFilaProducto('productos-container-actualizar'));
        
        // Manejar el toggle del campo de factura en el modal de actualización
        checkFacturaActualizar.addEventListener('change', function() {
            wrapperFacturaActualizar.style.display = this.checked ? 'block' : 'none';
        });
        
        // Event listener para los cambios en el contenedor de productos (delegación de eventos)
        productosContainerActualizar.addEventListener('change', (e) => {
            if (e.target.matches('.producto-select')) {
                const selectedProductId = e.target.value;
                const item = e.target.closest('.producto-item');
                const precioVentaInput = item.querySelector('.producto-precio-venta');
                const precioUnitario = getPrecioUnitarioById(selectedProductId, productosData);
                
                if (precioUnitario !== null) {
                    precioVentaInput.value = precioUnitario;
                } else {
                    precioVentaInput.value = '';
                }

                recalcularProductoTotales(item);
                recalcularTotalCompra('actualizar');
            }
        });
        
        productosContainerActualizar.addEventListener('input', (e) => {
            if (e.target.matches('.producto-cantidad') || e.target.matches('.producto-precio') || e.target.matches('.producto-precio-venta')) {
                const item = e.target.closest('.producto-item');
                recalcularProductoTotales(item);
                recalcularTotalCompra('actualizar');
            }
        });

        productosContainerActualizar.addEventListener('click', (e) => {
            if (e.target.matches('.remove-producto') || e.target.closest('.remove-producto')) {
                e.target.closest('.producto-item').remove();
                recalcularTotalCompra('actualizar');
            }
        });
        
        // Listener para el campo de monto entregado y estado de pago en el modal de Actualización
        document.getElementById('monto_entregado_actualizar').addEventListener('input', () => recalcularTotalCompra('actualizar'));
        document.getElementById('estado_pago_actualizar').addEventListener('change', () => recalcularTotalCompra('actualizar'));
        
        // Al mostrar el modal de actualización, cargar los datos y recalcular
        modalActualizarCompra.addEventListener('show.bs.modal', function (event) {
            // Lógica para llenar el modal de actualización de compra
            const btn = event.relatedTarget;
            const id = btn.getAttribute('data-id');
            const codigoFactura = btn.getAttribute('data-codigo-factura');
            const proveedorId = btn.getAttribute('data-proveedor-id');
            const personalId = btn.getAttribute('data-personal-id');
            const fecha = btn.getAttribute('data-fecha');
            const estadoPago = btn.getAttribute('data-estado-pago');
            const montoEntregado = btn.getAttribute('data-monto-entregado');
            
            // Llenar los campos del formulario de actualización
            document.getElementById('compra_id_actualizar').value = id;
            document.getElementById('proveedor_actualizar').value = proveedorId;
            document.getElementById('personal_actualizar').value = personalId;
            document.getElementById('fecha_actualizar').value = fecha;
            document.getElementById('estado_pago_actualizar').value = estadoPago;
            document.getElementById('monto_entregado_actualizar').value = parseFloat(montoEntregado).toFixed(2);
            
            // Lógica para el campo de factura
            const hasFactura = codigoFactura && codigoFactura !== 'NULL' && codigoFactura !== '';
            checkFacturaActualizar.checked = hasFactura;
            wrapperFacturaActualizar.style.display = hasFactura ? 'block' : 'none';
            document.getElementById('codigo_factura_actualizar').value = hasFactura ? codigoFactura : '';

            // Cargar los productos de la compra
            const detalles = <?= json_encode($comprasDetalle); ?>;
            productosContainerActualizar.innerHTML = '';
            
            if (detalles[id]) {
                detalles[id].forEach((detalle) => {
                    agregarFilaProducto('productos-container-actualizar', detalle);
                });
            }
            // Recalcular los totales después de cargar los datos
            recalcularTotalCompra('actualizar');
        });

        // --- Lógica del Buscador y Mensajes de Alerta (sin cambios) ---
        
        // Función para manejar la visibilidad del campo de factura
        function toggleFacturaVisibility(checkboxId, wrapperId, inputId) {
            const checkbox = document.getElementById(checkboxId);
            const wrapper = document.getElementById(wrapperId);
            const input = document.getElementById(inputId);

            if (checkbox && wrapper && input) {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        wrapper.style.display = 'block';
                    } else {
                        wrapper.style.display = 'none';
                    }
                });
            }
        }
        
        // Inicializar la lógica para el campo de factura en el modal de detalles
        toggleFacturaVisibility('checkFacturaDetalle', 'wrapperFacturaDetalle', 'detalle-codigo-factura');

        // Lógica para llenar el modal de detalles de compra
        const botonesVerDetalles = document.querySelectorAll('.btn-ver-detalles');
        botonesVerDetalles.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const codigoFactura = this.getAttribute('data-codigo-factura');
                const proveedor = this.getAttribute('data-proveedor');
                const personal = this.getAttribute('data-personal');
                const fecha = this.getAttribute('data-fecha');
                const total = this.getAttribute('data-total');
                const estadoPago = this.getAttribute('data-estado-pago');
                const montoEntregado = this.getAttribute('data-monto-entregado');
                const montoGastado = this.getAttribute('data-monto-gastado');
                const cambioDevuelto = this.getAttribute('data-cambio-devuelto');
                const montoPendiente = this.getAttribute('data-monto-pendiente');

                document.getElementById('detalle-id').textContent = id;
                document.getElementById('detalle-proveedor').textContent = proveedor;
                document.getElementById('detalle-personal').textContent = personal;
                document.getElementById('detalle-fecha').textContent = fecha;
                document.getElementById('detalle-total').textContent = `XAF${parseFloat(total).toFixed(2)}`;
                document.getElementById('detalle-estado-pago').textContent = estadoPago;

                document.getElementById('detalle-monto-entregado').textContent = `XAF${parseFloat(montoEntregado).toFixed(2)}`;
                document.getElementById('detalle-monto-gastado').textContent = `XAF${parseFloat(montoGastado).toFixed(2)}`;
                document.getElementById('detalle-cambio-devuelto').textContent = `XAF${parseFloat(cambioDevuelto).toFixed(2)}`;
                document.getElementById('detalle-monto-pendiente').textContent = `XAF${parseFloat(montoPendiente).toFixed(2)}`;

                const checkboxFactura = document.getElementById('checkFacturaDetalle');
                const wrapperFactura = document.getElementById('wrapperFacturaDetalle');
                const inputFactura = document.getElementById('detalle-codigo-factura');
                if (codigoFactura === 'NULL' || codigoFactura === '') {
                    checkboxFactura.checked = false;
                    wrapperFactura.style.display = 'none';
                    inputFactura.value = '';
                } else {
                    checkboxFactura.checked = true;
                    wrapperFactura.style.display = 'block';
                    inputFactura.value = codigoFactura;
                }

                const detalles = <?= json_encode($comprasDetalle); ?>;
                const tablaBody = document.querySelector('#detalles-compra-tabla tbody');
                tablaBody.innerHTML = '';
                const productosData = <?= json_encode($productos); ?>;
                if (detalles[id]) {
                    detalles[id].forEach(detalle => {
                        const productoNombre = productosData.find(p => p.id === detalle.producto_id)?.nombre || 'Producto Desconocido';
                        const precioUnitario = getPrecioUnitarioById(detalle.producto_id, productosData);
                        
                        // Usar el precio de venta de los detalles o el precio unitario del producto si no está en los detalles
                        const precioVenta = detalle.precio_venta || (precioUnitario !== null ? precioUnitario : 'N/A');

                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                            <td>${productoNombre}</td>
                            <td>${detalle.cantidad}</td>
                            <td>XAF${parseFloat(detalle.precio_compra).toFixed(2)}</td>
                            <td>XAF${parseFloat(precioVenta).toFixed(2)}</td>
                        `;
                        tablaBody.appendChild(fila);
                    });
                }
            });
        });

        const buscador = document.getElementById('buscador');
        const tabla = document.getElementById('tablaCompras');
        const filas = tabla.getElementsByTagName('tr');

        buscador.addEventListener('keyup', function () {
            const filtro = buscador.value.toLowerCase();
            for (let i = 1; i < filas.length; i++) {
                const fila = filas[i];
                const textoFila = fila.textContent.toLowerCase();
                if (textoFila.indexOf(filtro) > -1) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            }
        });
        
        setTimeout(() => {
            const mensaje = document.getElementById('mensaje');
            if (mensaje) {
                mensaje.style.transition = 'opacity 1s ease';
                mensaje.style.opacity = '0';
                setTimeout(() => mensaje.remove(), 1000);
            }
        }, 10000); // 10 segundos
    });
</script>