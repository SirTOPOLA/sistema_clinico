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
$sqlProductosFarmaciaDropdown = "SELECT id, nombre, 
    ROUND(COALESCE(precio_caja, 0)) AS precio_caja, 
    ROUND(COALESCE(precio_frasco, 0)) AS precio_frasco, 
    ROUND(COALESCE(precio_tira, 0)) AS precio_tira, 
    ROUND(COALESCE(precio_pastilla, 0)) AS precio_pastilla, 
    ROUND(COALESCE(tiras_por_caja, 0)) AS tiras_por_caja, 
    ROUND(COALESCE(pastillas_por_tira, 0)) AS pastillas_por_tira,
    ROUND(COALESCE(pastillas_por_frasco, 0)) AS pastillas_por_frasco
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
              $neto = $compra['monto_total'] - $compra['adelanto'];
              $claseColorNeto = '';
              if ($neto > 0) {
                  $claseColorNeto = 'text-danger fw-bold'; // Deuda (pendiente de pago)
              } elseif ($neto < 0) {
                  $claseColorNeto = 'text-success fw-bold'; // Crédito / Sobrepago
              }

              $totalEsperadoVenta = $compra['total_esperado_venta'] ?? 0.00;
              $beneficios = $totalEsperadoVenta - $compra['monto_total'];
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
                <td>XAF <?= formatXAF($compra['monto_total']) ?></td>
                <td>XAF <?= formatXAF($compra['adelanto']) ?></td>
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
  // Obtenemos los datos directamente del PHP, ya redondeados
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
    // El precio de venta también afecta los totales internos de la fila si se cambia
    if (newPrecioVentaInput) {
      newPrecioVentaInput.addEventListener('input', () => {
          // Asegurar que el valor sea entero
          newPrecioVentaInput.value = parseInt(newPrecioVentaInput.value) || 0;
          updateCalculatedTotals(newRow, calculateTotalFn);
      });
    }

    // Otros campos de unidades contenidas (asegurar que sean enteros al input)
    newRow.querySelector('.tiras-por-caja-input')?.addEventListener('input', function() { this.value = parseInt(this.value) || 0; });
    newRow.querySelector('.pastillas-por-tira-input')?.addEventListener('input', function() { this.value = parseInt(this.value) || 0; });
    newRow.querySelector('.pastillas-por-frasco-input')?.addEventListener('input', function() { this.value = parseInt(this.value) || 0; });

    // Listeners para los nuevos campos de precio de venta por sub-unidad (asegurar enteros)
    if (newPrecioVentaTiraSubInput) {
      newPrecioVentaTiraSubInput.addEventListener('input', function() { this.value = parseInt(this.value) || 0; });
    }
    if (newPrecioVentaPastillaSubTiraInput) {
      newPrecioVentaPastillaSubTiraInput.addEventListener('input', function() { this.value = parseInt(this.value) || 0; });
    }
    if (newPrecioVentaPastillaSubFrascoInput) {
      newPrecioVentaPastillaSubFrascoInput.addEventListener('input', function() { this.value = parseInt(this.value) || 0; });
    }

    // Listener para la fecha de vencimiento (no afecta cálculos directos)
    newRow.querySelector('.fecha-vencimiento-input')?.addEventListener('input', () => {});

    // Listener para el botón de eliminar fila
    if (newRemoveBtn) {
      newRemoveBtn.addEventListener('click', function() {
        newRow.remove();
        calculateTotalFn(); // Recalcular total al eliminar
      });
    }
  }

  /**
   * Actualiza el precio de venta sugerido y las unidades contenidas en una fila de producto.
   * También gestiona la visibilidad de los campos de unidades y sus precios de venta asociados.
   * @param {HTMLElement} currentRow - La fila de producto.
   */
  function updateSuggestedProductDetails(currentRow) {
    const selectProducto = currentRow.querySelector('.producto-select');
    const selectUnidad = currentRow.querySelector('.unidad-select');
    const inputPrecioVenta = currentRow.querySelector('.precio-venta-input');
    const unidadInfoSpan = currentRow.querySelector('.unidad-info');
    const unidadPrecioLabelSpan = currentRow.querySelector('.unidad-precio-label'); 
    
    // Contenedores de cantidades
    const tirasPorCajaContainer = currentRow.querySelector('.tiras-por-caja-container');
    const pastillasPorTiraContainer = currentRow.querySelector('.pastillas-por-tira-container');
    const pastillasPorFrascoContainer = currentRow.querySelector('.pastillas-por-frasco-container');

    // Campos de cantidades
    const inputTirasPorCaja = currentRow.querySelector('.tiras-por-caja-input');
    const inputPastillasPorTira = currentRow.querySelector('.pastillas-por-tira-input');
    const inputPastillasPorFrasco = currentRow.querySelector('.pastillas-por-frasco-input');

    // Nuevos campos y contenedores de precio de venta por sub-unidad
    const precioVentaTiraSubContainer = currentRow.querySelector('.precio-venta-tira-sub-container');
    const inputPrecioVentaTiraSub = currentRow.querySelector('.precio-venta-tira-sub-input');

    const precioVentaPastillaSubTiraContainer = currentRow.querySelector('.precio-venta-pastilla-sub-tira-container');
    const inputPrecioVentaPastillaSubTira = currentRow.querySelector('.precio-venta-pastilla-sub-tira-input');

    const precioVentaPastillaSubFrascoContainer = currentRow.querySelector('.precio-venta-pastilla-sub-frasco-container');
    const inputPrecioVentaPastillaSubFrasco = currentRow.querySelector('.precio-venta-pastilla-sub-frasco-input');


    const selectedProductOption = selectProducto.options[selectProducto.selectedIndex];
    const selectedUnidad = selectUnidad.value;

    let precioSugerido = 0;
    let unidadInfoText = '';
    let precioLabelText = 'Sugerido'; 
    let defaultTirasPorCaja = 0;
    let defaultPastillasPorTira = 0;
    let defaultPastillasPorFrasco = 0;

    let defaultPrecioTira = 0;
    let defaultPrecioPastilla = 0; // Para pastillas de tira
    let defaultPrecioPastillaFrasco = 0; // Para pastillas de frasco


    // Ocultar todos los contenedores y resetear precios sub-unidad por defecto
    if (tirasPorCajaContainer) tirasPorCajaContainer.style.display = 'none';
    if (pastillasPorTiraContainer) pastillasPorTiraContainer.style.display = 'none';
    if (pastillasPorFrascoContainer) pastillasPorFrascoContainer.style.display = 'none';

    if (precioVentaTiraSubContainer) precioVentaTiraSubContainer.style.display = 'none';
    if (precioVentaPastillaSubTiraContainer) precioVentaPastillaSubTiraContainer.style.display = 'none';
    if (precioVentaPastillaSubFrascoContainer) precioVentaPastillaSubFrascoContainer.style.display = 'none';

    if (inputPrecioVentaTiraSub) inputPrecioVentaTiraSub.value = 0;
    if (inputPrecioVentaPastillaSubTira) inputPrecioVentaPastillaSubTira.value = 0;
    if (inputPrecioVentaPastillaSubFrasco) inputPrecioVentaPastillaSubFrasco.value = 0;


    if (selectedProductOption && selectedProductOption.value) {
      defaultTirasPorCaja = parseInt(selectedProductOption.getAttribute('data-tiras-por-caja')) || 0;
      defaultPastillasPorTira = parseInt(selectedProductOption.getAttribute('data-pastillas-por-tira')) || 0;
      defaultPastillasPorFrasco = parseInt(selectedProductOption.getAttribute('data-pastillas-por-frasco')) || 0;

      defaultPrecioTira = parseInt(selectedProductOption.getAttribute('data-precio-tira')) || 0;
      defaultPrecioPastilla = parseInt(selectedProductOption.getAttribute('data-precio-pastilla')) || 0;
      // Asumimos que precio_pastilla del producto es general para pastillas de tira/frasco si no hay distinción explícita
      defaultPrecioPastillaFrasco = parseInt(selectedProductOption.getAttribute('data-precio-pastilla')) || 0; 


      switch (selectedUnidad) {
        case 'caja':
          precioSugerido = parseInt(selectedProductOption.getAttribute('data-precio-caja')) || 0;
          precioLabelText = 'Caja';
          
          if (tirasPorCajaContainer) tirasPorCajaContainer.style.display = 'block';
          if (precioVentaTiraSubContainer) precioVentaTiraSubContainer.style.display = 'block';
          if (inputPrecioVentaTiraSub) inputPrecioVentaTiraSub.value = defaultPrecioTira;

          if (pastillasPorTiraContainer) pastillasPorTiraContainer.style.display = 'block';
          if (precioVentaPastillaSubTiraContainer) precioVentaPastillaSubTiraContainer.style.display = 'block';
          if (inputPrecioVentaPastillaSubTira) inputPrecioVentaPastillaSubTira.value = defaultPrecioPastilla;

          if (defaultTirasPorCaja > 0 && defaultPastillasPorTira > 0) {
            unidadInfoText = `Esta caja contiene ${defaultTirasPorCaja} tiras, cada una con ${defaultPastillasPorTira} pastillas.`;
          }
          break;
        case 'frasco':
          precioSugerido = parseInt(selectedProductOption.getAttribute('data-precio-frasco')) || 0;
          precioLabelText = 'Frasco';
          
          if (pastillasPorFrascoContainer) pastillasPorFrascoContainer.style.display = 'block';
          if (precioVentaPastillaSubFrascoContainer) precioVentaPastillaSubFrascoContainer.style.display = 'block';
          if (inputPrecioVentaPastillaSubFrasco) inputPrecioVentaPastillaSubFrasco.value = defaultPrecioPastillaFrasco;

          if (defaultPastillasPorFrasco > 0) {
            unidadInfoText = `Este frasco contiene ${defaultPastillasPorFrasco} pastillas.`;
          }
          break;
        case 'tira':
          precioSugerido = parseInt(selectedProductOption.getAttribute('data-precio-tira')) || 0;
          precioLabelText = 'Tira';
          
          if (pastillasPorTiraContainer) pastillasPorTiraContainer.style.display = 'block';
          if (precioVentaPastillaSubTiraContainer) precioVentaPastillaSubTiraContainer.style.display = 'block';
          if (inputPrecioVentaPastillaSubTira) inputPrecioVentaPastillaSubTira.value = defaultPrecioPastilla;

          if (defaultPastillasPorTira > 0) {
            unidadInfoText = `Esta tira contiene ${defaultPastillasPorTira} pastillas.`;
          }
          break;
        case 'pastilla':
          precioSugerido = parseInt(selectedProductOption.getAttribute('data-precio-pastilla')) || 0;
          precioLabelText = 'Pastilla';
          break;
        default:
            precioLabelText = 'Sugerido';
            break;
      }
      
      if (inputPrecioVenta.value === '0' || inputPrecioVenta.value === '') {
        inputPrecioVenta.value = precioSugerido;
      }
    } else {
      if (inputPrecioVenta.value === '0' || inputPrecioVenta.value === '') {
        inputPrecioVenta.value = '0';
      }
      defaultTirasPorCaja = 0;
      defaultPastillasPorTira = 0;
      defaultPastillasPorFrasco = 0;
      unidadInfoText = '';
      precioLabelText = 'Sugerido';
    }
    unidadInfoSpan.textContent = unidadInfoText;
    unidadPrecioLabelSpan.textContent = precioLabelText; // Actualizar el label dinámico del precio de venta

    // Actualizar valores de unidades contenidas (solo si están vacías o 0)
    if (inputTirasPorCaja) {
      if (inputTirasPorCaja.value === '0' || inputTirasPorCaja.value === '') {
          inputTirasPorCaja.value = defaultTirasPorCaja;
      }
    }
    if (inputPastillasPorTira) {
      if (inputPastillasPorTira.value === '0' || inputPastillasPorTira.value === '') {
          inputPastillasPorTira.value = defaultPastillasPorTira;
      }
    }
    if (inputPastillasPorFrasco) {
      if (inputPastillasPorFrasco.value === '0' || inputPastillasPorFrasco.value === '') {
          inputPastillasPorFrasco.value = defaultPastillasPorFrasco;
      }
    }
  }

  /**
   * Actualiza los totales calculados para una fila de producto y el total general de la compra.
   * Asegura que todos los cálculos se hagan con enteros.
   * @param {HTMLElement} currentRow - La fila de producto.
   * @param {function} calculateTotalFn - Función para recalcular el total de la compra.
   */
  function updateCalculatedTotals(currentRow, calculateTotalFn) {
      const inputCantidad = currentRow.querySelector('.cantidad-input');
      const inputPrecioUnitario = currentRow.querySelector('.precio-unitario-input');
      const totalPorProductoSpan = currentRow.querySelector('.total-por-producto');

      // Convertir a enteros para los cálculos
      const cantidad = parseInt(inputCantidad.value) || 0;
      const precioUnitario = parseInt(inputPrecioUnitario.value) || 0;
      const rowTotal = cantidad * precioUnitario;
      totalPorProductoSpan.textContent = formatXAF(rowTotal);

      calculateTotalFn(); // Recalcular el total general de la compra (del modal actual)
  }

  /**
   * Añade una nueva fila de producto al modal de creación.
   */
  function addProductoRowCrear() {
    const clone = productoTemplate.content.cloneNode(true);
    const newRow = clone.querySelector('.producto-row');

    // Actualizar nombres de los campos para que PHP los reciba como un array
    newRow.querySelectorAll('[name*="INDEX"]').forEach(input => {
      input.name = input.name.replace('INDEX', productoIndexCrear);
    });

    // Poner las opciones de productos en el select
    newRow.querySelector('.producto-select').innerHTML = generateProductOptions();

    attachProductRowListeners(newRow, calculateTotalCompraCrear); // Adjuntar listeners con la función de cálculo del modal de crear
    productosDinamicsoContainer.appendChild(newRow);
    productoIndexCrear++;
    updateSuggestedProductDetails(newRow); 
    calculateTotalCompraCrear(); // Recalcular el total al añadir nuevo producto
  }

  /**
   * Calcula y actualiza el monto total en el modal de creación.
   */
  function calculateTotalCompraCrear() {
    let total = 0;
    document.querySelectorAll('#productos-dinamicos-container .producto-row').forEach(row => {
      const cantidadInput = row.querySelector('.cantidad-input');
      const precioUnitarioInput = row.querySelector('.precio-unitario-input');

      const cantidad = parseInt(cantidadInput.value) || 0;
      const precioUnitario = parseInt(precioUnitarioInput.value) || 0;
      
      total += (cantidad * precioUnitario);
    });
    document.getElementById('monto_total_display').value = formatXAF(total);
    document.getElementById('monto_total_raw').value = total; // Almacenar el valor raw (entero) para el backend
  }

  // Inicialización del modal de Crear Compra
  const agregarProductoBtn = document.getElementById('agregarProductoBtn');
  const productosDinamicsoContainer = document.getElementById('productos-dinamicos-container');
  const modalCrearCompra = document.getElementById('modalCrearCompra');

  if (agregarProductoBtn && productosDinamicsoContainer && modalCrearCompra) {
    agregarProductoBtn.addEventListener('click', addProductoRowCrear);

    modalCrearCompra.addEventListener('show.bs.modal', function() {
      productosDinamicsoContainer.innerHTML = ''; 
      productoIndexCrear = 0;
      addProductoRowCrear();
    });
    modalCrearCompra.addEventListener('shown.bs.modal', calculateTotalCompraCrear);
  }

  // Asegurar que el adelanto en el modal de crear también se trate como entero
  document.getElementById('adelanto')?.addEventListener('input', function() {
    this.value = parseInt(this.value) || 0; // Forzar a entero
  });


  // *** Lógica para el MODAL DE EDITAR COMPRA (AHORA DINÁMICO) ***
  const modalEditarCompra = document.getElementById('modalEditarCompra');
  const productosDinamicsoContainerEdit = document.getElementById('productos-dinamicos-container-edit');
  const agregarProductoEditBtn = document.getElementById('agregarProductoEditBtn');

  /**
   * Añade una nueva fila de producto al modal de edición y la rellena.
   * @param {Object} [detalleProducto=null] - Objeto con los detalles del producto para rellenar la fila.
   */
  function addProductoRowEditar(detalleProducto = null) {
    const clone = productoTemplate.content.cloneNode(true);
    const newRow = clone.querySelector('.producto-row');

    // Actualizar nombres de los campos para que PHP los reciba como un array
    newRow.querySelectorAll('[name*="INDEX"]').forEach(input => {
      input.name = input.name.replace('INDEX', productoIndexEditar);
    });

    // Poner las opciones de productos en el select
    newRow.querySelector('.producto-select').innerHTML = generateProductOptions(detalleProducto ? detalleProducto.id_producto : null);

    // Rellenar con datos si se proporcionó un detalle de producto
    if (detalleProducto) {
      newRow.querySelector('.cantidad-input').value = parseInt(detalleProducto.cantidad) || 0;
      newRow.querySelector('.unidad-select').value = detalleProducto.unidad;
      newRow.querySelector('.precio-unitario-input').value = parseInt(detalleProducto.precio_unitario) || 0;
      newRow.querySelector('.precio-venta-input').value = parseInt(detalleProducto.precio_venta) || 0;
      
      newRow.querySelector('.tiras-por-caja-input').value = parseInt(detalleProducto.tiras_por_caja) || 0;
      newRow.querySelector('.pastillas-por-tira-input').value = parseInt(detalleProducto.pastillas_por_tira) || 0;
      newRow.querySelector('.pastillas-por-frasco-input').value = parseInt(detalleProducto.pastillas_por_frasco) || 0;
      
      // Nuevos campos de precio de venta por sub-unidad (si existen en detalleProducto)
      newRow.querySelector('.precio-venta-tira-sub-input').value = parseInt(detalleProducto.precio_venta_tira_sub) || 0;
      newRow.querySelector('.precio-venta-pastilla-sub-tira-input').value = parseInt(detalleProducto.precio_venta_pastilla_sub_tira) || 0;
      newRow.querySelector('.precio-venta-pastilla-sub-frasco-input').value = parseInt(detalleProducto.precio_venta_pastilla_sub_frasco) || 0;
      
      if (detalleProducto.fecha_vencimiento) {
        newRow.querySelector('.fecha-vencimiento-input').value = detalleProducto.fecha_vencimiento;
      }
      newRow.querySelector('.detalle-id-input').value = detalleProducto.id_detalle; // Establecer el ID del detalle
    }

    attachProductRowListeners(newRow, calculateTotalCompraEditar); // Adjuntar listeners con la función de cálculo del modal de editar
    productosDinamicsoContainerEdit.appendChild(newRow);
    productoIndexEditar++;
    updateSuggestedProductDetails(newRow); // Asegurarse de que los detalles sugeridos se actualicen y visibilidad
    calculateTotalCompraEditar(); // Recalcular el total al añadir/cargar producto
  }

  /**
   * Calcula y actualiza el monto total en el modal de edición.
   */
  function calculateTotalCompraEditar() {
    let total = 0;
    document.querySelectorAll('#productos-dinamicos-container-edit .producto-row').forEach(row => {
      const cantidadInput = row.querySelector('.cantidad-input');
      const precioUnitarioInput = row.querySelector('.precio-unitario-input');

      const cantidad = parseInt(cantidadInput.value) || 0;
      const precioUnitario = parseInt(precioUnitarioInput.value) || 0;
      
      total += (cantidad * precioUnitario);
    });
    document.getElementById('edit-monto_total_display').value = formatXAF(total);
    document.getElementById('edit-monto_total_raw').value = total; // Almacenar el valor raw (entero) para el backend
  }

  if (modalEditarCompra) {
    modalEditarCompra.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const id_compra = button.getAttribute('data-id');

      document.getElementById('edit-compra-id').value = id_compra;
      document.getElementById('edit-compra-id-display').textContent = id_compra;
      productosDinamicsoContainerEdit.innerHTML = ''; // Limpiar productos anteriores
      productoIndexEditar = 0; // Resetear índice de productos de edición

      // Cargar datos de la compra y sus productos
      fetch(`api/detalles_compra.php?id=${id_compra}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            const compra = data.compra;
            const detalles = data.detalles;

            // Rellenar campos principales de la compra
            document.getElementById('edit-id_proveedor').value = compra.id_proveedor;
            document.getElementById('edit-id_personal').value = compra.id_personal;
            document.getElementById('edit-fecha_compra').value = compra.fecha_compra;
            document.getElementById('edit-monto_total_display').value = formatXAF(compra.monto_total);
            document.getElementById('edit-monto_total_raw').value = parseInt(compra.monto_total || 0);
            document.getElementById('edit-adelanto').value = parseInt(compra.adelanto || 0);
            document.getElementById('edit-estado_pago').value = compra.estado_pago;

            // Rellenar los productos asociados
            if (detalles.length > 0) {
              detalles.forEach(detalle => {
                // Agregar 'id' del detalle de compra para poder identificarlo al guardar
                addProductoRowEditar({...detalle, id_detalle: detalle.id_detalle}); 
              });
            } else {
              addProductoRowEditar(); // Añadir una fila vacía si no hay productos
            }
          } else {
            console.error('Error al cargar detalles de compra para edición:', data.message);
            alert('Error al cargar detalles de compra para edición: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error en la llamada AJAX para edición:', error);
          alert('Error de conexión al cargar los detalles de la compra para edición.');
        });
    });

    agregarProductoEditBtn.addEventListener('click', () => addProductoRowEditar(null)); // Para añadir un producto nuevo en edición
  }

  // Asegurar que el adelanto en el modal de editar también se trate como entero
  document.getElementById('edit-adelanto')?.addEventListener('input', function() {
    this.value = parseInt(this.value) || 0; // Forzar a entero
  });


  // Lógica para el modal de Ver Detalles de Compra
  const modalVerDetallesCompra = document.getElementById('modalVerDetallesCompra');
  if (modalVerDetallesCompra) {
    modalVerDetallesCompra.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget; // Botón que activó el modal
      const id_compra = button.getAttribute('data-id');

      // Mostrar el ID de la compra en el título del modal
      document.getElementById('detalle-compra-id-display').textContent = id_compra;

      // Realizar una llamada AJAX para obtener los detalles completos de la compra
      fetch(`api/detalles_compra.php?id=${id_compra}`)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.success) {
            const compra = data.compra;
            const detalles = data.detalles;

            // Rellenar los campos de la compra principal
            document.getElementById('detalle-proveedor').textContent = compra.nombre_proveedor;
            document.getElementById('detalle-personal').textContent = `${compra.nombre_personal} ${compra.apellidos_personal}`;
            document.getElementById('detalle-fecha-compra').textContent = new Date(compra.fecha_compra).toLocaleDateString('es-GQ');
            document.getElementById('detalle-fecha-registro').textContent = new Date(compra.fecha_registro).toLocaleDateString('es-GQ', {
                year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
            });


            // Rellenar la tabla de productos
            const detalleProductosContainer = document.getElementById('detalle-productos-container');
            detalleProductosContainer.innerHTML = ''; // Limpiar cualquier contenido anterior

            if (detalles.length > 0) {
              detalles.forEach(detalle => {
                const row = document.createElement('tr');
                const totalProducto = (parseInt(detalle.cantidad) || 0) * (parseInt(detalle.precio_unitario) || 0);
                row.innerHTML = `
                  <td class="py-2 px-4 text-sm text-gray-700">${detalle.nombre_producto}</td>
                  <td class="py-2 px-4 text-sm text-gray-700">${parseInt(detalle.cantidad) || 0}</td>
                  <td class="py-2 px-4 text-sm text-gray-700">${detalle.unidad}</td>
                  <td class="py-2 px-4 text-sm text-gray-700">${formatXAF(parseInt(detalle.precio_unitario) || 0)}</td>
                  <td class="py-2 px-4 text-sm text-gray-700">${formatXAF(parseInt(detalle.precio_venta) || 0)}</td>
                  <td class="py-2 px-4 text-sm text-gray-700">${parseInt(detalle.tiras_por_caja) || '0'}</td>
                  <td class="py-2 px-4 text-sm text-gray-700">${parseInt(detalle.pastillas_por_tira) || '0'}</td>
                  <td class="py-2 px-4 text-sm text-gray-700">${parseInt(detalle.pastillas_por_frasco) || '0'}</td>
                  <td class="py-2 px-4 text-sm text-gray-700">${detalle.fecha_vencimiento ? new Date(detalle.fecha_vencimiento).toLocaleDateString('es-GQ') : 'N/A'}</td>
                  <td class="py-2 px-4 text-sm font-semibold text-gray-800">${formatXAF(totalProducto)}</td>
                `;
                detalleProductosContainer.appendChild(row);
              });
            } else {
              detalleProductosContainer.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No hay productos asociados a esta compra.</td></tr>';
            }

            // Rellenar la sección de resumen de pago
            const montoTotalCompra = parseInt(compra.monto_total || 0);
            const adelanto = parseInt(compra.adelanto || 0);
            const netoPendiente = montoTotalCompra - adelanto;

            document.getElementById('detalle-monto-total-summary').textContent = formatXAF(montoTotalCompra);
            document.getElementById('detalle-adelanto-summary').textContent = formatXAF(adelanto);
            document.getElementById('detalle-estado-pago-summary').textContent = compra.estado_pago;
            document.getElementById('detalle-neto-pendiente').textContent = formatXAF(netoPendiente);
            // Cambiar color si hay neto pendiente
            if (netoPendiente > 0) {
              document.getElementById('detalle-neto-pendiente').classList.remove('text-green-600');
              document.getElementById('detalle-neto-pendiente').classList.add('text-red-600');
            } else {
              document.getElementById('detalle-neto-pendiente').classList.remove('text-red-600');
              document.getElementById('detalle-neto-pendiente').classList.add('text-green-600');
            }


          } else {
            console.error('Error al cargar los detalles de la compra:', data.message);
            alert('Error al cargar los detalles de la compra: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error en la llamada AJAX:', error);
          alert('Error de conexión al intentar cargar los detalles de la compra.');
        });
    });
  }
});
</script>