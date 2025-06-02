</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
 

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
 
</body>

</html>