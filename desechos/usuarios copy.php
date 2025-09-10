<?php
// Asegurar que la sesión esté iniciada (esto ya lo tienes)
// session_start();
// if (!isset($_SESSION['usuario'])) {
//     header("Location: login.php"); // Redirige si no hay sesión
//     exit();
// }

// Incluir tu archivo de conexión a la base de datos (si 'pdo' viene de ahí)
// require_once 'ruta/a/tu/conexion.php'; 

// Para selects del modal (se seguirán obteniendo con PHP, ya que son estáticos para los modales)
// Si estos datos pueden cambiar frecuentemente, también podrías cargarlos vía AJAX al abrir el modal.
// Para este ejemplo, los mantendremos cargados en PHP.
$empleados = $pdo->query("SELECT id, nombre, especialidad FROM personal")->fetchAll(PDO::FETCH_ASSOC);
$roles = $pdo->query("SELECT id, nombre FROM roles")->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="content" class="container-fluid py-4">
    <div class="thead sticky-top bg-white pb-2" style="top: 60px; z-index: 1040; border-bottom: 1px solid #dee2e6;">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="mb-3 mb-md-0">
                <i class="bi bi-person-gear me-2 text-primary"></i> Gestión de Usuarios
            </h3>
            <div class="d-flex gap-2 align-items-center">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="buscadorUsuarios" class="form-control" placeholder="Buscar usuario...">
                </div>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistro">
                    <i class="bi bi-person-plus me-1"></i> Nuevo Usuario
                </button>
            </div>
        </div>
    </div>

    <?php
    // Mostrar mensajes de sesión (si los hay)
    if (isset($_SESSION['error'])) {
        echo '<div id="mensaje" class="alert alert-danger alert-dismissible fade show mt-3" role="alert">' . $_SESSION['error'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div id="mensaje" class="alert alert-success alert-dismissible fade show mt-3" role="alert">' . $_SESSION['success'] . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION['success']);
    }
    ?>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaUsuarios" class="table table-hover table-bordered align-middle table-sm">
                    <thead class="table-light text-nowrap">
                        <tr>
                            <th><i class="bi bi-hash me-1 text-muted"></i>ID</th>
                            <th><i class="bi bi-person-badge me-1 text-muted"></i>Empleado</th>
                            <th><i class="bi bi-person me-1 text-muted"></i>Usuario</th>
                            <th><i class="bi bi-shield-lock me-1 text-muted"></i>Rol</th> 
                            <th><i class="bi bi-calendar-check me-1 text-muted"></i>Ingreso</th>
                            <th><i class="bi bi-tools me-1 text-muted"></i>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usuariosTableBody">
                        <tr>
                            <td colspan="7" class="text-center text-muted">Cargando usuarios...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Mostrando <span id="currentUsersRecords">0</span> de <span id="totalUsersRecords">0</span> registros
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0" id="paginationUsersControls">
                        </ul>
                </nav>
                <div class="d-flex align-items-center">
                    <label for="usersRecordsPerPage" class="me-2">Registros por página:</label>
                    <select class="form-select form-select-sm w-auto" id="usersRecordsPerPage">
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

<div class="modal fade" id="modalRegistro" tabindex="-1" aria-labelledby="registroUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content needs-validation" novalidate action="api/guardar_usuario.php" method="POST" id="formNuevoUsuario">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="registroUsuarioLabel"><i class="bi bi-person-plus-fill me-2"></i>Registrar Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person-vcard me-1"></i>Empleado</label>
                    <select class="form-select" name="id_personal" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($empleados as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?> (<?= htmlspecialchars($e['especialidad']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Por favor, seleccione un empleado.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person me-1"></i>Nombre de usuario</label>
                    <input type="text" name="nombre_usuario" class="form-control" required maxlength="25">
                    <div class="invalid-feedback">Ingrese un nombre de usuario.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-lock-fill me-1"></i>Contraseña</label>
                    <input type="password" name="contrasena" class="form-control" required minlength="6">
                    <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-shield-lock-fill me-1"></i>Rol</label>
                    <select name="id_rol" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Por favor, seleccione un rol.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-save me-1"></i>Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-labelledby="editarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content needs-validation" novalidate action="api/editar_usuario.php" method="POST" id="formEditarUsuario">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editarUsuarioLabel"><i class="bi bi-pencil-square me-2"></i>Editar Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit-id">

                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person me-1"></i>Nombre de usuario</label>
                    <input type="text" name="nombre_usuario" id="edit-nombre-usuario" class="form-control" required>
                    <div class="invalid-feedback">Ingrese un nombre de usuario.</div>
                </div> 
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-shield-lock me-1"></i>Rol</label>
                    <select name="id_rol" id="edit-rol" class="form-select" required>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Seleccione un rol.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-arrow-repeat me-1"></i>Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let currentPage = 1;
        let recordsPerPage = document.getElementById('usersRecordsPerPage').value;
        let searchQuery = '';

        const usuariosTableBody = document.getElementById('usuariosTableBody');
        const paginationUsersControls = document.getElementById('paginationUsersControls');
        const buscadorUsuariosInput = document.getElementById('buscadorUsuarios');
        const usersRecordsPerPageSelect = document.getElementById('usersRecordsPerPage');
        const formNuevoUsuario = document.getElementById('formNuevoUsuario');
        const formEditarUsuario = document.getElementById('formEditarUsuario');
        const modalRegistro = new bootstrap.Modal(document.getElementById('modalRegistro'));
        const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
        const contentContainer = document.querySelector('.container-fluid.py-4');

        // Función para mostrar alertas de éxito/error
        function showAlert(message, type) {
            const existingAlert = document.getElementById('mensaje');
            if (existingAlert) {
                existingAlert.remove();
            }

            const alertDiv = document.createElement('div');
            alertDiv.id = 'mensaje';
            alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            contentContainer.prepend(alertDiv);

            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getInstance(alertDiv);
                if (bsAlert) {
                    bsAlert.close();
                } else {
                    alertDiv.remove();
                }
            }, 10000);
        }

        // Función para escapar HTML
        function escapeHTML(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        // Función para cargar los datos de usuarios
        async function loadUsuarios(page, search, perPage) {
            usuariosTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">
                <div class="spinner-border text-primary spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>Cargando usuarios...
            </td></tr>`;

            const url = `api/obtener_usuarios.php?page=${page}&search=${encodeURIComponent(search)}&per_page=${perPage}`;
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();

                usuariosTableBody.innerHTML = '';
                if (data.success && data.usuarios.length > 0) {
                    data.usuarios.forEach(u => {
                       
                        const fechaIngreso = new Date(u.ingreso).toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                        const row = `
                            <tr>
                                <td>${parseInt(u.id)}</td>
                                <td>${escapeHTML(u.personal)}</td>
                                <td>${escapeHTML(u.usuario)}</td>
                                <td>${escapeHTML(u.rol)}</td>
                              
                                <td>${fechaIngreso}</td>
                                <td class="text-nowrap">
                                    <button class="btn btn-sm btn-outline-warning me-1 edit-user-btn"
                                        data-bs-toggle="modal" data-bs-target="#modalEditar"
                                        data-id="${u.id}" 
                                        data-nombre-usuario="${escapeHTML(u.usuario)}"
                                      
                                        data-rol-id="${u.id_rol}"
                                        title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <a href="eliminar_usuario.php?id=${u.id}" class="btn btn-sm btn-outline-danger delete-user-btn" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                        usuariosTableBody.insertAdjacentHTML('beforeend', row);
                    });
                } else {
                    usuariosTableBody.innerHTML = '<tr><td colspan="7" class="text-center">No se encontraron usuarios.</td></tr>';
                }
                updatePaginationControls(data.totalPages, data.currentPage, data.totalRecords, data.usuarios.length, perPage);

            } catch (error) {
                usuarios.error("Error al cargar usuarios:", error);
                usuariosTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error al cargar los datos. Inténtelo de nuevo.</td></tr>';
            }
        }

        // Función para actualizar los controles de paginación
        function updatePaginationControls(totalPages, currentPage, totalRecords, recordsDisplayed, perPage) {
            let paginationHtml = '';
            const maxPagesToShow = 5;

            paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${currentPage - 1}">Anterior</a>
                               </li>`;

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

            paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${currentPage + 1}">Siguiente</a>
                               </li>`;

            paginationUsersControls.innerHTML = paginationHtml;

            const startRecord = (currentPage - 1) * perPage + 1;
            const endRecord = startRecord + recordsDisplayed - 1;
            document.getElementById('currentUsersRecords').textContent = `${totalRecords > 0 ? startRecord : 0}-${endRecord}`;
            document.getElementById('totalUsersRecords').textContent = totalRecords;
        }

        // --- Event Listeners ---

        // Evento click para botones de paginación
        paginationUsersControls.addEventListener('click', (e) => {
            e.preventDefault();
            const target = e.target.closest('.page-link');
            if (target && !target.parentElement.classList.contains('disabled')) {
                currentPage = parseInt(target.dataset.page);
                loadUsuarios(currentPage, searchQuery, recordsPerPage);
            }
        });

        // Evento para el buscador (con debounce)
        let searchTimeout;
        buscadorUsuariosInput.addEventListener('keyup', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchQuery = buscadorUsuariosInput.value;
                currentPage = 1;
                loadUsuarios(currentPage, searchQuery, recordsPerPage);
            }, 300);
        });

        // Evento para cambiar registros por página
        usersRecordsPerPageSelect.addEventListener('change', () => {
            recordsPerPage = usersRecordsPerPageSelect.value;
            currentPage = 1;
            loadUsuarios(currentPage, searchQuery, recordsPerPage);
        });

        // Lógica para llenar el modal de edición (delegación de eventos)
        usuariosTableBody.addEventListener('click', (e) => {
            const btn = e.target.closest('.edit-user-btn');
            if (btn) {
                document.getElementById('edit-id').value = btn.dataset.id;
                document.getElementById('edit-nombre-usuario').value = btn.dataset.nombreUsuario; 
                document.getElementById('edit-rol').value = btn.dataset.rolId; // Asegúrate de que el data-attribute sea 'data-rol-id'
            }
        });

        // Para el botón de eliminar (delegación de eventos)
        usuariosTableBody.addEventListener('click', async (e) => {
            const btn = e.target.closest('.delete-user-btn');
            if (btn) {
                e.preventDefault();
                if (confirm('¿Deseas eliminar este usuario? Esta acción es irreversible.')) {
                    const deleteUrl = btn.href;
                    try {
                        const response = await fetch(deleteUrl, { method: 'GET' }); // Ajusta a POST si tu script lo espera
                        const data = await response.json();

                        if (response.ok && data.success) {
                            showAlert(data.message, 'success');
                            loadUsuarios(currentPage, searchQuery, recordsPerPage);
                        } else {
                            showAlert('Error al eliminar: ' + (data.message || 'Error desconocido al eliminar usuario.'), 'danger');
                        }
                    } catch (error) {
                        usuarios.error("Error al eliminar usuario:", error);
                        showAlert('Error de conexión o servidor al eliminar el usuario. Inténtelo de nuevo.', 'danger');
                    }
                }
            }
        });

        // Cargar usuarios al inicio
        loadUsuarios(currentPage, searchQuery, recordsPerPage);

        // Ocultar mensajes de sesión existentes al cargar (ej. si vienen de una redirección)
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

        // Validación de Bootstrap para formularios
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    });
</script>