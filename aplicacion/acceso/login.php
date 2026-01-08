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
        "TEXTO" => "Iniciar Sesión",
        "LINK" => "/index.php"
    ]
];
// __________________________________________

$datos = [
    "usuario" => "",
    "contrasena" => ""
];

$errores = [];

if (isset($_POST["login"])) {

    if (!isset($_POST["usuario"]) || $_POST["usuario"] == "") {
        $errores["usuario"][] = "Error. Debe introducir un usuario";
    } else
        $datos["usuario"] = $_POST["usuario"];

    if (!isset($_POST["contrasena"]) || $_POST["contrasena"] == "") {
        $errores["contrasena"][] = "Error. Debe introducir una contraseña";
    } else {
        $datos["contrasena"] = $_POST["contrasena"];
    }

    // Si no hay errores
    if (!$errores) {

        $nickAux = $datos["usuario"];

        if ($acl->esValido($nickAux, $datos["contrasena"])) { // → Si los datos son correctos
            $acceso->registrarUsuario( // → Si se registra correctamente devolvera true, si no, devolvera false y le añadimos un mensaje de error
                $nickAux,
                $acl->getNombre($acl->getCodUsuario($nickAux)),
                $acl->getPermisos($acl->getCodUsuario($nickAux))
            );
            header("location: /index.php");
            exit;
        } else {
        echo "Usuario o contraseña érronea";
    }
    } 
}
//___________________________________________
//dibuja la plantilla de vista
inicioCabecera("Inicia Sesión");
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

function cuerpo($datos, $errores)
{
?>
    <br>
    <section class="bienvenida">
        <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Bienvenido a nuestra tienda<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
        <h3>Infusiones y té de todo tipo a tu alcance. ¡Descubre todos nuestros productos!</h3>
    </section>

    <section class="formularioInicioSesion">

        <form action="" method="post">
            <h2>Iniciar sesión</h2>
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
            <input type="text" name="usuario" id="usuario" value="<?= $datos["usuario"]; ?>" placeholder="Nick">

            <br>
            <?php
            //mostrar el error del campo contrasena
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "contrasena") {
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
            <input type="password" name="contrasena" id="contrasena" placeholder="Contraseña">

            <br><br>

            <button class="boton" name="login">Iniciar Sesión</button>
        </form>
    </section>

<?php
}
