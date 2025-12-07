<!-- MODALES DE LA VISTA DE CONTABILIDAD -->

<!-- Cobrar consulta -->
<div class="modal fade" id="modalCobrarConsulta" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Cobrar consulta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="accion" value="cobrar_consulta">
                <input type="hidden" name="consulta_id" id="cc_consulta_id">
                <div class="mb-3">
                    <label class="form-label">Monto</label>
                    <div class="input-group">
                        <span class="input-group-text">XAF</span>
                        <input type="number" step="0.01" class="form-control" name="monto" id="cc_monto" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Método de pago</label>
                    <select class="form-select" name="metodo_pago">
                        <option>EFECTIVO</option>
                        <option>TARJETA</option>
                        <option>TRANSFERENCIA</option>
                        <option>OTRO</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Confirmar
                    cobro</button>
            </div>
        </form>
    </div>
</div>

<!-- Cobrar analítica -->
<div class="modal fade" id="modalCobrarAnalitica" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="post">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>Cobrar analítica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="accion" value="cobrar_analitica">
                <input type="hidden" name="analitica_id" id="ca_analitica_id">
                <div class="mb-3">
                    <label class="form-label">Monto</label>
                    <div class="input-group">
                        <span class="input-group-text">XAF</span>
                        <input type="number" step="0.01" class="form-control" name="monto" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de pago</label>
                    <select class="form-select" name="tipo_pago">
                        <option>EFECTIVO</option>
                        <option>SEGURO</option>
                        <option>ADEUDO</option>
                        <option>SIN PAGAR</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Confirmar
                    cobro</button>
            </div>
        </form>
    </div>
</div>

<!-- Nueva venta (farmacia) -->
<div class="modal fade" id="modalNuevaVenta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="post" onsubmit="return buildVentaItemsJson()">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cart-plus me-2"></i>Nueva venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="accion" value="nueva_venta">
                <input type="hidden" name="items_json" id="venta_items_json">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Paciente (opcional)</label>
                        <select name="paciente_id" class="form-select">
                            <option value="">Sin paciente</option>
                            <?php foreach ($pacientes as $p): ?>
                                <option value="<?php echo (int) $p['id']; ?>">
                                    <?php echo htmlspecialchars($p['nom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Método de pago</label>
                        <select name="metodo_pago" class="form-select">
                            <option>EFECTIVO</option>
                            <option>TARJETA</option>
                            <option>TRANSFERENCIA</option>
                            <option>OTRO</option>
                        </select>
                    </div>
                </div>

                <hr>
                <div class="table-responsive">
                    <table class="table align-middle" id="tablaVentaItems">
                        <thead>
                            <tr>
                                <th style="min-width:220px">Producto</th>
                                <th>Cant.</th>
                                <th>Precio</th>
                                <th class="text-end">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select class="form-select form-select-sm prod-select">
                                        <option value="">Selecciona...</option>
                                        <?php foreach ($productos as $pr): ?>
                                            <option value="<?php echo (int) $pr['id']; ?>"
                                                data-precio="<?php echo (float) $pr['precio_unitario']; ?>">
                                                <?php echo htmlspecialchars($pr['nombre']); ?> — XAF
                                                <?php echo money($pr['precio_unitario'] ?? 0); ?> (Stock:
                                                <?php echo (int) $pr['stock_actual']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" min="1" value="1"
                                        class="form-control form-control-sm cantidad"></td>
                                <td><input type="number" step="0.01" class="form-control form-control-sm precio"></td>
                                <td class="text-end subtotal">XAF 0,00</td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-danger"
                                        onclick="removeRow(this)"><i class="bi bi-x"></i></button></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end" id="ventaTotal">XAF 0,00</th>
                                <th class="text-end"><button type="button" class="btn btn-sm btn-primary"
                                        onclick="addRow()"><i class="bi bi-plus"></i></button></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check2-circle"></i> Guardar
                    venta</button>
            </div>
        </form>
    </div>
</div>

<!-- Nueva compra -->
<div class="modal fade" id="modalCrearCompra" tabindex="-1" aria-labelledby="modalCrearCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCrearCompraLabel"><i class="bi bi-plus-circle me-2"></i>Registrar
                    Compra</h5>
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
                                    <option value="<?= htmlspecialchars($prov['id']) ?>">
                                        <?= htmlspecialchars($prov['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="personal_crear" class="form-label">Personal</label>
                            <select class="form-select" id="personal_crear" name="personal_id" required>
                                <option value="" disabled selected>Seleccione el personal</option>
                                <?php foreach ($personal as $pers): ?>
                                    <option value="<?= htmlspecialchars($pers['id']) ?>">
                                        <?= htmlspecialchars($pers['nombre']) ?>
                                    </option>
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
                                <label class="form-check-label" for="checkFacturaCrear">Agregar Código de
                                    Factura</label>
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
                                        <option value="<?= htmlspecialchars($prod['id']) ?>">
                                            <?= htmlspecialchars($prod['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Cantidad</label>
                                <input type="number" class="form-control producto-cantidad"
                                    name="productos[0][cantidad]" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Precio Compra</label>
                                <input type="number" class="form-control producto-precio" id="producto-precio" name="productos[0][precio]"
                                    step="0.01" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Precio Venta</label>
                                <input type="number" class="form-control producto-precio-venta"
                                    name="productos[0][precio_venta]" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Detalles</label>
                                <p class="form-control-plaintext mb-0">
                                    <strong class="text-muted">Compra:</strong> <span class="total-compra">XAF
                                        0.00</span><br>
                                    <strong class="text-success">Venta:</strong> <span class="total-venta">XAF
                                        0.00</span><br>
                                    <strong class="text-primary">Beneficio:</strong> <span class="beneficio">XAF
                                        0.00</span>
                                </p>
                            </div>
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-danger btn-sm remove-producto"><i
                                        class="bi bi-x-circle"></i> Eliminar</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mb-3" id="add-producto-crear"><i
                            class="bi bi-plus-circle me-1"></i>Agregar otro producto</button>

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
                            <input type="number" class="form-control" id="monto_entregado_crear" name="monto_entregado"
                                step="0.01" disabled>
                        </div>
                        <div class="col-md-6">
                            <p class="mt-3"><strong>Total a Pagar:</strong> <span id="total_crear">XAF 0.00</span></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mt-3"><strong>Cambio / Pendiente:</strong> <span id="cambio_pendiente_crear">XAF
                                    0.00</span></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="formCrearCompra" class="btn btn-primary"><i
                        class="bi bi-save me-1"></i>Guardar Compra</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Actualizar Compra -->
<div class="modal fade" id="modalActualizarCompra" tabindex="-1" aria-labelledby="modalActualizarCompraLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalActualizarCompraLabel"><i class="bi bi-pencil me-2"></i>Actualizar
                    Compra</h5>
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
                                    <option value="<?= htmlspecialchars($prov['id']) ?>">
                                        <?= htmlspecialchars($prov['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="personal_actualizar" class="form-label">Personal</label>
                            <select class="form-select" id="personal_actualizar" name="personal_id" required>
                                <option value="" disabled selected>Seleccione el personal</option>
                                <?php foreach ($personal as $pers): ?>
                                    <option value="<?= htmlspecialchars($pers['id']) ?>">
                                        <?= htmlspecialchars($pers['nombre']) ?></option>
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
                                <label class="form-check-label" for="checkFacturaActualizar">Agregar Código de
                                    Factura</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="wrapperFacturaActualizar" style="display: none;">
                            <label for="codigo_factura_actualizar" class="form-label">Código de Factura</label>
                            <input type="text" class="form-control" id="codigo_factura_actualizar"
                                name="codigo_factura">
                        </div>
                    </div>

                    <h6>Productos</h6>
                    <div id="productos-container-actualizar">
                        <!-- Los productos se cargarán aquí con JS -->
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm mb-3" id="add-producto-actualizar"><i
                            class="bi bi-plus-circle me-1"></i>Agregar otro producto</button>

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
                            <input type="number" class="form-control" id="monto_entregado_actualizar"
                                name="monto_entregado" step="0.01" disabled>
                        </div>
                        <div class="col-md-6">
                            <p class="mt-3"><strong>Total a Pagar:</strong> <span id="total_actualizar">XAF 0.00</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mt-3"><strong>Cambio / Pendiente:</strong> <span
                                    id="cambio_pendiente_actualizar">XAF 0.00</span></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" form="formActualizarCompra" class="btn btn-primary"><i
                        class="bi bi-save me-1"></i>Actualizar Compra</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalles de Compra -->
<div class="modal fade" id="modalVerDetalles" tabindex="-1" aria-labelledby="modalVerDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalVerDetallesLabel"><i class="bi bi-receipt me-2"></i>Detalles de Compra
                </h5>
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


<!-- Pago a proveedor -->
<div class="modal fade" id="modalPagoProveedor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content shadow-lg rounded-3 border-0" action="api/actualizar_pagos_compra.php" method="post">

            <!-- Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="bi bi-cash-stack me-2"></i> Pago a proveedor
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">

                <!-- Campos ocultos -->
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="accion" value="pago_proveedor">
                <input type="hidden" name="compra_id" id="pp_compra_id">

                <!-- Información del proveedor -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">

                        <h6 class="fw-bold mb-3 text-primary">
                            <i class="bi bi-person-vcard me-2"></i>Datos del proveedor
                        </h6>

                        <label class="form-label fw-semibold">Código de Factura:</label>
                        <p id="factura" class="mb-2 text-dark fw-bold"></p>

                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light fw-semibold">Nombre:</span>
                            <input type="text" id="nombreProveedor" class="form-control" disabled>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-semibold">Fecha:</span>
                                    <input type="text" id="fechaCompra" class="form-control" disabled>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-semibold">Retraso:</span>
                                    <input type="text" id="tiempoRetraso" class="form-control" disabled>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Registro del pago -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">

                        <h6 class="fw-bold mb-3 text-primary">
                            <i class="bi bi-wallet2 me-2"></i>Registrar pago
                        </h6>

                        <label class="form-label fw-semibold">Monto a pagar</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light fw-semibold">XAF</span>
                            <input type="text" name="monto" id="montoPendiente" class="form-control" required>
                        </div>

                        <label class="form-label fw-semibold">Método de pago</label>
                        <select class="form-select" name="metodo_pago">
                            <option>EFECTIVO</option>
                            <option>TRANSFERENCIA</option>
                            <option>TARJETA</option>
                            <option>OTRO</option>
                        </select>

                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check2-circle me-1"></i> Registrar pago
                </button>
            </div>

        </form>
    </div>
</div>