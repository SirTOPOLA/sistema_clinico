<?php 

$idUsuario = $_SESSION['usuario']['id'] ?? 0;
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

// Función para formatear moneda XAF sin decimales y con punto de mil
function formatXAF($value) {
    return number_format($value, 0, '', '.');
}

// Consulta para productos de farmacia
$sql = "SELECT * FROM productos_farmacia ORDER BY fecha_registro DESC";
$productos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="content" class="container-fluid">
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0"><i class="bi bi-capsule me-2"></i>Listado de Productos</h3>
      <button class="btn btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#modalCrearProducto">
        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Producto
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscador" class="form-control" placeholder="Buscar producto...">
    </div>
  </div>

  <?php if (isset($_SESSION['success_producto'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $_SESSION['success_producto']; unset($_SESSION['success_producto']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error_producto'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= $_SESSION['error_producto']; unset($_SESSION['error_producto']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php
  function limitarTexto($texto, $limite = 100)
  {
    $textoPlano = strip_tags($texto);
    return strlen($textoPlano) > $limite
      ? substr(trim($textoPlano), 0, $limite) . '...'
      : $textoPlano;
  }
  ?>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaProductos" class="table table-hover table-bordered align-middle table-sm">
          <thead class="table-light text-nowrap">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Stock</th>
              <th>Precios Venta</th>
              <th>Vencimiento</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($productos as $p): ?>
              <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= nl2br(htmlspecialchars(limitarTexto($p['descripcion'], 60))) ?></td>
                <td>
                  <small>
                    Caja: <?= $p['stock_caja'] ?> |
                    Frasco: <?= $p['stock_frasco'] ?> |
                    Tira: <?= $p['stock_tira'] ?> |
                    Pastilla: <?= $p['stock_pastilla'] ?>
                  </small>
                </td>
                <td>
                  <small>
                    Caja: XAF <?= formatXAF($p['precio_caja']) ?> |
                    Frasco: XAF <?= formatXAF($p['precio_frasco']) ?> <br>
                    Tira: XAF <?= formatXAF($p['precio_tira']) ?> |
                    Pastilla: XAF <?= formatXAF($p['precio_pastilla']) ?>
                  </small>
                </td>
                <td><?= $p['fecha_vencimiento'] ? date('d/m/Y', strtotime($p['fecha_vencimiento'])) : '-' ?></td>
                <td class="text-nowrap">
                  <button
                    class="btn btn-sm btn-outline-primary me-1"
                    title="Editar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditarProducto"
                    data-id="<?= $p['id'] ?>"
                    data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>"
                    data-descripcion="<?= htmlspecialchars($p['descripcion'], ENT_QUOTES) ?>"
                    data-stock_caja="<?= $p['stock_caja'] ?>"
                    data-stock_frasco="<?= $p['stock_frasco'] ?>"
                    data-stock_tira="<?= $p['stock_tira'] ?>"
                    data-stock_pastilla="<?= $p['stock_pastilla'] ?>"
                    data-fecha_vencimiento="<?= $p['fecha_vencimiento'] ?>">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <?php if($rol === 'administrador'): ?>
                  <a href="acciones/productos_farmacia_crud.php?action=eliminar&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('¿Deseas eliminar este producto?')" title="Eliminar">
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

<!-- Modal para Crear Producto -->
<div class="modal fade" id="modalCrearProducto" tabindex="-1" aria-labelledby="modalCrearProductoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="modalCrearProductoLabel"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Producto</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="api/productos_farmacia_crud.php?action=crear" method="POST">
        <div class="modal-body p-4">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
          </div>
          
          <h6 class="mt-4 text-primary">Información Controlada por Compras</h6>
          <p class="text-muted small">Todos los detalles de stock, precios y vencimiento se gestionan y actualizan desde el módulo de compras.</p>
          
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Producto</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal para Editar Producto -->
<div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="modalEditarProductoLabel"><i class="bi bi-pencil-square me-2"></i>Editar Producto</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="api/productos_farmacia_crud.php?action=editar" method="POST">
        <div class="modal-body p-4">
          <input type="hidden" id="edit-id" name="id">
          <div class="row">
            <div class="col-md-12 mb-3">
              <label for="edit-nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="edit-nombre" name="nombre" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="edit-descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="edit-descripcion" name="descripcion" rows="3"></textarea>
          </div>
          
          <h6 class="mt-4 text-primary">Información Controlada por Compras</h6>
          <p class="text-muted small">Estos campos se actualizan automáticamente al registrar una compra de este producto.</p>
          <div class="mb-3">
            <label for="edit-fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
            <input type="date" class="form-control" id="edit-fecha_vencimiento" name="fecha_vencimiento" readonly style="background-color: #e9ecef;">
          </div>
          
          <div class="row">
            <div class="col-md-12 mb-3"> <!-- Ocupa todo el ancho -->
              <label class="form-label">Stock Actual</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                <input type="text" class="form-control" id="edit-stock-display" readonly style="background-color: #e9ecef;">
              </div>
            </div>
            <!-- Los precios de venta no se muestran aquí, ya que se gestionan desde el módulo de compras -->
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Función para formatear moneda XAF sin decimales y con punto de mil
  const formatXAF = (value) => {
    return new Intl.NumberFormat('es-GQ', { // 'es-GQ' para Guinea Ecuatorial, o 'fr-CM' si es más apropiado
      style: 'currency',
      currency: 'XAF',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(value);
  };

  // Script para el buscador de productos
  const buscador = document.getElementById('buscador');
  const tablaProductos = document.getElementById('tablaProductos');
  if (buscador && tablaProductos) {
    buscador.addEventListener('keyup', function () {
      const value = this.value.toLowerCase();
      const rows = tablaProductos.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
      for (let i = 0; i < rows.length; i++) {
        const rowText = rows[i].textContent.toLowerCase();
        rows[i].style.display = rowText.includes(value) ? '' : 'none';
      }
    });
  }

  // Script para rellenar el modal de edición de producto
  const modalEditarProducto = document.getElementById('modalEditarProducto');
  if (modalEditarProducto) {
    modalEditarProducto.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;

      const id = button.getAttribute('data-id');
      const nombre = button.getAttribute('data-nombre');
      const descripcion = button.getAttribute('data-descripcion');
      const stock_caja = button.getAttribute('data-stock_caja');
      const stock_frasco = button.getAttribute('data-stock_frasco');
      const stock_tira = button.getAttribute('data-stock_tira');
      const stock_pastilla = button.getAttribute('data-stock_pastilla');
      // Eliminados: precio_caja, precio_frasco, precio_tira, precio_pastilla
      const fecha_vencimiento = button.getAttribute('data-fecha_vencimiento');

      const modalTitle = modalEditarProducto.querySelector('.modal-title');
      const form = modalEditarProducto.querySelector('form');

      form.querySelector('#edit-id').value = id;
      form.querySelector('#edit-nombre').value = nombre;
      form.querySelector('#edit-descripcion').value = descripcion;
      
      // Mostrar stock en campos de solo lectura
      form.querySelector('#edit-stock-display').value = `Caja: ${stock_caja} | Frasco: ${stock_frasco} | Tira: ${stock_tira} | Pastilla: ${stock_pastilla}`;
      // Eliminado: el campo de precios de venta en el modal de edición
      
      form.querySelector('#edit-fecha_vencimiento').value = fecha_vencimiento;

      modalTitle.textContent = `Editar Producto #${id} - ${nombre}`;
    });
  }
});
</script>
