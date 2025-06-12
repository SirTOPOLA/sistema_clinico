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
                  <button class="btn btn-sm btn-outline-primary me-1" title="Editar"
                    data-bs-toggle="modal" data-bs-target="#modalEditar"
                    data-id="<?= $c['id'] ?>"
                    data-motivo="<?= htmlspecialchars($c['motivo_consulta'], ENT_QUOTES) ?>"
                    data-temperatura="<?= $c['temperatura'] ?>"
                    data-control="<?= $c['control_cada_horas'] ?>"
                    data-fc="<?= $c['frecuencia_cardiaca'] ?>"
                    data-fr="<?= $c['frecuencia_respiratoria'] ?>"
                    data-ta="<?= $c['tension_arterial'] ?>"
                    data-pulso="<?= $c['pulso'] ?>"
                    data-so="<?= $c['saturacion_oxigeno'] ?>"
                    data-pant="<?= $c['peso_anterior'] ?>"
                    data-pact="<?= $c['peso_actual'] ?>"
                    data-pideal="<?= $c['peso_ideal'] ?>"
                    data-imc="<?= $c['imc'] ?>"
                    data-paciente="<?= $c['id_paciente'] ?>"
                  >
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
    <form action="guardar_consulta.php" method="POST" class="modal-content">
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
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <form action="actualizar_consulta.php" method="POST" class="modal-content">
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
        <?php foreach ($campos as $name => $label): ?>
          <div class="col-md-4">
            <label><?= $label ?></label>
            <input type="<?= in_array($name, ['tension_arterial']) ? 'text' : 'number' ?>" step="any" name="<?= $name ?>" id="edit-<?= $name ?>" class="form-control">
          </div>
        <?php endforeach; ?>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const modalEditar = document.getElementById('modalEditar');
  modalEditar.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    document.getElementById('edit-id').value = btn.dataset.id;
    document.getElementById('edit-motivo').value = btn.dataset.motivo || '';
    document.getElementById('edit-paciente').value = btn.dataset.paciente;

    const campos = [
      'temperatura', 'control', 'fc', 'fr', 'ta', 'pulso', 'so',
      'pant', 'pact', 'pideal', 'imc'
    ];

    campos.forEach(c => {
      const id = 'edit-' + c.replace('pant', 'peso_anterior').replace('pact', 'peso_actual').replace('pideal', 'peso_ideal').replace('so', 'saturacion_oxigeno').replace('fc', 'frecuencia_cardiaca').replace('fr', 'frecuencia_respiratoria').replace('ta', 'tension_arterial');
      if (document.getElementById(id)) {
        document.getElementById(id).value = btn.dataset[c] || '';
      }
    });
  });
});
</script>
