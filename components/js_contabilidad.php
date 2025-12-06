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
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'" : '&#39;','`':'&#96;'
    }[s]));
}

/* function money(num) {
    const n = parseFloat(num);
    if (isNaN(n)) return "0 CFA";
    return n.toLocaleString("es-ES", {minimumFractionDigits:2, maximumFractionDigits:2}) + " CFA";
} */


</script>