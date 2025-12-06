<style>
    body {
        background: #0f172a;
    }

    /* slate-900 */
    .app-shell {
        min-height: 100vh;
    }

    .glass {
        background: rgba(255, 255, 255, .05);
        backdrop-filter: blur(8px);
    }

    .card {
        border: 0;
        border-radius: 1rem;
    }

    .card-header {
        border: 0;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
    }

    .nav-pills .nav-link {
        border-radius: 0.75rem;
    }

    .nav-pills .nav-link.active {
        background: linear-gradient(135deg, #2563eb, #22c55e);
    }

    /* azul->verde */
    .kpi {
        color: #e2e8f0;
    }

    .kpi .value {
        font-size: 1.4rem;
        font-weight: 700;
    }

    .kpi .label {
        font-size: .85rem;
        color: #94a3b8;
    }

    .table thead th {
        color: #94a3b8;
    }

    .table {
        --bs-table-bg: transparent;
        color: #e2e8f0;
    }

    .table-hover tbody tr:hover {
        background: rgba(255, 255, 255, .04);
    }

    .btn-soft {
        background: rgba(255, 255, 255, .08);
        color: #e2e8f0;
        border: 0;
    }

    .btn-soft:hover {
        background: rgba(255, 255, 255, .12);
        color: #fff;
    }

    .form-control,
    .form-select {
        background: #0b1220;
        border: 1px solid #1f2937;
        color: #e5e7eb;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .25)
    }

    .badge-soft {
        background: rgba(255, 255, 255, .08);
        color: #cbd5e1
    }

    .offcanvas {
        background: #0b1220;
        color: #e5e7eb
    }
</style>