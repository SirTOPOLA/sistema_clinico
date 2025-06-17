<?php
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Ingresos
$sqlIngresos = "SELECT i.id, i.fecha_ingreso, i.fecha_alta, i.numero_cama, 
                CONCAT(p.nombre, ' ', p.apellidos) AS paciente, 
                s.nombre AS sala, i.fecha_registro
                FROM ingresos i
                JOIN pacientes p ON i.id_paciente = p.id
                JOIN salas_ingreso s ON i.id_sala = s.id
                ORDER BY i.fecha_ingreso DESC";
$ingresos = $pdo->query($sqlIngresos)->fetchAll(PDO::FETCH_ASSOC);


//trayendo las salas..
$stmt = $pdo->prepare("SELECT id, nombre FROM salas_ingreso ORDER BY nombre ASC");
$stmt->execute();
$salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

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




  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-1"></i> Ingreso guardado correctamente.
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($_GET['error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>







  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaIngresos" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Paciente</th>
            <th>Sala</th>
            <th>Numero de Cama</th>
            <th>Fecha Ingreso</th>
            <th>Fecha Alta</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ingresos as $i): ?>
            <tr data-id="<?= $i['id'] ?>" data-id_paciente="<?= $i['paciente'] ?>" data-id_sala="<?= $i['sala'] ?>" data-token="<?= $i['numero_cama'] ?>" data-fecha_ingreso="<?= $i['fecha_ingreso'] ?>" data-fecha_alta="<?= $i['fecha_alta'] ?>">
              <td><?= $i['id'] ?></td>
              <td><?= htmlspecialchars($i['paciente']) ?></td>
              <td><?= htmlspecialchars($i['sala']) ?></td>
              <td><?= htmlspecialchars($i['numero_cama']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($i['fecha_ingreso'])) ?></td>

              <td>
                <?php if ($i['fecha_alta']): ?>
                  <span class="badge bg-success">Se le dio de alta</span><br>
                  <small class="text-muted"><?= date('d/m/Y H:i', strtotime($i['fecha_alta'])) ?></small>
                <?php else: ?>
                  <span class="badge bg-danger">Ingresado</span>
                <?php endif; ?>
              </td>



              <td class="text-nowrap">

                <?php
                $fechaAltaExistente = !empty($i['fecha_alta']);
                ?>

                <?php if (!$fechaAltaExistente): ?>
                  <button
                    class="btn btn-sm btn-outline-primary btn-editar-ingreso"
                    data-id="<?= $i['id'] ?>"
                    data-sala="<?= $i['sala'] ?>"
                    data-cama="<?= htmlspecialchars($i['numero_cama']) ?>"
                    data-fecha="<?= date('Y-m-d\TH:i', strtotime($i['fecha_ingreso'])) ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditarIngreso"
                    title="Editar Ingreso">
                    <i class="bi bi-pencil-square"></i>
                  </button>

                  <button
                    class="btn btn-sm btn-outline-success btn-alta"
                    data-bs-toggle="modal"
                    data-bs-target="#modalAlta"
                    data-id="<?= $i['id'] ?>">
                    <i class="bi bi-check-circle me-1"></i> Dar de Alta
                  </button>
                <?php endif; ?>




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






<div class="modal fade" id="modalAlta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formAlta" action="api/alta_paciente.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-calendar-check me-2"></i>Fecha de Alta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_ingreso" id="id_ingreso_alta">
        <div class="mb-3">
          <label for="fecha_alta" class="form-label">Seleccionar fecha y hora de alta</label>
          <input type="datetime-local" name="fecha_alta" id="fecha_alta" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Guardar Alta</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>






<!-- Modal Crear Ingreso -->
<div class="modal fade" id="modalCrearIngreso" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="api/guardar_ingreso.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nuevo Ingreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <input type="hidden" name="id_paciente" id="id_paciente_seleccionado">

        <!-- Buscador de pacientes -->
        <div class="col-md-12">
          <label>Buscar Paciente (por nombre, apellidos o código)</label>
          <input type="text" id="buscadorPacienteIngreso" class="form-control" placeholder="Escriba para buscar...">
          <div id="resultadosPacientesIngreso" class="mt-2"></div>
          <div id="pacienteSeleccionadoIngreso" class="alert alert-info mt-3 d-none"></div>
        </div>

        <div class="col-md-12">
          <label>Salas de la clinica</label>
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
          <label>numero de cama</label>
          <input type="number" name="cama" class="form-control" min="1" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
      </div>
    </form>

  </div>
</div>




<!-- MODAL EDITAR INGRESO -->
<div class="modal fade" id="modalEditarIngreso" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form action="api/actualizar_ingreso.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Ingreso</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body row g-3">
        <input type="hidden" name="id_ingreso" id="editar_id_ingreso">

        <div class="col-md-12">
          <label class="form-label">Sala</label>
          <select name="id_sala" id="editar_id_sala" class="form-select" required>
            <?php foreach ($salas as $s): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>



        <div class="col-md-12">
          <label class="form-label">Número de Cama</label>
          <input type="text" name="numero_cama" id="editar_numero_cama" class="form-control" required>
        </div>

        <div class="col-md-12">
          <label class="form-label">Fecha de Ingreso</label>
          <input type="datetime-local" name="fecha_ingreso" id="editar_fecha_ingreso" class="form-control" required>
        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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


<script>
  document.getElementById("buscadorPacienteIngreso").addEventListener("keyup", async function() {
    const valor = this.value.trim();
    const resultadosDiv = document.getElementById("resultadosPacientesIngreso");
    resultadosDiv.innerHTML = "";
    if (valor.length < 2) return;

    const res = await fetch(`api/buscar_pacientes.php?q=${encodeURIComponent(valor)}`);
    const data = await res.json();

    if (data.length > 0) {
      data.slice(0, 5).forEach(p => {
        const div = document.createElement("div");
        div.className = "p-2 border rounded mb-1 hover-bg-light";
        div.style.cursor = "pointer";
        div.textContent = `${p.nombre} ${p.apellidos} (${p.codigo})`;
        div.onclick = () => seleccionarPaciente(p);
        resultadosDiv.appendChild(div);
      });
    } else {
      resultadosDiv.innerHTML = "<div class='text-muted'>Sin coincidencias</div>";
    }
  });

  function seleccionarPaciente(paciente) {
    document.getElementById("id_paciente_seleccionado").value = paciente.id;
    document.getElementById("resultadosPacientesIngreso").innerHTML = "";
    const div = document.getElementById("pacienteSeleccionadoIngreso");
    div.innerHTML = `
    <strong>Paciente seleccionado:</strong> ${paciente.nombre} ${paciente.apellidos} (${paciente.codigo})
    <button type="button" class="btn btn-sm btn-danger float-end" onclick="quitarPacienteSeleccionado()">Quitar</button>
  `;
    div.classList.remove("d-none");
  }

  function quitarPacienteSeleccionado() {
    document.getElementById("id_paciente_seleccionado").value = "";
    document.getElementById("pacienteSeleccionadoIngreso").classList.add("d-none");
  }
</script>


<script>
  document.querySelectorAll('.btn-editar-ingreso').forEach(boton => {
    boton.addEventListener('click', () => {
      const id = boton.getAttribute('data-id');
      const sala = boton.getAttribute('data-sala');
      const cama = boton.getAttribute('data-cama');
      const fecha = boton.getAttribute('data-fecha');

      document.getElementById('editar_id_ingreso').value = id;
      document.getElementById('editar_id_sala').value = sala;
      document.getElementById('editar_numero_cama').value = cama;
      document.getElementById('editar_fecha_ingreso').value = fecha;
    });
  });
</script>

<script>
  const botonesAlta = document.querySelectorAll('.btn-alta');
  const inputIdAlta = document.getElementById('id_ingreso_alta');

  botonesAlta.forEach(boton => {
    boton.addEventListener('click', () => {
      const id = boton.getAttribute('data-id');
      inputIdAlta.value = id;
    });
  });
</script>



<script>
  // Ocultar automáticamente las alertas después de 5 segundos
  setTimeout(() => {
    const alerts = document.querySelectorAll('.auto-dismiss');
    alerts.forEach(alert => {
      alert.classList.remove('show'); // Oculta visualmente
      setTimeout(() => alert.remove(), 500); // Elimina del DOM después de la animación
    });
  }, 5000); // 5000 milisegundos = 5 segundos
</script>