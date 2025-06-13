<?php
 
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Listado de analíticas
$sql = "SELECT a.id, a.resultado, a.estado, a.codigo_paciente, a.pagado,
               tp.nombre AS tipo_prueba,
               CONCAT(p.nombre,' ',p.apellidos) AS paciente,
               a.fecha_registro
        FROM analiticas a
        JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id
        JOIN pacientes p ON a.id_paciente = p.id
        ORDER BY a.fecha_registro DESC";
$analiticas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Datos para selects
$tipos = $pdo->query("SELECT id, nombre FROM tipo_pruebas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
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




   <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $_SESSION['success'];
      unset($_SESSION['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= $_SESSION['error'];
      unset($_SESSION['error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>





  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaAnaliticas" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Tipo</th>
            <th>Paciente</th>
            <th>Código</th>
           
            <th>Resultado</th>
           
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
            
              data-resultado="<?= htmlspecialchars($a['resultado'], ENT_QUOTES) ?>"
              >
            <td><?= $a['id'] ?></td>
            <td><?= htmlspecialchars($a['tipo_prueba']) ?></td>
            <td><?= htmlspecialchars($a['paciente']) ?></td>
            <td><?= htmlspecialchars($a['codigo_paciente']) ?></td>
        
            <td><?= nl2br(htmlspecialchars($a['resultado'])) ?></td>
            
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
   <form action="api/guardar_analitica.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nueva Analítica</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">


         <div class="col-md-12">
          <label>Historia de la Enfermedad Actual</label>
          <textarea name="historia" class="form-control" rows="2" required></textarea>
        </div>

         <div class="col-md-12">
          <label>Exproracion Fisica</label>
          <textarea name="exproracion" class="form-control" rows="2" required></textarea>
        </div>

        <div class="col-md-12">
          <label>Buscar Consulta por Nombre del Paciente</label>
          <input type="text" id="buscadorConsulta" class="form-control" placeholder="Escriba el nombre del paciente">
          <div id="resultadosConsulta" class="mt-2"></div>
          <div id="consultaSeleccionada" class="mt-2"></div>
        </div>

        <div class="col-md-12">
          <label>Buscar Tipo de Prueba</label>
          <input type="text" id="buscadorTipoPrueba" class="form-control" placeholder="Escriba el nombre de la prueba">
          <div id="resultadosTipoPrueba" class="mt-2"></div>
          <div id="tiposSeleccionados" class="mt-2"></div>
        </div>

        <input type="hidden" name="id_consulta" id="inputIdConsulta">
        <input type="hidden" name="tipos_prueba_seleccionados" id="inputTiposSeleccionados">

        <input type="hidden" name="id_paciente" id="inputIdPaciente">

        <div class="col-md-12">
          <label>Código de Paciente</label>
          <input type="text" name="codigo_paciente" id="inputCodigo" class="form-control" readonly required>
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


<script>
const buscadorConsulta = document.getElementById("buscadorConsulta");
const resultadosConsulta = document.getElementById("resultadosConsulta");
const consultaSeleccionada = document.getElementById("consultaSeleccionada");
const inputIdConsulta = document.getElementById("inputIdConsulta");
const inputIdPaciente = document.getElementById("inputIdPaciente"); 
const inputCodigo = document.getElementById("inputCodigo"); 

buscadorConsulta.addEventListener("input", async () => {
  const q = buscadorConsulta.value.trim();
  if (q.length < 3) {
    resultadosConsulta.innerHTML = "";
    return;
  }

  const res = await fetch(`api/buscar_consulta.php?q=${encodeURIComponent(q)}`);
  const datos = await res.json();

  resultadosConsulta.innerHTML = datos.map(c => `
    <div class='form-check'>
      <input type='radio' name='consulta' id='consulta-${c.id}' class='form-check-input' value='${c.id}' onclick='seleccionarConsulta(${JSON.stringify(c)})'>
      <label class='form-check-label' for='consulta-${c.id}'>${c.nombre} ${c.apellidos}  || CODIGO: ${c.codigo} || ${c.fecha}</label>
    </div>`).join("");
});

function seleccionarConsulta(c) {
  consultaSeleccionada.innerHTML = `<div class='alert alert-secondary'>Consulta Seleccionada: ${c.nombre} ${c.apellidos} - ${c.fecha}</div>`;
  inputIdConsulta.value = c.id;
  inputIdPaciente.value = c.id_paciente;
  inputCodigo.value=c.codigo;
  resultadosConsulta.innerHTML = "";
  buscadorConsulta.value = "";
}

// Tipos de prueba múltiples
const buscadorTipo = document.getElementById("buscadorTipoPrueba");
const resultadosTipo = document.getElementById("resultadosTipoPrueba");
const tiposSeleccionados = document.getElementById("tiposSeleccionados");
const inputTipos = document.getElementById("inputTiposSeleccionados");

let tiposElegidos = [];

buscadorTipo.addEventListener("input", async () => {
  const q = buscadorTipo.value.trim();
  if (q.length < 2) {
    resultadosTipo.innerHTML = "";
    return;
  }

  const res = await fetch(`api/buscar_tipo_pruebas.php?q=${encodeURIComponent(q)}`);
  const datos = await res.json();

  resultadosTipo.innerHTML = datos.map(t => `
    <div class='form-check'>
      <input type='checkbox' id='tipo-${t.id}' class='form-check-input' onchange='toggleTipo(${JSON.stringify(t)})'>
      <label class='form-check-label' for='tipo-${t.id}'>${t.nombre}</label>
    </div>`).join("");
});

function toggleTipo(t) {
  const index = tiposElegidos.findIndex(el => el.id === t.id);
  if (index > -1) {
    tiposElegidos.splice(index, 1);
  } else {
    tiposElegidos.push(t);
  }
  actualizarTiposSeleccionados();
}

function eliminarTipo(id) {
  tiposElegidos = tiposElegidos.filter(t => t.id !== id);
  actualizarTiposSeleccionados();
}

function actualizarTiposSeleccionados() {
  tiposSeleccionados.innerHTML = tiposElegidos.map(t => `
    <span class='badge bg-info me-1'>${t.nombre} <button type='button' class='btn-close btn-close-white btn-sm ms-1' onclick='eliminarTipo(${t.id})'></button></span>`).join("");
  inputTipos.value = tiposElegidos.map(t => t.id).join(',');
}
</script>
