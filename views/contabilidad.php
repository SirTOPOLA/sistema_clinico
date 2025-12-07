<?php
require("models/consultas_contabilidad.php"); /* En este archivo encontramos todas las consultas que se realizan en esta vista */
require("components/estilos_contabilidad.php"); /* Este archivo proporciona estilos complementarios para esta vista */
?>
<div id="content" class="container-fluid">
    <div class="app-shell d-flex flex-column">
        <!-- Topbar -->
        <nav class="navbar navbar-dark glass sticky-top shadow-sm">
            <div class="container-fluid py-2">
                <a class="navbar-brand fw-bold" href="#"><i class="bi bi-hospital me-2"></i>Finanzas Clínica</a>
                <div class="d-flex gap-2">
                    <button class="btn btn-soft" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAcciones"><i
                            class="bi bi-plus-circle me-1"></i>Acciones rápidas</button>
                    <button class="btn btn-soft" data-bs-toggle="offcanvas" data-bs-target="#offcanvasFiltros"><i
                            class="bi bi-funnel me-1"></i>Filtros</button>
                </div>
            </div>
        </nav>
        <!-- Contenido principal -->
        <main class="container my-4">
            <!-- KPIs -->
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-3">
                    <div class="card glass p-3 kpi text-center">
                        <div class="label"><i class="bi bi-clipboard2-pulse me-1"></i>Consultas hoy</div>
                        <div class="value"><?php echo (int) $kpi['consultas_hoy']; ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card glass p-3 kpi text-center">
                        <div class="label"><i class="bi bi-cash-coin me-1"></i>Ingresos consultas</div>
                        <div class="value">XAF <?php echo money($kpi['ingresos_consultas']); ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card glass p-3 kpi text-center">
                        <div class="label"><i class="bi bi-lab me-1"></i>Analíticas hoy</div>
                        <div class="value"><?php echo (int) $kpi['analiticas_hoy']; ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card glass p-3 kpi text-center">
                        <div class="label"><i class="bi bi-cart-check me-1"></i>Ingresos farmacia</div>
                        <div class="value">XAF <?php echo money($kpi['ingresos_farmacia']); ?></div>
                    </div>
                </div>
            </div>
            <!-- Navegación principal (pills) -->
            <ul class="nav nav-pills glass p-2 mb-3 gap-2" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-consultas-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-consultas" type="button" role="tab" aria-controls="pills-consultas"
                        aria-selected="true">
                        <i class="bi bi-clipboard2-pulse me-1"></i>Consultas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-analiticas-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-analiticas" type="button" role="tab" aria-controls="pills-analiticas"
                        aria-selected="false">
                        <i class="bi bi-lab me-1"></i>Analíticas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-farmacia-venta-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-farmacia-venta" type="button" role="tab" aria-controls="pills-farmacia"
                        aria-selected="false">
                        <i class="bi bi-capsule-pill me-1"></i>Ventas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-farmacia-compra-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-farmacia-compra" type="button" role="tab" aria-controls="pills-farmacia"
                        aria-selected="false">
                        <i class="bi bi-capsule-pill me-1"></i>Compras
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="pills-tabContent">
                <!-- ================= Consultas ================ -->
                <div class="tab-pane fade show active" id="pills-consultas" role="tabpanel"
                    aria-labelledby="pills-consultas-tab" tabindex="0">
                    <div class="card glass">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-clipboard2-pulse me-2"></i>Consultas recientes</h5>
                            <div class="d-flex gap-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="searchConsultas"
                                        placeholder="Buscar por ID paciente...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Paciente</th>
                                            <th>Precio</th>
                                            <th>Pagado</th>
                                            <th>Fecha</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyConsultas">
                                        <?php foreach ($consultas as $c): ?>
                                            <tr>
                                                <td>#<?php echo (int) $c['id']; ?></td>
                                                <td><?= htmlspecialchars($c['nombre_paciente']); ?></td>
                                                <td>XAF <?php echo money($c['precio'] ?? 0); ?></td>
                                                <td>
                                                    <?php if ((int) $c['pagado'] === 1): ?>
                                                        <span class="badge rounded-pill bg-success"><i class="bi bi-check2"></i>
                                                            Pagado</span>
                                                    <?php else: ?>
                                                        <span class="badge rounded-pill bg-warning text-dark"><i
                                                                class="bi bi-exclamation-triangle"></i> Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($c['fecha_registro']); ?></td>
                                                <td class="text-end">
                                                    <?php if ((int) $c['pagado'] !== 1): ?>
                                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                            data-bs-target="#modalCobrarConsulta"
                                                            data-id="<?php echo (int) $c['id']; ?>"
                                                            data-monto="<?php echo (float) ($c['precio'] ?? 0); ?>">
                                                            <i class="bi bi-cash-coin"></i> Cobrar
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-soft" disabled><i
                                                                class="bi bi-receipt"></i> Factura</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ================= Analíticas ================ -->
                <div class="tab-pane fade" id="pills-analiticas" role="tabpanel" aria-labelledby="pills-analiticas-tab"
                    tabindex="0">
                    <div class="card glass">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-lab me-2"></i>Analíticas recientes</h5>
                            <div class="d-flex gap-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="searchAnaliticas"
                                        placeholder="Buscar por ID paciente...">
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Paciente</th>
                                            <th>Tipo prueba</th>
                                            <th>Estado</th>
                                            <th>Pago</th>
                                            <th>Fecha</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyAnaliticas">
                                        <?php foreach ($analiticas as $a): ?>
                                            <tr>
                                                <td>#<?php echo (int) $a['id']; ?></td>
                                                <td><?php echo htmlspecialchars($a['nombre_paciente']); ?></td>
                                                <td><?php echo htmlspecialchars($a['nombre_prueba']); ?></td>
                                                <td><span
                                                        class="badge badge-soft rounded-pill text-black"><?php echo htmlspecialchars($a['estado'] ?? ''); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ((int) $a['pagado'] === 1): ?>
                                                        <span class="badge rounded-pill bg-success"><i class="bi bi-check2"></i>
                                                            Pagado</span>
                                                    <?php else: ?>
                                                        <span class="badge rounded-pill bg-warning text-dark">Pendiente</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($a['fecha_registro']); ?></td>
                                                <td class="text-end">
                                                    <?php if ((int) $a['pagado'] !== 1): ?>
                                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                                            data-bs-target="#modalCobrarAnalitica"
                                                            data-id="<?php echo (int) $a['id']; ?>">
                                                            <i class="bi bi-cash-coin"></i> Cobrar
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-soft" disabled><i
                                                                class="bi bi-receipt"></i> Factura</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ================= Venta Farmacia ================ -->
                <div class="tab-pane fade" id="pills-farmacia-venta" role="tabpanel"
                    aria-labelledby="pills-farmacia-venta-tab" tabindex="0">

                    <div class="card glass">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-capsule-pill me-2"></i>Ventas recientes</h5>
                            <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalNuevaVenta"><i class="bi bi-cart-plus"></i> Nueva
                                venta</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Paciente</th>
                                            <th>Fecha</th>
                                            <th>Monto</th>
                                            <th>Pago</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ventas as $v): ?>
                                            <tr>
                                                <td>#<?php echo (int) $v['id']; ?></td>
                                                <td><?php echo htmlspecialchars($v['nombre_paciente']); ?></td>
                                                <td><?php echo htmlspecialchars($v['fecha']); ?></td>
                                                <td>XAF <?php echo money($v['monto_total']); ?></td>
                                                <td><span
                                                        class="badge rounded-pill bg-success"><?php echo htmlspecialchars($v['metodo_pago']); ?></span>
                                                </td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-soft"><i
                                                            class="bi bi-printer"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ====================== compra farmacia =====================-->
                <div class="tab-pane fade" id="pills-farmacia-compra" role="tabpanel"
                    aria-labelledby="pills-farmacia-compra-tab" tabindex="0">

                    <div class="card glass h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Compras a proveedores</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalCrearCompra"><i class="bi bi-bag-plus"></i> Nueva
                                compra</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Proveedor</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Pendiente</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($compras as $compra): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($compra['id']) ?></td>

                                                <td><?= htmlspecialchars($proveedores[array_search($compra['proveedor_id'], array_column($proveedores, 'id'))]['nombre']) ?>
                                                </td>
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
                                                    <span
                                                        class="badge <?= $badge_class ?>"><?= htmlspecialchars($compra['estado_pago']) ?></span>
                                                </td>
                                                <td><?= 'XAF' . number_format($compra['monto_pendiente'], 2) ?></td>

                                                <td class="text-end">
                                                    <div class="class=" btn-group btn-group-sm" role="group"">
                                                            <button class=" btn btn-outline-secondary px-2"
                                                        onclick="imprimirComprobante(<?= (int) $c['id']; ?>)"
                                                        title="Imprimir comprobante">
                                                        <i class="bi bi-printer"></i>
                                                        </button>

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
                                                            data-bs-toggle="modal" data-bs-target="#modalVerDetalles">
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
                                                            data-bs-toggle="modal" data-bs-target="#modalActualizarCompra">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <?php $montoPendiente = (float) str_replace(',', '', money($compra['monto_pendiente'])); ?>
                                                        <?php if ($montoPendiente > 0): ?>
                                                            <button class="btn btn-success px-2" data-bs-toggle="modal"
                                                                data-bs-target="#modalPagoProveedor"
                                                                data-id="<?= (int) $compra['id']; ?>"
                                                                data-proveedor="<?= htmlspecialchars($proveedores[array_search($compra['proveedor_id'], array_column($proveedores, 'id'))]['nombre']) ?>"
                                                                data-fechaCompra="<?= htmlspecialchars($compra['fecha']); ?>"
                                                                data-montoPendiente="<?= (float) $compra['monto_pendiente']; ?>"
                                                                data-factura="<?= htmlspecialchars($compra['codigo_factura']); ?>"
                                                                title="Registrar pago pendiente">
                                                                <i class="bi bi-cash-stack"></i>
                                                            </button>
                                                        <?php endif; ?>


                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>


                                </table>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <span class="text-secondary">Pendiente total</span>
                            <strong class="text-light">XAF
                                <?php echo money($kpi['pendientes_prov']); ?></strong>
                        </div>
                    </div>

                </div>

            </div>
        </main>
        <!-- Offcanvas Filtros -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasFiltros">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title"><i class="bi bi-funnel me-2"></i>Filtros</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="mb-3">
                    <label class="form-label">Rango de fechas</label>
                    <div class="input-group">
                        <input type="date" class="form-control" id="fdesde">
                        <input type="date" class="form-control" id="fhasta">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Paciente</label>
                    <select class="form-select" id="fpaciente">
                        <option value="">Todos</option>
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?php echo (int) $p['id']; ?>"><?php echo htmlspecialchars($p['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-success w-100"><i class="bi bi-funnel"></i> Aplicar</button>
            </div>
        </div>

        <!-- Offcanvas Acciones rápidas -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAcciones">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title"><i class="bi bi-lightning-charge me-2"></i>Acciones rápidas</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevaVenta"><i
                            class="bi bi-cart-plus me-1"></i> Registrar venta</button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearCompra"><i
                            class="bi bi-bag-plus me-1"></i> Registrar compra</button>
                    <a href="#pills-consultas" class="btn btn-soft" data-bs-toggle="pill"><i
                            class="bi bi-clipboard2-pulse me-1"></i> Ir a consultas</a>
                    <a href="#pills-analiticas" class="btn btn-soft" data-bs-toggle="pill"><i
                            class="bi bi-lab me-1"></i> Ir a analíticas</a>
                    <a href="#pills-farmacia" class="btn btn-soft" data-bs-toggle="pill"><i
                            class="bi bi-capsule-pill me-1"></i> Ir a farmacia</a>
                </div>
            </div>
        </div>



        <!-- =================== MODALES =================== -->
        <?php require("modals/modals_contabilidad.php"); ?>


        <!-- Toast de feedback -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
            <div id="appToast"
                class="toast align-items-center text-bg-<?php echo $flash['type'] ?? 'secondary'; ?> border-0"
                role="alert" aria-live="assertive" aria-atomic="true" <?php echo $flash ? 'data-bs-autohide="true"' : 'data-bs-autohide="false"'; ?>>
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo htmlspecialchars($flash['msg'] ?? 'Listo'); ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>

        <footer class="container py-4 text-center text-secondary">
            <small>© <?php echo date('Y'); ?> Finanzas Clínica — Dr. Óscar.</small>
        </footer>
    </div>
</div>

<?php

require("components/js_contabilidad.php"); /* Este archivo proporciona los script completos JS */

?>