<?php

// Nota: Asegúrate de tener tu archivo de conexión y la sesión iniciada.
// Por ejemplo:
// require_once('config/conexion.php');
// session_start();

// Consulta para obtener las analíticas, incluyendo el campo tipo_pago
$sql = "SELECT
    a.id,
    a.resultado,
    a.estado,
    a.codigo_paciente,
    a.pagado,
    a.tipo_pago,            -- <--- CAMBIO: Se agrega el tipo de pago
    tp.id AS id_tipo_prueba,
    tp.nombre AS tipo_prueba,
    tp.precio,
    p.id AS id_paciente,
    CONCAT(p.nombre,' ',p.apellidos) AS paciente,
    a.fecha_registro,
    DATE(a.fecha_registro) AS fecha_solo
FROM analiticas a
JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id
JOIN pacientes p ON a.id_paciente = p.id
ORDER BY a.fecha_registro DESC";

try {
    // Asegúrate de que $pdo es tu objeto de conexión PDO
    $stmt = $pdo->query($sql);
    $analiticas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
    $analiticas = [];
}

// Agrupar los registros por paciente y fecha
$grupos = [];
foreach ($analiticas as $a) {
    $clave = $a['paciente'] . '_' . $a['fecha_solo'];

    if (!isset($grupos[$clave])) {
        $grupos[$clave] = [
            'tipo' => $a['tipo_prueba'],
            'paciente' => $a['paciente'],
            'codigo' => $a['codigo_paciente'],
            'id_paciente' => $a['id_paciente'],
            'fecha' => $a['fecha_solo'],
            'registros' => [],
            'pagos' => [],
            'tipo_pago' => $a['tipo_pago'], // <--- CAMBIO: Se agrega el tipo de pago al grupo
        ];
    }

    $grupos[$clave]['registros'][] = $a;
    $grupos[$clave]['pagos'][] = $a['pagado'];
}
?>
<div class="container-fluid" id="content">

    <div class="row mb-3">
        <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-credit-card me-2"></i>Gestión de Pagos</h3>
        </div>
        <div class="col-md-4">
            <input type="text" id="buscadorPago" class="form-control" placeholder="Buscar pago...">
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table id="tablaAnaliticas" class="table table-hover table-bordered table-sm align-middle">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Paciente</th>
                        <th>Código</th>
                        <th>Pruebas</th>
                        <th>Resultados</th>
                        <th>Fecha</th>
                        <th>Pagos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grupos as $grupo): ?>
                        <tr>
                            <td><?= htmlspecialchars($grupo['registros'][0]['id']) ?></td>
                            <td><?= htmlspecialchars($grupo['paciente']) ?></td>
                            <td><?= htmlspecialchars($grupo['codigo']) ?></td>
                            <td>
                                <ul class="mb-0">
                                    <?php foreach ($grupo['registros'] as $r): ?>
                                        <li><?= htmlspecialchars($r['tipo_prueba']) ?></li>
                                    <?php endforeach ?>
                                </ul>
                            </td>
                            <td>
                                <?php
                                $todosConResultado = true;
                                foreach ($grupo['registros'] as $r) {
                                    if (empty($r['resultado'])) {
                                        $todosConResultado = false;
                                        break;
                                    }
                                }
                                ?>
                                <?php if ($todosConResultado): ?>
                                    <span class="badge bg-primary">Resultado</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Sin Resultado</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($grupo['fecha'])) ?></td>
                            <td>
                                <?php
                                $estado_pago = 'SIN PAGAR'; // Valor por defecto
                                // Buscar el estado de pago del primer registro en el grupo
                                if (isset($grupo['registros'][0]['tipo_pago'])) {
                                    $estado_pago = $grupo['registros'][0]['tipo_pago'];
                                }

                                switch ($estado_pago) {
                                    case 'EFECTIVO':
                                    case 'SEGURO':
                                        echo '<span class="badge bg-success">Pagado</span>';
                                        break;
                                    case 'ADEUDO':
                                        echo '<span class="badge bg-warning text-dark">Adeudo</span>';
                                        break;
                                    case 'SIN PAGAR':
                                        echo '<span class="badge bg-danger">Sin Pagar</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-secondary">Desconocido</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($estado_pago == 'SIN PAGAR' ): ?>
                                    <button class="btn btn-sm btn-outline-success btn-pagar" data-bs-toggle="modal"
                                            data-bs-target="#modalPagar"
                                            data-grupo='<?= json_encode(array_filter($grupo['registros'], fn($r) => $r['pagado'] == 0)) ?>'
                                            data-paciente="<?= htmlspecialchars($grupo['paciente']) ?>"
                                            data-fecha="<?= htmlspecialchars($grupo['fecha']) ?>"
                                            data-paciente-id="<?= htmlspecialchars($grupo['id_paciente']) ?>" title="Pagar pruebas">
                                        <i class="bi bi-cash-coin me-1"></i> Pagar
                                    </button>
                                <?php else: ?>
                                    <a href="fpdf/generar_factura.php?id=<?= $grupo['registros'][0]['id'] ?>&fecha=<?= $grupo['fecha'] ?>"
                                            target="_blank" class="btn btn-outline-secondary btn-sm" title="Imprimir Factura">
                                        <i class="bi bi-printer"></i> Imprimir Factura
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary btn-editar-pago mt-1" data-bs-toggle="modal"
                                            data-bs-target="#modalEditarPago"
                                            data-grupo='<?= json_encode($grupo['registros']) ?>'
                                            data-paciente="<?= htmlspecialchars($grupo['paciente']) ?>"
                                            data-fecha="<?= htmlspecialchars($grupo['fecha']) ?>"
                                            data-paciente-id="<?= htmlspecialchars($grupo['id_paciente']) ?>" title="Editar Pago">
                                        <i class="bi bi-pencil-square"></i> Editar
                                    </button>
                                <?php endif; ?>
                                <a href="fpdf/imprimir_pruebas.php?id=<?= $grupo['id_paciente'] ?>&fecha=<?= $grupo['fecha'] ?>"
                                        target="_blank" class="btn btn-outline-primary btn-sm mt-1" title="Imprimir Pruebas Médicas">
                                    <i class="bi bi-file-earmark-medical"></i> Ver Pruebas
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="modalPagar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="api/guardar_pago.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Pagar Pruebas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
                <input type="hidden" name="id_paciente" id="idPacientePago">

                <div class="mb-3">
                    <strong>Paciente:</strong> <span id="nombrePacientePago"></span><br>
                    <strong>Fecha:</strong> <span id="fechaPacientePago"></span>
                </div>

                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Seleccionar</th>
                            <th>Tipo de Prueba</th>
                            <th>Precio</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPruebasPago">
                    </tbody>
                </table>

                <div class="text-end mb-3">
                    <strong>Total a Pagar: </strong><span id="totalPago" class="fs-5">0 FCFA</span>
                </div>

                <div class="mb-3">
                    <label for="tipoPago" class="form-label">Tipo de Pago</label>
                    <select class="form-select" id="tipoPago" name="tipo_pago" required>
                    </select>
                </div>
                
                <div id="contenedorMontoAPagar" class="mb-3" style="display:none;">
                    <label for="montoPagar" class="form-label">Monto a Pagar</label>
                    <input type="number" class="form-control" id="montoPagar" name="monto_pagar" min="0">
                </div>

                <div id="contenedorMontoPendiente" class="mb-3" style="display:none;">
                    <strong>Monto Pendiente:</strong> <span id="montoPendiente" class="fs-5 text-danger">0 FCFA</span>
                </div>

                <div id="contenedorSeguro" class="mb-3" style="display:none;">
                    <label for="idSeguro" class="form-label">Seleccionar Seguro</label>
                    <select class="form-select" id="idSeguro" name="id_seguro">
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="bi bi-wallet2 me-1"></i> Confirmar Pago</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="modalEditarPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="api/actualizar_pago.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_usuario" value="<?= $_SESSION['usuario']['id'] ?? '' ?>">
                <input type="hidden" name="id_paciente" id="editIdPaciente">
                <input type="hidden" name="fecha" id="editFecha">

                <div class="mb-3">
                    <strong>Paciente:</strong> <span id="editNombrePaciente"></span><br>
                    <strong>Fecha:</strong> <span id="editFechaPaciente"></span>
                </div>

                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Prueba</th>
                            <th>Precio</th>
                            <th>Estado Actual</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPruebasEditar">
                    </tbody>
                </table>

                <div class="text-end mb-3">
                    <strong>Total del Grupo: </strong><span id="editTotalGrupo" class="fs-5">0 FCFA</span>
                </div>

                <div class="mb-3">
                    <label for="editTipoPago" class="form-label">Nuevo Tipo de Pago</label>
                    <select class="form-select" id="editTipoPago" name="tipo_pago" required>
                    </select>
                </div>

                <div id="editContenedorMontoAPagar" class="mb-3" style="display:none;">
                    <label for="editMontoPagar" class="form-label">Monto Pagado</label>
                    <input type="number" class="form-control" id="editMontoPagar" name="monto_pagar" min="0">
                </div>

                <div id="editContenedorMontoPendiente" class="mb-3" style="display:none;">
                    <strong>Monto Pendiente:</strong> <span id="editMontoPendiente" class="fs-5 text-danger">0 FCFA</span>
                </div>

                <div id="editContenedorSeguro" class="mb-3" style="display:none;">
                    <label for="editIdSeguro" class="form-label">Seleccionar Seguro</label>
                    <select class="form-select" id="editIdSeguro" name="id_seguro">
                    </select>
                </div>

                <div class="alert alert-info mt-3" role="alert">
                    <i class="bi bi-info-circle me-1"></i>
                    Al actualizar el pago, se aplicará el nuevo tipo de pago y monto a **todas las pruebas del grupo** que no estén pagadas.
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar Pago</button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="[https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js)"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Manejar clics en el botón de "Pagar"
        document.querySelectorAll('.btn-pagar').forEach(btn => {
            btn.addEventListener('click', async () => {
                const idPaciente = btn.dataset.pacienteId;
                const grupo = JSON.parse(btn.dataset.grupo);
                const paciente = btn.dataset.paciente;
                const fecha = btn.dataset.fecha;
                await handlePagarModalLogic(idPaciente, grupo, paciente, fecha);
            });
        });

        // Manejar clics en el botón de "Editar Pago"
        document.querySelectorAll('.btn-editar-pago').forEach(btn => {
            btn.addEventListener('click', async () => {
                const grupo = JSON.parse(btn.dataset.grupo);
                const paciente = btn.dataset.paciente;
                const fecha = btn.dataset.fecha;
                const idPaciente = btn.dataset.pacienteId;
                await handleEditModalLogic(idPaciente, grupo, paciente, fecha);
            });
        });

        // Lógica del modal de Pagar
        async function handlePagarModalLogic(idPaciente, grupo, paciente, fecha) {
            const tipoPagoSelect = document.getElementById('tipoPago');
            const contenedorSeguro = document.getElementById('contenedorSeguro');
            const contenedorMontoAPagar = document.getElementById('contenedorMontoAPagar');
            const contenedorMontoPendiente = document.getElementById('contenedorMontoPendiente');
            const montoPagarInput = document.getElementById('montoPagar');
            const montoPendienteSpan = document.getElementById('montoPendiente');
            const totalPagoSpan = document.getElementById('totalPago');
            
            tipoPagoSelect.innerHTML = '';
            
            // Opciones de tipo de pago
            const efectivoOption = document.createElement('option');
            efectivoOption.value = 'EFECTIVO';
            efectivoOption.textContent = '💰 Efectivo';
            tipoPagoSelect.appendChild(efectivoOption);

            const adeudoOption = document.createElement('option');
            adeudoOption.value = 'ADEUDO';
            adeudoOption.textContent = '📝 A Deber (Adeudo)';
            tipoPagoSelect.appendChild(adeudoOption);

            // Ocultar secciones no aplicables inicialmente
            contenedorSeguro.style.display = 'none';
            contenedorMontoAPagar.style.display = 'none';
            contenedorMontoPendiente.style.display = 'none';
            montoPagarInput.value = '';
            montoPagarInput.required = false;

            // Verificar si el paciente tiene seguro activo
            const hasInsurance = await checkPatientInsurance(idPaciente);

            if (hasInsurance) {
                const seguroOption = document.createElement('option');
                seguroOption.value = 'SEGURO';
                seguroOption.textContent = '🛡️ Seguro';
                tipoPagoSelect.appendChild(seguroOption);
            }
            
            // Llenar datos del paciente y fecha
            document.getElementById('nombrePacientePago').textContent = paciente;
            document.getElementById('fechaPacientePago').textContent = fecha;
            document.getElementById('idPacientePago').value = idPaciente;
            
            // Llenar tabla de pruebas
            const tabla = document.getElementById('tablaPruebasPago');
            tabla.innerHTML = '';
            let total = 0;

            grupo.forEach((prueba) => {
                const id = prueba.id;
                const tipo = prueba.tipo_prueba;
                const precio = parseFloat(prueba.precio || 0);
                const id_tipo_prueba = prueba.id_tipo_prueba;
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <input type="checkbox" name="pagos[${id}][seleccionado]" value="1" class="form-check-input pagar-checkbox" checked>
                        <input type="hidden" name="pagos[${id}][precio]" value="${precio}">
                        <input type="hidden" name="pagos[${id}][id_tipo_prueba]" value="${id_tipo_prueba}">
                    </td>
                    <td>${tipo}</td>
                    <td>${precio.toFixed(0)} FCFA</td>
                `;
                tabla.appendChild(row);
                total += precio;
            });
            
            totalPagoSpan.textContent = total.toFixed(0) + ' FCFA';
            
            // Función para actualizar el monto pendiente
            const actualizarMontoPendiente = () => {
                const totalAPagar = parseFloat(totalPagoSpan.textContent.replace(' FCFA', ''));
                const montoPagado = parseFloat(montoPagarInput.value || 0);
                const pendiente = totalAPagar - montoPagado;
                montoPendienteSpan.textContent = pendiente.toFixed(0) + ' FCFA';
            };

            // Event listener para cambios en el tipo de pago
            tipoPagoSelect.addEventListener('change', () => {
                const tipoSeleccionado = tipoPagoSelect.value;
                contenedorSeguro.style.display = 'none';
                contenedorMontoAPagar.style.display = 'none';
                contenedorMontoPendiente.style.display = 'none';
                montoPagarInput.value = '';
                montoPagarInput.required = false;

                if (tipoSeleccionado === 'SEGURO') {
                    contenedorSeguro.style.display = 'block';
                    cargarSeguros(idPaciente, 'idSeguro'); // Cargar seguros del paciente
                } else if (tipoSeleccionado === 'ADEUDO') {
                    contenedorMontoAPagar.style.display = 'block';
                    contenedorMontoPendiente.style.display = 'block';
                    montoPagarInput.required = true; // Hacer obligatorio el monto a pagar si es a deber
                }
                actualizarMontoPendiente();
            });

            // Event listener para cambios en el monto a pagar
            montoPagarInput.addEventListener('input', actualizarMontoPendiente);

            // Event listener para cambios en los checkboxes de selección de pruebas
            tabla.querySelectorAll('.pagar-checkbox').forEach(check => {
                check.addEventListener('change', () => {
                    let nuevoTotal = 0;
                    tabla.querySelectorAll('.pagar-checkbox').forEach(c => {
                        if (c.checked) {
                            const precioInput = c.parentElement.querySelector('input[name$="[precio]"]');
                            nuevoTotal += parseFloat(precioInput.value);
                        }
                    });
                    totalPagoSpan.textContent = nuevoTotal.toFixed(0) + ' FCFA';
                    actualizarMontoPendiente();
                });
            });
        }

        // --- Lógica del nuevo modal de edición ---
        async function handleEditModalLogic(idPaciente, grupo, paciente, fecha) {
            const tipoPagoSelect = document.getElementById('editTipoPago');
            const contenedorSeguro = document.getElementById('editContenedorSeguro');
            const contenedorMontoAPagar = document.getElementById('editContenedorMontoAPagar');
            const contenedorMontoPendiente = document.getElementById('editContenedorMontoPendiente');
            const montoPagarInput = document.getElementById('editMontoPagar');
            const montoPendienteSpan = document.getElementById('editMontoPendiente');
            const totalGrupoSpan = document.getElementById('editTotalGrupo');
            
            // Llenar datos del paciente y fecha en el modal de edición
            document.getElementById('editNombrePaciente').textContent = paciente;
            document.getElementById('editFechaPaciente').textContent = fecha;
            document.getElementById('editIdPaciente').value = idPaciente;
            document.getElementById('editFecha').value = fecha;

            tipoPagoSelect.innerHTML = '';
            
            // Opciones de tipo de pago para edición
            const efectivoOption = document.createElement('option');
            efectivoOption.value = 'EFECTIVO';
            efectivoOption.textContent = '💰 Efectivo';
            tipoPagoSelect.appendChild(efectivoOption);

            const adeudoOption = document.createElement('option');
            adeudoOption.value = 'ADEUDO';
            adeudoOption.textContent = '📝 A Deber (Adeudo)';
            tipoPagoSelect.appendChild(adeudoOption);

            // Ocultar secciones no aplicables inicialmente en edición
            contenedorSeguro.style.display = 'none';
            contenedorMontoAPagar.style.display = 'none';
            contenedorMontoPendiente.style.display = 'none';
            montoPagarInput.value = '';
            montoPagarInput.required = false;

            // Verificar seguro para el paciente en edición
            const hasInsurance = await checkPatientInsurance(idPaciente);

            if (hasInsurance) {
                const seguroOption = document.createElement('option');
                seguroOption.value = 'SEGURO';
                seguroOption.textContent = '🛡️ Seguro';
                tipoPagoSelect.appendChild(seguroOption);
            }
            
            // Llenar tabla de pruebas en el modal de edición
            const tabla = document.getElementById('tablaPruebasEditar');
            tabla.innerHTML = '';
            let totalGrupo = 0;

            grupo.forEach((prueba) => {
                const id = prueba.id;
                const tipo = prueba.tipo_prueba;
                const precio = parseFloat(prueba.precio || 0);
                const pagado = prueba.pagado; // 0 si no está pagado, 1 si está pagado
                const id_tipo_prueba = prueba.id_tipo_prueba;
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${tipo}</td>
                    <td>${precio.toFixed(0)} FCFA</td>
                    <td>
                        <span class="badge ${pagado == 1 ? 'bg-success' : 'bg-danger'}">${pagado == 1 ? 'Pagado' : 'Pendiente'}</span>
                        <input type="hidden" name="pruebas[${id}][id]" value="${id}">
                        <input type="hidden" name="pruebas[${id}][precio]" value="${precio}">
                        <input type="hidden" name="pruebas[${id}][pagado_actual]" value="${pagado}">
                        <input type="hidden" name="pruebas[${id}][id_tipo_prueba]" value="${id_tipo_prueba}">
                    </td>
                `;
                tabla.appendChild(row);
                totalGrupo += precio;
            });
            
            totalGrupoSpan.textContent = totalGrupo.toFixed(0) + ' FCFA';

            // Función para actualizar el monto pendiente en edición
            const actualizarMontoPendienteEdit = () => {
                const totalDelGrupo = parseFloat(totalGrupoSpan.textContent.replace(' FCFA', ''));
                const montoPagado = parseFloat(montoPagarInput.value || 0);
                const pendiente = totalDelGrupo - montoPagado;
                montoPendienteSpan.textContent = pendiente.toFixed(0) + ' FCFA';
            };

            // Event listener para cambios en el tipo de pago en edición
            tipoPagoSelect.addEventListener('change', () => {
                const tipoSeleccionado = tipoPagoSelect.value;
                contenedorSeguro.style.display = 'none';
                contenedorMontoAPagar.style.display = 'none';
                contenedorMontoPendiente.style.display = 'none';
                montoPagarInput.value = '';
                montoPagarInput.required = false;

                if (tipoSeleccionado === 'SEGURO') {
                    contenedorSeguro.style.display = 'block';
                    cargarSeguros(idPaciente, 'editIdSeguro'); // Cargar seguros del paciente
                } else if (tipoSeleccionado === 'ADEUDO') {
                    contenedorMontoAPagar.style.display = 'block';
                    contenedorMontoPendiente.style.display = 'block';
                    montoPagarInput.required = true; // Hacer obligatorio el monto a pagar si es a deber
                }
                actualizarMontoPendienteEdit();
            });

            // Event listener para cambios en el monto a pagar en edición
            montoPagarInput.addEventListener('input', actualizarMontoPendienteEdit);
        }

        // Función auxiliar para cargar los seguros de un paciente (implementación pendiente)
        async function cargarSeguros(idPaciente, selectId) {
            // Aquí deberías hacer una llamada AJAX a tu backend para obtener los seguros del paciente
            // y llenar el select con id 'selectId'.
            // Ejemplo:
            /*
            fetch('api/obtener_seguros.php?paciente_id=' + idPaciente)
                .then(response => response.json())
                .then(data => {
                    const selectElement = document.getElementById(selectId);
                    selectElement.innerHTML = ''; // Limpiar opciones existentes
                    data.forEach(seguro => {
                        const option = document.createElement('option');
                        option.value = seguro.id;
                        option.textContent = `${seguro.nombre} - Saldo: ${seguro.saldo_actual} FCFA`;
                        selectElement.appendChild(option);
                    });
                });
            */
            console.log(`Cargando seguros para el paciente ${idPaciente} en el select ${selectId}`);
            // Placeholder: añadir opciones manualmente si no se implementa AJAX
             const selectElement = document.getElementById(selectId);
             selectElement.innerHTML = `
                 <option value="1">Seguro Médico Principal - Saldo: 50000 FCFA</option>
                 <option value="2">Seguro Familiar - Saldo: 30000 FCFA</option>
             `;
        }

        // Función auxiliar para verificar si un paciente tiene seguro (implementación pendiente)
        async function checkPatientInsurance(idPaciente) {
            // Aquí deberías hacer una llamada AJAX a tu backend para verificar si el paciente tiene seguro
            // y devolver true o false.
            // Ejemplo:
            /*
            return fetch('api/verificar_seguro.php?paciente_id=' + idPaciente)
                .then(response => response.json())
                .then(data => data.has_insurance);
            */
            console.log(`Verificando seguro para el paciente ${idPaciente}`);
            // Placeholder: devolver true para probar la funcionalidad
            return true; 
        }

    });
</script>