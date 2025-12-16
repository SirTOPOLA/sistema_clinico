<!-- MODALES DE LA VISTA DE CONTABILIDAD -->
 
 <!--  ========================= CONSULTAS =================== -->
<div class="modal fade" id="modalCobrarConsulta" tabindex="-1">
    <div class="modal-dialog modal-lg">
    <form action="api/guardar_pago.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Pagar Consulta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
                <input type="hidden" name="id_paciente" id="idPacientePagoConsulta">

                <div class="mb-3">
                    <strong>Paciente:</strong> <span id="nombrePacientePagoConsulta"></span><br>
                    <strong>Fecha:</strong> <span id="fechaPacientePagoConsulta"></span>
                </div>

                          <div class="text-end mb-3">
                    <strong>Total a Pagar: </strong><span id="totalPagoConsulta" class="fs-5">0 FCFA</span>
                </div>

                <div class="mb-3">
                    <label for="tipoPagoConsulta" class="form-label">Tipo de Pago</label>
                    <select class="form-select" id="tipoPagoConsulta" name="tipo_pago" required>
                    </select>
                </div>
                
                <div id="contenedorMontoAPagarConsulta" class="mb-3" style="display:none;">
                    <label for="montoPagar" class="form-label">Monto a Pagar</label>
                    <input type="number" class="form-control" id="montoPagarConsulta" name="monto_pagar" min="0">
                </div>

                <div id="contenedorMontoPendienteConsulta" class="mb-3" style="display:none;">
                    <strong>Monto Pendiente:</strong> <span id="montoPendienteConsulta" class="fs-5 text-danger">0 FCFA</span>
                </div>

                <div id="contenedorSeguroConsulta" class="mb-3" style="display:none;">
                    <label for="idSeguro" class="form-label">Seleccionar Seguro</label>
                    <select class="form-select" id="idSeguroConsulta" name="id_seguro">
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="bi bi-wallet2 me-1"></i> Confirmar Pago</button>
            </div>
        </form>
    </div>
</div>



<!-- ===================== ANALÍTICAS ==================== -->
<div class="modal fade" id="modalPagar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="api/guardar_pago.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Pagar Pruebas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
                <input type="hidden" name="id_paciente" id="idPacientePago">

                <div class="mb-3">
                    <strong>Paciente:</strong> <span id="nombrePacientePago"></span><br>
                    <strong>Fecha:</strong> <span id="fechaPacientePago"></span>
                </div>

                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Seleccionar</th>
                            <th>Tipo de Prueba</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPruebasPago">
                    </tbody>
                </table>

                <div class="text-end mb-3">
                    <strong>Total a Pagar: </strong><span id="totalPago" class="fs-5">0 FCFA</span>
                </div>

                <div class="mb-3">
                    <label for="tipoPago" class="form-label">Tipo de Pago</label>
                    <select class="form-select" id="tipoPago" name="tipo_pago" required>
                    </select>
                </div>
                
                <div id="contenedorMontoAPagar" class="mb-3" style="display:none;">
                    <label for="montoPagar" class="form-label">Monto a Pagar</label>
                    <input type="number" class="form-control" id="montoPagar" name="monto_pagar" min="0">
                </div>

                <div id="contenedorMontoPendiente" class="mb-3" style="display:none;">
                    <strong>Monto Pendiente:</strong> <span id="montoPendiente" class="fs-5 text-danger">0 FCFA</span>
                </div>

                <div id="contenedorSeguro" class="mb-3" style="display:none;">
                    <label for="idSeguro" class="form-label">Seleccionar Seguro</label>
                    <select class="form-select" id="idSeguro" name="id_seguro">
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="bi bi-wallet2 me-1"></i> Confirmar Pago</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="modalEditarPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="api/actualizar_pago.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
                <input type="hidden" name="id_paciente" id="editIdPaciente">
                <input type="hidden" name="fecha" id="editFecha">

                <div class="mb-3">
                    <strong>Paciente:</strong> <span id="editNombrePaciente"></span><br>
                    <strong>Fecha:</strong> <span id="editFechaPaciente"></span>
                </div>

                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Prueba</th>
                            <th>Precio</th>
                            <th>Estado Actual</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPruebasEditar">
                    </tbody>
                </table>

                <div class="text-end mb-3">
                    <strong>Total del Grupo: </strong><span id="editTotalGrupo" class="fs-5">0 FCFA</span>
                </div>

                <div class="mb-3">
                    <label for="editTipoPago" class="form-label">Nuevo Tipo de Pago</label>
                    <select class="form-select" id="editTipoPago" name="tipo_pago" required>
                    </select>
                </div>

                <div id="editContenedorMontoAPagar" class="mb-3" style="display:none;">
                    <label for="editMontoPagar" class="form-label">Monto Pagado</label>
                    <input type="number" class="form-control" id="editMontoPagar" name="monto_pagar" min="0">
                </div>

                <div id="editContenedorMontoPendiente" class="mb-3" style="display:none;">
                    <strong>Monto Pendiente:</strong> <span id="editMontoPendiente" class="fs-5 text-danger">0 FCFA</span>
                </div>

                <div id="editContenedorSeguro" class="mb-3" style="display:none;">
                    <label for="editIdSeguro" class="form-label">Seleccionar Seguro</label>
                    <select class="form-select" id="editIdSeguro" name="id_seguro">
                    </select>
                </div>

                <div class="alert alert-info mt-3" role="alert">
                    <i class="bi bi-info-circle me-1"></i>
                    Al actualizar el pago, se aplicará el nuevo tipo de pago y monto a **todas las pruebas del grupo** que no estén pagadas.
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar Pago</button>
            </div>
        </form>
    </div>
</div>


<!-- =============================== Nueva compra ===============================  -->
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
                        <span id="factura" class="mb-2 text-dark fw-bold"></span>

                        <div class="input-group mb-3">
                            <span class="input-group-text bg-light fw-semibold">Nombre:</span>
                            <input type="text" id="nombreProveedorDePago" class="form-control" disabled>
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
                            <input type="number" name="monto" id="montoPendienteDePago" class="form-control" required>
                        </div>
                        <div class="input-group mb-3">
                           
                            <input type="hidden" name="metodo_pago" id="montoPendienteDePago" class="form-control" value="efectivo" required>
                        </div>

                        

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




<!-- ============================= VENTAS ======================= -->
 


<div class="modal fade" id="modalNuevaVenta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="api/guardar_venta_farmacia.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Registrar Nueva Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                <div class="col-md-6">
                    <label class="form-label">Paciente</label>
                    <input type="text" id="paciente-buscador" class="form-control"
                        placeholder="Buscar por nombre o ID...">
                    <input type="hidden" name="paciente_id" id="paciente_id_input">
                    <div id="paciente-resultados" class="mt-2 list-group"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de Venta</label>
                    <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <hr class="my-3">
                <div class="col-md-12">
                    <h5>Productos de la Venta</h5>
                    <div class="input-group mb-3">
                        <input type="text" id="producto-buscador" class="form-control" placeholder="Buscar producto...">
                        <input type="number" id="cantidad-producto" class="form-control" placeholder="Cantidad" min="1"
                            value="1">
                        <input type="number" id="descuento-producto" class="form-control" placeholder="Descuento (%)"
                            min="0" max="100" value="0">
                        <button class="btn btn-outline-secondary" type="button"
                            id="btn-agregar-producto">Agregar</button>
                    </div>
                    <div id="producto-resultados" class="list-group"></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-sm" id="tabla-detalle-venta-crear">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Descuento</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2"><span id="monto-total-display-crear">0.00 XAF</span></td>
                                    <input type="hidden" name="monto_total" id="monto_total_input_crear">
                                    <input type="hidden" name="productos_json" id="productos_json_crear">
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <hr class="my-3">
                <div class="col-md-6">
                    <label class="form-label">Método de Pago</label>
                    <select name="metodo_pago" class="form-select" id="metodo_pago"> 
                        <option value="efectivo">Efectivo</option>                         
                        <option id="seguro" value="seguro">Con Seguro</option>
                        
                    </select>
                </div>
               <div id="pago_efectivo" class="row">
               <div  class="row">
                   <div class="col-md-6">
                       <label class="form-label">Monto Recibido</label>
                       <input type="number" name="monto_recibido" id="monto_recibido_input_crear" class="form-control"
                           step="0.01" value="0" required>
                   </div>
                   <div class="col-md-6">
                       <label class="form-label">Cambio Devuelto</label>
                       <input type="text" id="cambio_devuelto_display_crear" class="form-control" value="0.00 XAF"
                           readonly>
                       <input type="hidden" name="cambio_devuelto" id="cambio_devuelto_input_crear">
                   </div>
                   </div>

               </div>  
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar Venta</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDetallesVenta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Detalles de la Venta <span
                        id="detalle-venta-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="detalles-venta-info">
                    <div class="col-md-6">
                        <strong>Paciente:</strong> <span id="detalle-paciente"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Atendido por:</strong> <span id="detalle-usuario"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Fecha:</strong> <span id="detalle-fecha"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Monto Total:</strong> <span id="detalle-monto-total"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Método de Pago:</strong> <span id="detalle-metodo-pago"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Estado de Pago:</strong> <span id="detalle-estado-pago"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Monto Recibido:</strong> <span id="detalle-monto-recibido"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Cambio Devuelto:</strong> <span id="detalle-cambio-devuelto"></span>
                    </div>
                    <div class="col-md-12">
                        <strong>Seguro:</strong> <span id="detalle-seguro"></span>
                    </div>
                    <hr class="my-3">
                    <div class="col-md-12">
                        <h5>Productos Vendidos</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Descuento</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="detalle-productos-table">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarVenta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="api/actualizar_venta_farmacia.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="edit-venta-id">
                <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                <div class="col-md-6">
                    <label class="form-label">Paciente</label>
                    <input type="text" id="edit-paciente-buscador" class="form-control"
                        placeholder="Buscar por nombre o ID...">
                    <input type="hidden" name="paciente_id" id="edit-paciente-id-input">
                    <div id="edit-paciente-resultados" class="mt-2 list-group"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de Venta</label>
                    <input type="date" name="fecha" id="edit-fecha" class="form-control" required>
                </div>
                <hr class="my-3">
                <div class="col-md-12">
                    <h5>Productos de la Venta</h5>
                    <div class="input-group mb-3">
                        <input type="text" id="edit-producto-buscador" class="form-control"
                            placeholder="Buscar producto...">
                        <input type="number" id="edit-cantidad-producto" class="form-control" placeholder="Cantidad"
                            min="1" value="1">
                        <input type="number" id="edit-descuento-producto" class="form-control"
                            placeholder="Descuento (%)" min="0" max="100" value="0">
                        <button class="btn btn-outline-secondary" type="button"
                            id="btn-agregar-producto-editar">Agregar</button>
                    </div>
                    <div id="edit-producto-resultados" class="list-group"></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-sm" id="tabla-detalle-venta-editar">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Descuento</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2"><span id="monto-total-display-editar">0.00 XAF</span></td>
                                    <input type="hidden" name="monto_total" id="monto_total_input_editar">
                                    <input type="hidden" name="productos_json" id="productos_json_editar">
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <hr class="my-3">
                <div class="col-md-6">
                    <label class="form-label">Método de Pago</label>
                    <select name="metodo_pago" id="edit-metodo-pago" class="form-select">
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TARJETA">Tarjeta</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="OTRO">Otro</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado de Pago</label>
                    <select name="estado_pago" id="edit-estado-pago" class="form-select">
                        <option value="PAGADO">Pagado</option>
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="PARCIAL">Parcial</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Monto Recibido</label>
                    <input type="number" name="monto_recibido" id="monto_recibido_input_editar" class="form-control"
                        step="0.01" value="0" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cambio Devuelto</label>
                    <input type="text" id="cambio_devuelto_display_editar" class="form-control" value="0.00 XAF"
                        readonly>
                    <input type="hidden" name="cambio_devuelto" id="cambio_devuelto_input_editar">
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit-seguro-check" name="seguro" value="1">
                        <label class="form-check-label" for="edit-seguro-check">Venta con seguro</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar Venta</button>
            </div>
        </form>
    </div>
</div>


<!-- ======================== SEGUROS ======================= -->

<!-- Modal para Crear Seguro -->
<div class="modal fade" id="modalCrearSeguro" tabindex="-1">
    <div class="modal-dialog">
        <form action="api/guardar_seguro.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nuevo Seguro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                <div class="col-md-12">
                    <label for="titular_id">Titular del Seguro (Paciente)</label>
                    <input type="text" id="crear-titular-search" class="form-control" placeholder="Buscar paciente...">
                    <input type="hidden" name="titular_id" id="crear-titular-id">
                    <div id="crear-titular-results" class="list-group mt-2"></div>
                </div>
                <div class="col-md-6">
                    <label for="monto_inicial">Monto Inicial (XAF)</label>
                    <input type="number" name="monto_inicial" step="0.01" min="0.01" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="metodo_pago">Método de Pago</label>
                    <select name="metodo_pago" class="form-control" required>
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TARJETA">Tarjeta</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="OTRO">Otro</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Editar Seguro -->
<div class="modal fade" id="modalEditarSeguro" tabindex="-1">
    <div class="modal-dialog">
        <form action="api/actualizar_seguro.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Seguro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="edit-seguro-id">
                <div class="col-md-12">
                    <label for="titular_id">Titular del Seguro (Paciente)</label>
                    <input type="text" id="edit-titular-search" class="form-control" placeholder="Buscar paciente..." disabled>
                </div>
                <div class="col-md-6">
                    <label for="monto_inicial">Monto Inicial (XAF)</label>
                    <input type="number" name="monto_inicial" id="edit-monto-inicial" step="0.01" min="0.01" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="saldo_actual">Saldo Actual (XAF)</label>
                    <input type="number" name="saldo_actual" id="edit-saldo-actual" step="0.01" class="form-control" readonly>
                </div>
                <div class="col-md-12">
                    <label for="metodo_pago">Método de Pago</label>
                    <select name="metodo_pago" id="edit-metodo-pago" class="form-control" required>
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TARJETA">Tarjeta</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="OTRO">Otro</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Gestionar Beneficiarios -->
<div class="modal fade" id="modalBeneficiarios" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-people me-2"></i>Beneficiarios del Seguro: <span id="beneficiario-titular-nombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <form id="form-agregar-beneficiario" action="api/agregar_beneficiario.php" method="POST">
                            <input type="hidden" name="seguro_id" id="beneficiario-seguro-id">
                            <label>Agregar Beneficiario (Paciente)</label>
                            <div class="input-group">
                                <input type="text" id="agregar-beneficiario-search" class="form-control" placeholder="Buscar paciente...">
                                <input type="hidden" name="paciente_id" id="agregar-beneficiario-id">
                                <button type="submit" class="btn btn-primary" id="btn-agregar-beneficiario" disabled><i class="bi bi-plus"></i> Agregar</button>
                            </div>
                            <div id="agregar-beneficiario-results" class="list-group mt-2"></div>
                        </form>
                    </div>
                </div>
                <hr>
                <h6>Beneficiarios Existentes</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-beneficiarios-body">
                            <!-- Los beneficiarios se cargarán aquí con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Nuevo Modal para Ver Detalles del Seguro -->
<div class="modal fade" id="modalDetalleSeguro" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Detalle de Seguro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="detalle-seguro-body">
                <!-- El contenido se cargará aquí con JS -->
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-imprimir-detalle"><i class="bi bi-printer me-1"></i>Imprimir</button>
            </div>
        </div>
    </div>
</div>


<!-- =======================  -->