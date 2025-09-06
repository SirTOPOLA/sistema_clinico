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
    a.tipo_pago,                 -- <--- CAMBIO: Se agrega el tipo de pago
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

<div class="modal fade" id="modalEditarPago" tabindex="-1">
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
                            <th>ID</th>
                            <th>Tipo de Prueba</th>
                            <th>Precio</th>
                            <th>Estado de Pago</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPruebasEditar">
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
            btn.addEventListener('click', () => {
                const grupo = JSON.parse(btn.dataset.grupo);
                const paciente = btn.dataset.paciente;
                const fecha = btn.dataset.fecha;
                const idPaciente = btn.dataset.pacienteId;
                setupEditModal(grupo, paciente, fecha, idPaciente);
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
            
            const efectivoOption = document.createElement('option');
            efectivoOption.value = 'EFECTIVO';
            efectivoOption.textContent = '💰 Efectivo';
            tipoPagoSelect.appendChild(efectivoOption);

            const adeberOption = document.createElement('option');
            adeberOption.value = 'ADEBER';
            adeberOption.textContent = '📝 A Deber (Préstamo)';
            tipoPagoSelect.appendChild(adeberOption);

            // Ocultar inicialmente los campos de monto a pagar y monto pendiente
            contenedorSeguro.style.display = 'none';
            contenedorMontoAPagar.style.display = 'none';
            contenedorMontoPendiente.style.display = 'none';
            montoPagarInput.value = '';

            const hasInsurance = await checkPatientInsurance(idPaciente);

            if (hasInsurance) {
                const seguroOption = document.createElement('option');
                seguroOption.value = 'SEGURO';
                seguroOption.textContent = '🛡️ Seguro';
                tipoPagoSelect.appendChild(seguroOption);
            }
            
            document.getElementById('nombrePacientePago').textContent = paciente;
            document.getElementById('fechaPacientePago').textContent = fecha;
            document.getElementById('idPacientePago').value = idPaciente;
            
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
            
            const actualizarMontoPendiente = () => {
                const totalAPagar = parseFloat(totalPagoSpan.textContent.replace(' FCFA', ''));
                const montoPagado = parseFloat(montoPagarInput.value || 0);
                const pendiente = totalAPagar - montoPagado;
                montoPendienteSpan.textContent = pendiente.toFixed(0) + ' FCFA';
            };

            tipoPagoSelect.addEventListener('change', () => {
                const tipoSeleccionado = tipoPagoSelect.value;
                contenedorSeguro.style.display = 'none';
                contenedorMontoAPagar.style.display = 'none';
                contenedorMontoPendiente.style.display = 'none';
                montoPagarInput.value = '';
                montoPagarInput.required = false;

                if (tipoSeleccionado === 'SEGURO') {
                    contenedorSeguro.style.display = 'block';
                    cargarSeguros(idPaciente);
                } else if (tipoSeleccionado === 'ADEBER') {
                    contenedorMontoAPagar.style.display = 'block';
                    contenedorMontoPendiente.style.display = 'block';
                    montoPagarInput.required = true;
                }
                actualizarMontoPendiente();
            });

            montoPagarInput.addEventListener('input', actualizarMontoPendiente);

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

        // Lógica del modal de Editar Pago
        function setupEditModal(grupo, paciente, fecha, idPaciente) {
            document.getElementById('editNombrePaciente').textContent = paciente;
            document.getElementById('editFechaPaciente').textContent = fecha;
            document.getElementById('editIdPaciente').value = idPaciente;
            document.getElementById('editFecha').value = fecha;

            const tabla = document.getElementById('tablaPruebasEditar');
            tabla.innerHTML = '';

            grupo.forEach(prueba => {
                const id = prueba.id;
                const tipo = prueba.tipo_prueba;
                const precio = parseFloat(prueba.precio || 0);
                const pagado = prueba.pagado;

                const estadoPagado = pagado === "1";
                const options = estadoPagado
                    ? `<option value="1" selected>Pagado</option><option value="0">Pendiente</option>`
                    : `<option value="0" selected>Pendiente</option><option value="1">Pagado</option>`;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${id}</td>
                    <td>${tipo}</td>
                    <td>${precio.toFixed(0)} FCFA</td>
                    <td>
                        <select name="pagos[${id}][pagado]" class="form-select" required>
                            ${options}
                        </select>
                        <input type="hidden" name="pagos[${id}][id_tipo_prueba]" value="${prueba.id_tipo_prueba}">
                    </td>
                `;
                tabla.appendChild(row);
            });
        }
        
        // Función para el mensaje de alerta
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                alert.classList.add('fade');
            }
        }, 10000); // 10 segundos
    });

    // Funciones de la API (para cargar seguros)
    async function checkPatientInsurance(idPaciente) {
        try {
            const response = await fetch(`api/verificar_seguro.php?id_paciente=${idPaciente}`);
            if (!response.ok) {
                throw new Error('Error de red al verificar el seguro.');
            }
            const data = await response.json();
            return data.has_insurance;
        } catch (error) {
            console.error('Error al verificar el seguro del paciente:', error);
            return false;
        }
    }

    function cargarSeguros(idPaciente) {
        const idSeguroSelect = document.getElementById('idSeguro');
        idSeguroSelect.innerHTML = '<option value="">Cargando...</option>';

        fetch(`api/obtener_seguros_paciente.php?id_paciente=${idPaciente}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(seguros => {
                idSeguroSelect.innerHTML = '';
                if (seguros.length > 0) {
                    seguros.forEach(seguro => {
                        const option = document.createElement('option');
                        option.value = seguro.id;
                        option.textContent = `ID: ${seguro.id} - Saldo: ${parseFloat(seguro.saldo_actual).toFixed(0)} FCFA`;
                        idSeguroSelect.appendChild(option);
                    });
                } else {
                    idSeguroSelect.innerHTML = '<option value="">No hay seguros disponibles</option>';
                }
            })
            .catch(error => {
                console.error('Error al cargar seguros:', error);
                idSeguroSelect.innerHTML = '<option value="">Error al cargar seguros</option>';
            });
    }
</script>
 