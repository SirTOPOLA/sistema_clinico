<?php
 
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Obtener detalles de consulta
$sql = "SELECT d.*, c.motivo_consulta, p.nombre AS pac_nom, p.apellidos AS pac_ape
        FROM detalle_consulta d
        LEFT JOIN consultas c ON d.id_consulta = c.id
        LEFT JOIN pacientes p ON c.id_paciente = p.id
        ORDER BY d.fecha_registro DESC";
$detalles = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de consultas para selects
$consultas = $pdo->query(
  "SELECT c.id, p.nombre, p.apellidos 
   FROM consultas c
   JOIN pacientes p ON c.id_paciente = p.id 
   ORDER BY c.fecha_registro DESC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container-fluid" id="content">
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-card-list me-2"></i>Detalle de Consultas</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrear">
        <i class="bi bi-plus-circle me-1"></i> Nuevo Registro
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscador" class="form-control" placeholder="Buscar detalle...">
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body table-responsive">
      <table class="table table-hover table-bordered table-sm align-middle" id="tablaDetalle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Consulta (Paciente)</th>
            <th>Operación</th>
            <th>Orina</th>
            <th>Defeca / días</th>
            <th>Duerme / horas</th>
            <th>Alergia</th>
            <th>Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($detalles as $d): ?>
            <tr
              data-id="<?= $d['id'] ?>"
              data-consulta="<?= $d['id_consulta'] ?>"
              data-operacion="<?= htmlspecialchars($d['operacion'], ENT_QUOTES) ?>"
              data-orina="<?= htmlspecialchars($d['orina'], ENT_QUOTES) ?>"
              data-defeca="<?= htmlspecialchars($d['defeca'], ENT_QUOTES) ?>"
              data-defeca_dias="<?= $d['defeca_dias'] ?>"
              data-duerme="<?= htmlspecialchars($d['duerme'], ENT_QUOTES) ?>"
              data-duerme_horas="<?= $d['duerme_horas'] ?>"
              data-alergico="<?= htmlspecialchars($d['alergico'], ENT_QUOTES) ?>"
            >
              <td><?= $d['id'] ?></td>
              <td><?= htmlspecialchars($d['motivo_consulta'] . ' (' . $d['pac_nom'] . ' ' . $d['pac_ape'] . ')') ?></td>
              <td><?= nl2br(htmlspecialchars($d['operacion'])) ?></td>
              <td><?= htmlspecialchars($d['orina']) ?></td>
              <td><?= htmlspecialchars($d['defeca']) ?> / <?= (int)$d['defeca_dias'] ?></td>
              <td><?= htmlspecialchars($d['duerme']) ?> / <?= (int)$d['duerme_horas'] ?></td>
              <td><?= nl2br(htmlspecialchars($d['alergico'])) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($d['fecha_registro'])) ?></td>
              <td class="text-nowrap">
                <button class="btn btn-sm btn-outline-primary btn-editar"
                        data-bs-toggle="modal" data-bs-target="#modalEditar"
                        title="Editar">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <a href="eliminar_detalle.php?id=<?= $d['id'] ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('¿Eliminar este registro?')">
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


<!-- Modal Crear -->
<div class="modal fade" id="modalCrear" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="guardar_detalle.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-chevron-double-right me-2"></i>Nuevo Detalle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">

        <div class="col-md-6">
          <label>Consulta</label>
          <select name="id_consulta" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($consultas as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['motivo_consulta'] . ' – ' . $c['nombre'] . ' ' . $c['apellidos']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6"><label>Operación</label><textarea name="operacion" class="form-control"></textarea></div>
        <div class="col-md-3"><label>Orina</label><input type="text" name="orina" class="form-control"></div>
        <div class="col-md-3"><label>Defeca</label><input type="text" name="defeca" class="form-control"></div>
        <div class="col-md-3"><label>Días defecando</label><input type="number" name="defeca_dias" class="form-control"></div>
        <div class="col-md-3"><label>Duerme</label><input type="text" name="duerme" class="form-control"></div>
        <div class="col-md-3"><label>Horas durmiendo</label><input type="number" name="duerme_horas" class="form-control"></div>
        <div class="col-md-6"><label>Alergias</label><textarea name="alergico" class="form-control"></textarea></div>
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
    <form action="actualizar_detalle.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Detalle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">

        <div class="col-md-6">
          <label>Consulta</label>
          <select name="id_consulta" id="edit-id_consulta" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($consultas as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['motivo_consulta'] . ' – ' . $c['nombre'] . ' ' . $c['apellidos']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6"><label>Operación</label><textarea name="operacion" id="edit-operacion" class="form-control"></textarea></div>
        <div class="col-md-3"><label>Orina</label><input type="text" name="orina" id="edit-orina" class="form-control"></div>
        <div class="col-md-3"><label>Defeca</label><input type="text" name="defeca" id="edit-defeca" class="form-control"></div>
        <div class="col-md-3"><label>Días defecando</label><input type="number" name="defeca_dias" id="edit-defeca_dias" class="form-control"></div>
        <div class="col-md-3"><label>Duerme</label><input type="text" name="duerme" id="edit-duerme" class="form-control"></div>
        <div class="col-md-3"><label>Horas durmiendo</label><input type="number" name="duerme_horas" id="edit-duerme_horas" class="form-control"></div>
        <div class="col-md-6"><label>Alergias</label><textarea name="alergico" id="edit-alergico" class="form-control"></textarea></div>
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
  modal.addEventListener('show.bs.modal', event => {
    const btn = event.relatedTarget;
    document.getElementById('edit-id').value = btn.dataset.id;
    document.getElementById('edit-id_consulta').value = btn.dataset.consulta;
    document.getElementById('edit-operacion').value = btn.dataset.operacion;
    document.getElementById('edit-orina').value = btn.dataset.orina;
    document.getElementById('edit-defeca').value = btn.dataset.defeca;
    document.getElementById('edit-defeca_dias').value = btn.dataset.defeca_dias;
    document.getElementById('edit-duerme').value = btn.dataset.duerme;
    document.getElementById('edit-duerme_horas').value = btn.dataset.duerme_horas;
    document.getElementById('edit-alergico').value = btn.dataset.alergico;
  });
});
</script>
