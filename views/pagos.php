<?php


// Consulta modificada con fecha_solo
$sql = "SELECT 
    a.id, 
    a.resultado, 
    a.estado, 
    a.codigo_paciente, 
    a.pagado,
    tp.id AS id_tipo_prueba,           -- ID de tipo de prueba
    tp.nombre AS tipo_prueba,
    tp.precio,
    CONCAT(p.nombre,' ',p.apellidos) AS paciente,
    a.fecha_registro,
    DATE(a.fecha_registro) AS fecha_solo
FROM analiticas a
JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id
JOIN pacientes p ON a.id_paciente = p.id
ORDER BY a.fecha_registro DESC";

$stmt = $pdo->query($sql);
$analiticas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por paciente y fecha
$grupos = [];
foreach ($analiticas as $a) {
  $clave = $a['paciente'] . '_' . $a['fecha_solo'];

  if (!isset($grupos[$clave])) {
    $grupos[$clave] = [
      'tipo' => $a['tipo_prueba'],
      'paciente' => $a['paciente'],
      'codigo' => $a['codigo_paciente'],
      'fecha' => $a['fecha_solo'],
      'registros' => [],
      'pagos' => [],
    ];
  }

  $grupos[$clave]['registros'][] = $a;
  $grupos[$clave]['pagos'][] = $a['pagado'];
}
?>




<div class="container-fluid" id="content">

  <!-- Pagos -->
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-credit-card me-2"></i>Gestión de Pagos</h3>

    </div>
    <div class="col-md-4">
      <input type="text" id="buscadorPago" class="form-control" placeholder="Buscar pago...">
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
            <th>Nombre del Paciente</th>
            <th>Codigo</th>
            <th>Pruebas</th>

            <th>Resultados</th>

            <th>Fecha</th>
            <th>Pagos</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($grupos as $grupo): ?>
            <tr>
              <td><?= htmlspecialchars($grupo['registros'][0]['id']) ?></td>
              <td><?= htmlspecialchars($grupo['paciente']) ?></td>
              <td><?= htmlspecialchars($grupo['codigo']) ?></td>
              <td>
                <ul class="mb-0">
                  <?php foreach ($grupo['registros'] as $r): ?>
                    <li><?= htmlspecialchars($r['tipo_prueba']) ?></li>
                  <?php endforeach ?>
                </ul>
              </td>
              <td>
                <?php
                $todosConResultado = true;
                foreach ($grupo['registros'] as $r) {
                  if (empty($r['resultado'])) {
                    $todosConResultado = false;
                    break;
                  }
                }
                ?>
                <?php if ($todosConResultado): ?>
                  <span class="badge bg-primary">Resultado</span>
                <?php else: ?>
                  <span class="badge bg-danger">Sin Resultado</span>
                <?php endif; ?>
              </td>
              <td><?= date('d/m/Y', strtotime($grupo['fecha'])) ?></td>
              <td>
                <?php if (!in_array(0, $grupo['pagos'])): ?>
                  <span class="badge bg-success">Pagado</span>
                <?php else: ?>
                  <span class="badge bg-warning text-dark">Pendiente</span>
                <?php endif; ?>
              </td>
              <td>


                <?php if (in_array(0, $grupo['pagos'])): ?>
                  <button
                    class="btn btn-sm btn-outline-success btn-pagar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalPagar"
                    data-grupo='<?= json_encode(array_filter($grupo['registros'], fn($r) => $r['pagado'] == 0)) ?>'
                    data-paciente="<?= htmlspecialchars($grupo['paciente']) ?>"
                    data-fecha="<?= htmlspecialchars($grupo['fecha']) ?>"
                    title="Pagar pruebas">
                    <i class="bi bi-cash-coin me-1"></i> Pagar
                  </button>
                <?php else: ?>
                  <a href="fpdf/generar_factura.php?id=<?= $grupo['registros'][0]['id'] ?>&fecha=<?= $grupo['fecha'] ?>"
                    target="_blank"
                    class="btn btn-outline-secondary btn-sm"
                    title="Imprimir Factura">
                    <i class="bi bi-printer"></i> Imprimir Factura
                  </a>
                <?php endif; ?>




              </td>
            </tr>
          <?php endforeach ?>
        </tbody>

      </table>
    </div>
  </div>


</div>






<div class="modal fade" id="modalPagar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="api/guardar_pago.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Pagar Pruebas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
        <input type="hidden" name="ids_seleccionados" id="idsSeleccionadosPago">

        <div class="mb-3">
          <strong>Paciente:</strong> <span id="nombrePacientePago"></span><br>
          <strong>Fecha:</strong> <span id="fechaPacientePago"></span>
        </div>

        <table class="table table-sm table-bordered align-middle">
          <thead>
            <tr>
              <th>Seleccionar</th>
              <th>Tipo de Prueba</th>
              <th>Precio</th>
            </tr>
          </thead>
          <tbody id="tablaPruebasPago">
            <!-- Se llena con JavaScript -->
          </tbody>
        </table>

        <div class="text-end">
          <strong>Total a Pagar: </strong><span id="totalPago" class="fs-5">0 FCFA</span>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-success"><i class="bi bi-wallet2 me-1"></i> Confirmar Pago</button>
      </div>
    </form>
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

<script>
  document.querySelectorAll('.btn-pagar').forEach(btn => {
    btn.addEventListener('click', () => {
      const grupo = JSON.parse(btn.dataset.grupo);
      const paciente = btn.dataset.paciente;
      const fecha = btn.dataset.fecha;

      document.getElementById('nombrePacientePago').textContent = paciente;
      document.getElementById('fechaPacientePago').textContent = fecha;

      const tabla = document.getElementById('tablaPruebasPago');
      tabla.innerHTML = '';

      let total = 0;

      grupo.forEach((prueba, index) => {
        const id = prueba.id;
        const tipo = prueba.tipo_prueba;
        const precio = parseFloat(prueba.precio || 0); // precio real
        const id_tipo_prueba = prueba.id_tipo_prueba;

        const row = document.createElement('tr');

        row.innerHTML = `
        <td>
          <input type="checkbox" name="pagos[${id}][seleccionado]" value="1" class="form-check-input pagar-checkbox" checked>
          <input type="hidden" name="pagos[${id}][precio]" value="${precio}">
          <input type="hidden" name="pagos[${id}][id_tipo_prueba]" value="${id_tipo_prueba}">
        </td>
        <td>${tipo}</td>
        <td>${precio.toFixed(0)} FCFA</td>
      `;

        tabla.appendChild(row);
        total += precio;
      });

      document.getElementById('totalPago').textContent = total + ' FCFA';

      // Actualizar total dinámicamente
      tabla.querySelectorAll('.pagar-checkbox').forEach(check => {
        check.addEventListener('change', () => {
          let nuevoTotal = 0;
          tabla.querySelectorAll('.pagar-checkbox').forEach(c => {
            if (c.checked) {
              const precioInput = c.parentElement.querySelector('input[name$="[precio]"]');
              nuevoTotal += parseFloat(precioInput.value);
            }
          });
          document.getElementById('totalPago').textContent = nuevoTotal + ' FCFA';
        });
      });
    });
  });
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