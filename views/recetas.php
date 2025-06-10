<?php
 $idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Consulta básica (puedes unir con consultas, pacientes si gustas)
$sql = "SELECT r.*, p.nombre AS nombre_paciente, p.apellidos 
        FROM recetas r
        JOIN pacientes p ON r.id_paciente = p.id
        ORDER BY r.fecha_registro DESC";
$recetas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="content" class="container-fluid"> 
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0"><i class="bi bi-clipboard2-plus me-2"></i>Listado de Recetas</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrear">
        <i class="bi bi-file-earmark-plus-fill me-1"></i> Nueva Receta
      </button>
    </div>
    <div class="col-md-4"> 
      <input type="text" id="buscador" class="form-control" placeholder="Buscar receta...">
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaRecetas" class="table table-hover table-bordered align-middle table-sm">
          <thead class="table-light text-nowrap">
            <tr>
              <th>ID</th>
              <th>Paciente</th>
              <th>Código</th>
              <th>Descripción</th>
              <th>Comentario</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recetas as $r): ?>
              <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= htmlspecialchars($r['nombre_paciente'] . ' ' . $r['apellidos']) ?></td>
                <td><?= htmlspecialchars($r['codigo_paciente']) ?></td>
                <td><?= nl2br(htmlspecialchars($r['descripcion'])) ?></td>
                <td><?= nl2br(htmlspecialchars($r['comentario'])) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($r['fecha_registro'])) ?></td>
                <td class="text-nowrap">
                  <button 
                    class="btn btn-sm btn-outline-primary me-1" 
                    title="Editar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditar"
                    data-id="<?= $r['id'] ?>"
                    data-descripcion="<?= htmlspecialchars($r['descripcion'], ENT_QUOTES) ?>"
                    data-comentario="<?= htmlspecialchars($r['comentario'], ENT_QUOTES) ?>"
                    data-id_paciente="<?= $r['id_paciente'] ?>"
                    data-id_consulta="<?= $r['id_consulta'] ?>"
                    data-codigo="<?= $r['codigo_paciente'] ?>"
                  >
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <a href="eliminar_receta.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('¿Deseas eliminar esta receta?')" title="Eliminar">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- MODAL CREAR RECETA -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="guardar_receta.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-file-earmark-plus-fill me-2"></i>Nueva Receta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <div class="col-md-6">
          <label>Paciente</label>
          <select name="id_paciente" class="form-select" required>
            <option value="">Seleccione</option>
            <?php
            $pacientes = $pdo->query("SELECT id, nombre, apellidos FROM pacientes ORDER BY nombre")->fetchAll();
            foreach ($pacientes as $p):
            ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>ID de Consulta</label>
          <input type="number" name="id_consulta" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Código del paciente</label>
          <input type="text" name="codigo_paciente" class="form-control">
        </div>
        <div class="col-md-12">
          <label>Descripción</label>
          <textarea name="descripcion" class="form-control" rows="3" required></textarea>
        </div>
        <div class="col-md-12">
          <label>Comentario</label>
          <textarea name="comentario" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EDITAR RECETA -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="actualizar_receta.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Receta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <div class="col-md-6">
          <label>Paciente</label>
          <select name="id_paciente" id="edit-id_paciente" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($pacientes as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>ID de Consulta</label>
          <input type="number" name="id_consulta" id="edit-id_consulta" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Código del paciente</label>
          <input type="text" name="codigo_paciente" id="edit-codigo_paciente" class="form-control">
        </div>
        <div class="col-md-12">
          <label>Descripción</label>
          <textarea name="descripcion" id="edit-descripcion" class="form-control" rows="3" required></textarea>
        </div>
        <div class="col-md-12">
          <label>Comentario</label>
          <textarea name="comentario" id="edit-comentario" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const modalEditar = document.getElementById('modalEditar');
  modalEditar.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;

    document.getElementById('edit-id').value = button.getAttribute('data-id');
    document.getElementById('edit-descripcion').value = button.getAttribute('data-descripcion');
    document.getElementById('edit-comentario').value = button.getAttribute('data-comentario');
    document.getElementById('edit-id_paciente').value = button.getAttribute('data-id_paciente');
    document.getElementById('edit-id_consulta').value = button.getAttribute('data-id_consulta');
    document.getElementById('edit-codigo_paciente').value = button.getAttribute('data-codigo');
  });
});
</script>
