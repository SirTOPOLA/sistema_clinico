 <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa; /* Light background for the page */
        }
        .container-fluid {
            padding: 20px;
        }
        .card {
            border-radius: 0.75rem; /* More rounded corners */
            overflow: hidden; /* Ensures shadow and border-radius apply correctly */
        }
        .custom-shadow {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05); /* Lighter, modern shadow */
        }
        .table-responsive {
            border-radius: 0.75rem; /* Match card border-radius */
            overflow-x: auto; /* Ensure horizontal scrolling for tables */
        }
        /* Custom scrollbar for better appearance */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-thumb {
            background: #adb5bd; /* Bootstrap's gray */
            border-radius: 10px;
        }
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #6c757d;
        }

        .badge {
            padding: 0.4em 0.8em;
            border-radius: 0.5rem;
            font-weight: 600;
        }
        .btn-pay {
            background-color: #28a745; /* Bootstrap success green */
            border-color: #28a745;
            color: white;
            transition: all 0.2s ease-in-out;
        }
        .btn-pay:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(40, 167, 69, 0.2);
        }
        .btn-print-invoice {
            background-color: #6c757d; /* Bootstrap secondary gray */
            border-color: #6c757d;
            color: white;
            transition: all 0.2s ease-in-out;
        }
        .btn-print-invoice:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(108, 117, 125, 0.2);
        }
        .btn-view-tests {
            background-color: #007bff; /* Bootstrap primary blue */
            border-color: #007bff;
            color: white;
            transition: all 0.2s ease-in-out;
        }
        .btn-view-tests:hover {
            background-color: #0069d9;
            border-color: #0062cc;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 123, 255, 0.2);
        }
        /* Custom message box styling */
        .message-box {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1050; /* Above modals */
            min-width: 250px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
    </style>
 

<div class="container-fluid" id="content">

    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-3 mb-md-0 text-gray-900">
            <i class="fas fa-file-invoice-dollar me-3 text-primary"></i>Gestión de Reportes Financieros
        </h1>
        <div class="w-100 w-md-auto d-flex justify-content-end">
            <div class="input-group" style="max-width: 400px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" id="buscadorPago" class="form-control border-start-0 rounded-end shadow-sm" placeholder="Buscar paciente, código, prueba o fecha...">
            </div>
        </div>
    </div>

    <!-- Custom Message Box Area -->
    <div id="messageBoxContainer"></div>

    <div class="card border-0 shadow-sm custom-shadow">
        <div class="card-body table-responsive p-0">
            <table id="tablaAnaliticas" class="table table-hover table-sm align-middle mb-0">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider rounded-top-left-lg">ID (Analítica)</th>
                        <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre del Paciente</th>
                        <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Código</th>
                        <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Pruebas</th>
                        <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Resultados</th>
                        <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">Pagos</th>
                        <th scope="col" class="px-4 py-3 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider rounded-top-right-lg">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="reportsTableBody">
                    <!-- Table rows will be dynamically inserted here by JavaScript -->
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">Cargando reportes...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="modalPagar" tabindex="-1" aria-labelledby="modalPagarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-xl shadow-lg">
            <div class="modal-header bg-primary text-white border-0 rounded-top-xl">
                <h5 class="modal-title" id="modalPagarLabel"><i class="bi bi-credit-card-fill me-2"></i>Realizar Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted" id="modalPatientInfo"></p>
                <div id="pendingTestsList" class="mb-3 max-h-60 overflow-y-auto border rounded p-3 bg-light">
                    <!-- Pending tests will be dynamically inserted here -->
                    <p class="text-center text-muted">No hay pruebas pendientes de pago.</p>
                </div>
                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
                    <span class="fs-5 fw-bold text-dark">Total a Pagar: <span id="totalToPayDisplay" class="text-success">€0.00</span></span>
                    <button type="button" class="btn btn-success btn-lg rounded-pill shadow-sm" id="confirmPaymentBtn" disabled>
                        <i class="bi bi-cash-coin me-2"></i>Confirmar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Bootstrap 5 JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9Gkcq+pma+48nqqxLpvC2/mCWILvM" crossorigin="anonymous"></script>

<script>
    // Mock data to simulate API response
    const mockAnaliticasData = [
        { id: 1, resultado: "Normal", estado: "Completado", codigo_paciente: "P001", pagado: 0, id_tipo_prueba: 101, tipo_prueba: "Hemograma Completo", precio: 50.00, id_paciente: 1, paciente: "Juan Perez", fecha_registro: "2024-06-20 10:00:00", fecha_solo: "2024-06-20" },
        { id: 2, resultado: "Alto", estado: "Completado", codigo_paciente: "P001", pagado: 0, id_tipo_prueba: 102, tipo_prueba: "Glucosa en Sangre", precio: 30.00, id_paciente: 1, paciente: "Juan Perez", fecha_registro: "2024-06-20 10:05:00", fecha_solo: "2024-06-20" },
        { id: 3, resultado: "", estado: "Pendiente", codigo_paciente: "P002", pagado: 1, id_tipo_prueba: 103, tipo_prueba: "Perfil Lipídico", precio: 70.00, id_paciente: 2, paciente: "Maria Garcia", fecha_registro: "2024-06-19 14:00:00", fecha_solo: "2024-06-19" },
        { id: 4, resultado: "Normal", estado: "Completado", codigo_paciente: "P003", pagado: 1, id_tipo_prueba: 101, tipo_prueba: "Hemograma Completo", precio: 50.00, id_paciente: 3, paciente: "Pedro Lopez", fecha_registro: "2024-06-18 09:30:00", fecha_solo: "2024-06-18" },
        { id: 5, resultado: "Normal", estado: "Completado", codigo_paciente: "P001", pagado: 1, id_tipo_prueba: 104, tipo_prueba: "Prueba de Orina", precio: 25.00, id_paciente: 1, paciente: "Juan Perez", fecha_registro: "2024-06-17 11:00:00", fecha_solo: "2024-06-17" },
        { id: 6, resultado: "", estado: "Pendiente", codigo_paciente: "P004", pagado: 0, id_tipo_prueba: 105, tipo_prueba: "Función Renal", precio: 60.00, id_paciente: 4, paciente: "Ana Martinez", fecha_registro: "2024-06-20 16:00:00", fecha_solo: "2024-06-20" },
    ];

    let currentReportsData = []; // Stores the raw fetched data
    let groupedReports = []; // Stores the grouped data for display

    // Helper function to group data similar to your PHP logic
    const groupAnaliticas = (data) => {
        const grupos = {};
        data.forEach(a => {
            const clave = `${a.paciente}_${a.fecha_solo}`;
            if (!grupos[clave]) {
                grupos[clave] = {
                    paciente: a.paciente,
                    codigo: a.codigo_paciente,
                    id_paciente: a.id_paciente,
                    fecha: a.fecha_solo,
                    registros: [],
                    pagos: [],
                };
            }
            grupos[clave].registros.push(a);
            grupos[clave].pagos.push(a.pagado);
        });
        // Convert object to array for easier iteration
        return Object.values(grupos).sort((a, b) => new Date(b.fecha) - new Date(a.fecha)); // Sort by date descending
    };

    // Custom Message Box Function
    function showMessage(message, type) {
        const container = document.getElementById('messageBoxContainer');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show message-box`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        container.appendChild(alertDiv);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }, 5000);
    }

    // Helper to format date for display
    const formatDate = (dateString) => {
        if (!dateString) return '';
        const [year, month, day] = dateString.split('-');
        return `${day}/${month}/${year}`;
    };

    // Function to render the reports table
    const renderReportsTable = (dataToRender) => {
        const tableBody = document.getElementById('reportsTableBody');
        tableBody.innerHTML = ''; // Clear existing rows

        if (dataToRender.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">No se encontraron reportes.</td>
                </tr>
            `;
            return;
        }

        dataToRender.forEach((group, index) => {
            const todosConResultado = group.registros.every(r => r.resultado && r.resultado.trim() !== '');
            const todosPagados = group.pagos.every(p => p === 1);
            const displayId = group.registros.length > 0 ? group.registros[0].id : 'N/A'; // Use first analytic ID for display

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-3 text-sm text-gray-900">${displayId}</td>
                <td class="px-4 py-3 text-sm text-gray-900 fw-bold">${group.paciente}</td>
                <td class="px-4 py-3 text-sm text-gray-700">${group.codigo}</td>
                <td class="px-4 py-3 text-sm text-gray-700">
                    <ul class="list-unstyled mb-0 small">
                        ${group.registros.map(r => `<li>${r.tipo_prueba}</li>`).join('')}
                    </ul>
                </td>
                <td class="px-4 py-3 text-sm">
                    ${todosConResultado ?
                        `<span class="badge bg-primary"><i class="fas fa-check-circle me-1"></i> Resultado</span>` :
                        `<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Sin Resultado</span>`
                    }
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">${formatDate(group.fecha)}</td>
                <td class="px-4 py-3 text-sm">
                    ${todosPagados ?
                        `<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Pagado</span>` :
                        `<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i> Pendiente</span>`
                    }
                </td>
                <td class="px-4 py-3 text-sm text-nowrap">
                    ${!todosPagados ? `
                        <button class="btn btn-pay btn-sm rounded-pill shadow-sm mb-2 w-100"
                                data-bs-toggle="modal" data-bs-target="#modalPagar"
                                data-group='${JSON.stringify(group)}'
                                title="Pagar pruebas pendientes">
                            <i class="fas fa-cash-register me-2"></i> Pagar
                        </button>
                    ` : `
                        <a href="fpdf/generar_factura.php?id=${displayId}&fecha=${group.fecha}"
                           target="_blank" class="btn btn-print-invoice btn-sm rounded-pill shadow-sm mb-2 w-100" title="Imprimir Factura">
                            <i class="fas fa-print me-2"></i> Imprimir Factura
                        </a>
                    `}
                    <a href="fpdf/imprimir_pruebas.php?id=${group.id_paciente}&fecha=${group.fecha}"
                       target="_blank" class="btn btn-view-tests btn-sm rounded-pill shadow-sm w-100" title="Ver Pruebas Médicas">
                        <i class="fas fa-file-medical me-2"></i> Ver Pruebas
                    </a>
                </td>
            `;
            tableBody.appendChild(row);
        });
    };

    // Function to simulate fetching data from API
    const fetchReports = async () => {
        const tableBody = document.getElementById('reportsTableBody');
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
            <p class="mt-2">Cargando reportes...</p>
        </td></tr>`;

        // In a real application, you'd make a fetch call to your PHP backend:
        // const response = await fetch('/api/reports/financial?startDate=...&endDate=...&search=...');
        // const data = await response.json();
        await new Promise(resolve => setTimeout(resolve, 500)); // Simulate API delay

        currentReportsData = [...mockAnaliticasData]; // Use a copy for manipulation
        groupedReports = groupAnaliticas(currentReportsData);
        renderReportsTable(groupedReports);
    };

    // Handle search input change
    document.getElementById('buscadorPago').addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const filteredReports = groupedReports.filter(group => {
            return (
                group.paciente.toLowerCase().includes(searchTerm) ||
                group.codigo.toLowerCase().includes(searchTerm) ||
                group.fecha.includes(searchTerm) ||
                group.registros.some(r => r.tipo_prueba.toLowerCase().includes(searchTerm))
            );
        });
        renderReportsTable(filteredReports);
    });

    // --- Payment Modal Logic ---
    const modalPagar = document.getElementById('modalPagar');
    let selectedGroupForPayment = null;
    let selectedTestsForPayment = [];
    let currentTotalToPay = 0;

    modalPagar.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget; // Button that triggered the modal
        const groupData = JSON.parse(button.getAttribute('data-group'));
        selectedGroupForPayment = groupData;

        const modalPatientInfo = modalPagar.querySelector('#modalPatientInfo');
        modalPatientInfo.textContent = `Paciente: ${groupData.paciente} (Fecha: ${formatDate(groupData.fecha)})`;

        const pendingTestsList = modalPagar.querySelector('#pendingTestsList');
        pendingTestsList.innerHTML = '';
        currentTotalToPay = 0;
        selectedTestsForPayment = []; // Reset selected tests

        const pending = groupData.registros.filter(r => r.pagado === 0);

        if (pending.length === 0) {
            pendingTestsList.innerHTML = `<p class="text-center text-muted">No hay pruebas pendientes de pago para este grupo.</p>`;
            document.getElementById('confirmPaymentBtn').disabled = true;
        } else {
            pending.forEach(test => {
                const div = document.createElement('div');
                div.className = 'd-flex justify-content-between align-items-center py-2 border-bottom';
                div.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="${test.id}" data-price="${test.precio}" id="testCheck${test.id}" checked>
                        <label class="form-check-label text-dark" for="testCheck${test.id}">${test.tipo_prueba}</label>
                    </div>
                    <span class="fw-bold text-dark">€${parseFloat(test.precio).toFixed(2)}</span>
                `;
                pendingTestsList.appendChild(div);

                // Add to selected tests by default and update total
                selectedTestsForPayment.push(test.id);
                currentTotalToPay += parseFloat(test.precio);
            });
            // Remove last border-bottom
            if (pendingTestsList.lastChild) {
                pendingTestsList.lastChild.classList.remove('border-bottom');
            }
            document.getElementById('confirmPaymentBtn').disabled = false;
        }
        updateTotalToPayDisplay();
    });

    modalPagar.addEventListener('change', (event) => {
        if (event.target.matches('.form-check-input')) {
            const checkbox = event.target;
            const testId = parseInt(checkbox.value);
            const price = parseFloat(checkbox.dataset.price);

            if (checkbox.checked) {
                selectedTestsForPayment.push(testId);
                currentTotalToPay += price;
            } else {
                selectedTestsForPayment = selectedTestsForPayment.filter(id => id !== testId);
                currentTotalToPay -= price;
            }
            updateTotalToPayDisplay();
            document.getElementById('confirmPaymentBtn').disabled = selectedTestsForPayment.length === 0;
        }
    });

    const updateTotalToPayDisplay = () => {
        document.getElementById('totalToPayDisplay').textContent = `€${currentTotalToPay.toFixed(2)}`;
    };

    document.getElementById('confirmPaymentBtn').addEventListener('click', async () => {
        const confirmPaymentBtn = document.getElementById('confirmPaymentBtn');
        confirmPaymentBtn.disabled = true;
        confirmPaymentBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Procesando...`;

        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate API delay

        // In a real application, you'd send this to your PHP backend:
        // const formData = new FormData();
        // selectedTestsForPayment.forEach(id => formData.append('analyticIds[]', id));
        // // Example of adding more data if needed:
        // // formData.append('pacienteId', selectedGroupForPayment.id_paciente);
        // // formData.append('fechaPago', new Date().toISOString().slice(0,10));

        // try {
        //     const response = await fetch('/api/payments/markPaid', {
        //         method: 'POST',
        //         body: formData,
        //     });
        //     const result = await response.json();
        //     if (result.success) {
        //         showMessage('Pago(s) procesado(s) exitosamente.', 'success');
        //         fetchReports(); // Re-fetch or update data
        //     } else {
        //         showMessage('Error al procesar el pago: ' + result.message, 'danger');
        //     }
        // } catch (error) {
        //     console.error('Error al enviar el pago:', error);
        //     showMessage('Error de red o del servidor al procesar el pago.', 'danger');
        // }

        // Update mock data for demonstration
        mockAnaliticasData.forEach(item => {
            if (selectedTestsForPayment.includes(item.id)) {
                item.pagado = 1;
            }
        });

        // Hide the modal manually
        const bootstrapModal = bootstrap.Modal.getInstance(modalPagar);
        if (bootstrapModal) {
            bootstrapModal.hide();
        }

        showMessage('Pago(s) procesado(s) exitosamente.', 'success');
        fetchReports(); // Re-fetch or update data

        confirmPaymentBtn.disabled = false;
        confirmPaymentBtn.innerHTML = `<i class="bi bi-cash-coin me-2"></i>Confirmar Pago`;
    });

    // Initial fetch of reports when the page loads
    document.addEventListener('DOMContentLoaded', fetchReports);

</script>

 
