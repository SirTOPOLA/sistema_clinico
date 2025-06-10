<?php
// Consulta para el listado

$sql = "SELECT u.id, 
        u.nombre_usuario AS usuario, 
        CONCAT(p.nombre, ' ', p.apellidos) AS personal, 
        u.fecha_registro AS ingreso,
        r.nombre AS rol
        FROM usuarios u
        JOIN personal p ON u.id_personal = p.id
        JOIN roles r ON u.id_rol = r.id";
$usuarios = $pdo->query($sql);


// Para selects del modal (guardamos en arrays para reutilizar)
$empleados = $pdo->query("SELECT id, nombre, especialidad FROM personal")->fetchAll(PDO::FETCH_ASSOC);
$roles = $pdo->query("SELECT id, nombre FROM roles")->fetchAll(PDO::FETCH_ASSOC);
?>


<div id="content" class="container-fluid py-4">
  <div class="thead sticky-top bg-white pb-2" style="top: 60px; z-index: 1040; border-bottom: 1px solid #dee2e6;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
      <h3 class="mb-3 mb-md-0">
        <i class="bi bi-person-gear me-2 text-primary"></i> Gestión de Usuarios
      </h3>
      <div class="d-flex gap-2">
        <input type="text" class="form-control form-control-sm" placeholder="Buscar usuario...">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
          <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
        </button>
      </div>
    </div>
  </div>

  <div class="table-responsive">
  <table id="tablaUsuarios" class="table table-hover table-bordered align-middle table-sm">

      <thead class="table-light text-nowrap">
        <tr>
          <th><i class="bi bi-hash me-1 text-muted"></i>ID</th>
          <th><i class="bi bi-person-badge me-1 text-muted"></i>Empleado</th>
          <th><i class="bi bi-person me-1 text-muted"></i>Usuario</th>
          <th><i class="bi bi-shield-lock me-1 text-muted"></i>Rol</th>
          <th><i class="bi bi-toggle-on me-1 text-muted"></i>Creado</th>
          <th><i class="bi bi-tools me-1 text-muted"></i>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($u = $usuarios->fetch(PDO::FETCH_ASSOC)): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><?= htmlspecialchars($u['personal']) ?></td>
            <td><?= htmlspecialchars($u['usuario']) ?></td>
            <td><?= htmlspecialchars($u['rol']) ?></td> 
              <td><?= htmlspecialchars($u['ingreso']) ?></td> 
            <td class="text-nowrap">
              <!-- Botón editar -->
              <button class="btn btn-sm btn-outline-warning me-1"
               data-bs-toggle="modal" data-bs-target="#modalEditar"
                data-id="<?= $u['id'] ?>" 
                data-usuario="<?= $u['usuario'] ?>" >
                <i class="bi bi-pencil-square"></i>
              </button>


            </td>

          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>


<!-- Modal Registro -->
<div class="modal fade" id="modalRegistro" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content needs-validation" novalidate action="api/guardar_usuario.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-person-add"></i> Registrar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-person-vcard"></i> Empleado</label>
          <select class="form-select" name="empleado_id" required>
            <option value="">Seleccione...</option>
            <?php foreach ($empleados as $e): ?>
              <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?> (<?= htmlspecialchars($e['especialidad']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-person"></i> Nombre de usuario</label>
          <input type="text" name="nombre" class="form-control" required maxlength="25">
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-lock"></i> Contraseña</label>
          <input type="password" name="contrasena" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
          <label class="form-label"><i class="bi bi-shield-lock"></i> Rol</label>
          <select name="rol_id" class="form-select" required>
            <option value="">Seleccione...</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">
          <i class="bi bi-save"></i> Guardar
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" action="api/editar_usuario.php" method="POST">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="edit-id">
        <div class="mb-3">
          <label class="form-label">Nombre de usuario</label>
          <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Estado</label>
          <select name="estado" id="edit-estado" class="form-select" required>
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Rol</label>
          <select name="rol_id" class="form-select" required>
            <?php foreach ($roles as $r): ?>
              <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?> (<?= htmlspecialchars($e['especialidad']) ?>) </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-arrow-repeat"></i> Actualizar
        </button>
      </div>
    </form>
  </div>
</div>

 
<script>
 
 /*  $(document).ready(function () {
    $('#tablaUsuarios').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
      },
      lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
      pageLength: 10,
      ordering: true,
      responsive: true,
      info: true,
      autoWidth: false
    });
  });
  */

 



document.addEventListener('DOMContentLoaded', () => {
  // Validación de formularios
  (() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
      form.addEventListener('submit', e => {
        if (!form.checkValidity()) {
          e.preventDefault();
          e.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  })();

  // Rellenar modal editar
  const modalEditar = document.getElementById('modalEditar');
  if (modalEditar) {
    modalEditar.addEventListener('show.bs.modal', e => {
      const btn = e.relatedTarget;
      document.getElementById('edit-id').value = btn.getAttribute('data-id');
      document.getElementById('edit-nombre').value = btn.getAttribute('data-usuario');
      document.getElementById('edit-estado').value = btn.getAttribute('data-estado');
    });
  }
});


 
</script>