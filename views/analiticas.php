<?php
 
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Listado de analíticas
$sql = "SELECT a.id, a.resultado, a.estado, a.codigo_paciente, a.pagado,
               tp.nombre AS tipo_prueba,
               CONCAT(p.nombre,' ',p.apellidos) AS paciente,
               a.fecha_registro
        FROM analiticas a
        JOIN tipos_prueba tp ON a.id_tipo_prueba = tp.id
        JOIN pacientes p ON a.id_paciente = p.id
        ORDER BY a.fecha_registro DESC";
$analiticas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Datos para selects
$tipos = $pdo->query("SELECT id, nombre FROM tipos_prueba ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
$pacientes = $pdo->query("SELECT id, nombre, apellidos FROM pacientes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container-fluid" id="content">
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-beaker me-2"></i>Gestión de Analíticas</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrear">
        <i class="bi bi-plus-circle me-1"></i>Nueva Analítica
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscador" class="form-control" placeholder="Buscar analítica...">
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaAnaliticas" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Tipo</th>
            <th>Paciente</th>
            <th>Código</th>
            <th>Estado</th>
            <th>Resultado</th>
            <th>Pagado</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($analiticas as $a): ?>
          <tr data-id="<?= $a['id'] ?>"
              data-tipo="<?= $a['tipo_prueba'] ?>"
              data-id_tipo_prueba="<?= htmlspecialchars($a['tipo_prueba'], ENT_QUOTES) ?>"
              data-id_paciente="<?= htmlspecialchars($a['paciente'], ENT_QUOTES) ?>"
              data-codigo="<?= htmlspecialchars($a['codigo_paciente'], ENT_QUOTES) ?>"
              data-estado="<?= htmlspecialchars($a['estado'], ENT_QUOTES) ?>"
              data-resultado="<?= htmlspecialchars($a['resultado'], ENT_QUOTES) ?>"
              data-pagado="<?= $a['pagado'] ?>">
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['tipo_prueba']) ?></td>
            <td><?= htmlspecialchars($a['paciente']) ?></td>
            <td><?= htmlspecialchars($a['codigo_paciente']) ?></td>
            <td><?= htmlspecialchars($a['estado']) ?></td>
            <td><?= nl2br(htmlspecialchars($a['resultado'])) ?></td>
            <td><?= $a['pagado'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>' ?></td>
            <td><?= date('d/m/Y H:i', strtotime($a['fecha_registro'])) ?></td>
            <td class="text-nowrap">
              <button class="btn btn-sm btn-outline-primary btn-editar" data-bs-toggle="modal" data-bs-target="#modalEditar">
                <i class="bi bi-pencil-square"></i>
              </button>
              <a href="eliminar_analitica.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('¿Eliminar esta analítica?')">
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


<!-- Modal Crear -->
<div class="modal fade" id="modalCrear" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="guardar_analitica.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nueva Analítica</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <div class="col-md-6">
          <label>Tipo de Prueba</label>
          <select name="id_tipo_prueba" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($tipos as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>Paciente</label>
          <select name="id_paciente" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($pacientes as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-6"><label>Código de Paciente</label><input type="text" name="codigo_paciente" class="form-control"></div>
        <div class="col-md-6"><label>Estado</label><input type="text" name="estado" class="form-control" required></div>
        <div class="col-md-12"><label>Resultado</label><textarea name="resultado" class="form-control" rows="3" required></textarea></div>
        <div class="col-md-4 form-check form-switch">
          <input class="form-check-input" type="checkbox" name="pagado" id="pagadoCrear" value="1">
          <label class="form-check-label" for="pagadoCrear">Pagado</label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>


<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="actualizar_analitica.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Analítica</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">

        <div class="col-md-6">
          <label>Tipo de Prueba</label>
          <select name="id_tipo_prueba" id="edit-tipo" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($tipos as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="col-md-6">
          <label>Paciente</label>
          <select name="id_paciente" id="edit-paciente" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($pacientes as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="col-md-6"><label>Código de Paciente</label><input type="text" name="codigo_paciente" id="edit-codigo" class="form-control"></div>
        <div class="col-md-6"><label>Estado</label><input type="text" name="estado" id="edit-estado" class="form-control" required></div>
        <div class="col-md-12"><label>Resultado</label><textarea name="resultado" id="edit-resultado" class="form-control" rows="3" required></textarea></div>
        <div class="col-md-4 form-check form-switch">
          <input class="form-check-input" type="checkbox" name="pagado" id="pagadoEditar" value="1">
          <label class="form-check-label" for="pagadoEditar">Pagado</label>
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
  const modal = document.getElementById('modalEditar');
  modal.addEventListener('show.bs.modal', e => {
    const btn = e.relatedTarget;
    document.getElementById('edit-id').value = btn.dataset.id;
    document.getElementById('edit-tipo').value = btn.dataset.id_tipo_prueba;
    document.getElementById('edit-paciente').value = btn.dataset.id_paciente;
    document.getElementById('edit-codigo').value = btn.dataset.codigo || '';
    document.getElementById('edit-estado').value = btn.dataset.estado;
    document.getElementById('edit-resultado').value = btn.dataset.resultado;
    document.getElementById('pagadoEditar').checked = btn.dataset.pagado == 1;
  });
});
</script>
