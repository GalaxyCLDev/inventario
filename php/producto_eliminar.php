<?php
session_start(); // Inicia la sesión

$product_id_del = limpiar_cadena($_GET['product_id_del']);

$check_producto = conexion();
$statement = $check_producto->prepare("SELECT * FROM producto WHERE producto_id=:product_id");
$statement->execute([':product_id' => $product_id_del]);
$datos = $statement->fetch(PDO::FETCH_ASSOC);

if ($datos) {
    $nuevo_stock = $datos['producto_stock'] - 1; // Restar 1 al stock disponible

    $actualizar_stock = conexion();
    $statement = $actualizar_stock->prepare("UPDATE producto SET producto_stock = :nuevo_stock WHERE producto_id = :id");
    $result = $statement->execute([':nuevo_stock' => $nuevo_stock, ':id' => $product_id_del]);

    if ($result) {
        // Mensaje de éxito
        $_SESSION['success_message'] = '¡STOCK ACTUALIZADO! Se ha restado 1 al stock del producto con éxito';
        // Redireccionar de vuelta a la página de donde proviene
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Mensaje de error
        $_SESSION['error_message'] = '¡Ocurrió un error inesperado! No se pudo actualizar el stock del producto, por favor intente nuevamente';
        // Redireccionar de vuelta a la página de donde proviene
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Mensaje de error
    $_SESSION['error_message'] = '¡Ocurrió un error inesperado! El producto que intenta actualizar no existe';
    // Redireccionar de vuelta a la página de donde proviene
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
