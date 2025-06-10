<?php
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Ingresos
$sqlIngresos = "SELECT i.id, i.fecha_ingreso, i.fecha_alta, i.token, 
                CONCAT(p.nombre, ' ', p.apellidos) AS paciente, 
                s.nombre AS sala, i.fecha_registro
                FROM ingresos i
                JOIN pacientes p ON i.id_paciente = p.id
                JOIN salas_ingreso s ON i.id_sala = s.id
                ORDER BY i.fecha_ingreso DESC";
$ingresos = $pdo->query($sqlIngresos)->fetchAll(PDO::FETCH_ASSOC);

// Pagos
$sqlPagos = "SELECT p.id, p.cantidad, tp.nombre AS tipo_prueba, a.codigo_paciente, 
             a.estado, a.resultado, p.fecha_registro
             FROM pagos p
             JOIN analiticas a ON p.id_analitica = a.id
             JOIN tipos_prueba tp ON p.id_tipo_prueba = tp.id
             ORDER BY p.fecha_registro DESC";
$pagos = $pdo->query($sqlPagos)->fetchAll(PDO::FETCH_ASSOC);

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
  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaIngresos" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Paciente</th>
            <th>Sala</th>
            <th>Token</th>
            <th>Fecha Ingreso</th>
            <th>Fecha Alta</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ingresos as $i): ?>
            <tr data-id="<?= $i['id'] ?>" data-id_paciente="<?= $i['paciente'] ?>" data-id_sala="<?= $i['sala'] ?>" data-token="<?= $i['token'] ?>" data-fecha_ingreso="<?= $i['fecha_ingreso'] ?>" data-fecha_alta="<?= $i['fecha_alta'] ?>">
              <td><?= $i['id'] ?></td>
              <td><?= htmlspecialchars($i['paciente']) ?></td>
              <td><?= htmlspecialchars($i['sala']) ?></td>
              <td><?= htmlspecialchars($i['token']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($i['fecha_ingreso'])) ?></td>
              <td><?= $i['fecha_alta'] ? date('d/m/Y H:i', strtotime($i['fecha_alta'])) : 'Pendiente' ?></td>
              <td class="text-nowrap">
                <button class="btn btn-sm btn-outline-primary btn-editar-ingreso" data-bs-toggle="modal" data-bs-target="#modalEditarIngreso">
                  <i class="bi bi-pencil-square"></i>
                </button>
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

  <!-- Pagos -->
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-credit-card me-2"></i>Gestión de Pagos</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearPago">
        <i class="bi bi-plus-circle me-1"></i>Nuevo Pago
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscadorPago" class="form-control" placeholder="Buscar pago...">
    </div>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaPagos" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Tipo Prueba</th>
            <th>Código Paciente</th>
            <th>Estado</th>
            <th>Resultado</th>
            <th>Cantidad</th>
            <th>Fecha</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pagos as $p): ?>
            <tr data-id="<?= $p['id'] ?>" data-tipo="<?= $p['tipo_prueba'] ?>" data-codigo="<?= $p['codigo_paciente'] ?>" data-estado="<?= $p['estado'] ?>" data-resultado="<?= $p['resultado'] ?>" data-cantidad="<?= $p['cantidad'] ?>">
              <td><?= $p['id'] ?></td>
              <td><?= htmlspecialchars($p['tipo_prueba']) ?></td>
              <td><?= htmlspecialchars($p['codigo_paciente']) ?></td>
              <td><?= htmlspecialchars($p['estado']) ?></td>
              <td><?= nl2br(htmlspecialchars($p['resultado'])) ?></td>
              <td><?= number_format($p['cantidad'], 2) ?> €</td>
              <td><?= date('d/m/Y H:i', strtotime($p['fecha_registro'])) ?></td>
              <td class="text-nowrap">
                <button class="btn btn-sm btn-outline-primary btn-editar-pago" data-bs-toggle="modal" data-bs-target="#modalEditarPago">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <a href="eliminar_pago.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este pago?')">
                  <i class="bi bi-trash"></i>
                </a>
              </td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Salas -->
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3><i class="bi bi-door-open me-2"></i>Gestión de Salas</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearSala">
        <i class="bi bi-plus-circle me-1"></i>Crear Sala
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscadorSala" class="form-control" placeholder="Buscar sala...">
    </div>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaSalas" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Fecha Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($salas as $s): ?>
            <tr data-id="<?= $s['id'] ?>" data-nombre="<?= $s['nombre'] ?>">
              <td><?= $s['id'] ?></td>
              <td><?= htmlspecialchars($s['nombre']) ?></td>
              <td><?= date('d/m/Y H:i', strtotime($s['fecha_registro'])) ?></td>
              <td class="text-nowrap">
                <button class="btn btn-sm btn-outline-primary btn-editar-sala" data-bs-toggle="modal" data-bs-target="#modalEditarSala">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <a href="eliminar_sala.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta sala?')">
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
