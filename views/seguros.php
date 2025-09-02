<?php
// Incluir el archivo de conexión a la base de datos
 
$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Consultar la tabla `seguros`
// Unir con la tabla `pacientes` para obtener el nombre del titular
$seguros = $pdo->query("
    SELECT 
        s.id,
        s.titular_id,
        CONCAT(p.nombre, ' ', p.apellidos) AS nombre_titular,
        s.monto_inicial,
        s.saldo_actual,
        s.fecha_deposito,
        s.metodo_pago
    FROM 
        seguros s
    JOIN 
        pacientes p ON s.titular_id = p.id
    ORDER BY 
        s.fecha_deposito DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid" id="content">

    <div class="row mb-3">
        <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-shield-lock me-2"></i>Gestión de Seguros</h3>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearSeguro">
                <i class="bi bi-plus-circle me-1"></i>Crear Seguro
            </button>
        </div>
        <div class="col-md-4">
            <input type="text" id="buscadorSeguro" class="form-control" placeholder="Buscar seguro por titular...">
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

    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table id="tablaSeguros" class="table table-hover table-bordered table-sm align-middle">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th>ID</th>
                        <th>Titular</th>
                        <th>Monto Inicial</th>
                        <th>Saldo Actual</th>
                        <th>Fecha Depósito</th>
                        <th>Método de Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seguros as $seguro): ?>
                        <tr>
                            <td><?= $seguro['id'] ?></td>
                            <td><?= htmlspecialchars($seguro['nombre_titular']) ?></td>
                            <td>XAF <?= number_format($seguro['monto_inicial'], 2) ?></td>
                            <td>XAF <?= number_format($seguro['saldo_actual'], 2) ?></td>
                            <td><?= date('d/m/Y', strtotime($seguro['fecha_deposito'])) ?></td>
                            <td><?= htmlspecialchars($seguro['metodo_pago']) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-info btn-beneficiarios"
                                    data-id="<?= $seguro['id'] ?>"
                                    data-titular="<?= htmlspecialchars($seguro['nombre_titular']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalBeneficiarios"
                                    title="Ver Beneficiarios">
                                    <i class="bi bi-people"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary btn-editar-seguro"
                                    data-id="<?= $seguro['id'] ?>"
                                    data-titular-id="<?= $seguro['titular_id'] ?>"
                                    data-monto-inicial="<?= $seguro['monto_inicial'] ?>"
                                    data-saldo-actual="<?= $seguro['saldo_actual'] ?>"
                                    data-metodo-pago="<?= htmlspecialchars($seguro['metodo_pago']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarSeguro"
                                    title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-dark btn-detalle-seguro"
                                    data-id="<?= $seguro['id'] ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDetalleSeguro"
                                    title="Ver Detalles">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <a href="api/eliminar_seguro.php?id=<?= $seguro['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este seguro y sus beneficiarios? Esta acción es irreversible.')" title="Eliminar">
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

<!-- Modal para Crear Seguro -->
<div class="modal fade" id="modalCrearSeguro" tabindex="-1">
    <div class="modal-dialog">
        <form action="api/guardar_seguro.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nuevo Seguro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                <div class="col-md-12">
                    <label for="titular_id">Titular del Seguro (Paciente)</label>
                    <input type="text" id="crear-titular-search" class="form-control" placeholder="Buscar paciente...">
                    <input type="hidden" name="titular_id" id="crear-titular-id">
                    <div id="crear-titular-results" class="list-group mt-2"></div>
                </div>
                <div class="col-md-6">
                    <label for="monto_inicial">Monto Inicial (XAF)</label>
                    <input type="number" name="monto_inicial" step="0.01" min="0.01" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="metodo_pago">Método de Pago</label>
                    <select name="metodo_pago" class="form-control" required>
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TARJETA">Tarjeta</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="OTRO">Otro</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Editar Seguro -->
<div class="modal fade" id="modalEditarSeguro" tabindex="-1">
    <div class="modal-dialog">
        <form action="api/actualizar_seguro.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Seguro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="edit-seguro-id">
                <div class="col-md-12">
                    <label for="titular_id">Titular del Seguro (Paciente)</label>
                    <input type="text" id="edit-titular-search" class="form-control" placeholder="Buscar paciente..." disabled>
                </div>
                <div class="col-md-6">
                    <label for="monto_inicial">Monto Inicial (XAF)</label>
                    <input type="number" name="monto_inicial" id="edit-monto-inicial" step="0.01" min="0.01" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="saldo_actual">Saldo Actual (XAF)</label>
                    <input type="number" name="saldo_actual" id="edit-saldo-actual" step="0.01" class="form-control" readonly>
                </div>
                <div class="col-md-12">
                    <label for="metodo_pago">Método de Pago</label>
                    <select name="metodo_pago" id="edit-metodo-pago" class="form-control" required>
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TARJETA">Tarjeta</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="OTRO">Otro</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Gestionar Beneficiarios -->
<div class="modal fade" id="modalBeneficiarios" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-people me-2"></i>Beneficiarios del Seguro: <span id="beneficiario-titular-nombre"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <form id="form-agregar-beneficiario" action="api/agregar_beneficiario.php" method="POST">
                            <input type="hidden" name="seguro_id" id="beneficiario-seguro-id">
                            <label>Agregar Beneficiario (Paciente)</label>
                            <div class="input-group">
                                <input type="text" id="agregar-beneficiario-search" class="form-control" placeholder="Buscar paciente...">
                                <input type="hidden" name="paciente_id" id="agregar-beneficiario-id">
                                <button type="submit" class="btn btn-primary" id="btn-agregar-beneficiario" disabled><i class="bi bi-plus"></i> Agregar</button>
                            </div>
                            <div id="agregar-beneficiario-results" class="list-group mt-2"></div>
                        </form>
                    </div>
                </div>
                <hr>
                <h6>Beneficiarios Existentes</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-beneficiarios-body">
                            <!-- Los beneficiarios se cargarán aquí con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Nuevo Modal para Ver Detalles del Seguro -->
<div class="modal fade" id="modalDetalleSeguro" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-file-text me-2"></i>Detalle de Seguro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="detalle-seguro-body">
                <!-- El contenido se cargará aquí con JS -->
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-imprimir-detalle"><i class="bi bi-printer me-1"></i>Imprimir</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para manejar el buscador de pacientes y los datos de los modales
    document.addEventListener('DOMContentLoaded', function () {
        // Lógica para el modal de Crear Seguro
        const crearTitularSearch = document.getElementById('crear-titular-search');
        const crearTitularId = document.getElementById('crear-titular-id');
        const crearTitularResults = document.getElementById('crear-titular-results');
        
        crearTitularSearch.addEventListener('input', function() {
            const query = this.value;
            if (query.length > 2) {
                fetch(`api/buscar_paciente.php?q=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        crearTitularResults.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(paciente => {
                                const item = document.createElement('a');
                                item.classList.add('list-group-item', 'list-group-item-action');
                                item.href = '#';
                                item.textContent = `${paciente.nombre} ${paciente.apellidos} (DIP: ${paciente.dip})`;
                                item.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    crearTitularSearch.value = `${paciente.nombre} ${paciente.apellidos}`;
                                    crearTitularId.value = paciente.id;
                                    crearTitularResults.innerHTML = '';
                                });
                                crearTitularResults.appendChild(item);
                            });
                        } else {
                            crearTitularResults.innerHTML = '<div class="list-group-item">No se encontraron pacientes.</div>';
                        }
                    });
            } else {
                crearTitularResults.innerHTML = '';
            }
        });

        // Lógica para el modal de Editar Seguro
        const botonesEditarSeguro = document.querySelectorAll('.btn-editar-seguro');
        botonesEditarSeguro.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const montoInicial = this.getAttribute('data-monto-inicial');
                const saldoActual = this.getAttribute('data-saldo-actual');
                const metodoPago = this.getAttribute('data-metodo-pago');
                
                document.getElementById('edit-seguro-id').value = id;
                document.getElementById('edit-monto-inicial').value = montoInicial;
                document.getElementById('edit-saldo-actual').value = saldoActual;
                document.getElementById('edit-metodo-pago').value = metodoPago;
                
                // No se edita el titular, solo se muestra el nombre
                const titularNombre = this.closest('tr').querySelector('td:nth-child(2)').textContent;
                document.getElementById('edit-titular-search').value = titularNombre;
            });
        });
        
        // Lógica para el modal de Beneficiarios
        const botonesBeneficiarios = document.querySelectorAll('.btn-beneficiarios');
        botonesBeneficiarios.forEach(btn => {
            btn.addEventListener('click', function () {
                const seguroId = this.getAttribute('data-id');
                const titularNombre = this.getAttribute('data-titular');
                
                document.getElementById('beneficiario-seguro-id').value = seguroId;
                document.getElementById('beneficiario-titular-nombre').textContent = titularNombre;
                
                cargarBeneficiarios(seguroId);
            });
        });
        
        // Función para cargar los beneficiarios de un seguro
        function cargarBeneficiarios(seguroId) {
            fetch(`api/listar_beneficiarios.php?seguro_id=${seguroId}`)
                .then(response => response.json())
                .then(data => {
                    const tablaBeneficiariosBody = document.getElementById('tabla-beneficiarios-body');
                    tablaBeneficiariosBody.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(beneficiario => {
                            const fila = `
                                <tr>
                                    <td>${beneficiario.id}</td>
                                    <td>${beneficiario.nombre_paciente}</td>
                                    <td>
                                        <a href="api/eliminar_beneficiario.php?id=${beneficiario.id}" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar este beneficiario?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                            tablaBeneficiariosBody.insertAdjacentHTML('beforeend', fila);
                        });
                    } else {
                        tablaBeneficiariosBody.innerHTML = '<tr><td colspan="3">No hay beneficiarios registrados.</td></tr>';
                    }
                });
        }
        
        // Lógica para el buscador de beneficiarios en el modal
        const agregarBeneficiarioSearch = document.getElementById('agregar-beneficiario-search');
        const agregarBeneficiarioId = document.getElementById('agregar-beneficiario-id');
        const agregarBeneficiarioResults = document.getElementById('agregar-beneficiario-results');
        const btnAgregarBeneficiario = document.getElementById('btn-agregar-beneficiario');
        
        agregarBeneficiarioSearch.addEventListener('input', function() {
            const query = this.value;
            if (query.length > 2) {
                fetch(`api/buscar_paciente.php?q=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        agregarBeneficiarioResults.innerHTML = '';
                        btnAgregarBeneficiario.disabled = true; // Deshabilitar el botón por defecto
                        if (data.length > 0) {
                            data.forEach(paciente => {
                                const item = document.createElement('a');
                                item.classList.add('list-group-item', 'list-group-item-action');
                                item.href = '#';
                                item.textContent = `${paciente.nombre} ${paciente.apellidos} (DIP: ${paciente.dip})`;
                                item.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    agregarBeneficiarioSearch.value = `${paciente.nombre} ${paciente.apellidos}`;
                                    agregarBeneficiarioId.value = paciente.id;
                                    agregarBeneficiarioResults.innerHTML = '';
                                    btnAgregarBeneficiario.disabled = false; // Habilitar el botón al seleccionar
                                });
                                agregarBeneficiarioResults.appendChild(item);
                            });
                        } else {
                            agregarBeneficiarioResults.innerHTML = '<div class="list-group-item">No se encontraron pacientes.</div>';
                        }
                    });
            } else {
                agregarBeneficiarioResults.innerHTML = '';
                btnAgregarBeneficiario.disabled = true;
            }
        });

        // Lógica para el nuevo modal de Detalles del Seguro
        const botonesDetalleSeguro = document.querySelectorAll('.btn-detalle-seguro');
        const detalleSeguroBody = document.getElementById('detalle-seguro-body');
        const btnImprimir = document.getElementById('btn-imprimir-detalle');

        botonesDetalleSeguro.forEach(btn => {
            btn.addEventListener('click', function () {
                const seguroId = this.getAttribute('data-id');
                detalleSeguroBody.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
                fetch(`api/obtener_detalle_seguro.php?id=${seguroId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            detalleSeguroBody.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                            return;
                        }
                        
                        // Generar el contenido HTML para la factura
                        let htmlContent = `
                            <style>
                                @media print {
                                    body * {
                                        visibility: hidden;
                                    }
                                    .modal-content, .modal-content * {
                                        visibility: visible;
                                    }
                                    .modal-header, .modal-footer {
                                        display: none !important;
                                    }
                                }
                                .factura-header, .factura-section {
                                    border-bottom: 1px solid #dee2e6;
                                    padding-bottom: 1rem;
                                    margin-bottom: 1rem;
                                }
                                .factura-section h5 {
                                    border-left: 4px solid #000;
                                    padding-left: 10px;
                                    font-weight: bold;
                                    color: #333;
                                }
                                .factura-table th {
                                    background-color: #f8f9fa;
                                }
                                .saldo-final {
                                    font-size: 1.5rem;
                                    font-weight: bold;
                                }
                                .deuda {
                                    color: red;
                                }
                                .credito {
                                    color: green;
                                }
                            </style>
                            <div class="factura-container">
                                <div class="factura-header text-center">
                                    <h2 class="fw-bold">Detalle de Uso de Seguro</h2>
                                    <p>Fecha de Emisión: ${new Date().toLocaleDateString('es-ES')}</p>
                                </div>
                                <div class="factura-section">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Información del Seguro</h5>
                                            <p><strong>ID Seguro:</strong> ${data.seguro.id}</p>
                                            <p><strong>Monto Inicial:</strong> XAF ${data.seguro.monto_inicial}</p>
                                            <p><strong>Saldo Actual:</strong> <span class="saldo-final">XAF ${data.seguro.saldo_actual}</span></p>
                                            <p><strong>Método de Pago:</strong> ${data.seguro.metodo_pago}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h5>Titular y Beneficiarios</h5>
                                            <p><strong>Titular:</strong> ${data.seguro.nombre_titular} (ID: ${data.seguro.titular_id})</p>
                                            <p><strong>Beneficiarios:</strong></p>
                                            <ul>
                                                ${data.beneficiarios.length > 0 ? data.beneficiarios.map(b => `<li>${b.nombre_paciente} (ID: ${b.paciente_id})</li>`).join('') : '<li>Ninguno</li>'}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="factura-section">
                                    <h5>Movimientos del Seguro</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered factura-table">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Tipo</th>
                                                    <th>Monto</th>
                                                    <th>Paciente</th>
                                                    <th>Descripción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${data.movimientos.length > 0 ? data.movimientos.map(m => `
                                                    <tr>
                                                        <td>${new Date(m.fecha).toLocaleDateString('es-ES')}</td>
                                                        <td><span class="${m.tipo === 'DEBITO' ? 'deuda' : 'credito'}">${m.tipo}</span></td>
                                                        <td>XAF ${m.monto}</td>
                                                        <td>${m.nombre_paciente}</td>
                                                        <td>${m.descripcion}</td>
                                                    </tr>
                                                `).join('') : '<tr><td colspan="5" class="text-center">No hay movimientos registrados.</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="factura-section">
                                    <h5>Detalle de Préstamos (Deuda con la entidad)</h5>
                                    <p class="mb-2">Según la política del seguro, cuando el saldo llega a XAF 0, se otorga un préstamo automático del 50% del monto inicial para continuar la atención.</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered factura-table">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Monto Préstamo</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${data.prestamos.length > 0 ? data.prestamos.map(p => `
                                                    <tr>
                                                        <td>${new Date(p.fecha).toLocaleDateString('es-ES')}</td>
                                                        <td>XAF ${p.total}</td>
                                                        <td><span class="${p.estado === 'PAGADO' ? 'text-success' : 'text-danger'}">${p.estado}</span></td>
                                                    </tr>
                                                `).join('') : '<tr><td colspan="3" class="text-center">No hay préstamos registrados.</td></tr>'}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `;
                        detalleSeguroBody.innerHTML = htmlContent;
                    });
            });
        });
        btnImprimir.addEventListener('click', function() {
            const printContent = document.getElementById('detalle-seguro-body').innerHTML;
            const originalContent = document.body.innerHTML;            
            const printWindow = window.open('', '', 'height=600,width=800');
            printWindow.document.write('<html><head><title>Detalle de Seguro</title>');
            printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">');
            printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        });
    });
</script>

<script>
    setTimeout(() => {
        const mensaje = document.getElementById('mensaje');
        if (mensaje) {
            mensaje.style.transition = 'opacity 1s ease';
            mensaje.style.opacity = '0';
            setTimeout(() => mensaje.remove(), 1000);
        }
    }, 10000); // 10 segundos
</script>