<?php
session_start(); // Iniciamos la sesión al principio del archivo

/*== Almacenando datos ==*/
$usuario = limpiar_cadena($_POST['login_usuario']);
$clave = limpiar_cadena($_POST['login_clave']);

/*== Verificando campos obligatorios ==*/
if ($usuario == "" || $clave == "") {
    mostrar_error("No has llenado todos los campos que son obligatorios");
}

/*== Verificando integridad de los datos ==*/
if (verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
    mostrar_error("El USUARIO no coincide con el formato solicitado");
}

if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave)) {
    mostrar_error("La CLAVE no coincide con el formato solicitado");
}

try {
    $conexion = conexion();

    $query = "SELECT * FROM usuario WHERE usuario_usuario = :usuario";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(":usuario", $usuario);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        if (password_verify($clave, $row['usuario_clave'])) {
            $_SESSION['id'] = $row['usuario_id'];
            $_SESSION['nombre'] = $row['usuario_nombre'];
            $_SESSION['apellido'] = $row['usuario_apellido'];
            $_SESSION['usuario'] = $row['usuario_usuario'];

            header("Location: index.php?vista=home");
            exit();
        } else {
            mostrar_error("Usuario o clave incorrectos");
        }
    } else {
        mostrar_error("Usuario o clave incorrectos");
    }
} catch (PDOException $e) {
    mostrar_error("Error de conexión a la base de datos: " . $e->getMessage());
}

function mostrar_error($mensaje) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrió un error inesperado!</strong><br>
            ' . $mensaje . '
        </div>
    ';
    exit();
}
?>