<?php

$idUsuario = $_SESSION['usuario']['id'] ?? 0;
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

// Obtener lista de consultas
$sql = "SELECT c.*, p.nombre, p.apellidos
        FROM consultas c
        LEFT JOIN pacientes p ON c.id_paciente = p.id
        ORDER BY c.fecha_registro DESC";
$consultas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Obtener pacientes para selects
$pacientes = $pdo->query("SELECT id, nombre, apellidos FROM pacientes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Definir campos y sus placeholders
$campos = [
    "temperatura" => ["label" => "Temperatura (°C)", "placeholder" => "Ej: 36.5"],
    "control_cada_horas" => ["label" => "Control cada (horas)", "placeholder" => "Ej: 8 horas"],
    "frecuencia_cardiaca" => ["label" => "Frecuencia cardíaca (bpm)", "placeholder" => "Ej: 75"],
    "frecuencia_respiratoria" => ["label" => "Frecuencia respiratoria (rpm)", "placeholder" => "Ej: 16"],
    "tension_arterial" => ["label" => "Tensión arterial (mmHg)", "placeholder" => "Ej: 120/80"],
    "pulso" => ["label" => "Pulso (bpm)", "placeholder" => "Ej: 72"],
    "saturacion_oxigeno" => ["label" => "Saturación O₂ (%)", "placeholder" => "Ej: 98"],
    "peso_anterior" => ["label" => "Peso anterior (kg)", "placeholder" => "Ej: 70"],
    "peso_actual" => ["label" => "Peso actual (kg)", "placeholder" => "Ej: 69.8"],
    "peso_ideal" => ["label" => "Peso ideal (kg)", "placeholder" => "Ej: 72"],
    "imc" => ["label" => "IMC", "placeholder" => "Ej: 22.5"]
];

// Campos booleanos
$campos_booleanos = [
    "operacion" => "Operación",
    "orina" => "Orina",
    "defeca" => "Defeca",
    "duerme" => "Duerme",
    "antecedentes_patologicos" => "Antecedentes Patológicos",
    "antecedentes_familiares" => "Antecedentes Familiares",
    "antecedentes_conyuge" => "Antecedentes del Cónyuge",
    "control_signos_vitales" => "Control de signos vitales",
    "alergico" => "Alergias"
];
?>

<div class="container-fluid" id="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="bi bi-clipboard-pulse me-2"></i>Listado de Consultas</h3>
        <?php if ($rol === 'administrador'): ?>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <i class="bi bi-plus-circle me-1"></i> Nueva Consulta
            </button>
        <?php endif;?>
    </div>

    <div class="row mb-3">
        <div class="col-md-4 ms-auto">
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
                            <tr id="consultaFila-<?= $c['id'] ?>">
                                <td><?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['nombre'] . ' ' . $c['apellidos']) ?></td>
                                <td><?= nl2br(htmlspecialchars($c['motivo_consulta'])) ?></td>
                                <td><?= $c['temperatura'] ?> °C</td>
                                <td><?= $c['pulso'] ?> bpm</td>
                                <td><?= $c['peso_actual'] ?> kg</td>
                                <td><?= $c['imc'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($c['fecha_registro'])) ?></td>
                                <td class="text-nowrap">
                                    <?php if ($rol === 'administrador'): ?>
                                        <button class="btn btn-sm btn-primary editar-consulta" data-id="<?= $c['id'] ?>"
                                                data-bs-toggle="modal" data-bs-target="#modal-editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <a href="eliminar_consulta.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('¿Eliminar esta consulta?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif;?>
                                    <button class="btn btn-sm btn-info ver-detalles-consulta" data-id="<?= $c['id'] ?>"
                                            data-bs-toggle="modal" data-bs-target="#modalDetallesConsulta">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

 
<div class="modal fade" id="modal-editar" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="api/actualizar_consulta.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Consulta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="edit-id">
                <input type="hidden" name="id_usuario" value="<?= $idUsuario ?> ">

                <div class="col-md-12">
                    <label>Paciente</label>
                    <select name="id_paciente" id="edit-paciente" class="form-select" required readonly>
                        <option value="">Seleccione</option>
                        <?php foreach ($pacientes as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellidos']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-12">
                    <label>Motivo de consulta</label>
                    <textarea name="motivo_consulta" id="edit-motivo" class="form-control" rows="2" placeholder="Describa el motivo de la consulta..." required></textarea>
                </div>

                <?php foreach ($campos as $name => $vals): ?>
                    <div class="col-md-4">
                        <label><?= $vals['label'] ?></label>
                        <input type="<?= in_array($name, ['tension_arterial']) ? 'text' : 'number' ?>" step="any" name="<?= $name ?>"
                               id="edit-<?= $name ?>" class="form-control" placeholder="<?= $vals['placeholder'] ?>">
                    </div>
                <?php endforeach; ?>

                <hr class="my-4">
                <h5 class="text-primary">Detalles Clínicos</h5>

                <?php foreach ($campos_booleanos as $name => $label): ?>
                    <div class="col-md-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" name="<?= $name ?>" id="edit-<?= $name ?>">
                            <label class="form-check-label" for="edit-<?= $name ?>"><?= $label ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="col-md-3">
                    <label>Días que defeca</label>
                    <input type="number" name="defeca_dias" id="edit-defeca_dias" class="form-control" min="0" placeholder="Ej: 1, 2, 3...">
                </div>

                <div class="col-md-3">
                    <label>Horas que duerme</label>
                    <input type="number" name="duerme_horas" id="edit-duerme_horas" class="form-control" min="0" max="24" placeholder="Ej: 8 horas">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

 
<div class="modal fade" id="modalCrear" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form action="api/guardar_consulta.php" method="POST" class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-plus-circle me-2"></i> Nueva Consulta Clínica
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                <input type="hidden" name="precio" value="<?= (int) 1000 ?>">

                <div class="mb-4">
                    <label class="form-label fw-semibold"><i class="bi bi-person-badge-fill me-2"></i>Paciente <span
                                class="text-danger">*</span></label>
                    <input type="text" id="buscador-paciente" class="form-control" placeholder="Buscar por nombre...">
                    <div id="resultado-paciente" class="mt-2 border rounded bg-white p-2 overflow-auto"
                         style="max-height: 200px;"></div>
                    <input type="hidden" name="id_paciente" id="input-id-paciente" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold"><i class="bi bi-chat-text-fill me-2"></i>Motivo de Consulta</label>
                    <textarea name="motivo_consulta" class="form-control" rows="2" required placeholder="Ej: Dolor abdominal, chequeo de rutina, etc."></textarea>
                </div>

                <div class="row g-3">
                    <?php
                    foreach ($campos as $name => $vals): ?>
                        <div class="col-md-3">
                            <label class="form-label fw-medium"><i class="bi bi-clipboard-pulse me-1"></i><?= $vals['label'] ?></label>
                            <input type="<?= in_array($name, ['tension_arterial']) ? 'text' : 'number' ?>" step="any"
                                   name="<?= $name ?>" class="form-control" placeholder="<?= $vals['placeholder'] ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr class="my-4">
                <h5 class="text-success"><i class="bi bi-file-medical me-2"></i>Detalles Clínicos</h5>

                <div class="row g-3 mt-2">
                    <?php foreach ($campos_booleanos as $name => $label): ?>
                        <div class="col-md-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="<?= $name ?>" id="create-<?= $name ?>">
                                <label class="form-check-label" for="create-<?= $name ?>"><?= $label ?></label>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="col-md-3">
                        <label class="form-label">Días que defeca</label>
                        <input type="number" name="defeca_dias" class="form-control" min="0" placeholder="Ej: 1, 2, 3...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Horas que duerme</label>
                        <input type="number" name="duerme_horas" class="form-control" min="0" max="24" placeholder="Ej: 8 horas">
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light rounded-bottom-4 px-4 py-3">
                <button class="btn btn-success px-4"><i class="bi bi-save me-1"></i> Guardar Consulta</button>
            </div>
        </form>
    </div>
</div>

 
<div class="modal fade" id="modalDetallesConsulta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-info-circle-fill me-2"></i>Detalles de la Consulta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="contenido-detalles-consulta" class="p-2 text-center">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

 
<div class="modal fade" id="modalPagoConsulta" tabindex="-1" aria-labelledby="modalPagoConsultaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalPagoConsultaLabel"><i class="bi bi-credit-card-2-front-fill me-2"></i>Pago de Consulta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formPagoConsulta">
                <div class="modal-body">
                    <input type="hidden" id="consulta_id" name="consulta_id">
                    <div class="mb-3">
                        <label for="monto" class="form-label">Monto a pagar</label>
                        <input type="number" class="form-control" id="monto" name="monto" min="500" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Pagar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

 
 

<script>
    // Detectar clic en botón de pago
    document.querySelectorAll('.ver-detalles-consulta').forEach(btn => {
        btn.addEventListener('click', function() {
            const idConsulta = this.getAttribute('data-id');
            document.getElementById('consulta_id').value = idConsulta;
        });
    });

    // Envío del formulario de pago
    document.getElementById('formPagoConsulta').addEventListener('submit', function(e) {
        e.preventDefault();

        const datos = new FormData(this);

        fetch('api/actualizar_pago.php', {
            method: 'POST',
            body: datos
        })
        .then(res => res.json())
        .then(respuesta => {
            if (respuesta.success) {
                // Cierra el modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalPagoConsulta'));
                modal.hide();

                // Opcional: pequeño retraso antes de recargar
                setTimeout(() => {
                    location.reload(); // 🔁 Recarga la página
                }, 500);
            } else {
                alert('Error: ' + respuesta.message);
            }
        })
        .catch(error => {
            console.error('Error en el pago:', error);
        });
    });

    document.addEventListener("DOMContentLoaded", () => {
        const botonesDetalles = document.querySelectorAll(".ver-detalles-consulta");
        const contenedor = document.getElementById("contenido-detalles-consulta");

        botonesDetalles.forEach(boton => {
            boton.addEventListener("click", () => {
                const id = boton.dataset.id;

                // Muestra spinner mientras se carga
                contenedor.innerHTML = `
                <div class="text-center my-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>`;

                // Realiza la petición
                fetch("api/detalles_consulta.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: new URLSearchParams({
                            id
                        })
                    })
                    .then(response => response.text())
                    .then(html => {
                        contenedor.innerHTML = html;
                    })
                    .catch(err => {
                        console.error(err);
                        contenedor.innerHTML = `<div class="alert alert-danger">Ocurrió un error al cargar los detalles.</div>`;
                    });
            });
        });
    });

    const buscadorPaciente = document.getElementById('buscador-paciente');
    const resultadoPaciente = document.getElementById('resultado-paciente');
    const inputPacienteId = document.getElementById('input-id-paciente');

    buscadorPaciente.addEventListener('input', async () => {
        const query = buscadorPaciente.value.trim();
        if (query.length < 2) {
            resultadoPaciente.innerHTML = '';
            return;
        }

        try {
            const res = await fetch(`api/buscar_pacientes.php?q=${encodeURIComponent(query)}`);
            const data = await res.json();

            resultadoPaciente.innerHTML = '';
            data.forEach(paciente => {
                const div = document.createElement('div');
                div.className = 'd-flex align-items-center justify-content-between border-bottom py-2';
                div.innerHTML = `
                    <span><i class="bi bi-person-circle me-1"></i> ${paciente.nombre} ${paciente.apellidos}</span>
                    <button class="btn btn-sm btn-outline-success seleccionar-paciente" data-id="${paciente.id}">
                        <i class="bi bi-check-circle"></i>
                    </button>`;
                resultadoPaciente.appendChild(div);
            });

            document.querySelectorAll('.seleccionar-paciente').forEach(btn => {
                btn.addEventListener('click', () => {
                    inputPacienteId.value = btn.getAttribute('data-id');
                    buscadorPaciente.value = btn.previousElementSibling.textContent.trim();
                    resultadoPaciente.innerHTML = '';
                });
            });

        } catch (err) {
            resultadoPaciente.innerHTML = '<div class="text-danger">Error al buscar pacientes.</div>';
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const botones = document.querySelectorAll('.editar-consulta');

        botones.forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;

                try {
                    const response = await fetch(`api/obtener_consulta.php?id=${id}`);
                    const data = await response.json();

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
                        if (input) {
                            if (input.type === 'checkbox') {
                                input.checked = data.detalle[campo] === 1;
                            } else {
                                input.value = data.detalle[campo] ?? '';
                            }
                        }
                    });

                } catch (error) {
                    console.error('Error cargando datos de la consulta:', error);
                    alert('Ocurrió un error al cargar los datos.');
                    console.log(id);
                }
            });
        });
    });

    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            alert.classList.add('fade');
        }
    }, 10000); // 10 segundos
</script>