<?php
// Asegurar que la sesión esté iniciada (esto ya lo tienes)
// session_start();
// if (!isset($_SESSION['usuario'])) {
//     header("Location: login.php"); // Redirige si no hay sesión
//     exit();
// }

// Incluir tu archivo de conexión a la base de datos (si 'pdo' viene de ahí)
// require_once 'ruta/a/tu/conexion.php'; 

// Las consultas PHP directas ya no serán necesarias aquí, la tabla se llenará con AJAX
?>

<div id="content" class="container-fluid py-4">
    <div class="row mb-3 align-items-center">
        <div class="col-md-6">
            <h3 class="mb-0"><i class="bi bi-people-fill me-2"></i>Listado de Personal</h3>
        </div>
        <div class="col-md-4 ">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="buscador" class="form-control" placeholder="Buscar personal...">
            </div>
        </div>
        <div class="col-md-2 text-end">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevo">
                <i class="bi bi-person-plus-fill me-1"></i> Nuevo Personal
            </button>
        </div>
    </div>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<div id="mensaje" class="alert alert-danger alert-dismissible fade show" role="alert">' . $_SESSION['error'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div id="mensaje" class="alert alert-success alert-dismissible fade show" role="alert">' . $_SESSION['success'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['success']);
    }
    ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaPersonal" class="table table-hover table-bordered align-middle table-sm">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th><i class="bi bi-hash text-muted"></i> ID</th>
                            <th><i class="bi bi-person-badge-fill text-muted"></i> Empleado</th>
                            <th><i class="bi bi-envelope-at-fill text-muted"></i> Correo</th>
                            <th><i class="bi bi-telephone-fill text-muted"></i> Teléfono</th>
                            <th><i class="bi bi-award-fill text-muted"></i> Especialidad</th>
                            <th><i class="bi bi-person-circle text-muted"></i> Usuario</th>
                            <th><i class="bi bi-calendar-check-fill text-muted"></i> Registro</th>
                            <th><i class="bi bi-tools text-muted"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="personalTableBody">
                        <tr>
                            <td colspan="8" class="text-center text-muted">Cargando personal...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Mostrando <span id="currentRecords">0</span> de <span id="totalRecords">0</span> registros
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="paginationControls">
                        </ul>
                </nav>
                <div class="d-flex align-items-center">
                    <label for="recordsPerPage" class="me-2">Registros por página:</label>
                    <select class="form-select form-select-sm w-auto" id="recordsPerPage">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL NUEVO PERSONAL -->
<div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <form action="api/guardar_personal.php" method="POST" id="formNuevoPersonal">
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title fs-4" id="modalNuevoLabel">
            <i class="bi bi-person-plus-fill text-primary me-2"></i>Nuevo Personal
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body pt-3">
          <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" class="form-control form-control-lg" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Apellidos</label>
              <input type="text" name="apellidos" class="form-control form-control-lg" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Correo</label>
              <input type="email" name="correo" class="form-control form-control-lg">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" class="form-control form-control-lg">
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha de Nacimiento</label>
              <input type="date" name="fecha_nacimiento" class="form-control form-control-lg">
            </div>
            <div class="col-md-6">
              <label class="form-label">Dirección</label>
              <input type="text" name="direccion" class="form-control form-control-lg">
            </div>
            <div class="col-12">
              <label class="form-label">Especialidad</label>
              <input type="text" name="especialidad" class="form-control form-control-lg">
            </div>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-save2-fill me-1"></i>Guardar
          </button>
          <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i>Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL EDITAR PERSONAL -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="modalEditarLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">
      <form action="api/actualizar_personal.php" method="POST" id="formEditarPersonal">
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title fs-4" id="modalEditarLabel">
            <i class="bi bi-pencil-square text-warning me-2"></i>Editar Personal
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body pt-3">
          <input type="hidden" name="id" id="editar_id">
          <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input type="text" name="nombre" id="editar_nombre" class="form-control form-control-lg" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Apellidos</label>
              <input type="text" name="apellidos" id="editar_apellidos" class="form-control form-control-lg" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Correo</label>
              <input type="email" name="correo" id="editar_correo" class="form-control form-control-lg">
            </div>
            <div class="col-md-6">
              <label class="form-label">Teléfono</label>
              <input type="text" name="telefono" id="editar_telefono" class="form-control form-control-lg">
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha de Nacimiento</label>
              <input type="date" name="fecha_nacimiento" id="editar_fecha_nacimiento" class="form-control form-control-lg">
            </div>
            <div class="col-md-6">
              <label class="form-label">Dirección</label>
              <input type="text" name="direccion" id="editar_direccion" class="form-control form-control-lg">
            </div>
            <div class="col-12">
              <label class="form-label">Especialidad</label>
              <input type="text" name="especialidad" id="editar_especialidad" class="form-control form-control-lg">
            </div>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="submit" class="btn btn-warning btn-lg text-white">
            <i class="bi bi-pencil-fill me-1"></i>Actualizar
          </button>
          <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i>Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        let currentPage = 1;
        let recordsPerPage = document.getElementById('recordsPerPage').value;
        let searchQuery = '';

        const personalTableBody = document.getElementById('personalTableBody');
        const paginationControls = document.getElementById('paginationControls');
        const buscadorInput = document.getElementById('buscador');
        const recordsPerPageSelect = document.getElementById('recordsPerPage');
        const formNuevoPersonal = document.getElementById('formNuevoPersonal');
        const formEditarPersonal = document.getElementById('formEditarPersonal');
        const modalNuevo = new bootstrap.Modal(document.getElementById('modalNuevo'));
        const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
        const contentContainer = document.querySelector('.container-fluid.py-4'); // Para mostrar las alertas

        // Función para mostrar alertas de éxito/error
        function showAlert(message, type) {
            const existingAlert = document.getElementById('mensaje');
            if (existingAlert) {
                existingAlert.remove(); // Elimina la alerta existente si la hay
            }

            const alertDiv = document.createElement('div');
            alertDiv.id = 'mensaje';
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            contentContainer.prepend(alertDiv);

            // Cierre automático de la alerta
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getInstance(alertDiv);
                if (bsAlert) {
                    bsAlert.close();
                } else {
                    alertDiv.remove(); // Fallback si no se obtuvo la instancia
                }
            }, 10000);
        }

        // Función para cargar los datos del personal
        async function loadPersonal(page, search, perPage) {
            personalTableBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted">
                <div class="spinner-border text-primary spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>Cargando personal...
            </td></tr>`;

            const url = `api/obtener_personal.php?page=${page}&search=${encodeURIComponent(search)}&per_page=${perPage}`;
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                personalTableBody.innerHTML = ''; // Limpiar el cuerpo de la tabla
                if (data.success && data.personal.length > 0) {
                    data.personal.forEach(p => {
                        const usuario = p.nombre_usuario ? p.nombre_usuario : '<span class="text-muted fst-italic">Sin asignar</span>';
                        const fechaRegistro = new Date(p.fecha_registro).toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                        const row = `
                            <tr>
                                <td>${parseInt(p.id)}</td>
                                <td>${escapeHTML(p.nombre)} ${escapeHTML(p.apellidos)}</td>
                                <td>${escapeHTML(p.correo)}</td>
                                <td>${escapeHTML(p.telefono)}</td>
                                <td>${escapeHTML(p.especialidad)}</td>
                                <td>${usuario}</td>
                                <td>${fechaRegistro}</td>
                                <td class="text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary me-1 editar-btn"
                                        data-id="${p.id}"
                                        data-nombre="${escapeHTML(p.nombre)}"
                                        data-apellidos="${escapeHTML(p.apellidos)}"
                                        data-correo="${escapeHTML(p.correo)}"
                                        data-telefono="${escapeHTML(p.telefono)}"
                                        data-fecha_nacimiento="${escapeHTML(p.fecha_nacimiento)}"
                                        data-direccion="${escapeHTML(p.direccion)}"
                                        data-especialidad="${escapeHTML(p.especialidad)}"
                                        title="Editar" data-bs-toggle="modal" data-bs-target="#modalEditar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="api/eliminar_personal.php?id=${p.id}"
                                        class="btn btn-sm btn-outline-danger delete-btn" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                        personalTableBody.insertAdjacentHTML('beforeend', row);
                    });
                } else {
                    personalTableBody.innerHTML = '<tr><td colspan="8" class="text-center">No se encontraron registros.</td></tr>';
                }
                updatePaginationControls(data.totalPages, data.currentPage, data.totalRecords, data.personal.length, perPage);

            } catch (error) {
                console.error("Error al cargar personal:", error);
                personalTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Error al cargar los datos. Inténtelo de nuevo.</td></tr>';
            }
        }

        // Función para escapar HTML, previene XSS al renderizar datos
        function escapeHTML(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        // Función para actualizar los controles de paginación
        function updatePaginationControls(totalPages, currentPage, totalRecords, recordsDisplayed, perPage) {
            let paginationHtml = '';
            const maxPagesToShow = 5; // Número máximo de botones de página a mostrar

            // Botón "Anterior"
            paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${currentPage - 1}">Anterior</a>
                               </li>`;

            // Botones de número de página
            let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
            let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

            if (endPage - startPage + 1 < maxPagesToShow) {
                startPage = Math.max(1, endPage - maxPagesToShow + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                                   </li>`;
            }

            // Botón "Siguiente"
            paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${currentPage + 1}">Siguiente</a>
                               </li>`;

            paginationControls.innerHTML = paginationHtml;

            // Actualizar el texto "Mostrando X de Y registros"
            const startRecord = (currentPage - 1) * perPage + 1;
            const endRecord = startRecord + recordsDisplayed - 1;
            document.getElementById('currentRecords').textContent = `${totalRecords > 0 ? startRecord : 0}-${endRecord}`;
            document.getElementById('totalRecords').textContent = totalRecords;
        }

        // --- Event Listeners ---

        // Evento click para botones de paginación (delegación de eventos)
        paginationControls.addEventListener('click', (e) => {
            e.preventDefault();
            const target = e.target.closest('.page-link');
            if (target && !target.parentElement.classList.contains('disabled')) {
                currentPage = parseInt(target.dataset.page);
                loadPersonal(currentPage, searchQuery, recordsPerPage);
            }
        });

        // Evento para el buscador (debounce para rendimiento)
        let searchTimeout;
        buscadorInput.addEventListener('keyup', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchQuery = buscadorInput.value;
                currentPage = 1; // Resetear a la primera página al buscar
                loadPersonal(currentPage, searchQuery, recordsPerPage);
            }, 300); // Esperar 300ms después de la última pulsación
        });

        // Evento para cambiar registros por página
        recordsPerPageSelect.addEventListener('change', () => {
            recordsPerPage = recordsPerPageSelect.value;
            currentPage = 1; // Resetear a la primera página al cambiar el límite
            loadPersonal(currentPage, searchQuery, recordsPerPage);
        });

        // Lógica para llenar el modal de edición
        // Usamos delegación de eventos ya que los botones se añaden dinámicamente
        personalTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('.editar-btn');
            if (btn) {
                document.getElementById('editar_id').value = btn.dataset.id;
                document.getElementById('editar_nombre').value = btn.dataset.nombre;
                document.getElementById('editar_apellidos').value = btn.dataset.apellidos;
                document.getElementById('editar_correo').value = btn.dataset.correo;
                document.getElementById('editar_telefono').value = btn.dataset.telefono;
                document.getElementById('editar_fecha_nacimiento').value = btn.dataset.fecha_nacimiento;
                document.getElementById('editar_direccion').value = btn.dataset.direccion;
                document.getElementById('editar_especialidad').value = btn.dataset.especialidad;
            }
        });

        

        // Para el botón de eliminar (delegación de eventos)
        personalTableBody.addEventListener('click', async (e) => {
            const btn = e.target.closest('.delete-btn');
            if (btn) {
                e.preventDefault(); // Evita la navegación directa
                if (confirm('¿Deseas eliminar este registro?')) {
                    const deleteUrl = btn.href;
                    try {
                        const response = await fetch(deleteUrl, { method: 'GET' }); // O 'POST' si eliminar_personal.php espera POST
                        const data = await response.json();

                        if (data.success) {
                            showAlert(data.message, 'success');
                            loadPersonal(currentPage, searchQuery, recordsPerPage); // Recargar la tabla
                        } else {
                            showAlert('Error al eliminar: ' + data.message, 'danger');
                        }
                    } catch (error) {
                        console.error("Error al eliminar personal:", error);
                        showAlert('Error al procesar la eliminación. Inténtelo de nuevo.', 'danger');
                    }
                }
            }
        });

        // Cargar personal al inicio
        loadPersonal(currentPage, searchQuery, recordsPerPage);

        // Ocultar mensajes de sesión existentes al cargar
        const initialMessage = document.getElementById('mensaje');
        if (initialMessage) {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getInstance(initialMessage);
                if (bsAlert) {
                    bsAlert.close();
                } else {
                    initialMessage.remove();
                }
            }, 10000);
        }
    });
</script>