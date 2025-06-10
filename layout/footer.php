
</div>

 
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

   
    function cerrarSession() {
        document.getElementById('cerrarSession').addEventListener('click', () => {
            if (confirm('¿Estás seguro de que deseas cerrar la sesión?')) {
                window.location.href = 'logout.php';  
                location.reload()
            }
        });
    }

    function sidebarToggle () { 
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

}

document.addEventListener('DOMContentLoaded', function () {
        cerrarSession();
        sidebarToggle ()
    });
</script>

</body>

</html>