 <style>
 h1, h2 {
            color: #343a40; /* Color de texto oscuro */
            margin-bottom: 25px;
        }
        /* Estilos para etiquetas de formulario */
        .form-label {
            font-weight: bold; /* Texto en negrita */
            color: #495057; /* Color de texto para etiquetas */
        }
        /* Estilos base para todos los botones de reporte */
        .btn-primary, .btn-info, .btn-warning, .btn-success, .btn-danger, .btn-dark {
            border-radius: 8px; /* Bordes redondeados */
            padding: 10px 20px;
            font-weight: bold; /* Texto en negrita */
            transition: all 0.3s ease; /* Transición suave para efectos hover */
            margin-bottom: 10px; /* Margen inferior para un mejor espaciado en pantallas pequeñas */
        }
        /* Colores específicos para botones primarios */
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
            transform: translateY(-2px); /* Pequeño efecto de elevación al pasar el ratón */
        }
        /* Colores específicos para botones de información */
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-info:hover {
            background-color: #117a8b;
            border-color: #0c5460;
            transform: translateY(-2px);
        }
        /* Colores específicos para botones de advertencia */
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529; /* Color de texto oscuro para contraste */
        }
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #c69500;
            transform: translateY(-2px);
        }
        /* Colores específicos para botones de éxito */
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #1e7e34;
            border-color: #1c7430;
            transform: translateY(-2px);
        }
         /* Colores específicos para botones de peligro (rojo) */
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
            transform: translateY(-2px);
        }
        /* Nuevo estilo para botón de PDF */
        .btn-dark {
            background-color: #343a40;
            border-color: #343a40;
            color: #ffffff;
        }
        .btn-dark:hover {
            background-color: #1d2124;
            border-color: #171a1d;
            transform: translateY(-2px);
        }

        /* Sección donde se mostrarán los resultados del reporte */
        .report-section {
            margin-top: 40px;
            padding: 20px;
            background-color: #e9ecef; /* Fondo ligeramente gris */
            border-radius: 10px;
            border: 1px solid #dee2e6; /* Borde sutil */
        }
        .report-section h3 {
            color: #007bff; /* Color de título para la sección de resultados */
            margin-bottom: 20px;
        }
        /* Estilos para tablas responsivas */
        .table-responsive {
            margin-top: 20px;
        }
        .table {
            background-color: #ffffff; /* Fondo blanco para las tablas */
            border-radius: 10px;
            overflow: hidden; /* Asegura que los bordes redondeados se apliquen al contenido */
        }
        /* Encabezado de la tabla */
        .table thead {
            background-color: #007bff; /* Fondo azul para el encabezado */
            color: #ffffff; /* Texto blanco en el encabezado */
        }
        .table th, .table td {
            vertical-align: middle; /* Alineación vertical de celdas */
            padding: 12px;
        }
        /* Spinner de carga */
        .loading-spinner {
            display: none; /* Oculto por defecto */
            text-align: center;
            margin-top: 20px;
        }
        .loading-spinner .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        /* Mensajes de alerta */
        .alert-message {
            margin-top: 20px;
        }
        /* Estilos para el contenedor del gráfico */
        .chart-container {
            width: 100%;
            max-width: 800px; /* Ancho máximo para el gráfico */
            margin: 20px auto; /* Centra el gráfico */
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: none; /* Oculto por defecto */
        }
        /* Media queries para responsividad en pantallas pequeñas */
        @media (max-width: 768px) {
            .d-md-flex {
                flex-direction: column; /* Apila los botones en pantallas pequeñas */
                gap: 10px; /* Espaciado entre botones */
            }
            .container {
                padding: 15px; /* Reduce el padding en el contenedor */
            }
        }
    </style>
 
<div id="content" class="container-fluid">
        <h1 class="text-center mb-4">Reportes Financieros</h1>

        <!-- Sección de selección de fechas -->
        <div class="row d-flex justify-content-center mb-3">
            <div class="col-md-2">
                <label for="startDate" class="form-label">Fecha de Inicio:</label>
                <input type="date" class="form-control" id="startDate">
            </div>
            <div class="col-md-2">
                <label for="endDate" class="form-label">Fecha de Fin:</label>
                <input type="date" class="form-control" id="endDate">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-dark w-100" id="btnGeneratePdf" style="display: none;">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Generar PDF
                </button>
            </div>
        </div>
        <hr>


        <!-- Grupo de botones para los diferentes tipos de reportes -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-5">
            <button class="btn btn-primary" id="btnTotalIncome">
                <i class="bi bi-cash-stack"></i> Ingresos Totales por Período
            </button>
            <button class="btn btn-info" id="btnIncomeByType">
                <i class="bi bi-pie-chart"></i> Ingresos por Tipo de Servicio
            </button>
            <button class="btn btn-warning" id="btnIncomeByPatient">
                <i class="bi bi-person-fill"></i> Ingresos por Paciente
            </button>
            <button class="btn btn-success" id="btnOutstandingConsultations">
                <i class="bi bi-clipboard-check"></i> Consultas Pendientes de Pago
            </button>
            <button class="btn btn-danger" id="btnOutstandingAnalytics">
                <i class="bi bi-bar-chart"></i> Analíticas Pendientes de Pago
            </button>
        </div>

        <!-- Spinner de carga que se muestra mientras se carga el reporte -->
        <div class="loading-spinner" id="loadingSpinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando reporte...</p>
        </div>

        <!-- Contenedor para el gráfico -->
        <div class="chart-container" id="chartContainer">
            <canvas id="incomeChart"></canvas>
        </div>

        <!-- Área donde se mostrarán los resultados del reporte -->
        <div id="reportResults" class="report-section d-none">
            <!-- Los resultados del reporte se inyectarán aquí mediante JavaScript -->
        </div>
    </div>

    <!-- Incluye Bootstrap JS (bundle incluye Popper.js) al final del body para un mejor rendimiento -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Incluye Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <!-- Incluye jsPDF para generar PDFs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <!-- Incluye html2canvas para convertir HTML a imagen para PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>


    <script>
        // Inicializa jsPDF al cargar el script
        const { jsPDF } = window.jspdf;

        document.addEventListener('DOMContentLoaded', function() {
            // Obtener referencias a los elementos del DOM
            const startDateInput = document.getElementById('startDate');
            const endDateInput = document.getElementById('endDate');
            const reportResultsDiv = document.getElementById('reportResults');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const chartContainer = document.getElementById('chartContainer');
            const incomeChartCanvas = document.getElementById('incomeChart');
            const btnGeneratePdf = document.getElementById('btnGeneratePdf');

            let myChart; // Variable para almacenar la instancia de Chart.js

            // Establecer fechas por defecto: hoy y 30 días atrás
            const today = new Date();
            const thirtyDaysAgo = new Date();
            thirtyDaysAgo.setDate(today.getDate() - 30); // Resta 30 días

            // Formatear las fechas para el input type="date" (YYYY-MM-DD)
            startDateInput.value = thirtyDaysAgo.toISOString().split('T')[0];
            endDateInput.value = today.toISOString().split('T')[0];

            /**
             * Destruye el gráfico existente si lo hay.
             */
            function destroyChart() {
                if (myChart) {
                    myChart.destroy();
                }
            }

            /**
             * Función asíncrona genérica para enviar solicitudes AJAX al backend PHP.
             * @param {string} action La acción a realizar en el backend (ej. 'getTotalIncome').
             * @param {object} extraData Datos adicionales a enviar en la solicitud.
             */
            async function fetchData(action, extraData = {}) {
                reportResultsDiv.classList.add('d-none'); // Oculta los resultados anteriores
                chartContainer.style.display = 'none'; // Oculta el contenedor del gráfico
                btnGeneratePdf.style.display = 'none'; // Oculta el botón de PDF
                loadingSpinner.style.display = 'block'; // Muestra el spinner de carga
                destroyChart(); // Destruye cualquier gráfico existente

                // Crea un objeto FormData para enviar los datos al servidor
                const formData = new FormData();
                formData.append('action', action); // Agrega la acción
                formData.append('startDate', startDateInput.value); // Agrega la fecha de inicio
                formData.append('endDate', endDateInput.value);     // Agrega la fecha de fin

                // Agrega cualquier dato adicional al FormData
                for (const key in extraData) {
                    formData.append(key, extraData[key]);
                }

                try {
                    // Realiza la solicitud fetch (AJAX) al script PHP
                    const response = await fetch('api/reports.php', { // ¡IMPORTANTE! Ajusta esta ruta si tu archivo PHP está en otro lugar
                        method: 'POST', // Método POST para enviar datos
                        body: formData  // Cuerpo de la solicitud con FormData
                    });

                    // Parsea la respuesta JSON del servidor
                    const result = await response.json();

                    if (result.status === 'success') {
                        // Si la operación fue exitosa, muestra el reporte y el gráfico si aplica
                        displayReport(action, result.data);
                        btnGeneratePdf.style.display = 'block'; // Muestra el botón de PDF después de cargar el reporte
                    } else {
                        // Si hubo un error en el servidor, muestra el mensaje de error
                        displayError(result.message || 'Error al cargar el reporte.');
                    }
                } catch (error) {
                    // Captura errores de red o del fetch
                    console.error('Error fetching data:', error);
                    displayError('Error de conexión con el servidor. Por favor, verifica la ruta del archivo PHP y la conexión.');
                } finally {
                    // Oculta el spinner de carga y muestra la sección de resultados
                    loadingSpinner.style.display = 'none';
                    reportResultsDiv.classList.remove('d-none');
                }
            }

            /**
             * Función para mostrar los datos del reporte en la interfaz de usuario.
             * @param {string} action La acción del reporte que se está mostrando.
             * @param {object|array} data Los datos recibidos del backend para el reporte.
             */
            function displayReport(action, data) {
                reportResultsDiv.innerHTML = ''; // Limpia el contenido anterior del div de resultados
                let html = '<h3>Resultados del Reporte</h3>'; // Título de la sección de resultados

                // Estructura el HTML según el tipo de reporte
                switch (action) {
                    case 'getTotalIncome':
                        html += `
                            <div class="alert alert-success" role="alert">
                                <h4>Ingresos Totales por Período:</h4>
                                <p class="lead mb-0"><strong>${data.totalIncome ? parseFloat(data.totalIncome).toFixed(2) + ' CFA' : '0.00 CFA'}</strong></p>
                            </div>
                        `;
                        // Aquí podrías agregar un gráfico de línea si tuvieras datos por día/mes
                        // Para este ejemplo, solo se muestra el total, no hay datos para un gráfico de tendencia.
                        // Si el backend proporcionara ingresos por día, se podría usar renderLineChart.
                        break;
                    case 'getIncomeByType':
                        html += `
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card text-center bg-success text-white">
                                        <div class="card-body">
                                            <h5 class="card-title">Ingresos por Consultas</h5>
                                            <p class="card-text fs-4 text-white"><strong>${data.consultations ? parseFloat(data.consultations).toFixed(2) + ' CFA' : '0.00 CFA'}</strong></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card text-center bg-white text-dark">
                                        <div class="card-body">
                                            <h5 class="card-title">Ingresos por Analíticas</h5>
                                            <p class="card-text fs-4"><strong>${data.analytics ? parseFloat(data.analytics).toFixed(2) + ' CFA' : '0.00 CFA'}</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        // Renderiza un gráfico de pastel para ingresos por tipo
                        renderPieChart(data);
                        break;
                    case 'getIncomeByPatient':
                        html += '<h4>Ingresos por Paciente:</h4>';
                        if (data.length > 0) {
                            html += `
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Paciente</th>
                                                <th>Ingreso Total</th>
                                                <th>Ingreso Consultas</th>
                                                <th>Ingreso Analíticas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                            data.forEach(patient => {
                                html += `
                                    <tr>
                                        <td>${patient.name}</td>
                                        <td>${patient.total_income.toFixed(2)} CFA</td>
                                        <td>${patient.consultation_income.toFixed(2)} CFA</td>
                                        <td>${patient.analytic_income.toFixed(2)} CFA</td>
                                    </tr>
                                `;
                            });
                            html += `
                                        </tbody>
                                    </table>
                                </div>
                            `;
                        } else {
                            html += '<p class="text-muted">No se encontraron ingresos por paciente para el período seleccionado.</p>';
                        }
                        break;
                    case 'getOutstandingConsultations':
                        html += '<h4>Consultas Pendientes de Pago:</h4>';
                        if (data.length > 0) {
                            html += `
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID Consulta</th>
                                                <th>Paciente</th>
                                                <th>Motivo</th>
                                                <th>Precio</th>
                                                <th>Fecha Registro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                            data.forEach(consultation => {
                                html += `
                                    <tr>
                                        <td>${consultation.consulta_id}</td>
                                        <td>${consultation.nombre} ${consultation.apellidos}</td>
                                        <td>${consultation.motivo_consulta}</td>
                                        <td>${parseFloat(consultation.precio).toFixed(2)} CFA</td>
                                        <td>${new Date(consultation.fecha_registro).toLocaleDateString('es-ES')}</td>
                                    </tr>
                                `;
                            });
                            html += `
                                        </tbody>
                                    </table>
                                </div>
                            `;
                        } else {
                            html += '<p class="text-muted">No hay consultas pendientes de pago.</p>';
                        }
                        break;
                    case 'getOutstandingAnalytics':
                        html += '<h4>Analíticas Pendientes de Pago:</h4>';
                        if (data.length > 0) {
                            html += `
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID Analítica</th>
                                                <th>Paciente</th>
                                                <th>Tipo Prueba</th>
                                                <th>Precio</th>
                                                <th>Fecha Registro</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                            data.forEach(analytic => {
                                html += `
                                    <tr>
                                        <td>${analytic.analitica_id}</td>
                                        <td>${analytic.nombre} ${analytic.apellidos}</td>
                                        <td>${analytic.tipo_prueba}</td>
                                        <td>${parseFloat(analytic.precio).toFixed(2)} CFA</td>
                                        <td>${new Date(analytic.fecha_registro).toLocaleDateString('es-ES')}</td>
                                    </tr>
                                `;
                            });
                            html += `
                                        </tbody>
                                    </table>
                                </div>
                            `;
                        } else {
                            html += '<p class="text-muted">No hay analíticas pendientes de pago.</p>';
                        }
                        break;
                    default:
                        html += '<p>Selecciona un tipo de reporte para ver los resultados.</p>';
                        break;
                }
                reportResultsDiv.innerHTML = html; // Inyecta el HTML generado en el div de resultados
            }

            /**
             * Renderiza un gráfico de pastel para los ingresos por tipo (consultas vs. analíticas).
             * @param {object} data Objeto con las propiedades 'consultations' y 'analytics'.
             */
            function renderPieChart(data) {
                chartContainer.style.display = 'block'; // Muestra el contenedor del gráfico

                const ctx = incomeChartCanvas.getContext('2d');
                destroyChart(); // Destruye cualquier gráfico anterior

                myChart = new Chart(ctx, {
                    type: 'pie', // Tipo de gráfico: pastel
                    data: {
                        labels: ['Consultas', 'Analíticas'], // Etiquetas para las secciones del pastel
                        datasets: [{
                            data: [data.consultations, data.analytics], // Datos para cada sección
                            backgroundColor: [
                                'rgba(0, 123, 255, 0.7)', // Azul para Consultas
                                'rgba(23, 162, 184, 0.7)'  // Celeste para Analíticas
                            ],
                            borderColor: [
                                'rgba(0, 123, 255, 1)',
                                'rgba(23, 162, 184, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true, // El gráfico será responsivo
                        maintainAspectRatio: false, // Permite que el gráfico no mantenga su aspecto original
                        plugins: {
                            title: {
                                display: true,
                                text: 'Distribución de Ingresos por Tipo de Servicio',
                                font: {
                                    size: 18
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += parseFloat(context.raw).toFixed(2) + ' CFA';
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            /**
             * Función para mostrar mensajes de error en la interfaz de usuario.
             * @param {string} message El mensaje de error a mostrar.
             */
            function displayError(message) {
                reportResultsDiv.innerHTML = `<div class="alert alert-danger alert-message" role="alert">
                                                <strong>Error:</strong> ${message}
                                              </div>`;
                reportResultsDiv.classList.remove('d-none'); // Asegura que la sección de resultados esté visible
                chartContainer.style.display = 'none'; // Oculta el contenedor del gráfico en caso de error
                btnGeneratePdf.style.display = 'none'; // Oculta el botón de PDF en caso de error
            }

            /**
             * Genera un archivo PDF del contenido del reporte visible.
             */
            btnGeneratePdf.addEventListener('click', async () => {
                btnGeneratePdf.disabled = true; // Deshabilita el botón mientras se genera el PDF
                btnGeneratePdf.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generando PDF...';

                // Usamos html2canvas para capturar la sección de resultados y el gráfico
                const reportSection = document.getElementById('reportResults');
                const chartElement = document.getElementById('incomeChart');
                const chartContainerElement = document.getElementById('chartContainer');

                const doc = new jsPDF('p', 'mm', 'a4'); // 'p' para retrato, 'mm' para milímetros, 'a4' tamaño de página

                // Agrega un título al PDF
                doc.setFontSize(22);
                doc.text('Reporte Financiero del Consultorio Clínico', 14, 20);
                doc.setFontSize(12);
                doc.text(`Fecha de Generación: ${new Date().toLocaleDateString('es-ES')}`, 14, 30);
                doc.text(`Período: ${startDateInput.value} al ${endDateInput.value}`, 14, 37);

                let yPos = 50; // Posición inicial Y para el contenido

                // 1. Capturar y añadir el gráfico si está visible
                if (chartContainerElement.style.display === 'block' && myChart) {
                    doc.setFontSize(16);
                    doc.text('Gráfico de Ingresos', 14, yPos);
                    yPos += 10;

                    // Convierte el canvas del gráfico en una imagen de datos URL
                    const chartImage = incomeChartCanvas.toDataURL('image/png', 1.0); // Calidad 1.0 (máxima)

                    // Añade la imagen del gráfico al PDF
                    // Calcula las dimensiones para que quepa en la página y mantenga el aspecto
                    const imgWidth = 180; // Ancho deseado en mm
                    const imgHeight = (incomeChartCanvas.height * imgWidth) / incomeChartCanvas.width;
                    doc.addImage(chartImage, 'PNG', 14, yPos, imgWidth, imgHeight);
                    yPos += imgHeight + 20; // Deja espacio después del gráfico
                }

                // 2. Capturar y añadir el contenido HTML de la sección de resultados
                doc.setFontSize(16);
                doc.text('Detalle del Reporte', 14, yPos);
                yPos += 10;

                // html2canvas renderiza el HTML a un canvas (imagen)
                const canvas = await html2canvas(reportSection, { scale: 2 }); // Escala 2 para mejor resolución en PDF
                const imgData = canvas.toDataURL('image/png', 1.0); // Calidad 1.0 (máxima)

                // Calcula las dimensiones para que quepa en la página y mantenga el aspecto
                const imgWidth = 190; // Ancho deseado en mm (casi el ancho de la página A4)
                const pageHeight = doc.internal.pageSize.height;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;

                let position = yPos; // Posición inicial para la imagen del HTML

                // Si el contenido es demasiado largo para una página, divídelo en varias
                doc.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                heightLeft -= (pageHeight - position); // Reduce la altura restante

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight; // Calcula la nueva posición para la siguiente página
                    doc.addPage(); // Añade una nueva página
                    doc.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                // Guarda el PDF con un nombre de archivo
                doc.save(`Reporte_Financiero_${new Date().toLocaleDateString('es-ES').replace(/\//g, '-')}.pdf`);

                btnGeneratePdf.disabled = false; // Habilita el botón nuevamente
                btnGeneratePdf.innerHTML = '<i class="bi bi-file-earmark-pdf-fill"></i> Generar PDF';
            });

            // Asigna los event listeners a los botones para ejecutar las funciones de reporte
            document.getElementById('btnTotalIncome').addEventListener('click', () => fetchData('getTotalIncome'));
            document.getElementById('btnIncomeByType').addEventListener('click', () => fetchData('getIncomeByType'));
            document.getElementById('btnIncomeByPatient').addEventListener('click', () => fetchData('getIncomeByPatient'));
            document.getElementById('btnOutstandingConsultations').addEventListener('click', () => fetchData('getOutstandingConsultations'));
            document.getElementById('btnOutstandingAnalytics').addEventListener('click', () => fetchData('getOutstandingAnalytics'));
        });
    </script>