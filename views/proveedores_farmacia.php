<?php
try {

  // Consulta real a la base de datos
  $stmt = $pdo->query("SELECT id, nombre, direccion, telefono, contacto FROM proveedores ORDER BY nombre");
  $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Datos simulados para que la vista funcione sin una base de datos real
/*   $proveedores = [
    ['id' => 1, 'nombre' => 'Laboratorios ABC', 'direccion' => 'Calle Falsa 123', 'telefono' => '555-1234', 'contacto' => 'Juan Pérez'],
    ['id' => 2, 'nombre' => 'Distribuidores XYZ', 'direccion' => 'Avenida Siempre Viva 456', 'telefono' => '555-5678', 'contacto' => 'Ana Gómez'],
    ['id' => 3, 'nombre' => 'Farmaceutica LMN', 'direccion' => 'Plaza Central 789', 'telefono' => '555-9012', 'contacto' => 'Carlos Ruíz'],
  ]; */

} catch (PDOException $e) {
  $proveedores = [];
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
      <h3><i class="bi bi-truck me-2"></i>Gestión de Proveedores</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearProveedor">
        <i class="bi bi-plus-circle me-1"></i>Crear Proveedor
      </button>
    </div>
    <div class="col-md-4 offset-md-2">
      <input type="text" id="buscador" class="form-control" placeholder="Buscar proveedor...">
    </div>
  </div>

  <!-- Mensajes de alerta -->
  <?php if ($mensaje_error): ?>
    <div id="mensaje" class="alert alert-danger"><?= htmlspecialchars($mensaje_error) ?></div>
  <?php endif; ?>
  <?php if ($mensaje_exito): ?>
    <div id="mensaje" class="alert alert-success"><?= htmlspecialchars($mensaje_exito) ?></div>
  <?php endif; ?>

  <!-- Tabla de Proveedores -->
  <div class="card border-0 shadow-sm">
    <div class="card-body table-responsive">
      <table id="tablaProveedores" class="table table-hover table-bordered table-sm align-middle">
        <thead class="table-light text-nowrap">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Contacto</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($proveedores as $proveedor): ?>
            <tr>
              <td><?= htmlspecialchars($proveedor['id']) ?></td>
              <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
              <td><?= htmlspecialchars($proveedor['direccion']) ?></td>
              <td><?= htmlspecialchars($proveedor['telefono']) ?></td>
              <td><?= htmlspecialchars($proveedor['contacto']) ?></td>
              <td class="text-nowrap">
                <button class="btn btn-sm btn-outline-primary btn-editar-proveedor"
                  data-id="<?= htmlspecialchars($proveedor['id']) ?>"
                  data-nombre="<?= htmlspecialchars($proveedor['nombre']) ?>"
                  data-direccion="<?= htmlspecialchars($proveedor['direccion']) ?>"
                  data-telefono="<?= htmlspecialchars($proveedor['telefono']) ?>"
                  data-contacto="<?= htmlspecialchars($proveedor['contacto']) ?>" data-bs-toggle="modal"
                  data-bs-target="#modalEditarProveedor">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <a href="api/eliminar_proveedor.php?id=<?= htmlspecialchars($proveedor['id']) ?>"
                  class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('¿Está seguro de eliminar a este proveedor?')">
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

<!-- Modal Crear Proveedor -->
<div class="modal fade" id="modalCrearProveedor" tabindex="-1" aria-labelledby="modalCrearProveedorLabel"
  aria-hidden="true">
  <div class="modal-dialog">
    <form action="api/guardar_proveedor.php" method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalCrearProveedorLabel"><i class="bi bi-plus-circle me-2"></i>Nuevo Proveedor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body row g-3">
        <div class="col-md-12">
          <label for="nombre_crear" class="form-label">Nombre</label>
          <input type="text" name="nombre" id="nombre_crear" class="form-control" required>
        </div>
        <div class="col-md-12">
          <label for="direccion_crear" class="form-label">Dirección (opcional)</label>
          <input type="text" name="direccion" id="direccion_crear" class="form-control">
        </div>
        <div class="col-md-12">
          <label for="telefono_crear" class="form-label">Teléfono (opcional)</label>
          <input type="tel" name="telefono" id="telefono_crear" class="form-control">
        </div>
        <div class="col-md-12">
          <label for="contacto_crear" class="form-label">Contacto (opcional)</label>
          <input type="text" name="contacto" id="contacto_crear" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Editar Proveedor -->
<div class="modal fade" id="modalEditarProveedor" tabindex="-1" aria-labelledby="modalEditarProveedorLabel"
  aria-hidden="true">
  <div class="modal-dialog">
    <form action="api/actualizar_proveedor.php" method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalEditarProveedorLabel"><i class="bi bi-pencil-square me-2"></i>Editar Proveedor
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id" id="edit-id">
        <div class="col-md-12">
          <label for="edit-nombre" class="form-label">Nombre</label>
          <input type="text" name="nombre" id="edit-nombre" class="form-control" required>
        </div>
        <div class="col-md-12">
          <label for="edit-direccion" class="form-label">Dirección (opcional)</label>
          <input type="text" name="direccion" id="edit-direccion" class="form-control">
        </div>
        <div class="col-md-12">
          <label for="edit-telefono" class="form-label">Teléfono (opcional)</label>
          <input type="tel" name="telefono" id="edit-telefono" class="form-control">
        </div>
        <div class="col-md-12">
          <label for="edit-contacto" class="form-label">Contacto (opcional)</label>
          <input type="text" name="contacto" id="edit-contacto" class="form-control">
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
    const botonesEditar = document.querySelectorAll('.btn-editar-proveedor');
    botonesEditar.forEach(btn => {
      btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        const nombre = this.getAttribute('data-nombre');
        const direccion = this.getAttribute('data-direccion');
        const telefono = this.getAttribute('data-telefono');
        const contacto = this.getAttribute('data-contacto');

        document.getElementById('edit-id').value = id;
        document.getElementById('edit-nombre').value = nombre;
        document.getElementById('edit-direccion').value = direccion;
        document.getElementById('edit-telefono').value = telefono;
        document.getElementById('edit-contacto').value = contacto;
      });
    });

    // Lógica para el buscador de la tabla
    const buscador = document.getElementById('buscador');
    const tabla = document.getElementById('tablaProveedores');
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