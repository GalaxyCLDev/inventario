<?php
session_start();

// Verificamos si recibimos los datos necesarios para agregar un producto al carrito
if(isset($_POST['producto_id']) && isset($_POST['nombre_producto']) && isset($_POST['precio_producto'])) {
    // Guardamos los datos del producto en un array asociativo
    $producto = array(
        'id' => $_POST['producto_id'],
        'nombre' => $_POST['nombre_producto'],
        'precio' => $_POST['precio_producto'],
        'cantidad' => 1 // Por ahora asumimos que agregamos una unidad del producto
    );

    // Verificamos si ya existe un carrito en la sesión del usuario
    if(isset($_SESSION['carrito'])) {
        // Si el producto ya está en el carrito, aumentamos la cantidad en 1
        if(array_key_exists($producto['id'], $_SESSION['carrito'])) {
            $_SESSION['carrito'][$producto['id']]['cantidad']++;
        } else {
            // Si el producto no está en el carrito, lo agregamos al carrito
            $_SESSION['carrito'][$producto['id']] = $producto;
        }
    } else {
        // Si no hay carrito en la sesión del usuario, creamos uno y agregamos el producto
        $_SESSION['carrito'] = array(
            $producto['id'] => $producto
        );
    }

    // Devolvemos una respuesta de éxito
    echo json_encode(array('success' => true));
} else {
    // Si no se recibieron los datos necesarios, devolvemos un mensaje de error
    echo json_encode(array('success' => false, 'message' => 'Error: No se recibieron los datos del producto.'));
}
?>
