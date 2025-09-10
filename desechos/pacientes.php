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
    echo '<div id="mensaje" class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']);
  }
  if (isset($_SESSION['success'])) {
    echo '<div id="mensaje" class="alert alert-success">' . $_SESSION['success'] . '</div>';
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
              <th>CODIGO</th>
              <th>Sexo</th>
              <th>Teléfono</th>
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
                <td><?= htmlspecialchars($p['codigo']) ?></td>
                <td><?= htmlspecialchars($p['sexo']) ?></td>
                <td><?= htmlspecialchars($p['telefono']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($p['fecha_registro'])) ?></td>
                <td class="text-nowrap">
                  <button class="btn btn-sm btn-outline-primary btn-editar" data-bs-toggle="modal"
                    data-bs-target="#modalEditar">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalHistorial"
                    onclick="cargarHistorialMedico(<?= $p['id'] ?>)">
                    <i class="bi bi-journal-text"></i> Historial
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
        <h5 class="modal-title" id="modalCrearLabel">Registrar Nuevo Paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?>">
        
        <!-- Sección de Información General -->
        <h6 class="mb-3 border-bottom pb-2 text-primary">Información General</h6>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label for="crear_nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="crear_nombre" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label for="crear_apellidos" class="form-label">Apellidos</label>
            <input type="text" name="apellidos" id="crear_apellidos" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label for="crear_fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" id="crear_fecha_nacimiento" class="form-control">
          </div>
          
          <div class="col-md-4">
            <label for="crear_sexo" class="form-label">Sexo</label>
            <select name="sexo" id="crear_sexo" class="form-select">
              <option value="">Seleccionar</option>
              <option value="Masculino">Masculino</option>
              <option value="Femenino">Femenino</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="crear_direccion" class="form-label">Dirección</label> 
            <input type="text" name="direccion" id="crear_direccion" class="form-control">
          </div>
        </div>

        <!-- Sección de Información Adicional -->
        <h6 class="mb-3 border-bottom pb-2 text-primary">Información Adicional</h6>
        <div class="row g-3">
          <div class="col-md-4">
            <label for="crear_tutor_nombre" class="form-label">Tutor</label>
            <input type="text" name="tutor_nombre" id="crear_tutor_nombre" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="crear_dip" class="form-label">DIP</label>
            <input type="text" name="dip" id="crear_dip" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="crear_telefono" class="form-label">Teléfono</label>
            <input type="text" name="telefono" id="crear_telefono" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="crear_ocupacion" class="form-label">Ocupación (Opcional)</label>
            <input type="text" name="ocupacion" id="crear_ocupacion" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="crear_profesion" class="form-label">Profesión (Opcional)</label>
            <input type="text" name="profesion" id="crear_profesion" class="form-control">
          </div>
          <div class="col-md-4">
            <label for="crear_telefono_tutor" class="form-label">Teléfono del Tutor (Opcional)</label>
            <input type="text" name="telefono_tutor" id="crear_telefono_tutor" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
        <h5 class="modal-title" id="modalEditarLabel">Editar Paciente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="edit_id">
        <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?>">
        
        <!-- Sección de Información General -->
        <h6 class="mb-3 border-bottom pb-2 text-primary">Información General</h6>
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label for="edit_nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label for="edit_apellidos" class="form-label">Apellidos</label>
            <input type="text" name="apellidos" id="edit_apellidos" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label for="edit_fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
            <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento" class="form-control">
          </div>
         
          <div class="col-md-4">
            <label for="edit_sexo" class="form-label">Sexo</label>
            <select name="sexo" id="edit_sexo" class="form-select">
              <option value="">Seleccionar</option>
              <option value="Masculino">Masculino</option>
              <option value="Femenino">Femenino</option>
            </select>
          </div>
          <div class="col-12">
            <label for="edit_direccion" class="form-label">Dirección</label>
            <textarea name="direccion" id="edit_direccion" class="form-control" rows="2"></textarea>
          </div>
        </div>

        <!-- Sección de Información Adicional -->
        <h6 class="mb-3 border-bottom pb-2 text-primary">Información Adicional</h6>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="edit_tutor_nombre" class="form-label">Tutor</label>
            <input type="text" name="tutor_nombre" id="edit_tutor_nombre" class="form-control">
          </div>
           <div class="col-md-4">
            <label for="edit_dip" class="form-label">DIP</label>
            <input type="text" name="dip" id="edit_dip" class="form-control">
          </div>
          <div class="col-md-6">
            <label for="edit_telefono" class="form-label">Teléfono</label>
            <input type="text" name="telefono" id="edit_telefono" class="form-control">
          </div>
          <div class="col-md-6">
            <label for="edit_ocupacion" class="form-label">Ocupación</label>
            <input type="text" name="ocupacion" id="edit_ocupacion" class="form-control">
          </div>
          <div class="col-md-6">
            <label for="edit_profesion" class="form-label">Profesión</label>
            <input type="text" name="profesion" id="edit_profesion" class="form-control">
          </div>
          <div class="col-md-6">
            <label for="edit_telefono_tutor" class="form-label">Segundo Teléfono del Tutor</label>
            <input type="text" name="telefono_tutor" id="edit_telefono_tutor" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Actualizar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content shadow rounded-4">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalHistorialLabel"><i class="bi bi-folder2-open"></i> Historial Médico</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <!-- Filtros -->
        <div class="row g-2 mb-3">
          <div class="col-md-4">
            <label>Desde</label>
            <input type="date" id="fecha_inicio" class="form-control">
          </div>
          <div class="col-md-4">
            <label>Hasta</label>
            <input type="date" id="fecha_fin" class="form-control">
          </div>
          <div class="col-md-4 d-flex align-items-end justify-content-center gap-2">
            <button onclick="filtrarHistorial()" class="btn btn-success"><i class="bi bi-search"></i> Buscar</button>
            <button onclick="generarPDF()" class="btn btn-danger"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</button>

          </div>
        </div>

        <!-- Contenedor del historial -->
        <div id="historialContenido" class="border p-3 bg-light rounded-3" style="font-size: 14px;">
          <!-- Datos cargados por JS -->
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  let id_paciente = 0;

  function cargarHistorialMedico(id) {
    if (!id || isNaN(id)) {
      console.warn("ID de paciente inválido");
      return;
    }

    id_paciente = id;
    const contenedor = document.getElementById('historialContenido');
    if (!contenedor) return;

    contenedor.innerHTML = '<p class="text-center text-muted">Cargando historial...</p>';

    fetch(`api/obtener_historial.php?id_paciente=${id}`)
      .then(response => {
        if (!response.ok) throw new Error('Respuesta inválida del servidor');
        return response.text();
      })
      .then(html => {
        contenedor.innerHTML = html || '<div class="alert alert-info">No se encontró historial.</div>';
      })
      .catch(error => {
        contenedor.innerHTML = `<div class="alert alert-danger">Error al cargar historial</div>`;
        console.error('Error al cargar historial:', error);
      });
  }

  function filtrarHistorial() {
    const inicioEl = document.getElementById('fecha_inicio');
    const finEl = document.getElementById('fecha_fin');
    const contenedor = document.getElementById('historialContenido');

    if (!inicioEl || !finEl || !contenedor) return;

    const inicio = inicioEl.value;
    const fin = finEl.value;

    if (!id_paciente || isNaN(id_paciente)) {
      alert("Seleccione un paciente antes de filtrar.");
      return;
    }

    if (!inicio || !fin) {
      alert("Debe seleccionar un rango de fechas válido.");
      return;
    }

    if (new Date(inicio) > new Date(fin)) {
      alert("La fecha de inicio no puede ser mayor que la fecha de fin.");
      return;
    }

    contenedor.innerHTML = '<p class="text-center text-muted">Filtrando...</p>';

    const params = new URLSearchParams({
      id_paciente,
      inicio,
      fin
    });

    fetch(`api/obtener_historial.php?${params.toString()}`)
      .then(res => {
        if (!res.ok) throw new Error('Respuesta inválida del servidor');
        return res.text();
      })
      .then(html => {
        contenedor.innerHTML = html || '<div class="alert alert-info">No se encontraron registros.</div>';
      })
      .catch(err => {
        contenedor.innerHTML = `<div class="alert alert-danger">Error al filtrar historial</div>`;
        console.error('Error al filtrar:', err);
      });
  }

  function generarPDF() {
    const inicio = document.getElementById('fecha_inicio')?.value;
    const fin = document.getElementById('fecha_fin')?.value;

    if (!id_paciente || isNaN(id_paciente)) {
      alert("Seleccione un paciente antes de generar el PDF.");
      return;
    }

    if (!inicio || !fin) {
      alert("Debe seleccionar un rango de fechas para generar el PDF.");
      return;
    }

    const url = `fpdf/generar_pdf.php?id_paciente=${id_paciente}&inicio=${inicio}&fin=${fin}`;
    window.open(url, '_blank');
  }

  // Eventos DOM
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-editar').forEach(btn => {
      btn.addEventListener('click', () => {
        const tr = btn.closest('tr');
        const fields = ['id', 'nombre', 'apellidos', 'fecha_nacimiento', 'dip', 'sexo', 'email',
          'telefono', 'profesion', 'ocupacion', 'tutor_nombre', 'telefono_tutor', 'direccion'
        ];

        fields.forEach(f => {
          const el = document.getElementById('edit_' + f);
          if (el && tr.dataset[f]) {
            el.value = tr.dataset[f];
          }
        });
      });
    });
  });

  // Ocultar mensajes de éxito/error después de 10s
  setTimeout(() => {
    const mensaje = document.getElementById('mensaje');
    if (mensaje) {
      mensaje.style.transition = 'opacity 1s ease';
      mensaje.style.opacity = '0';
      setTimeout(() => mensaje.remove(), 1000);
    }
  }, 10000);
</script>