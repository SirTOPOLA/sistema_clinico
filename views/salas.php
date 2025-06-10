<?php
$idUsuario = $_SESSION['usuario']['id'] ?? 0;
 
// Salas
$salas = $pdo->query("SELECT id, nombre FROM salas_ingreso ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
?>


<div class="container-fluid" id="content">
 
  <!-- Salas -->
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-door-open me-2"></i>Gestión de Salas</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearSala">
        <i class="bi bi-plus-circle me-1"></i>Crear Sala
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscadorSala" class="form-control" placeholder="Buscar sala...">
    </div>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaSalas" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Fecha Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($salas as $s): ?>
            <tr data-id="<?= $s['id'] ?>" data-nombre="<?= $s['nombre'] ?>">
              <td><?= $s['id'] ?></td>
              <td><?= htmlspecialchars($s['nombre']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($s['fecha_registro'])) ?></td>
              <td class="text-nowrap">
                <button class="btn btn-sm btn-outline-primary btn-editar-sala" data-bs-toggle="modal" data-bs-target="#modalEditarSala">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <a href="eliminar_sala.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta sala?')">
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



<!-- Modal Crear Sala -->
<div class="modal fade" id="modalCrearSala" tabindex="-1">
  <div class="modal-dialog">
    <form action="guardar_sala.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nueva Sala</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <div class="col-md-12">
          <label>Nombre de la Sala</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>









<!-- Modal Editar Sala -->
<div class="modal fade" id="modalEditarSala" tabindex="-1">
  <div class="modal-dialog">
    <form action="actualizar_sala.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Sala</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id-sala">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <div class="col-md-12">
          <label>Nombre de la Sala</label>
          <input type="text" name="nombre" id="edit-nombre-sala" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
      </div>
    </form>
  </div>
</div>
