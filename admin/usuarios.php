<?php
include_once('../includes/header.php');

include_once('../includes/navbar.php');
include_once('../includes/sidebar.php');
?>

<?php
$roles = $pdo->query("SELECT * FROM roles ORDER BY id DESC");
$usuarios = $pdo->query("SELECT u.id, u.usuario, u.estado, u.ultima_sesion, CONCAT(e.nombre, ' ', e.apellido) AS empleado
                              FROM usuarios u
                              JOIN empleados e ON u.empleado_id = e.id
                              ORDER BY u.id DESC");
$empleados = $pdo->query("SELECT e.id, e.nombre, e.apellido, e.dni, e.telefono, e.correo, r.nombre AS rol
                               FROM empleados e
                               JOIN roles r ON e.rol_id = r.id
                               ORDER BY e.id DESC");

?>
<div id="content" class="container-fluid py-4">
    <!-- Barra superior -->
    <div
        class="bg-dark text-white rounded-3 shadow-sm p-3 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h4 id="titulo" class="fw-bold mb-0"><i class="bi bi-people me-2"></i>Gestión de Usuarios</h4>


        <div class="input-group" style="max-width: 200px;">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" id="buscador" placeholder="Buscar...">
        </div>

        <div id="btnGroup" class="btn-group" role="group">
            <button class="btn btn-outline-light active" onclick="mostrarTabla('usuarios')">
                <i class="bi bi-person-badge"></i> Usuarios
            </button>
            <button class="btn btn-outline-light" onclick="mostrarTabla('empleados')">
                <i class="bi bi-person-lines-fill"></i> Empleados
            </button>
            <button class="btn btn-outline-light" onclick="mostrarTabla('roles')">
                <i class="bi bi-diagram-3"></i> Roles
            </button>
        </div>
    </div>

    <!-- Tabla Usuarios -->
    <div id="tablaUsuarios" class="table-responsive mb-4">
        <table class="table table-bordered table-hover align-middle text-center shadow-sm">
            <thead class="table-light">
                <tr>
                    <th><i class="bi bi-hash"></i> ID</th>
                    <th><i class="bi bi-person"></i> Usuario</th>
                    <th><i class="bi bi-person-lines-fill"></i> Empleado</th>
                    <th><i class="bi bi-check-circle"></i> Estado</th>
                    <th><i class="bi bi-clock-history"></i> Última sesión</th>
                    <th><i class="bi bi-gear"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?= htmlspecialchars($usuario['id']) ?></td>
                        <td><?= htmlspecialchars($usuario['usuario']) ?></td>
                        <td><?= htmlspecialchars($usuario['empleado']) ?></td>
                        <td><?= $usuario['estado'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>' ?>
                        </td>
                        <td><?= htmlspecialchars($usuario['ultima_sesion']) ?></td>
                        <td>
                            <a href="editar_usuario.php?id=<?= urlencode($usuario['id']) ?>"
                                class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
                            <a href="eliminar_usuario.php?id=<?= urlencode($usuario['id']) ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('¿Eliminar usuario?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
        <div class="d-flex justify-content-between flex-column flex-md-row text-muted small text-center">
            <div id="resumenUsuarios" class="mb-2 mb-md-0 w-100"></div>
            <div id="paginacionUsuarios" class="d-flex justify-content-center w-100"></div>
        </div>
    </div>

    <!-- Tabla Empleados -->
    <div id="tablaEmpleados" class="table-responsive d-none mb-4">
        <table class="table table-bordered table-hover align-middle text-center shadow-sm">
            <thead class="table-light">
                <tr>
                    <th><i class="bi bi-hash"></i> ID</th>
                    <th><i class="bi bi-person"></i> Nombre</th>
                    <th><i class="bi bi-person-bounding-box"></i> Apellido</th>
                    <th><i class="bi bi-credit-card-2-front"></i> DNI</th>
                    <th><i class="bi bi-telephone"></i> Teléfono</th>
                    <th><i class="bi bi-envelope"></i> Correo</th>
                    <th><i class="bi bi-diagram-3"></i> Rol</th>
                    <th><i class="bi bi-gear"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($empleados as $empleado): ?>
                    <tr>
                        <td><?= htmlspecialchars($empleado['id']) ?></td>
                        <td><?= htmlspecialchars($empleado['nombre']) ?></td>
                        <td><?= htmlspecialchars($empleado['apellido']) ?></td>
                        <td><?= htmlspecialchars($empleado['dni']) ?></td>
                        <td><?= htmlspecialchars($empleado['telefono']) ?></td>
                        <td><?= htmlspecialchars($empleado['correo']) ?></td>
                        <td><?= htmlspecialchars($empleado['rol']) ?></td>
                        <td>
                            <a href="editar_empleado.php?id=<?= urlencode($empleado['id']) ?>"
                                class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></a>
                            <a href="eliminar_empleado.php?id=<?= urlencode($empleado['id']) ?>"
                                class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar empleado?')"><i
                                    class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
        <div class="d-flex justify-content-between flex-column flex-md-row text-muted small text-center">
            <div id="resumenEmpleados" class="mb-2 mb-md-0 w-100"></div>
            <div id="paginacionEmpleados" class="d-flex justify-content-center w-100"></div>
        </div>
    </div>

    <!-- Tabla Roles -->
    <div id="tablaRoles" class="table-responsive d-none mb-4">
        <table class="table table-bordered table-hover align-middle text-center shadow-sm">
            <thead class="table-light">
                <tr>
                    <th><i class="bi bi-hash"></i> ID</th>
                    <th><i class="bi bi-shield-lock"></i> Nombre del Rol</th>
                    <th><i class="bi bi-gear"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $rol): ?>
                    <tr>
                        <td><?= htmlspecialchars($rol['id']) ?></td>
                        <td><?= htmlspecialchars($rol['nombre']) ?></td>
                        <td>
                            <a href="editar_rol.php?id=<?= urlencode($rol['id']) ?>" class="btn btn-sm btn-warning"><i
                                    class="bi bi-pencil-square"></i></a>
                            <a href="eliminar_rol.php?id=<?= urlencode($rol['id']) ?>" class="btn btn-sm btn-danger"
                                onclick="return confirm('¿Eliminar rol?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
        <div class="d-flex justify-content-between flex-column flex-md-row text-muted small text-center">
            <div id="resumenRoles" class="mb-2 mb-md-0 w-100"></div>
            <div id="paginacionRoles" class="d-flex justify-content-center w-100"></div>
        </div>
    </div>
</div>

 
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="formNuevoUsuario" method="POST" class="modal-content shadow-lg border-0 rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title d-flex align-items-center gap-2" id="modalCrearUsuarioLabel">
          <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body px-4 pt-4 pb-0">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="usuario" class="form-label fw-semibold">
              <i class="bi bi-person-circle me-1 text-primary"></i> Usuario
            </label>
            <input type="text" name="usuario" class="form-control rounded-pill" required>
          </div>
          <div class="col-md-6">
            <label for="contrasena" class="form-label fw-semibold">
              <i class="bi bi-lock-fill me-1 text-primary"></i> Contraseña
            </label>
            <input type="password" name="contrasena" class="form-control rounded-pill" required>
          </div>
          <div class="col-md-12">
            <label for="empleado_id" class="form-label fw-semibold">
              <i class="bi bi-person-badge-fill me-1 text-primary"></i> Empleado
            </label>
            <select name="empleado_id" class="form-select rounded-pill" required>
              <option value="">Seleccione</option>
              <?php foreach ($empleados as $emp): ?>
                <option value="<?= $emp['id'] ?>"><?= $emp['nombre'] . ' ' . $emp['apellido'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label for="estado" class="form-label fw-semibold">
              <i class="bi bi-toggle-on me-1 text-primary"></i> Estado
            </label>
            <select name="estado" class="form-select rounded-pill">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light rounded-bottom-4 px-4 py-3">
        <button type="submit" class="btn btn-success rounded-pill px-4">
          <i class="bi bi-save2-fill me-1"></i> Guardar
        </button>
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cancelar
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Crear Empleado -->
 <div class="modal fade" id="modalCrearEmpleado" tabindex="-1" aria-labelledby="modalCrearEmpleadoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <form id="formNuevoEmpleado" method="POST" class="modal-content shadow-lg border-0 rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title d-flex align-items-center gap-2" id="modalCrearEmpleadoLabel">
          <i class="bi bi-person-fill-add"></i> Nuevo Empleado
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body px-4 pt-4 pb-0">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-person-fill me-1 text-primary"></i> Nombre
            </label>
            <input type="text" name="nombre" class="form-control rounded-pill" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-person-lines-fill me-1 text-primary"></i> Apellido
            </label>
            <input type="text" name="apellido" class="form-control rounded-pill" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-credit-card-2-front-fill me-1 text-primary"></i> DNI
            </label>
            <input type="text" name="dni" class="form-control rounded-pill" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-telephone-fill me-1 text-primary"></i> Teléfono
            </label>
            <input type="text" name="telefono" class="form-control rounded-pill">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-envelope-fill me-1 text-primary"></i> Correo
            </label>
            <input type="email" name="correo" class="form-control rounded-pill">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">
              <i class="bi bi-briefcase-fill me-1 text-primary"></i> Rol
            </label>
            <select name="rol_id" class="form-select rounded-pill" required>
              <option value="">Seleccione</option>
              <?php foreach ($roles as $rol): ?>
                <option value="<?= $rol['id'] ?>"><?= $rol['nombre'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer bg-light rounded-bottom-4 px-4 py-3">
        <button type="submit" class="btn btn-success rounded-pill px-4">
          <i class="bi bi-save-fill me-1"></i> Guardar
        </button>
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cancelar
        </button>
      </div>
    </form>
  </div>
</div>


<!-- Modal Crear Rol -->
<div class="modal fade" id="modalCrearRol" tabindex="-1" aria-labelledby="modalCrearRolLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="formNuevoRol" method="POST" class="modal-content shadow-lg border-0 rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title d-flex align-items-center gap-2" id="modalCrearRolLabel">
          <i class="bi bi-shield-lock-fill"></i> Nuevo Rol
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body px-4 pt-4 pb-0">
        <div class="mb-3">
          <label class="form-label fw-semibold">
            <i class="bi bi-tag-fill me-1 text-primary"></i> Nombre del Rol
          </label>
          <input type="text" name="nombre" class="form-control rounded-pill" required placeholder="Ej. Recepcionista">
        </div>
      </div>

      <div class="modal-footer bg-light rounded-bottom-4 px-4 py-3">
        <button type="submit" class="btn btn-success rounded-pill px-4">
          <i class="bi bi-check-circle-fill me-1"></i> Guardar
        </button>
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cancelar
        </button>
      </div>
    </form>
  </div>
</div>


<script>
  function mostrarTabla(tabla) {
    const tablas = {
        usuarios: "tablaUsuarios",
        empleados: "tablaEmpleados",
        roles: "tablaRoles"
    };

    // Ocultar todas
    Object.values(tablas).forEach(id => document.getElementById(id).classList.add("d-none"));
    document.querySelectorAll(".btn-group .btn").forEach(btn => btn.classList.remove("active"));

    const btnGroup = document.getElementById("btnGroup");
    const botonExistente = document.getElementById("btnNuevoItem");
    if (botonExistente) botonExistente.remove();

    let titulo = '';
    let modalId = '';

    if (tabla === "usuarios") {
        document.getElementById(tablas.usuarios).classList.remove("d-none");
        document.querySelectorAll(".btn-group .btn")[0].classList.add("active");
        titulo = "Gestión de Usuarios";
        modalId = "#modalCrearUsuario";
    } else if (tabla === "empleados") {
        document.getElementById(tablas.empleados).classList.remove("d-none");
        document.querySelectorAll(".btn-group .btn")[1].classList.add("active");
        titulo = "Gestión de Empleados";
        modalId = "#modalCrearEmpleado";
    } else if (tabla === "roles") {
        document.getElementById(tablas.roles).classList.remove("d-none");
        document.querySelectorAll(".btn-group .btn")[2].classList.add("active");
        titulo = "Gestión de Roles";
        modalId = "#modalCrearRol";
    }

    document.getElementById('titulo').textContent = titulo;

    const nuevoBtn = document.createElement('button');
    nuevoBtn.className = 'btn btn-secondary';
    nuevoBtn.id = 'btnNuevoItem';
    nuevoBtn.setAttribute('data-bs-toggle', 'modal');
    nuevoBtn.setAttribute('data-bs-target', modalId);
    nuevoBtn.innerHTML = `<i class="bi bi-plus"></i> Nuevo`;
    btnGroup.insertBefore(nuevoBtn, btnGroup.firstChild);
}

document.getElementById('formNuevoUsuario').addEventListener('submit', function(e) {
  e.preventDefault();
  const datos = new FormData(this);

  fetch('api/guardar_usuario.php', {
    method: 'POST',
    body: datos
  })
  .then(res => res.text())
  .then(respuesta => {
    alert(respuesta.trim());
    location.reload();
  }).catch(err => console.error(err));
});

document.getElementById('formNuevoEmpleado').addEventListener('submit', function(e) {
  e.preventDefault();
  const datos = new FormData(this);

  fetch('api/guardar_empleado.php', {
    method: 'POST',
    body: datos
  })
  .then(res => res.text())
  .then(respuesta => {
    alert(respuesta.trim());
    location.reload();
  }).catch(err => console.error(err));
});

document.getElementById('formNuevoRol').addEventListener('submit', function(e) {
  e.preventDefault();
  const datos = new FormData(this);

  fetch('api/guardar_rol.php', {
    method: 'POST',
    body: datos
  })
  .then(res => res.text())
  .then(respuesta => {
    alert(respuesta.trim());
    location.reload();
  }).catch(err => console.error(err));
});

</script>




<?php
include_once('../includes/footer.php');
?>