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

              data-resultado="<?= htmlspecialchars($a['resultado'], ENT_QUOTES) ?>">
              <td><?= $a['id'] ?></td>
              <td><?= htmlspecialchars($a['tipo_prueba']) ?></td>
              <td><?= htmlspecialchars($a['paciente']) ?></td>
              <td><?= htmlspecialchars($a['codigo_paciente']) ?></td>

              <td>
                <?php if (!empty($a['resultado'])): ?>
                  <span class="badge bg-primary">Resultado</span>
                  <br>

                <?php else: ?>
                  <span class="badge bg-danger">Sin Resultado</span>
                <?php endif; ?>
              </td>

              <td><?= date('d/m/Y H:i', strtotime($a['fecha_registro'])) ?></td>
              <td class="text-nowrap">

                <button class="btn btn-sm btn-outline-primary btn-editar" data-bs-toggle="modal" data-bs-target="#modalEditar">
                  <i class="bi bi-pencil-square"></i>
                </button>

                <?php if (empty($a['resultado'])): ?>
                  <button
                    class="btn btn-sm btn-outline-primary btn-editar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditar"
                    id="<?= $a['id'] ?>"
                    title="Añadir Resultado">
                    <i class="bi bi-pencil-square me-1"></i> Añadir Resultado
                  </button>
                <?php endif; ?>

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







<!-- MODAL EDITAR RESULTADO -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Añadir Resultado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <form action="api/guardar_resultado.php" method="POST" class="modal-content">
          <div id="infoResultado"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
      </form>
    </div>
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
    inputCodigo.value = c.codigo;
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

<script>
  const botonesEditar = document.querySelectorAll('.btn-editar');
  botonesEditar.forEach(boton => {
    boton.addEventListener('click', async () => {
      const id = boton.getAttribute('id');
      const res = await fetch(`api/obtener_analitica.php?id=${id}`);
      const data = await res.json();

      if (data.success) {
        document.getElementById('infoResultado').innerHTML = `
  <p><strong>Paciente:</strong> ${data.nombre} ${data.apellidos}</p>
  <p><strong>Tipo de Prueba:</strong> ${data.tipo_prueba}</p>
  <p><strong>Fecha de Solicitud:</strong> ${data.fecha}</p>

  <div class="mb-3">
    <label for="resultado">Resultado</label>
     <input type="text" class="form-control" name="resultado" required>
  </div>

  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" id="toggleReferencia">
    <label class="form-check-label" for="toggleReferencia">¿Desea añadir valores de referencia?</label>
  </div>

  <div class="mb-3 d-none" id="contenedorReferencia">
    <label for="valores_referencia">Valores de Referencia</label>
    <textarea class="form-control" name="valores_referencia" rows="3"></textarea>
  </div>

  <input type="hidden" name="id_analitica" value="${data.id}">
  <button class="btn btn-primary mt-3">Guardar Resultado</button>
`;
      } else {
        document.getElementById('infoResultado').innerHTML = '<div class="alert alert-danger">No se encontró información.</div>';
      }




      document.getElementById('toggleReferencia').addEventListener('change', function() {
        const contenedor = document.getElementById('contenedorReferencia');
        if (this.checked) {
          contenedor.classList.remove('d-none');
        } else {
          contenedor.classList.add('d-none');
        }
      });








    });
  });
</script>