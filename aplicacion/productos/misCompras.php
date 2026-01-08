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
            "TEXTO" => "Mis Compras",
            "LINK" => "./misCompras.php"
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
    if (!$acceso->puedePermiso(8)) {
        paginaError("Lo sentimos. No tienes permisos para acceder a esta página");
        exit;
    }

    $errores = [];
    $filas = [];

    // SENTENCIA CONS_COMPRAS
    $sentSelect = "*";
    $sentFrom = "cons_compras";
    $sentWhere = "where nick = '$nick'";

    $query = "select $sentSelect from $sentFrom $sentWhere";

    $consulta = $conex->query($query);

    //_____________________

    while ($fila = $consulta->fetch_assoc()) {
        $partes = explode("-", $fila["fecha"]);
        $partes[2] = mb_substr($partes[2], 0, 2);
        $fila["fecha"] = $partes[2] . "/" . $partes[1] . "/" . $partes[0];

        $fila["importe_base"] = round($fila["importe_base"], 2) . " €";
        $fila["importe_iva"] = round($fila["importe_iva"], 2) . " €";
        $fila["importe_total"] = round($fila["importe_total"], 2) . " €";

        $fila["operaciones"] = "<a class='botonInfo'  href='./misCompras.php?cod_compra=" . $fila["cod_compra"] . "'>Ver más</a>";
        $filas[] = $fila;
    }

    //____________________________________________
    $cod_compra = 0;
    if (isset($_GET["cod_compra"])) {
        $cod_compra = $_GET["cod_compra"];
    }

    $filas2 = [];

    // SENTENCIA CONS_COMPRA_LINEAS
    $sentSelect = "nombre_producto,unidades,precio_unidad,iva,importe_total";
    $sentFrom = "cons_compra_lineas";
    $sentWhere = "where cod_compra = '$cod_compra'";

    $query = "select $sentSelect from $sentFrom $sentWhere";

    $consulta = $conex->query($query);

    //_____________________

    while ($fila2 = $consulta->fetch_assoc()) {

        $fila2["nombre_producto"] = $fila2["nombre_producto"];
        $fila2["unidades"] = $fila2["unidades"];
        $fila2["precio_unidad"] = round($fila2["precio_unidad"], 2) . " €";
        $fila2["iva"] = $fila2["iva"] . " %";
        $fila2["importe_total"] = round($fila2["importe_total"], 2) . " €";


        $filas2[] = $fila2;
    }

    //___________________________________________
    //dibuja la plantilla de vista
    inicioCabecera("Mis compras");
    cabecera();
    finCabecera();

    inicioCuerpo("InfuDiana",  $barraUbi);
    // ____________ ____ _ _

    cuerpo($filas, $filas2);
    finCuerpo();

    // **********************************************************

    function cabecera() {}

    function cuerpo(array $datos, array $filas2)
    {
    ?>
        <br>
        <section class="bienvenida">
            <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Mis compras<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
        </section>
        <table class="tabla">
            <tr>
                <th>Código compra</th>
                <th>Código usuario</th>
                <th>Fecha</th>
                <th>Importe base</th>
                <th>Importe iva</th>
                <th>Importe total</th>
                <th>Modo de pago</th>
                <th>Datos de cuenta</th>
                <th>Nick</th>
                <th>Nombre</th>
                <th>Más información</th>
            </tr>
            <?php
            for ($i = 0; $i < count($datos); $i++) {
                echo "<tr>";
                foreach ($datos[$i] as $clave => $valor) {
                    echo "<td>{$valor}</td>";
                }
                echo "</tr>";
            }
            ?>
        </table>
        <?php
        if (!empty($filas2)) {
        ?>
            <br><br>
            <table class="tabla">
                <tr>
                    <th>Producto</th>
                    <th>Unidades</th>
                    <th>Precio por unidad</th>
                    <th>Iva</th>
                    <th>Importe total</th>
                </tr>
                <?php
                for ($i = 0; $i < count($filas2); $i++) {
                    echo "<tr>";
                    foreach ($filas2[$i] as $clave => $valor) {
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
    }
