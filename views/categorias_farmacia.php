<?php
 
try { 

    // Consulta real a la base de datos
     $stmt = $pdo->query("SELECT id, nombre, descripcion FROM categorias ORDER BY nombre");
     $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Datos simulados para que la vista funcione sin una base de datos real
  /*   $categorias = [
        ['id' => 1, 'nombre' => 'Analgésico', 'descripcion' => 'Medicamentos para aliviar el dolor.'],
        ['id' => 2, 'nombre' => 'Antibiótico', 'descripcion' => 'Medicamentos para combatir infecciones bacterianas.'],
        ['id' => 3, 'nombre' => 'Antifebril', 'descripcion' => 'Medicamentos para reducir la fiebre.'],
        ['id' => 4, 'nombre' => 'Vitaminas', 'descripcion' => 'Suplementos nutricionales.'],
    ]; */

} catch (PDOException $e) {
    $categorias = [];
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
            <h3><i class="bi bi-tags me-2"></i>Gestión de Categorías</h3>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
                <i class="bi bi-plus-circle me-1"></i>Crear Categoría
            </button>
        </div>
        <div class="col-md-4 offset-md-2">
            <input type="text" id="buscador" class="form-control" placeholder="Buscar categoría...">
        </div>
    </div>

    <!-- Mensajes de alerta -->
    <?php if ($mensaje_error): ?>
        <div id="mensaje" class="alert alert-danger"><?= htmlspecialchars($mensaje_error) ?></div>
    <?php endif; ?>
    <?php if ($mensaje_exito): ?>
        <div id="mensaje" class="alert alert-success"><?= htmlspecialchars($mensaje_exito) ?></div>
    <?php endif; ?>

    <!-- Tabla de Categorías -->
    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive">
            <table id="tablaCategorias" class="table table-hover table-bordered table-sm align-middle">
                <thead class="table-light text-nowrap">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td><?= htmlspecialchars($categoria['id']) ?></td>
                            <td><?= htmlspecialchars($categoria['nombre']) ?></td>
                            <td><?= htmlspecialchars($categoria['descripcion']) ?></td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-primary btn-editar-categoria"
                                    data-id="<?= htmlspecialchars($categoria['id']) ?>"
                                    data-nombre="<?= htmlspecialchars($categoria['nombre']) ?>"
                                    data-descripcion="<?= htmlspecialchars($categoria['descripcion']) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarCategoria">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="api/eliminar_categoria.php?id=<?= htmlspecialchars($categoria['id']) ?>"
                                    class="btn btn-sm btn-outline-danger"
                                    onclick="return confirm('¿Está seguro de eliminar esta categoría? Esto podría afectar a los productos asociados.')">
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

<!-- Modal Crear Categoría -->
<div class="modal fade" id="modalCrearCategoria" tabindex="-1" aria-labelledby="modalCrearCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="api/guardar_categoria.php" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalCrearCategoriaLabel"><i class="bi bi-plus-circle me-2"></i>Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <div class="col-md-12">
                    <label for="nombre_crear" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="nombre_crear" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label for="descripcion_crear" class="form-label">Descripción (opcional)</label>
                    <textarea name="descripcion" id="descripcion_crear" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Categoría -->
<div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-labelledby="modalEditarCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="api/actualizar_categoria.php" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarCategoriaLabel"><i class="bi bi-pencil-square me-2"></i>Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-3">
                <input type="hidden" name="id" id="edit-id">
                <div class="col-md-12">
                    <label for="edit-nombre" class="form-label">Nombre</label>
                    <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label for="edit-descripcion" class="form-label">Descripción (opcional)</label>
                    <textarea name="descripcion" id="edit-descripcion" class="form-control" rows="3"></textarea>
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
        const botonesEditar = document.querySelectorAll('.btn-editar-categoria');
        botonesEditar.forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const descripcion = this.getAttribute('data-descripcion');

                document.getElementById('edit-id').value = id;
                document.getElementById('edit-nombre').value = nombre;
                document.getElementById('edit-descripcion').value = descripcion;
            });
        });

        // Lógica para el buscador de la tabla
        const buscador = document.getElementById('buscador');
        const tabla = document.getElementById('tablaCategorias');
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
