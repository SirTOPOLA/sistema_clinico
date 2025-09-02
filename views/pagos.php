<?php 
// Consulta optimizada: pacientes con o sin seguro (titular o beneficiario)
$sql = "
    SELECT  
        a.id,  
        a.resultado,  
        a.estado,  
        a.codigo_paciente,  
        a.pagado, 
        tp.id AS id_tipo_prueba,       
        tp.nombre AS tipo_prueba, 
        tp.precio, 
        p.id AS id_paciente,           
        CONCAT(p.nombre, ' ', p.apellidos) AS paciente, 
        a.fecha_registro, 
        DATE(a.fecha_registro) AS fecha_solo,
        s.id AS id_seguro,
        s.saldo_actual AS saldo_seguro
    FROM analiticas a 
    JOIN tipo_pruebas tp ON a.id_tipo_prueba = tp.id 
    JOIN pacientes p ON a.id_paciente = p.id

    LEFT JOIN (
        SELECT 
            s.id, 
            s.titular_id, 
            s.saldo_actual, 
            sb.paciente_id AS beneficiario_id
        FROM seguros s
        LEFT JOIN seguros_beneficiarios sb ON s.id = sb.seguro_id
    ) AS s ON s.titular_id = p.id OR s.beneficiario_id = p.id

    ORDER BY a.fecha_registro DESC
";

$stmt = $pdo->query($sql); 
$analiticas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupamiento por paciente y fecha
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
            'tiene_seguro' => (bool) $a['id_seguro'],
            'saldo_seguro' => (float) ($a['saldo_seguro'] ?? 0)
        ]; 
    } 

    $grupos[$clave]['registros'][] = $a; 
    $grupos[$clave]['pagos'][] = $a['pagado']; 
} 
?>

<div class="container-fluid" id="content">

  <!-- Pagos -->
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
                    <th>Estado de Pago</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grupos as $grupo): ?>
                    <?php
                    // Lógica para determinar el estado del pago
                    $estado_pago = 'Pendiente';
                    $badge_class = 'bg-warning text-dark';
                    $es_pagado = !in_array(0, $grupo['pagos']);

                    if ($es_pagado) {
                        // Aquí es donde deberías tener la lógica para saber si el pago fue por seguro, efectivo, etc.
                        // Para este ejemplo, lo manejaremos con un valor estático, pero idealmente vendría de la base de datos.
                        $estado_pago = 'Pagado';
                        $badge_class = 'bg-success';
                    }
                    ?>
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
                                <span class="badge bg-primary">Con Resultado</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Sin Resultado</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($grupo['fecha'])) ?></td>
                        <td>
                            <span class="badge <?= $badge_class ?>">
                                <?= $estado_pago ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!$es_pagado): ?>
                                <button class="btn btn-sm btn-outline-success btn-pagar" data-bs-toggle="modal"
                                    data-bs-target="#modalPagar"
                                    data-grupo='<?= json_encode(array_filter($grupo['registros'], fn($r) => $r['pagado'] == 0)) ?>'
                                    data-paciente="<?= htmlspecialchars($grupo['paciente']) ?>"
                                    data-fecha="<?= htmlspecialchars($grupo['fecha']) ?>" title="Pagar pruebas">
                                    <i class="bi bi-cash-coin me-1"></i> Pagar
                                </button>
                            <?php else: ?>
                                <a href="fpdf/generar_factura.php?id=<?= $grupo['registros'][0]['id'] ?>&fecha=<?= $grupo['fecha'] ?>"
                                    target="_blank" class="btn btn-outline-secondary btn-sm" title="Imprimir Factura">
                                    <i class="bi bi-printer"></i> Imprimir Factura
                                </a>
                            <?php endif; ?>
                            <a href="fpdf/imprimir_pruebas.php?id=<?= $grupo['id_paciente'] ?>&fecha=<?= $grupo['fecha'] ?>"
                                target="_blank" class="btn btn-outline-primary btn-sm mt-1" title="Ver Pruebas Médicas">
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
                <input type="hidden" name="ids_seleccionados" id="idsSeleccionadosPago">

                <div class="mb-3">
                    <strong>Paciente:</strong> <span id="nombrePacientePago"></span><br>
                    <strong>Fecha:</strong> <span id="fechaPacientePago"></span>
                </div>
<div class="mb-3">
    <label class="form-label">Tipo de Pago:</label>
    <div class="btn-group w-100" role="group" aria-label="Tipo de Pago">
        <input type="radio" class="btn-check" name="metodo_pago" id="pagoEfectivo" autocomplete="off" value="EFECTIVO" checked>
        <label class="btn btn-outline-primary" for="pagoEfectivo">
            <i class="bi bi-cash me-1"></i> Efectivo
        </label>

        <?php if (!empty($tiene_seguro)): ?>
            <input type="radio" class="btn-check" name="metodo_pago" id="pagoSeguro" autocomplete="off" value="SEGURO">
            <label class="btn btn-outline-primary" for="pagoSeguro">
                <i class="bi bi-shield-lock me-1"></i> Seguro
            </label>
        <?php endif; ?>

        <input type="radio" class="btn-check" name="metodo_pago" id="pagoDebe" autocomplete="off" value="DEBE">
        <label class="btn btn-outline-primary" for="pagoDebe">
            <i class="bi bi-file-text me-1"></i> Deja a Deber
        </label>
    </div>
</div>


                <div id="descuento-group" class="mb-3 d-none">
                    <label for="descuento" class="form-label">Descuento (%)</label>
                    <input type="number" name="descuento" id="descuento" class="form-control" step="0.01" value="0">
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
                
                <div class="text-end">
                    <strong>Total a Pagar: </strong><span id="totalPago" class="fs-5">0 FCFA</span>
                    <br>
                    <div id="descuento-aplicado" class="d-none">
                        <strong>Descuento Aplicado: </strong><span id="valorDescuento" class="fs-6">0 FCFA</span>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="bi bi-wallet2 me-1"></i> Confirmar Pago</button>
            </div>
        </form>
    </div>
</div>
 


<!-- Modal Editar Pago -->
<div class="modal fade" id="modalEditarPago" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form action="actualizar_pago.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id-pago">
        <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
        <div class="col-md-6">
          <label>Analítica</label>
          <select name="id_analitica" id="edit-id_analitica" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($analiticas as $a): ?>
              <option value="<?= $a['id'] ?>"><?= "Código: {$a['codigo_paciente']} - {$a['tipo_prueba']}" ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>Tipo de Prueba</label>
          <select name="id_tipo_prueba" id="edit-id_tipo_prueba" class="form-select" required>
            <option value="">Seleccione</option>
            <?php foreach ($tipos as $t): ?>
              <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nombre']) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-6">
          <label>Cantidad (€)</label>
          <input type="number" step="0.01" name="cantidad" id="edit-cantidad" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
      </div>
    </form>
  </div>
</div>




<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalPagar = document.getElementById('modalPagar');
        const totalPagoSpan = document.getElementById('totalPago');
        const descuentoGroup = document.getElementById('descuento-group');
        const descuentoInput = document.getElementById('descuento');
        const valorDescuentoSpan = document.getElementById('valorDescuento');
        const descuentoAplicadoDiv = document.getElementById('descuento-aplicado');

        // Función para calcular el total
        const calcularTotal = () => {
            let nuevoTotal = 0;
            const checkboxes = modalPagar.querySelectorAll('.pagar-checkbox:checked');
            checkboxes.forEach(c => {
                const precioInput = c.closest('tr').querySelector('input[name$="[precio]"]');
                if (precioInput) {
                    nuevoTotal += parseFloat(precioInput.value);
                }
            });
            
            let totalFinal = nuevoTotal;
            let valorDescuento = 0;
            
            const metodoPago = document.querySelector('input[name="metodo_pago"]:checked').value;
            if (metodoPago === 'SEGURO') {
                const descuentoPorcentaje = parseFloat(descuentoInput.value) || 0;
                valorDescuento = (nuevoTotal * descuentoPorcentaje) / 100;
                totalFinal = nuevoTotal - valorDescuento;
                descuentoAplicadoDiv.classList.remove('d-none');
            } else {
                descuentoAplicadoDiv.classList.add('d-none');
            }

            totalPagoSpan.textContent = totalFinal.toFixed(0) + ' FCFA';
            valorDescuentoSpan.textContent = valorDescuento.toFixed(0) + ' FCFA';
        };

        // Event listener para el botón "Pagar"
        document.querySelectorAll('.btn-pagar').forEach(btn => {
            btn.addEventListener('click', () => {
                const grupo = JSON.parse(btn.dataset.grupo);
                const paciente = btn.dataset.paciente;
                const fecha = btn.dataset.fecha;

                document.getElementById('nombrePacientePago').textContent = paciente;
                document.getElementById('fechaPacientePago').textContent = fecha;

                const tabla = document.getElementById('tablaPruebasPago');
                tabla.innerHTML = '';
                
                // Limpiar y resetear el modal
                descuentoInput.value = 0;
                document.getElementById('pagoEfectivo').checked = true;
                descuentoGroup.classList.add('d-none');

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
                });

                // Añadir event listeners para los checkboxes y el total
                tabla.querySelectorAll('.pagar-checkbox').forEach(check => {
                    check.addEventListener('change', calcularTotal);
                });

                // Añadir event listeners para los radio buttons de método de pago
                document.querySelectorAll('input[name="metodo_pago"]').forEach(radio => {
                    radio.addEventListener('change', (e) => {
                        if (e.target.value === 'SEGURO') {
                            descuentoGroup.classList.remove('d-none');
                        } else {
                            descuentoGroup.classList.add('d-none');
                        }
                        calcularTotal();
                    });
                });
                
                descuentoInput.addEventListener('input', calcularTotal);
                
                calcularTotal(); // Llamada inicial para mostrar el total
            });
        });
    });
</script>

<script>
  setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
      alert.classList.remove('show');
      alert.classList.add('fade');
    }
  }, 10000); // 10 segundos
</script>