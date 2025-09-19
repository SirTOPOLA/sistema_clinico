<?php
function getTableData($pdo, $table, $limit = 20)
{
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
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
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

    .offcanvas-header,
    .offcanvas-body {
        color: #333;
    }
</style>


<div id="content" class="container-fluid py-4">
    <header class="d-flex justify-content-between align-items-center mb-5">
        <h2 class="fw-light text-primary"><i class="bi bi-box-seam me-2"></i> Gestión de Inventario</h2>
        <button class="btn btn-outline-primary rounded-pill" data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasAcciones">
            <i class="bi bi-plus-circle me-2"></i> Nuevo Registro
        </button>
    </header>

    <hr>

    <!-- Navegación y Tablas -->
    <ul class="nav nav-pills mb-3 justify-content-center" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-productos-tab" data-bs-toggle="pill"
                data-bs-target="#pills-productos" type="button"><i class="bi bi-box me-2"></i>Productos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-categorias-tab" data-bs-toggle="pill" data-bs-target="#pills-categorias"
                type="button"><i class="bi bi-tags me-2"></i>Categorías</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-unidades-tab" data-bs-toggle="pill" data-bs-target="#pills-unidades"
                type="button"><i class="bi bi-rulers me-2"></i>Unidades</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-proveedores-tab" data-bs-toggle="pill"
                data-bs-target="#pills-proveedores" type="button"><i class="bi bi-truck me-2"></i>Proveedores</button>
        </li>
    </ul>









    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-productos" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Lista de Productos</h4>
             <div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead class="table-light text-nowrap">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Concentración</th>
                <th>Forma Farmacéutica</th>
                <th>Presentación</th>
                <th>Categoría</th>
                <th>Unidad</th>
                <th>Precio Unitario</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productosData as $producto): ?>
                <tr>
                    <td><?= htmlspecialchars($producto['id']) ?></td>
                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                    <td>
                        <?= htmlspecialchars($producto['concentracion']) ?: 
                            '<i class="bi bi-lock-fill text-muted" title="Sin concentración"></i>' ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($producto['forma_farmaceutica']) ?: 
                            '<i class="bi bi-lock-fill text-muted" title="Sin forma farmacéutica"></i>' ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($producto['presentacion']) ?: 
                            '<i class="bi bi-lock-fill text-muted" title="Sin presentación"></i>' ?>
                    </td>
                    <td><?= htmlspecialchars($producto['categoria_nombre']) ?: 
                        '<i class="bi bi-lock-fill text-muted" title="Sin categoría"></i>' ?>
                    </td>
                    <td><?= htmlspecialchars($producto['unidad_nombre']) ?: 
                        '<i class="bi bi-lock-fill text-muted" title="Sin unidad"></i>' ?>
                    </td>
                    <td>
                        <?= ($producto['precio_unitario'] !== NULL) ? 
                            'XAF ' . number_format($producto['precio_unitario'], 0) : 
                            '<i class="bi bi-lock-fill text-muted" title="Sin precio unitario"></i>' ?>
                    </td>
                    <td>
                        <?php
                            $stock_actual = (int) $producto['stock_actual'];
                            $stock_minimo = (int) $producto['stock_minimo'];
                            $porcentaje = ($stock_minimo > 0) ? min(100, ($stock_actual / $stock_minimo) * 100) : 100;
                            $color = ($porcentaje <= 25) ? 'bg-danger' : (($porcentaje <= 50) ? 'bg-warning' : 'bg-success');
                        ?>
                        <?= htmlspecialchars($stock_actual) ?>
                        <div class="progress mt-1" style="height: 5px;">
                            <div class="progress-bar <?= $color ?>" role="progressbar"
                                 style="width: <?= $porcentaje ?>%;" aria-valuenow="<?= $porcentaje ?>"
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($stock_minimo) ?></td>
                    <td class="text-nowrap">
                        <button class="btn btn-sm btn-outline-primary btn-editar-producto"
                                data-item='<?= json_encode($producto) ?>' data-bs-toggle="modal"
                                data-bs-target="#modalEditarProducto">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <a href="api/eliminar_producto.php?id=<?= htmlspecialchars($producto['id']) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('¿Está seguro de eliminar este producto? Esta acción no se puede deshacer.')">
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

        <div class="tab-pane fade" id="pills-categorias" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Lista de Categorías</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                        </thead>
                        <tbody>
                            <?php foreach ($categoriasData as $categoria): ?>
                                <tr>
                                    <td><?= htmlspecialchars($categoria['id']); ?></td>
                                    <td><?= htmlspecialchars($categoria['nombre']); ?></td>
                                    <td><?= htmlspecialchars($categoria['descripcion']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action btn-editar-categoria"
                                            data-item='<?= json_encode($categoria); ?>' data-bs-toggle="modal"
                                            data-bs-target="#modalEditCategoria">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm btn-action"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pills-unidades" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Lista de Unidades de Medida</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                        </thead>
                        <tbody>
                            <?php foreach ($unidadesData as $unidad): ?>
                                <tr>
                                    <td><?= htmlspecialchars($unidad['id']); ?></td>
                                    <td><?= htmlspecialchars($unidad['nombre']); ?></td>
                                    <td><?= htmlspecialchars($unidad['abreviatura']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action btn-editar-unidad"
                                            data-item='<?= json_encode($unidad); ?>' data-bs-toggle="modal"
                                            data-bs-target="#modalEditUnidad">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm btn-action"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pills-proveedores" role="tabpanel">
            <div class="card p-4">
                <h4 class="mb-3 text-muted">Lista de Proveedores</h4>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedoresData as $proveedor): ?>
                                <tr>
                                    <td><?= htmlspecialchars($proveedor['id']); ?></td>
                                    <td><?= htmlspecialchars($proveedor['nombre']); ?></td>
                                    <td><?= htmlspecialchars($proveedor['contacto']); ?></td>
                                    <td><?= htmlspecialchars($proveedor['telefono']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-warning btn-sm btn-action btn-editar-proveedor"
                                            data-item='<?= json_encode($proveedor); ?>' data-bs-toggle="modal"
                                            data-bs-target="#modalEditProveedor">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm btn-action"><i
                                                class="bi bi-trash"></i></button>
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
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalProducto"><i
                    class="bi bi-box me-2"></i> Registrar Producto</button>
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalCategoria"><i
                    class="bi bi-tags me-2"></i> Registrar Categoría</button>
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalUnidad"><i
                    class="bi bi-rulers me-2"></i> Registrar Unidad</button>
            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#modalProveedor"><i
                    class="bi bi-truck me-2"></i> Registrar Proveedor</button>
        </div>
    </div>
</div>

<!-- Modales de Registro -->
<?php require 'modals/modals_registro_farmacia.php'; ?>

<!-- Modales de Edición -->
<?php require 'modals/modals_edicion_farmacia.php'; ?>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Obtenemos los datos de las tablas del DOM para usarlos en el modal de productos
        const categoriasData = <?= json_encode($categoriasData); ?>;
        const unidadesData = <?= json_encode($unidadesData); ?>;

        /**
         * Maneja el clic en los botones de edición de PRODUCTOS.
         * Rellena el modal con los datos del producto seleccionado.
         */
        document.querySelectorAll('.btn-editar-producto').forEach(button => {
            button.addEventListener('click', function () {
                const producto = JSON.parse(this.dataset.item);

                document.getElementById('edit_producto_id').value = producto.id;
                document.getElementById('edit_nombreProducto').value = producto.nombre;
                document.getElementById('edit_concentracionProducto').value = producto.concentracion;
                document.getElementById('edit_formaFarmaceutica').value = producto.forma_farmaceutica;
                document.getElementById('edit_presentacionProducto').value = producto.presentacion;
                document.getElementById('edit_precioProducto').value = producto.precio_unitario;
                document.getElementById('edit_stockActual').value = producto.stock_actual;
                document.getElementById('edit_stockMinimo').value = producto.stock_minimo;

                // Rellenar y seleccionar el dropdown de categorías
                const categoriaSelect = document.getElementById('edit_categoriaProducto');
                categoriaSelect.innerHTML = '<option value="">Seleccione una categoría</option>';
                categoriasData.forEach(categoria => {
                    const option = document.createElement('option');
                    option.value = categoria.id;
                    option.text = categoria.nombre;
                    if (categoria.id === producto.categoria_id) {
                        option.selected = true;
                    }
                    categoriaSelect.appendChild(option);
                });

                // Rellenar y seleccionar el dropdown de unidades
                const unidadSelect = document.getElementById('edit_unidadProducto');
                unidadSelect.innerHTML = '<option value="">Seleccione una unidad</option>';
                unidadesData.forEach(unidad => {
                    const option = document.createElement('option');
                    option.value = unidad.id;
                    option.text = unidad.nombre;
                    if (unidad.id === producto.unidad_id) {
                        option.selected = true;
                    }
                    unidadSelect.appendChild(option);
                });
            });
        });

        /**
         * Maneja el clic en los botones de edición de CATEGORÍAS.
         * Rellena el modal con los datos de la categoría seleccionada.
         */
        document.querySelectorAll('.btn-editar-categoria').forEach(button => {
            button.addEventListener('click', function () {
                const categoria = JSON.parse(this.dataset.item);
                document.getElementById('edit_categoria_id').value = categoria.id;
                document.getElementById('edit_nombreCategoria').value = categoria.nombre;
                document.getElementById('edit_descripcionCategoria').value = categoria.descripcion;
            });
        });

        /**
         * Maneja el clic en los botones de edición de UNIDADES DE MEDIDA.
         * Rellena el modal con los datos de la unidad seleccionada.
         */
        document.querySelectorAll('.btn-editar-unidad').forEach(button => {
            button.addEventListener('click', function () {
                const unidad = JSON.parse(this.dataset.item);
                document.getElementById('edit_unidad_id').value = unidad.id;
                document.getElementById('edit_nombreUnidad').value = unidad.nombre;
                document.getElementById('edit_abreviaturaUnidad').value = unidad.abreviatura;
            });
        });

        /**
         * Maneja el clic en los botones de edición de PROVEEDORES.
         * Rellena el modal con los datos del proveedor seleccionado.
         */
        document.querySelectorAll('.btn-editar-proveedor').forEach(button => {
            button.addEventListener('click', function () {
                const proveedor = JSON.parse(this.dataset.item);
                document.getElementById('edit_proveedor_id').value = proveedor.id;
                document.getElementById('edit_nombreProveedor').value = proveedor.nombre;
                document.getElementById('edit_telefonoProveedor').value = proveedor.telefono;
                document.getElementById('edit_contactoProveedor').value = proveedor.contacto;
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
    // ... tu código existente para los modales de edición ...

    /**
     * Maneja la visibilidad de los campos opcionales en el modal de registro.
     */
    function setupToggle(checkboxId, wrapperId, inputId) {
        const checkbox = document.getElementById(checkboxId);
        const wrapper = document.getElementById(wrapperId);
        const input = document.getElementById(inputId);

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                wrapper.style.display = 'block';
                input.required = true;
                input.value = ''; // Limpiar el valor si se vuelve a activar
            } else {
                wrapper.style.display = 'none';
                input.required = false;
                input.value = ''; // Asegurar que el campo se envíe vacío
            }
        });
        // Estado inicial al cargar la página
        if (!checkbox.checked) {
            wrapper.style.display = 'none';
            input.required = false;
        }
    }

    setupToggle('checkConcentracionCrear', 'wrapperConcentracionCrear', 'concentracion_crear');
    setupToggle('checkFormaCrear', 'wrapperFormaCrear', 'forma_farmaceutica_crear');
    setupToggle('checkPresentacionCrear', 'wrapperPresentacionCrear', 'presentacion_crear');
    setupToggle('checkPrecioCrear', 'wrapperPrecioCrear', 'precio_unitario_crear');
});
</script>


