<!-- Topbar -->
<nav id="navbar" class="navbar position-fixed navbar-expand navbar-dark shadow-sm px-3">

    <div class="container-fluid d-flex align-items-center py-2 border-bottom">
        <!-- Botón toggle sidebar -->
        <button id="toggleSidebar" class="btn btn-outline-dark d-flex align-items-center me-3"
            aria-label="Menú lateral">
            <i class="bi bi-list fs-4"></i>
        </button>

        <!-- Logo y título -->
        <span class="navbar-brand d-flex align-items-center text-dark fw-semibold mb-0 fs-5">
            <i class="bi bi-hospital-fill me-2"></i>
            <?= htmlspecialchars($_SESSION['usuario']['rol']) ?>
        </span>

        <!-- Elementos al extremo derecho -->
        <div class="ms-auto d-flex align-items-center gap-3">
            <!-- Información usuario -->
            <div class="text-end">
                <div class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['usuario']['nombre']) ?></div>
                <small class="text-muted text-capitalize"><?= htmlspecialchars($_SESSION['usuario']['rol']) ?></small>
            </div>
        </div>
    </div>

</nav>


<!-- Toast container global -->
<div id="toast-container"></div>