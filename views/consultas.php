<?php

$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Obtener lista de consultas
$sql = "SELECT c.*, p.nombre, p.apellidos
        FROM consultas c
        LEFT JOIN pacientes p ON c.id_paciente = p.id
        ORDER BY c.fecha_registro DESC";
$consultas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Obtener pacientes para selects
$pacientes = $pdo->query("SELECT id, nombre, apellidos FROM pacientes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid" id="content">
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0"><i class="bi bi-clipboard-pulse me-2"></i>Listado de Consultas</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrear">
        <i class="bi bi-plus-circle me-1"></i> Nueva Consulta
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscador" class="form-control" placeholder="Buscar consulta...">
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



  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover table-bordered table-sm align-middle" id="tablaConsultas">
          <thead class="table-light text-nowrap">
            <tr>
              <th>ID</th>
              <th>Paciente</th>
              <th>Motivo</th>
              <th>Temperatura</th>
              <th>Pulso</th>
              <th>Peso actual</th>
              <th>IMC</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($consultas as $c): ?>
              <tr>
                <td><?= $c['id'] ?></td>
                <td><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellidos']) ?></td>
                <td><?= nl2br(htmlspecialchars($c['motivo_consulta'])) ?></td>
                <td><?= $c['temperatura'] ?> °C</td>
                <td><?= $c['pulso'] ?> bpm</td>
                <td><?= $c['peso_actual'] ?> kg</td>
                <td><?= $c['imc'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($c['fecha_registro'])) ?></td>
                <td class="text-nowrap">
              


<button class="btn btn-sm btn-primary editar-consulta"
        data-id="<?= $consulta['id'] ?>"
        data-bs-toggle="modal"
        data-bs-target="#modal-editar">
  <i class="bi bi-pencil-square"></i>
</button>


                  <a href="eliminar_consulta.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('¿Eliminar esta consulta?')">
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




<!-- MODAL CREAR CONSULTA -->
<div class="modal fade" id="modalCrear" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <form action="api/guardar_consulta.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nueva Consulta</h5>
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
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>Motivo de consulta</label>
          <textarea name="motivo_consulta" class="form-control" rows="2" required></textarea>
        </div>
        <?php
        $campos = [
          "temperatura" => "Temperatura (°C)",
          "control_cada_horas" => "Control cada (horas)",
          "frecuencia_cardiaca" => "Frecuencia cardíaca",
          "frecuencia_respiratoria" => "Frecuencia respiratoria",
          "tension_arterial" => "Tensión arterial",
          "pulso" => "Pulso",
          "saturacion_oxigeno" => "Saturación O₂ (%)",
          "peso_anterior" => "Peso anterior (kg)",
          "peso_actual" => "Peso actual (kg)",
          "peso_ideal" => "Peso ideal (kg)",
          "imc" => "IMC"
        ];
        foreach ($campos as $name => $label): ?>
          <div class="col-md-4">
            <label><?= $label ?></label>
            <input type="<?= in_array($name, ['tension_arterial']) ? 'text' : 'number' ?>" step="any" name="<?= $name ?>" class="form-control">
          </div>
        <?php endforeach; ?>
        <hr class="my-4">
        <h5 class="text-success">Detalles Clínicos</h5>

        <div class="col-md-6">
          <label>Operación</label>
          <textarea name="operacion" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-3">
          <label>Orina</label>
          <input type="text" name="orina" class="form-control">
        </div>

        <div class="col-md-3">
          <label>Defeca</label>
          <input type="text" name="defeca" class="form-control">
        </div>

        <div class="col-md-3">
          <label>Días que defeca</label>
          <input type="number" name="defeca_dias" class="form-control" min="0">
        </div>

        <div class="col-md-3">
          <label>Duerme</label>
          <input type="text" name="duerme" class="form-control">
        </div>

        <div class="col-md-3">
          <label>Horas que duerme</label>
          <input type="number" name="duerme_horas" class="form-control" min="0" max="24">
        </div>

        <div class="col-md-6">
          <label>Antecedentes Patológicos</label>
          <textarea name="antecedentes_patologicos" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-6">
          <label>Alergias</label>
          <textarea name="alergico" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-6">
          <label>Antecedentes Familiares</label>
          <textarea name="antecedentes_familiares" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-6">
          <label>Antecedentes del Cónyuge</label>
          <textarea name="antecedentes_conyuge" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-12">
          <label>Control de signos vitales</label>
          <textarea name="control_signos_vitales" class="form-control" rows="3"></textarea>
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EDITAR CONSULTA -->
<div class="modal fade" id="modal-editar" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <form action="api/actualizar_consulta.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Consulta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">

        <div class="col-md-6">
          <label>Paciente</label>
          <select name="id_paciente" id="edit-paciente" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($pacientes as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label>Motivo de consulta</label>
          <textarea name="motivo_consulta" id="edit-motivo" class="form-control" rows="2" required></textarea>
        </div>

        <?php
        foreach ($campos as $name => $label): ?>
          <div class="col-md-4">
            <label><?= $label ?></label>
            <input type="<?= in_array($name, ['tension_arterial']) ? 'text' : 'number' ?>" step="any" name="<?= $name ?>" id="edit-<?= $name ?>" class="form-control">
          </div>
        <?php endforeach; ?>

        <hr class="my-4">
        <h5 class="text-primary">Detalles Clínicos</h5>

        <div class="col-md-6">
          <label>Operación</label>
          <textarea name="operacion" id="edit-operacion" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-3">
          <label>Orina</label>
          <input type="text" name="orina" id="edit-orina" class="form-control">
        </div>

        <div class="col-md-3">
          <label>Defeca</label>
          <input type="text" name="defeca" id="edit-defeca" class="form-control">
        </div>

        <div class="col-md-3">
          <label>Días que defeca</label>
          <input type="number" name="defeca_dias" id="edit-defeca_dias" class="form-control" min="0">
        </div>

        <div class="col-md-3">
          <label>Duerme</label>
          <input type="text" name="duerme" id="edit-duerme" class="form-control">
        </div>

        <div class="col-md-3">
          <label>Horas que duerme</label>
          <input type="number" name="duerme_horas" id="edit-duerme_horas" class="form-control" min="0" max="24">
        </div>

        <div class="col-md-6">
          <label>Antecedentes Patológicos</label>
          <textarea name="antecedentes_patologicos" id="edit-antecedentes_patologicos" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-6">
          <label>Alergias</label>
          <textarea name="alergico" id="edit-alergico" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-6">
          <label>Antecedentes Familiares</label>
          <textarea name="antecedentes_familiares" id="edit-antecedentes_familiares" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-6">
          <label>Antecedentes del Cónyuge</label>
          <textarea name="antecedentes_conyuge" id="edit-antecedentes_conyuge" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-12">
          <label>Control de signos vitales</label>
          <textarea name="control_signos_vitales" id="edit-control_signos_vitales" class="form-control" rows="3"></textarea>
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
  const botones = document.querySelectorAll('.editar-consulta');

  botones.forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = btn.dataset.id;

      try {
        const response = await fetch(`api/obtener_consulta.php?id=${id}`);
        const data = await response.json();

        console.log(id);

        
        // Cargar campos de la tabla consultas
        document.getElementById('edit-id').value = data.consulta.id;
        document.getElementById('edit-paciente').value = data.consulta.id_paciente;
        document.getElementById('edit-motivo').value = data.consulta.motivo_consulta;

        const campos = [
          'temperatura', 'control_cada_horas', 'frecuencia_cardiaca', 'frecuencia_respiratoria',
          'tension_arterial', 'pulso', 'saturacion_oxigeno', 'peso_anterior',
          'peso_actual', 'peso_ideal', 'imc'
        ];
        campos.forEach(campo => {
          const input = document.getElementById('edit-' + campo);
          if (input) input.value = data.consulta[campo] ?? '';
        });

        // Cargar campos de la tabla detalle_consulta
        const detalleCampos = [
          'operacion', 'orina', 'defeca', 'defeca_dias', 'duerme', 'duerme_horas',
          'antecedentes_patologicos', 'alergico', 'antecedentes_familiares',
          'antecedentes_conyuge', 'control_signos_vitales'
        ];
        detalleCampos.forEach(campo => {
          const input = document.getElementById('edit-' + campo);
          if (input) input.value = data.detalle[campo] ?? '';
        });

      } catch (error) {
        console.error('Error cargando datos de la consulta:', error);
        alert('Ocurrió un error al cargar los datos.');
      }
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