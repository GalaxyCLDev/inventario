<?php
// Función para conectar a la base de datos
function conexion() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "inventario";
    
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    return $conn;
}

// Función para limpiar cadenas y evitar inyección SQL
function limpiar_cadena($string) {
    $string = trim($string);
    $string = stripslashes($string);
    $string = htmlspecialchars($string);
    return $string;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar información de la venta (nombre del producto y cantidad)
    $producto_nombre = limpiar_cadena($_POST['producto_nombre']);
    $cantidad_vendida = limpiar_cadena($_POST['cantidad']);
    $usuario_id = 1; // Aquí debes establecer el ID del usuario que realiza la venta. Debes implementar la lógica para obtener este valor según tu sistema de autenticación.

    try {
        // Conectar a la base de datos
        $conn = conexion();

        // Verificar si el producto existe y hay suficiente stock
        $stmt_producto = $conn->prepare("SELECT producto_id, producto_stock FROM producto WHERE producto_nombre = :nombre");
        $stmt_producto->bindParam(":nombre", $producto_nombre);
        $stmt_producto->execute();
        $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            echo "El producto no existe.";
        } else {
            $producto_id = $producto['producto_id'];
            $stock_actual = $producto['producto_stock'];

            if ($cantidad_vendida > $stock_actual) {
                echo "No hay suficiente stock disponible para realizar la venta.";
            } else {
                // Calcular el nuevo stock después de la venta
                $nuevo_stock = $stock_actual - $cantidad_vendida;

                // Iniciar transacción
                $conn->beginTransaction();

                // Registrar la venta
                $stmt_venta = $conn->prepare("INSERT INTO ventas (usuario_id, producto_id, cantidad_vendida) VALUES (:usuario_id, :producto_id, :cantidad_vendida)");
                $stmt_venta->bindParam(":usuario_id", $usuario_id);
                $stmt_venta->bindParam(":producto_id", $producto_id);
                $stmt_venta->bindParam(":cantidad_vendida", $cantidad_vendida);
                $stmt_venta->execute();

                // Actualizar el stock del producto
                $stmt_actualizar_stock = $conn->prepare("UPDATE producto SET producto_stock = :nuevo_stock WHERE producto_id = :id");
                $stmt_actualizar_stock->bindParam(":nuevo_stock", $nuevo_stock);
                $stmt_actualizar_stock->bindParam(":id", $producto_id);
                $stmt_actualizar_stock->execute();

                // Confirmar la transacción
                $conn->commit();

                echo "Venta realizada con éxito.";
            }
        }
    } catch(PDOException $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Venta</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Realizar Venta</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="producto_nombre">Nombre del Producto:</label>
            <input type="text" id="producto_nombre" name="producto_nombre" required><br><br>

            <label for="cantidad">Cantidad:</label>
            <input type="number" id="cantidad" name="cantidad" min="1" required><br><br>

            <button type="submit">Realizar Venta</button>
        </form>
    </div>
</body>
</html>
