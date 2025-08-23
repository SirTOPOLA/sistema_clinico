<?php
session_start(); // Inicia la sesión para usar mensajes de éxito/error

require_once '../config/conexion.php'; // Ajusta la ruta a tu archivo de conexión

// Define $allowed_unidades al principio del script para que esté disponible globalmente
$allowed_unidades = ['caja', 'frasco', 'tira', 'pastilla'];

/**
 * Helper function to process price inputs.
 * If the filtered value is false (validation failed) or 0.0 (explicitly set to 0, which for prices means NULL),
 * it returns null. Otherwise, it returns the rounded float value.
 * @param mixed $value The input value from the form.
 * @return int|null The rounded integer price or null.
 */
function getNullablePrice($value) {
    $filteredValue = filter_var($value, FILTER_VALIDATE_FLOAT);
    // If validation failed, or value is 0 (meaning "no price set" for a price field), return null
    if ($filteredValue === false || (float)$filteredValue === 0.0) {
        return null;
    }
    return round($filteredValue);
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    try {
        switch ($action) {
            case 'crear':
                // 1. Obtener y sanear datos de la compra principal
                $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT);
                $id_personal = filter_input(INPUT_POST, 'id_personal', FILTER_VALIDATE_INT);
                $fecha_compra = filter_input(INPUT_POST, 'fecha_compra', FILTER_SANITIZE_STRING);
                $monto_total = getNullablePrice(filter_input(INPUT_POST, 'monto_total', FILTER_UNSAFE_RAW)); // Usar helper para monto_total
                $adelanto = getNullablePrice(filter_input(INPUT_POST, 'adelanto', FILTER_UNSAFE_RAW));       // Usar helper para adelanto
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
                if ($monto_total === false || (is_numeric($monto_total) && $monto_total < 0)) { // Modificado para aceptar null
                    $_SESSION['error_compra'] = 'El monto total de la compra no es válido o es negativo.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($adelanto === false || (is_numeric($adelanto) && $adelanto < 0)) { // Modificado para aceptar null
                    $_SESSION['error_compra'] = 'El adelanto no es válido o es negativo.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                // Convertir $monto_total y $adelanto a 0 para la comparación si son null
                $monto_total_comp = $monto_total ?? 0;
                $adelanto_comp = $adelanto ?? 0;
                if ($adelanto_comp > $monto_total_comp) {
                     $_SESSION['error_compra'] = 'El adelanto no puede ser mayor que el monto total de la compra.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if (!in_array($estado_pago, ['pendiente', 'pagado', 'parcial'])) {
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
                
                $stmt_compra->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
                $stmt_compra->bindParam(':id_personal', $id_personal, PDO::PARAM_INT);
                $stmt_compra->bindParam(':fecha_compra', $fecha_compra);
                $stmt_compra->bindParam(':monto_total', $monto_total); // PDO::PARAM_INT o PARAM_NULL se infiere
                $stmt_compra->bindParam(':adelanto', $adelanto);       // PDO::PARAM_INT o PARAM_NULL se infiere
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
                    // --- Obtener y sanear campos de detalle de producto ---
                    $id_producto = filter_var($producto_detalle['id_producto'], FILTER_VALIDATE_INT);
                    $cantidad = filter_var($producto_detalle['cantidad'], FILTER_VALIDATE_INT); // Cantidad siempre es INT, no null
                    $unidad = filter_var($producto_detalle['unidad'], FILTER_SANITIZE_STRING);
                    $precio_unitario = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_unitario'], FILTER_UNSAFE_RAW)); // Usar helper
                    $precio_venta = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta'], FILTER_UNSAFE_RAW));       // Usar helper
                    
                    // Precios específicos de venta por sub-unidad del formulario para actualizar `productos_farmacia`
                    $tira_price_from_form = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta_tira_sub'], FILTER_UNSAFE_RAW));
                    $pastilla_tira_price_from_form = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta_pastilla_sub_tira'], FILTER_UNSAFE_RAW));
                    $pastilla_frasco_price_from_form = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta_pastilla_sub_frasco'], FILTER_UNSAFE_RAW));

                    // Campos de unidades contenidas (para actualizar productos_farmacia)
                    // Estos son cantidades, si son 0 se guardan como 0, no como NULL
                    $tiras_por_caja_comprada = round(filter_var($producto_detalle['tiras_por_caja'] ?? 0, FILTER_VALIDATE_FLOAT));
                    $pastillas_por_tira_comprada = round(filter_var($producto_detalle['pastillas_por_tira'] ?? 0, FILTER_VALIDATE_FLOAT));
                    $pastillas_por_frasco_comprada = round(filter_var($producto_detalle['pastillas_por_frasco'] ?? 0, FILTER_VALIDATE_FLOAT));
                    $fecha_vencimiento_producto = filter_var($producto_detalle['fecha_vencimiento'], FILTER_SANITIZE_STRING);

                    $current_product_num = $index + 1; // Para mensajes de error más claros

                    // --- Validación de campos de detalle ---
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
                    if (empty($unidad) || !in_array($unidad, $allowed_unidades)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Unidad de medida inválida o no seleccionada.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($precio_unitario === false || (is_numeric($precio_unitario) && $precio_unitario < 0)) { // Modificado para aceptar null
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: El precio unitario de compra no es válido o es negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($precio_venta === false || (is_numeric($precio_venta) && $precio_venta < 0)) { // Modificado para aceptar null
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: El precio de venta sugerido no es válido o es negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    // Validaciones para los precios de venta por sub-unidad (los que van a productos_farmacia)
                    if ($tira_price_from_form === false || (is_numeric($tira_price_from_form) && $tira_price_from_form < 0)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Precio de venta por tira inválido o negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($pastilla_tira_price_from_form === false || (is_numeric($pastilla_tira_price_from_form) && $pastilla_tira_price_from_form < 0)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Precio de venta por pastilla (Tira) inválido o negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($pastilla_frasco_price_from_form === false || (is_numeric($pastilla_frasco_price_from_form) && $pastilla_frasco_price_from_form < 0)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Precio de venta por pastilla (Frasco) inválido o negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }

                    // Validaciones para las unidades contenidas y fecha de vencimiento (relevante para productos_farmacia)
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
                    // Fecha de vencimiento puede ser null si está vacía, pero debe ser válida si se proporciona
                    $parsed_fecha_vencimiento = null;
                    if (!empty($fecha_vencimiento_producto)) {
                        $date_obj = DateTime::createFromFormat('Y-m-d', $fecha_vencimiento_producto);
                        if ($date_obj && $date_obj->format('Y-m-d') === $fecha_vencimiento_producto) {
                            $parsed_fecha_vencimiento = $fecha_vencimiento_producto;
                        } else {
                            $pdo->rollBack();
                            $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: La fecha de vencimiento no es válida. Formato esperado: AAAA-MM-DD.";
                            header('Location: ../index.php?vista=compras');
                            exit();
                        }
                    }
                    // --- Fin Validación de campos de detalle ---

                    // Insertar detalle del producto en 'detalle_compra_proveedores' (SIN los campos de precio de sub-unidad)
                    $stmt_detalle = $pdo->prepare("INSERT INTO detalle_compra_proveedores 
                        (id_compra, id_producto, cantidad, unidad, precio_unitario, precio_venta) 
                        VALUES (:id_compra, :id_producto, :cantidad, :unidad, :precio_unitario, :precio_venta)");
                    
                    $stmt_detalle->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                    $stmt_detalle->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                    $stmt_detalle->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                    $stmt_detalle->bindParam(':unidad', $unidad);
                    $stmt_detalle->bindParam(':precio_unitario', $precio_unitario); // PDO::PARAM_INT o PARAM_NULL se infiere
                    $stmt_detalle->bindParam(':precio_venta', $precio_venta);       // PDO::PARAM_INT o PARAM_NULL se infiere
                    $stmt_detalle->execute();

                    // 4. Actualizar stock, precios de venta y conversiones en 'productos_farmacia'
                    $stmt_update_producto = $pdo->prepare("SELECT stock_caja, stock_frasco, stock_tira, stock_pastilla, 
                                                                   precio_caja, precio_frasco, precio_tira, precio_pastilla,
                                                                   fecha_vencimiento
                                                            FROM productos_farmacia WHERE id = :id_producto FOR UPDATE");
                    $stmt_update_producto->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                    $stmt_update_producto->execute();
                    $producto_actual = $stmt_update_producto->fetch(PDO::FETCH_ASSOC);

                    if ($producto_actual) {
                        $new_stock_caja = $producto_actual['stock_caja'];
                        $new_stock_frasco = $producto_actual['stock_frasco'];
                        $new_stock_tira = $producto_actual['stock_tira'];
                        $new_stock_pastilla = $producto_actual['stock_pastilla'];

                        // Obtener precios actuales de productos_farmacia, manteniendo NULL si ya lo eran
                        $updated_precio_caja = $producto_actual['precio_caja'];
                        $updated_precio_frasco = $producto_actual['precio_frasco'];
                        $updated_precio_tira = $producto_actual['precio_tira'];
                        $updated_precio_pastilla = $producto_actual['precio_pastilla'];

                        // Actualizar el precio de la unidad principal comprada (si aplica)
                        switch ($unidad) {
                            case 'caja':
                                $new_stock_caja += $cantidad;
                                $updated_precio_caja = $precio_venta; 
                                break;
                            case 'frasco':
                                $new_stock_frasco += $cantidad;
                                $updated_precio_frasco = $precio_venta; 
                                break;
                            case 'tira':
                                $new_stock_tira += $cantidad;
                                $updated_precio_tira = $precio_venta; 
                                break;
                            case 'pastilla':
                                $new_stock_pastilla += $cantidad;
                                $updated_precio_pastilla = $precio_venta; 
                                break;
                        }

                        // Actualizar precio_tira si se proporcionó un precio de venta por tira específico
                        if ($tira_price_from_form !== null) {
                            $updated_precio_tira = $tira_price_from_form;
                        }

                        // Actualizar precio_pastilla si se proporcionó un precio de venta por pastilla específico
                        // Se prioriza el precio de pastilla desde frasco si ambos están presentes
                        if ($pastilla_tira_price_from_form !== null) {
                            $updated_precio_pastilla = $pastilla_tira_price_from_form;
                        }
                        if ($pastilla_frasco_price_from_form !== null) {
                            $updated_precio_pastilla = $pastilla_frasco_price_from_form;
                        }


                        // Lógica para la fecha de vencimiento (prioriza la más lejana o la nueva si no hay)
                        $updated_fecha_vencimiento = $producto_actual['fecha_vencimiento']; // Valor actual de la BD
                        // Si no había fecha o la nueva fecha es posterior, actualizamos
                        if ($parsed_fecha_vencimiento !== null) {
                            if ($updated_fecha_vencimiento === null || strtotime($parsed_fecha_vencimiento) > strtotime($updated_fecha_vencimiento)) {
                                $updated_fecha_vencimiento = $parsed_fecha_vencimiento;
                            }
                        }

                        // Actualizar la tabla productos_farmacia con todos los precios relevantes y unidades contenidas
                        // NO INCLUYE LOS CAMPOS DE PRECIO DE VENTA X SUB-UNIDAD SINO LOS YA EXISTENTES
                        $stmt_update_prod_data = $pdo->prepare("UPDATE productos_farmacia 
                            SET stock_caja = :stock_caja, stock_frasco = :stock_frasco, 
                                stock_tira = :stock_tira, stock_pastilla = :stock_pastilla,
                                precio_caja = :precio_caja, precio_frasco = :precio_frasco,
                                precio_tira = :precio_tira, precio_pastilla = :precio_pastilla,
                                fecha_vencimiento = :fecha_vencimiento,
                                tiras_por_caja = :tiras_por_caja, pastillas_por_tira = :pastillas_por_tira,
                                pastillas_por_frasco = :pastillas_por_frasco
                            WHERE id = :id_producto");
                        
                        $stmt_update_prod_data->bindParam(':stock_caja', $new_stock_caja, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':stock_frasco', $new_stock_frasco, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':stock_tira', $new_stock_tira, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':stock_pastilla', $new_stock_pastilla, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':precio_caja', $updated_precio_caja); 
                        $stmt_update_prod_data->bindParam(':precio_frasco', $updated_precio_frasco); 
                        $stmt_update_prod_data->bindParam(':precio_tira', $updated_precio_tira); 
                        $stmt_update_prod_data->bindParam(':precio_pastilla', $updated_precio_pastilla); 
                        $stmt_update_prod_data->bindParam(':fecha_vencimiento', $updated_fecha_vencimiento); 
                        $stmt_update_prod_data->bindParam(':tiras_por_caja', $tiras_por_caja_comprada, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':pastillas_por_tira', $pastillas_por_tira_comprada, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':pastillas_por_frasco', $pastillas_por_frasco_comprada, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
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
                $id_compra = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT);
                $id_personal = filter_input(INPUT_POST, 'id_personal', FILTER_VALIDATE_INT);
                $fecha_compra = filter_input(INPUT_POST, 'fecha_compra', FILTER_SANITIZE_STRING);
                $monto_total = getNullablePrice(filter_input(INPUT_POST, 'monto_total', FILTER_UNSAFE_RAW)); // Usar helper
                $adelanto = getNullablePrice(filter_input(INPUT_POST, 'adelanto', FILTER_UNSAFE_RAW));       // Usar helper
                $estado_pago = filter_input(INPUT_POST, 'estado_pago', FILTER_SANITIZE_STRING);

                // --- Validación de campos principales de la compra para edición ---
                if ($id_compra === false || $id_compra <= 0) {
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
                if ($monto_total === false || (is_numeric($monto_total) && $monto_total < 0)) { // Modificado para aceptar null
                    $_SESSION['error_compra'] = 'El monto total de la compra no es válido o es negativo para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($adelanto === false || (is_numeric($adelanto) && $adelanto < 0)) { // Modificado para aceptar null
                    $_SESSION['error_compra'] = 'El adelanto no es válido o es negativo para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                // Convertir $monto_total y $adelanto a 0 para la comparación si son null
                $monto_total_comp = $monto_total ?? 0;
                $adelanto_comp = $adelanto ?? 0;
                if ($adelanto_comp > $monto_total_comp) {
                    $_SESSION['error_compra'] = 'El adelanto no puede ser mayor que el monto total de la compra en la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if (!in_array($estado_pago, ['pendiente', 'pagado', 'parcial'])) {
                    $_SESSION['error_compra'] = 'El estado de pago seleccionado no es válido para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                // --- Fin Validación de campos principales para edición ---

                $pdo->beginTransaction();

                // Obtener detalles actuales de la compra para comparar
                $stmt_current_details = $pdo->prepare("SELECT id, id_producto, cantidad, unidad FROM detalle_compra_proveedores WHERE id_compra = :id_compra");
                $stmt_current_details->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                $stmt_current_details->execute();
                $current_details = $stmt_current_details->fetchAll(PDO::FETCH_ASSOC);

                $current_detail_ids = array_column($current_details, 'id');
                $updated_detail_ids = [];

                // 1. Actualizar la compra principal
                $stmt_compra_principal = $pdo->prepare("UPDATE compras_proveedores SET 
                    id_proveedor = :id_proveedor, 
                    id_personal = :id_personal, 
                    fecha_compra = :fecha_compra, 
                    monto_total = :monto_total, 
                    adelanto = :adelanto, 
                    estado_pago = :estado_pago 
                    WHERE id = :id_compra");
                
                $stmt_compra_principal->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
                $stmt_compra_principal->bindParam(':id_personal', $id_personal, PDO::PARAM_INT);
                $stmt_compra_principal->bindParam(':fecha_compra', $fecha_compra);
                $stmt_compra_principal->bindParam(':monto_total', $monto_total); // PDO::PARAM_INT o PARAM_NULL se infiere
                $stmt_compra_principal->bindParam(':adelanto', $adelanto);       // PDO::PARAM_INT o PARAM_NULL se infiere
                $stmt_compra_principal->bindParam(':estado_pago', $estado_pago);
                $stmt_compra_principal->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                $stmt_compra_principal->execute();

                // 2. Procesar los productos adquiridos del modal de edición
                $productos_editados = $_POST['productos'] ?? [];

                foreach ($productos_editados as $index => $producto_detalle) {
                    $id_detalle = filter_var($producto_detalle['id_detalle'] ?? null, FILTER_VALIDATE_INT); // Puede ser NULL si es nuevo
                    $id_producto = filter_var($producto_detalle['id_producto'], FILTER_VALIDATE_INT);
                    $cantidad = filter_var($producto_detalle['cantidad'], FILTER_VALIDATE_INT); // Cantidad siempre es INT, no null
                    $unidad = filter_var($producto_detalle['unidad'], FILTER_SANITIZE_STRING);
                    $precio_unitario = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_unitario'], FILTER_UNSAFE_RAW)); // Usar helper
                    $precio_venta = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta'], FILTER_UNSAFE_RAW));       // Usar helper
                    
                    // Precios específicos de venta por sub-unidad del formulario para actualizar `productos_farmacia`
                    $tira_price_from_form = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta_tira_sub'], FILTER_UNSAFE_RAW));
                    $pastilla_tira_price_from_form = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta_pastilla_sub_tira'], FILTER_UNSAFE_RAW));
                    $pastilla_frasco_price_from_form = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta_pastilla_sub_frasco'], FILTER_UNSAFE_RAW));

                    $tiras_por_caja_comprada = round(filter_var($producto_detalle['tiras_por_caja'] ?? 0, FILTER_VALIDATE_FLOAT));
                    $pastillas_por_tira_comprada = round(filter_var($producto_detalle['pastillas_por_tira'] ?? 0, FILTER_VALIDATE_FLOAT));
                    $pastillas_por_frasco_comprada = round(filter_var($producto_detalle['pastillas_por_frasco'] ?? 0, FILTER_VALIDATE_FLOAT));
                    $fecha_vencimiento_producto = filter_var($producto_detalle['fecha_vencimiento'], FILTER_SANITIZE_STRING);

                    $current_product_num = $index + 1; // Para mensajes de error más claros

                    // --- Validación de campos de detalle (similar a 'crear') ---
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
                    if (empty($unidad) || !in_array($unidad, $allowed_unidades)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Unidad de medida inválida o no seleccionada.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($precio_unitario === false || (is_numeric($precio_unitario) && $precio_unitario < 0)) { // Modificado para aceptar null
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: El precio unitario de compra no es válido o es negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($precio_venta === false || (is_numeric($precio_venta) && $precio_venta < 0)) { // Modificado para aceptar null
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: El precio de venta sugerido no es válido o es negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    // Validaciones para los precios de venta por sub-unidad
                    if ($tira_price_from_form === false || (is_numeric($tira_price_from_form) && $tira_price_from_form < 0)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Precio de venta por tira inválido o negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($pastilla_tira_price_from_form === false || (is_numeric($pastilla_tira_price_from_form) && $pastilla_tira_price_from_form < 0)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Precio de venta por pastilla (Tira) inválido o negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($pastilla_frasco_price_from_form === false || (is_numeric($pastilla_frasco_price_from_form) && $pastilla_frasco_price_from_form < 0)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Precio de venta por pastilla (Frasco) inválido o negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    // Validaciones para las unidades contenidas y fecha de vencimiento (relevante para productos_farmacia)
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
                     // Fecha de vencimiento puede ser null si está vacía, pero debe ser válida si se proporciona
                    $parsed_fecha_vencimiento = null;
                    if (!empty($fecha_vencimiento_producto)) {
                        $date_obj = DateTime::createFromFormat('Y-m-d', $fecha_vencimiento_producto);
                        if ($date_obj && $date_obj->format('Y-m-d') === $fecha_vencimiento_producto) {
                            $parsed_fecha_vencimiento = $fecha_vencimiento_producto;
                        } else {
                            $pdo->rollBack();
                            $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: La fecha de vencimiento no es válida. Formato esperado: AAAA-MM-DD.";
                            header('Location: ../index.php?vista=compras');
                            exit();
                        }
                    }
                    // --- Fin Validación de campos de detalle ---

                    // Recuperar la cantidad y unidad anterior si el detalle ya existía
                    $old_cantidad = 0;
                    $old_unidad = '';
                    $current_product_index = array_search($id_detalle, array_column($current_details, 'id'));
                    if ($id_detalle && $current_product_index !== false) {
                        $old_cantidad = $current_details[$current_product_index]['cantidad'];
                        $old_unidad = $current_details[$current_product_index]['unidad'];
                        $updated_detail_ids[] = $id_detalle; // Marcar como actualizado/existente
                    }

                    if ($id_detalle) { // Es un detalle existente, actualizar
                        $stmt_update_detalle = $pdo->prepare("UPDATE detalle_compra_proveedores SET 
                            id_producto = :id_producto, cantidad = :cantidad, unidad = :unidad, 
                            precio_unitario = :precio_unitario, precio_venta = :precio_venta
                            WHERE id = :id_detalle AND id_compra = :id_compra");
                        
                        $stmt_update_detalle->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                        $stmt_update_detalle->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                        $stmt_update_detalle->bindParam(':unidad', $unidad);
                        $stmt_update_detalle->bindParam(':precio_unitario', $precio_unitario); 
                        $stmt_update_detalle->bindParam(':precio_venta', $precio_venta);       
                        $stmt_update_detalle->bindParam(':id_detalle', $id_detalle, PDO::PARAM_INT);
                        $stmt_update_detalle->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                        $stmt_update_detalle->execute();
                    } else { // Es un nuevo detalle, insertar
                        $stmt_insert_detalle = $pdo->prepare("INSERT INTO detalle_compra_proveedores 
                            (id_compra, id_producto, cantidad, unidad, precio_unitario, precio_venta) 
                            VALUES (:id_compra, :id_producto, :cantidad, :unidad, :precio_unitario, :precio_venta)");
                        
                        $stmt_insert_detalle->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                        $stmt_insert_detalle->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                        $stmt_insert_detalle->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                        $stmt_insert_detalle->bindParam(':unidad', $unidad);
                        $stmt_insert_detalle->bindParam(':precio_unitario', $precio_unitario); 
                        $stmt_insert_detalle->bindParam(':precio_venta', $precio_venta);       
                        $stmt_insert_detalle->execute();

                        // No necesitamos agregar el id a updated_detail_ids porque es un nuevo registro
                        // y no se usará para determinar eliminaciones de registros viejos.
                    }

                    // 3. Ajustar el stock y los precios de venta en 'productos_farmacia' para el producto.
                    // Primero, revertir el stock antiguo si es una actualización
                    if ($id_detalle && ($old_cantidad !== $cantidad || $old_unidad !== $unidad)) {
                        $stmt_revert_stock = $pdo->prepare("SELECT stock_caja, stock_frasco, stock_tira, stock_pastilla FROM productos_farmacia WHERE id = :id_producto FOR UPDATE");
                        $stmt_revert_stock->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                        $stmt_revert_stock->execute();
                        $current_product_stock = $stmt_revert_stock->fetch(PDO::FETCH_ASSOC);

                        if ($current_product_stock) {
                            $reverted_stock_caja = $current_product_stock['stock_caja'];
                            $reverted_stock_frasco = $current_product_stock['stock_frasco'];
                            $reverted_stock_tira = $current_product_stock['stock_tira'];
                            $reverted_stock_pastilla = $current_product_stock['stock_pastilla'];

                            switch ($old_unidad) {
                                case 'caja': $reverted_stock_caja -= $old_cantidad; break;
                                case 'frasco': $reverted_stock_frasco -= $old_cantidad; break;
                                case 'tira': $reverted_stock_tira -= $old_cantidad; break;
                                case 'pastilla': $reverted_stock_pastilla -= $old_cantidad; break;
                            }
                            // Asegurar que el stock no sea negativo
                            $reverted_stock_caja = max(0, $reverted_stock_caja);
                            $reverted_stock_frasco = max(0, $reverted_stock_frasco);
                            $reverted_stock_tira = max(0, $reverted_stock_tira);
                            $reverted_stock_pastilla = max(0, $reverted_stock_pastilla);
                            
                            $stmt_update_reverted_stock = $pdo->prepare("UPDATE productos_farmacia SET 
                                stock_caja = :stock_caja, stock_frasco = :stock_frasco, 
                                stock_tira = :stock_tira, stock_pastilla = :stock_pastilla
                                WHERE id = :id_producto");
                            $stmt_update_reverted_stock->bindParam(':stock_caja', $reverted_stock_caja, PDO::PARAM_INT);
                            // CORRECTED TYPO: $reverted_stock_franco changed to $reverted_stock_frasco
                            $stmt_update_reverted_stock->bindParam(':stock_frasco', $reverted_stock_frasco, PDO::PARAM_INT);
                            $stmt_update_reverted_stock->bindParam(':stock_tira', $reverted_stock_tira, PDO::PARAM_INT);
                            $stmt_update_reverted_stock->bindParam(':stock_pastilla', $reverted_stock_pastilla, PDO::PARAM_INT);
                            $stmt_update_reverted_stock->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                            $stmt_update_reverted_stock->execute();
                        }
                    }

                    // Obtener el stock actual del producto (después de revertir si aplica) para el nuevo ajuste
                    $stmt_current_stock = $pdo->prepare("SELECT stock_caja, stock_frasco, stock_tira, stock_pastilla,
                                                                   precio_caja, precio_frasco, precio_tira, precio_pastilla,
                                                                   fecha_vencimiento
                                                            FROM productos_farmacia WHERE id = :id_producto FOR UPDATE");
                    $stmt_current_stock->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                    $stmt_current_stock->execute();
                    $producto_stock = $stmt_current_stock->fetch(PDO::FETCH_ASSOC);

                    if ($producto_stock) {
                        $new_stock_caja = $producto_stock['stock_caja'];
                        $new_stock_frasco = $producto_stock['stock_frasco'];
                        $new_stock_tira = $producto_stock['stock_tira'];
                        $new_stock_pastilla = $producto_stock['stock_pastilla'];

                        // Almacenamos los precios actuales, manteniendo NULL si ya lo eran
                        $updated_precio_caja = $producto_stock['precio_caja'];
                        $updated_precio_frasco = $producto_stock['precio_frasco'];
                        $updated_precio_tira = $producto_stock['precio_tira'];
                        $updated_precio_pastilla = $producto_stock['precio_pastilla'];

                        // Actualizar el precio de la unidad principal comprada (si aplica)
                        switch ($unidad) {
                            case 'caja':    $new_stock_caja += $cantidad; $updated_precio_caja = $precio_venta; break;
                            case 'frasco':  $new_stock_frasco += $cantidad; $updated_precio_frasco = $precio_venta; break;
                            case 'tira':    $new_stock_tira += $cantidad; $updated_precio_tira = $precio_venta; break;
                            case 'pastilla':$new_stock_pastilla += $cantidad; $updated_precio_pastilla = $precio_venta; break;
                        }
                        
                        // Actualizar precio_tira si se proporcionó un precio de venta por tira específico
                        if ($tira_price_from_form !== null) {
                            $updated_precio_tira = $tira_price_from_form;
                        }

                        // Actualizar precio_pastilla si se proporcionó un precio de venta por pastilla específico
                        // Se prioriza el precio de pastilla desde frasco si ambos están presentes
                        if ($pastilla_tira_price_from_form !== null) {
                            $updated_precio_pastilla = $pastilla_tira_price_from_form;
                        }
                        if ($pastilla_frasco_price_from_form !== null) {
                            $updated_precio_pastilla = $pastilla_frasco_price_from_form;
                        }

                        // Lógica para la fecha de vencimiento (prioriza la más lejana o la nueva si no hay)
                        $updated_fecha_vencimiento = $producto_stock['fecha_vencimiento']; // Valor actual de la BD
                        // Si no había fecha o la nueva fecha es posterior, actualizamos
                        if ($parsed_fecha_vencimiento !== null) {
                            if ($updated_fecha_vencimiento === null || strtotime($parsed_fecha_vencimiento) > strtotime($updated_fecha_vencimiento)) {
                                $updated_fecha_vencimiento = $parsed_fecha_vencimiento;
                            }
                        }

                        // Actualizar la tabla productos_farmacia con todos los precios relevantes y unidades contenidas
                        // NO INCLUYE LOS CAMPOS DE PRECIO DE VENTA X SUB-UNIDAD SINO LOS YA EXISTENTES
                        $stmt_update_prod_data = $pdo->prepare("UPDATE productos_farmacia 
                            SET stock_caja = :stock_caja, stock_frasco = :stock_frasco, 
                                stock_tira = :stock_tira, stock_pastilla = :stock_pastilla,
                                precio_caja = :precio_caja, precio_frasco = :precio_frasco,
                                precio_tira = :precio_tira, precio_pastilla = :precio_pastilla,
                                fecha_vencimiento = :fecha_vencimiento,
                                tiras_por_caja = :tiras_por_caja, pastillas_por_tira = :pastillas_por_tira,
                                pastillas_por_frasco = :pastillas_por_frasco
                            WHERE id = :id_producto");
                        
                        $stmt_update_prod_data->bindParam(':stock_caja', $new_stock_caja, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':stock_frasco', $new_stock_frasco, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':stock_tira', $new_stock_tira, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':stock_pastilla', $new_stock_pastilla, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':precio_caja', $updated_precio_caja); 
                        $stmt_update_prod_data->bindParam(':precio_frasco', $updated_precio_frasco); 
                        $stmt_update_prod_data->bindParam(':precio_tira', $updated_precio_tira); 
                        $stmt_update_prod_data->bindParam(':precio_pastilla', $updated_precio_pastilla); 
                        $stmt_update_prod_data->bindParam(':fecha_vencimiento', $updated_fecha_vencimiento); 
                        $stmt_update_prod_data->bindParam(':tiras_por_caja', $tiras_por_caja_comprada, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':pastillas_por_tira', $pastillas_por_tira_comprada, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':pastillas_por_frasco', $pastillas_por_frasco_comprada, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
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
                $id_compra = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                $id_proveedor = filter_input(INPUT_POST, 'id_proveedor', FILTER_VALIDATE_INT);
                $id_personal = filter_input(INPUT_POST, 'id_personal', FILTER_VALIDATE_INT);
                $fecha_compra = filter_input(INPUT_POST, 'fecha_compra', FILTER_SANITIZE_STRING);
                $monto_total = getNullablePrice(filter_input(INPUT_POST, 'monto_total', FILTER_UNSAFE_RAW)); // Usar helper
                $adelanto = getNullablePrice(filter_input(INPUT_POST, 'adelanto', FILTER_UNSAFE_RAW));       // Usar helper
                $estado_pago = filter_input(INPUT_POST, 'estado_pago', FILTER_SANITIZE_STRING);

                // --- Validación de campos principales de la compra para edición ---
                if ($id_compra === false || $id_compra <= 0) {
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
                if ($monto_total === false || (is_numeric($monto_total) && $monto_total < 0)) { // Modificado para aceptar null
                    $_SESSION['error_compra'] = 'El monto total de la compra no es válido o es negativo para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if ($adelanto === false || (is_numeric($adelanto) && $adelanto < 0)) { // Modificado para aceptar null
                    $_SESSION['error_compra'] = 'El adelanto no es válido o es negativo para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                // Convertir $monto_total y $adelanto a 0 para la comparación si son null
                $monto_total_comp = $monto_total ?? 0;
                $adelanto_comp = $adelanto ?? 0;
                if ($adelanto_comp > $monto_total_comp) {
                    $_SESSION['error_compra'] = 'El adelanto no puede ser mayor que el monto total de la compra en la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                if (!in_array($estado_pago, ['pendiente', 'pagado', 'parcial'])) {
                    $_SESSION['error_compra'] = 'El estado de pago seleccionado no es válido para la edición.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }
                // --- Fin Validación de campos principales para edición ---

                $pdo->beginTransaction();

                // Obtener detalles actuales de la compra para comparar
                $stmt_current_details = $pdo->prepare("SELECT id, id_producto, cantidad, unidad FROM detalle_compra_proveedores WHERE id_compra = :id_compra");
                $stmt_current_details->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                $stmt_current_details->execute();
                $current_details = $stmt_current_details->fetchAll(PDO::FETCH_ASSOC);

                $current_detail_ids = array_column($current_details, 'id');
                $updated_detail_ids = [];

                // 1. Actualizar la compra principal
                $stmt_compra_principal = $pdo->prepare("UPDATE compras_proveedores SET 
                    id_proveedor = :id_proveedor, 
                    id_personal = :id_personal, 
                    fecha_compra = :fecha_compra, 
                    monto_total = :monto_total, 
                    adelanto = :adelanto, 
                    estado_pago = :estado_pago 
                    WHERE id = :id_compra");
                
                $stmt_compra_principal->bindParam(':id_proveedor', $id_proveedor, PDO::PARAM_INT);
                $stmt_compra_principal->bindParam(':id_personal', $id_personal, PDO::PARAM_INT);
                $stmt_compra_principal->bindParam(':fecha_compra', $fecha_compra);
                $stmt_compra_principal->bindParam(':monto_total', $monto_total); // PDO::PARAM_INT o PARAM_NULL se infiere
                $stmt_compra_principal->bindParam(':adelanto', $adelanto);       // PDO::PARAM_INT o PARAM_NULL se infiere
                $stmt_compra_principal->bindParam(':estado_pago', $estado_pago);
                $stmt_compra_principal->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                $stmt_compra_principal->execute();

                // 2. Procesar los productos adquiridos del modal de edición
                $productos_editados = $_POST['productos'] ?? [];

                foreach ($productos_editados as $index => $producto_detalle) {
                    $id_detalle = filter_var($producto_detalle['id_detalle'] ?? null, FILTER_VALIDATE_INT); // Puede ser NULL si es nuevo
                    $id_producto = filter_var($producto_detalle['id_producto'], FILTER_VALIDATE_INT);
                    $cantidad = filter_var($producto_detalle['cantidad'], FILTER_VALIDATE_INT); // Cantidad siempre es INT, no null
                    $unidad = filter_var($producto_detalle['unidad'], FILTER_SANITIZE_STRING);
                    $precio_unitario = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_unitario'], FILTER_UNSAFE_RAW)); // Usar helper
                    $precio_venta = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta'], FILTER_UNSAFE_RAW));       // Usar helper
                    
                    // Precios específicos de venta por sub-unidad del formulario para actualizar `productos_farmacia`
                    $tira_price_from_form = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta_tira_sub'], FILTER_UNSAFE_RAW));
                    $pastilla_tira_price_from_form = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta_pastilla_sub_tira'], FILTER_UNSAFE_RAW));
                    $pastilla_frasco_price_from_form = getNullablePrice(filter_input(INPUT_POST['productos'][$index]['precio_venta_pastilla_sub_frasco'], FILTER_UNSAFE_RAW));

                    $tiras_por_caja_comprada = round(filter_var($producto_detalle['tiras_por_caja'] ?? 0, FILTER_VALIDATE_FLOAT));
                    $pastillas_por_tira_comprada = round(filter_var($producto_detalle['pastillas_por_tira'] ?? 0, FILTER_VALIDATE_FLOAT));
                    $pastillas_por_frasco_comprada = round(filter_var($producto_detalle['pastillas_por_frasco'] ?? 0, FILTER_VALIDATE_FLOAT));
                    $fecha_vencimiento_producto = filter_var($producto_detalle['fecha_vencimiento'], FILTER_SANITIZE_STRING);

                    $current_product_num = $index + 1; // Para mensajes de error más claros

                    // --- Validación de campos de detalle (similar a 'crear') ---
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
                    if (empty($unidad) || !in_array($unidad, $allowed_unidades)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Unidad de medida inválida o no seleccionada.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($precio_unitario === false || (is_numeric($precio_unitario) && $precio_unitario < 0)) { // Modificado para aceptar null
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: El precio unitario de compra no es válido o es negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($precio_venta === false || (is_numeric($precio_venta) && $precio_venta < 0)) { // Modificado para aceptar null
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: El precio de venta sugerido no es válido o es negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    // Validaciones para los precios de venta por sub-unidad
                    if ($tira_price_from_form === false || (is_numeric($tira_price_from_form) && $tira_price_from_form < 0)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Precio de venta por tira inválido o negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($pastilla_tira_price_from_form === false || (is_numeric($pastilla_tira_price_from_form) && $pastilla_tira_price_from_form < 0)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Precio de venta por pastilla (Tira) inválido o negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    if ($pastilla_frasco_price_from_form === false || (is_numeric($pastilla_frasco_price_from_form) && $pastilla_frasco_price_from_form < 0)) {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: Precio de venta por pastilla (Frasco) inválido o negativo.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                    // Validaciones para las unidades contenidas y fecha de vencimiento (relevante para productos_farmacia)
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
                     // Fecha de vencimiento puede ser null si está vacía, pero debe ser válida si se proporciona
                    $parsed_fecha_vencimiento = null;
                    if (!empty($fecha_vencimiento_producto)) {
                        $date_obj = DateTime::createFromFormat('Y-m-d', $fecha_vencimiento_producto);
                        if ($date_obj && $date_obj->format('Y-m-d') === $fecha_vencimiento_producto) {
                            $parsed_fecha_vencimiento = $fecha_vencimiento_producto;
                        } else {
                            $pdo->rollBack();
                            $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: La fecha de vencimiento no es válida. Formato esperado: AAAA-MM-DD.";
                            header('Location: ../index.php?vista=compras');
                            exit();
                        }
                    }
                    // --- Fin Validación de campos de detalle ---

                    // Recuperar la cantidad y unidad anterior si el detalle ya existía
                    $old_cantidad = 0;
                    $old_unidad = '';
                    $current_product_index = array_search($id_detalle, array_column($current_details, 'id'));
                    if ($id_detalle && $current_product_index !== false) {
                        $old_cantidad = $current_details[$current_product_index]['cantidad'];
                        $old_unidad = $current_details[$current_product_index]['unidad'];
                        $updated_detail_ids[] = $id_detalle; // Marcar como actualizado/existente
                    }

                    if ($id_detalle) { // Es un detalle existente, actualizar
                        $stmt_update_detalle = $pdo->prepare("UPDATE detalle_compra_proveedores SET 
                            id_producto = :id_producto, cantidad = :cantidad, unidad = :unidad, 
                            precio_unitario = :precio_unitario, precio_venta = :precio_venta
                            WHERE id = :id_detalle AND id_compra = :id_compra");
                        
                        $stmt_update_detalle->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                        $stmt_update_detalle->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                        $stmt_update_detalle->bindParam(':unidad', $unidad);
                        $stmt_update_detalle->bindParam(':precio_unitario', $precio_unitario); 
                        $stmt_update_detalle->bindParam(':precio_venta', $precio_venta);       
                        $stmt_update_detalle->bindParam(':id_detalle', $id_detalle, PDO::PARAM_INT);
                        $stmt_update_detalle->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                        $stmt_update_detalle->execute();
                    } else { // Es un nuevo detalle, insertar
                        $stmt_insert_detalle = $pdo->prepare("INSERT INTO detalle_compra_proveedores 
                            (id_compra, id_producto, cantidad, unidad, precio_unitario, precio_venta) 
                            VALUES (:id_compra, :id_producto, :cantidad, :unidad, :precio_unitario, :precio_venta)");
                        
                        $stmt_insert_detalle->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                        $stmt_insert_detalle->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                        $stmt_insert_detalle->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
                        $stmt_insert_detalle->bindParam(':unidad', $unidad);
                        $stmt_insert_detalle->bindParam(':precio_unitario', $precio_unitario); 
                        $stmt_insert_detalle->bindParam(':precio_venta', $precio_venta);       
                        $stmt_insert_detalle->execute();

                        // No necesitamos agregar el id a updated_detail_ids porque es un nuevo registro
                        // y no se usará para determinar eliminaciones de registros viejos.
                    }

                    // 3. Ajustar el stock y los precios de venta en 'productos_farmacia' para el producto.
                    // Primero, revertir el stock antiguo si es una actualización
                    if ($id_detalle && ($old_cantidad !== $cantidad || $old_unidad !== $unidad)) {
                        $stmt_revert_stock = $pdo->prepare("SELECT stock_caja, stock_frasco, stock_tira, stock_pastilla FROM productos_farmacia WHERE id = :id_producto FOR UPDATE");
                        $stmt_revert_stock->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                        $stmt_revert_stock->execute();
                        $current_product_stock = $stmt_revert_stock->fetch(PDO::FETCH_ASSOC);

                        if ($current_product_stock) {
                            $reverted_stock_caja = $current_product_stock['stock_caja'];
                            $reverted_stock_frasco = $current_product_stock['stock_frasco'];
                            $reverted_stock_tira = $current_product_stock['stock_tira'];
                            $reverted_stock_pastilla = $current_product_stock['stock_pastilla'];

                            switch ($old_unidad) {
                                case 'caja': $reverted_stock_caja -= $old_cantidad; break;
                                case 'frasco': $reverted_stock_frasco -= $old_cantidad; break;
                                case 'tira': $reverted_stock_tira -= $old_cantidad; break;
                                case 'pastilla': $reverted_stock_pastilla -= $old_cantidad; break;
                            }
                            // Asegurar que el stock no sea negativo
                            $reverted_stock_caja = max(0, $reverted_stock_caja);
                            $reverted_stock_frasco = max(0, $reverted_stock_frasco);
                            $reverted_stock_tira = max(0, $reverted_stock_tira);
                            $reverted_stock_pastilla = max(0, $reverted_stock_pastilla);
                            
                            $stmt_update_reverted_stock = $pdo->prepare("UPDATE productos_farmacia SET 
                                stock_caja = :stock_caja, stock_frasco = :stock_frasco, 
                                stock_tira = :stock_tira, stock_pastilla = :stock_pastilla
                                WHERE id = :id_producto");
                            $stmt_update_reverted_stock->bindParam(':stock_caja', $reverted_stock_caja, PDO::PARAM_INT);
                            // CORRECTED TYPO: $reverted_stock_franco changed to $reverted_stock_frasco
                            $stmt_update_reverted_stock->bindParam(':stock_frasco', $reverted_stock_frasco, PDO::PARAM_INT);
                            $stmt_update_reverted_stock->bindParam(':stock_tira', $reverted_stock_tira, PDO::PARAM_INT);
                            $stmt_update_reverted_stock->bindParam(':stock_pastilla', $reverted_stock_pastilla, PDO::PARAM_INT);
                            $stmt_update_reverted_stock->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                            $stmt_update_reverted_stock->execute();
                        }
                    }

                    // Obtener el stock actual del producto (después de revertir si aplica) para el nuevo ajuste
                    $stmt_current_stock = $pdo->prepare("SELECT stock_caja, stock_frasco, stock_tira, stock_pastilla,
                                                                   precio_caja, precio_frasco, precio_tira, precio_pastilla,
                                                                   fecha_vencimiento
                                                            FROM productos_farmacia WHERE id = :id_producto FOR UPDATE");
                    $stmt_current_stock->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                    $stmt_current_stock->execute();
                    $producto_stock = $stmt_current_stock->fetch(PDO::FETCH_ASSOC);

                    if ($producto_stock) {
                        $new_stock_caja = $producto_stock['stock_caja'];
                        $new_stock_frasco = $producto_stock['stock_frasco'];
                        $new_stock_tira = $producto_stock['stock_tira'];
                        $new_stock_pastilla = $producto_stock['stock_pastilla'];

                        // Almacenamos los precios actuales, manteniendo NULL si ya lo eran
                        $updated_precio_caja = $producto_stock['precio_caja'];
                        $updated_precio_frasco = $producto_stock['precio_frasco'];
                        $updated_precio_tira = $producto_stock['precio_tira'];
                        $updated_precio_pastilla = $producto_stock['precio_pastilla'];

                        // Actualizar el precio de la unidad principal comprada (si aplica)
                        switch ($unidad) {
                            case 'caja':    $new_stock_caja += $cantidad; $updated_precio_caja = $precio_venta; break;
                            case 'frasco':  $new_stock_frasco += $cantidad; $updated_precio_frasco = $precio_venta; break;
                            case 'tira':    $new_stock_tira += $cantidad; $updated_precio_tira = $precio_venta; break;
                            case 'pastilla':$new_stock_pastilla += $cantidad; $updated_precio_pastilla = $precio_venta; break;
                        }
                        
                        // Actualizar precio_tira si se proporcionó un precio de venta por tira específico
                        if ($tira_price_from_form !== null) {
                            $updated_precio_tira = $tira_price_from_form;
                        }

                        // Actualizar precio_pastilla si se proporcionó un precio de venta por pastilla específico
                        // Se prioriza el precio de pastilla desde frasco si ambos están presentes
                        if ($pastilla_tira_price_from_form !== null) {
                            $updated_precio_pastilla = $pastilla_tira_price_from_form;
                        }
                        if ($pastilla_frasco_price_from_form !== null) {
                            $updated_precio_pastilla = $pastilla_frasco_price_from_form;
                        }

                        // Lógica para la fecha de vencimiento (prioriza la más lejana o la nueva si no hay)
                        $updated_fecha_vencimiento = $producto_stock['fecha_vencimiento']; // Valor actual de la BD
                        // Si no había fecha o la nueva fecha es posterior, actualizamos
                        if ($parsed_fecha_vencimiento !== null) {
                            if ($updated_fecha_vencimiento === null || strtotime($parsed_fecha_vencimiento) > strtotime($updated_fecha_vencimiento)) {
                                $updated_fecha_vencimiento = $parsed_fecha_vencimiento;
                            }
                        }

                        // Actualizar la tabla productos_farmacia con todos los precios relevantes y unidades contenidas
                        // NO INCLUYE LOS CAMPOS DE PRECIO DE VENTA X SUB-UNIDAD SINO LOS YA EXISTENTES
                        $stmt_update_prod_data = $pdo->prepare("UPDATE productos_farmacia 
                            SET stock_caja = :stock_caja, stock_frasco = :stock_frasco, 
                                stock_tira = :stock_tira, stock_pastilla = :stock_pastilla,
                                precio_caja = :precio_caja, precio_frasco = :precio_frasco,
                                precio_tira = :precio_tira, precio_pastilla = :precio_pastilla,
                                fecha_vencimiento = :fecha_vencimiento,
                                tiras_por_caja = :tiras_por_caja, pastillas_por_tira = :pastillas_por_tira,
                                pastillas_por_frasco = :pastillas_por_frasco
                            WHERE id = :id_producto");
                        
                        $stmt_update_prod_data->bindParam(':stock_caja', $new_stock_caja, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':stock_frasco', $new_stock_frasco, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':stock_tira', $new_stock_tira, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':stock_pastilla', $new_stock_pastilla, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':precio_caja', $updated_precio_caja); 
                        $stmt_update_prod_data->bindParam(':precio_frasco', $updated_precio_frasco); 
                        $stmt_update_prod_data->bindParam(':precio_tira', $updated_precio_tira); 
                        $stmt_update_prod_data->bindParam(':precio_pastilla', $updated_precio_pastilla); 
                        $stmt_update_prod_data->bindParam(':fecha_vencimiento', $updated_fecha_vencimiento); 
                        $stmt_update_prod_data->bindParam(':tiras_por_caja', $tiras_por_caja_comprada, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':pastillas_por_tira', $pastillas_por_tira_comprada, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':pastillas_por_frasco', $pastillas_por_frasco_comprada, PDO::PARAM_INT);
                        $stmt_update_prod_data->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                        $stmt_update_prod_data->execute();

                    } else {
                        $pdo->rollBack();
                        $_SESSION['error_compra'] = "Error en Producto #{$current_product_num}: No se encontró el producto para actualizar el stock.";
                        header('Location: ../index.php?vista=compras');
                        exit();
                    }
                }

                // 4. Eliminar detalles de compra que no fueron enviados en la edición
                $details_to_delete = array_diff($current_detail_ids, $updated_detail_ids);
                foreach ($details_to_delete as $id_detalle_to_delete) {
                    $deleted_detail_info = array_filter($current_details, function($detail) use ($id_detalle_to_delete) {
                        return $detail['id'] == $id_detalle_to_delete;
                    });
                    $deleted_detail_info = reset($deleted_detail_info); // Obtener el primer (y único) elemento

                    if ($deleted_detail_info) {
                        $id_producto_del = $deleted_detail_info['id_producto'];
                        $cantidad_del = $deleted_detail_info['cantidad'];
                        $unidad_del = $deleted_detail_info['unidad'];

                        // Revertir stock del producto eliminado
                        $stmt_revert_stock_del = $pdo->prepare("SELECT stock_caja, stock_frasco, stock_tira, stock_pastilla FROM productos_farmacia WHERE id = :id_producto FOR UPDATE");
                        $stmt_revert_stock_del->bindParam(':id_producto', $id_producto_del, PDO::PARAM_INT);
                        $stmt_revert_stock_del->execute();
                        $current_product_stock_del = $stmt_revert_stock_del->fetch(PDO::FETCH_ASSOC);

                        if ($current_product_stock_del) {
                            $new_stock_caja_del = $current_product_stock_del['stock_caja'];
                            $new_stock_frasco_del = $current_product_stock_del['stock_frasco'];
                            $new_stock_tira_del = $current_product_stock_del['stock_tira'];
                            $new_stock_pastilla_del = $current_product_stock_del['stock_pastilla'];

                            switch ($unidad_del) {
                                case 'caja': $new_stock_caja_del -= $cantidad_del; break;
                                case 'frasco': $new_stock_frasco_del -= $cantidad_del; break;
                                case 'tira': $new_stock_tira_del -= $cantidad_del; break;
                                case 'pastilla': $new_stock_pastilla_del -= $cantidad_del; break;
                            }
                            // Asegurar que el stock no sea negativo
                            $new_stock_caja_del = max(0, $new_stock_caja_del);
                            $new_stock_frasco_del = max(0, $new_stock_frasco_del);
                            $new_stock_tira_del = max(0, $new_stock_tira_del);
                            $new_stock_pastilla_del = max(0, $new_stock_pastilla_del);

                            $stmt_update_stock_del = $pdo->prepare("UPDATE productos_farmacia SET 
                                stock_caja = :stock_caja, stock_frasco = :stock_frasco, 
                                stock_tira = :stock_tira, stock_pastilla = :stock_pastilla
                                WHERE id = :id_producto");
                            $stmt_update_stock_del->bindParam(':stock_caja', $new_stock_caja_del, PDO::PARAM_INT);
                            $stmt_update_stock_del->bindParam(':stock_frasco', $new_stock_frasco_del, PDO::PARAM_INT);
                            $stmt_update_stock_del->bindParam(':stock_tira', $new_stock_tira_del, PDO::PARAM_INT);
                            $stmt_update_stock_del->bindParam(':stock_pastilla', $new_stock_pastilla_del, PDO::PARAM_INT);
                            $stmt_update_stock_del->bindParam(':id_producto', $id_producto_del, PDO::PARAM_INT);
                            $stmt_update_stock_del->execute();
                        }
                    }

                    $stmt_delete_detail = $pdo->prepare("DELETE FROM detalle_compra_proveedores WHERE id = :id_detalle_to_delete");
                    $stmt_delete_detail->bindParam(':id_detalle_to_delete', $id_detalle_to_delete, PDO::PARAM_INT);
                    $stmt_delete_detail->execute();
                }

                $pdo->commit(); 
                $_SESSION['success_compra'] = 'Compra y detalles de productos actualizados exitosamente.';
                break;

            case 'eliminar':
                $id_compra = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

                if ($id_compra === false || $id_compra <= 0) {
                    $_SESSION['error_compra'] = 'ID de la compra no válido para eliminar.';
                    header('Location: ../index.php?vista=compras');
                    exit();
                }

                $pdo->beginTransaction(); 

                // 1. Obtener los detalles de la compra para revertir el stock
                $stmt_get_details = $pdo->prepare("SELECT id_producto, cantidad, unidad FROM detalle_compra_proveedores WHERE id_compra = :id_compra");
                $stmt_get_details->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                $stmt_get_details->execute();
                $detalles_a_revertir = $stmt_get_details->fetchAll(PDO::FETCH_ASSOC);

                foreach ($detalles_a_revertir as $detalle) {
                    $id_producto = $detalle['id_producto'];
                    $cantidad = $detalle['cantidad'];
                    $unidad = $detalle['unidad'];

                    $stmt_get_current_stock = $pdo->prepare("SELECT stock_caja, stock_frasco, stock_tira, stock_pastilla FROM productos_farmacia WHERE id = :id_producto FOR UPDATE");
                    $stmt_get_current_stock->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                    $stmt_get_current_stock->execute();
                    $producto_actual_stock = $stmt_get_current_stock->fetch(PDO::FETCH_ASSOC);

                    if ($producto_actual_stock) {
                        $new_stock_caja = $producto_actual_stock['stock_caja'];
                        $new_stock_frasco = $producto_actual_stock['stock_frasco'];
                        $new_stock_tira = $producto_actual_stock['stock_tira'];
                        $new_stock_pastilla = $producto_actual_stock['stock_pastilla'];

                        switch ($unidad) {
                            case 'caja': $new_stock_caja -= $cantidad; break;
                            case 'frasco': $new_stock_frasco -= $cantidad; break;
                            case 'tira': $new_stock_tira -= $cantidad; break;
                            case 'pastilla': $new_stock_pastilla -= $cantidad; break;
                        }
                        
                        $new_stock_caja = max(0, $new_stock_caja);
                        $new_stock_frasco = max(0, $new_stock_frasco);
                        $new_stock_tira = max(0, $new_stock_tira);
                        $new_stock_pastilla = max(0, $new_stock_pastilla);

                        $stmt_update_stock = $pdo->prepare("UPDATE productos_farmacia SET 
                            stock_caja = :stock_caja, stock_frasco = :stock_frasco, 
                            stock_tira = :stock_tira, stock_pastilla = :stock_pastilla
                            WHERE id = :id_producto");
                        
                        $stmt_update_stock->bindParam(':stock_caja', $new_stock_caja, PDO::PARAM_INT);
                        $stmt_update_stock->bindParam(':stock_frasco', $new_stock_frasco, PDO::PARAM_INT);
                        $stmt_update_stock->bindParam(':stock_tira', $new_stock_tira, PDO::PARAM_INT);
                        $stmt_update_stock->bindParam(':stock_pastilla', $new_stock_pastilla, PDO::PARAM_INT);
                        $stmt_update_stock->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
                        $stmt_update_stock->execute();
                    }
                }

                // 2. Eliminar los detalles de la compra
                $stmt_delete_details = $pdo->prepare("DELETE FROM detalle_compra_proveedores WHERE id_compra = :id_compra");
                $stmt_delete_details->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
                $stmt_delete_details->execute();

                // 3. Eliminar la compra principal
                $stmt_delete_compra = $pdo->prepare("DELETE FROM compras_proveedores WHERE id = :id_compra");
                $stmt_delete_compra->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
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
