<?php
 
$idUsuario = $_SESSION['usuario']['id'] ?? 0;
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

// Función para formatear moneda XAF sin decimales y con punto de mil
function formatXAF($value) {
    return number_format($value, 0, '', '.');
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

// NUEVA Consulta para obtener la lista de personal para los selects en los modales
$sqlPersonalDropdown = "SELECT id, nombre, apellidos FROM personal ORDER BY nombre ASC";
$personalDropdown = $pdo->query($sqlPersonalDropdown)->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener la lista de productos de farmacia para los selects en los modales
// Asegúrate de que tu tabla `productos_farmacia` tenga las columnas para estas conversiones:
// `tiras_por_caja` INT DEFAULT 0,
// `pastillas_por_tira` INT DEFAULT 0,
// `pastillas_por_frasco` INT DEFAULT 0
$sqlProductosFarmaciaDropdown = "SELECT id, nombre, precio_caja, precio_frasco, precio_tira, precio_pastilla, 
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
                    class="btn btn-sm btn-outline-primary me-1"
                    title="Editar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditarCompra"
                    data-id="<?= $compra['id'] ?>"
                    data-id_proveedor="<?= $compra['id_proveedor'] ?>"
                    data-fecha_compra="<?= $compra['fecha_compra'] ?>"
                    data-monto_total="<?= $compra['monto_total'] ?>"
                    data-adelanto="<?= $compra['adelanto'] ?>"
                    data-estado_pago="<?= htmlspecialchars($compra['estado_pago'], ENT_QUOTES) ?>">
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
  <div class="modal-dialog modal-lg"> <!-- Aumentado a modal-lg para más espacio -->
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="modalCrearCompraLabel"><i class="bi bi-plus-circle me-2"></i>Registrar Nueva Compra</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="api/compras_crud.php?action=crear" method="POST">
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
              <!-- Hidden input to send the raw numerical value to the backend -->
              <input type="hidden" id="monto_total_raw" name="monto_total" value="0.00">
            </div>
          </div>
          <div class="mb-3">
            <label for="adelanto" class="form-label">Adelanto</label>
            <div class="input-group">
              <span class="input-group-text">XAF</span>
              <input type="number" class="form-control" id="adelanto" name="adelanto" step="0.01" value="0.00">
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
          <!-- El id_personal ahora se selecciona en el campo select, no se envía como hidden -->
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

    <!-- Nuevos campos para las unidades contenidas -->
    <h6 class="mt-2 text-primary small">Unidades Contenidas por Unidad Comprada</h6>
    <div class="row">
      <div class="col-md-4 mb-3">
        <label class="form-label small">Tiras por Caja</label>
        <input type="number" class="form-control tiras-por-caja-input" name="productos[INDEX][tiras_por_caja]" min="0" value="0">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label small">Pastillas por Tira</label>
        <input type="number" class="form-control pastillas-por-tira-input" name="productos[INDEX][pastillas_por_tira]" min="0" value="0">
      </div>
      <div class="col-md-4 mb-3">
        <label class="form-label small">Pastillas por Frasco</label>
        <input type="number" class="form-control pastillas-por-frasco-input" name="productos[INDEX][pastillas_por_frasco]" min="0" value="0">
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
          <input type="number" class="form-control precio-unitario-input" name="productos[INDEX][precio_unitario]" step="0.01" value="0.00" required>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label small">Precio Venta (Sugerido) <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text">XAF</span>
          <input type="number" class="form-control precio-venta-input" name="productos[INDEX][precio_venta]" step="0.01" value="0.00" required>
        </div>
      </div>
    </div>
    <div class="text-end fw-bold mt-2">
        Total por Producto: <span class="total-por-producto">XAF 0</span>
    </div>
  </div>
</template>

<!-- Modal para Editar Compra (Este modal se mantiene simple para editar solo la compra principal) -->
<div class="modal fade" id="modalEditarCompra" tabindex="-1" aria-labelledby="modalEditarCompraLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="modalEditarCompraLabel"><i class="bi bi-pencil-square me-2"></i>Editar Compra</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="api/compras_crud.php?action=editar" method="POST">
        <div class="modal-body p-4">
          <input type="hidden" id="edit-compra-id" name="id">
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
            <label for="edit-monto_total" class="form-label">Monto Total <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text">XAF</span>
              <input type="number" class="form-control" id="edit-monto_total" name="monto_total" step="0.01" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="edit-adelanto" class="form-label">Adelanto</label>
            <div class="input-group">
              <span class="input-group-text">XAF</span>
              <input type="number" class="form-control" id="edit-adelanto" name="adelanto" step="0.01">
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
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <!-- El id_personal ahora se selecciona en el campo select, no se envía como hidden -->
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Función para formatear moneda XAF sin decimales y con punto de mil
  const formatXAF = (value) => {
    return new Intl.NumberFormat('es-GQ', { // 'es-GQ' para Guinea Ecuatorial, si no, usa 'fr-CM' para Camerún, etc.
      style: 'currency',
      currency: 'XAF',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(value);
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

  // Script para rellenar el modal de edición de compra
  const modalEditarCompra = document.getElementById('modalEditarCompra');
  if (modalEditarCompra) {
    modalEditarCompra.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget; // Botón que activó el modal
      
      const id = button.getAttribute('data-id');
      const id_proveedor = button.getAttribute('data-id_proveedor');
      const fecha_compra = button.getAttribute('data-fecha_compra');
      const monto_total = button.getAttribute('data-monto_total');
      const adelanto = button.getAttribute('data-adelanto');
      const estado_pago = button.getAttribute('data-estado_pago');
      // Obtener el id_personal de la fila para la edición
      const id_personal = button.getAttribute('data-id_personal');


      const modalTitle = modalEditarCompra.querySelector('.modal-title');
      const form = modalEditarCompra.querySelector('form');
      
      form.querySelector('#edit-compra-id').value = id;
      form.querySelector('#edit-id_proveedor').value = id_proveedor;
      form.querySelector('#edit-fecha_compra').value = fecha_compra;
      form.querySelector('#edit-monto_total').value = parseFloat(monto_total || 0).toFixed(2); // Mantener 2 decimales para edición
      form.querySelector('#edit-adelanto').value = parseFloat(adelanto || 0).toFixed(2); // Mantener 2 decimales para edición
      form.querySelector('#edit-estado_pago').value = estado_pago;
      // Establecer el personal seleccionado en el modal de edición
      form.querySelector('#edit-id_personal').value = id_personal;

      // Ensure the selected option in the dropdown matches the data-estado_pago
      const estadoPagoSelect = form.querySelector('#edit-estado_pago');
      for (let i = 0; i < estadoPagoSelect.options.length; i++) {
        if (estadoPagoSelect.options[i].value === estado_pago) {
          estadoPagoSelect.selectedIndex = i;
          break;
        }
      }

      modalTitle.textContent = `Editar Compra #${id}`;
    });
  }

  // Lógica para añadir dinámicamente productos a la compra
  const agregarProductoBtn = document.getElementById('agregarProductoBtn');
  const productosDinamicsoContainer = document.getElementById('productos-dinamicos-container');
  const productoTemplate = document.getElementById('producto-compra-template');
  let productoIndex = 0; // Para asignar nombres únicos a los campos de productos

  // Función para calcular y actualizar el monto total de la compra
  function calculateTotalCompra() {
    let total = 0;
    document.querySelectorAll('#productos-dinamicos-container .producto-row').forEach(row => {
      const cantidadInput = row.querySelector('.cantidad-input');
      const precioUnitarioInput = row.querySelector('.precio-unitario-input');

      const cantidad = parseFloat(cantidadInput.value) || 0;
      const precioUnitario = parseFloat(precioUnitarioInput.value) || 0;
      
      total += (cantidad * precioUnitario);
    });
    document.getElementById('monto_total_display').value = formatXAF(total);
    document.getElementById('monto_total_raw').value = total.toFixed(2); // Almacenar el valor raw para el backend
  }

  // Función para actualizar el precio de venta sugerido y las unidades contenidas
  function updateSuggestedProductDetails(currentRow) {
    const selectProducto = currentRow.querySelector('.producto-select');
    const selectUnidad = currentRow.querySelector('.unidad-select');
    const inputPrecioVenta = currentRow.querySelector('.precio-venta-input');
    const unidadInfoSpan = currentRow.querySelector('.unidad-info');
    const inputTirasPorCaja = currentRow.querySelector('.tiras-por-caja-input');
    const inputPastillasPorTira = currentRow.querySelector('.pastillas-por-tira-input');
    const inputPastillasPorFrasco = currentRow.querySelector('.pastillas-por-frasco-input');

    const selectedProductOption = selectProducto.options[selectProducto.selectedIndex];
    const selectedUnidad = selectUnidad.value;

    let precioSugerido = 0;
    let unidadInfoText = '';
    let defaultTirasPorCaja = 0;
    let defaultPastillasPorTira = 0;
    let defaultPastillasPorFrasco = 0;

    if (selectedProductOption) {
      // Obtener los valores predefinidos de las data-attributes
      defaultTirasPorCaja = parseFloat(selectedProductOption.getAttribute('data-tiras-por-caja')) || 0;
      defaultPastillasPorTira = parseFloat(selectedProductOption.getAttribute('data-pastillas-por-tira')) || 0;
      defaultPastillasPorFrasco = parseFloat(selectedProductOption.getAttribute('data-pastillas-por-frasco')) || 0;

      switch (selectedUnidad) {
        case 'caja':
          precioSugerido = selectedProductOption.getAttribute('data-precio-caja');
          if (defaultTirasPorCaja > 0 && defaultPastillasPorTira > 0) {
            unidadInfoText = `Esta caja contiene ${defaultTirasPorCaja} tiras, cada una con ${defaultPastillasPorTira} pastillas.`;
          }
          break;
        case 'frasco':
          precioSugerido = selectedProductOption.getAttribute('data-precio-frasco');
          if (defaultPastillasPorFrasco > 0) {
            unidadInfoText = `Este frasco contiene ${defaultPastillasPorFrasco} pastillas.`;
          }
          break;
        case 'tira':
          precioSugerido = selectedProductOption.getAttribute('data-precio-tira');
          if (defaultPastillasPorTira > 0) {
            unidadInfoText = `Esta tira contiene ${defaultPastillasPorTira} pastillas.`;
          }
          break;
        case 'pastilla':
          precioSugerido = selectedProductOption.getAttribute('data-precio-pastilla');
          break;
      }
      // Solo actualiza el valor si el campo está vacío o "0.00"
      if (inputPrecioVenta.value === '0.00' || inputPrecioVenta.value === '') {
        inputPrecioVenta.value = parseFloat(precioSugerido || 0).toFixed(2);
      }
    } else {
      if (inputPrecioVenta.value === '0.00' || inputPrecioVenta.value === '') {
        inputPrecioVenta.value = '0.00';
      }
    }
    unidadInfoSpan.textContent = unidadInfoText;

    // Solo actualiza los valores si los campos están vacíos o "0"
    if (inputTirasPorCaja.value === '0' || inputTirasPorCaja.value === '') {
        inputTirasPorCaja.value = defaultTirasPorCaja;
    }
    if (inputPastillasPorTira.value === '0' || inputPastillasPorTira.value === '') {
        inputPastillasPorTira.value = defaultPastillasPorTira;
    }
    if (inputPastillasPorFrasco.value === '0' || inputPastillasPorFrasco.value === '') {
        inputPastillasPorFrasco.value = defaultPastillasPorFrasco;
    }
  }

  // Función para actualizar los totales calculados (basado en cantidad y precio unitario de compra)
  function updateCalculatedTotals(currentRow) {
      const inputCantidad = currentRow.querySelector('.cantidad-input');
      const inputPrecioUnitario = currentRow.querySelector('.precio-unitario-input');
      const totalPorProductoSpan = currentRow.querySelector('.total-por-producto');

      const cantidad = parseFloat(inputCantidad.value) || 0;
      const precioUnitario = parseFloat(inputPrecioUnitario.value) || 0;
      const rowTotal = cantidad * precioUnitario;
      totalPorProductoSpan.textContent = formatXAF(rowTotal);

      calculateTotalCompra(); // Recalcular el total general de la compra
  }


  // Función para añadir una nueva fila de producto
  function addProductoRow() {
    const clone = productoTemplate.content.cloneNode(true);
    const newRow = clone.querySelector('.producto-row');

    // Actualizar nombres de los campos para que PHP los reciba como un array
    newRow.querySelectorAll('[name*="INDEX"]').forEach(input => {
      input.name = input.name.replace('INDEX', productoIndex);
    });

    // Adjuntar listeners
    const newSelectProducto = newRow.querySelector('.producto-select');
    const newSelectUnidad = newRow.querySelector('.unidad-select');
    const newCantidadInput = newRow.querySelector('.cantidad-input');
    const newPrecioUnitarioInput = newRow.querySelector('.precio-unitario-input');
    const newPrecioVentaInput = newRow.querySelector('.precio-venta-input'); 
    const newTirasPorCajaInput = newRow.querySelector('.tiras-por-caja-input');
    const newPastillasPorTiraInput = newRow.querySelector('.pastillas-por-tira-input');
    const newPastillasPorFrascoInput = newRow.querySelector('.pastillas-por-frasco-input');
    const newFechaVencimientoInput = newRow.querySelector('.fecha-vencimiento-input');
    const newRemoveBtn = newRow.querySelector('.eliminar-producto-btn');

    // Listeners para actualizar valores sugeridos (solo al cambiar producto/unidad)
    if (newSelectProducto) {
      newSelectProducto.addEventListener('change', () => updateSuggestedProductDetails(newRow));
    }
    if (newSelectUnidad) {
      newSelectUnidad.addEventListener('change', () => updateSuggestedProductDetails(newRow));
    }

    // Listeners para actualizar totales calculados (cantidad, precio unitario de compra)
    if (newCantidadInput) {
      newCantidadInput.addEventListener('input', () => updateCalculatedTotals(newRow));
    }
    if (newPrecioUnitarioInput) {
      newPrecioUnitarioInput.addEventListener('input', () => updateCalculatedTotals(newRow));
    }
    // Listeners para campos que pueden ser editados manualmente pero también tienen sugerencias
    if (newPrecioVentaInput) {
      newPrecioVentaInput.addEventListener('input', () => updateCalculatedTotals(newRow)); // Afecta el total por producto, no la compra principal
    }
    if (newTirasPorCajaInput) {
        newTirasPorCajaInput.addEventListener('input', () => updateCalculatedTotals(newRow)); // No afecta el total, pero la coherencia
    }
    if (newPastillasPorTiraInput) {
        newPastillasPorTiraInput.addEventListener('input', () => updateCalculatedTotals(newRow)); // No afecta el total, pero la coherencia
    }
    if (newPastillasPorFrascoInput) {
        newPastillasPorFrascoInput.addEventListener('input', () => updateCalculatedTotals(newRow)); // No afecta el total, pero la coherencia
    }
    // Listener para la fecha de vencimiento (no afecta cálculos directos)
    if (newFechaVencimientoInput) {
        newFechaVencimientoInput.addEventListener('input', () => {}); 
    }


    // Listener para el botón de eliminar fila
    if (newRemoveBtn) {
      newRemoveBtn.addEventListener('click', function() {
        newRow.remove();
        calculateTotalCompra(); // Recalcular total al eliminar
      });
    }

    productosDinamicsoContainer.appendChild(newRow);
    productoIndex++; // Incrementar el índice para la próxima fila
    
    // Inicializar los detalles sugeridos y totales para la nueva fila
    // Aquí es donde se aplican los valores predeterminados si los campos están vacíos
    updateSuggestedProductDetails(newRow); 
    updateCalculatedTotals(newRow); 
  }

  if (agregarProductoBtn && productosDinamicsoContainer && productoTemplate) {
    agregarProductoBtn.addEventListener('click', addProductoRow);

    // Añadir la primera fila de producto al cargar el modal de creación
    const modalCrearCompra = document.getElementById('modalCrearCompra');
    if (modalCrearCompra) {
      modalCrearCompra.addEventListener('show.bs.modal', function() {
        // Limpiar filas existentes antes de añadir una nueva si el modal se abre de nuevo
        productosDinamicsoContainer.innerHTML = ''; 
        productoIndex = 0; // Reiniciar el índice
        addProductoRow(); // Añadir la primera fila por defecto
      });
      // Asegurarse de que el total se calcule al abrir el modal si ya hay productos predefinidos
      modalCrearCompra.addEventListener('shown.bs.modal', calculateTotalCompra);
    }
  }
});
</script>