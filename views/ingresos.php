<?php
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Ingresos
$sqlIngresos = "SELECT i.id, i.fecha_ingreso, i.fecha_alta, i.token, 
                CONCAT(p.nombre, ' ', p.apellidos) AS paciente, 
                s.nombre AS sala, i.fecha_registro
                FROM ingresos i
                JOIN pacientes p ON i.id_paciente = p.id
                JOIN salas_ingreso s ON i.id_sala = s.id
                ORDER BY i.fecha_ingreso DESC";
$ingresos = $pdo->query($sqlIngresos)->fetchAll(PDO::FETCH_ASSOC);

 ?>


<div class="container-fluid" id="content">
  <!-- Ingresos -->
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-house-door me-2"></i>Gestión de Ingresos</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearIngreso">
        <i class="bi bi-plus-circle me-1"></i>Nuevo Ingreso
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscadorIngreso" class="form-control" placeholder="Buscar ingreso...">
    </div>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaIngresos" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Paciente</th>
            <th>Sala</th>
            <th>Token</th>
            <th>Fecha Ingreso</th>
            <th>Fecha Alta</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ingresos as $i): ?>
            <tr data-id="<?= $i['id'] ?>" data-id_paciente="<?= $i['paciente'] ?>" data-id_sala="<?= $i['sala'] ?>" data-token="<?= $i['token'] ?>" data-fecha_ingreso="<?= $i['fecha_ingreso'] ?>" data-fecha_alta="<?= $i['fecha_alta'] ?>">
              <td><?= $i['id'] ?></td>
              <td><?= htmlspecialchars($i['paciente']) ?></td>
              <td><?= htmlspecialchars($i['sala']) ?></td>
              <td><?= htmlspecialchars($i['token']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($i['fecha_ingreso'])) ?></td>
              <td><?= $i['fecha_alta'] ? date('d/m/Y H:i', strtotime($i['fecha_alta'])) : 'Pendiente' ?></td>
              <td class="text-nowrap">
                <button class="btn btn-sm btn-outline-primary btn-editar-ingreso" data-bs-toggle="modal" data-bs-target="#modalEditarIngreso">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <a href="eliminar_ingreso.php?id=<?= $i['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este ingreso?')">
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



<!-- Modal Crear Ingreso -->
<div class="modal fade" id="modalCrearIngreso" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="guardar_ingreso.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nuevo Ingreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">

        <div class="col-md-6">
          <label>Paciente</label>
          <select name="id_paciente" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($pacientes as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="col-md-6">
          <label>Sala</label>
          <select name="id_sala" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($salas as $s): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="col-md-6">
          <label>Fecha de Ingreso</label>
          <input type="datetime-local" name="fecha_ingreso" class="form-control" required>
        </div>

        <div class="col-md-6">
          <label>Token (opcional)</label>
          <input type="text" name="token" class="form-control">
        </div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>




<!-- Modal Editar Ingreso -->
<div class="modal fade" id="modalEditarIngreso" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="actualizar_ingreso.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Ingreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id-ingreso">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <div class="col-md-6">
          <label>Paciente</label>
          <select name="id_paciente" id="edit-id_paciente" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($pacientes as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>Sala</label>
          <select name="id_sala" id="edit-id_sala" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($salas as $s): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>Fecha Ingreso</label>
          <input type="datetime-local" name="fecha_ingreso" id="edit-fecha_ingreso" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label>Fecha Alta</label>
          <input type="datetime-local" name="fecha_alta" id="edit-fecha_alta" class="form-control">
        </div>
        <div class="col-md-12">
          <label>Token</label>
          <input type="text" name="token" id="edit-token" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
      </div>
    </form>
  </div>
</div>
