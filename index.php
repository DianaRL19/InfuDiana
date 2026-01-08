    <?php
    include_once(dirname(__FILE__) . "/cabecera.php");
    
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
        ]
    ];
    // __________________________________________

    $usuarioVerificado = false;

    if ($acceso->hayUsuario()) { //&& $acceso->puedePermiso(8)
        $usuarioVerificado = true;
    }

    // ___________ _ ___ _

    $filas = [];

    // ___ Sentencia Cons_Productos

    $sentSelect = "nombre, unidades, precio_venta, foto";
    $sentFrom = "cons_productos";
    $sentWhere = "borrado = 0";

    $query = "select $sentSelect from $sentFrom where $sentWhere";

    $consulta = $conex->query($query);

    while ($fila = $consulta->fetch_assoc()) {
        $filas[] = $fila;
    }

    // ______ VALIDACION DE DATOS _____

    if (isset($_POST["comprar"])) {

        $nom_product = $_POST['nombre'];

        if (isset($_POST["unidades"])) {
            $cant_seleccionada = intval($_POST["unidades"]);

            if (!validaEntero($cant_seleccionada, 0, 1000, 1)) {
                paginaError("Has introducido unidades no válidas");
                exit;
            }
        } 

        $existe = false;

        foreach ($_SESSION["carrito"] as &$producto) {

            if ($producto["nombre"] == $nom_product) {
                $producto["unidades"] += $cant_seleccionada;
                $existe = true;
            }
        }

        if (!$existe) {
            $_SESSION["carrito"][] = [
                "nombre" => $nom_product,
                "unidades" => $cant_seleccionada
            ];
        }

        header("location: /aplicacion/productos/carrito.php");
        exit();
    }

    //___________________________________________
    //dibuja la plantilla de vista
    inicioCabecera("Inicio");
    cabecera();
    finCabecera();

    inicioCuerpo("InfuDiana",  $barraUbi);
    // ____________ ____ _ _

    cuerpo($filas, $usuarioVerificado);
    finCuerpo();

    // **********************************************************

    function cabecera() {}

    function cuerpo(array $datos, $usuarioVerificado)
    {
        foreach ($datos as $value) {
    ?>
            <div class="tarjetaProducto" style="text-align: center;">
                <img src="<?php echo "/img/productos/" . $value["foto"] ?>" height="100px">

                <p>
                    <?php echo $value["nombre"] . " " .
                        "<br><span class=\"precio\">" . str_replace(".", ",", round(floatval($value["precio_venta"]), 2))  . "€</span><br>" .
                        ($value["unidades"] > 5 ? "<span class=\"stock\">En stock</span> <span class=\"unidades\">Unidades disponibles: " . $value["unidades"] . "</span>" : ($value["unidades"] <= 5 ? "<span class=\"ultimasUnidades\">En stock</span> <span class=\"unidades\">Unidades disponibles: " . $value["unidades"] . "</span>" : "<span class=\"unidades\">Articulo no disponible</span>"))
                    ?>
                </p>

                <?php
                if ($usuarioVerificado) {
                    echo "<form method=\"post\">
                                <input type=\"text\" name=\"nombre\" value=\"{$value["nombre"]}\" hidden><br>
                                <input type=\"number\" name=\"unidades\" min=\"0\" max=\"{$value["unidades"]}\" value=\"1\" class=\"cantidadComprar\">
                                <input type=\"submit\" value=\"Comprar\" name=\"comprar\" class=\"boton\">
                            </form>";
                }
                ?>
            </div>
        <?php
        }
        ?>
        <br><br><br>
    <?php
    }
