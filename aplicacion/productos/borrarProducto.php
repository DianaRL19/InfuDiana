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
        "TEXTO" => "Productos",
        "LINK" => "./listaProductos.php"
    ],
    [
        "TEXTO" => "Borrar",
        "LINK" => "./borrarProducto.php"
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
if (!$acceso->puedePermiso(9)) {
    paginaError("Lo sentimos. No tienes permisos para acceder a esta página");
    exit;
}

$errores = [];
$filas = [];

$nom_product = ($_GET["nombre"]);

// SENTENCIA PRODUCTOS
$sentSelect = "*";
$sentFrom = "productos";
$sentWhere = "where nombre = '$nom_product'";

$query = "select $sentSelect from $sentFrom $sentWhere";

$consulta = $conex->query($query);

//_____________________

while ($fila = $consulta->fetch_assoc()) {

    $partes = explode("-", $fila["fecha_alta"]);
    $partes[2] = mb_substr($partes[2], 0, 2);

    $fila["fecha_alta"] = $partes[2] . "/" . $partes[1] . "/" . $partes[0];

    $filas[] = $fila;
}

if ($_POST) {
    if (isset($_POST["borrar"])) {

        $operacion = "UPDATE productos set borrado=true where nombre='" . $filas[0]["nombre"] . "'";
        $conex->query($operacion);

        header("location:./verProducto.php");
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
        <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Borrar Producto<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
    </section>
    <table class="tabla">
        <tr>
            <th>Código Producto</th>
            <th>Código Categoría</th>
            <th>Nombre</th>
            <th>Fabricante</th>
            <th>Fecha de alta</th>
            <th>Unidades</th>
            <th>Precio base</th>
            <th>Iva</th>
            <th>Precio iva</th>
            <th>Precio venta</th>
            <th>Foto</th>
            <th>Borrado</th>
        </tr>
        <?php
        for ($i = 0; $i < count($datos); $i++) {
            echo "<tr>";
            foreach ($datos[$i] as $clave => $valor) {
                if ($clave == "foto") {
                    $imagen = "../../img/productos/" . $valor;
                    echo "<td><img src='$imagen' width='45px'></td>";
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
        <label for="">¿Desea borrar este producto?</label>
        <input type="submit" value="Borrar" name="borrar" class="boton">
        <a href='<?php echo "/aplicacion/productos/listaProductos.php" ?>' class="boton">Cancelar</a>
    </form>
<?php
}
