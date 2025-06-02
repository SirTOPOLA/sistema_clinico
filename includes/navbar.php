<!-- Topbar -->
<nav id="navbar" class="navbar position-fixed navbar-expand navbar-dark shadow-sm px-3">
    <!-- Topbar -->
    <!-- <nav id="navbar" class="navbar navbar-expand-lg navbar-light bg-light position-fixed shadow-sm px-3 w-100" style="z-index: 1030;">
 -->
    <div class="container-fluid">

        <!-- Botón toggle para sidebar -->
        <button id="toggleSidebar" class="btn btn-outline-primary me-3" aria-label="Mostrar menú lateral"
            aria-controls="sidebar" aria-expanded="false" type="button">
            <i class="bi bi-list fs-5" aria-hidden="true"></i>
        </button>

        <!-- Logo y título -->
        <a href="index.php?vista=inicio"
            class="navbar-brand d-flex align-items-center text-primary fw-bold mb-0 text-decoration-none">
            <i class="bi bi-hospital-fill me-2 fs-4" aria-hidden="true"></i> Panel Consultorio DR. Oscar
        </a>

        <!-- Elementos del lado derecho -->
        <div class="ms-auto d-flex align-items-center gap-3">

            <!-- Botón Home -->
            <a href="index.php?vista=inicio" class="btn btn-sm btn-primary d-flex align-items-center gap-1"
                role="button" aria-label="Ir a la página principal">
                <i class="bi bi-house-door-fill"></i> Inicio
            </a>

            <!-- Información del usuario -->
            <div class="text-end text-primary me-2" aria-live="polite" aria-atomic="true" role="region">
                <div class="fw-semibold">
                    <?= empty($_SESSION['usuario']['usuario']) ? 'SIR-TOPOLA' : htmlspecialchars($_SESSION['usuario']['usuario']) ?>
                </div>
                <small class="text-muted">
                    <?= empty($_SESSION['usuario']['rol']) ? 'SIR-TOPOLA' : htmlspecialchars($_SESSION['usuario']['rol']) ?>
                </small>
            </div>

            <!-- Botón notificaciones -->
            <button id="btnNotificaciones" class="btn btn-sm btn-warning position-relative"
                aria-label="Ver notificaciones" aria-haspopup="true" aria-expanded="false" type="button">
                <i class="bi bi-bell-fill fs-5"></i>
                <span id="contadorNotificaciones"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    0
                    <span class="visually-hidden">notificaciones nuevas</span>
                </span>
            </button>

            <!-- Botón logout -->
            <button id="cerrarSession" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                aria-label="Cerrar sesión" type="button">
                <i class="bi bi-box-arrow-right"></i> Salir
            </button>

        </div>

    </div>

</nav>


<!-- Toast container global -->
<div id="toast-container"></div>