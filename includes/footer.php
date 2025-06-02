</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Validación personalizada Bootstrap 5
    (() => {
        'use strict'
        const forms = document.querySelectorAll('.needs-validation')
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        document.getElementById("toggleSidebar").addEventListener("click", function () {
            if (window.innerWidth <= 767.98) {
                document.getElementById("sidebar").classList.toggle("show");
                document.getElementById("sidebar").classList.remove("collapsed");
                document.getElementById("content").classList.remove("collapsed");
                document.getElementById("navbar").classList.remove("collapsed");


            } else {
                document.getElementById("sidebar").classList.toggle("collapsed");
                document.getElementById("content").classList.toggle("collapsed");
                document.getElementById("navbar").classList.toggle("collapsed");

            }
        });


    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const btn = document.getElementById("btnNotificaciones");
     
        const contador = document.getElementById("contadorNotificaciones");


        function cargarLogs() {
            fetch("api/obtener_logs.php")
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.mensajes.length > 0) {
                        let html = `<div class="container">`;

                        data.mensajes.slice(0, 10).forEach((msg, index) => {
                            const campos = msg.split(" | ").reduce((acc, parte) => {
                                const [clave, valor] = parte.split(": ");
                                if (clave && valor) acc[clave.trim()] = valor.trim();
                                return acc;
                            }, {});

                            html += `
                        <div class="card mb-3 border-primary">
                            <div class="card-body">
                                <h5 class="card-title"><strong>Nombre:</strong> ${campos["Nombre"] || "Sin nombre"}</h5>
                                <p class="card-text">
                                    <strong>Código:</strong> ${campos["Código"] || ""}<br>
                                    <strong>Teléfono:</strong> ${campos["Teléfono"] || ""}<br>
                                    <strong>Dirección:</strong> ${campos["Dirección"] || ""}<br>
                                    <strong>Email:</strong> ${campos["Email"] || ""}<br>
                                    <strong>Descripción:</strong> ${campos["Descripción"] || ""}
                                </p>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-success me-2 btnLeer" data-index="${index}">Marcar como leído</button>
                                    <button class="btn btn-danger btnEliminar" data-index="${index}">Eliminar</button>
                                </div>
                            </div>
                        </div>`;
                        });

                        html += `</div>`;

                        Swal.fire({
                            title: "📋 Notificaciones recientes",
                            html: html,
                            width: 700,
                            showConfirmButton: false,
                            customClass: { popup: 'text-start' },
                            didOpen: () => {
                                // Marcar como leído
                                document.querySelectorAll(".btnLeer").forEach(btn => {
                                    btn.addEventListener("click", e => {
                                        const idx = btn.dataset.index;
                                        const msg = data.mensajes[idx];

                                        // Convertir mensaje plano a objeto
                                        const campos = msg.split(" | ").reduce((acc, parte) => {
                                            const [clave, valor] = parte.split(": ");
                                            if (clave && valor) acc[clave.trim().toLowerCase()] = valor.trim();
                                            return acc;
                                        }, {});

                                        // Preparar objeto limpio para localStorage
                                        const cliente = {
                                            nombre: campos["nombre"] || "",
                                            correo: campos["email"] || "",
                                            telefono: campos["teléfono"] || campos["telefono"] || "",
                                            codigo: campos["código"] || campos["codigo"] || "",
                                            direccion: campos["dirección"] || campos["direccion"] || ""
                                        };

                                        // Guardar en localStorage
                                        localStorage.setItem("clienteTemporal", JSON.stringify(cliente));

                                        // Redirigir
                                        window.location.href = "index.php?vista=registrar_clientes";
                                    });

                                });

                                // Eliminar
                                document.querySelectorAll(".btnEliminar").forEach(btn => {
                                    btn.addEventListener("click", e => {
                                        const idx = btn.dataset.index;
                                        fetch("api/eliminar_logs.php", {
                                            method: "POST",
                                            headers: { "Content-Type": "application/json" },
                                            body: JSON.stringify({ index: idx })
                                        })
                                            .then(res => res.json())
                                            .then(resp => {
                                                if (resp.success) {
                                                    Swal.fire("Eliminado", "La notificación fue eliminada", "success");
                                                    cargarLogs(); // Recargar la lista
                                                }
                                            });
                                    });
                                });
                            }
                        });
                    } else {
                        Swal.fire("Sin notificaciones", "No hay registros en logs.txt", "info");
                    }
                });
        }

        btn.addEventListener("click", () => {
            cargarLogs();
        });

        // Contador de notificaciones
        fetch("api/obtener_logs.php")
            .then(res => res.json())
            .then(data => {
                if (data.success && data.mensajes.length > 0) {
                    contador.textContent = data.mensajes.length;
                    contador.classList.add("badge", "bg-danger", "ms-1");
                } else {
                    contador.style.display = "none";
                }
            });
    });


    function cerrarSession() {
    document.getElementById('cerrarSession').addEventListener('click', () => {
        if (confirm('¿Estás seguro de que deseas cerrar la sesión?')) {
           // console.log('mola')
            // Redirecciona o ejecuta la lógica de cierre
            window.location.href = 'logout.php'; // Cambia a tu ruta real
        }
    });
}

cerrarSession();

</script>

</body>

</html>