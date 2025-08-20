<?php
session_start(); // Asegúrate de que la sesión esté iniciada para los mensajes
// Asumiendo que $pdo ya está inicializado y conectado a la base de datos
require_once 'acciones/conexion.php'; 

$idUsuario = $_SESSION['usuario']['id'] ?? 0;
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

// Función para formatear moneda XAF sin decimales y con punto de mil
function formatXAF($value) {
    return number_format($value, 0, '', '.');
}

// Consulta para productos de farmacia
// Asegúrate de que tu tabla `productos_farmacia` tenga estas columnas para conversiones:
// `tiras_por_caja` INT DEFAULT 0,
// `pastillas_por_tira` INT DEFAULT 0,
// `pastillas_por_frasco` INT DEFAULT 0
$sql = "SELECT *, COALESCE(tiras_por_caja, 0) AS tiras_por_caja, 
        COALESCE(pastillas_por_tira, 0) AS pastillas_por_tira,
        COALESCE(pastillas_por_frasco, 0) AS pastillas_por_frasco
        FROM productos_farmacia ORDER BY fecha_registro DESC";
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
              <th>Unidades Contenidas</th> <!-- Nueva columna -->
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
                        <?php 
                        $unidades_info = [];
                        if ($p['tiras_por_caja'] > 0) $unidades_info[] = "Caja tiene " . $p['tiras_por_caja'] . " tiras";
                        if ($p['pastillas_por_tira'] > 0) $unidades_info[] = "Tira tiene " . $p['pastillas_por_tira'] . " pastillas";
                        if ($p['pastillas_por_frasco'] > 0) $unidades_info[] = "Frasco tiene " . $p['pastillas_por_frasco'] . " pastillas";
                        echo empty($unidades_info) ? '-' : implode(' | ', $unidades_info);
                        ?>
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
                    data-precio_caja="<?= $p['precio_caja'] ?>"
                    data-precio_frasco="<?= $p['precio_frasco'] ?>"
                    data-precio_tira="<?= $p['precio_tira'] ?>"
                    data-precio_pastilla="<?= $p['precio_pastilla'] ?>"
                    data-fecha_vencimiento="<?= $p['fecha_vencimiento'] ?>"
                    data-tiras_por_caja="<?= $p['tiras_por_caja'] ?>"
                    data-pastillas_por_tira="<?= $p['pastillas_por_tira'] ?>"
                    data-pastillas_por_frasco="<?= $p['pastillas_por_frasco'] ?>">
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
      <form action="acciones/productos_farmacia_crud.php?action=crear" method="POST">
        <div class="modal-body p-4">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento</label>
              <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento">
            </div>
          </div>
          <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label for="codigo_barras" class="form-label">Código de Barras</label>
            <input type="text" class="form-control" id="codigo_barras" name="codigo_barras">
          </div>
          
          <h6 class="mt-4 text-primary">Precios de Venta por Unidad</h6>
          <p class="text-muted small">Estos precios se usarán para las ventas. El stock se actualiza con las compras.</p>
          <div class="row">
            <div class="col-md-3 mb-3">
              <label for="precio_caja" class="form-label">Precio Caja</label>
              <div class="input-group">
                <span class="input-group-text">XAF</span>
                <input type="number" class="form-control" id="precio_caja" name="precio_caja" step="0.01" value="0.00" required>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <label for="precio_frasco" class="form-label">Precio Frasco</label>
              <div class="input-group">
                <span class="input-group-text">XAF</span>
                <input type="number" class="form-control" id="precio_frasco" name="precio_frasco" step="0.01" value="0.00" required>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <label for="precio_tira" class="form-label">Precio Tira</label>
              <div class="input-group">
                <span class="input-group-text">XAF</span>
                <input type="number" class="form-control" id="precio_tira" name="precio_tira" step="0.01" value="0.00" required>
              </div>
            </div>
            <div class="col-md-3 mb-3">
              <label for="precio_pastilla" class="form-label">Precio Pastilla</label>
              <div class="input-group">
                <span class="input-group-text">XAF</span>
                <input type="number" class="form-control" id="precio_pastilla" name="precio_pastilla" step="0.01" value="0.00" required>
              </div>
            </div>
          </div>

          <h6 class="mt-4 text-primary">Conversión de Unidades</h6>
          <p class="text-muted small">Define cuántas unidades menores contiene una unidad mayor. Si no aplica, dejar en 0.</p>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="tiras_por_caja" class="form-label">Tiras por Caja</label>
              <input type="number" class="form-control" id="tiras_por_caja" name="tiras_por_caja" min="0" value="0">
            </div>
            <div class="col-md-4 mb-3">
              <label for="pastillas_por_tira" class="form-label">Pastillas por Tira</label>
              <input type="number" class="form-control" id="pastillas_por_tira" name="pastillas_por_tira" min="0" value="0">
            </div>
            <div class="col-md-4 mb-3">
              <label for="pastillas_por_frasco" class="form-label">Pastillas por Frasco</label>
              <input type="number" class="form-control" id="pastillas_por_frasco" name="pastillas_por_frasco" min="0" value="0">
            </div>
          </div>
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
      <form action="acciones/productos_farmacia_crud.php?action=editar" method="POST">
        <div class="modal-body p-4">
          <input type="hidden" id="edit-id" name="id">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="edit-nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="edit-nombre" name="nombre" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="edit-codigo_barras" class="form-label">Código de Barras</label>
              <input type="text" class="form-control" id="edit-codigo_barras" name="codigo_barras">
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
            <div class="col-md-6 mb-3">
              <label class="form-label">Stock Actual</label>
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                <input type="text" class="form-control" id="edit-stock-display" readonly style="background-color: #e9ecef;">
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Precios de Venta Actuales</label>
              <div class="input-group">
                <span class="input-group-text">XAF</span>
                <input type="text" class="form-control" id="edit-prices-venta-display" readonly style="background-color: #e9ecef;">
              </div>
            </div>
          </div>

          <h6 class="mt-4 text-primary">Conversión de Unidades Actual</h6>
          <p class="text-muted small">Las unidades contenidas se actualizan con las compras.</p>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Tiras por Caja</label>
              <input type="text" class="form-control" id="edit-tiras_por_caja" readonly style="background-color: #e9ecef;">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Pastillas por Tira</label>
              <input type="text" class="form-control" id="edit-pastillas_por_tira" readonly style="background-color: #e9ecef;">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Pastillas por Frasco</label>
              <input type="text" class="form-control" id="edit-pastillas_por_frasco" readonly style="background-color: #e9ecef;">
            </div>
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
      const codigo_barras = button.getAttribute('data-codigo_barras'); 
      const stock_caja = button.getAttribute('data-stock_caja');
      const stock_frasco = button.getAttribute('data-stock_frasco');
      const stock_tira = button.getAttribute('data-stock_tira');
      const stock_pastilla = button.getAttribute('data-stock_pastilla');
      const precio_caja = button.getAttribute('data-precio_caja');
      const precio_frasco = button.getAttribute('data-precio_frasco');
      const precio_tira = button.getAttribute('data-precio_tira');
      const precio_pastilla = button.getAttribute('data-precio_pastilla');
      const fecha_vencimiento = button.getAttribute('data-fecha_vencimiento');
      const tiras_por_caja = button.getAttribute('data-tiras_por_caja');
      const pastillas_por_tira = button.getAttribute('data-pastillas_por_tira');
      const pastillas_por_frasco = button.getAttribute('data-pastillas_por_frasco');


      const modalTitle = modalEditarProducto.querySelector('.modal-title');
      const form = modalEditarProducto.querySelector('form');

      form.querySelector('#edit-id').value = id;
      form.querySelector('#edit-nombre').value = nombre;
      form.querySelector('#edit-descripcion').value = descripcion;
      form.querySelector('#edit-codigo_barras').value = codigo_barras; // Vuelve a agregar el código de barras aquí
      
      // Mostrar stock y precios en campos de solo lectura
      form.querySelector('#edit-stock-display').value = `Caja: ${stock_caja} | Frasco: ${stock_frasco} | Tira: ${stock_tira} | Pastilla: ${stock_pastilla}`;
      form.querySelector('#edit-prices-venta-display').value = `Caja: ${formatXAF(precio_caja)} | Frasco: ${formatXAF(precio_frasco)} | Tira: ${formatXAF(precio_tira)} | Pastilla: ${formatXAF(precio_pastilla)}`;
      
      form.querySelector('#edit-fecha_vencimiento').value = fecha_vencimiento;

      // Mostrar unidades contenidas en campos de solo lectura
      form.querySelector('#edit-tiras_por_caja').value = tiras_por_caja;
      form.querySelector('#edit-pastillas_por_tira').value = pastillas_por_tira;
      form.querySelector('#edit-pastillas_por_frasco').value = pastillas_por_frasco;

      modalTitle.textContent = `Editar Producto #${id} - ${nombre}`;
    });
  }
});
</script>
