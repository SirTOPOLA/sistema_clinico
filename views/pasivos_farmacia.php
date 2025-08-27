<?php
// Este archivo requiere la inclusión de 'config/conexion.php' y una sesión activa
// La inclusión debe estar en el archivo principal que carga esta vista.
// Por ejemplo:
// require_once 'config/conexion.php'; 
// session_start();

// Lógica para obtener los datos de la base de datos
try {
    // Consulta SQL para seleccionar todos los pagos de la tabla 'pagos_proveedores'
    $sql = "SELECT 
                p.id, 
                p.compra_id, 
                prov.nombre AS nombre_proveedor, 
                p.monto, 
                p.fecha, 
                p.metodo_pago 
            FROM pagos_proveedores p
            INNER JOIN proveedores prov ON p.proveedor_id = prov.id
            ORDER BY p.fecha DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pagos_proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $sql = "SELECT c.id, c.monto_pendiente, c.estado_pago, c.fecha, p.nombre AS nombre_proveedor
            FROM compras c
            INNER JOIN proveedores p ON c.proveedor_id = p.id
            WHERE c.estado_pago IN ('PENDIENTE', 'PARCIAL')
            ORDER BY c.fecha DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pasivos = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    // Si hay un error, se guarda un mensaje en la sesión
    $_SESSION['error'] = 'Error de base de datos: ' . $e->getMessage();
    $pagos_proveedores = []; // Inicializa la variable para evitar errores en la vista
}
?>

<div class="container-fluid" id="content">

    <!-- Encabezado y botón para registrar pago -->
    <div class="row mb-3">
        <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-arrow-left-right me-2"></i>Gestión de Pagos a Proveedores</h3>
            <div class="d-flex gap-2">
                <?php if (!empty($pasivos)): ?>
                    <button id="pasivos" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCargarDeudas">
                        <i class="bi bi-card-checklist me-1"></i> Ver deudas
                    </button>
                <?php endif; ?>

                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearPago">
                    <i class="bi bi-plus-circle me-1"></i>Registrar Pago
                </button>
            </div>
        </div>
        <div class="col-md-4">
            <input type="text" id="buscadorPagos" class="form-control" placeholder="Buscar pago...">
        </div>
    </div>

    <!-- Mensajes de estado (PHP) -->
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

    <!-- Tabla de pagos -->
    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table id="tablaPagos" class="table table-hover table-bordered table-sm align-middle">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th>ID</th>
                        <th>ID Compra</th>
                        <th>Proveedor</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Método de Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Bucle para mostrar los pagos dinámicamente
                    foreach ($pagos_proveedores as $pago):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($pago['id']) ?></td>
                            <td><?= htmlspecialchars($pago['compra_id']) ?></td>
                            <td><?= htmlspecialchars($pago['nombre_proveedor']) ?></td>
                            <td class="monto-pago"><?= 'XAF' . ' ' . number_format($pago['monto'], 0) ?></td>
                            <td class="fecha-pago"><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                            <td class="metodo-pago"><?= htmlspecialchars($pago['metodo_pago']) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-info btn-ver-detalles-pago"
                                    data-id="<?= $pago['id'] ?>" data-compra-id="<?= $pago['compra_id'] ?>"
                                    data-monto-pago="<?= $pago['monto'] ?>" data-fecha-pago="<?= $pago['fecha'] ?>"
                                    data-metodo-pago="<?= $pago['metodo_pago'] ?>" data-bs-toggle="modal"
                                    data-bs-target="#modalDetallesPago">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary btn-editar-pago" data-id="<?= $pago['id'] ?>"
                                    data-compra-id="<?= $pago['compra_id'] ?>" data-monto="<?= $pago['monto'] ?>"
                                    data-fecha="<?= $pago['fecha'] ?>" data-metodo="<?= $pago['metodo_pago'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarPago">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="eliminar_pago.php?id=<?= $pago['id'] ?>" class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Eliminar este pago?')">
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

<!-- Modal para Registrar Pago -->
<div class="modal fade" id="modalCrearPago" tabindex="-1">
    <div class="modal-dialog">
        <form action="api/guardar_pasivos_compra.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-12">
                    <label class="form-label">ID de Compra</label>
                    <input type="number" name="compra_id" id="compra_id_input_crear" class="form-control" required>
                    <div id="compra-feedback-message-crear" class="mt-2" style="font-size: 0.9em;"></div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">ID de Proveedor</label>
                    <input type="number" name="proveedor_id" id="proveedor_id_input_crear" class="form-control" readonly
                        required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Monto Pendiente</label>
                    <input type="text" id="monto_pendiente_display_crear" class="form-control" readonly>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Monto del Pago</label>
                    <input type="number" name="monto" id="monto_input_crear" class="form-control" step="0.01" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Fecha del Pago</label>
                    <input type="date" name="fecha" id="fecha_input_crear" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Método de Pago</label>
                    <select name="metodo_pago" id="metodo_pago_input_crear" class="form-select">
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="TARJETA">Tarjeta</option>
                        <option value="OTRO">Otro</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success" id="btn-guardar-pago"><i class="bi bi-save me-1"></i>
                    Guardar Pago</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Editar Pago -->
<div class="modal fade" id="modalEditarPago" tabindex="-1">
    <div class="modal-dialog">
        <form action="api/actualizar_pasivos_compra.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="edit-id-pago">
                <div class="col-md-12">
                    <label class="form-label">ID de Compra</label>
                    <input type="number" name="compra_id" id="compra_id_input_editar" class="form-control" required>
                    <div id="compra-feedback-message-editar" class="mt-2" style="font-size: 0.9em;"></div>
                </div>
                <div class="col-md-12">
                    <label class="form-label">ID de Proveedor</label>
                    <input type="number" name="proveedor_id" id="proveedor_id_input_editar" class="form-control"
                        readonly required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Monto Pendiente</label>
                    <input type="text" id="monto_pendiente_display_editar" class="form-control" readonly>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Monto del Pago</label>
                    <input type="number" name="monto" id="monto_input_editar" class="form-control" step="0.01" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Fecha del Pago</label>
                    <input type="date" name="fecha" id="fecha_input_editar" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Método de Pago</label>
                    <select name="metodo_pago" id="metodo_pago_input_editar" class="form-select">
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="TARJETA">Tarjeta</option>
                        <option value="OTRO">Otro</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Nuevo Modal para Ver Compras con Deudas -->
<div class="modal fade" id="modalCargarDeudas" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-card-checklist me-2"></i>Compras con Deudas Pendientes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deudas-loading" class="text-center">Cargando...</p>
                <div id="tabla-deudas-container" style="display: none;">
                    <table class="table table-hover table-bordered table-sm align-middle">
                        <thead class="table-light text-nowrap">
                            <tr>
                                <th>ID Compra</th>
                                <th>Proveedor</th>
                                <th>Monto Pendiente</th>
                                <th>Estado</th>
                                <th>Fecha Compra</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-deudas-body">
                            <!-- Datos cargados por JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div id="deudas-error" class="alert alert-warning" style="display: none;">
                    No se encontraron compras pendientes o parciales.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Visualizar Detalles del Pago -->
<div class="modal fade" id="modalDetallesPago" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Detalles del Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detalles-pago-container" style="display: none;">
                    <p><strong>ID del Pago:</strong> <span id="detalle-id-pago"></span></p>
                    <p><strong>ID de Compra:</strong> <span id="detalle-compra-id"></span></p>
                    <p><strong>Proveedor:</strong> <span id="detalle-proveedor"></span></p>
                    <p><strong>Monto del Pago:</strong> <span id="detalle-monto-pago"></span></p>
                    <p><strong>Fecha del Pago:</strong> <span id="detalle-fecha-pago"></span></p>
                    <p><strong>Método de Pago:</strong> <span id="detalle-metodo-pago"></span></p>
                    <hr>
                    <h5>Detalles de la Compra</h5>
                    <div id="compra-details-container">
                        <p><strong>Fecha de Compra:</strong> <span id="detalle-fecha-compra"></span></p>
                        <p><strong>Monto Total Compra:</strong> <span id="detalle-monto-compra"></span></p>
                        <p><strong>Monto Pendiente Compra:</strong> <span id="detalle-monto-pendiente-compra"
                                class="text-danger"></span></p>
                        <p><strong>Estado de Pago:</strong> <span id="detalle-estado-pago-compra"></span></p>
                    </div>
                </div>
                <p id="detalles-loading" class="text-center">Cargando...</p>
                <div id="detalles-error" class="alert alert-danger" style="display: none;">Error al cargar los detalles.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Scripts de JavaScript (para funcionalidad en la vista) -->
<script>
    // Script para ocultar los mensajes de estado
    setTimeout(() => {
        const mensaje = document.getElementById('mensaje');
        if (mensaje) {
            mensaje.style.transition = 'opacity 1s ease';
            mensaje.style.opacity = '0';
            setTimeout(() => mensaje.remove(), 1000);
        }
    }, 10000);

    // Lógica para validar el ID de compra y cargar los datos
    document.addEventListener('DOMContentLoaded', function () {
        // Lógica para el modal de CREAR
        const compraIdInputCrear = document.getElementById('compra_id_input_crear');
        const proveedorIdInputCrear = document.getElementById('proveedor_id_input_crear');
        const montoPendienteDisplayCrear = document.getElementById('monto_pendiente_display_crear');
        const feedbackMessageCrear = document.getElementById('compra-feedback-message-crear');
        const btnGuardarPagoCrear = document.getElementById('btn-guardar-pago');

        compraIdInputCrear.addEventListener('change', async function () {
            const compraId = this.value;
            proveedorIdInputCrear.value = '';
            montoPendienteDisplayCrear.value = '';
            feedbackMessageCrear.innerHTML = '';
            btnGuardarPagoCrear.disabled = true;

            if (compraId) {
                try {
                    const response = await fetch(`api/obtener_datos_compra.php?id=${compraId}`);
                    const data = await response.json();

                    if (response.ok && data.success) {
                        const compra = data.compra;
                        if (compra.estado_pago === 'PENDIENTE' || compra.estado_pago === 'PARCIAL') {
                            proveedorIdInputCrear.value = compra.proveedor_id;
                            montoPendienteDisplayCrear.value = `XAF ${parseFloat(compra.monto_pendiente).toFixed(2)}`;
                            feedbackMessageCrear.className = 'mt-2 text-success';
                            feedbackMessageCrear.innerHTML = '<i class="bi bi-check-circle-fill"></i> Compra válida. Proveedor y monto cargados.';
                            btnGuardarPagoCrear.disabled = false;
                        } else {
                            feedbackMessageCrear.className = 'mt-2 text-warning';
                            feedbackMessageCrear.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i> Esta compra ya ha sido pagada.';
                        }
                    } else {
                        feedbackMessageCrear.className = 'mt-2 text-danger';
                        feedbackMessageCrear.innerHTML = '<i class="bi bi-x-circle-fill"></i> ID de compra no encontrado.';
                    }
                } catch (error) {
                    feedbackMessageCrear.className = 'mt-2 text-danger';
                    feedbackMessageCrear.innerHTML = '<i class="bi bi-exclamation-octagon-fill"></i> Error de conexión. Intente de nuevo.';
                    console.error('Error al obtener datos de la compra:', error);
                }
            }
        });

        // Lógica para el modal de EDITAR
        const modalEditar = document.getElementById('modalEditarPago');
        const compraIdInputEditar = document.getElementById('compra_id_input_editar');
        const proveedorIdInputEditar = document.getElementById('proveedor_id_input_editar');
        const montoPendienteDisplayEditar = document.getElementById('monto_pendiente_display_editar');
        const montoInputEditar = document.getElementById('monto_input_editar');
        const fechaInputEditar = document.getElementById('fecha_input_editar');
        const metodoPagoInputEditar = document.getElementById('metodo_pago_input_editar');
        const pagoIdInputEditar = document.getElementById('edit-id-pago');
        const feedbackMessageEditar = document.getElementById('compra-feedback-message-editar');

        // Cuando se hace clic en un botón de edición, se cargan los datos en el modal
        modalEditar.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const pagoId = button.getAttribute('data-id');
            const compraId = button.getAttribute('data-compra-id');
            const monto = button.getAttribute('data-monto');
            const fecha = button.getAttribute('data-fecha');
            const metodo = button.getAttribute('data-metodo');

            pagoIdInputEditar.value = pagoId;
            compraIdInputEditar.value = compraId;
            montoInputEditar.value = monto;
            fechaInputEditar.value = fecha;
            metodoPagoInputEditar.value = metodo;

            // Disparar el evento 'change' para cargar el proveedor y el monto pendiente
            // Esto asegura que la lógica de validación se ejecute al abrir el modal
            const eventChange = new Event('change');
            compraIdInputEditar.dispatchEvent(eventChange);
        });

        compraIdInputEditar.addEventListener('change', async function () {
            const compraId = this.value;
            proveedorIdInputEditar.value = '';
            montoPendienteDisplayEditar.value = '';
            feedbackMessageEditar.innerHTML = '';

            if (compraId) {
                try {
                    const response = await fetch(`api/obtener_datos_compra.php?id=${compraId}`);
                    const data = await response.json();

                    if (response.ok && data.success) {
                        const compra = data.compra;
                        proveedorIdInputEditar.value = compra.proveedor_id;
                        montoPendienteDisplayEditar.value = `XAF ${parseFloat(compra.monto_pendiente).toFixed(2)}`;
                        feedbackMessageEditar.className = 'mt-2 text-success';
                        feedbackMessageEditar.innerHTML = '<i class="bi bi-check-circle-fill"></i> Compra válida. Proveedor y monto cargados.';
                    } else {
                        feedbackMessageEditar.className = 'mt-2 text-danger';
                        feedbackMessageEditar.innerHTML = '<i class="bi bi-x-circle-fill"></i> ID de compra no encontrado.';
                    }
                } catch (error) {
                    feedbackMessageEditar.className = 'mt-2 text-danger';
                    feedbackMessageEditar.innerHTML = '<i class="bi bi-exclamation-octagon-fill"></i> Error de conexión. Intente de nuevo.';
                    console.error('Error al obtener datos de la compra:', error);
                }
            }
        });

    });



    // Lógica para cargar la tabla de deudas al abrir el modal
    const modalDeudas = document.getElementById('modalCargarDeudas');
    modalDeudas.addEventListener('show.bs.modal', async function () {
        const loadingMessage = document.getElementById('deudas-loading');
        const tableContainer = document.getElementById('tabla-deudas-container');
        const errorAlert = document.getElementById('deudas-error');
        const tableBody = document.getElementById('tabla-deudas-body');
        const botonPasivos = document.getElementById('pasivos');

        // Resetear el estado
        loadingMessage.style.display = 'block';
        tableContainer.style.display = 'none';
        errorAlert.style.display = 'none';
        tableBody.innerHTML = '';

        try {
            const response = await fetch('api/obtener_pasivos_compra.php');
            const data = await response.json();

            loadingMessage.style.display = 'none';

            if (response.ok && data.success && data.deudas.length > 0) {
                tableContainer.style.display = 'block';
                data.deudas.forEach(deuda => {
                    const row = `
                        <tr>
                            <td>${deuda.id}</td>
                            <td>${deuda.nombre_proveedor}</td>
                            <td>XAF ${parseFloat(deuda.monto_pendiente).toFixed(2)}</td>
                            <td>${deuda.estado_pago}</td>
                            <td>${deuda.fecha}</td>
                        </tr>
                    `;
                    tableBody.innerHTML += row;
                });
            } else {
                errorAlert.style.display = 'block';

            }
        } catch (error) {
            loadingMessage.style.display = 'none';
            errorAlert.style.display = 'block';
            errorAlert.innerHTML = 'Error al cargar las deudas. Intente de nuevo.';
            console.error('Error al obtener deudas:', error);
        }
    });

    // Lógica para el nuevo modal de detalles del pago
    const modalDetalles = document.getElementById('modalDetallesPago');
    modalDetalles.addEventListener('show.bs.modal', async function (event) {
        const button = event.relatedTarget;
        const compraId = button.getAttribute('data-compra-id');

        const detallesPagoContainer = document.getElementById('detalles-pago-container');
        const loadingMessage = document.getElementById('detalles-loading');
        const errorAlert = document.getElementById('detalles-error');

        // Ocultar detalles previos y mostrar el cargando
        detallesPagoContainer.style.display = 'none';
        errorAlert.style.display = 'none';
        loadingMessage.style.display = 'block';

        // Llenar los datos del pago
        document.getElementById('detalle-id-pago').textContent = button.getAttribute('data-id');
        document.getElementById('detalle-compra-id').textContent = compraId;
        document.getElementById('detalle-monto-pago').textContent = `XAF ${parseFloat(button.getAttribute('data-monto-pago')).toFixed(2)}`;
        document.getElementById('detalle-fecha-pago').textContent = new Date(button.getAttribute('data-fecha-pago')).toLocaleDateString('es-ES');
        document.getElementById('detalle-metodo-pago').textContent = button.getAttribute('data-metodo-pago');

        if (compraId) {
            try {
                // Obtener datos de la compra
                const response = await fetch(`api/obtener_datos_compra.php?id=${compraId}`);
                const data = await response.json();

                if (response.ok && data.success) {
                    const compra = data.compra;
                    document.getElementById('detalle-proveedor').textContent = compra.nombre_proveedor;
                    document.getElementById('detalle-fecha-compra').textContent = new Date(compra.fecha).toLocaleDateString('es-ES');
                    document.getElementById('detalle-monto-compra').textContent = `XAF ${parseFloat(compra.total).toFixed(2)}`;
                    document.getElementById('detalle-monto-pendiente-compra').textContent = `XAF ${parseFloat(compra.monto_pendiente).toFixed(2)}`;
                    document.getElementById('detalle-estado-pago-compra').textContent = compra.estado_pago;

                    loadingMessage.style.display = 'none';
                    detallesPagoContainer.style.display = 'block';
                } else {
                    loadingMessage.style.display = 'none';
                    errorAlert.style.display = 'block';
                    errorAlert.textContent = 'Error: No se pudo cargar los detalles de la compra.';
                }
            } catch (error) {
                loadingMessage.style.display = 'none';
                errorAlert.style.display = 'block';
                errorAlert.textContent = 'Error de conexión. Intente de nuevo.';
                console.error('Error al obtener datos de la compra para detalles:', error);
            }
        } else {
            loadingMessage.style.display = 'none';
            errorAlert.style.display = 'block';
            errorAlert.textContent = 'ID de compra no encontrado.';
        }
    });
</script>