<?php

// Funciones para obtener datos
function getKpis($pdo)
{
    $kpis = [
        'personal' => 0,
        'usuarios' => 0,
        'pacientes' => 0,
    ];

    $kpis['personal'] = $pdo->query("SELECT COUNT(*) FROM personal")->fetchColumn();
    $kpis['usuarios'] = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $kpis['pacientes'] = $pdo->query("SELECT COUNT(*) FROM pacientes")->fetchColumn();

    return $kpis;
}

function getTableData($pdo, $table, $limit = 20)
{
    $stmt = $pdo->prepare("SELECT * FROM `$table` ORDER BY id DESC LIMIT :limit");
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getPacienteHistorial($pdo, $pacienteId)
{
    $historial = [];

    // Datos generales del paciente
    $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = ?");
    $stmt->execute([$pacienteId]);
    $historial['paciente'] = $stmt->fetch();

    if (!$historial['paciente']) {
        return null;
    }

    // Consultas y detalles
    $stmt = $pdo->prepare("SELECT
        c.fecha_registro AS fecha,
        c.motivo_consulta,
        c.temperatura,
        c.frecuencia_cardiaca,
        c.peso_actual,
        c.pagado,
        c.precio,
        dc.operacion,
        dc.antecedentes_patologicos,
        dc.alergico,
        u.nombre_usuario AS usuario_atencion
    FROM consultas c
    JOIN detalle_consulta dc ON c.id = dc.id_consulta
    JOIN usuarios u ON c.id_usuario = u.id
    WHERE c.id_paciente = ?
    ORDER BY c.fecha_registro DESC");
    $stmt->execute([$pacienteId]);
    $historial['consultas'] = $stmt->fetchAll();

    // Ventas y consumos
    $stmt = $pdo->prepare("SELECT
        v.fecha AS fecha_venta,
        v.monto_total,
        v.metodo_pago,
        vd.cantidad,
        p.nombre AS producto_nombre
    FROM ventas v
    JOIN ventas_detalle vd ON v.id = vd.venta_id
    JOIN productos p ON vd.producto_id = p.id
    WHERE v.paciente_id = ?
    ORDER BY v.fecha DESC");
    $stmt->execute([$pacienteId]);
    $historial['consumos'] = $stmt->fetchAll();

    // Ingresos y egresos hospitalarios
    $stmt = $pdo->prepare("SELECT
        fecha_ingreso,
        fecha_alta,
        s.nombre AS sala_nombre
    FROM ingresos i
    JOIN salas_ingreso s ON i.id_sala = s.id
    WHERE i.id_paciente = ?
    ORDER BY fecha_ingreso DESC");
    $stmt->execute([$pacienteId]);
    $historial['ingresos'] = $stmt->fetchAll();

    return $historial;
}

// usuarios
$sql = "SELECT u.id,
            r.id AS id_rol,
            p.id AS id_personal,
            u.nombre_usuario,
            CONCAT(p.nombre, ' ',p.apellidos) AS personal, 
            p.correo,
            r.nombre AS rol
        FROM 
            usuarios u
        JOIN 
            personal p ON u.id_personal = p.id
        LEFT JOIN 
            roles r ON u.id_rol = r.id
        ORDER BY
            u.nombre_usuario ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$usuariosData = $stmt->fetchAll();

// Obtener datos para la vista
$kpis = getKpis($pdo);
$personalData = getTableData($pdo, 'personal');
//$usuariosData = getTableData($pdo, 'usuarios');
$pacientesData = getTableData($pdo, 'pacientes');



?>

<style>
    :root {
        --bs-body-bg: #f8f9fa;
        /* Fondo claro */
        --bs-body-color: #c2bfbfff;
        /* Texto oscuro */
    }



    .card {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .nav-pills .nav-link {
        /* color: var(--bs-body-color); */
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
    }

    .nav-pills .nav-link.active {
        background-color: #007bff;
        color: #fff;
    }

    .table thead th {
        border-bottom: 2px solid #e9ecef;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-action {
        border-radius: 50%;
        width: 38px;
        height: 38px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .offcanvas {
        background-color: #fff;
        border-left: 1px solid #e9ecef;
        box-shadow: -4px 0 12px rgba(0, 0, 0, 0.05);
    }

    .offcanvas-header,
    .offcanvas-body {
        /*  color: #333; */
    }

    .a4-document {
        width: 210mm;
        min-height: 297mm;
        padding: 20mm;
        margin: 0 auto;
        background: white;
        box-shadow: 0 0 5mm rgba(0, 0, 0, 0.1);
        font-family: 'Roboto', sans-serif;
        /*  color: #000; */
    }

    .a4-document h1,
    .a4-document h2,
    .a4-document h3 {
        font-weight: 500;
        color: #007bff;
    }

    .a4-document .section {
        margin-bottom: 20px;
    }

    .a4-document .section-title {
        font-weight: 500;
        /* color: #555; */
        border-bottom: 1px solid #ccc;
        padding-bottom: 5px;
        margin-bottom: 10px;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .a4-document,
        .a4-document * {
            visibility: visible;
        }

        .a4-document {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 10mm;
        }
    }
</style>


<div id="content" class="container-fluid py-4">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-light text-primary"><i class="bi bi-hospital me-2"></i> Gestión Clínica del Personal y Pacientes
        </h2>
        <button class="btn btn-outline-primary rounded-pill" data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasAcciones"><i class="bi bi-plus-circle me-2"></i> Nuevo Registro</button>
    </header>

    <!-- KPIs -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <h5 class="card-title text-muted">Total Personal</h5>
                <h2 class="card-text fw-bold text-primary"><?php echo $kpis['personal']; ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <h5 class="card-title text-muted">Usuarios Activos</h5>
                <h2 class="card-text fw-bold text-success"><?php echo $kpis['usuarios']; ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-4 text-center">
                <h5 class="card-title text-muted">Pacientes Registrados</h5>
                <h2 class="card-text fw-bold text-warning"><?php echo $kpis['pacientes']; ?></h2>
            </div>
        </div>
    </div>

    <hr>

    <!-- Navegación y Tablas -->
    <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-personal-tab" data-bs-toggle="pill"
                data-bs-target="#pills-personal" type="button"><i class="bi bi-people me-2"></i>Personal</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-usuarios-tab" data-bs-toggle="pill" data-bs-target="#pills-usuarios"
                type="button"><i class="bi bi-person-badge me-2"></i>Usuarios</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-pacientes-tab" data-bs-toggle="pill" data-bs-target="#pills-pacientes"
                type="button"><i class="bi bi-person-hearts me-2"></i>Pacientes</button>
        </li>
    </ul>
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
    <div class="tab-content" id="pills-tabContent">
        <!-- Personal -->
        <div class="tab-pane fade show active" id="pills-personal" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Últimos registros de Personal</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Especialidad</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personalData as $personal): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($personal['id']); ?></td>
                                    <td><?php echo htmlspecialchars($personal['nombre'] . ' ' . $personal['apellidos']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($personal['especialidad']); ?></td>
                                    <td><?php echo htmlspecialchars($personal['telefono']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action"
                                            onclick='showEditPersonalModal(<?php echo json_encode($personal); ?>)'><i
                                                class="bi bi-pencil"></i></button>
                                        <button class="btn btn-outline-danger btn-sm btn-action"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Usuarios -->
        <div class="tab-pane fade" id="pills-usuarios" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Últimos registros de Usuarios</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Personal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuariosData as $usuario): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['personal']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action"
                                            onclick='showEditUsuarioModal(<?php echo json_encode($usuario); ?>)'><i
                                                class="bi bi-pencil"></i></button>
                                        <button class="btn btn-outline-danger btn-sm btn-action"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pacientes -->
        <div class="tab-pane fade" id="pills-pacientes" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Últimos registros de Pacientes</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Sexo</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pacientesData as $paciente): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($paciente['id']); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellidos']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($paciente['sexo']); ?></td>
                                    <td><?php echo htmlspecialchars($paciente['telefono']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action"
                                            onclick='showEditPacienteModal(<?php echo json_encode($paciente); ?>)'><i
                                                class="bi bi-pencil"></i></button>
                                        <!-- Botón original (con evento JS) -->
                                        <button onclick="seleccionarFechas(<?php echo $paciente['id']; ?>)"
                                            class="btn btn-outline-info btn-sm btn-action">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </button>
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm btn-action"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Offcanvas Acciones -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAcciones">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-muted">Acciones rápidas</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalPersonal"><i
                    class="bi bi-person-plus me-2"></i> Registrar Personal</button>
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalUsuario"><i
                    class="bi bi-person-badge me-2"></i> Registrar Usuario</button>
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalPaciente"><i
                    class="bi bi-person-hearts me-2"></i> Registrar Paciente</button>
        </div>
    </div>

    <!-- Toast de notificación -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    <!-- El mensaje se insertará aquí con JavaScript -->
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
</div>

<!-- Modales de para solicitar pdf de historial de paciente -->
<?php require 'modals/modals_historial_fpdf.php'; ?>
<!-- Modales de Registro -->
<?php require 'modals/modals_registro_usuarios.php'; ?>
<!-- Modales de Edición y Historial -->
<?php require 'modals/modals_edicion_usuarios.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script para mostrar el toast
    function showToast(message, isError = false) {
        const toastElement = document.getElementById('liveToast');
        const toastBody = toastElement.querySelector('.toast-body');

        if (isError) {
            toastElement.classList.remove('bg-success');
            toastElement.classList.add('bg-danger');
            toastBody.innerHTML = '<i class="bi bi-x-circle me-2"></i> ' + message;
        } else {
            toastElement.classList.remove('bg-danger');
            toastElement.classList.add('bg-success');
            toastBody.innerHTML = '<i class="bi bi-check-circle me-2"></i> ' + message;
        }

        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    }

    // Lógica para mostrar los modales de edición
    function showEditPersonalModal(personal) {
        document.getElementById('edit_personal_id').value = personal.id;
        document.getElementById('edit_nombrePersonal').value = personal.nombre;
        document.getElementById('edit_apellidosPersonal').value = personal.apellidos;
        document.getElementById('edit_fechaNacimientoPersonal').value = personal.fecha_nacimiento;
        document.getElementById('edit_telefonoPersonal').value = personal.telefono;
        document.getElementById('edit_especialidadPersonal').value = personal.especialidad;
        document.getElementById('edit_direccionPersonal').value = personal.direccion;
        document.getElementById('edit_correoPersonal').value = personal.correo;
        const modal = new bootstrap.Modal(document.getElementById('modalEditPersonal'));
        modal.show();
    }

    function showEditUsuarioModal(usuario) {
        document.getElementById('edit_usuario_id').value = usuario.id;
        document.getElementById('edit_nombreUsuario').value = usuario.nombre_usuario;
        document.getElementById('edit_rolUsuario').value = usuario.id_rol;
        document.getElementById('edit_personalAsociado').value = usuario.id_personal;
       
        const modal = new bootstrap.Modal(document.getElementById('modalEditUsuario'));
        modal.show();
    }

    function showEditPacienteModal(paciente) {
        document.getElementById('edit_paciente_id').value = paciente.id;
        document.getElementById('edit_nombrePaciente').value = paciente.nombre;
        document.getElementById('edit_apellidosPaciente').value = paciente.apellidos;
        document.getElementById('edit_fechaNacimientoPaciente').value = paciente.fecha_nacimiento;
        document.getElementById('edit_sexoPaciente').value = paciente.sexo;
        document.getElementById('edit_telefonoPaciente').value = paciente.telefono;
        document.getElementById('edit_emailPaciente').value = paciente.email;
        document.getElementById('edit_dipPaciente').value = paciente.dip;
        document.getElementById('edit_profesionPaciente').value = paciente.profesion;
        document.getElementById('edit_ocupacionPaciente').value = paciente.ocupacion;
        document.getElementById('edit_tutorNombrePaciente').value = paciente.tutor_nombre;
        document.getElementById('edit_telefonoTutorPaciente').value = paciente.telefono_tutor;
        document.getElementById('edit_direccionPaciente').value = paciente.direccion;
        const modal = new bootstrap.Modal(document.getElementById('modalEditPaciente'));
        modal.show();
    }

    // Chequear al cargar la página si hay mensajes en la URL
    window.onload = function () {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            showToast(decodeURIComponent(urlParams.get('message')), false);
        } else if (urlParams.has('error')) {
            showToast(decodeURIComponent(urlParams.get('message')), true);
        }
    };

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