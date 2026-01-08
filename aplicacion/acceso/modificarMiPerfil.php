<?php
include_once(dirname(__FILE__) . "/../../cabecera.php");

//_____________________
//____ Controlador ____

// Barra de ubicación
/**
 * Cada elemento es un array de 2 posiciones asociativas
 * TEXTO: Titulo de la página
 * LINK: Enlace a la página. Si está vacío, no tiene enlace
 */

$barraUbi = [
    [
        "TEXTO" => "Inicio",
        "LINK" => "../../index.php"
    ],
    [
        "TEXTO" => "Mi perfil",
        "LINK" => "/modificarMiPerfil.php"
    ]
];

// _______________________________________________________________________________________
// _______ COMPROBACIÓN DE QUE HAY UN USUARIO CON LA SESION INICIADA CORRECTAMENTE _______

// Validamos el acceso a la pagina

// Comprobamos si el usuario está validado o no
$nick = $_SESSION["acceso"]["nick"];

if (!$acceso->hayUsuario()) {
    pedirLogin();
    exit;
}

// _____ Declaración Arrays con los datos y errores ____

$errores = [];

$datos = [
    "nombre" => "",
    "direccion" => "",
    "contrasenia" => ""
];

// _______ Sentencia Tabla USUARIOS (nombre y direccion)______

$sentSelect = "nombre, direccion";
$sentFrom = "usuarios";
$sentWhere = "where nick = '$nick'";

$consulta = "select $sentSelect from $sentFrom $sentWhere";

$resultadoConsulta = $conex->query($consulta);

$codUsuario = $acl->getCodUsuario($nick);

foreach ($resultadoConsulta->fetch_assoc() as $clave => $valor) {
    $datos[$clave] = $valor;
}

// _______  Sentencia Tabla ACL_USUARIOS (contraseña)______

$sentSelect = "contrasenia";
$sentFrom = "acl_usuarios";
$sentWhere = "where nick = '$nick'";

$consulta = "select $sentSelect from $sentFrom $sentWhere";

$resultadoConsulta = $conex->query($consulta);

foreach ($resultadoConsulta->fetch_assoc() as $clave => $valor) {
    $datos[$clave] = $valor;
}

if (isset($_POST["modificar"])) {
    $nombre = "";

    if (isset($_POST["nombre"])) {
        $nombre = trim($_POST["nombre"]);

        if ($nombre == "") {
            $errores["nombre"][] = "Debe indicar un nombre.";
        }

        if (!validaCadena($nombre, 50, $datos["nombre"])) {
            $errores["nombre"][] = "No puede contener más de 50 caracteres.";
        }
    }

    $datos["nombre"] = $nombre;

    $direccion = "";

    if (isset($_POST["direccion"])) {

        $direccion = $_POST["direccion"];

        if ($direccion == "") {
            $errores["direccion"][] = "Debe indicar una direccion.";
        }

        if (!validaCadena($direccion, 50, $datos["direccion"])) {
            $errores["direccion"][] = "No puede contener más de 50 caracteres.";
        }
    }

    $datos["direccion"] = $direccion;

    $existeContra = false;

    if (isset($_POST["contrasenia"])) {
        $existeContra = true;
        $contrasenia = trim($_POST["contrasenia"]);

        if ($contrasenia == "") {
            $existeContra = false;
            $errores["contrasenia"][] = "Indica una nueva contraseña.";
        } else if (!validaCadena($contrasenia, 50, "")) {
            $existeContra = false;
            $errores["contrasenia"][] = "No puede contener más de 50 caracteres.";
        }

        $datos["contrasenia"] = $contrasenia;
    }

    if (!$errores) {
        $operacion = "UPDATE usuarios SET nombre='$datos[nombre]',direccion='$datos[direccion]'" .
            "where nick='$nick'";
        $resultadoConsulta = $conex->query($operacion);

        $acl->setNombre($acl->getCodUsuario($acceso->getNick()), $datos["nombre"]);

        if (!$resultadoConsulta) {
            paginaError("Error al actualizar el usuario");
            exit;
        }

        if ($existeContra) {
            $acl->setContrasenia($acl->getCodUsuario($nick), $datos["contrasenia"]);
        }
        header("location:/index.php");
        exit;
    }
}


//___________________________________________
//dibuja la plantilla de vista
inicioCabecera("Mi perfil");
cabecera();
finCabecera();

inicioCuerpo("InfuDiana",  $barraUbi);

// ____________ ____ _ _

cuerpo($datos, $errores); // llamo a la vista
finCuerpo();

// **********************************************************

// vista
function cabecera() {}

//vista

function cuerpo(array $datos, $errores)
{
?>
    <br>
    <section class="bienvenida">
        <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Mi perfil<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
    </section>

    <section class="formularioInicioSesion">
        <form action="" method="post">
            <h2>Mis Datos</h2>
            <hr width="70%" style="margin-left: 10%;">

            <?php
            //mostrar el error del campo usuario
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "usuario") {
                        if (is_array($valor)) {
                            foreach ($valor as $contenido) {
                                echo "<br><p style=\"text-align: center;\">{$contenido}</p>";
                            }
                        } else
                            echo "<br><p style=\"text-align: center;\">{$valor}</p>";
                    }
                }
            }
            ?><br>
            <input type="text" name="nombre" id="nombre" value="<?= $datos["nombre"]; ?>" placeholder="Usuario">

            <?php
            //mostrar el error del campo usuario
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "usuario") {
                        if (is_array($valor)) {
                            foreach ($valor as $contenido) {
                                echo "<br><p style=\"text-align: center;\">{$contenido}</p>";
                            }
                        } else
                            echo "<br><p style=\"text-align: center;\">{$valor}</p>";
                    }
                }
            }
            ?><br>
            <input type="text" name="direccion" id="direccion" value="<?= $datos["direccion"]; ?>" placeholder="Dirección">

            <br>
            <?php
            //mostrar el error del campo contrasenia
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "contrasenia") {
                        if (is_array($valor)) {
                            foreach ($valor as $contenido) {
                                echo "<br><p style=\"text-align: center;\">{$contenido}</p>";
                            }
                        } else
                            echo "<br><p style=\"text-align: center;\">{$valor}</p>";
                    }
                }
            }
            ?>
            <input type="password" name="contrasenia" id="contrasenia" placeholder="Contraseña">

            <br><br>
            <input type="submit" value="Modificar" name="modificar" class="boton">
            <br><br>
            <a href='<?php echo "/index.php" ?>' class="boton">Cancelar</a>
        </form>
    </section>

<?php
}
