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
            mostrarAlerta("ID de comprobante no válido.", "danger");
            return;
        }

        try {
            mostrarAlerta("Generando comprobante... Espere un momento.", "info");

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

            mostrarAlerta("Comprobante descargado correctamente.", "success");

        } catch (error) {
            console.error(error);
            mostrarAlerta("No se pudo generar el comprobante.", "danger");
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
        const productosData = <?= json_encode($productos ?? []); ?>;

        console.log(productosData);

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

       


 
    });
</script>