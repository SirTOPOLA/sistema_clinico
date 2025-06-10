<?php
// Asegurar que la sesión esté iniciada
 

// Consulta para obtener personal y su usuario (si tiene)
$sql = "SELECT p.*, u.nombre_usuario 
        FROM personal p 
        LEFT JOIN usuarios u ON p.id = u.id_personal 
        ORDER BY p.id DESC";

$stmt = $pdo->query($sql);
$personal = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="content" class="container-fluid py-4">
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0"><i class="bi bi-people-fill me-2"></i>Listado de Personal</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevo">
        <i class="bi bi-person-plus-fill me-1"></i> Nuevo Personal
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscador" class="form-control" placeholder="Buscar personal...">
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaPersonal" class="table table-hover table-bordered align-middle table-sm">
          <thead class="table-light text-nowrap">
            <tr>
              <th><i class="bi bi-hash text-muted"></i> ID</th>
              <th><i class="bi bi-person-badge-fill text-muted"></i> Empleado</th>
              <th><i class="bi bi-envelope-at-fill text-muted"></i> Correo</th>
              <th><i class="bi bi-telephone-fill text-muted"></i> Teléfono</th>
              <th><i class="bi bi-award-fill text-muted"></i> Especialidad</th>
              <th><i class="bi bi-person-circle text-muted"></i> Usuario</th>
              <th><i class="bi bi-calendar-check-fill text-muted"></i> Registro</th>
              <th><i class="bi bi-tools text-muted"></i> Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($personal as $p): ?>
              <tr>
                <td><?= (int) $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></td>
                <td><?= htmlspecialchars($p['correo']) ?></td>
                <td><?= htmlspecialchars($p['telefono']) ?></td>
                <td><?= htmlspecialchars($p['especialidad']) ?></td>
                <td>
                  <?= $p['nombre_usuario'] ? htmlspecialchars($p['nombre_usuario']) : '<span class="text-muted fst-italic">Sin asignar</span>' ?>
                </td>
                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['fecha_registro']))) ?></td>
                <td class="text-nowrap">
                  <button class="btn btn-sm btn-outline-primary me-1 editar-btn" data-id="<?= $p['id'] ?>"
                    data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                    data-apellidos="<?= htmlspecialchars($p['apellidos']) ?>"
                    data-correo="<?= htmlspecialchars($p['correo']) ?>"
                    data-telefono="<?= htmlspecialchars($p['telefono']) ?>"
                    data-especialidad="<?= htmlspecialchars($p['especialidad']) ?>" title="Editar" data-bs-toggle="modal"
                    data-bs-target="#modalEditar">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <a href="eliminar_personal.php?id=<?= $p['id'] ?>"
                    onclick="return confirm('¿Deseas eliminar este registro?')" class="btn btn-sm btn-outline-danger"
                    title="Eliminar">
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

<!-- Modal Nuevo -->
<div class="modal fade" id="modalNuevo" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="guardar_personal.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>Nuevo Personal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Apellidos</label>
              <input type="text" name="apellidos" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Correo</label>
              <input type="email" name="correo" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control">
            </div>
            <div class="col-md-12">
              <label class="form-label">Especialidad</label>
              <input type="text" name="especialidad" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="editar_personal.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Personal</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="editar_id">
          <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
          <div class="row g-2">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" id="editar_nombre" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Apellidos</label>
              <input type="text" name="apellidos" id="editar_apellidos" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Correo</label>
              <input type="email" name="correo" id="editar_correo" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" id="editar_telefono" class="form-control">
            </div>
            <div class="col-md-12">
              <label class="form-label">Especialidad</label>
              <input type="text" name="especialidad" id="editar_especialidad" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Actualizar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.editar-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editar_id').value = btn.dataset.id;
      document.getElementById('editar_nombre').value = btn.dataset.nombre;
      document.getElementById('editar_apellidos').value = btn.dataset.apellidos;
      document.getElementById('editar_correo').value = btn.dataset.correo;
      document.getElementById('editar_telefono').value = btn.dataset.telefono;
      document.getElementById('editar_especialidad').value = btn.dataset.especialidad;
    });
  });
</script>