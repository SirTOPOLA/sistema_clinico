<?php


// Consulta modificada con fecha_solo
$sql = "SELECT 
    a.id, 
    a.resultado, 
    a.estado, 
    a.codigo_paciente, 
    a.pagado,
    tp.nombre AS tipo_prueba,
    CONCAT(p.nombre,' ',p.apellidos) AS paciente,
    a.fecha_registro,
    DATE(a.fecha_registro) AS fecha_solo
FROM analiticas a
JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id
JOIN pacientes p ON a.id_paciente = p.id
ORDER BY a.fecha_registro DESC";

$stmt = $pdo->query($sql);
$analiticas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por paciente y fecha
$grupos = [];
foreach ($analiticas as $a) {
  $clave = $a['paciente'] . '_' . $a['fecha_solo'];

  if (!isset($grupos[$clave])) {
    $grupos[$clave] = [
      'tipo' => $a['tipo_prueba'],
      'paciente' => $a['paciente'],
      'codigo' => $a['codigo_paciente'],
      'fecha' => $a['fecha_solo'],
      'registros' => [],
      'pagos' => [],
    ];
  }

  $grupos[$clave]['registros'][] = $a;
  $grupos[$clave]['pagos'][] = $a['pagado'];
}
?>




<div class="container-fluid" id="content">

  <!-- Pagos -->
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-credit-card me-2"></i>Gestión de Pagos</h3>

    </div>
    <div class="col-md-4">
      <input type="text" id="buscadorPago" class="form-control" placeholder="Buscar pago...">
    </div>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaAnaliticas" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Nombre de la Prueba</th>
            <th>Paciente</th>
            <th>Código</th>

            <th>Pagos</th>

            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($grupos as $grupo): ?>
            <tr>
              <td><?= htmlspecialchars($grupo['id']) ?></td>
              <td><?= htmlspecialchars($grupo['paciente']) ?></td>
              <td><?= htmlspecialchars($grupo['codigo']) ?></td>
              <td>
                <ul class="mb-0">
                  <?php foreach ($grupo['registros'] as $r): ?>
                    <li><?= htmlspecialchars($r['tipo_prueba']) ?></li>
                  <?php endforeach ?>
                </ul>
              </td>
              <td>
                <?php
                $todosConResultado = true;
                foreach ($grupo['registros'] as $r) {
                  if (empty($r['resultado'])) {
                    $todosConResultado = false;
                    break;
                  }
                }
                ?>
                <?php if ($todosConResultado): ?>
                  <span class="badge bg-primary">Resultado</span>
                <?php else: ?>
                  <span class="badge bg-danger">Sin Resultado</span>
                <?php endif; ?>
              </td>
              <td><?= date('d/m/Y', strtotime($grupo['fecha'])) ?></td>
              <td>
                <?php if (!in_array(0, $grupo['pagos'])): ?>
                  <span class="badge bg-success">Pagado</span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark">Pendiente</span>
                <?php endif; ?>
              </td>
              <td>
                <!-- Aquí podrías poner acciones agrupadas si lo deseas -->
                <a href="#" class="btn btn-sm btn-outline-info">Ver Detalles</a>
              </td>
            </tr>
          <?php endforeach ?>
        </tbody>

      </table>
    </div>
  </div>


</div>



<!-- Modal Crear Pago -->
<div class="modal fade" id="modalCrearPago" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="guardar_pago.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nuevo Pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">

        <div class="col-md-6">
          <label>Analítica</label>
          <select name="id_analitica" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($analiticas as $a): ?>
              <option value="<?= $a['id'] ?>">
                <?= "Código: " . htmlspecialchars($a['codigo_paciente']) . " - " . htmlspecialchars($a['tipo_prueba']) ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>

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
          <label>Cantidad (€)</label>
          <input type="number" step="0.01" name="cantidad" class="form-control" required>
        </div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-success"><i class="bi bi-cash-coin me-1"></i> Registrar Pago</button>
      </div>
    </form>
  </div>
</div>


<!-- Modal Editar Pago -->
<div class="modal fade" id="modalEditarPago" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="actualizar_pago.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id-pago">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <div class="col-md-6">
          <label>Analítica</label>
          <select name="id_analitica" id="edit-id_analitica" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($analiticas as $a): ?>
              <option value="<?= $a['id'] ?>"><?= "Código: {$a['codigo_paciente']} - {$a['tipo_prueba']}" ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>Tipo de Prueba</label>
          <select name="id_tipo_prueba" id="edit-id_tipo_prueba" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($tipos as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>Cantidad (€)</label>
          <input type="number" step="0.01" name="cantidad" id="edit-cantidad" class="form-control" required>
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
    const modalEditar = document.getElementById('modalEditarPago');
    modalEditar.addEventListener('show.bs.modal', e => {
      const btn = e.relatedTarget;
      document.getElementById('edit-id-pago').value = btn.dataset.id;
      document.getElementById('edit-cantidad').value = btn.dataset.cantidad;
      document.getElementById('edit-id_tipo_prueba').value = btn.dataset.tipo;
      // Ojo: No estás trayendo el ID de analítica ni ID de tipo de prueba en los data-*
      // Si los necesitas, inclúyelos también en el <tr> y usa aquí como:
      // document.getElementById('edit-id_analitica').value = btn.dataset.id_analitica;
    });
  });
</script>