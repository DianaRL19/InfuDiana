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
        "TEXTO" => "Usuarios",
        "LINK" => "./index.php"
    ],
    [
        "TEXTO" => "Borrar",
        "LINK" => "./borrarUsuario.php"
    ]
];

// __________________________________________
// Validamos el acceso a la pagina
// Comprobamos si el usuario está validado o no
$nick = $_SESSION["acceso"]["nick"];

if (!$acceso->hayUsuario()) {
    pedirLogin();
    exit;
}

// Si el usuario esta validado comprobamos los permisos que tiene
if (!$acceso->puedePermiso(10)) {
    paginaError("Lo sentimos. No tienes permisos para acceder a esta página");
    exit;
}

$errores = [];
$filas = [];

$codUsu = (intval($_GET["cod_usuario"]));

// SENTENCIA USurarios
$sentSelect = "nick, nombre, nif, direccion, poblacion, provincia, codigo_postal, fecha_nacimiento, borrado, foto";
$sentFrom = "usuarios";
$sentWhere = "where cod_usuario = '$codUsu'";

$query = "select $sentSelect from $sentFrom $sentWhere";

$consulta = $conex->query($query);

//_____________________

while ($fila = $consulta->fetch_assoc()) {
    $partes = explode("-", $fila["fecha_nacimiento"]);
    $fila["fecha_nacimiento"] = $partes[2] . "/" . $partes[1] . "/" . $partes[0];
    $filas[] = $fila;
}

if ($_POST) {
    if (isset($_POST["borrar"])) {

        $acl->setBorrado($filas[0]["cod_usuario"], true);
        $operacion = "UPDATE usuarios set borrado=true where nick='" . $filas[0]["nick"] . "'";
        $conex->query($operacion);

        $operacion = "UPDATE acl_usuarios set borrado=true where nick='" . $filas[0]["nick"] . "'";
        $conex->query($operacion);

        header("location:./verUsuario.php");
        exit;
    }
}

//___________________________________________
//dibuja la plantilla de vista
inicioCabecera("Borrar");
cabecera();
finCabecera();

inicioCuerpo("InfuDiana",  $barraUbi);
// ____________ ____ _ _

cuerpo($filas);
finCuerpo();

// **********************************************************

function cabecera() {}

function cuerpo(array $datos)
{
?>

    <br>
    <section class="bienvenida">
        <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Borrar Usuario<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
    </section>
    <table class="tabla">
        <tr>
            <th>Nick</th>
            <th>Nombre</th>
            <th>Nif</th>
            <th>Dirección</th>
            <th>Población</th>
            <th>Provincia</th>
            <th>Código postal</th>
            <th>Nacimiento</th>
            <th>Borrado</th>
            <th>Foto</th>
        </tr>
        <?php
        for ($i = 0; $i < count($datos); $i++) {
            echo "<tr>";
            foreach ($datos[$i] as $clave => $valor) {
                if ($clave == "foto") {
                    $imagen = "../../imagenes/fotos/" . $valor;
                    echo "<td><img src='$imagen' width='45px' style=\"border-radius:20px\"></td>";
                } else {
                    echo "<td>{$valor}</td>";
                }
            }
            echo "</tr>";
        }
        ?>
    </table>
    <br>
    <form action="" method="post" style="margin-left: 8%;">
        <label for="">¿Desea borrar el usuario?</label>
        <input type="submit" value="Borrar" name="borrar" class="boton">
        <a href='<?php echo "/aplicacion/usuarios/index.php" ?>' class="boton">Cancelar</a>
    </form>
<?php
}
