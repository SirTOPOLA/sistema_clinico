
<?php
session_start(); // Inicia la sesión para usar mensajes de éxito/error

// Asume que tu archivo de conexión a la base de datos se llama 'conexion.php'
// y que define una variable $pdo para la conexión PDO.
 
require_once '../config/conexion.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    try {
        switch ($action) {
            case 'crear':
                // 1. Obtener y sanear datos de la compra principal
                $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT);
                $id_personal = filter_input(INPUT_POST, 'id_personal', FILTER_VALIDATE_INT);
                $fecha_compra = filter_input(INPUT_POST, 'fecha_compra', FILTER_SANITIZE_STRING);
                $monto_total = filter_input(INPUT_POST, 'monto_total', FILTER_VALIDATE_FLOAT); 
                $adelanto = filter_input(INPUT_POST, 'adelanto', FILTER_VALIDATE_FLOAT);
                $estado_pago = filter_input(INPUT_POST, 'estado_pago', FILTER_SANITIZE_STRING);
                
                // --- Validación de campos principales de la compra ---
                if ($id_proveedor === false || $id_proveedor <= 0) {
                    $_SESSION['error_compra'] = 'El proveedor seleccionado no es válido.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($id_personal === false || $id_personal <= 0) {
                    $_SESSION['error_compra'] = 'El ID de personal no es válido.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if (!DateTime::createFromFormat('Y-m-d', $fecha_compra)) {
                    $_SESSION['error_compra'] = 'La fecha de compra no es válida. Formato esperado: AAAA-MM-DD.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($monto_total === false || $monto_total < 0) {
                    $_SESSION['error_compra'] = 'El monto total de la compra no es válido o es negativo. '.htmlspecialchars($monto_total);
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($adelanto === false || $adelanto < 0) {
                    $_SESSION['error_compra'] = 'El adelanto no es válido o es negativo.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($adelanto > $monto_total) {
                    $_SESSION['error_compra'] = 'El adelanto no puede ser mayor que el monto total de la compra.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                $allowed_estados = ['pendiente', 'pagado', 'parcial'];
                if (!in_array($estado_pago, $allowed_estados)) {
                    $_SESSION['error_compra'] = 'El estado de pago seleccionado no es válido.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                // --- Fin Validación de campos principales ---


                // 2. Insertar la compra principal en 'compras_proveedores'
                $pdo->beginTransaction(); // Inicia una transacción para asegurar la integridad de los datos
                
                $stmt_compra = $pdo->prepare("INSERT INTO compras_proveedores 
                    (id_proveedor, id_personal, fecha_compra, monto_total, adelanto, estado_pago) 
                    VALUES (:id_proveedor, :id_personal, :fecha_compra, :monto_total, :adelanto, :estado_pago)");
                
                $stmt_compra->bindParam(':id_proveedor', $id_proveedor);
                $stmt_compra->bindParam(':id_personal', $id_personal);
                $stmt_compra->bindParam(':fecha_compra', $fecha_compra);
                $stmt_compra->bindParam(':monto_total', $monto_total);
                $stmt_compra->bindParam(':adelanto', $adelanto);
                $stmt_compra->bindParam(':estado_pago', $estado_pago);
                $stmt_compra->execute();
                
                $id_compra = $pdo->lastInsertId(); // Obtener el ID de la compra recién insertada

                // 3. Procesar los productos adquiridos (detalle de la compra)
                $productos_adquiridos = $_POST['productos'] ?? []; // Array de productos del formulario
                
                if (empty($productos_adquiridos)) {
                    $pdo->rollBack();
                    $_SESSION['error_compra'] = 'La compra debe incluir al menos un producto. Por favor, agregue productos.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }

                foreach ($productos_adquiridos as $index => $producto_detalle) {
                    // --- Validación de campos de detalle de producto ---
                    $id_producto = filter_var($producto_detalle['id_producto'], FILTER_VALIDATE_INT);
                    $cantidad = filter_var($producto_detalle['cantidad'], FILTER_VALIDATE_INT);
                    $unidad = filter_var($producto_detalle['unidad'], FILTER_SANITIZE_STRING);
                    $precio_unitario = filter_var($producto_detalle['precio_unitario'], FILTER_VALIDATE_FLOAT);
                    $precio_venta = filter_var($producto_detalle['precio_venta'], FILTER_VALIDATE_FLOAT);
                    
                    $tiras_por_caja_comprada = filter_var($producto_detalle['tiras_por_caja'], FILTER_VALIDATE_INT);
                    $pastillas_por_tira_comprada = filter_var($producto_detalle['pastillas_por_tira'], FILTER_VALIDATE_INT);
                    $pastillas_por_frasco_comprada = filter_var($producto_detalle['pastillas_por_frasco'], FILTER_VALIDATE_INT);
                    $fecha_vencimiento_producto = filter_var($producto_detalle['fecha_vencimiento'], FILTER_SANITIZE_STRING);

                    $current_product_num = $index + 1; // Para mensajes de error más claros

                    if ($id_producto === false || $id_producto <= 0) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: ID de producto inválido.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($cantidad === false || $cantidad <= 0) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: La cantidad debe ser un número entero positivo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    $allowed_unidades = ['caja', 'frasco', 'tira', 'pastilla'];
                    if (empty($unidad) || !in_array($unidad, $allowed_unidades)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Unidad de medida inválida o no seleccionada.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($precio_unitario === false || $precio_unitario < 0) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: El precio unitario de compra no es válido o es negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($precio_venta === false || $precio_venta < 0) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: El precio de venta sugerido no es válido o es negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($tiras_por_caja_comprada === false || $tiras_por_caja_comprada < 0) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Tiras por caja inválidas o negativas.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($pastillas_por_tira_comprada === false || $pastillas_por_tira_comprada < 0) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Pastillas por tira inválidas o negativas.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($pastillas_por_frasco_comprada === false || $pastillas_por_frasco_comprada < 0) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Pastillas por frasco inválidas o negativas.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if (!empty($fecha_vencimiento_producto) && !DateTime::createFromFormat('Y-m-d', $fecha_vencimiento_producto)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: La fecha de vencimiento no es válida. Formato esperado: AAAA-MM-DD.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    // --- Fin Validación de campos de detalle ---

                    // Insertar detalle del producto en 'detalle_compra_proveedores'
                    $stmt_detalle = $pdo->prepare("INSERT INTO detalle_compra_proveedores 
                        (id_compra, id_producto, cantidad, unidad, precio_unitario, precio_venta, 
                        tiras_por_caja_comprada, pastillas_por_tira_comprada, pastillas_por_frasco_comprada, fecha_vencimiento_producto) 
                        VALUES (:id_compra, :id_producto, :cantidad, :unidad, :precio_unitario, :precio_venta, 
                        :tiras_por_caja_comprada, :pastillas_por_tira_comprada, :pastillas_por_frasco_comprada, :fecha_vencimiento_producto)");
                    
                    $stmt_detalle->bindParam(':id_compra', $id_compra);
                    $stmt_detalle->bindParam(':id_producto', $id_producto);
                    $stmt_detalle->bindParam(':cantidad', $cantidad);
                    $stmt_detalle->bindParam(':unidad', $unidad);
                    $stmt_detalle->bindParam(':precio_unitario', $precio_unitario);
                    $stmt_detalle->bindParam(':precio_venta', $precio_venta);
                    $stmt_detalle->bindParam(':tiras_por_caja_comprada', $tiras_por_caja_comprada);
                    $stmt_detalle->bindParam(':pastillas_por_tira_comprada', $pastillas_por_tira_comprada);
                    $stmt_detalle->bindParam(':pastillas_por_frasco_comprada', $pastillas_por_frasco_comprada);
                    // Convertir fecha de vencimiento a NULL si está vacía
                    $fecha_venc_db = !empty($fecha_vencimiento_producto) ? $fecha_vencimiento_producto : null;
                    $stmt_detalle->bindParam(':fecha_vencimiento_producto', $fecha_venc_db);
                    $stmt_detalle->execute();

                    // 4. Actualizar stock, precios de venta y fecha de vencimiento en 'productos_farmacia'
                    $stmt_update_producto = $pdo->prepare("SELECT stock_caja, stock_frasco, stock_tira, stock_pastilla, fecha_vencimiento FROM productos_farmacia WHERE id = :id_producto FOR UPDATE");
                    $stmt_update_producto->bindParam(':id_producto', $id_producto);
                    $stmt_update_producto->execute();
                    $producto_actual = $stmt_update_producto->fetch(PDO::FETCH_ASSOC);

                    if ($producto_actual) {
                        $new_stock_caja = $producto_actual['stock_caja'];
                        $new_stock_frasco = $producto_actual['stock_frasco'];
                        $new_stock_tira = $producto_actual['stock_tira'];
                        $new_stock_pastilla = $producto_actual['stock_pastilla'];

                        switch ($unidad) {
                            case 'caja':
                                $new_stock_caja += $cantidad;
                                break;
                            case 'frasco':
                                $new_stock_frasco += $cantidad;
                                break;
                            case 'tira':
                                $new_stock_tira += $cantidad;
                                break;
                            case 'pastilla':
                                $new_stock_pastilla += $cantidad;
                                break;
                        }

                        // Actualizar la fecha de vencimiento solo si la nueva es más reciente
                        $current_vencimiento = $producto_actual['fecha_vencimiento'];
                        $new_vencimiento = $fecha_venc_db; 

                        if ($current_vencimiento === null || ($new_vencimiento !== null && $new_vencimiento > $current_vencimiento)) {
                            $updated_fecha_vencimiento = $new_vencimiento;
                        } else {
                            $updated_fecha_vencimiento = $current_vencimiento;
                        }

                        // Actualizar precios de venta y conversiones en el producto principal con los precios/conversiones del detalle de compra
                        $stmt_update_prod_data = $pdo->prepare("UPDATE productos_farmacia 
                            SET stock_caja = :stock_caja, stock_frasco = :stock_frasco, 
                                stock_tira = :stock_tira, stock_pastilla = :stock_pastilla,
                                precio_caja = :precio_caja, precio_frasco = :precio_frasco,
                                precio_tira = :precio_tira, precio_pastilla = :precio_pastilla,
                                fecha_vencimiento = :fecha_vencimiento,
                                tiras_por_caja = :tiras_por_caja, pastillas_por_tira = :pastillas_por_tira,
                                pastillas_por_frasco = :pastillas_por_frasco
                            WHERE id = :id_producto");
                        
                        $stmt_update_prod_data->bindParam(':stock_caja', $new_stock_caja);
                        $stmt_update_prod_data->bindParam(':stock_frasco', $new_stock_frasco);
                        $stmt_update_prod_data->bindParam(':stock_tira', $new_stock_tira);
                        $stmt_update_prod_data->bindParam(':stock_pastilla', $new_stock_pastilla);
                        $stmt_update_prod_data->bindParam(':precio_caja', $producto_detalle['precio_venta']); // Usar el precio_venta del detalle
                        $stmt_update_prod_data->bindParam(':precio_frasco', $producto_detalle['precio_venta']); // Asumiendo que precio_venta es el relevante aquí
                        $stmt_update_prod_data->bindParam(':precio_tira', $producto_detalle['precio_venta']); 
                        $stmt_update_prod_data->bindParam(':precio_pastilla', $producto_detalle['precio_venta']); 
                        $stmt_update_prod_data->bindParam(':fecha_vencimiento', $updated_fecha_vencimiento);
                        $stmt_update_prod_data->bindParam(':tiras_por_caja', $tiras_por_caja_comprada);
                        $stmt_update_prod_data->bindParam(':pastillas_por_tira', $pastillas_por_tira_comprada);
                        $stmt_update_prod_data->bindParam(':pastillas_por_frasco', $pastillas_por_frasco_comprada);
                        $stmt_update_prod_data->bindParam(':id_producto', $id_producto);
                        $stmt_update_prod_data->execute();

                    } else {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: No se encontró el producto para actualizar el stock.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                }
                
                $pdo->commit(); 
                $_SESSION['success_compra'] = 'Compra y detalles de productos registrados exitosamente.';
                break;

            case 'editar':
                // Solo se editan los campos principales de la compra. Los detalles de productos no se editan desde aquí.
                $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT);
                $id_personal = filter_input(INPUT_POST, 'id_personal', FILTER_VALIDATE_INT);
                $fecha_compra = filter_input(INPUT_POST, 'fecha_compra', FILTER_SANITIZE_STRING);
                $monto_total = filter_input(INPUT_POST, 'monto_total', FILTER_VALIDATE_FLOAT);
                $adelanto = filter_input(INPUT_POST, 'adelanto', FILTER_VALIDATE_FLOAT);
                $estado_pago = filter_input(INPUT_POST, 'estado_pago', FILTER_SANITIZE_STRING);

                // --- Validación de campos principales de la compra para edición ---
                if ($id === false || $id <= 0) {
                    $_SESSION['error_compra'] = 'ID de compra no válido para editar.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($id_proveedor === false || $id_proveedor <= 0) {
                    $_SESSION['error_compra'] = 'El proveedor seleccionado no es válido para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($id_personal === false || $id_personal <= 0) {
                    $_SESSION['error_compra'] = 'El ID de personal no es válido para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if (!DateTime::createFromFormat('Y-m-d', $fecha_compra)) {
                    $_SESSION['error_compra'] = 'La fecha de compra no es válida para la edición. Formato esperado: AAAA-MM-DD.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($monto_total === false || $monto_total < 0) {
                    $_SESSION['error_compra'] = 'El monto total de la compra no es válido o es negativo para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($adelanto === false || $adelanto < 0) {
                    $_SESSION['error_compra'] = 'El adelanto no es válido o es negativo para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($adelanto > $monto_total) {
                    $_SESSION['error_compra'] = 'El adelanto no puede ser mayor que el monto total de la compra en la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                $allowed_estados = ['pendiente', 'pagado', 'parcial'];
                if (!in_array($estado_pago, $allowed_estados)) {
                    $_SESSION['error_compra'] = 'El estado de pago seleccionado no es válido para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                // --- Fin Validación de campos principales para edición ---

                $stmt = $pdo->prepare("UPDATE compras_proveedores SET 
                    id_proveedor = :id_proveedor, 
                    id_personal = :id_personal, 
                    fecha_compra = :fecha_compra, 
                    monto_total = :monto_total, 
                    adelanto = :adelanto, 
                    estado_pago = :estado_pago 
                    WHERE id = :id");
                
                $stmt->bindParam(':id_proveedor', $id_proveedor);
                $stmt->bindParam(':id_personal', $id_personal);
                $stmt->bindParam(':fecha_compra', $fecha_compra);
                $stmt->bindParam(':monto_total', $monto_total);
                $stmt->bindParam(':adelanto', $adelanto);
                $stmt->bindParam(':estado_pago', $estado_pago);
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    $_SESSION['success_compra'] = 'Compra actualizada exitosamente.';
                } else {
                    $_SESSION['error_compra'] = 'Error al actualizar la compra. Intente nuevamente.';
                }
                break;

            case 'eliminar':
                $id_compra = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

                // --- Validación de campo para eliminar ---
                if ($id_compra === false || $id_compra <= 0) {
                    $_SESSION['error_compra'] = 'ID de la compra no válido para eliminar.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                // --- Fin Validación de campo para eliminar ---

                $pdo->beginTransaction(); 

                // 1. Obtener los detalles de la compra para revertir el stock
                $stmt_get_details = $pdo->prepare("SELECT id_producto, cantidad, unidad FROM detalle_compra_proveedores WHERE id_compra = :id_compra");
                $stmt_get_details->bindParam(':id_compra', $id_compra);
                $stmt_get_details->execute();
                $detalles_a_revertir = $stmt_get_details->fetchAll(PDO::FETCH_ASSOC);

                foreach ($detalles_a_revertir as $detalle) {
                    $id_producto = $detalle['id_producto'];
                    $cantidad = $detalle['cantidad'];
                    $unidad = $detalle['unidad'];

                    $stmt_get_current_stock = $pdo->prepare("SELECT stock_caja, stock_frasco, stock_tira, stock_pastilla FROM productos_farmacia WHERE id = :id_producto FOR UPDATE");
                    $stmt_get_current_stock->bindParam(':id_producto', $id_producto);
                    $stmt_get_current_stock->execute();
                    $producto_actual_stock = $stmt_get_current_stock->fetch(PDO::FETCH_ASSOC);

                    if ($producto_actual_stock) {
                        $new_stock_caja = $producto_actual_stock['stock_caja'];
                        $new_stock_frasco = $producto_actual_stock['stock_frasco'];
                        $new_stock_tira = $producto_actual_stock['stock_tira'];
                        $new_stock_pastilla = $producto_actual_stock['stock_pastilla'];

                        switch ($unidad) {
                            case 'caja':
                                $new_stock_caja -= $cantidad;
                                break;
                            case 'frasco':
                                $new_stock_frasco -= $cantidad;
                                break;
                            case 'tira':
                                $new_stock_tira -= $cantidad;
                                break;
                            case 'pastilla':
                                $new_stock_pastilla -= $cantidad;
                                break;
                        }
                        
                        $new_stock_caja = max(0, $new_stock_caja);
                        $new_stock_frasco = max(0, $new_stock_frasco);
                        $new_stock_tira = max(0, $new_stock_tira);
                        $new_stock_pastilla = max(0, $new_stock_pastilla);

                        $stmt_update_stock = $pdo->prepare("UPDATE productos_farmacia SET 
                            stock_caja = :stock_caja, stock_frasco = :stock_frasco, 
                            stock_tira = :stock_tira, stock_pastilla = :stock_pastilla
                            WHERE id = :id_producto");
                        
                        $stmt_update_stock->bindParam(':stock_caja', $new_stock_caja);
                        $stmt_update_stock->bindParam(':stock_frasco', $new_stock_frasco);
                        $stmt_update_stock->bindParam(':stock_tira', $new_stock_tira);
                        $stmt_update_stock->bindParam(':stock_pastilla', $new_stock_pastilla);
                        $stmt_update_stock->bindParam(':id_producto', $id_producto);
                        $stmt_update_stock->execute();
                    }
                }

                // 2. Eliminar los detalles de la compra
                $stmt_delete_details = $pdo->prepare("DELETE FROM detalle_compra_proveedores WHERE id_compra = :id_compra");
                $stmt_delete_details->bindParam(':id_compra', $id_compra);
                $stmt_delete_details->execute();

                // 3. Eliminar la compra principal
                $stmt_delete_compra = $pdo->prepare("DELETE FROM compras_proveedores WHERE id = :id_compra");
                $stmt_delete_compra->bindParam(':id_compra', $id_compra);
                $stmt_delete_compra->execute();

                $pdo->commit(); 
                $_SESSION['success_compra'] = 'Compra y sus detalles eliminados y stock revertido exitosamente.';
                break;

            default:
                $_SESSION['error_compra'] = 'Acción no válida. Contacte al administrador.';
                break;
        }
    } catch (PDOException $e) {
        $pdo->rollBack(); 
        $_SESSION['error_compra'] = 'Error de base de datos al procesar la compra: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_compra'] = 'No se especificó ninguna acción para la compra.';
}

header('Location: ../index.php?vista=compras');
exit();
?>
