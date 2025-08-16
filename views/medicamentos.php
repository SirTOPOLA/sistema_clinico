


<?php
$idUsuario = $_SESSION['usuario']['id'] ?? 0;
$rol = strtolower(trim($_SESSION['usuario']['rol'] ?? ''));

// Consulta para productos de farmacia
$sql = "SELECT * FROM productos_farmacia ORDER BY fecha_registro DESC";
$productos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="content" class="container-fluid">
  <div class="row mb-3">
    <div class="col-md-6 d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0"><i class="bi bi-capsule me-2"></i>Listado de Productos</h3>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearProducto">
        <i class="bi bi-plus-circle-fill me-1"></i> Nuevo Producto
      </button>
    </div>
    <div class="col-md-4">
      <input type="text" id="buscador" class="form-control" placeholder="Buscar producto...">
    </div>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php
  function limitarTexto($texto, $limite = 100)
  {
    $textoPlano = strip_tags($texto);
    return strlen($textoPlano) > $limite
      ? substr($textoPlano, 0, $limite) . '...'
      : $textoPlano;
  }
  ?>

  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaProductos" class="table table-hover table-bordered align-middle table-sm">
          <thead class="table-light text-nowrap">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Descripción</th>
              <th>Código de Barras</th>
              <th>Stock</th>
              <th>Precios</th>
              <th>Vencimiento</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($productos as $p): ?>
              <tr>
                <td><?= (int)$p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= nl2br(htmlspecialchars(limitarTexto($p['descripcion'], 60))) ?></td>
                <td><?= htmlspecialchars($p['codigo_barras'] ?? '-') ?></td>
                <td>
                  <small>
                    Caja: <?= $p['stock_caja'] ?> |
                    Frasco: <?= $p['stock_frasco'] ?> |
                    Tira: <?= $p['stock_tira'] ?> |
                    Pastilla: <?= $p['stock_pastilla'] ?>
                  </small>
                </td>
                <td>
                  <small>
                    Caja: $<?= $p['precio_caja'] ?> |
                    Frasco: $<?= $p['precio_frasco'] ?> <br>
                    Tira: $<?= $p['precio_tira'] ?> |
                    Pastilla: $<?= $p['precio_pastilla'] ?>
                  </small>
                </td>
                <td><?= $p['fecha_vencimiento'] ? date('d/m/Y', strtotime($p['fecha_vencimiento'])) : '-' ?></td>
                <td class="text-nowrap">
                  <button
                    class="btn btn-sm btn-outline-primary me-1"
                    title="Editar"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEditarProducto"
                    data-id="<?= $p['id'] ?>"
                    data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>"
                    data-descripcion="<?= htmlspecialchars($p['descripcion'], ENT_QUOTES) ?>"
                    data-codigo_barras="<?= htmlspecialchars($p['codigo_barras'], ENT_QUOTES) ?>"
                    data-stock_caja="<?= $p['stock_caja'] ?>"
                    data-stock_frasco="<?= $p['stock_frasco'] ?>"
                    data-stock_tira="<?= $p['stock_tira'] ?>"
                    data-stock_pastilla="<?= $p['stock_pastilla'] ?>"
                    data-precio_caja="<?= $p['precio_caja'] ?>"
                    data-precio_frasco="<?= $p['precio_frasco'] ?>"
                    data-precio_tira="<?= $p['precio_tira'] ?>"
                    data-precio_pastilla="<?= $p['precio_pastilla'] ?>"
                    data-fecha_vencimiento="<?= $p['fecha_vencimiento'] ?>">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <?php if($rol === 'administrador'): ?>
                  <a href="eliminar_producto.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger"
                  onclick="return confirm('¿Deseas eliminar este producto?')" title="Eliminar">
                    <i class="bi bi-trash"></i>
                  </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody> 
        </table>
      </div>
    </div>
  </div>
</div>
