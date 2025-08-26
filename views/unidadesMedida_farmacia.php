<?php
 
try {
    // Aquí iría el código real de la conexión PDO
    // $pdo = new PDO("mysql:host=localhost;dbname=farmacia", "usuario", "contraseña");
    // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta real a la base de datos
    // $stmt = $pdo->query("SELECT id, nombre, abreviatura FROM unidades_medida ORDER BY nombre");
    // $unidadesMedida = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Datos simulados para que la vista funcione sin una base de datos real
    $unidadesMedida = [
        ['id' => 1, 'nombre' => 'Miligramo', 'abreviatura' => 'mg'],
        ['id' => 2, 'nombre' => 'Tableta', 'abreviatura' => 'tab'],
        ['id' => 3, 'nombre' => 'Mililitro', 'abreviatura' => 'ml'],
        ['id' => 4, 'nombre' => 'Cápsula', 'abreviatura' => 'cap'],
    ];

} catch (PDOException $e) {
    // En caso de error, se puede manejar de la siguiente manera:
    $unidadesMedida = [];
    $mensaje_error = "Error al conectar a la base de datos: " . $e->getMessage();
}

// Mensajes de alerta simulados, normalmente se obtendrían de la sesión.
$mensaje_error = $_SESSION['error'] ?? null;
$mensaje_exito = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>

<div class="container-fluid" id="content">

    <!-- Encabezado y buscador -->
    <div class="row mb-3 align-items-center">
        <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
            <h3><i class="bi bi-rulers me-2"></i>Gestión de Unidades de Medida</h3>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearUnidad">
                <i class="bi bi-plus-circle me-1"></i>Crear Unidad
            </button>
        </div>
        <div class="col-md-4 offset-md-2">
            <input type="text" id="buscador" class="form-control" placeholder="Buscar unidad...">
        </div>
    </div>

    <!-- Mensajes de alerta -->
    <?php if ($mensaje_error): ?>
        <div id="mensaje" class="alert alert-danger"><?= htmlspecialchars($mensaje_error) ?></div>
    <?php endif; ?>
    <?php if ($mensaje_exito): ?>
        <div id="mensaje" class="alert alert-success"><?= htmlspecialchars($mensaje_exito) ?></div>
    <?php endif; ?>

    <!-- Tabla de Unidades de Medida -->
    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table id="tablaUnidades" class="table table-hover table-bordered table-sm align-middle">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Abreviatura</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unidadesMedida as $unidad): ?>
                        <tr>
                            <td><?= htmlspecialchars($unidad['id']) ?></td>
                            <td><?= htmlspecialchars($unidad['nombre']) ?></td>
                            <td><?= htmlspecialchars($unidad['abreviatura']) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-primary btn-editar-unidad"
                                    data-id="<?= htmlspecialchars($unidad['id']) ?>"
                                    data-nombre="<?= htmlspecialchars($unidad['nombre']) ?>"
                                    data-abreviatura="<?= htmlspecialchars($unidad['abreviatura']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarUnidad">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="api/eliminar_unidad.php?id=<?= htmlspecialchars($unidad['id']) ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Está seguro de eliminar esta unidad de medida?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Crear Unidad -->
<div class="modal fade" id="modalCrearUnidad" tabindex="-1" aria-labelledby="modalCrearUnidadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="api/guardar_unidad.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCrearUnidadLabel"><i class="bi bi-plus-circle me-2"></i>Nueva Unidad de Medida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-12">
                    <label for="nombre_crear" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre_crear" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label for="abreviatura_crear" class="form-label">Abreviatura</label>
                    <input type="text" name="abreviatura" id="abreviatura_crear" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Unidad -->
<div class="modal fade" id="modalEditarUnidad" tabindex="-1" aria-labelledby="modalEditarUnidadLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="api/actualizar_unidad.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarUnidadLabel"><i class="bi bi-pencil-square me-2"></i>Editar Unidad de Medida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="edit-id">
                <div class="col-md-12">
                    <label for="edit-nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label for="edit-abreviatura" class="form-label">Abreviatura</label>
                    <input type="text" name="abreviatura" id="edit-abreviatura" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Scripts de JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Lógica para llenar el modal de edición
        const botonesEditar = document.querySelectorAll('.btn-editar-unidad');
        botonesEditar.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const abreviatura = this.getAttribute('data-abreviatura');

                document.getElementById('edit-id').value = id;
                document.getElementById('edit-nombre').value = nombre;
                document.getElementById('edit-abreviatura').value = abreviatura;
            });
        });

        // Lógica para el buscador de la tabla
        const buscador = document.getElementById('buscador');
        const tabla = document.getElementById('tablaUnidades');
        const filas = tabla.getElementsByTagName('tr');

        buscador.addEventListener('keyup', function () {
            const filtro = buscador.value.toLowerCase();
            for (let i = 1; i < filas.length; i++) { // Empezamos en 1 para saltar el thead
                const fila = filas[i];
                const textoFila = fila.textContent.toLowerCase();
                if (textoFila.indexOf(filtro) > -1) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            }
        });

        // Lógica para ocultar los mensajes de alerta automáticamente
        setTimeout(() => {
            const mensaje = document.getElementById('mensaje');
            if (mensaje) {
                mensaje.style.transition = 'opacity 1s ease';
                mensaje.style.opacity = '0';
                setTimeout(() => mensaje.remove(), 1000);
            }
        }, 10000); // 10 segundos
    });
</script>
