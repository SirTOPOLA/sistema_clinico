<?php
// Inicia la sesión para poder usar mensajes flash (éxito o error).
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
// En un entorno de producción, se recomienda usar un token CSRF aquí.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Obtiene los datos del formulario, aplicando tipado fuerte y validación básica.
        $nombre = $_POST['nombre'] ?? '';
        $concentracion = getOptionalValue('concentracion');
        $forma_farmaceutica = getOptionalValue('forma_farmaceutica');
        $presentacion = getOptionalValue('presentacion');
        $precio_unitario = getOptionalValue('precio_unitario');

        // Convierte el stock mínimo a int.
        $stock_minimo = (int) ($_POST['stock_minimo'] ?? 0);
        
        // Se valida que los IDs de categoría y unidad sean enteros.
        $categoria_id = filter_var($_POST['categoria_id'] ?? null, FILTER_VALIDATE_INT);
        $unidad_id = filter_var($_POST['unidad_id'] ?? null, FILTER_VALIDATE_INT);
        
        // Si la validación falla, lanza una excepción.
        if ($categoria_id === false || $unidad_id === false || empty($nombre)) {
            throw new Exception("Datos de formulario inválidos. Asegúrate de proporcionar todos los campos obligatorios.");
        }

        // Prepara la consulta SQL para insertar un nuevo producto.
        $sql = "INSERT INTO productos (nombre, concentracion, forma_farmaceutica, presentacion, categoria_id, unidad_id, precio_unitario, stock_actual, stock_minimo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?)";
        
        // Prepara la sentencia.
        $stmt = $pdo->prepare($sql);
        
        // Ejecuta la sentencia con los valores del formulario.
        $stmt->execute([
            (string) $nombre,
            $concentracion,
            $forma_farmaceutica,
            $presentacion,
            (int) $categoria_id,
            (int) $unidad_id,
            $precio_unitario, // Ahora puede ser null
            (int) $stock_minimo
        ]);
        
        // Establece un mensaje de éxito en la sesión.
        $_SESSION['success'] = "Producto '$nombre' creado correctamente.";

    } catch (PDOException $e) {
        // En caso de error de la base de datos, guarda el mensaje de error en la sesión.
        $_SESSION['error'] = "Error al guardar el producto: " . $e->getMessage();
    } catch (Exception $e) {
        // Captura errores de validación y otros errores.
        $_SESSION['error'] = $e->getMessage();
    } finally {
        // Redirecciona al usuario, independientemente de si la operación fue exitosa o no.
        header("Location: $redirect_url");
        exit();
    }
} else {
    // Si la solicitud no es POST, se redirige para evitar el acceso directo.
    $_SESSION['error'] = "Acceso no autorizado.";
    header("Location: $redirect_url");
    exit();
}
