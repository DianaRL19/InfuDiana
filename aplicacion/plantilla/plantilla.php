<?php

function paginaError(string $mensaje)
{
    header("HTTP/1.0 404 $mensaje");
    inicioCabecera("ERROR");
    finCabecera();
    inicioCuerpo("InfuDiana");
    echo "<br />\n";
    echo "<h2>{$mensaje}</h2>";
    echo "<br />\n";
    echo "<br />\n";
    echo "<br />\n";
    echo "<a href='/index.php'>Ir a la pagina principal</a>\n";

    finCuerpo();
}

function inicioCabecera(string $titulo)
{
?>
    <!DOCTYPE html>
    <html lang="es">

    <head>
        <meta charset="utf-8">
        <!---->
        <!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame Remove this if you use the .htaccess -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo $titulo ?></title>
        <meta name="description" content="">
        <meta name="author" content="Administrador">
        <meta name="viewport" content="width=device-width; initialscale=1.0">
        <!-- Replace favicon.ico & apple-touch-icon.png in the root of your domain and delete these references -->
        <link rel="shortcut icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="stylesheet" type="text/css" href="/estilos/base.css">
    <?php
}

function finCabecera()
{
    ?>
    </head>
<?php
}

function inicioCuerpo(string $cabecera, array $barraUbi = [])
{
    global $acceso;
?>
    <body>
        <header id="cabeceraDiana"> <!-- ESTO ES LA BARRA LOGUIN, LE HE CAMBIADO EL NOMBRE PORQUE ME ESTABA LIANDO-->
            <div>
                <img src="../../img/IconoLogo.png" class="imgTitu">
                <h1 id="titulo"><?php echo $cabecera; ?></h1>
                <?php
                if ($acceso->hayUsuario()) {
                    echo "<p class=\"usuarioConectado\">Usuario conectado: " . $acceso->getNombre() . "</p><br>" . PHP_EOL;
                }
                ?>
            </div>
            <ul>
                <?php
                // Muestra la opción de cerrar sesión y de Mi perfil para poder editar y visualizar sus datos
                if ($acceso->hayUsuario()) {
                    echo "<a href='/aplicacion/acceso/modificarMiPerfil.php' class='confPerfil'>Mi perfil</a>" . PHP_EOL;
                    echo "<a href='/aplicacion/acceso/loggout.php' class='iniciaSesion'>Cerrar Sesion</a>" . PHP_EOL;

                    if ($acceso->puedePermiso(8)) { // Perm 8 → Comprar y ver sus propias compras
                        echo "<li><a href='/aplicacion/productos/carrito.php'>Cesta</a></li>";
                        echo "<li><a href='/aplicacion/productos/misCompras.php'>Mis compras</a></li>";
                    }
                    if ($acceso->puedePermiso(9)) {  // Perm 9 → CRUD Compras y Productos
                        echo "<li><a href='/aplicacion/productos/listaProductos.php'>Productos</a></li>";
                    }
                    if ($acceso->puedePermiso(10)) {  // Perm 10 → CRUD Usuarios
                        echo "<li><a href='/aplicacion/usuarios/index.php'>Usuarios</a></li>";
                    }
                } else {
                    // Mostrar la opción de iniciar sesión
                    echo "<a href='/aplicacion/acceso/login.php' class='iniciaSesion inicia'>Iniciar sesión</a>" . PHP_EOL;
                }
                ?>
            </ul>
            <br>
        </header>
        <div id="documento">
            <div id="barraUbicacion">
                <?php
                if ($barraUbi) {
                    foreach ($barraUbi as $elemento) {
                        if (isset($elemento["TEXTO"]) && isset($elemento["LINK"])) {
                            if ($elemento["LINK"]) {
                                echo "<li><a href=\"{$elemento["LINK"]}\">";
                            }
                            echo $elemento["TEXTO"];
                            echo "  ►  ";
                            if ($elemento["LINK"]) {
                                echo "</a></li>";
                            }
                        }
                    }
                }
                ?>
            </div>

            <div>
            <?php
        }

        function finCuerpo()
        {
            ?>
                <br />
                <br />
            </div>
            <footer>
                <div>
                    &copy; Diana Romero León
                </div>
            </footer>
        </div>
    </body>

    </html>
<?php
        }

        function pedirLogin(): void
        {
            $_SESSION["direccionLogin"] = $_SERVER["REQUEST_URI"];
            header("location: /aplicacion/acceso/login.php");
        }
