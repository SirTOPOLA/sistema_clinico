<?php

$idUsuario = $_SESSION['usuario']['id'] ?? 0;
$pacientes = [];

$sql = "SELECT * FROM pacientes ORDER BY fecha_registro DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);





?>
<div id="content" class="container-fluid">
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0"><i class="bi bi-people-fill me-2"></i>Listado de Pacientes</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrear">
        <i class="bi bi-person-plus-fill me-1"></i> Nuevo Paciente
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscador" class="form-control" placeholder="Buscar paciente...">
    </div>
  </div>



  <?php


if (isset($_SESSION['error'])) {
    echo '<div id="mensaje" class="alert alert-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    echo '<div id="mensaje" class="alert alert-success">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
}
?>







  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaPacientes" class="table table-hover table-bordered align-middle table-sm">
          <thead class="table-light text-nowrap">
            <tr>
              <th>ID</th>
              <th>Paciente</th>
              <th>DIP</th>
              <th>Sexo</th>
              <th>Teléfono</th>
              <th>Email</th>
              <th>Registro</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pacientes as $p): ?>
              <tr data-id="<?= $p['id'] ?>" data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>"
                data-apellidos="<?= htmlspecialchars($p['apellidos'], ENT_QUOTES) ?>"
                data-fecha_nacimiento="<?= $p['fecha_nacimiento'] ?>"
                data-dip="<?= htmlspecialchars($p['dip'], ENT_QUOTES) ?>"
                data-sexo="<?= htmlspecialchars($p['sexo'], ENT_QUOTES) ?>"
                data-direccion="<?= htmlspecialchars($p['direccion'], ENT_QUOTES) ?>"
                data-email="<?= htmlspecialchars($p['email'], ENT_QUOTES) ?>"
                data-telefono="<?= htmlspecialchars($p['telefono'], ENT_QUOTES) ?>"
                data-profesion="<?= htmlspecialchars($p['profesion'], ENT_QUOTES) ?>"
                data-ocupacion="<?= htmlspecialchars($p['ocupacion'], ENT_QUOTES) ?>"
                data-tutor_nombre="<?= htmlspecialchars($p['tutor_nombre'], ENT_QUOTES) ?>"
                data-telefono_tutor="<?= htmlspecialchars($p['telefono_tutor'], ENT_QUOTES) ?>">
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></td>
                <td><?= htmlspecialchars($p['dip']) ?></td>
                <td><?= htmlspecialchars($p['sexo']) ?></td>
                <td><?= htmlspecialchars($p['telefono']) ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($p['fecha_registro'])) ?></td>
                <td class="text-nowrap">
                  <button class="btn btn-sm btn-outline-primary btn-editar" data-bs-toggle="modal"
                    data-bs-target="#modalEditar">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <a href="eliminar_paciente.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('¿Deseas eliminar este paciente?')">
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
</div>


<!-- Modal Crear -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <form action="api/guardar_paciente.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Registrar Nuevo Paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?>">
        <div class="col-md-6"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
        <div class="col-md-6"><label>Apellidos</label><input type="text" name="apellidos" class="form-control" required>
        </div>
        <div class="col-md-4"><label>Fecha de Nacimiento</label><input type="date" name="fecha_nacimiento"
            class="form-control"></div>
        <div class="col-md-4"><label>DIP</label><input type="text" name="dip" class="form-control"></div>
        <div class="col-md-4"><label>Sexo</label>
          <select name="sexo" class="form-select">
            <option value="">Seleccionar</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
          </select>
        </div>
        <div class="col-md-6"><label>Email</label><input type="email" name="email" class="form-control"></div>
        <div class="col-md-6"><label>Teléfono</label><input type="text" name="telefono" class="form-control"></div>
        <div class="col-md-6"><label>Profesión</label><input type="text" name="profesion" class="form-control"></div>
        <div class="col-md-6"><label>Ocupación</label><input type="text" name="ocupacion" class="form-control"></div>
        <div class="col-md-6"><label>Nombre del Tutor</label><input type="text" name="tutor_nombre"
            class="form-control"></div>
        <div class="col-md-6"><label>Teléfono del Tutor</label><input type="text" name="telefono_tutor"
            class="form-control"></div>
        <div class="col-md-12"><label>Dirección</label><textarea name="direccion" class="form-control"></textarea></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-success">Guardar</button>
      </div>
    </form>
  </div>
</div>



<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <form action="api/actualizar_paciente.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Editar Paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?>">
        <div class="col-md-6"><label>Nombre</label><input type="text" name="nombre" id="edit_nombre"
            class="form-control" required></div>
        <div class="col-md-6"><label>Apellidos</label><input type="text" name="apellidos" id="edit_apellidos"
            class="form-control" required></div>
        <div class="col-md-4"><label>Fecha de Nacimiento</label><input type="date" name="fecha_nacimiento"
            id="edit_fecha_nacimiento" class="form-control"></div>
        <div class="col-md-4"><label>DIP</label><input type="text" name="dip" id="edit_dip" class="form-control"></div>
        <div class="col-md-4"><label>Sexo</label>
          <select name="sexo" id="edit_sexo" class="form-select">
            <option value="">Seleccionar</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
          </select>
        </div>
        <div class="col-md-6"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control">
        </div>
        <div class="col-md-6"><label>Teléfono</label><input type="text" name="telefono" id="edit_telefono"
            class="form-control"></div>
        <div class="col-md-6"><label>Profesión</label><input type="text" name="profesion" id="edit_profesion"
            class="form-control"></div>
        <div class="col-md-6"><label>Ocupación</label><input type="text" name="ocupacion" id="edit_ocupacion"
            class="form-control"></div>
        <div class="col-md-6"><label>Nombre del Tutor</label><input type="text" name="tutor_nombre"
            id="edit_tutor_nombre" class="form-control"></div>
        <div class="col-md-6"><label>Teléfono del Tutor</label><input type="text" name="telefono_tutor"
            id="edit_telefono_tutor" class="form-control"></div>
        <div class="col-md-12"><label>Dirección</label><textarea name="direccion" id="edit_direccion"
            class="form-control"></textarea></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Actualizar</button>
      </div>
    </form>
  </div>
</div>



<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-editar').forEach(btn => {
      btn.addEventListener('click', () => {
        const tr = btn.closest('tr');
        const fields = ['id', 'nombre', 'apellidos', 'fecha_nacimiento', 'dip', 'sexo', 'email',
          'telefono', 'profesion', 'ocupacion', 'tutor_nombre', 'telefono_tutor', 'direccion'];
        fields.forEach(f => {
          const el = document.getElementById('edit_' + f);
          if (el) el.value = tr.dataset[f];
        });
      });
    });
  });
</script>




<script>
  setTimeout(() => {
    const mensaje = document.getElementById('mensaje');
    if (mensaje) {
      mensaje.style.transition = 'opacity 1s ease';
      mensaje.style.opacity = '0';
      setTimeout(() => mensaje.remove(), 1000);
    }
  }, 10000); // 10 segundos
</script>