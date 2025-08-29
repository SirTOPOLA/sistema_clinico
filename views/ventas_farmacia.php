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
                u.nombre_usuario AS nombre_usuario,
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

    <!-- Encabezado y botón para registrar venta -->
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
    
    <!-- Mensajes de estado (PHP) -->
    <?php
    if (isset($_SESSION['error'])) {
        echo '<div id="mensaje" class="alert alert-danger">'.$_SESSION['error'].'</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo '<div id="mensaje" class="alert alert-success">'.$_SESSION['success'].'</div>';
        unset($_SESSION['success']);
    }
    ?>

    <!-- Tabla de ventas -->
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
                            <td class="monto-total-venta"><?= '$' . number_format($venta['monto_total'], 2) ?></td>
                            <td class="estado-pago"><?= htmlspecialchars($venta['estado_pago']) ?></td>
                            <td class="metodo-pago"><?= htmlspecialchars($venta['metodo_pago']) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-info btn-ver-detalles-venta"
                                    data-id="<?= $venta['id'] ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDetallesVenta">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary btn-editar-venta"
                                    data-id="<?= $venta['id'] ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarVenta">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="eliminar_venta.php?id=<?= $venta['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar esta venta?')">
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

<!-- Modal para Registrar Venta -->
<div class="modal fade" id="modalCrearVenta" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="procesos/guardar_venta.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Registrar Nueva Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id_usuario" value="<?= $idUsuario ?>">
                <div class="col-md-6">
                    <label class="form-label">Paciente</label>
                    <input type="text" id="paciente-buscador" class="form-control" placeholder="Buscar por nombre o ID...">
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
                        <input type="number" id="cantidad-producto" class="form-control" placeholder="Cantidad" min="1" value="1">
                        <button class="btn btn-outline-secondary" type="button" id="btn-agregar-producto">Agregar</button>
                    </div>
                    <div id="producto-resultados" class="list-group"></div>
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-sm" id="tabla-detalle-venta">
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
                                <!-- Filas de productos se agregarán aquí -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2"><span id="monto-total-display">$0.00</span></td>
                                    <input type="hidden" name="monto_total" id="monto_total_input">
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
                    <input type="number" name="monto_recibido" id="monto_recibido_input" class="form-control" step="0.01" value="0" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cambio Devuelto</label>
                    <input type="text" id="cambio_devuelto_display" class="form-control" value="$0.00" readonly>
                    <input type="hidden" name="cambio_devuelto" id="cambio_devuelto_input">
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

<!-- Modal de edición y detalles (placeholder) -->
<!-- Aquí se agregarán los modales para editar y ver detalles de la venta -->

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

    document.addEventListener('DOMContentLoaded', function() {
        const pacienteBuscador = document.getElementById('paciente-buscador');
        const pacienteIdInput = document.getElementById('paciente_id_input');
        const pacienteResultados = document.getElementById('paciente-resultados');
        const productoBuscador = document.getElementById('producto-buscador');
        const cantidadProductoInput = document.getElementById('cantidad-producto');
        const productoResultados = document.getElementById('producto-resultados');
        const btnAgregarProducto = document.getElementById('btn-agregar-producto');
        const tablaDetalleVenta = document.getElementById('tabla-detalle-venta');
        const montoTotalDisplay = document.getElementById('monto-total-display');
        const montoTotalInput = document.getElementById('monto_total_input');
        const montoRecibidoInput = document.getElementById('monto_recibido_input');
        const cambioDevueltoDisplay = document.getElementById('cambio_devuelto_display');
        const cambioDevueltoInput = document.getElementById('cambio_devuelto_input');
        
        const productosAgregados = {};

        // Función para actualizar el total de la venta
        function actualizarTotal() {
            let total = 0;
            for (const id in productosAgregados) {
                total += productosAgregados[id].cantidad * productosAgregados[id].precio;
            }
            montoTotalDisplay.textContent = `$${total.toFixed(2)}`;
            montoTotalInput.value = total.toFixed(2);
            
            // Recalcular el cambio
            actualizarCambio();
        }

        // Función para actualizar el cambio devuelto
        function actualizarCambio() {
            const montoRecibido = parseFloat(montoRecibidoInput.value) || 0;
            const montoTotal = parseFloat(montoTotalInput.value) || 0;
            const cambio = montoRecibido - montoTotal;
            cambioDevueltoDisplay.textContent = `$${cambio.toFixed(2)}`;
            cambioDevueltoInput.value = cambio.toFixed(2);
        }

        // Búsqueda de pacientes
        pacienteBuscador.addEventListener('input', async function() {
            const query = this.value;
            pacienteResultados.innerHTML = '';
            if (query.length < 2) return;

            try {
                const response = await fetch(`api/get_pacientes.php?q=${query}`);
                const pacientes = await response.json();

                if (pacientes.length > 0) {
                    pacientes.forEach(paciente => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = `${paciente.nombre} (${paciente.cedula})`;
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            pacienteBuscador.value = paciente.nombre;
                            pacienteIdInput.value = paciente.id;
                            pacienteResultados.innerHTML = '';
                        });
                        pacienteResultados.appendChild(item);
                    });
                } else {
                    pacienteResultados.innerHTML = '<div class="p-2">No se encontraron pacientes.</div>';
                }
            } catch (error) {
                console.error('Error al buscar pacientes:', error);
            }
        });

        // Búsqueda de productos
        productoBuscador.addEventListener('input', async function() {
            const query = this.value;
            productoResultados.innerHTML = '';
            if (query.length < 2) return;

            try {
                const response = await fetch(`api/get_productos.php?q=${query}`);
                const productos = await response.json();

                if (productos.length > 0) {
                    productos.forEach(producto => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action';
                        item.dataset.id = producto.id;
                        item.dataset.nombre = producto.nombre;
                        item.dataset.precio = producto.precio_venta;
                        item.textContent = `${producto.nombre} - $${producto.precio_venta}`;
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            productoBuscador.value = producto.nombre;
                            productoBuscador.dataset.id = producto.id;
                            productoBuscador.dataset.precio = producto.precio_venta;
                            productoResultados.innerHTML = '';
                        });
                        productoResultados.appendChild(item);
                    });
                } else {
                    productoResultados.innerHTML = '<div class="p-2">No se encontraron productos.</div>';
                }
            } catch (error) {
                console.error('Error al buscar productos:', error);
            }
        });

        // Agregar producto a la tabla
        btnAgregarProducto.addEventListener('click', function() {
            const productoId = productoBuscador.dataset.id;
            const productoNombre = productoBuscador.value;
            const cantidad = parseInt(cantidadProductoInput.value, 10);
            const precio = parseFloat(productoBuscador.dataset.precio);

            if (!productoId || !productoNombre || isNaN(cantidad) || cantidad <= 0 || isNaN(precio)) {
                alert('Por favor, seleccione un producto y una cantidad válida.');
                return;
            }

            // Verificar si el producto ya está en la lista
            if (productosAgregados[productoId]) {
                productosAgregados[productoId].cantidad += cantidad;
            } else {
                productosAgregados[productoId] = {
                    nombre: productoNombre,
                    cantidad: cantidad,
                    precio: precio
                };
            }

            // Limpiar campos de búsqueda
            productoBuscador.value = '';
            cantidadProductoInput.value = '1';
            productoBuscador.dataset.id = '';
            productoBuscador.dataset.precio = '';

            // Actualizar la tabla
            const tbody = tablaDetalleVenta.querySelector('tbody');
            tbody.innerHTML = '';
            for (const id in productosAgregados) {
                const producto = productosAgregados[id];
                const subtotal = producto.cantidad * producto.precio;
                const row = `
                    <tr>
                        <td>${producto.nombre}</td>
                        <td>${producto.cantidad}</td>
                        <td>$${producto.precio.toFixed(2)}</td>
                        <td>$0.00</td>
                        <td>$${subtotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-remover-producto" data-id="${id}">
                                <i class="bi bi-x-circle"></i>
                            </button>
                            <input type="hidden" name="productos[${id}][id]" value="${id}">
                            <input type="hidden" name="productos[${id}][cantidad]" value="${producto.cantidad}">
                            <input type="hidden" name="productos[${id}][precio]" value="${producto.precio}">
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            }

            // Añadir evento a los botones de remover
            document.querySelectorAll('.btn-remover-producto').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    delete productosAgregados[id];
                    this.closest('tr').remove();
                    actualizarTotal();
                });
            });

            actualizarTotal();
        });

        // Evento para recalcular el cambio al cambiar el monto recibido
        montoRecibidoInput.addEventListener('input', actualizarCambio);

    });
</script>
