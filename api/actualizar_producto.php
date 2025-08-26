<?php
// Inicia la sesión.
session_start();

// Incluye el archivo de conexión a la base de datos.
require_once '../config/conexion.php';

// Redirecciona al usuario a la vista de productos.
$redirect_url = "../index.php?vista=productos_farmacia";

/**
 * Función para obtener un valor de un array POST y devolver NULL si está vacío.
 * @param string $key La clave del array $_POST.
 * @return mixed El valor, o NULL si está vacío.
 */
function getOptionalValue(string $key) {
    return empty($_POST[$key]) ? null : $_POST[$key];
}

// Verifica que el método de solicitud sea POST.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Obtiene los datos del formulario, incluido el ID.
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $nombre = $_POST['nombre'] ?? '';
        $concentracion = getOptionalValue('concentracion');
        $forma_farmaceutica = getOptionalValue('forma_farmaceutica');
        $presentacion = getOptionalValue('presentacion');
        $precio_unitario = getOptionalValue('precio_unitario');
        $stock_minimo = (int) ($_POST['stock_minimo'] ?? 0);
        $categoria_id = filter_var($_POST['categoria_id'] ?? null, FILTER_VALIDATE_INT);
        $unidad_id = filter_var($_POST['unidad_id'] ?? null, FILTER_VALIDATE_INT);

        // Si no hay un ID o los IDs de FK no son válidos, se redirige con un error.
        if ($id === false || $categoria_id === false || $unidad_id === false || empty($nombre)) {
            $_SESSION['error'] = "Datos de formulario inválidos.";
            header("Location: $redirect_url");
            exit();
        }

        // Prepara la consulta SQL para actualizar un producto.
        $sql = "UPDATE productos SET nombre = ?, concentracion = ?, forma_farmaceutica = ?, presentacion = ?, precio_unitario = ?, stock_minimo = ?, categoria_id = ?, unidad_id = ? 
                WHERE id = ?";
        
        // Prepara la sentencia.
        $stmt = $pdo->prepare($sql);
        
        // Ejecuta la sentencia con los valores actualizados.
        $stmt->execute([
            (string) $nombre,
            $concentracion,
            $forma_farmaceutica,
            $presentacion,
            $precio_unitario, // Ahora puede ser null
            (int) $stock_minimo,
            (int) $categoria_id,
            (int) $unidad_id,
            (int) $id
        ]);
        
        // Establece un mensaje de éxito en la sesión.
        $_SESSION['success'] = "Producto '$nombre' actualizado correctamente.";

    } catch (PDOException $e) {
        // En caso de error, guarda el mensaje de error en la sesión.
        $_SESSION['error'] = "Error al actualizar el producto: " . $e->getMessage();
    } finally {
        // Redirecciona al usuario.
        header("Location: $redirect_url");
        exit();
    }
} else {
    // Si la solicitud no es POST, se redirige.
    $_SESSION['error'] = "Acceso no autorizado.";
    header("Location: $redirect_url");
    exit();
}