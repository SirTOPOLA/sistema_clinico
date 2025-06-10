<?php
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

$pagos = [];
$analiticas = [];
$tipos = [];

$sqlPagos = "SELECT p.id, p.cantidad, tp.nombre AS tipo_prueba, a.codigo_paciente, 
               a.estado, a.resultado, p.fecha_registro
               FROM pagos p
               JOIN analiticas a ON p.id_analitica = a.id
               JOIN tipo_pruebas tp ON p.id_tipo_prueba = tp.id
               ORDER BY p.fecha_registro DESC";
$pagos = $pdo->query($sqlPagos)->fetchAll(PDO::FETCH_ASSOC);



$analiticas = $pdo->query("SELECT a.id, a.codigo_paciente, tp.nombre AS tipo_prueba 
                              FROM analiticas a 
                              JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id")->fetchAll(PDO::FETCH_ASSOC);

$tipos = $pdo->query("SELECT id, nombre FROM tipo_pruebas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);



?>


<div class="container-fluid" id="content">

  <!-- Pagos -->
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-credit-card me-2"></i>Gestión de Pagos</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearPago">
        <i class="bi bi-plus-circle me-1"></i>Nuevo Pago
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscadorPago" class="form-control" placeholder="Buscar pago...">
    </div>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaPagos" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Tipo Prueba</th>
            <th>Código Paciente</th>
            <th>Estado</th>
            <th>Resultado</th>
            <th>Cantidad</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pagos as $p): ?>
            <tr data-id="<?= $p['id'] ?>" data-tipo="<?= $p['tipo_prueba'] ?>" data-codigo="<?= $p['codigo_paciente'] ?>"
              data-estado="<?= $p['estado'] ?>" data-resultado="<?= $p['resultado'] ?>"
              data-cantidad="<?= $p['cantidad'] ?>">
              <td><?= $p['id'] ?></td>
              <td><?= htmlspecialchars($p['tipo_prueba']) ?></td>
              <td><?= htmlspecialchars($p['codigo_paciente']) ?></td>
              <td><?= htmlspecialchars($p['estado']) ?></td>
              <td><?= nl2br(htmlspecialchars($p['resultado'])) ?></td>
              <td><?= number_format($p['cantidad'], 2) ?> €</td>
              <td><?= date('d/m/Y H:i', strtotime($p['fecha_registro'])) ?></td>
              <td class="text-nowrap">
                <button class="btn btn-sm btn-outline-primary btn-editar-pago" data-bs-toggle="modal"
                  data-bs-target="#modalEditarPago">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <a href="eliminar_pago.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('¿Eliminar este pago?')">
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