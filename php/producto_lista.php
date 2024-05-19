<?php
$inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
$tabla = "";

$campos = "producto.producto_id,producto.producto_codigo,producto.producto_nombre,producto.producto_precio,producto.producto_stock,producto.producto_foto,producto.categoria_id,producto.usuario_id,categoria.categoria_id,categoria.categoria_nombre,usuario.usuario_id,usuario.usuario_nombre,usuario.usuario_apellido";

if (isset($busqueda) && $busqueda != "") {
    $consulta_datos = "SELECT $campos FROM producto INNER JOIN categoria ON producto.categoria_id=categoria.categoria_id INNER JOIN usuario ON producto.usuario_id=usuario.usuario_id WHERE producto.producto_codigo LIKE '%$busqueda%' OR producto.producto_nombre LIKE '%$busqueda%' ORDER BY producto.producto_nombre ASC LIMIT $inicio,$registros";

    $consulta_total = "SELECT COUNT(producto_id) FROM producto WHERE producto_codigo LIKE '%$busqueda%' OR producto_nombre LIKE '%$busqueda%'";
} elseif ($categoria_id > 0) {
    $consulta_datos = "SELECT $campos FROM producto INNER JOIN categoria ON producto.categoria_id=categoria.categoria_id INNER JOIN usuario ON producto.usuario_id=usuario.usuario_id WHERE producto.categoria_id='$categoria_id' ORDER BY producto.producto_nombre ASC LIMIT $inicio,$registros";

    $consulta_total = "SELECT COUNT(producto_id) FROM producto WHERE categoria_id='$categoria_id'";
} else {
    $consulta_datos = "SELECT $campos FROM producto INNER JOIN categoria ON producto.categoria_id=categoria.categoria_id INNER JOIN usuario ON producto.usuario_id=usuario.usuario_id ORDER BY producto.producto_nombre ASC LIMIT $inicio,$registros";

    $consulta_total = "SELECT COUNT(producto_id) FROM producto";
}

$conexion = conexion();

$datos = $conexion->query($consulta_datos);
$datos = $datos->fetchAll();

$total = $conexion->query($consulta_total);
$total = (int)$total->fetchColumn();

$Npaginas = ceil($total / $registros);

if ($total >= 1 && $pagina <= $Npaginas) {
    $contador = $inicio + 1;
    $pag_inicio = $inicio + 1;
    foreach ($datos as $rows) {
        $tabla .= '
            <article class="media">
                <figure class="media-left">
                    <p class="image is-64x64">';
        if (is_file("./img/producto/" . $rows['producto_foto'])) {
            $tabla .= '<img src="./img/producto/' . $rows['producto_foto'] . '">';
        } else {
            $tabla .= '<img src="./img/producto.png">';
        }
        $tabla .= '</p>
                </figure>
                <div class="media-content">
                    <div class="content">
                        <p>
                            <strong>' . $contador . ' - ' . $rows['producto_nombre'] . '</strong><br>
                            <strong>CODIGO:</strong> ' . $rows['producto_codigo'] . ', <strong>PRECIO:</strong> $' . $rows['producto_precio'] . ', <strong>STOCK:</strong> ' . $rows['producto_stock'] . ', <strong>CATEGORIA:</strong> ' . $rows['categoria_nombre'] . ', <strong>REGISTRADO POR:</strong> ' . $rows['usuario_nombre'] . ' ' . $rows['usuario_apellido'] . '
                        </p>
                    </div>
                    <div class="has-text-right">
                        <a href="index.php?vista=product_img&product_id_up=' . $rows['producto_id'] . '" class="button is-link is-rounded is-small">Imagen</a>
                        <a href="index.php?vista=product_update&product_id_up=' . $rows['producto_id'] . '" class="button is-success is-rounded is-small">Actualizar</a>
                        <a href="'.$url.$pagina.'&product_id_del=' . $rows['producto_id'] . '" class="button is-danger is-rounded is-small">Vender x 1</a>
                    </div>
                </div>
            </article>

            <hr>
        ';
        $contador++;
    }
    $pag_final = $contador - 1;
} else {
    if ($total >= 1) {
        $tabla .= '
            <p class="has-text-centered" >
                <a href="' . $url . '1" class="button is-link is-rounded is-small mt-4 mb-4">
                    Haga clic acá para recargar el listado
                </a>
            </p>
        ';
    } else {
        $tabla .= '
            <p class="has-text-centered" >No hay registros en el sistema</p>
        ';
    }
}

if ($total > 0 && $pagina <= $Npaginas) {
    $tabla .= '<p class="has-text-right">Mostrando productos <strong>' . $pag_inicio . '</strong> al <strong>' . $pag_final . '</strong> de un <strong>total de ' . $total . '</strong></p>';
}

$conexion = null;
echo $tabla;

// Verifica si se ha solicitado eliminar un producto y agrega la venta
if (isset($_GET['product_id_del'])) {
    $product_id = $_GET['product_id_del'];

    // Consulta para obtener los datos del producto que se va a vender
    $consulta_producto = "SELECT * FROM producto WHERE producto_id = :product_id";
    $stmt_producto = $conexion->prepare($consulta_producto);
    $stmt_producto->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $stmt_producto->execute();

    $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

    // Verifica si hay suficiente stock para vender
    if ($producto && $producto['producto_stock'] > 0) {
        // Actualiza el stock del producto
        $nuevo_stock = $producto['producto_stock'] - 1;
        $actualizar_stock = $conexion->prepare("UPDATE producto SET producto_stock = :stock WHERE producto_id = :id");
        $actualizar_stock->bindParam(':stock', $nuevo_stock, PDO::PARAM_INT);
        $actualizar_stock->bindParam(':id', $product_id, PDO::PARAM_INT);
        $actualizar_stock->execute();

        // Agrega la venta a la tabla de ventas
        $usuario_nombre = $_SESSION['usuario_nombre']; // Asegúrate de tener el nombre de usuario correcto aquí
        $valor_producto = $producto['producto_precio']; // Suponiendo que el precio del producto es el valor de la venta
        $fecha_venta = date('Y-m-d H:i:s'); // Fecha y hora actual

        // Construir la consulta de inserción
        $consulta_insert_venta = "INSERT INTO ventas (fecha_venta, valor_producto, nombre_usuario) VALUES ('$fecha_venta', '$valor_producto', '$usuario_nombre')";

        // Ejecutar la consulta de inserción utilizando db.query
        $result_insert_venta = $db->query($consulta_insert_venta);
        
        // Verificar si la consulta se ejecutó con éxito
        if ($result_insert_venta) {
            echo "Venta agregada correctamente.";
        } else {
            echo "Error al agregar la venta.";
            // Imprimir información de error si es necesario
            // echo $db->error;
        }
    } else {
        echo "No hay suficiente stock para vender.";
    }
}
?>