<?php
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Listado de tipos de prueba
$sql = "SELECT id, nombre, precio, fecha_registro FROM tipo_pruebas ORDER BY fecha_registro DESC";
$tipos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid" id="content">
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-vial me-2"></i>Gestión de Tipos de Prueba</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearTipo">
        <i class="bi bi-plus-circle me-1"></i>Nuevo Tipo
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscador" class="form-control" placeholder="Buscar tipo de prueba...">
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaTipos" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Precio</th>
            <th>Fecha de Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tipos as $t): ?>
            <tr data-id="<?= $t['id'] ?>"
                data-nombre="<?= htmlspecialchars($t['nombre'], ENT_QUOTES) ?>"
                data-precio="<?= $t['precio'] ?>">
              <td><?= $t['id'] ?></td>
              <td><?= htmlspecialchars($t['nombre']) ?></td>
              <td><?= number_format($t['precio'], 2) ?> €</td>
              <td><?= date('d/m/Y H:i', strtotime($t['fecha_registro'])) ?></td>
              <td class="text-nowrap">
                <button class="btn btn-sm btn-outline-primary btn-editar-tipo" data-bs-toggle="modal" data-bs-target="#modalEditarTipo">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <a href="eliminar_tipo_prueba.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este tipo de prueba?')">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Crear Tipo de Prueba -->
<div class="modal fade" id="modalCrearTipo" tabindex="-1">
  <div class="modal-dialog">
    <form action="guardar_tipo_prueba.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nuevo Tipo de Prueba</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <div class="col-md-12">
          <label>Nombre</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="col-md-12">
          <label>Precio (€)</label>
          <input type="number" name="precio" class="form-control" step="0.01" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Editar Tipo de Prueba -->
<div class="modal fade" id="modalEditarTipo" tabindex="-1">
  <div class="modal-dialog">
    <form action="actualizar_tipo_prueba.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Tipo de Prueba</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id-tipo">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <div class="col-md-12">
          <label>Nombre</label>
          <input type="text" name="nombre" id="edit-nombre-tipo" class="form-control" required>
        </div>
        <div class="col-md-12">
          <label>Precio (€)</label>
          <input type="number" name="precio" id="edit-precio-tipo" class="form-control" step="0.01" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalTipo = document.getElementById('modalEditarTipo');
  modalTipo.addEventListener('show.bs.modal', e => {
    const btn = e.relatedTarget;
    document.getElementById('edit-id-tipo').value = btn.closest('tr').dataset.id;
    document.getElementById('edit-nombre-tipo').value = btn.closest('tr').dataset.nombre;
    document.getElementById('edit-precio-tipo').value = btn.closest('tr').dataset.precio;
  });
});
</script>
