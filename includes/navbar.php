<!-- Topbar -->
<nav id="navbar" class="navbar position-fixed navbar-expand navbar-dark shadow-sm px-3">
 <!-- Topbar -->
<!-- <nav id="navbar" class="navbar navbar-expand-lg navbar-light bg-light position-fixed shadow-sm px-3 w-100" style="z-index: 1030;">
 -->    
<div class="container-fluid">
        <!-- Botón de toggle para sidebar -->
        <button id="toggleSidebar" class="btn btn-outline-dark me-3" aria-label="Menú lateral">
            <i class="bi bi-list fs-5"></i>
        </button>

        <!-- Logo y título -->
        <span class="navbar-brand d-flex align-items-center text-dark fw-semibold mb-0">
            <i class="bi bi-hammer me-2 fs-4"></i> Panel Carpintería
        </span>

        <!-- Elementos del lado derecho -->
        <div class="ms-auto d-flex align-items-center gap-3">
            <!-- Botón Home -->
            <a href="index.php?vista=inicio" class="btn btn-sm btn-outline-primary d-flex align-items-center">
                <i class="bi bi-house-door me-1"></i> Home
            </a>

            <!-- Información del usuario -->
            <div class="text-end me-2">
                <div class="fw-bold text-dark"><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></div>
                <small class="text-muted"><?= htmlspecialchars($_SESSION['usuario']['rol']) ?></small>
            </div>

            <!-- Botón de notificaciones -->
            <button id="btnNotificaciones" class="btn btn-sm btn-outline-warning position-relative" aria-label="Notificaciones">
                <i class="bi bi-bell-fill"></i>
                <span id="contadorNotificaciones" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    0
                </span>
            </button>

            <!-- Botón logout -->
            <button id="cerrarSession" class="btn btn-sm btn-outline-danger d-flex align-items-center">
                <i class="bi bi-box-arrow-right me-1"></i> Salir
            </button>
        </div>
    </div>
</nav>

</nav>
<!-- Toast container global -->
<div id="toast-container"></div>