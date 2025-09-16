<?php
function getTableData($pdo, $table, $limit = 20) {
    try {
        switch ($table) {
            case 'productos':
                $sql = "
                    SELECT 
                        p.*,
                        c.nombre AS categoria_nombre,
                        u.nombre AS unidad_nombre
                    FROM productos p
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    LEFT JOIN unidades_medida u ON p.unidad_id = u.id
                    LIMIT :limit
                ";
                break;

            case 'categorias':
                $sql = "SELECT * FROM categorias LIMIT :limit";
                break;

            case 'unidades_medida':
                $sql = "SELECT * FROM unidades_medida LIMIT :limit";
                break;

            case 'proveedores':
                $sql = "SELECT * FROM proveedores LIMIT :limit";
                break;

            default:
                return [];
        }

        // Preparar y ejecutar consulta
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        // Retornar resultados como array asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // Manejo de errores
        echo "Error al obtener datos de la tabla '$table': " . $e->getMessage();
        return [];
    }
}

$productosData = getTableData($pdo, 'productos');
$categoriasData = getTableData($pdo, 'categorias');
$unidadesData = getTableData($pdo, 'unidades_medida');
$proveedoresData = getTableData($pdo, 'proveedores');

?>

   <style>
        :root {
            --bs-body-bg: #f8f9fa;
            --bs-body-color: #333;
        }
        body {
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .nav-pills .nav-link {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
        }
        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
        }
        .table thead th {
            border-bottom: 2px solid #e9ecef;
        }
        .table td {
            vertical-align: middle;
        }
        .btn-action {
            border-radius: 50%;
            width: 38px;
            height: 38px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .offcanvas {
            background-color: #fff;
            border-left: 1px solid #e9ecef;
            box-shadow: -4px 0 12px rgba(0, 0, 0, 0.05);
        }
        .offcanvas-header, .offcanvas-body {
            color: #333;
        }
    </style>
 

<div id="content" class="container-fluid py-4">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-light text-primary"><i class="bi bi-box-seam me-2"></i> Gestión de Inventario</h2>
        <button class="btn btn-outline-primary rounded-pill" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAcciones">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Registro
        </button>
    </header>

    <hr>

    <!-- Navegación y Tablas -->
    <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-productos-tab" data-bs-toggle="pill" data-bs-target="#pills-productos" type="button"><i class="bi bi-box me-2"></i>Productos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-categorias-tab" data-bs-toggle="pill" data-bs-target="#pills-categorias" type="button"><i class="bi bi-tags me-2"></i>Categorías</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-unidades-tab" data-bs-toggle="pill" data-bs-target="#pills-unidades" type="button"><i class="bi bi-rulers me-2"></i>Unidades</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-proveedores-tab" data-bs-toggle="pill" data-bs-target="#pills-proveedores" type="button"><i class="bi bi-truck me-2"></i>Proveedores</button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <!-- Productos -->
        <div class="tab-pane fade show active" id="pills-productos" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Lista de Productos</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Concentración</th>
                                <th>Categoría</th>
                                <th>Unidad</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productosData as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['id']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['concentracion']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['unidad_nombre']); ?></td>
                                    <td><?php echo 'XAF ' . number_format($producto['precio_unitario'], 2, '.', ','); ?></td>
                                    <td><?php echo htmlspecialchars($producto['stock_actual']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action" onclick='showEditProductoModal(<?php echo json_encode($producto); ?>, <?php echo json_encode($categoriasData); ?>, <?php echo json_encode($unidadesData); ?>)'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-outline-danger btn-sm btn-action"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Categorías -->
        <div class="tab-pane fade" id="pills-categorias" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Lista de Categorías</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categoriasData as $categoria): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($categoria['id']); ?></td>
                                    <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($categoria['descripcion']); ?></td> 
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action" onclick='showEditCategoriaModal(<?php echo json_encode($categoria); ?>)'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-outline-danger btn-sm btn-action"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Unidades de Medida -->
        <div class="tab-pane fade" id="pills-unidades" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Lista de Unidades de Medida</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Abreviatura</th>
                                 
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($unidadesData as $unidad): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($unidad['id']); ?></td>
                                    <td><?php echo htmlspecialchars($unidad['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($unidad['abreviatura']); ?></td>
                                     
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action" onclick='showEditUnidadModal(<?php echo json_encode($unidad); ?>)'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-outline-danger btn-sm btn-action"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Proveedores -->
        <div class="tab-pane fade" id="pills-proveedores" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Lista de Proveedores</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedoresData as $proveedor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($proveedor['id']); ?></td>
                                    <td><?php echo htmlspecialchars($proveedor['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($proveedor['contacto']); ?></td>
                                    <td><?php echo htmlspecialchars($proveedor['telefono']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action" onclick='showEditProveedorModal(<?php echo json_encode($proveedor); ?>)'><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-outline-danger btn-sm btn-action"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Offcanvas Acciones -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAcciones">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title text-muted">Acciones rápidas</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalProducto"><i class="bi bi-box me-2"></i> Registrar Producto</button>
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalCategoria"><i class="bi bi-tags me-2"></i> Registrar Categoría</button>
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalUnidad"><i class="bi bi-rulers me-2"></i> Registrar Unidad</button>
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalProveedor"><i class="bi bi-truck me-2"></i> Registrar Proveedor</button>
        </div>
    </div>
</div>

<!-- Modales de Registro -->
<?php require 'modals/modals_registro_farmacia.php'; ?>

<!-- Modales de Edición -->
<?php require 'modals/modals_edicion_farmacia.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showEditProductoModal(producto, categorias, unidades) {
        document.getElementById('edit_producto_id').value = producto.id;
        document.getElementById('edit_nombreProducto').value = producto.nombre;
        document.getElementById('edit_concentracionProducto').value = producto.concentracion;
        document.getElementById('edit_formaFarmaceutica').value = producto.forma_farmaceutica;
        document.getElementById('edit_presentacionProducto').value = producto.presentacion;
        document.getElementById('edit_precioProducto').value = producto.precio_unitario;
        document.getElementById('edit_stockActual').value = producto.stock_actual;
        document.getElementById('edit_stockMinimo').value = producto.stock_minimo;

        // Populate and select categories dropdown
        const categoriaSelect = document.getElementById('edit_categoriaProducto');
        categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
        categorias.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria.id;
            option.text = categoria.nombre;
            if (categoria.id === producto.categoria_id) {
                option.selected = true;
            }
            categoriaSelect.appendChild(option);
        });

        // Populate and select units dropdown
        const unidadSelect = document.getElementById('edit_unidadProducto');
        unidadSelect.innerHTML = '<option value="">Seleccione una unidad</option>';
        unidades.forEach(unidad => {
            const option = document.createElement('option');
            option.value = unidad.id;
            option.text = unidad.nombre;
            if (unidad.id === producto.unidad_id) {
                option.selected = true;
            }
            unidadSelect.appendChild(option);
        });

        const modal = new bootstrap.Modal(document.getElementById('modalEditProducto'));
        modal.show();
    }
    
    function showEditCategoriaModal(categoria) {
        document.getElementById('edit_categoria_id').value = categoria.id;
        document.getElementById('edit_nombreCategoria').value = categoria.nombre;
        document.getElementById('edit_descripcionCategoria').value = categoria.descripcion;
        const modal = new bootstrap.Modal(document.getElementById('modalEditCategoria'));
        modal.show();
    }
    
    function showEditUnidadModal(unidad) {
        document.getElementById('edit_unidad_id').value = unidad.id;
        document.getElementById('edit_nombreUnidad').value = unidad.nombre;
        document.getElementById('edit_abreviaturaUnidad').value = unidad.abreviatura;
        const modal = new bootstrap.Modal(document.getElementById('modalEditUnidad'));
        modal.show();
    }
    
    function showEditProveedorModal(proveedor) {
        document.getElementById('edit_proveedor_id').value = proveedor.id;
        document.getElementById('edit_nombreProveedor').value = proveedor.nombre;
        document.getElementById('edit_telefonoProveedor').value = proveedor.telefono;
        document.getElementById('edit_contactoProveedor').value = proveedor.contacto;
        const modal = new bootstrap.Modal(document.getElementById('modalEditProveedor'));
        modal.show();
    }
</script>

 