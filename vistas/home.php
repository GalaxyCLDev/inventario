<?php
// Inicia la sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Realiza la conexión a tu base de datos (cambia los valores según tu configuración)
$servername = "localhost";
$username = "root";
$password = "";
$database = "inventario";

$conn = new mysqli($servername, $username, $password, $database);

// Verifica la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta SQL para contar todas las filas en la tabla producto
$sql = "SELECT COUNT(producto_id) AS total_productos FROM producto";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    // Obtiene el resultado de la consulta
    $row = $result->fetch_assoc();
    $total_productos = $row["total_productos"];
} else {
    $total_productos = 0; // Si no hay resultados, establece el total de productos en 0
}

// Cierra la conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Inventario Maggie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .title {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            font-size: 18px;
            margin-bottom: 20px;
        }
        .total-products {
            color: #333;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">Inicio - Inventario Maggie</h1>
        <h2 class="subtitle">¡Bienvenida, al Inventario! <?php echo $_SESSION['nombre']." ".$_SESSION['apellido']; ?>! Recuerda que puedes crear Categorias Nuevas, Para tus Productos.</h2>
        <p class="total-products">Total de productos en el inventario: <?php echo $total_productos; ?></p>
		
        <form action="exportar_excel.php" method="post" class="export-button">
            <button type="submit">Descargar Productos a Excel</button>
        </form>		
    </div>
</body>
</html>
