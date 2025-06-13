<?php
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Consulta básica (puedes unir con consultas, pacientes si gustas)
$sql = "SELECT r.*, p.nombre AS nombre_paciente, p.apellidos 
        FROM recetas r
        JOIN pacientes p ON r.id_paciente = p.id
        ORDER BY r.fecha_registro DESC";
$recetas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="content" class="container-fluid">
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0"><i class="bi bi-clipboard2-plus me-2"></i>Listado de Recetas</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrear">
        <i class="bi bi-file-earmark-plus-fill me-1"></i> Nueva Receta
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscador" class="form-control" placeholder="Buscar receta...">
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



  <?php
  function limitarTexto($texto, $limite = 100)
  {
    $textoPlano = strip_tags($texto); // por si acaso
    return strlen($textoPlano) > $limite
      ? substr($textoPlano, 0, $limite) . '...'
      : $textoPlano;
  }
  ?>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaRecetas" class="table table-hover table-bordered align-middle table-sm">
          <thead class="table-light text-nowrap">
            <tr>
              <th>ID</th>
              <th>Paciente</th>
              <th>Código</th>
              <th>Descripción</th>
              <th>Comentario</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recetas as $r): ?>
              <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= htmlspecialchars($r['nombre_paciente'] . ' ' . $r['apellidos']) ?></td>
                <td><?= htmlspecialchars($r['codigo_paciente']) ?></td>

                <td><?= nl2br(htmlspecialchars(limitarTexto($r['descripcion'], 80))) ?></td>
                <td><?= nl2br(htmlspecialchars(limitarTexto($r['comentario'], 80))) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($r['fecha_registro'])) ?></td>
                <td class="text-nowrap">
                  <button
                    class="btn btn-sm btn-outline-primary me-1"
                    title="Editar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditar"
                    data-id="<?= $r['id'] ?>"
                    data-descripcion="<?= htmlspecialchars($r['descripcion'], ENT_QUOTES) ?>"
                    data-comentario="<?= htmlspecialchars($r['comentario'], ENT_QUOTES) ?>"
                    data-id_paciente="<?= $r['id_paciente'] ?>"
                    data-id_consulta="<?= $r['id_consulta'] ?>"
                    data-codigo="<?= $r['codigo_paciente'] ?>">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <a href="eliminar_receta.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('¿Deseas eliminar esta receta?')" title="Eliminar">
                    <i class="bi bi-trash"></i>
                  </a>

                  <a href="fpdf/imprimir_receta.php?id=<?= $r['id'] ?>" target="_blank" class="btn btn-sm btn-outline-warning" title="Imprimir">
                    <i class="bi bi-printer"></i>
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





<!-- MODAL CREAR RECETA -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="api/guardar_receta.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-file-earmark-plus-fill me-2"></i>Nueva Receta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">

        <div class="col-md-12">
          <label>Buscar Consulta por Nombre del Paciente</label>
          <input type="text" id="buscadorConsultaReceta" class="form-control" placeholder="Escriba el nombre del paciente">
          <div id="resultadosConsultaReceta" class="mt-2"></div>
          <div id="consultaRecetaSeleccionada" class="mt-2"></div>
        </div>

        <input type="hidden" name="id_consulta" id="inputIdConsultaReceta">
        <input type="hidden" name="id_paciente" id="inputIdPacienteReceta">

        <div class="col-md-12">
          <label>Código del paciente</label>
          <input type="text" name="codigo_paciente" id="inputCodigo" class="form-control" readonly>
        </div>
        <div class="col-md-12">
          <label>Contenido de la Receta</label>
          <textarea name="descripcion" class="form-control" rows="3" required></textarea>
        </div>
        <div class="col-md-12">
          <label>Comentario</label>
          <textarea name="comentario" class="form-control" rows="2" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>


<!-- MODAL EDITAR RECETA -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="api/actualizar_receta.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Receta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">

        <div class="col-md-6">
          <label>Nombre del paciente</label>
          <input type="text" name="codigo_paciente" id="nombre_paciente" class="form-control">
        </div>
        <div class="col-md-12">
          <label>Código del paciente</label>
          <input type="text" name="codigo_paciente" id="codigo_paciente" class="form-control" readonly>
        </div>
        <div class="col-md-12">
          <label>Descripción</label>
          <textarea name="descripcion" id="edit-descripcion" class="form-control" rows="3" required></textarea>
        </div>
        <div class="col-md-12">
          <label>Comentario</label>
          <textarea name="comentario" id="edit-comentario" class="form-control" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const modalEditar = document.getElementById('modalEditar');
    modalEditar.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;

      document.getElementById('edit-id').value = button.getAttribute('data-id');
      document.getElementById('edit-descripcion').value = button.getAttribute('data-descripcion');
      document.getElementById('edit-comentario').value = button.getAttribute('data-comentario');
      document.getElementById('codigo_paciente').value = button.getAttribute('data-codigo');
    });
  });
</script>






<script>
  const buscadorConsultaReceta = document.getElementById("buscadorConsultaReceta");
  const resultadosConsultaReceta = document.getElementById("resultadosConsultaReceta");
  const consultaRecetaSeleccionada = document.getElementById("consultaRecetaSeleccionada");
  const inputIdConsultaReceta = document.getElementById("inputIdConsultaReceta");
  const inputIdPacienteReceta = document.getElementById("inputIdPacienteReceta");

  const inputCodigo = document.getElementById("inputCodigo");

  buscadorConsultaReceta.addEventListener("input", async () => {
    const q = buscadorConsultaReceta.value.trim();
    if (q.length < 3) {
      resultadosConsultaReceta.innerHTML = "";
      return;
    }

    const res = await fetch(`api/buscar_consulta.php?q=${encodeURIComponent(q)}`);
    const datos = await res.json();

    resultadosConsultaReceta.innerHTML = datos.map(c => `
    <div class='form-check'>
      <input type='radio' name='consultaReceta' id='consultaReceta-${c.id}' class='form-check-input' value='${c.id}' onclick='seleccionarConsultaReceta(${JSON.stringify(c)})'>
      <label class='form-check-label' for='consultaReceta-${c.id}'>${c.nombre} ${c.apellidos} - ID: ${c.id} - CODIGO: ${c.codigo} - ${c.fecha}</label>
    </div>`).join("");
  });

  function seleccionarConsultaReceta(c) {
    consultaRecetaSeleccionada.innerHTML = `
    <div class='alert alert-primary d-flex justify-content-between align-items-center'>
      <span>Consulta Seleccionada: <strong>${c.nombre} ${c.apellidos}</strong> - ${c.fecha} - ${c.codigo} </span>
      <button type='button' class='btn-close' onclick='eliminarConsultaSeleccionada()'></button>
    </div>`;
    inputIdConsultaReceta.value = c.id;
    inputIdPacienteReceta.value = c.id_paciente;
    inputCodigo.value = c.codigo;
    resultadosConsultaReceta.innerHTML = "";
    buscadorConsultaReceta.value = "";
  }

  function eliminarConsultaSeleccionada() {
    consultaRecetaSeleccionada.innerHTML = "";
    inputIdConsultaReceta.value = "";
    inputIdPacienteReceta.value = "";
  }
</script>

<script>
  setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      alert.classList.remove('show');
      alert.classList.add('fade');
    }
  }, 10000); // 10 segundos
</script>