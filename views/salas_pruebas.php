<?php


function getTableData($pdo, $table, $limit = 20)
{
    $stmt = $pdo->prepare("SELECT * FROM `$table` ORDER BY id DESC LIMIT :limit");
    $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Obtener datos para las tablas
$tiposPruebasData = getTableData($pdo, 'tipo_pruebas');
$salasIngresoData = getTableData($pdo, 'salas_ingreso');

?>

<style>
    :root {
        --bs-body-bg: #f8f9fa;
        --bs-body-color: #333;
    }

    body {
        background-color: var(--bs-body-bg);
        color: var(--bs-body-color);
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
        color: #333;
    }
</style>


<div id="content" class="container-fluid py-4">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-light text-primary"><i class="bi bi-hospital me-2"></i> Gestión de Salas y Pruebas</h2>
        <button class="btn btn-outline-primary rounded-pill" data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasAcciones"><i class="bi bi-plus-circle me-2"></i> Nuevo Registro</button>
    </header>

    <hr>

    <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-salas-tab" data-bs-toggle="pill" data-bs-target="#pills-salas"
                type="button"><i class="bi bi-hospital-fill me-2"></i>Salas</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-pruebas-tab" data-bs-toggle="pill" data-bs-target="#pills-pruebas"
                type="button"><i class="bi bi-journal-medical me-2"></i>Pruebas</button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-salas" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Últimos registros de Salas de Ingreso</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($salasIngresoData as $sala): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sala['id']); ?></td>
                                    <td><?php echo htmlspecialchars($sala['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($sala['fecha_registro']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action"
                                            onclick='showEditSalaModal(<?php echo json_encode($sala); ?>)'><i
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

        <div class="tab-pane fade" id="pills-pruebas" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Últimos registros de Tipos de Pruebas</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tiposPruebasData as $prueba): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prueba['id']); ?></td>
                                    <td><?php echo htmlspecialchars($prueba['nombre']); ?></td>
                                   <td><?php echo 'XAF ' . number_format((float)$prueba['precio'], 0, ',', '.'); ?></td>

                                    <td><?php echo htmlspecialchars($prueba['fecha_registro']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action"
                                            onclick='showEditPruebaModal(<?php echo json_encode($prueba); ?>)'><i
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
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAcciones">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-muted">Acciones rápidas</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalSala"><i
                    class="bi bi-plus-lg me-2"></i> Registrar Sala</button>
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalPrueba"><i
                    class="bi bi-plus-lg me-2"></i> Registrar Prueba</button>
        </div>
    </div>
</div>

<?php require 'modals/modals_registro_salas.php'; ?>
<?php require 'modals/modals_registro_pruebas.php'; ?>

<?php require 'modals/modals_edicion_salas.php'; ?>
<?php require 'modals/modals_edicion_pruebas.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showEditSalaModal(sala) {
        document.getElementById('edit_sala_id').value = sala.id;
        document.getElementById('edit_nombreSala').value = sala.nombre;
        const modal = new bootstrap.Modal(document.getElementById('modalEditSala'));
        modal.show();
    }

    function showEditPruebaModal(prueba) {
        document.getElementById('edit_prueba_id').value = prueba.id;
        document.getElementById('edit_nombrePrueba').value = prueba.nombre;
        document.getElementById('edit_precioPrueba').value = prueba.precio;
        const modal = new bootstrap.Modal(document.getElementById('modalEditPrueba'));
        modal.show();
    }
</script>