<?php

 
$productos = $pdo->query("SELECT id, nombre, precio_unitario FROM productos ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Utilidades UI
    const money = n => new Intl.NumberFormat('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(n || 0));

    // Toast si hay flash
    <?php if ($flash): ?>
        const t = new bootstrap.Toast(document.getElementById('appToast'), { delay: 3000 });
        t.show();
    <?php endif; ?>

    // Pasar datos a modal Cobrar Consulta
    const modalCC = document.getElementById('modalCobrarConsulta');
    modalCC?.addEventListener('show.bs.modal', ev => {
        const btn = ev.relatedTarget;
        document.getElementById('cc_consulta_id').value = btn?.dataset.id || '';
        document.getElementById('cc_monto').value = btn?.dataset.monto || '';
    });

    // Pasar datos a modal Cobrar Analítica
    const modalCA = document.getElementById('modalCobrarAnalitica');
    modalCA?.addEventListener('show.bs.modal', ev => {
        const id = ev.relatedTarget?.dataset.id || '';
        document.getElementById('ca_analitica_id').value = id;
    });

    // Pasar compra_id a Pago Proveedor
    const modalPP = document.getElementById('modalPagoProveedor');
    modalPP?.addEventListener('show.bs.modal', ev => {
        const id = ev.relatedTarget?.dataset.id || '';
        const nombreProveedor = ev.relatedTarget?.dataset.nombreproveedor || '';
        const montoPendiente = (ev.relatedTarget?.dataset.montopendiente || '').replace(',', '.');
        const factura = ev.relatedTarget?.dataset.factura || '';

        console.log(factura);
        document.getElementById('pp_compra_id').value = id;
        document.getElementById('nombreProveedor').value = nombreProveedor;
        document.getElementById('montoPendiente').value = montoPendiente;
        document.getElementById('factura').innerText = factura;
    });

    // ------------------- Construcción dinámica de items (Venta/Compra) -------------------
    function addRow(tableId) {
        const table = document.getElementById(tableId || 'tablaVentaItems');
        const tr = table.tBodies[0].rows[0].cloneNode(true);
        tr.querySelectorAll('input').forEach(i => { i.value = i.classList.contains('cantidad') ? 1 : '' });
        tr.querySelector('.subtotal').textContent = 'XAF 0,00';
        table.tBodies[0].appendChild(tr);
    }
    function removeRow(btn) {
        const tr = btn.closest('tr');
        const tbody = tr.parentElement;
        if (tbody.rows.length > 1) tr.remove();
        updateTotals();
    }

    // Auto-set precio cuando eliges producto
    document.addEventListener('change', (e) => {
        if (e.target.matches('.prod-select')) {
            const opt = e.target.selectedOptions[0];
            const precio = opt?.dataset.precio || 0;
            const tr = e.target.closest('tr');
            tr.querySelector('.precio').value = precio;
            updateTotals();
        }
        if (e.target.matches('.cantidad, .precio')) updateTotals();
    });

    function updateTotals() {
        // Ventas
        const tv = document.getElementById('tablaVentaItems');
        if (tv) {
            let total = 0;
            tv.tBodies[0].querySelectorAll('tr').forEach(tr => {
                const cant = Number(tr.querySelector('.cantidad')?.value || 0);
                const precio = Number(tr.querySelector('.precio')?.value || 0);
                const sub = cant * precio;
                tr.querySelector('.subtotal').textContent = 'XAF ' + money(sub);
                total += sub;
            });
            document.getElementById('ventaTotal').textContent = 'XAF ' + money(total);
        }
        // Compras
        const tc = document.getElementById('tablaCompraItems');
        if (tc) {
            let total = 0;
            tc.tBodies[0].querySelectorAll('tr').forEach(tr => {
                const cant = Number(tr.querySelector('.cantidad')?.value || 0);
                const precio = Number(tr.querySelector('.precio')?.value || 0);
                const sub = cant * precio;
                tr.querySelector('.subtotal').textContent = 'XAF ' + money(sub);
                total += sub;
            });
            document.getElementById('compraTotal').textContent = 'XAF ' + money(total);
        }
    }

    function buildVentaItemsJson() {
        const rows = document.querySelectorAll('#tablaVentaItems tbody tr');
        const items = [];
        for (const tr of rows) {
            const prod = tr.querySelector('.prod-select')?.value;
            const cant = Number(tr.querySelector('.cantidad')?.value || 0);
            const precio = Number(tr.querySelector('.precio')?.value || 0);
            if (!prod || cant <= 0 || precio <= 0) {
                alert('Verifica producto, cantidad y precio en todos los renglones');
                return false;
            }
            items.push({ producto_id: Number(prod), cantidad: cant, precio: precio });
        }
        document.getElementById('venta_items_json').value = JSON.stringify(items);
        return true;
    }

    function buildCompraItemsJson() {
        const rows = document.querySelectorAll('#tablaCompraItems tbody tr');
        const items = [];
        for (const tr of rows) {
            const prod = tr.querySelector('.prod-select')?.value;
            const cant = Number(tr.querySelector('.cantidad')?.value || 0);
            const precio = Number(tr.querySelector('.precio')?.value || 0);
            if (!prod || cant <= 0 || precio <= 0) {
                alert('Verifica producto, cantidad y precio en todos los renglones');
                return false;
            }
            items.push({ producto_id: Number(prod), cantidad: cant, precio_compra: precio });
        }
        document.getElementById('compra_items_json').value = JSON.stringify(items);
        return true;
    }

    // Búsquedas rápidas (sólo cliente)
    document.getElementById('searchConsultas')?.addEventListener('input', function () {
        const val = this.value.trim();
        document.querySelectorAll('#tbodyConsultas tr').forEach(tr => {
            const pac = tr.children[1]?.textContent || '';
            tr.style.display = pac.includes(val) ? '' : 'none';
        });
    });
    document.getElementById('searchAnaliticas')?.addEventListener('input', function () {
        const val = this.value.trim();
        document.querySelectorAll('#tbodyAnaliticas tr').forEach(tr => {
            const pac = tr.children[1]?.textContent || '';
            tr.style.display = pac.includes(val) ? '' : 'none';
        });
    });

    //FUNCION PARA LLAMAR ALERTA DE IMPRIMIR Compra

    async function imprimirComprobante(id) {
        if (!id || isNaN(id)) {
            console.log("ID de comprobante no válido.", "danger");
            return;
        }

        try {
            console.log("Generando comprobante... Espere un momento.", "info");

            // Petición al backend
            const response = await fetch("fpdf/imprimirCompra.php?id=" + id);

            if (!response.ok) {
                throw new Error("Error del servidor: " + response.status);
            }

            // Convertimos a PDF (blob)
            const blob = await response.blob();

            // Creamos URL temporal con el PDF
            const pdfUrl = URL.createObjectURL(blob);

            // Creamos enlace invisible
            const a = document.createElement("a");
            a.href = pdfUrl;
            a.download = `Comprobante_${id}.pdf`;
            document.body.appendChild(a);
            a.click();
            a.remove();

            // Liberamos memoria
            URL.revokeObjectURL(pdfUrl);

            console.log("Comprobante descargado correctamente.", "success");

        } catch (error) {
            console.error(error);
            console.log("No se pudo generar el comprobante.", "danger");
        }
    }





</script>



<!-- 
// EL SIGUIENTE CODIGO CALCULA LOS DÍAS TRANSCURIDOS DESDE 
UNA FECHA X DE LA BASE DE DATOS A LA FECHA DEL SISTEMA

 -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const modal = document.getElementById("modalPagoProveedor");

        modal.addEventListener("show.bs.modal", function (event) {

            const boton = event.relatedTarget;
            const fechaCompra = boton.getAttribute("data-fechaCompra");
            console.log(fechaCompra)
            // Mostrar fecha en el input
            document.getElementById("fechaCompra").value = fechaCompra;

            // Calcular retraso
            const hoy = new Date();
            const fecha = new Date(fechaCompra);

            const diffMs = hoy - fecha;

            if (isNaN(diffMs)) {
                document.getElementById("tiempoRetraso").value = "Fecha inválida";
                return;
            }

            const dias = Math.floor(diffMs / (1000 * 60 * 60 * 24));
            const meses = Math.floor(dias / 30);
            const años = Math.floor(meses / 12);

            let textoRetraso = "";

            if (años > 0) textoRetraso += `${años} año(s) `;
            if (meses % 12 > 0) textoRetraso += `${meses % 12} mes(es) `;
            textoRetraso += `${dias % 30} día(s)`;

            document.getElementById("tiempoRetraso").value = textoRetraso.trim();
        });
    });
</script>


<!-- Scripts que regula el comportamiento del modal de registro de una compra a un proveedor -->
<script>

document.addEventListener('DOMContentLoaded', function () {
        // --- Lógica de los Modales de Registro y Actualización ---

        // Función para recalcular los totales de compra, venta y beneficio por producto
        function recalcularProductoTotales(item) {
            const cantidad = parseFloat(item.querySelector('.producto-cantidad').value) || 0;
            const precioCompra = parseFloat(item.querySelector('.producto-precio').value) || 0;
            const precioVenta = parseFloat(item.querySelector('.producto-precio-venta').value) || 0;

            const totalCompra = cantidad * precioCompra;
            const totalVenta = cantidad * precioVenta;
            const beneficio = totalVenta - totalCompra;

            item.querySelector('.total-compra').textContent = `XAF${totalCompra.toFixed(2)}`;
            item.querySelector('.total-venta').textContent = `XAF${totalVenta.toFixed(2)}`;
            item.querySelector('.beneficio').textContent = `XAF${beneficio.toFixed(2)}`;
        }

        // Función para recalcular el total de la compra y la diferencia de pago
        function recalcularTotalCompra(modalId) {
            const container = document.getElementById(`productos-container-${modalId}`);
            let totalGeneralCompra = 0;
            const items = container.querySelectorAll('.producto-item');

            items.forEach(item => {
                const cantidadInput = item.querySelector('.producto-cantidad');
                const precioInput = item.querySelector('.producto-precio');

                const cantidad = parseFloat(cantidadInput.value) || 0;
                const precio = parseFloat(precioInput.value) || 0;
                totalGeneralCompra += cantidad * precio;
            });

            const totalSpan = document.getElementById(`total_${modalId}`);
            totalSpan.textContent = `XAF${totalGeneralCompra.toFixed(2)}`;

            const montoEntregadoInput = document.getElementById(`monto_entregado_${modalId}`);
            const cambioPendienteSpan = document.getElementById(`cambio_pendiente_${modalId}`);

            // Habilita/Deshabilita el campo de monto entregado
            const estadoPagoSelect = document.getElementById(`estado_pago_${modalId}`);
            const estado = estadoPagoSelect.value;

            if (estado === "PENDIENTE") {
                montoEntregadoInput.value = '';
                montoEntregadoInput.disabled = true;
            } else {
                montoEntregadoInput.disabled = false;
            }

            const montoEntregado = parseFloat(montoEntregadoInput.value) || 0;
            let cambioPendiente = montoEntregado - totalGeneralCompra;

            // Alternar color y símbolo
            if (cambioPendiente >= 0) {
                cambioPendienteSpan.style.color = 'blue';
                cambioPendienteSpan.textContent = `+XAF${cambioPendiente.toFixed(2)}`;
            } else {
                cambioPendienteSpan.style.color = 'red';
                cambioPendienteSpan.textContent = `-XAF${Math.abs(cambioPendiente).toFixed(2)}`;
            }
        }

        // Función para agregar una fila de producto
        function agregarFilaProducto(containerId, data = {}) {
            const container = document.getElementById(containerId);
            // Asegúrate de que los datos de productos estén disponibles
            const productosData = <?= json_encode($productos); ?>;
            const newIndex = container.children.length;

            // Buscar el precio unitario del producto si se proporciona un ID
            let precioUnitarioProducto = null;
            if (data.producto_id) {
                const producto = productosData.find(p => p.id == data.producto_id);
                if (producto && producto.precio_unitario !== null) {
                    precioUnitarioProducto = producto.precio_unitario;
                }
            }

            const newItemHtml = `
                <div class="row g-3 mb-2 producto-item border-bottom pb-2">
                    <div class="col-md-3">
                        <label class="form-label">Producto</label>
                        <select class="form-select producto-select" name="productos[${newIndex}][id]" required>
                            <option value="" disabled selected>Seleccione un producto</option>
                            <?php foreach ($productos as $prod): ?>
                                <option value="<?= htmlspecialchars($prod['id']) ?>"><?= htmlspecialchars($prod['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cantidad</label>
                        <input type="number" class="form-control producto-cantidad" name="productos[${newIndex}][cantidad]" min="1" value="${data.cantidad || ''}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio Compra</label>
                        <input type="number" class="form-control producto-precio" name="productos[${newIndex}][precio]" step="0.01" value="${data.precio_compra || ''}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Precio Venta</label>
                        <input type="number" class="form-control producto-precio-venta" name="productos[${newIndex}][precio_venta]" step="0.01" value="${data.precio_venta || (precioUnitarioProducto !== null ? precioUnitarioProducto : '')}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Detalles</label>
                        <p class="form-control-plaintext mb-0">
                            <strong class="text-muted">Compra:</strong> <span class="total-compra">XAF 0.00</span><br>
                            <strong class="text-success">Venta:</strong> <span class="total-venta">XAF 0.00</span><br>
                            <strong class="text-primary">Beneficio:</strong> <span class="beneficio">XAF 0.00</span>
                        </p>
                    </div>
                    <div class="col-md-12 text-end">
                        <button type="button" class="btn btn-danger btn-sm remove-producto"><i class="bi bi-x-circle"></i> Eliminar</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newItemHtml);

            const newElement = container.lastElementChild;
            // Después de agregar el elemento, establecer el valor seleccionado y recalcular
            if (data.producto_id) {
                const selectElement = newElement.querySelector('.producto-select');
                selectElement.value = data.producto_id;
            }
            recalcularProductoTotales(newElement);
        }

        // Helper para buscar el precio unitario de un producto
        function getPrecioUnitarioById(productId, productosData) {
            const producto = productosData.find(p => p.id == productId);
            return producto ? producto.precio_unitario : null;
        }

        // Lógica para el modal de Creación
        const modalCrearCompra = document.getElementById('modalCrearCompra');
        const productosContainerCrear = document.getElementById('productos-container-crear');
        const addProductoCrearBtn = document.getElementById('add-producto-crear');
        const checkFacturaCrear = document.getElementById('checkFacturaCrear');
        const wrapperFacturaCrear = document.getElementById('wrapperFacturaCrear');
        const productosData = <?= json_encode($productos); ?>;

        // Listener para agregar productos
        addProductoCrearBtn.addEventListener('click', () => agregarFilaProducto('productos-container-crear'));

        // Manejar el toggle del campo de factura en el modal de creación
        checkFacturaCrear.addEventListener('change', function () {
            wrapperFacturaCrear.style.display = this.checked ? 'block' : 'none';
        });

        // Event listener para los cambios en el contenedor de productos (delegación de eventos)
        productosContainerCrear.addEventListener('change', (e) => {
            if (e.target.matches('.producto-select')) {
                const selectedProductId = e.target.value;
                const item = e.target.closest('.producto-item');
                const precioVentaInput = item.querySelector('.producto-precio-venta');
                const precioUnitario = getPrecioUnitarioById(selectedProductId, productosData);

                if (precioUnitario !== null) {
                    precioVentaInput.value = precioUnitario;
                } else {
                    precioVentaInput.value = '';
                }

                recalcularProductoTotales(item);
                recalcularTotalCompra('crear');
            }
        });

        productosContainerCrear.addEventListener('input', (e) => {
            if (e.target.matches('.producto-cantidad') || e.target.matches('.producto-precio') || e.target.matches('.producto-precio-venta')) {
                const item = e.target.closest('.producto-item');
                recalcularProductoTotales(item);
                recalcularTotalCompra('crear');
            }
        });

        productosContainerCrear.addEventListener('click', (e) => {
            if (e.target.matches('.remove-producto') || e.target.closest('.remove-producto')) {
                e.target.closest('.producto-item').remove();
                recalcularTotalCompra('crear');
            }
        });

        // Listener para el campo de monto entregado y estado de pago en el modal de Creación
        document.getElementById('monto_entregado_crear').addEventListener('input', () => recalcularTotalCompra('crear'));
        document.getElementById('estado_pago_crear').addEventListener('change', () => recalcularTotalCompra('crear'));

        // Al abrir el modal, asegurar el cálculo inicial
        modalCrearCompra.addEventListener('show.bs.modal', function () {
            recalcularTotalCompra('crear');
            checkFacturaCrear.checked = false;
            wrapperFacturaCrear.style.display = 'none';
        });

        // Lógica para el modal de Actualización
        const modalActualizarCompra = document.getElementById('modalActualizarCompra');
        const productosContainerActualizar = document.getElementById('productos-container-actualizar');
        const addProductoActualizarBtn = document.getElementById('add-producto-actualizar');
        const checkFacturaActualizar = document.getElementById('checkFacturaActualizar');
        const wrapperFacturaActualizar = document.getElementById('wrapperFacturaActualizar');

        // Listener para agregar productos en el modal de actualización
        addProductoActualizarBtn.addEventListener('click', () => agregarFilaProducto('productos-container-actualizar'));

        // Manejar el toggle del campo de factura en el modal de actualización
        checkFacturaActualizar.addEventListener('change', function () {
            wrapperFacturaActualizar.style.display = this.checked ? 'block' : 'none';
        });

        // Event listener para los cambios en el contenedor de productos (delegación de eventos)
        productosContainerActualizar.addEventListener('change', (e) => {
            if (e.target.matches('.producto-select')) {
                const selectedProductId = e.target.value;
                const item = e.target.closest('.producto-item');
                const precioVentaInput = item.querySelector('.producto-precio-venta');
                const precioUnitario = getPrecioUnitarioById(selectedProductId, productosData);

                if (precioUnitario !== null) {
                    precioVentaInput.value = precioUnitario;
                } else {
                    precioVentaInput.value = '';
                }

                recalcularProductoTotales(item);
                recalcularTotalCompra('actualizar');
            }
        });

        productosContainerActualizar.addEventListener('input', (e) => {
            if (e.target.matches('.producto-cantidad') || e.target.matches('.producto-precio') || e.target.matches('.producto-precio-venta')) {
                const item = e.target.closest('.producto-item');
                recalcularProductoTotales(item);
                recalcularTotalCompra('actualizar');
            }
        });

        productosContainerActualizar.addEventListener('click', (e) => {
            if (e.target.matches('.remove-producto') || e.target.closest('.remove-producto')) {
                e.target.closest('.producto-item').remove();
                recalcularTotalCompra('actualizar');
            }
        });

        // Listener para el campo de monto entregado y estado de pago en el modal de Actualización
        document.getElementById('monto_entregado_actualizar').addEventListener('input', () => recalcularTotalCompra('actualizar'));
        document.getElementById('estado_pago_actualizar').addEventListener('change', () => recalcularTotalCompra('actualizar'));

        // Al mostrar el modal de actualización, cargar los datos y recalcular
        modalActualizarCompra.addEventListener('show.bs.modal', function (event) {
            // Lógica para llenar el modal de actualización de compra
            const btn = event.relatedTarget;
            const id = btn.getAttribute('data-id');
            const codigoFactura = btn.getAttribute('data-codigo-factura');
            const proveedorId = btn.getAttribute('data-proveedor-id');
            const personalId = btn.getAttribute('data-personal-id');
            const fecha = btn.getAttribute('data-fecha');
            const estadoPago = btn.getAttribute('data-estado-pago');
            const montoEntregado = btn.getAttribute('data-monto-entregado');

            // Llenar los campos del formulario de actualización
            document.getElementById('compra_id_actualizar').value = id;
            document.getElementById('proveedor_actualizar').value = proveedorId;
            document.getElementById('personal_actualizar').value = personalId;
            document.getElementById('fecha_actualizar').value = fecha;
            document.getElementById('estado_pago_actualizar').value = estadoPago;
            document.getElementById('monto_entregado_actualizar').value = parseFloat(montoEntregado).toFixed(2);

            // Lógica para el campo de factura
            const hasFactura = codigoFactura && codigoFactura !== 'NULL' && codigoFactura !== '';
            checkFacturaActualizar.checked = hasFactura;
            wrapperFacturaActualizar.style.display = hasFactura ? 'block' : 'none';
            document.getElementById('codigo_factura_actualizar').value = hasFactura ? codigoFactura : '';

            // Cargar los productos de la compra
            const detalles = <?= json_encode($comprasDetalle); ?>;
            productosContainerActualizar.innerHTML = '';

            if (detalles[id]) {
                detalles[id].forEach((detalle) => {
                    agregarFilaProducto('productos-container-actualizar', detalle);
                });
            }
            // Recalcular los totales después de cargar los datos
            recalcularTotalCompra('actualizar');
        });

        // --- Lógica del Buscador y Mensajes de Alerta (sin cambios) ---

        // Función para manejar la visibilidad del campo de factura
        function toggleFacturaVisibility(checkboxId, wrapperId, inputId) {
            const checkbox = document.getElementById(checkboxId);
            const wrapper = document.getElementById(wrapperId);
            const input = document.getElementById(inputId);

            if (checkbox && wrapper && input) {
                checkbox.addEventListener('change', function () {
                    if (this.checked) {
                        wrapper.style.display = 'block';
                    } else {
                        wrapper.style.display = 'none';
                    }
                });
            }
        }

        // Inicializar la lógica para el campo de factura en el modal de detalles
        toggleFacturaVisibility('checkFacturaDetalle', 'wrapperFacturaDetalle', 'detalle-codigo-factura');

        // Lógica para llenar el modal de detalles de compra
        const botonesVerDetalles = document.querySelectorAll('.btn-ver-detalles');
        botonesVerDetalles.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const codigoFactura = this.getAttribute('data-codigo-factura');
                const proveedor = this.getAttribute('data-proveedor');
                const personal = this.getAttribute('data-personal');
                const fecha = this.getAttribute('data-fecha');
                const total = this.getAttribute('data-total');
                const estadoPago = this.getAttribute('data-estado-pago');
                const montoEntregado = this.getAttribute('data-monto-entregado');
                const montoGastado = this.getAttribute('data-monto-gastado');
                const cambioDevuelto = this.getAttribute('data-cambio-devuelto');
                const montoPendiente = this.getAttribute('data-monto-pendiente');

                document.getElementById('detalle-id').textContent = id;
                document.getElementById('detalle-proveedor').textContent = proveedor;
                document.getElementById('detalle-personal').textContent = personal;
                document.getElementById('detalle-fecha').textContent = fecha;
                document.getElementById('detalle-total').textContent = `XAF${parseFloat(total).toFixed(2)}`;
                document.getElementById('detalle-estado-pago').textContent = estadoPago;

                document.getElementById('detalle-monto-entregado').textContent = `XAF${parseFloat(montoEntregado).toFixed(2)}`;
                document.getElementById('detalle-monto-gastado').textContent = `XAF${parseFloat(montoGastado).toFixed(2)}`;
                document.getElementById('detalle-cambio-devuelto').textContent = `XAF${parseFloat(cambioDevuelto).toFixed(2)}`;
                document.getElementById('detalle-monto-pendiente').textContent = `XAF${parseFloat(montoPendiente).toFixed(2)}`;

                const checkboxFactura = document.getElementById('checkFacturaDetalle');
                const wrapperFactura = document.getElementById('wrapperFacturaDetalle');
                const inputFactura = document.getElementById('detalle-codigo-factura');
                if (codigoFactura === 'NULL' || codigoFactura === '') {
                    checkboxFactura.checked = false;
                    wrapperFactura.style.display = 'none';
                    inputFactura.value = '';
                } else {
                    checkboxFactura.checked = true;
                    wrapperFactura.style.display = 'block';
                    inputFactura.value = codigoFactura;
                }

                const detalles = <?= json_encode($comprasDetalle); ?>;
                const tablaBody = document.querySelector('#detalles-compra-tabla tbody');
                tablaBody.innerHTML = '';
                const productosData = <?= json_encode($productos); ?>;
                if (detalles[id]) {
                    detalles[id].forEach(detalle => {
                        const productoNombre = productosData.find(p => p.id === detalle.producto_id)?.nombre || 'Producto Desconocido';
                        const precioUnitario = getPrecioUnitarioById(detalle.producto_id, productosData);

                        // Usar el precio de venta de los detalles o el precio unitario del producto si no está en los detalles
                        const precioVenta = detalle.precio_venta || (precioUnitario !== null ? precioUnitario : 'N/A');

                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                            <td>${productoNombre}</td>
                            <td>${detalle.cantidad}</td>
                            <td>XAF${parseFloat(detalle.precio_compra).toFixed(2)}</td>
                            <td>XAF${parseFloat(precioVenta).toFixed(2)}</td>
                        `;
                        tablaBody.appendChild(fila);
                    });
                }
            });
        });

        const buscador = document.getElementById('buscador');
        const tabla = document.getElementById('tablaCompras');
        const filas = tabla.getElementsByTagName('tr');

        buscador.addEventListener('keyup', function () {
            const filtro = buscador.value.toLowerCase();
            for (let i = 1; i < filas.length; i++) {
                const fila = filas[i];
                const textoFila = fila.textContent.toLowerCase();
                if (textoFila.indexOf(filtro) > -1) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            }
        });

        setTimeout(() => {
            const mensaje = document.getElementById('mensaje');
            if (mensaje) {
                mensaje.style.transition = 'opacity 1s ease';
                mensaje.style.opacity = '0';
                setTimeout(() => mensaje.remove(), 1000);
            }
        }, 10000); // 10 segundos
    });

</script>

<!-- Scripts que regula el comportamiento del modal ver detalles compra-->
<script>
    async function verDetallesCompra(id) {
        if (!id) return;

        const modal = new bootstrap.Modal(document.getElementById("modalVerDetalles"));
        modal.show();

        const spinner = document.getElementById("spinnerDetalles");
        const tbodyProductos = document.getElementById("tablaDetalleProductos");
        const tbodyPagos = document.getElementById("tablaHistorialPagos");

        spinner.classList.remove("d-none");
        tbodyProductos.innerHTML = `<tr><td colspan="4" class="text-center text-muted small">Cargando...</td></tr>`;
        tbodyPagos.innerHTML = `<tr><td colspan="4" class="text-center text-muted small">Cargando...</td></tr>`;

        try {
            const res = await fetch(`api/getDetallesCompra.php?id=${id}`);
            const data = await res.json();
            spinner.classList.add("d-none");

            if (!data.success) {
                tbodyProductos.innerHTML = `<tr><td colspan="4" class="text-center text-danger small">${data.error || "Error"}</td></tr>`;
                tbodyPagos.innerHTML = tbodyProductos.innerHTML;
                return;
            }

            const c = data.compra;
            const detalle = data.detalle;
            const pagos = data.pagos;

            // Información General
            document.getElementById("detalleFactura").textContent = c.codigo_factura || "Sin factura";
            document.getElementById("detalleFecha").textContent = c.fecha;
            document.getElementById("detalleEstado").innerHTML =
                c.estado_pago === "PAGADO" ? `<span class="badge bg-success">Pagado</span>` :
                    c.estado_pago === "PARCIAL" ? `<span class="badge bg-warning text-dark">Parcial</span>` :
                        `<span class="badge bg-danger">Pendiente</span>`;
            document.getElementById("detalleTotal").textContent = money(c.total);
            document.getElementById("detallePagado").textContent = money(c.monto_gastado);
            document.getElementById("detallePendiente").textContent = money(c.monto_pendiente);

            // Proveedor
            document.getElementById("provNombre").textContent = c.proveedor_nombre || "-";
            document.getElementById("provTelefono").textContent = c.proveedor_telefono || "-";
            document.getElementById("provDireccion").textContent = c.proveedor_direccion || "-";

            // Personal
            document.getElementById("personalNombre").textContent = `${c.personal_nombre || ''} ${c.personal_apellidos || ''}`;

            // Detalle Productos
            tbodyProductos.innerHTML = detalle.length ? detalle.map(i => `
            <tr>
                <td>${sanitize(i.producto_nombre)}</td>
                <td class="text-center">${sanitize(i.cantidad)}</td>
                <td class="text-end">${money(i.precio_compra)}</td>
                <td class="text-end">${money(i.precio_compra * i.cantidad)}</td>
            </tr>`).join('') :
                `<tr><td colspan="4" class="text-center text-muted small">No hay productos</td></tr>`;

            // Historial Pagos
            tbodyPagos.innerHTML = pagos.length ? pagos.map(p => `
            <tr>
                <td>${p.fecha}</td>
                <td class="text-end">${money(p.monto)}</td>
                <td>${p.personal_nombre || ''} ${p.personal_apellidos || ''}</td>
                <td>${p.metodo_pago || '-'}</td>
            </tr>`).join('') :
                `<tr><td colspan="4" class="text-center text-muted small">No hay pagos</td></tr>`;

        } catch (err) {
            console.error(err);
            spinner.classList.add("d-none");
            tbodyProductos.innerHTML = `<tr><td colspan="4" class="text-center text-danger small">Error al cargar datos</td></tr>`;
            tbodyPagos.innerHTML = tbodyProductos.innerHTML;
        }
    }

    function sanitize(str) {
        if (!str) return '';
        return str.toString().replace(/[&<>"'`]/g, s => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '`': '&#96;'
        }[s]));
    }

    /* function money(num) {
        const n = parseFloat(num);
        if (isNaN(n)) return "0 CFA";
        return n.toLocaleString("es-ES", {minimumFractionDigits:2, maximumFractionDigits:2}) + " CFA";
    } */


</script>