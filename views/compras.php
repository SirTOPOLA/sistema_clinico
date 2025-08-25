<?php 
$idUsuario = $_SESSION['usuario']['id'] ?? 0;
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

// Función para formatear moneda XAF sin decimales y con punto de mil
function formatXAF($value) {
    return number_format(round($value), 0, '', '.'); // Aseguramos 0 decimales
}

// Consulta para compras a proveedores, uniendo con personal y proveedores
// Se añade un subquery para calcular el total esperado de venta por cada compra
$sqlCompras = "
    SELECT
        cp.id,
        cp.fecha_compra,
        cp.monto_total,
        cp.adelanto,
        cp.estado_pago,
        cp.fecha_registro,
        p.nombre AS nombre_proveedor,
        per.id AS id_personal_compra,
        per.nombre AS nombre_personal,
        per.apellidos AS apellidos_personal,
        (SELECT SUM(dcp.cantidad * dcp.precio_venta)
         FROM detalle_compra_proveedores AS dcp
         WHERE dcp.id_compra = cp.id) AS total_esperado_venta
    FROM
        compras_proveedores AS cp
    JOIN
        proveedores AS p ON cp.id_proveedor = p.id
    JOIN
        personal AS per ON cp.id_personal = per.id
    ORDER BY
        cp.fecha_registro DESC
";
$compras = $pdo->query($sqlCompras)->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener la lista de proveedores para los selects en los modales
$sqlProveedoresDropdown = "SELECT id, nombre FROM proveedores ORDER BY nombre ASC";
$proveedoresDropdown = $pdo->query($sqlProveedoresDropdown)->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener la lista de personal para los selects en los modales
$sqlPersonalDropdown = "SELECT id, nombre, apellidos FROM personal ORDER BY nombre ASC";
$personalDropdown = $pdo->query($sqlPersonalDropdown)->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener la lista de productos de farmacia para los selects en los modales
// Se redondean los valores a enteros desde PHP para consistencia con la interfaz.
// COALESCE se usa para que los NULLs se muestren como 0 en el frontend si no hay precio.
$sqlProductosFarmaciaDropdown = "SELECT id, nombre, 
    COALESCE(precio_caja, 0) AS precio_caja, 
    COALESCE(precio_frasco, 0) AS precio_frasco, 
    COALESCE(precio_tira, 0) AS precio_tira, 
    COALESCE(precio_pastilla, 0) AS precio_pastilla, 
    COALESCE(tiras_por_caja, 0) AS tiras_por_caja, 
    COALESCE(pastillas_por_tira, 0) AS pastillas_por_tira,
    COALESCE(pastillas_por_frasco, 0) AS pastillas_por_frasco
    FROM productos_farmacia ORDER BY nombre ASC";
$productosFarmaciaDropdown = $pdo->query($sqlProductosFarmaciaDropdown)->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="content" class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0"><i class="bi bi-cart-fill me-2"></i>Listado de Compras a Proveedores</h3>
            <button class="btn btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCrearCompra">
                <i class="bi bi-plus-circle-fill me-1"></i> Nueva Compra
            </button>
        </div>
        <div class="col-md-4">
            <input type="text" id="buscadorCompras" class="form-control" placeholder="Buscar compra...">
        </div>
    </div>

    <?php if (isset($_SESSION['success_compra'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_compra']; unset($_SESSION['success_compra']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_compra'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_compra']; unset($_SESSION['error_compra']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaCompras" class="table table-hover table-bordered align-middle table-sm">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th>ID Compra</th>
                            <th>Proveedor</th>
                            <th>Realizada por</th>
                            <th>Fecha Compra</th>
                            <th>Monto Total (Compra)</th>
                            <th>Adelanto</th>
                            <th>Neto (Compra)</th>
                            <th>Total Esperado (Venta)</th>
                            <th>Beneficios</th>
                            <th>Estado Pago</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $compra): ?>
                            <?php
                            $monto_total_val = $compra['monto_total'] ?? 0;
                            $adelanto_val = $compra['adelanto'] ?? 0;
                            $neto = $monto_total_val - $adelanto_val;
                            $claseColorNeto = '';
                            if ($neto > 0) {
                                $claseColorNeto = 'text-danger fw-bold'; // Deuda (pendiente de pago)
                            } elseif ($neto < 0) {
                                $claseColorNeto = 'text-success fw-bold'; // Crédito / Sobrepago
                            }

                            $totalEsperadoVenta = $compra['total_esperado_venta'] ?? 0;
                            $beneficios = $totalEsperadoVenta - $monto_total_val;
                            $claseColorBeneficios = '';
                            if ($beneficios > 0) {
                                $claseColorBeneficios = 'text-success fw-bold'; // Ganancia
                            } elseif ($beneficios < 0) {
                                $claseColorBeneficios = 'text-danger fw-bold'; // Pérdida
                            }
                            ?>
                            <tr>
                                <td><?= (int)$compra['id'] ?></td>
                                <td><?= htmlspecialchars($compra['nombre_proveedor']) ?></td>
                                <td><?= htmlspecialchars($compra['nombre_personal'] . ' ' . $compra['apellidos_personal']) ?></td>
                                <td><?= date('d/m/Y', strtotime($compra['fecha_compra'])) ?></td>
                                <td>XAF <?= formatXAF($monto_total_val) ?></td>
                                <td>XAF <?= formatXAF($adelanto_val) ?></td>
                                <td class="<?= $claseColorNeto ?>">
                                    XAF <?= formatXAF($neto) ?>
                                </td>
                                <td>XAF <?= formatXAF($totalEsperadoVenta) ?></td>
                                <td class="<?= $claseColorBeneficios ?>">
                                    XAF <?= formatXAF($beneficios) ?>
                                </td>
                                <td><?= htmlspecialchars($compra['estado_pago']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($compra['fecha_registro'])) ?></td>
                                <td class="text-nowrap">
                                    <button
                                        class="btn btn-sm btn-outline-info me-1 ver-detalles-btn"
                                        title="Ver Detalles"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalVerDetallesCompra"
                                        data-id="<?= $compra['id'] ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button
                                        class="btn btn-sm btn-outline-primary me-1 editar-compra-btn"
                                        title="Editar Compra"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditarCompra"
                                        data-id="<?= $compra['id'] ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <?php if($rol === 'administrador'): ?>
                                    <a href="api/compras_crud.php?action=eliminar&id=<?= $compra['id'] ?>" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Deseas eliminar esta compra? Esta acción es irreversible.')" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </a>
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

<!-- Modal para Crear Compra -->
<div class="modal fade" id="modalCrearCompra" tabindex="-1" aria-labelledby="modalCrearCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title" id="modalCrearCompraLabel"><i class="bi bi-plus-circle me-2"></i>Registrar Nueva Compra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCrearCompra" action="api/compras_crud.php?action=crear" method="POST">
                <div class="modal-body p-4">
                    <h6 class="mb-3 text-primary">Detalles de la Compra Principal</h6>
                    <div class="mb-3">
                        <label for="id_proveedor" class="form-label">Proveedor <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_proveedor" name="id_proveedor" required>
                            <option value="">Seleccione un proveedor</option>
                            <?php foreach ($proveedoresDropdown as $prov): ?>
                                <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="id_personal_crear" class="form-label">Realizada por <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_personal_crear" name="id_personal" required>
                            <option value="">Seleccione el personal</option>
                            <?php foreach ($personalDropdown as $per): ?>
                                <option value="<?= $per['id'] ?>" <?= ($per['id'] == $idUsuario) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($per['nombre'] . ' ' . $per['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_compra" class="form-label">Fecha de Compra <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="fecha_compra" name="fecha_compra" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="monto_total_display" class="form-label">Monto Total <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">XAF</span>
                            <input type="text" class="form-control" id="monto_total_display" value="0" readonly required style="background-color: #e9ecef;">
                            <input type="hidden" id="monto_total_raw" name="monto_total" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="adelanto" class="form-label">Adelanto</label>
                        <div class="input-group">
                            <span class="input-group-text">XAF</span>
                            <input type="number" class="form-control" id="adelanto" name="adelanto" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="estado_pago" class="form-label">Estado de Pago</label>
                        <select class="form-select" id="estado_pago" name="estado_pago">
                            <option value="pendiente">Pendiente</option>
                            <option value="pagado">Pagado</option>
                            <option value="parcial">Parcial</option>
                        </select>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3 text-primary">Detalles de Productos Adquiridos</h6>
                    
                    <div id="productos-dinamicos-container">
                        <!-- Aquí se agregarán dinámicamente los campos de producto -->
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm mt-3 w-100 py-2" id="agregarProductoBtn">
                        <i class="bi bi-plus-circle me-1"></i> Agregar Otro Producto
                    </button>

                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Compra</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template para un nuevo producto en la compra (OCULTO) -->
<template id="producto-compra-template">
    <div class="producto-row border p-3 mb-3 rounded-3 bg-light shadow-sm">
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-danger btn-sm eliminar-producto-btn rounded-pill">
                <i class="bi bi-x-circle"></i> Quitar
            </button>
        </div>
        <div class="mb-3">
            <label class="form-label small">Producto <span class="text-danger">*</span></label>
            <select class="form-select producto-select" name="productos[INDEX][id_producto]" required>
                <option value="">Seleccione un producto</option>
                <?php foreach ($productosFarmaciaDropdown as $prod): ?>
                    <option value="<?= $prod['id'] ?>" 
                            data-precio-caja="<?= $prod['precio_caja'] ?>"
                            data-precio-frasco="<?= $prod['precio_frasco'] ?>"
                            data-precio-tira="<?= $prod['precio_tira'] ?>"
                            data-precio-pastilla="<?= $prod['precio_pastilla'] ?>"
                            data-tiras-por-caja="<?= $prod['tiras_por_caja'] ?>"
                            data-pastillas-por-tira="<?= $prod['pastillas_por_tira'] ?>"
                            data-pastillas-por-frasco="<?= $prod['pastillas_por_frasco'] ?>">
                        <?= htmlspecialchars($prod['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small">Cantidad <span class="text-danger">*</span></label>
                <input type="number" class="form-control cantidad-input" name="productos[INDEX][cantidad]" min="1" value="1" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small">Unidad <span class="text-danger">*</span></label>
                <select class="form-select unidad-select" name="productos[INDEX][unidad]" required>
                    <option value="">Seleccione unidad</option>
                    <option value="caja">Caja</option>
                    <option value="frasco">Frasco</option>
                    <option value="tira">Tira</option>
                    <option value="pastilla">Pastilla</option>
                </select>
                <small class="form-text text-muted unidad-info"></small> <!-- Para mostrar info de la unidad -->
            </div>
        </div>

        <!-- Nuevos campos para las unidades contenidas (con contenedores para mostrar/ocultar) -->
        <h6 class="mt-2 text-primary small">Unidades Contenidas por Unidad Comprada</h6>
        <div class="row">
            <!-- Tiras por Caja (cantidad y su precio de venta) -->
            <div class="col-md-6 mb-3 tiras-por-caja-container">
                <label class="form-label small">Tiras por Caja</label>
                <input type="number" class="form-control tiras-por-caja-input" name="productos[INDEX][tiras_por_caja]" min="0" value="0">
            </div>
            <div class="col-md-6 mb-3 precio-venta-tira-sub-container">
                <label class="form-label small">P. Venta por Tira</label>
                <div class="input-group">
                    <span class="input-group-text">XAF</span>
                    <input type="number" class="form-control precio-venta-tira-sub-input" name="productos[INDEX][precio_venta_tira_sub]" value="0">
                </div>
            </div>

            <!-- Pastillas por Tira (cantidad y su precio de venta) -->
            <div class="col-md-6 mb-3 pastillas-por-tira-container">
                <label class="form-label small">Pastillas por Tira</label>
                <input type="number" class="form-control pastillas-por-tira-input" name="productos[INDEX][pastillas_por_tira]" min="0" value="0">
            </div>
            <div class="col-md-6 mb-3 precio-venta-pastilla-sub-tira-container">
                <label class="form-label small">P. Venta por Pastilla (Tira)</label>
                <div class="input-group">
                    <span class="input-group-text">XAF</span>
                    <input type="number" class="form-control precio-venta-pastilla-sub-tira-input" name="productos[INDEX][precio_venta_pastilla_sub_tira]" value="0">
                </div>
            </div>

            <!-- Pastillas por Frasco (cantidad y su precio de venta) -->
            <div class="col-md-6 mb-3 pastillas-por-frasco-container">
                <label class="form-label small">Pastillas por Frasco</label>
                <input type="number" class="form-control pastillas-por-frasco-input" name="productos[INDEX][pastillas_por_frasco]" min="0" value="0">
            </div>
            <div class="col-md-6 mb-3 precio-venta-pastilla-sub-frasco-container">
                <label class="form-label small">P. Venta por Pastilla (Frasco)</label>
                <div class="input-group">
                    <span class="input-group-text">XAF</span>
                    <input type="number" class="form-control precio-venta-pastilla-sub-frasco-input" name="productos[INDEX][precio_venta_pastilla_sub_frasco]" value="0">
                </div>
            </div>
        </div>
        
        <!-- Campo para la fecha de vencimiento -->
        <div class="mb-3">
            <label class="form-label small">Fecha de Vencimiento</label>
            <input type="date" class="form-control fecha-vencimiento-input" name="productos[INDEX][fecha_vencimiento]">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small">Precio Unitario (Compra) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">XAF</span>
                    <input type="number" class="form-control precio-unitario-input" name="productos[INDEX][precio_unitario]" value="0" required>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small">Precio Venta (<span class="unidad-precio-label">Sugerido</span>) <span class="text-danger">*</span></label> <!-- LABEL DINÁMICO -->
                <div class="input-group">
                    <span class="input-group-text">XAF</span>
                    <input type="number" class="form-control precio-venta-input" name="productos[INDEX][precio_venta]" value="0" required>
                </div>
            </div>
        </div>
        <div class="text-end fw-bold mt-2">
            Total por Producto: <span class="total-por-producto">XAF 0</span>
        </div>
        <input type="hidden" class="detalle-id-input" name="productos[INDEX][id_detalle]" value="">
    </div>
</template>

<!-- Modal para Editar Compra (Ahora es dinámico para productos) -->
<div class="modal fade" id="modalEditarCompra" tabindex="-1" aria-labelledby="modalEditarCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title" id="modalEditarCompraLabel"><i class="bi bi-pencil-square me-2"></i>Editar Compra #<span id="edit-compra-id-display"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarCompra" action="api/compras_crud.php?action=editar" method="POST">
                <input type="hidden" id="edit-compra-id" name="id">
                <div class="modal-body p-4">
                    <h6 class="mb-3 text-primary">Detalles de la Compra Principal</h6>
                    <div class="mb-3">
                        <label for="edit-id_proveedor" class="form-label">Proveedor <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit-id_proveedor" name="id_proveedor" required>
                            <option value="">Seleccione un proveedor</option>
                            <?php foreach ($proveedoresDropdown as $prov): ?>
                                <option value="<?= $prov['id'] ?>"><?= htmlspecialchars($prov['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-id_personal" class="form-label">Realizada por <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit-id_personal" name="id_personal" required>
                            <option value="">Seleccione el personal</option>
                            <?php foreach ($personalDropdown as $per): ?>
                                <option value="<?= $per['id'] ?>">
                                    <?= htmlspecialchars($per['nombre'] . ' ' . $per['apellidos']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-fecha_compra" class="form-label">Fecha de Compra <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit-fecha_compra" name="fecha_compra" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-monto_total_display" class="form-label">Monto Total <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">XAF</span>
                            <input type="text" class="form-control" id="edit-monto_total_display" value="0" readonly required style="background-color: #e9ecef;">
                            <input type="hidden" id="edit-monto_total_raw" name="monto_total" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-adelanto" class="form-label">Adelanto</label>
                        <div class="input-group">
                            <span class="input-group-text">XAF</span>
                            <input type="number" class="form-control" id="edit-adelanto" name="adelanto" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit-estado_pago" class="form-label">Estado de Pago</label>
                        <select class="form-select" id="edit-estado_pago" name="estado_pago">
                            <option value="pendiente">Pendiente</option>
                            <option value="pagado">Pagado</option>
                            <option value="parcial">Parcial</option>
                        </select>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3 text-primary">Detalles de Productos Adquiridos</h6>
                    
                    <div id="productos-dinamicos-container-edit">
                        <!-- Aquí se agregarán dinámicamente los campos de producto para edición -->
                    </div>

                    <button type="button" class="btn btn-outline-secondary btn-sm mt-3 w-100 py-2" id="agregarProductoEditBtn">
                        <i class="bi bi-plus-circle me-1"></i> Agregar Otro Producto
                    </button>

                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Ver Detalles de Compra (Incluye productos) -->
<div class="modal fade" id="modalVerDetallesCompra" tabindex="-1" aria-labelledby="modalVerDetallesCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg rounded-4">
            <div class="modal-header bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-top-4">
                <h5 class="modal-title text-xl font-bold" id="modalVerDetallesCompraLabel"><i class="bi bi-receipt me-2"></i>Factura de Compra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <div class="text-center mb-6 border-b pb-4">
                        <h2 class="text-3xl font-bold text-gray-800 mb-1">Detalles de Compra</h2>
                        <p class="text-gray-600">Referencia #<span id="detalle-compra-id-display" class="font-bold text-blue-700"></span></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-700 mb-3">Información del Proveedor</h6>
                            <p class="text-gray-600"><strong>Nombre:</strong> <span id="detalle-proveedor" class="font-medium text-gray-800"></span></p>
                        </div>
                        <div>
                            <h6 class="text-lg font-semibold text-gray-700 mb-3">Información de la Compra</h6>
                            <p class="text-gray-600"><strong>Realizada por:</strong> <span id="detalle-personal" class="font-medium text-gray-800"></span></p>
                            <p class="text-gray-600"><strong>Fecha de Compra:</strong> <span id="detalle-fecha-compra" class="font-medium text-gray-800"></span></p>
                            <p class="text-gray-600"><strong>Fecha de Registro:</strong> <span id="detalle-fecha-registro" class="font-medium text-gray-800"></span></p>
                        </div>
                    </div>

                    <h6 class="text-lg font-semibold text-gray-700 mb-4">Productos Adquiridos</h6>
                    <div class="overflow-x-auto mb-8 rounded-lg border border-gray-200">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100 border-b border-gray-200">
                                <tr>
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Producto</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Cant.</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Unidad</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">P. Unitario (C)</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">P. Venta (S)</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">T/C</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">P/T</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">P/F</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">F. Venc.</th>
                                    <th class="py-3 px-4 text-left text-sm font-semibold text-gray-700">Total</th>
                                </tr>
                            </thead>
                            <tbody id="detalle-productos-container">
                                <!-- Los detalles de los productos se cargarán aquí -->
                            </tbody>
                        </table>
                    </div>

                    <div class="flex flex-col md:flex-row justify-end gap-4 text-right">
                        <div class="w-full md:w-1/2 lg:w-1/3 p-4 bg-gray-50 rounded-lg shadow-inner">
                            <p class="text-lg font-semibold text-gray-700 mb-2 border-b pb-2">Resumen de Pago</p>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600">Monto Total Compra:</span>
                                <span id="detalle-monto-total-summary" class="font-bold text-gray-800"></span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600">Adelanto:</span>
                                <span id="detalle-adelanto-summary" class="font-bold text-gray-800"></span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-gray-600">Estado de Pago:</span>
                                <span id="detalle-estado-pago-summary" class="font-bold text-blue-600"></span>
                            </div>
                            <div class="flex justify-between items-center py-2 mt-2 border-t-2 border-gray-300">
                                <span class="text-xl font-bold text-gray-800">Neto Pendiente:</span>
                                <span id="detalle-neto-pendiente" class="text-xl font-bold text-red-600"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para formatear moneda XAF sin decimales y con punto de mil
    const formatXAF = (value) => {
        return new Intl.NumberFormat('es-GQ', {
            style: 'currency',
            currency: 'XAF',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(Math.round(value)); // Aseguramos que siempre se redondee para 0 decimales
    };

    // Script para el buscador de compras
    const buscadorCompras = document.getElementById('buscadorCompras');
    const tablaCompras = document.getElementById('tablaCompras');
    if (buscadorCompras && tablaCompras) {
        buscadorCompras.addEventListener('keyup', function() {
            const value = this.value.toLowerCase();
            const rows = tablaCompras.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            for (let i = 0; i < rows.length; i++) {
                const rowText = rows[i].textContent.toLowerCase();
                rows[i].style.display = rowText.includes(value) ? '' : 'none';
            }
        });
    }

    let productoIndexCrear = 0; // Índice para productos en el modal de crear
    let productoIndexEditar = 0; // Índice para productos en el modal de editar

    const productoTemplate = document.getElementById('producto-compra-template');
    // Obtenemos los datos directamente del PHP, ya con COALESCE para mostrar 0 en vez de null
    const productosFarmaciaDropdownData = <?= json_encode($productosFarmaciaDropdown); ?>;

    /**
     * Genera HTML para las opciones del select de productos.
     * @param {number} selectedProductId - El ID del producto que debe estar seleccionado.
     * @returns {string} HTML de las opciones.
     */
    function generateProductOptions(selectedProductId = null) {
        let options = '<option value="">Seleccione un producto</option>';
        productosFarmaciaDropdownData.forEach(prod => {
            const selected = (prod.id == selectedProductId) ? 'selected' : '';
            options += `
                <option value="${prod.id}" ${selected}
                        data-precio-caja="${prod.precio_caja}"
                        data-precio-frasco="${prod.precio_frasco}"
                        data-precio-tira="${prod.precio_tira}"
                        data-precio-pastilla="${prod.precio_pastilla}"
                        data-tiras-por-caja="${prod.tiras_por_caja}"
                        data-pastillas-por-tira="${prod.pastillas_por_tira}"
                        data-pastillas-por-frasco="${prod.pastillas_por_frasco}">
                    ${prod.nombre}
                </option>
            `;
        });
        return options;
    }

    /**
     * Muestra/Oculta campos de unidades contenidas y actualiza precios sugeridos.
     * @param {HTMLElement} productRow - La fila de producto.
     */
    function updateSuggestedProductDetails(productRow) {
        const selectedProductOption = productRow.querySelector('.producto-select option:checked');
        const selectedUnidad = productRow.querySelector('.unidad-select').value;

        const precioVentaInput = productRow.querySelector('.precio-venta-input');
        const unidadPrecioLabel = productRow.querySelector('.unidad-precio-label');
        
        // Campos de unidades contenidas
        const tirasPorCajaInput = productRow.querySelector('.tiras-por-caja-input');
        const pastillasPorTiraInput = productRow.querySelector('.pastillas-por-tira-input');
        const pastillasPorFrascoInput = productRow.querySelector('.pastillas-por-frasco-input');

        // Contenedores de precios por sub-unidad
        const precioVentaTiraSubInput = productRow.querySelector('.precio-venta-tira-sub-input');
        const precioVentaPastillaSubTiraInput = productRow.querySelector('.precio-venta-pastilla-sub-tira-input');
        const precioVentaPastillaSubFrascoInput = productRow.querySelector('.precio-venta-pastilla-sub-frasco-input');

        // Contenedores de visibilidad
        const tirasPorCajaContainer = productRow.querySelector('.tiras-por-caja-container');
        const precioVentaTiraSubContainer = productRow.querySelector('.precio-venta-tira-sub-container');
        const pastillasPorTiraContainer = productRow.querySelector('.pastillas-por-tira-container');
        const precioVentaPastillaSubTiraContainer = productRow.querySelector('.precio-venta-pastilla-sub-tira-container');
        const pastillasPorFrascoContainer = productRow.querySelector('.pastillas-por-frasco-container');
        const precioVentaPastillaSubFrascoContainer = productRow.querySelector('.precio-venta-pastilla-sub-frasco-container');

        // Ocultar todos por defecto
        tirasPorCajaContainer.style.display = 'none';
        precioVentaTiraSubContainer.style.display = 'none';
        pastillasPorTiraContainer.style.display = 'none';
        precioVentaPastillaSubTiraContainer.style.display = 'none';
        pastillasPorFrascoContainer.style.display = 'none';
        precioVentaPastillaSubFrascoContainer.style.display = 'none';

        if (selectedProductOption && selectedProductOption.value) {
            let suggestedPrice = 0;
            let precioTiraProducto = parseFloat(selectedProductOption.dataset.precioTira) || 0;
            let precioPastillaProducto = parseFloat(selectedProductOption.dataset.precioPastilla) || 0;

            // Actualizar campos de unidades contenidas y sus precios específicos
            tirasPorCajaInput.value = parseFloat(selectedProductOption.dataset.tirasPorCaja) || 0;
            pastillasPorTiraInput.value = parseFloat(selectedProductOption.dataset.pastillasPorTira) || 0;
            pastillasPorFrascoInput.value = parseFloat(selectedProductOption.dataset.pastillasPorFrasco) || 0;
            
            // Establecer precios de venta por sub-unidad con los valores del producto
            precioVentaTiraSubInput.value = precioTiraProducto;
            precioVentaPastillaSubTiraInput.value = precioPastillaProducto;
            precioVentaPastillaSubFrascoInput.value = precioPastillaProducto; // Ambos se actualizan desde precio_pastilla

            switch (selectedUnidad) {
                case 'caja':
                    suggestedPrice = parseFloat(selectedProductOption.dataset.precioCaja) || 0;
                    unidadPrecioLabel.textContent = 'Caja';
                    // Si se compra una caja, podría contener tiras y pastillas
                    tirasPorCajaContainer.style.display = 'block';
                    precioVentaTiraSubContainer.style.display = 'block';
                    pastillasPorTiraContainer.style.display = 'block';
                    precioVentaPastillaSubTiraContainer.style.display = 'block';
                    pastillasPorFrascoContainer.style.display = 'block';
                    precioVentaPastillaSubFrascoContainer.style.display = 'block';
                    break;
                case 'frasco':
                    suggestedPrice = parseFloat(selectedProductOption.dataset.precioFrasco) || 0;
                    unidadPrecioLabel.textContent = 'Frasco';
                    // Si se compra un frasco, podría contener pastillas
                    pastillasPorFrascoContainer.style.display = 'block';
                    precioVentaPastillaSubFrascoContainer.style.display = 'block';
                    break;
                case 'tira':
                    suggestedPrice = parseFloat(selectedProductOption.dataset.precioTira) || 0;
                    unidadPrecioLabel.textContent = 'Tira';
                    // Si se compra una tira, podría contener pastillas
                    pastillasPorTiraContainer.style.display = 'block';
                    precioVentaPastillaSubTiraContainer.style.display = 'block';
                    break;
                case 'pastilla':
                    suggestedPrice = parseFloat(selectedProductOption.dataset.precioPastilla) || 0;
                    unidadPrecioLabel.textContent = 'Pastilla';
                    break;
                default:
                    unidadPrecioLabel.textContent = 'Sugerido';
                    break;
            }
            precioVentaInput.value = suggestedPrice;
        } else {
            // Resetear campos si no hay producto seleccionado
            precioVentaInput.value = 0;
            unidadPrecioLabel.textContent = 'Sugerido';
            tirasPorCajaInput.value = 0;
            pastillasPorTiraInput.value = 0;
            pastillasPorFrascoInput.value = 0;
            precioVentaTiraSubInput.value = 0;
            precioVentaPastillaSubTiraInput.value = 0;
            precioVentaPastillaSubFrascoInput.value = 0;
        }
        updateCalculatedTotals(productRow, calculateTotalCompraMonto); // Recalcular total del producto y compra
    }

    /**
     * Calcula y actualiza el total por producto y el monto total de la compra.
     * @param {HTMLElement} productRow - La fila de producto a actualizar.
     * @param {function} calculateTotalCompraFn - Función que recalcula el monto total de la compra.
     */
    function updateCalculatedTotals(productRow, calculateTotalCompraFn) {
        const cantidad = parseFloat(productRow.querySelector('.cantidad-input').value) || 0;
        const precioUnitario = parseFloat(productRow.querySelector('.precio-unitario-input').value) || 0;
        const totalPorProductoSpan = productRow.querySelector('.total-por-producto');
        const total = cantidad * precioUnitario;
        totalPorProductoSpan.textContent = formatXAF(total);
        calculateTotalCompraFn();
    }

    /**
     * Recalcula el monto total de la compra sumando todos los totales por producto.
     * @param {string} containerId - ID del contenedor de productos ('productos-dinamicos-container' o 'productos-dinamicos-container-edit').
     * @param {string} montoTotalDisplayId - ID del input de display del monto total.
     * @param {string} montoTotalRawId - ID del input hidden del monto total.
     */
    function calculateTotalCompraMonto(containerId = 'productos-dinamicos-container', montoTotalDisplayId = 'monto_total_display', montoTotalRawId = 'monto_total_raw') {
        let totalCompra = 0;
        const productRows = document.getElementById(containerId).querySelectorAll('.producto-row');
        productRows.forEach(row => {
            const cantidad = parseFloat(row.querySelector('.cantidad-input').value) || 0;
            const precioUnitario = parseFloat(row.querySelector('.precio-unitario-input').value) || 0;
            totalCompra += (cantidad * precioUnitario);
        });
        document.getElementById(montoTotalDisplayId).value = formatXAF(totalCompra);
        document.getElementById(montoTotalRawId).value = totalCompra;
    }

    /**
     * Agrega una nueva fila de producto al modal.
     * @param {string} containerId - ID del contenedor de productos.
     * @param {number} productIndex - Índice actual del producto.
     * @param {function} calculateTotalFn - Función para recalcular el total de la compra.
     * @param {Object} [initialData={}] - Datos iniciales para precargar la fila (para edición).
     * @returns {HTMLElement} La nueva fila de producto.
     */
    function addProductRow(containerId, productIndex, calculateTotalFn, initialData = {}) {
        const templateContent = productoTemplate.content.cloneNode(true);
        const newRow = templateContent.firstElementChild;

        // Reemplazar INDEX en los atributos name
        newRow.innerHTML = newRow.innerHTML.replace(/INDEX/g, productIndex);

        const newSelectProducto = newRow.querySelector('.producto-select');
        newSelectProducto.innerHTML = generateProductOptions(initialData.id_producto);

        // Si hay datos iniciales, precargar los campos
        if (Object.keys(initialData).length > 0) {
            newRow.querySelector('.detalle-id-input').value = initialData.id_detalle || '';
            newRow.querySelector('.cantidad-input').value = initialData.cantidad || 1;
            newRow.querySelector('.unidad-select').value = initialData.unidad || '';
            newRow.querySelector('.precio-unitario-input').value = initialData.precio_unitario || 0;
            newRow.querySelector('.precio-venta-input').value = initialData.precio_venta || 0;
            
            // Campos de unidades contenidas y precios por sub-unidad
            newRow.querySelector('.tiras-por-caja-input').value = initialData.tiras_por_caja || 0;
            newRow.querySelector('.pastillas-por-tira-input').value = initialData.pastillas_por_tira || 0;
            newRow.querySelector('.pastillas-por-frasco-input').value = initialData.pastillas_por_frasco || 0;

            // Asegurarse de que los precios de sub-unidad se precarguen correctamente
            // desde los datos del producto (que vienen en initialData)
            const selectedProductFromDropdown = productosFarmaciaDropdownData.find(p => p.id == initialData.id_producto);
            if(selectedProductFromDropdown) {
                newRow.querySelector('.precio-venta-tira-sub-input').value = selectedProductFromDropdown.precio_tira || 0;
                newRow.querySelector('.precio-venta-pastilla-sub-tira-input').value = selectedProductFromDropdown.precio_pastilla || 0;
                newRow.querySelector('.precio-venta-pastilla-sub-frasco-input').value = selectedProductFromDropdown.precio_pastilla || 0;
            } else {
                 newRow.querySelector('.precio-venta-tira-sub-input').value = 0;
                 newRow.querySelector('.precio-venta-pastilla-sub-tira-input').value = 0;
                 newRow.querySelector('.precio-venta-pastilla-sub-frasco-input').value = 0;
            }

            // Fecha de vencimiento
            newRow.querySelector('.fecha-vencimiento-input').value = initialData.fecha_vencimiento || '';

            // Inicializar la vista de detalles del producto con los datos precargados
            // para que se muestren/oculten correctamente los contenedores de sub-unidades
            // y se establezca el label de precio de venta.
            // Primero, aseguramos que el select de producto esté establecido antes de llamar a updateSuggestedProductDetails
            setTimeout(() => { // Pequeño delay para asegurar que el DOM se actualice
                newSelectProducto.value = initialData.id_producto;
                newRow.querySelector('.unidad-select').value = initialData.unidad;
                updateSuggestedProductDetails(newRow);
            }, 0);
        } else {
            // Para nuevas filas, selecciona la primera opción por defecto si no hay un "Seleccione un producto"
            if (newSelectProducto.options.length > 1 && newSelectProducto.value === "") {
                newSelectProducto.value = newSelectProducto.options[1].value;
            }
            // Seleccionar "caja" como unidad por defecto para nuevas filas
            newRow.querySelector('.unidad-select').value = 'caja';
            updateSuggestedProductDetails(newRow);
        }

        document.getElementById(containerId).appendChild(newRow);
        attachProductRowListeners(newRow, calculateTotalFn);

        return newRow;
    }


    /**
     * Adjunta listeners a los elementos de una fila de producto.
     * @param {HTMLElement} newRow - La fila de producto a la que adjuntar listeners.
     * @param {function} calculateTotalFn - Función para recalcular el total de la compra.
     */
    function attachProductRowListeners(newRow, calculateTotalFn) {
        const newSelectProducto = newRow.querySelector('.producto-select');
        const newSelectUnidad = newRow.querySelector('.unidad-select');
        const newCantidadInput = newRow.querySelector('.cantidad-input');
        const newPrecioUnitarioInput = newRow.querySelector('.precio-unitario-input');
        const newPrecioVentaInput = newRow.querySelector('.precio-venta-input'); 
        const newRemoveBtn = newRow.querySelector('.eliminar-producto-btn');

        // Nuevos campos de precio de venta por sub-unidad
        const newPrecioVentaTiraSubInput = newRow.querySelector('.precio-venta-tira-sub-input');
        const newPrecioVentaPastillaSubTiraInput = newRow.querySelector('.precio-venta-pastilla-sub-tira-input');
        const newPrecioVentaPastillaSubFrascoInput = newRow.querySelector('.precio-venta-pastilla-sub-frasco-input');

        // Campos de unidades contenidas
        const tirasPorCajaInput = newRow.querySelector('.tiras-por-caja-input');
        const pastillasPorTiraInput = newRow.querySelector('.pastillas-por-tira-input');
        const pastillasPorFrascoInput = newRow.querySelector('.pastillas-por-frasco-input');

        // Listeners para actualizar valores sugeridos y visibilidad (solo al cambiar producto/unidad)
        if (newSelectProducto) {
            newSelectProducto.addEventListener('change', () => updateSuggestedProductDetails(newRow));
        }
        if (newSelectUnidad) {
            newSelectUnidad.addEventListener('change', () => updateSuggestedProductDetails(newRow));
        }

        // Listeners para actualizar totales calculados (cantidad, precio unitario de compra)
        if (newCantidadInput) {
            newCantidadInput.addEventListener('input', () => updateCalculatedTotals(newRow, calculateTotalFn));
        }
        if (newPrecioUnitarioInput) {
            newPrecioUnitarioInput.addEventListener('input', () => updateCalculatedTotals(newRow, calculateTotalFn));
        }
        // No necesitamos listeners para los precios de venta ya que solo son sugeridos o inputs manuales
        // y no afectan el `monto_total` de la compra. Solo se validan al enviar el formulario.

        // Listeners para campos de unidades contenidas (su valor puede cambiar y afectar visualmente)
        if (tirasPorCajaInput) { tirasPorCajaInput.addEventListener('input', () => updateCalculatedTotals(newRow, calculateTotalFn)); }
        if (pastillasPorTiraInput) { pastillasPorTiraInput.addEventListener('input', () => updateCalculatedTotals(newRow, calculateTotalFn)); }
        if (pastillasPorFrascoInput) { pastillasPorFrascoInput.addEventListener('input', () => updateCalculatedTotals(newRow, calculateTotalFn)); }

        if (newRemoveBtn) {
            newRemoveBtn.addEventListener('click', function() {
                newRow.remove();
                calculateTotalFn(); // Recalcular total después de eliminar
            });
        }
    }


    // Lógica para el modal de CREAR COMPRA
    document.getElementById('agregarProductoBtn').addEventListener('click', function() {
        addProductRow('productos-dinamicos-container', productoIndexCrear++, () => calculateTotalCompraMonto('productos-dinamicos-container', 'monto_total_display', 'monto_total_raw'));
    });

    // Asegurarse de que se agregue al menos una fila al abrir el modal de crear por primera vez
    const modalCrearCompra = document.getElementById('modalCrearCompra');
    if (modalCrearCompra) {
        modalCrearCompra.addEventListener('show.bs.modal', function() {
            if (document.getElementById('productos-dinamicos-container').children.length === 0) {
                productoIndexCrear = 0; // Resetear índice al abrir el modal
                addProductRow('productos-dinamicos-container', productoIndexCrear++, () => calculateTotalCompraMonto('productos-dinamicos-container', 'monto_total_display', 'monto_total_raw'));
            }
            calculateTotalCompraMonto('productos-dinamicos-container', 'monto_total_display', 'monto_total_raw'); // Calcular al abrir
        });

        // Limpiar productos al cerrar el modal de crear
        modalCrearCompra.addEventListener('hidden.bs.modal', function() {
            document.getElementById('productos-dinamicos-container').innerHTML = '';
            document.getElementById('monto_total_display').value = formatXAF(0);
            document.getElementById('monto_total_raw').value = 0;
            document.getElementById('adelanto').value = 0;
            document.getElementById('id_proveedor').value = '';
            document.getElementById('id_personal_crear').value = <?= json_encode($idUsuario); ?>; // Reset a usuario actual
            document.getElementById('fecha_compra').value = '<?= date('Y-m-d'); ?>';
            document.getElementById('estado_pago').value = 'pendiente';
        });

        // Listener para adelanto en el modal de Crear
        document.getElementById('adelanto').addEventListener('input', () => calculateTotalCompraMonto('productos-dinamicos-container', 'monto_total_display', 'monto_total_raw'));
    }


    // Lógica para el modal de EDITAR COMPRA
    document.getElementById('agregarProductoEditBtn').addEventListener('click', function() {
        addProductRow('productos-dinamicos-container-edit', productoIndexEditar++, () => calculateTotalCompraMonto('productos-dinamicos-container-edit', 'edit-monto_total_display', 'edit-monto_total_raw'));
    });

    const modalEditarCompra = document.getElementById('modalEditarCompra');
    if (modalEditarCompra) {
        modalEditarCompra.addEventListener('show.bs.modal', async function(event) {
            const button = event.relatedTarget;
            const compraId = button.dataset.id;
            document.getElementById('edit-compra-id').value = compraId;
            document.getElementById('edit-compra-id-display').textContent = compraId;

            // Limpiar productos anteriores antes de cargar nuevos
            const productosContainerEdit = document.getElementById('productos-dinamicos-container-edit');
            productosContainerEdit.innerHTML = '';
            productoIndexEditar = 0; // Resetear el índice para los productos de edición

            // Cargar datos de la compra para edición
            try {
                const response = await fetch(`api/detalles_compra.php?id=${compraId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                if (data.compra) {
                    document.getElementById('edit-id_proveedor').value = data.compra.id_proveedor;
                    document.getElementById('edit-id_personal').value = data.compra.id_personal;
                    document.getElementById('edit-fecha_compra').value = data.compra.fecha_compra;
                    document.getElementById('edit-adelanto').value = data.compra.adelanto;
                    document.getElementById('edit-estado_pago').value = data.compra.estado_pago;
                    // Monto total se calcula desde los productos, así que se actualizará al cargarlos
                }

                if (data.productos && data.productos.length > 0) {
                    data.productos.forEach(prod => {
                        addProductRow('productos-dinamicos-container-edit', productoIndexEditar++, () => calculateTotalCompraMonto('productos-dinamicos-container-edit', 'edit-monto_total_display', 'edit-monto_total_raw'), prod);
                    });
                } else {
                     // Si no hay productos, agregar una fila vacía para que el usuario pueda añadir
                     addProductRow('productos-dinamicos-container-edit', productoIndexEditar++, () => calculateTotalCompraMonto('productos-dinamicos-container-edit', 'edit-monto_total_display', 'edit-monto_total_raw'));
                }
                calculateTotalCompraMonto('productos-dinamicos-container-edit', 'edit-monto_total_display', 'edit-monto_total_raw'); // Calcular al cargar
            } catch (error) {
                console.error('Error al cargar datos de la compra para edición:', error);
                alert('No se pudieron cargar los detalles de la compra para edición. Intente de nuevo.');
                modalEditarCompra.hide();
            }
        });

        // Limpiar el modal de edición al cerrarlo
        modalEditarCompra.addEventListener('hidden.bs.modal', function() {
            document.getElementById('productos-dinamicos-container-edit').innerHTML = '';
            document.getElementById('edit-compra-id').value = '';
            document.getElementById('edit-compra-id-display').textContent = '';
            document.getElementById('edit-monto_total_display').value = formatXAF(0);
            document.getElementById('edit-monto_total_raw').value = 0;
            document.getElementById('edit-adelanto').value = 0;
            document.getElementById('edit-id_proveedor').value = '';
            document.getElementById('edit-id_personal').value = '';
            document.getElementById('edit-fecha_compra').value = '';
            document.getElementById('edit-estado_pago').value = 'pendiente';
        });

        // Listener para adelanto en el modal de Editar
        document.getElementById('edit-adelanto').addEventListener('input', () => calculateTotalCompraMonto('productos-dinamicos-container-edit', 'edit-monto_total_display', 'edit-monto_total_raw'));
    }


    // Lógica para el modal de VER DETALLES DE COMPRA
    const modalVerDetallesCompra = document.getElementById('modalVerDetallesCompra');
    if (modalVerDetallesCompra) {
        modalVerDetallesCompra.addEventListener('show.bs.modal', async function(event) {
            const button = event.relatedTarget;
            const compraId = button.dataset.id;
            
            document.getElementById('detalle-compra-id-display').textContent = compraId;
            document.getElementById('detalle-productos-container').innerHTML = '<tr><td colspan="10" class="text-center py-4">Cargando detalles...</td></tr>';

            try {
                const response = await fetch(`api/detalles_compra.php?id=${compraId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                if (data.compra) {
                    document.getElementById('detalle-proveedor').textContent = data.compra.nombre_proveedor;
                    document.getElementById('detalle-personal').textContent = `${data.compra.nombre_personal} ${data.compra.apellidos_personal}`;
                    document.getElementById('detalle-fecha-compra').textContent = new Date(data.compra.fecha_compra).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
                    document.getElementById('detalle-fecha-registro').textContent = new Date(data.compra.fecha_registro).toLocaleString('es-ES');
                    document.getElementById('detalle-monto-total-summary').textContent = formatXAF(data.compra.monto_total ?? 0);
                    document.getElementById('detalle-adelanto-summary').textContent = formatXAF(data.compra.adelanto ?? 0);
                    document.getElementById('detalle-estado-pago-summary').textContent = data.compra.estado_pago;

                    const netoPendiente = (data.compra.monto_total ?? 0) - (data.compra.adelanto ?? 0);
                    document.getElementById('detalle-neto-pendiente').textContent = formatXAF(netoPendiente);
                    if (netoPendiente > 0) {
                        document.getElementById('detalle-neto-pendiente').classList.remove('text-green-600');
                        document.getElementById('detalle-neto-pendiente').classList.add('text-red-600');
                    } else if (netoPendiente < 0) {
                        document.getElementById('detalle-neto-pendiente').classList.remove('text-red-600');
                        document.getElementById('detalle-neto-pendiente').classList.add('text-green-600');
                    } else {
                        document.getElementById('detalle-neto-pendiente').classList.remove('text-red-600', 'text-green-600');
                    }
                }

                const detalleProductosContainer = document.getElementById('detalle-productos-container');
                detalleProductosContainer.innerHTML = ''; // Limpiar cualquier contenido de carga
                if (data.productos && data.productos.length > 0) {
                    data.productos.forEach(prod => {
                        const row = document.createElement('tr');
                        row.classList.add('border-b', 'border-gray-200');
                        // Asegurarse de que los valores null se manejen como 0 o '-' para visualización
                        const precioUnitario = prod.precio_unitario ?? 0;
                        const precioVenta = prod.precio_venta ?? 0;
                        const tirasPorCaja = prod.tiras_por_caja ?? '-';
                        const pastillasPorTira = prod.pastillas_por_tira ?? '-';
                        const pastillasPorFrasco = prod.pastillas_por_frasco ?? '-';
                        const fechaVencimiento = prod.fecha_vencimiento ? new Date(prod.fecha_vencimiento).toLocaleDateString('es-ES') : '-';
                        const totalProducto = (prod.cantidad ?? 0) * precioUnitario;

                        row.innerHTML = `
                            <td class="py-3 px-4 text-sm text-gray-800">${prod.nombre_producto}</td>
                            <td class="py-3 px-4 text-sm text-gray-800">${prod.cantidad}</td>
                            <td class="py-3 px-4 text-sm text-gray-800">${prod.unidad}</td>
                            <td class="py-3 px-4 text-sm text-gray-800">${formatXAF(precioUnitario)}</td>
                            <td class="py-3 px-4 text-sm text-gray-800">${formatXAF(precioVenta)}</td>
                            <td class="py-3 px-4 text-sm text-gray-800">${tirasPorCaja}</td>
                            <td class="py-3 px-4 text-sm text-gray-800">${pastillasPorTira}</td>
                            <td class="py-3 px-4 text-sm text-gray-800">${pastillasPorFrasco}</td>
                            <td class="py-3 px-4 text-sm text-gray-800">${fechaVencimiento}</td>
                            <td class="py-3 px-4 text-sm text-gray-800">${formatXAF(totalProducto)}</td>
                        `;
                        detalleProductosContainer.appendChild(row);
                    });
                } else {
                    detalleProductosContainer.innerHTML = '<tr><td colspan="10" class="text-center py-4">No hay productos en esta compra.</td></tr>';
                }

            } catch (error) {
                console.error('Error al cargar detalles de la compra:', error);
                document.getElementById('detalle-productos-container').innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Error al cargar los detalles.</td></tr>';
            }
        });

        // Limpiar detalles al cerrar el modal
        modalVerDetallesCompra.addEventListener('hidden.bs.modal', function() {
            document.getElementById('detalle-compra-id-display').textContent = '';
            document.getElementById('detalle-proveedor').textContent = '';
            document.getElementById('detalle-personal').textContent = '';
            document.getElementById('detalle-fecha-compra').textContent = '';
            document.getElementById('detalle-fecha-registro').textContent = '';
            document.getElementById('detalle-monto-total-summary').textContent = '';
            document.getElementById('detalle-adelanto-summary').textContent = '';
            document.getElementById('detalle-estado-pago-summary').textContent = '';
            document.getElementById('detalle-neto-pendiente').textContent = '';
            document.getElementById('detalle-neto-pendiente').classList.remove('text-red-600', 'text-green-600');
            document.getElementById('detalle-productos-container').innerHTML = '';
        });
    }

});
</script>
