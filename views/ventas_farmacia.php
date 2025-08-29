<?php
// Este archivo requiere la inclusión de 'config/conexion.php' y una sesión activa
// La inclusión debe estar en el archivo principal que carga esta vista.
// Por ejemplo:
// require_once 'config/conexion.php'; 
// session_start();

$idUsuario = $_SESSION['usuario']['id'] ?? 0;

// Lógica para obtener los datos de la base de datos
try {
    // Consulta SQL para seleccionar todas las ventas de la tabla 'ventas'
    $sql = "SELECT 
                v.id,
                p.nombre AS nombre_paciente,
                u.nombre_usuario ,
                v.fecha,
                v.monto_total,
                v.estado_pago,
                v.metodo_pago
            FROM ventas v
            LEFT JOIN pacientes p ON v.paciente_id = p.id
            LEFT JOIN usuarios u ON v.usuario_id = u.id
            ORDER BY v.fecha DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Si hay un error, se guarda un mensaje en la sesión
    $_SESSION['error'] = 'Error de base de datos: ' . $e->getMessage();
    $ventas = []; // Inicializa la variable para evitar errores en la vista
}
?>

<div class="container-fluid" id="content">

    <div class="row mb-3">
        <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-cart-fill me-2"></i>Gestión de Ventas</h3>
            <div class="d-flex gap-2">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearVenta">
                    <i class="bi bi-plus-circle me-1"></i>Registrar Venta
                </button>
            </div>
        </div>
        <div class="col-md-4">
            <input type="text" id="buscadorVentas" class="form-control" placeholder="Buscar venta...">
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
            <table id="tablaVentas" class="table table-hover table-bordered table-sm align-middle">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th>ID</th>
                        <th>Paciente</th>
                        <th>Atendido por</th>
                        <th>Fecha</th>
                        <th>Monto Total</th>
                        <th>Estado Pago</th>
                        <th>Método Pago</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Bucle para mostrar las ventas dinámicamente
                    foreach ($ventas as $venta):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($venta['id']) ?></td>
                            <td><?= htmlspecialchars($venta['nombre_paciente']) ?></td>
                            <td><?= htmlspecialchars($venta['nombre_usuario']) ?></td>
                            <td class="fecha-venta"><?= date('d/m/Y', strtotime($venta['fecha'])) ?></td>
                            <td class="monto-total-venta"><?= number_format($venta['monto_total'], 2) . ' XAF' ?></td>
                            <td class="estado-pago"><?= htmlspecialchars($venta['estado_pago']) ?></td>
                            <td class="metodo-pago"><?= htmlspecialchars($venta['metodo_pago']) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-info btn-ver-detalles-venta"
                                    data-id="<?= $venta['id'] ?>" data-bs-toggle="modal"
                                    data-bs-target="#modalDetallesVenta">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary btn-editar-venta"
                                    data-id="<?= $venta['id'] ?>" data-bs-toggle="modal" data-bs-target="#modalEditarVenta">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="eliminar_venta_farmacia.php?id=<?= $venta['id'] ?>"
                                    class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta venta?')">
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

<div class="modal fade" id="modalCrearVenta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="api/guardar_venta_farmacia.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Registrar Nueva Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                <div class="col-md-6">
                    <label class="form-label">Paciente</label>
                    <input type="text" id="paciente-buscador" class="form-control"
                        placeholder="Buscar por nombre o ID...">
                    <input type="hidden" name="paciente_id" id="paciente_id_input">
                    <div id="paciente-resultados" class="mt-2 list-group"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de Venta</label>
                    <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <hr class="my-3">
                <div class="col-md-12">
                    <h5>Productos de la Venta</h5>
                    <div class="input-group mb-3">
                        <input type="text" id="producto-buscador" class="form-control" placeholder="Buscar producto...">
                        <input type="number" id="cantidad-producto" class="form-control" placeholder="Cantidad" min="1"
                            value="1">
                        <input type="number" id="descuento-producto" class="form-control" placeholder="Descuento (%)"
                            min="0" max="100" value="0">
                        <button class="btn btn-outline-secondary" type="button"
                            id="btn-agregar-producto">Agregar</button>
                    </div>
                    <div id="producto-resultados" class="list-group"></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-sm" id="tabla-detalle-venta-crear">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Descuento</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2"><span id="monto-total-display-crear">0.00 XAF</span></td>
                                    <input type="hidden" name="monto_total" id="monto_total_input_crear">
                                    <input type="hidden" name="productos_json" id="productos_json_crear">
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <hr class="my-3">
                <div class="col-md-6">
                    <label class="form-label">Método de Pago</label>
                    <select name="metodo_pago" class="form-select">
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TARJETA">Tarjeta</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="OTRO">Otro</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado de Pago</label>
                    <select name="estado_pago" class="form-select">
                        <option value="PAGADO">Pagado</option>
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="PARCIAL">Parcial</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Monto Recibido</label>
                    <input type="number" name="monto_recibido" id="monto_recibido_input_crear" class="form-control"
                        step="0.01" value="0" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cambio Devuelto</label>
                    <input type="text" id="cambio_devuelto_display_crear" class="form-control" value="0.00 XAF"
                        readonly>
                    <input type="hidden" name="cambio_devuelto" id="cambio_devuelto_input_crear">
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="seguro-check" name="seguro" value="1">
                        <label class="form-check-label" for="seguro-check">Venta con seguro</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar Venta</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDetallesVenta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-eye me-2"></i>Detalles de la Venta <span
                        id="detalle-venta-id"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="detalles-venta-info">
                    <div class="col-md-6">
                        <strong>Paciente:</strong> <span id="detalle-paciente"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Atendido por:</strong> <span id="detalle-usuario"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Fecha:</strong> <span id="detalle-fecha"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Monto Total:</strong> <span id="detalle-monto-total"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Método de Pago:</strong> <span id="detalle-metodo-pago"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Estado de Pago:</strong> <span id="detalle-estado-pago"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Monto Recibido:</strong> <span id="detalle-monto-recibido"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Cambio Devuelto:</strong> <span id="detalle-cambio-devuelto"></span>
                    </div>
                    <div class="col-md-12">
                        <strong>Seguro:</strong> <span id="detalle-seguro"></span>
                    </div>
                    <hr class="my-3">
                    <div class="col-md-12">
                        <h5>Productos Vendidos</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Descuento</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody id="detalle-productos-table">
                                    </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarVenta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="api/actualizar_venta_farmacia.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Editar Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="edit-venta-id">
                <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                <div class="col-md-6">
                    <label class="form-label">Paciente</label>
                    <input type="text" id="edit-paciente-buscador" class="form-control"
                        placeholder="Buscar por nombre o ID...">
                    <input type="hidden" name="paciente_id" id="edit-paciente-id-input">
                    <div id="edit-paciente-resultados" class="mt-2 list-group"></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de Venta</label>
                    <input type="date" name="fecha" id="edit-fecha" class="form-control" required>
                </div>
                <hr class="my-3">
                <div class="col-md-12">
                    <h5>Productos de la Venta</h5>
                    <div class="input-group mb-3">
                        <input type="text" id="edit-producto-buscador" class="form-control"
                            placeholder="Buscar producto...">
                        <input type="number" id="edit-cantidad-producto" class="form-control" placeholder="Cantidad"
                            min="1" value="1">
                        <input type="number" id="edit-descuento-producto" class="form-control"
                            placeholder="Descuento (%)" min="0" max="100" value="0">
                        <button class="btn btn-outline-secondary" type="button"
                            id="btn-agregar-producto-editar">Agregar</button>
                    </div>
                    <div id="edit-producto-resultados" class="list-group"></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-sm" id="tabla-detalle-venta-editar">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Descuento</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2"><span id="monto-total-display-editar">0.00 XAF</span></td>
                                    <input type="hidden" name="monto_total" id="monto_total_input_editar">
                                    <input type="hidden" name="productos_json" id="productos_json_editar">
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <hr class="my-3">
                <div class="col-md-6">
                    <label class="form-label">Método de Pago</label>
                    <select name="metodo_pago" id="edit-metodo-pago" class="form-select">
                        <option value="EFECTIVO">Efectivo</option>
                        <option value="TARJETA">Tarjeta</option>
                        <option value="TRANSFERENCIA">Transferencia</option>
                        <option value="OTRO">Otro</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado de Pago</label>
                    <select name="estado_pago" id="edit-estado-pago" class="form-select">
                        <option value="PAGADO">Pagado</option>
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="PARCIAL">Parcial</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Monto Recibido</label>
                    <input type="number" name="monto_recibido" id="monto_recibido_input_editar" class="form-control"
                        step="0.01" value="0" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cambio Devuelto</label>
                    <input type="text" id="cambio_devuelto_display_editar" class="form-control" value="0.00 XAF"
                        readonly>
                    <input type="hidden" name="cambio_devuelto" id="cambio_devuelto_input_editar">
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit-seguro-check" name="seguro" value="1">
                        <label class="form-check-label" for="edit-seguro-check">Venta con seguro</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar Venta</button>
            </div>
        </form>
    </div>
</div>

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

    document.addEventListener('DOMContentLoaded', function () {
        const productosAgregadosCrear = {};
        const productosAgregadosEditar = {};
        const currency = ' XAF';

        // --- Lógica del Modal CREAR Venta ---
        const pacienteBuscador = document.getElementById('paciente-buscador');
        const pacienteIdInput = document.getElementById('paciente_id_input');
        const pacienteResultados = document.getElementById('paciente-resultados');
        const productoBuscador = document.getElementById('producto-buscador');
        const cantidadProductoInput = document.getElementById('cantidad-producto');
        const descuentoProductoInput = document.getElementById('descuento-producto');
        const productoResultados = document.getElementById('producto-resultados');
        const btnAgregarProducto = document.getElementById('btn-agregar-producto');
        const tablaDetalleVentaCrear = document.getElementById('tabla-detalle-venta-crear');
        const montoTotalDisplayCrear = document.getElementById('monto-total-display-crear');
        const montoTotalInputCrear = document.getElementById('monto_total_input_crear');
        const montoRecibidoInputCrear = document.getElementById('monto_recibido_input_crear');
        const cambioDevueltoDisplayCrear = document.getElementById('cambio_devuelto_display_crear');
        const cambioDevueltoInputCrear = document.getElementById('cambio_devuelto_input_crear');
        const productosJsonCrear = document.getElementById('productos_json_crear');

        // --- Lógica del Modal EDITAR Venta ---
        const btnEditarVenta = document.querySelectorAll('.btn-editar-venta');
        const editVentaId = document.getElementById('edit-venta-id');
        const editPacienteBuscador = document.getElementById('edit-paciente-buscador');
        const editPacienteIdInput = document.getElementById('edit-paciente-id-input');
        const editPacienteResultados = document.getElementById('edit-paciente-resultados');
        const editFecha = document.getElementById('edit-fecha');
        const editMetodoPago = document.getElementById('edit-metodo-pago');
        const editEstadoPago = document.getElementById('edit-estado-pago');
        const editSeguroCheck = document.getElementById('edit-seguro-check');
        const editMontoRecibidoInput = document.getElementById('monto_recibido_input_editar');
        const editCambioDevueltoDisplay = document.getElementById('cambio_devuelto_display_editar');
        const editCambioDevueltoInput = document.getElementById('cambio_devuelto_input_editar');
        const editProductoBuscador = document.getElementById('edit-producto-buscador');
        const editCantidadProductoInput = document.getElementById('edit-cantidad-producto');
        const editDescuentoProductoInput = document.getElementById('edit-descuento-producto');
        const editProductoResultados = document.getElementById('edit-producto-resultados');
        const btnAgregarProductoEditar = document.getElementById('btn-agregar-producto-editar');
        const tablaDetalleVentaEditar = document.getElementById('tabla-detalle-venta-editar');
        const montoTotalDisplayEditar = document.getElementById('monto-total-display-editar');
        const montoTotalInputEditar = document.getElementById('monto_total_input_editar');
        const productosJsonEditar = document.getElementById('productos_json_editar');

        // --- Lógica del Modal VER DETALLES Venta ---
        const btnVerDetalles = document.querySelectorAll('.btn-ver-detalles-venta');
        const detalleVentaId = document.getElementById('detalle-venta-id');
        const detallePaciente = document.getElementById('detalle-paciente');
        const detalleUsuario = document.getElementById('detalle-usuario');
        const detalleFecha = document.getElementById('detalle-fecha');
        const detalleMontoTotal = document.getElementById('detalle-monto-total');
        const detalleMetodoPago = document.getElementById('detalle-metodo-pago');
        const detalleEstadoPago = document.getElementById('detalle-estado-pago');
        const detalleMontoRecibido = document.getElementById('detalle-monto-recibido');
        const detalleCambioDevuelto = document.getElementById('detalle-cambio-devuelto');
        const detalleSeguro = document.getElementById('detalle-seguro');
        const detalleProductosTable = document.getElementById('detalle-productos-table');

        // Función genérica para manejar la búsqueda de pacientes
        async function buscarPacientes(query, resultadosElement, idInput, nombreInput) {
            resultadosElement.innerHTML = '';
            if (query.length < 2) return;
            try {
                const response = await fetch(`api/obtener_paciente.php?q=${query}`);
                const pacientes = await response.json();
                if (pacientes.length > 0) {
                    pacientes.forEach(paciente => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = `${paciente.nombre} (${paciente.codigo})`;
                        item.addEventListener('click', function (e) {
                            e.preventDefault();
                            nombreInput.value = paciente.nombre;
                            idInput.value = paciente.id;
                            resultadosElement.innerHTML = '';
                        });
                        resultadosElement.appendChild(item);
                    });
                } else {
                    resultadosElement.innerHTML = '<div class="p-2">No se encontraron pacientes.</div>';
                }
            } catch (error) {
                console.error('Error al buscar pacientes:', error);
            }
        }

        // Función genérica para manejar la búsqueda de productos
        async function buscarProductos(query, resultadosElement, buscadorInput) {
            resultadosElement.innerHTML = '';
            if (query.length < 2) return;
            try {
                const response = await fetch(`api/obtener_producto_farmacia.php?q=${query}`);
                const productos = await response.json();
                if (productos.length > 0) {
                    productos.forEach(producto => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.dataset.id = producto.id;
                        item.dataset.nombre = producto.nombre;
                        item.dataset.precio = producto.precio_venta;
                        item.textContent = `${producto.nombre} - ${producto.precio_venta}${currency}`;
                        item.addEventListener('click', function (e) {
                            e.preventDefault();
                            buscadorInput.value = producto.nombre;
                            buscadorInput.dataset.id = producto.id;
                            buscadorInput.dataset.precio = producto.precio_venta;
                            resultadosElement.innerHTML = '';
                        });
                        resultadosElement.appendChild(item);
                    });
                } else {
                    resultadosElement.innerHTML = '<div class="p-2">No se encontraron productos.</div>';
                }
            } catch (error) {
                console.error('Error al buscar productos:', error);
            }
        }

        // Función para renderizar la tabla de productos y actualizar los cálculos
        function actualizarCalculos(productos, montoRecibidoInput, montoTotalDisplay, montoTotalInput, cambioDevueltoDisplay, cambioDevueltoInput, productosJsonInput, tablaDetalleVenta) {
            let total = 0;
            const tablaBody = tablaDetalleVenta.querySelector('tbody');
            tablaBody.innerHTML = '';

            for (const id in productos) {
                const producto = productos[id];
                const subtotal = (producto.precio * producto.cantidad) * (1 - (producto.descuento / 100));
                total += subtotal;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        ${producto.nombre}
                        <input type="hidden" name="productos[${id}][id]" value="${id}">
                        <input type="hidden" name="productos[${id}][cantidad]" value="${producto.cantidad}">
                        <input type="hidden" name="productos[${id}][descuento]" value="${producto.descuento}">
                    </td>
                    <td>${producto.cantidad}</td>
                    <td>${producto.precio.toFixed(2)}${currency}</td>
                    <td>${producto.descuento}%</td>
                    <td>${subtotal.toFixed(2)}${currency}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-eliminar-producto" data-id="${id}">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </td>
                `;
                tablaBody.appendChild(row);
            }

            montoTotalDisplay.textContent = `${total.toFixed(2)}${currency}`;
            montoTotalInput.value = total.toFixed(2);
            productosJsonInput.value = JSON.stringify(productos);

            const montoRecibido = parseFloat(montoRecibidoInput.value) || 0;
            const cambio = Math.max(0, montoRecibido - total);
            cambioDevueltoDisplay.value = `${cambio.toFixed(2)}${currency}`;
            cambioDevueltoInput.value = cambio.toFixed(2);
        }

        // Función para agregar un producto a la lista
        function agregarProducto(productoBuscador, cantidadInput, descuentoInput, productosList, context) {
            const productoId = productoBuscador.dataset.id;
            const productoNombre = productoBuscador.value;
            const cantidad = parseInt(cantidadInput.value, 10);
            const precio = parseFloat(productoBuscador.dataset.precio);
            const descuento = parseFloat(descuentoInput.value, 10) || 0;

            if (!productoId || !productoNombre || isNaN(cantidad) || cantidad <= 0 || isNaN(precio) || isNaN(descuento)) {
                console.error('Por favor, seleccione un producto y ingrese una cantidad y descuento válidos.');
                return;
            }

            if (productosList[productoId]) {
                productosList[productoId].cantidad += cantidad;
            } else {
                productosList[productoId] = {
                    id: productoId,
                    nombre: productoNombre,
                    cantidad: cantidad,
                    precio: precio,
                    descuento: descuento
                };
            }

            productoBuscador.value = '';
            cantidadInput.value = 1;
            descuentoInput.value = 0;
            productoBuscador.dataset.id = '';
            productoBuscador.dataset.precio = '';

            if (context === 'crear') {
                actualizarCalculos(productosAgregadosCrear, montoRecibidoInputCrear, montoTotalDisplayCrear, montoTotalInputCrear, cambioDevueltoDisplayCrear, cambioDevueltoInputCrear, productosJsonCrear, tablaDetalleVentaCrear);
            } else if (context === 'editar') {
                actualizarCalculos(productosAgregadosEditar, editMontoRecibidoInput, montoTotalDisplayEditar, montoTotalInputEditar, editCambioDevueltoDisplay, editCambioDevueltoInput, productosJsonEditar, tablaDetalleVentaEditar);
            }
        }
        
        // Función para eliminar un producto de la lista
        function eliminarProducto(id, productosList, context) {
            delete productosList[id];
            if (context === 'crear') {
                actualizarCalculos(productosAgregadosCrear, montoRecibidoInputCrear, montoTotalDisplayCrear, montoTotalInputCrear, cambioDevueltoDisplayCrear, cambioDevueltoInputCrear, productosJsonCrear, tablaDetalleVentaCrear);
            } else if (context === 'editar') {
                actualizarCalculos(productosAgregadosEditar, editMontoRecibidoInput, montoTotalDisplayEditar, montoTotalInputEditar, editCambioDevueltoDisplay, editCambioDevueltoInput, productosJsonEditar, tablaDetalleVentaEditar);
            }
        }

        // Event listener para los botones de eliminar en la tabla de CREAR
        tablaDetalleVentaCrear.addEventListener('click', function(e) {
            if (e.target.closest('.btn-eliminar-producto')) {
                const id = e.target.closest('.btn-eliminar-producto').dataset.id;
                eliminarProducto(id, productosAgregadosCrear, 'crear');
            }
        });

        // Event listener para los botones de eliminar en la tabla de EDITAR
        tablaDetalleVentaEditar.addEventListener('click', function(e) {
            if (e.target.closest('.btn-eliminar-producto')) {
                const id = e.target.closest('.btn-eliminar-producto').dataset.id;
                eliminarProducto(id, productosAgregadosEditar, 'editar');
            }
        });

        // Event listeners para la búsqueda en CREAR
        pacienteBuscador.addEventListener('input', (e) => buscarPacientes(e.target.value, pacienteResultados, pacienteIdInput, pacienteBuscador));
        productoBuscador.addEventListener('input', (e) => buscarProductos(e.target.value, productoResultados, productoBuscador));
        btnAgregarProducto.addEventListener('click', () => agregarProducto(productoBuscador, cantidadProductoInput, descuentoProductoInput, productosAgregadosCrear, 'crear'));
        montoRecibidoInputCrear.addEventListener('input', () => actualizarCalculos(productosAgregadosCrear, montoRecibidoInputCrear, montoTotalDisplayCrear, montoTotalInputCrear, cambioDevueltoDisplayCrear, cambioDevueltoInputCrear, productosJsonCrear, tablaDetalleVentaCrear));


        // **Función clave para el problema original**
        async function cargarDatosVentaParaEdicion(ventaId) {
            try {
                // Limpiar la lista de productos agregados previamente
                for (const prop in productosAgregadosEditar) {
                    if (productosAgregadosEditar.hasOwnProperty(prop)) {
                        delete productosAgregadosEditar[prop];
                    }
                }

                // Hacer la llamada AJAX para obtener los datos de la venta
                const response = await fetch(`api/obtener_detalles_venta_farmacia.php?id=${ventaId}`);
                const venta = await response.json();

                if (venta.error) {
                    alert('Error: ' + venta.error);
                    return;
                }

                // Rellenar los campos del formulario de edición
                editVentaId.value = venta.id;
                editPacienteIdInput.value = venta.paciente_id;
                editPacienteBuscador.value = venta.paciente_nombre; // Se asume que la API devuelve este campo
                editFecha.value = venta.fecha;
                editMetodoPago.value = venta.metodo_pago;
                editEstadoPago.value = venta.estado_pago;
                editMontoRecibidoInput.value = venta.monto_recibido;
                editSeguroCheck.checked = venta.seguro == 1;

                // Cargar los productos de la venta
                venta.productos.forEach(p => {
                    productosAgregadosEditar[p.producto_id] = {
                        id: p.producto_id,
                        nombre: p.nombre,
                        cantidad: parseInt(p.cantidad),
                        precio: parseFloat(p.precio_unitario),
                        descuento: parseFloat(p.descuento)
                    };
                });

                // Actualizar la tabla y los totales del modal de edición
                actualizarCalculos(productosAgregadosEditar, editMontoRecibidoInput, montoTotalDisplayEditar, montoTotalInputEditar, editCambioDevueltoDisplay, editCambioDevueltoInput, productosJsonEditar, tablaDetalleVentaEditar);

            } catch (error) {
                console.error('Error al cargar datos para edición:', error);
                alert('Hubo un error al cargar los datos de la venta.');
            }
        }
        
        // Event listener para los botones de EDITAR
        btnEditarVenta.forEach(button => {
            button.addEventListener('click', function () {
                const ventaId = this.dataset.id;
                cargarDatosVentaParaEdicion(ventaId);
            });
        });

        // Event listeners para la búsqueda en EDITAR
        editPacienteBuscador.addEventListener('input', (e) => buscarPacientes(e.target.value, editPacienteResultados, editPacienteIdInput, editPacienteBuscador));
        editProductoBuscador.addEventListener('input', (e) => buscarProductos(e.target.value, editProductoResultados, editProductoBuscador));
        btnAgregarProductoEditar.addEventListener('click', () => agregarProducto(editProductoBuscador, editCantidadProductoInput, editDescuentoProductoInput, productosAgregadosEditar, 'editar'));
        editMontoRecibidoInput.addEventListener('input', () => actualizarCalculos(productosAgregadosEditar, editMontoRecibidoInput, montoTotalDisplayEditar, montoTotalInputEditar, editCambioDevueltoDisplay, editCambioDevueltoInput, productosJsonEditar, tablaDetalleVentaEditar));


        // Lógica del modal de VER DETALLES
        btnVerDetalles.forEach(button => {
            button.addEventListener('click', async function () {
                const ventaId = this.dataset.id;
                try {
                    const response = await fetch(`api/obtener_detalles_venta_farmacia.php?id=${ventaId}`);
                    const venta = await response.json();

                    if (venta.error) {
                        alert('Error: ' + venta.error);
                        return;
                    }

                    detalleVentaId.textContent = venta.id;
                    detallePaciente.textContent = venta.paciente_nombre || 'No asignado';
                    detalleUsuario.textContent = venta.usuario_nombre;
                    detalleFecha.textContent = new Date(venta.fecha).toLocaleDateString();
                    detalleMontoTotal.textContent = `${parseFloat(venta.monto_total).toFixed(2)}${currency}`;
                    detalleMetodoPago.textContent = venta.metodo_pago;
                    detalleEstadoPago.textContent = venta.estado_pago;
                    detalleMontoRecibido.textContent = `${parseFloat(venta.monto_recibido).toFixed(2)}${currency}`;
                    detalleCambioDevuelto.textContent = `${parseFloat(venta.cambio_devuelto).toFixed(2)}${currency}`;
                    detalleSeguro.textContent = venta.seguro == 1 ? 'Sí' : 'No';

                    // Limpiar y rellenar la tabla de productos
                    detalleProductosTable.innerHTML = '';
                    venta.productos.forEach(producto => {
                        const row = document.createElement('tr');
                        const subtotal = (parseFloat(producto.precio_unitario) * parseInt(producto.cantidad)) * (1 - (parseFloat(producto.descuento) / 100));
                        row.innerHTML = `
                            <td>${producto.nombre}</td>
                            <td>${producto.cantidad}</td>
                            <td>${parseFloat(producto.precio_unitario).toFixed(2)}${currency}</td>
                            <td>${parseFloat(producto.descuento).toFixed(2)}%</td>
                            <td>${subtotal.toFixed(2)}${currency}</td>
                        `;
                        detalleProductosTable.appendChild(row);
                    });
                } catch (error) {
                    console.error('Error al obtener detalles de la venta:', error);
                    alert('Hubo un error al cargar los detalles de la venta.');
                }
            });
        });
    });
</script>