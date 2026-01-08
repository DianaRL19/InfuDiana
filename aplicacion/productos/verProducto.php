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
            "TEXTO" => "Ver",
            "LINK" => "./verProducto.php"
        ]
    ];

    // __________________________________________
    // Validamos el acceso a la pagina
    // Comprobamos si el Producto está validado o no
    $nick = $_SESSION["acceso"]["nick"];

    if (!$acceso->hayUsuario()) {
        pedirLogin();
        exit;
    }

    // Si el Producto esta validado comprobamos los permisos que tiene
    if (!$acceso->puedePermiso(9)) {
        paginaError("Lo sentimos. No tienes permisos para acceder a esta página");
        exit;
    }

    $errores = [];
    $filas = [];

    $nom_product = ($_GET["nombre"]);

    // SENTENCIA USurarios
    $sentSelect = "cod_producto, nombre, fabricante, nombre_categoria, fecha_alta, unidades, precio_base, iva, precio_iva, precio_venta, foto, borrado";
    $sentFrom = "cons_productos";
    $sentWhere = "where nombre = '$nom_product'";

    $query = "select $sentSelect from $sentFrom $sentWhere";

    $consulta = $conex->query($query);

    //_____________________

    while ($fila = $consulta->fetch_assoc()) {
        $partes = explode("-", $fila["fecha_alta"]);
        $partes[2] = mb_substr($partes[2], 0, 2);
        $fila["fecha_alta"] = $partes[2] . "/" . $partes[1] . "/" . $partes[0];

        $fila["iva"] = $fila["iva"] . "%";

        $fila["operaciones"] = "";

        $fila["operaciones"] .=
            "<a href='modificarProducto.php?nombre={$fila["nombre"]}'> " . "<img src='/img/24x24/modificar.png'>" . "</a>" .
            "<a href='borrarProducto.php?nombre={$fila["nombre"]}'> " . "<img src='/img/24x24/borrar.png'>" . "</a>";
        $filas[] = $fila;
    }


    //___________________________________________
    //dibuja la plantilla de vista
    inicioCabecera("Ver");
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
            <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Datos producto<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
        </section>
        <table class="tabla">
            <tr>
                <th>Código Producto</th>
                <th>Nombre</th>
                <th>Fabricante</th>
                <th>Categoría</th>
                <th>Fecha de alta</th>
                <th>Unidades</th>
                <th>Precio base</th>
                <th>Iva</th>
                <th>Precio iva</th>
                <th>Precio venta</th>
                <th>Foto</th>
                <th>Borrado</th>
                <th>Operaciones</th>
            </tr>
            <?php
            for ($i = 0; $i < count($datos); $i++) {
                echo "<tr>";
                foreach ($datos[$i] as $clave => $valor) {
                    if ($clave == "foto") {
                        $imagen = "../../img/productos/" . $valor;
                        echo "<td><img src='$imagen' class=\"imgProducto2\"></td>";
                    } else {
                        echo "<td>{$valor}</td>";
                    }
                }
                echo "</tr>";
            }
            ?>
        </table>
    <?php
    }
