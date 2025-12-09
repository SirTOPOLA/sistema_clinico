<?php
require("models/consultas_contabilidad.php"); /* En este archivo encontramos todas las consultas que se realizan en esta vista */
require("components/estilos_contabilidad.php"); /* Este archivo proporciona estilos complementarios para esta vista */
?>
<div id="content" class="container-fluid">
    <div class="app-shell d-flex flex-column">

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
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-seguro-tab" data-bs-toggle="pill" data-bs-target="#pills-seguro"
                        type="button" role="tab" aria-controls="pills-farmacia" aria-selected="false">
                        <i class="bi bi-file-earmark-medical me-1"></i>Seguros
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
                                <thead class="table-light text-nowrap">
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Paciente</th>
                        <th>Código</th>
                        <th>Pruebas</th>
                        <th>Resultados</th>
                        <th>Fecha</th>
                        <th>Pagos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grupos as $grupo): ?>
                        <tr>
                            <td><?= htmlspecialchars($grupo['registros'][0]['id']) ?></td>
                            <td><?= htmlspecialchars($grupo['paciente']) ?></td>
                            <td><?= htmlspecialchars($grupo['codigo']) ?></td>
                            <td>
                                <ul class="mb-0">
                                    <?php foreach ($grupo['registros'] as $r): ?>
                                        <li><?= htmlspecialchars($r['tipo_prueba']) ?></li>
                                    <?php endforeach ?>
                                </ul>
                            </td>
                            <td>
                                <?php
                                $todosConResultado = true;
                                foreach ($grupo['registros'] as $r) {
                                    if (empty($r['resultado'])) {
                                        $todosConResultado = false;
                                        break;
                                    }
                                }
                                ?>
                                <?php if ($todosConResultado): ?>
                                    <span class="badge bg-primary">Resultado</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Sin Resultado</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($grupo['fecha'])) ?></td>
                            <td>
                                <?php
                                $estado_pago = 'SIN PAGAR'; // Valor por defecto
                                // Buscar el estado de pago del primer registro en el grupo
                                if (isset($grupo['registros'][0]['tipo_pago'])) {
                                    $estado_pago = $grupo['registros'][0]['tipo_pago'];
                                }

                                switch ($estado_pago) {
                                    case 'EFECTIVO':
                                    case 'SEGURO':
                                        echo '<span class="badge bg-success">Pagado</span>';
                                        break;
                                    case 'ADEUDO':
                                        echo '<span class="badge bg-warning text-dark">Adeudo</span>';
                                        break;
                                    case 'SIN PAGAR':
                                        echo '<span class="badge bg-danger">Sin Pagar</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-secondary">Desconocido</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($estado_pago == 'SIN PAGAR' ): ?>
                                    <button class="btn btn-sm btn-outline-success btn-pagar" data-bs-toggle="modal"
                                            data-bs-target="#modalPagar"
                                            data-grupo='<?= json_encode(array_filter($grupo['registros'], fn($r) => $r['pagado'] == 0)) ?>'
                                            data-paciente="<?= htmlspecialchars($grupo['paciente']) ?>"
                                            data-fecha="<?= htmlspecialchars($grupo['fecha']) ?>"
                                            data-paciente-id="<?= htmlspecialchars($grupo['id_paciente']) ?>" title="Pagar pruebas">
                                        <i class="bi bi-cash-coin me-1"></i> Pagar
                                    </button>
                                <?php else: ?>
                                    <a href="fpdf/generar_factura.php?id=<?= $grupo['registros'][0]['id'] ?>&fecha=<?= $grupo['fecha'] ?>"
                                            target="_blank" class="btn btn-outline-secondary btn-sm" title="Imprimir Factura">
                                        <i class="bi bi-printer"></i> Imprimir Factura
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary btn-editar-pago mt-1" data-bs-toggle="modal"
                                            data-bs-target="#modalEditarPago"
                                            data-grupo='<?= json_encode($grupo['registros']) ?>'
                                            data-paciente="<?= htmlspecialchars($grupo['paciente']) ?>"
                                            data-fecha="<?= htmlspecialchars($grupo['fecha']) ?>"
                                            data-paciente-id="<?= htmlspecialchars($grupo['id_paciente']) ?>" title="Editar Pago">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </button>
                                <?php endif; ?>
                                <a href="fpdf/imprimir_pruebas.php?id=<?= $grupo['id_paciente'] ?>&fecha=<?= $grupo['fecha'] ?>"
                                        target="_blank" class="btn btn-outline-primary btn-sm mt-1" title="Imprimir Pruebas Médicas">
                                    <i class="bi bi-file-earmark-medical"></i> Ver Pruebas
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
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

                                    <thead class="table-light text-nowrap">
                                        <tr>
                                            <th>ID</th>
                                            <th>Paciente</th>
                                            <th>Atendido por</th>
                                            <th>Fecha</th>
                                            <th>Monto Total</th>
                                            <th>Estado Pago</th>
                                            <th>Método Pago</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Bucle para mostrar las ventas dinámicamente
                                        foreach ($ventas as $venta):
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($venta['id']) ?></td>
                                                <td><?= htmlspecialchars($venta['nombre_paciente']) ?></td>
                                                <td><?= htmlspecialchars($venta['nombre_usuario']) ?></td>
                                                <td class="fecha-venta"><?= date('d/m/Y', strtotime($venta['fecha'])) ?>
                                                </td>
                                                <td class="monto-total-venta">
                                                    <?= number_format($venta['monto_total'], 2) . ' XAF' ?>
                                                </td>
                                                <?php
                                                $estado = htmlspecialchars($venta['estado_pago']);
                                                $clase = ($estado === 'PAGADO') ? 'bg-success text-white fw-bold rounded px-2 py-1'
                                                    : 'bg-warning text-dark fw-bold rounded px-2 py-1';
                                                ?>
                                                <td class="estado-pago">
                                                    <span class="<?= $clase ?>"><?= $estado ?></span>
                                                </td>

                                                <td class="metodo-pago"><?php if ((int) $venta["seguro"] == 1) {
                                                    echo htmlspecialchars("SEGURO");
                                                } else {
                                                    echo htmlspecialchars($venta['metodo_pago']);
                                                }
                                                ?></td>
                                                <td class="text-nowrap">
                                                    <button class="btn btn-sm btn-outline-info btn-ver-detalles-venta"
                                                        data-id="<?= $venta['id'] ?>" data-bs-toggle="modal"
                                                        data-bs-target="#modalDetallesVenta">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary btn-editar-venta"
                                                        data-id="<?= $venta['id'] ?>" data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarVenta">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <a href="eliminar_venta_farmacia.php?id=<?= $venta['id'] ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('¿Eliminar esta venta?')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>



                                <!-- 
                                <table class="table table-hover mb-0 align-middle">
                                    </thead>
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
 -->
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
                <!--====================== Seguros =================================== -->
                <div class="tab-pane fade" id="pills-seguro" role="tabpanel" aria-labelledby="pills-seguro-tab"
                    tabindex="0">

                    <div class="card glass h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Pacientes Asegurados</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalCrearSeguro"><i class="bi bi-bag-plus"></i> Nuevo Seguro
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="table-light text-nowrap">
                                        <tr>
                                            <th>ID</th>
                                            <th>Titular</th>
                                            <th>Monto Inicial</th>
                                            <th>Saldo Actual</th>
                                            <th>Fecha Depósito</th>
                                            <th>Método de Pago</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($seguros as $seguro): ?>
                                            <tr>
                                                <td><?= $seguro['id'] ?></td>
                                                <td><?= htmlspecialchars($seguro['nombre_titular']) ?></td>
                                                <td>XAF <?= number_format($seguro['monto_inicial'], 2) ?></td>
                                                <td>XAF <?= number_format($seguro['saldo_actual'], 2) ?></td>
                                                <td><?= date('d/m/Y', strtotime($seguro['fecha_deposito'])) ?></td>
                                                <td><?= htmlspecialchars($seguro['metodo_pago']) ?></td>
                                                <td class="text-nowrap">
                                                    <button class="btn btn-sm btn-outline-info btn-beneficiarios"
                                                        data-id="<?= $seguro['id'] ?>"
                                                        data-titular="<?= htmlspecialchars($seguro['nombre_titular']) ?>"
                                                        data-bs-toggle="modal" data-bs-target="#modalBeneficiarios"
                                                        title="Ver Beneficiarios">
                                                        <i class="bi bi-people"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary btn-editar-seguro"
                                                        data-id="<?= $seguro['id'] ?>"
                                                        data-titular-id="<?= $seguro['titular_id'] ?>"
                                                        data-monto-inicial="<?= $seguro['monto_inicial'] ?>"
                                                        data-saldo-actual="<?= $seguro['saldo_actual'] ?>"
                                                        data-metodo-pago="<?= htmlspecialchars($seguro['metodo_pago']) ?>"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditarSeguro"
                                                        title="Editar">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-dark btn-detalle-seguro"
                                                        data-id="<?= $seguro['id'] ?>" data-bs-toggle="modal"
                                                        data-bs-target="#modalDetalleSeguro" title="Ver Detalles">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <a href="api/eliminar_seguro.php?id=<?= $seguro['id'] ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('¿Eliminar este seguro y sus beneficiarios? Esta acción es irreversible.')"
                                                        title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
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



        <!-- =================== MODALES =================== -->
        <?php require("modals/modals_contabilidad.php"); ?>



    </div>
</div>

<?php

require("components/js_contabilidad.php"); /* Este archivo proporciona los script completos JS */

?>