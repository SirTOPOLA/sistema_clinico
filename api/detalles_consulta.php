<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
require_once '../config/conexion.php'; // PDO con excepciones activadas

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo "Consulta no válida.";
    exit;
}

$id = intval($_POST['id']);

// Consulta principal
$sql = "SELECT c.*, p.nombre AS nombre_paciente, p.apellidos, p.codigo AS codigo_paciente
        FROM consultas c
        JOIN pacientes p ON c.id_paciente = p.id
        WHERE c.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$consulta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$consulta) {
    echo "<div class='alert alert-danger'>Consulta no encontrada.</div>";
    exit;
}

// Detalle consulta
$detalle = $pdo->prepare("SELECT * FROM detalle_consulta WHERE id_consulta = ?");
$detalle->execute([$id]);
$detalle = $detalle->fetch(PDO::FETCH_ASSOC);

// Recetas
$recetas = $pdo->prepare("SELECT * FROM recetas WHERE id_consulta = ?");
$recetas->execute([$id]);
$recetas = $recetas->fetchAll(PDO::FETCH_ASSOC);

// Analíticas
$analiticas = $pdo->prepare("SELECT a.*, t.nombre AS tipo
                             FROM analiticas a
                             JOIN tipo_pruebas t ON a.id_tipo_prueba = t.id
                             WHERE a.id_consulta = ?");
$analiticas->execute([$id]);
$analiticas = $analiticas->fetchAll(PDO::FETCH_ASSOC);

// Ingresos
$ingresos = $pdo->prepare("SELECT s.nombre AS sala, i.fecha_ingreso, i.fecha_alta
                           FROM ingresos i
                           JOIN salas_ingreso s ON i.id_sala = s.id
                           WHERE i.id_paciente = ?");
$ingresos->execute([$consulta['id_paciente']]);
$ingresos = $ingresos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
  <div class="row mb-4">
    <div class="col-md-12">
      <h5 class="text-success"><i class="bi bi-person-vcard me-2"></i>Paciente: <?= htmlspecialchars($consulta['nombre_paciente'] . ' ' . $consulta['apellidos']) ?> (<?= $consulta['codigo_paciente'] ?>)</h5>
      <hr>
    </div>

    <div class="col-md-6 mb-3">
      <p><i class="bi bi-calendar2-plus me-2 text-primary"></i><strong>Fecha consulta:</strong> <?= date('d/m/Y H:i', strtotime($consulta['fecha_registro'])) ?></p>
      <p><i class="bi bi-heart-pulse-fill me-2 text-primary"></i><strong>Motivo:</strong> <?= nl2br(htmlspecialchars($consulta['motivo_consulta'])) ?></p>
    </div>

    <div class="col-md-6 mb-3">
      <p><i class="bi bi-thermometer-half me-2 text-primary"></i><strong>Temperatura:</strong> <?= $consulta['temperatura'] ?> °C</p>
      <p><i class="bi bi-droplet-half me-2 text-primary"></i><strong>Pulso:</strong> <?= $consulta['pulso'] ?> bpm</p>
      <p><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i><strong>IMC:</strong> <?= $consulta['imc'] ?></p>
    </div>
  </div>

  <?php if ($detalle): ?>
  <div class="mb-4">
    <h5 class="text-info"><i class="bi bi-file-earmark-text me-2"></i>Detalles Clínicos</h5>
    <div class="row">
      <div class="col-md-6"><strong>Operación:</strong> <?= htmlspecialchars($detalle['operacion']) ?></div>
      <div class="col-md-6"><strong>Orina:</strong> <?= htmlspecialchars($detalle['orina']) ?></div>
      <div class="col-md-6"><strong>Defeca:</strong> <?= htmlspecialchars($detalle['defeca']) ?></div>
      <div class="col-md-6"><strong>Duerme:</strong> <?= htmlspecialchars($detalle['duerme']) ?> (<?= $detalle['duerme_horas'] ?> hrs)</div>
      <div class="col-md-6"><strong>Antecedentes Patológicos:</strong> <?= htmlspecialchars($detalle['antecedentes_patologicos']) ?></div>
      <div class="col-md-6"><strong>Familiares:</strong> <?= htmlspecialchars($detalle['antecedentes_familiares']) ?></div>
      <div class="col-md-6"><strong>Conyuge:</strong> <?= htmlspecialchars($detalle['antecedentes_conyuge']) ?></div>
      <div class="col-md-6"><strong>Control signos vitales:</strong> <?= htmlspecialchars($detalle['control_signos_vitales']) ?></div>
      <div class="col-md-12"><strong>Alergias:</strong> <?= nl2br(htmlspecialchars($detalle['alergico'])) ?></div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($recetas): ?>
  <div class="mb-4">
    <h5 class="text-warning"><i class="bi bi-capsule-pill me-2"></i>Recetas</h5>
    <ul class="list-group">
      <?php foreach ($recetas as $r): ?>
        <li class="list-group-item">
          <i class="bi bi-check-circle-fill text-success me-2"></i>
          <?= nl2br(htmlspecialchars($r['descripcion'])) ?>
          <?php if (!empty($r['comentario'])): ?>
            <div class="text-muted small"><i class="bi bi-chat-text me-1"></i><?= htmlspecialchars($r['comentario']) ?></div>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <?php if ($analiticas): ?>
  <div class="mb-4">
    <h5 class="text-danger"><i class="bi bi-flask me-2"></i>Análisis Clínicos</h5>
    <table class="table table-sm table-bordered">
      <thead class="table-light">
        <tr>
          <th>Prueba</th>
          <th>Resultado</th>
          
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($analiticas as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['tipo']) ?></td>
            <td><?= nl2br(htmlspecialchars($a['resultado'])) ?></td> 
            <td><?= date('d/m/Y H:i', strtotime($a['fecha_registro'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($ingresos): ?>
  <div class="mb-4">
    <h5 class="text-secondary"><i class="bi bi-hospital me-2"></i>Historial de Ingresos</h5>
    <table class="table table-bordered table-sm">
      <thead class="table-light">
        <tr>
          <th>Sala</th>
          <th>Ingreso</th>
          <th>Alta</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($ingresos as $i): ?>
          <tr>
            <td><?= htmlspecialchars($i['sala']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($i['fecha_ingreso'])) ?></td>
            <td><?= $i['fecha_alta'] ? date('d/m/Y H:i', strtotime($i['fecha_alta'])) : '<span class="badge bg-warning">Aún internado</span>' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
