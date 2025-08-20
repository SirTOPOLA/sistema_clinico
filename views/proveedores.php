<?php
// Asumiendo que $pdo ya está inicializado y conectado a la base de datos
// y que $_SESSION['usuario']['id'] y $_SESSION['usuario']['rol'] están disponibles.
$idUsuario = $_SESSION['usuario']['id'] ?? 0;
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

// Consulta para proveedores
$sqlProveedores = "SELECT * FROM proveedores ORDER BY fecha_registro DESC";
$proveedores = $pdo->query($sqlProveedores)->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="content" class="container-fluid">
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0"><i class="bi bi-truck me-2"></i>Listado de Proveedores</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearProveedor">
        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Proveedor
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscadorProveedores" class="form-control" placeholder="Buscar proveedor...">
    </div>
  </div>

  <?php if (isset($_SESSION['success_proveedor'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $_SESSION['success_proveedor']; unset($_SESSION['success_proveedor']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error_proveedor'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= $_SESSION['error_proveedor']; unset($_SESSION['error_proveedor']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaProveedores" class="table table-hover table-bordered align-middle table-sm">
          <thead class="table-light text-nowrap">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Contacto</th>
              <th>Teléfono</th>
              <th>Dirección</th>
              <th>Fecha Registro</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($proveedores as $prov): ?>
              <tr>
                <td><?= (int)$prov['id'] ?></td>
                <td><?= htmlspecialchars($prov['nombre']) ?></td>
                <td><?= htmlspecialchars($prov['contacto'] ?? '-') ?></td>
                <td><?= htmlspecialchars($prov['telefono'] ?? '-') ?></td>
                <td><?= htmlspecialchars($prov['direccion'] ?? '-') ?></td>
                <td><?= date('d/m/Y H:i', strtotime($prov['fecha_registro'])) ?></td>
                <td class="text-nowrap">
                  <button
                    class="btn btn-sm btn-outline-primary me-1"
                    title="Editar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditarProveedor"
                    data-id="<?= $prov['id'] ?>"
                    data-nombre="<?= htmlspecialchars($prov['nombre'], ENT_QUOTES) ?>"
                    data-contacto="<?= htmlspecialchars($prov['contacto'], ENT_QUOTES) ?>"
                    data-telefono="<?= htmlspecialchars($prov['telefono'], ENT_QUOTES) ?>"
                    data-direccion="<?= htmlspecialchars($prov['direccion'], ENT_QUOTES) ?>">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <?php if($rol === 'administrador'): ?>
                  <a href="eliminar_proveedor.php?id=<?= $prov['id'] ?>" class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('¿Deseas eliminar este proveedor? Esta acción es irreversible.')" title="Eliminar">
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

<!-- Modal para Crear Proveedor -->
<div class="modal fade" id="modalCrearProveedor" tabindex="-1" aria-labelledby="modalCrearProveedorLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="modalCrearProveedorLabel"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Proveedor</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="api/proveedores_crud.php?action=crear" method="POST">
        <div class="modal-body p-4">
          <div class="mb-3">
            <label for="proveedor-nombre" class="form-label">Nombre del Proveedor <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="proveedor-nombre" name="nombre" required>
          </div>
          <div class="mb-3">
            <label for="proveedor-contacto" class="form-label">Contacto</label>
            <input type="text" class="form-control" id="proveedor-contacto" name="contacto">
          </div>
          <div class="mb-3">
            <label for="proveedor-telefono" class="form-label">Teléfono</label>
            <input type="tel" class="form-control" id="proveedor-telefono" name="telefono">
          </div>
          <div class="mb-3">
            <label for="proveedor-direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="proveedor-direccion" name="direccion">
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Proveedor</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal para Editar Proveedor -->
<div class="modal fade" id="modalEditarProveedor" tabindex="-1" aria-labelledby="modalEditarProveedorLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="modalEditarProveedorLabel"><i class="bi bi-pencil-square me-2"></i>Editar Proveedor</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="api/proveedores_crud.php?action=editar" method="POST">
        <div class="modal-body p-4">
          <input type="hidden" id="edit-proveedor-id" name="id">
          <div class="mb-3">
            <label for="edit-proveedor-nombre" class="form-label">Nombre del Proveedor <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit-proveedor-nombre" name="nombre" required>
          </div>
          <div class="mb-3">
            <label for="edit-proveedor-contacto" class="form-label">Contacto</label>
            <input type="text" class="form-control" id="edit-proveedor-contacto" name="contacto">
          </div>
          <div class="mb-3">
            <label for="edit-proveedor-telefono" class="form-label">Teléfono</label>
            <input type="tel" class="form-control" id="edit-proveedor-telefono" name="telefono">
          </div>
          <div class="mb-3">
            <label for="edit-proveedor-direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="edit-proveedor-direccion" name="direccion">
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-between">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary rounded-pill px-4">Guardar Cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Script para el buscador de proveedores
  const buscadorProveedores = document.getElementById('buscadorProveedores');
  const tablaProveedores = document.getElementById('tablaProveedores');
  if (buscadorProveedores && tablaProveedores) {
    buscadorProveedores.addEventListener('keyup', function() {
      const value = this.value.toLowerCase();
      const rows = tablaProveedores.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
      for (let i = 0; i < rows.length; i++) {
        const rowText = rows[i].textContent.toLowerCase();
        rows[i].style.display = rowText.includes(value) ? '' : 'none';
      }
    });
  }

  // Script para rellenar el modal de edición de proveedor
  const modalEditarProveedor = document.getElementById('modalEditarProveedor');
  if (modalEditarProveedor) {
    modalEditarProveedor.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget; // Botón que activó el modal
      
      const id = button.getAttribute('data-id');
      const nombre = button.getAttribute('data-nombre');
      const contacto = button.getAttribute('data-contacto');
      const telefono = button.getAttribute('data-telefono');
      const direccion = button.getAttribute('data-direccion');

      const modalTitle = modalEditarProveedor.querySelector('.modal-title');
      const form = modalEditarProveedor.querySelector('form');
      
      form.querySelector('#edit-proveedor-id').value = id;
      form.querySelector('#edit-proveedor-nombre').value = nombre;
      form.querySelector('#edit-proveedor-contacto').value = contacto;
      form.querySelector('#edit-proveedor-telefono').value = telefono;
      form.querySelector('#edit-proveedor-direccion').value = direccion;

      modalTitle.textContent = `Editar Proveedor #${id} - ${nombre}`;
    });
  }
});
</script>