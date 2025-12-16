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
        const tipoPagoSelect = document.getElementById('tipoPagoConsulta');
        const contenedorSeguro = document.getElementById('contenedorSeguroConsulta');
        const contenedorMontoAPagar = document.getElementById('contenedorMontoAPagarConsulta');
        const contenedorMontoPendiente = document.getElementById('contenedorMontoPendienteConsulta');
        const montoPagarInput = document.getElementById('montoPagarConsulta');
        const montoPendienteSpan = document.getElementById('montoPendienteConsulta');
        const totalPagoSpan = document.getElementById('totalPagoConsulta');
        const btn = ev.relatedTarget;

        document.getElementById('idPacientePagoConsulta').value = btn?.dataset.id || '';
        document.getElementById('nombrePacientePagoConsulta').innerText = btn?.dataset.paciente || '';
        document.getElementById('fechaPacientePagoConsulta').innerText = btn?.dataset.fecha || '';
        totalPagoSpan.innerText = `${btn?.dataset.monto} XAF` || '';
        console.log('paciente: ' + btn?.dataset.paciente || '')
        const idPaciente = btn?.dataset.id || '';


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

 
            fetch('api/verificar_seguro.php?paciente_id=' + idPaciente)
                    .then(response => response.json())
                    .then(data => {
                       
                        console.log(`Tiene: ${ data.existeSeguro}`)
                        if ( data.existeSeguro) {
                            const seguroOption = document.createElement('option');
                            seguroOption.value = 'SEGURO';
                            seguroOption.textContent = '🛡️ Seguro';
                            tipoPagoSelect.appendChild(seguroOption);
                        }
                    })
  

        // Función auxiliar para cargar los seguros de un paciente  
        async function cargarSeguros(idPaciente, selectId) {
            // Ejemplo:
            fetch('api/verificar_seguro.php?paciente_id=' + idPaciente)
                .then(response => response.json())
                .then(response => {
                    let seguro = response.dataSeguro;
                    const selectElement = document.getElementById(selectId);
                    selectElement.innerHTML = ''; // Limpiar opciones existentes
                    const option = document.createElement('option');
                    option.value = seguro.seguro_id;
                    option.textContent = `${seguro.nombre} - Saldo: ${seguro.saldo_actual} FCFA`;
                    selectElement.appendChild(option);

                });

        }

        



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
        const nombreProveedor = ev.relatedTarget?.dataset.proveedor || '';
        const montoPendiente = (ev.relatedTarget?.dataset.montopendiente || '');
        const factura = ev.relatedTarget?.dataset.factura.toUpperCase() || '';

        document.getElementById('pp_compra_id').value = id;
        document.getElementById('nombreProveedorDePago').value = nombreProveedor;
        document.getElementById('montoPendienteDePago').value = montoPendiente;
        document.getElementById('factura').innerText = factura;
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

                cambioDevueltoF = total - montoGastado;
                console.log("Los")
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
        /* 
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
        
                 */


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

<!-- =========================== VENTAS =================== -->


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
        const optionSeguro = document.getElementById("seguro");
        const metodoPago = document.getElementById('metodo_pago');
        const pagoEfectivo = document.getElementById("pago_efectivo");
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

                            if (paciente.tiene_seguro == 0) {
                                optionSeguro.style.display = "none"; // Oculta la opción
                            } else {
                                optionSeguro.style.display = "block"; // Asegura que esté visible si tiene seguro
                            }
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
        tablaDetalleVentaCrear.addEventListener('click', function (e) {
            if (e.target.closest('.btn-eliminar-producto')) {
                const id = e.target.closest('.btn-eliminar-producto').dataset.id;
                eliminarProducto(id, productosAgregadosCrear, 'crear');
            }
        });

        // Activar y desactivar los campos de Monto recibido y cambio devuelto segun el select
        metodoPago.addEventListener("change", function (e) {
            const metodo = e.target.value;

            if (metodo === "efectivo") {
                pagoEfectivo.classList.remove("d-none");
                pagoEfectivo.classList.add("d-block");

            } else if (metodo === "seguro") {
                pagoEfectivo.classList.remove("d-block");
                pagoEfectivo.classList.add("d-none");

            }
        });


        // Event listener para los botones de eliminar en la tabla de EDITAR
        tablaDetalleVentaEditar.addEventListener('click', function (e) {
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
                    detalleEstadoPago.textContent = `${venta.estado_pago}`;
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

<!-- ========================= SEGUROS ==================== -->

<script>
    // Script para manejar el buscador de pacientes y los datos de los modales
    document.addEventListener('DOMContentLoaded', function () {
        // Lógica para el modal de Crear Seguro
        const crearTitularSearch = document.getElementById('crear-titular-search');
        const crearTitularId = document.getElementById('crear-titular-id');
        const crearTitularResults = document.getElementById('crear-titular-results');

        crearTitularSearch.addEventListener('input', function () {
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
                                item.addEventListener('click', function (e) {
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

        agregarBeneficiarioSearch.addEventListener('input', function () {
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
                                item.addEventListener('click', function (e) {
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
        btnImprimir.addEventListener('click', function () {
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

<!--======================= ANAL{ITICA ====================== -->
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

        // Función auxiliar para cargar los seguros de un paciente  
        async function cargarSeguros(idPaciente, selectId) {
            // Ejemplo:
            fetch('api/verificar_seguro.php?paciente_id=' + idPaciente)
                .then(response => response.json())
                .then(response => {
                    let seguro = response.dataSeguro;
                    const selectElement = document.getElementById(selectId);
                    selectElement.innerHTML = ''; // Limpiar opciones existentes
                    const option = document.createElement('option');
                    option.value = seguro.seguro_id;
                    option.textContent = `${seguro.nombre} - Saldo: ${seguro.saldo_actual} FCFA`;
                    selectElement.appendChild(option);

                });

        }

        // Función auxiliar para verificar si un paciente tiene seguro  
        async function checkPatientInsurance(idPaciente) {
            // Aquí se hace una llamada AJAX al backend para verificar si el paciente tiene seguro
            // y devolver true o false. 
            return fetch('api/verificar_seguro.php?paciente_id=' + idPaciente)
                .then(response => response.json())
                .then(data => {
                    if (data.existeSeguro)
                        return true;
                    else
                        return false
                });


        }

    });
</script>