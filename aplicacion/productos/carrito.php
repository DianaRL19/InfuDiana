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
            "TEXTO" => "Carrito",
            "LINK" => ""
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

    // // Si el usuario esta validado comprobamos los permisos que tiene
    if (!$acceso->puedePermiso(8)) {
        paginaError("Lo sentimos. No tienes permisos para acceder a esta página");
        exit;
    }

    // ___________ _ ___ _

    $filas = [];

    $productosCesta = [];

    foreach ($_SESSION["carrito"] as $clave => $valor) {
        $productosCesta[$valor["nombre"]] = intval($valor["unidades"]);
    }

    // ___________ _ ___ _

    $datos = [
        "cod_usuario" => "",
        "cod_producto" => "",
        "fecha" => "",
        "importe_base" => 0.0,
        "importe_iva" => 0.0,
        "importe_total" => 0.0,
        "modoPago" => "",
        "datosCuenta" => "",
        "precio_unidad" => 0,
        "iva" => 0,
        "total" => 0
    ];

    $errores = [];
    $contOrden = 1;
    $totalPrecio = 0.0;

    $resumen = "<div class=\"tarjetaTicket\"><h3 class=\"tituTicket\">Compra realizada con exito</h3>";

    //____________ _ _ _
    // ___ Consulta para obtener codigo de usuario _________

    $sentSelect = "cod_usuario";
    $sentFrom = "usuarios";
    $sentWhere = "where nick = '$nick'";

    $query = "select $sentSelect from $sentFrom $sentWhere";
    $consulta = $conex->query($query);

    while ($fila = $consulta->fetch_assoc()) {
        $datos["cod_usuario"] = intval($fila["cod_usuario"]);
    }

    //____________ _ _ _
    // ___ Sentencia Cons_Productos

    $sentSelect = "nombre, unidades, precio_venta, foto";
    $sentFrom = "cons_productos";

    foreach ($productosCesta as $clave => $valor) {
        $sentWhere = "nombre = '" . $clave . "'";
        $query = "select $sentSelect from $sentFrom where $sentWhere";

        $consulta = $conex->query($query);

        while ($fila = $consulta->fetch_assoc()) {
            $filas[] = $fila;

            $datos["total"] += $valor * $fila["precio_venta"];
        }
    }

    // ___ Quitar producto de la cesta

    if (isset($_POST["quitarProducto"])) {
        foreach ($_SESSION["carrito"] as $clave => $valor) {
            if ([$_POST["nombre"]][0] == $valor["nombre"]) {
                array_splice($_SESSION["carrito"], $clave);
            }
        }
        header("location: /aplicacion/productos/carrito.php"); // Refresacar para que se vuelvan a visualizar los productos
        exit();
    }

    // ___ Sumar o restar unidades al rpducto de la cesta
    if (isset($_POST["actualizarProducto"])) {

        foreach ($_SESSION["carrito"] as $clave => $valor) {

            if ([$_POST["nombre"]][0] == $valor["nombre"]) {

                $unidadesBaseDatos = intval($filas[$clave]["unidades"]);
                $unidadesCarrito = intval($_SESSION["carrito"][$clave]["unidades"]);
                $unidadesNuevas = intval($_POST["unidades"]);

                if (validaEntero($unidadesNuevas, 1, $unidadesBaseDatos, $unidadesCarrito)) {
                    $_SESSION["carrito"][$clave]["unidades"] = $_POST["unidades"];
                }
            }
        }
        header("location: /aplicacion/productos/carrito.php"); // Refresacar para que se vuelvan a visualizar los productos
        exit();
    }

    // ___ Finalizar compra
    if (isset($_POST["finalizarCompra"])) {

        // __ VALIDACION DE QUE EL PRODUCTO EXISTA
        foreach ($_SESSION["carrito"] as $clave => $valor) {

            // __ VALIDACION DE NOMBRE

            $nombreBaseDatos = $filas[$clave]["nombre"];

            foreach ($filas as $indice => $campo) {
                if (!validaRango($campo["nombre"], $productosCesta, 2)) {
                    $errores["nombre"][] = "El producto seleccionado no está disponible.";
                } else {
                    $datos["nombre"] = $campo["nombre"];
                }
            }

            // __ VALIDACION DE UNIDADES
            $unidadesBaseDatos = intval($filas[$clave]["unidades"]);
            $unidadesCarrito = intval($_SESSION["carrito"][$clave]["unidades"]);

            if (isset($_POST["unidades"])) {
                $unidadesCarrito = intval($_POST["unidades"]);
            }

            // Comprobamos la cantidad de unidades disponicles en la base de datos antes de confirmar la compra

            if (!validaEntero($unidadesCarrito, 1, $unidadesBaseDatos, $unidadesCarrito)) {
                $errores["unidades"][] = "No quedan suficientes unidades disponibles del producto seleccionado.";
            }

            // __ VALIDACION DE PAGO

            $modoPago = $_POST["opcionesPago"];

            if ($modoPago != "") {
                if (validaCadena($modoPago, 15, "")) {
                    $datos["modoPago"] = $modoPago;
                } else {
                    $errores["modoPago"][] = "No disponemos de la opción de pago seleccionada.";
                }
            }

            $datosCuenta = $_POST["datosCuenta"];

            if ($datosCuenta != "") {
                if (validaCadena($datosCuenta, 15, "")) {
                    $datos["datosCuenta"] = $datosCuenta;
                } else {
                    $errores["datosCuenta"][] = "Datos de cuenta inválidos.";
                }
            } else {
                $errores["datosCuenta"][] = "Datos de cuenta inválidos.";
            }
        }

        // __ INSERCIÓN DE LA COMPRA EN LA BASE DE DATOS
        foreach ($productosCesta as $nombre => $unidades) {

            // ________ ___ __ _
            // SI NO HAY ERRORES
            if (!$errores) {

                $sentSelect = "*";
                $sentFrom = "productos";

                $sentWhere = "nombre = '" . $nombre . "'";
                $query = "select $sentSelect from $sentFrom where $sentWhere";

                $consulta = $conex->query($query);

                while ($fila = $consulta->fetch_assoc()) {
                    $datos["cod_producto"] = intval($fila["cod_producto"]);
                    $datos["precio_unidad"] = floatval($fila["precio_base"]);
                    $datos["iva"] = floatval($fila["iva"]);
                    $datos["importe_base"] = $datos["precio_unidad"] * $unidadesCarrito;
                    $datos["importe_iva"] = floatval($datos["importe_base"] * $datos["iva"] / 100);
                    $datos["importe_total"] = floatval($datos["importe_base"]) + $datos["importe_iva"];

                    if (intval($fila["unidades"]) > $unidades) {
                        $datos["unidades_restantes"] = intval($fila["unidades"]) - $unidades;
                    } else {
                        $datos["unidades_restantes"] = 0;
                    }
                }

                $datos["fecha"] = (new DateTime())->format("Y-m-d");


                // INSERTAR COMPRA
                $sentencia = "INSERT INTO compras (cod_usuario, fecha, importe_base, importe_iva, importe_total, modo_pago, datos_pago)" .
                    " VALUES (" .
                    $datos["cod_usuario"] . ",'" .
                    $datos["fecha"] . "'," .
                    $datos["importe_base"] . "," .
                    $datos["importe_iva"] . "," .
                    $datos["importe_total"] . ",'" .
                    $datos["modoPago"] . "','" .
                    $datos["datosCuenta"] . "');";

                $consulta = $conex->query($sentencia);

                if (!$consulta) {
                    paginaError("Error al realizar la compra.");
                    exit;
                }

                $idCompra[] = $conex->insert_id;

                // INSERTAR LINEA DE COMPRA
                $sentencia = "INSERT INTO compra_lineas (cod_compra, cod_producto, orden, unidades, precio_unidad, iva, importe_base, 
                importe_iva, importe_total) 
                VALUES (" .
                    $idCompra[($contOrden - 1)] . "," .
                    $datos["cod_producto"] . "," .
                    $contOrden . "," .
                    $unidades . "," .
                    $datos["precio_unidad"] . "," .
                    $datos["iva"] . "," .
                    $datos["importe_base"] . "," .
                    $datos["importe_iva"] . "," .
                    $datos["importe_total"] . ");";

                $consulta = $conex->query($sentencia);

                if (!$consulta) {
                    paginaError("Error al realizar la compra.");
                    exit;
                }

                $contOrden++;

                //_______ _____ _ _

                // ACTUALIZAR UNIDADES PRODUCTO
                $sentencia = "UPDATE productos SET unidades = " . $datos["unidades_restantes"] . " where nombre = '$nombre';";
                $consulta = $conex->query($sentencia);

                if (!$consulta) {
                    paginaError("Error al actualizar el producto.");
                    exit;
                }
            }
        }

        // ______ CREO EL RESUMEN _______

        $totalPrecio = 0.0;

        $sentSelect = "*";
        $sentFrom = "cons_compra_lineas";

        $cont = 0;
        foreach ($idCompra as $indice => $id) {
            $sentWhere = "cod_compra = " . $id;
            $query = "select $sentSelect from $sentFrom where $sentWhere;";

            $consulta = $conex->query($query);

            while ($fila = $consulta->fetch_assoc()) {
                $totalPrecio += floatval($fila["importe_total"]);
                $resumen .= "<br><p>" . $fila["nombre_producto"] . " <span class=\"precioProductoResumen\">" . round(floatval($fila["importe_total"]), 2) . " €</span></p>";
                $resumen .= "<p class=\"unidades2\">Unidades: " . $fila["unidades"] . "</p><hr>";
            }
        }

        $resumen .= "<p style=\"text-align: right;\">Total " . round($totalPrecio, 2) . " €</p>";
        $resumen .= "</div>";

        $_SESSION["carrito"] = [];
        $productosCesta = [];
    }


    //___________________________________________
    //dibuja la plantilla de vista
    inicioCabecera("Inicio");
    cabecera();
    finCabecera();

    inicioCuerpo("InfuDiana",  $barraUbi);
    // ____________ ____ _ _

    cuerpo($filas, $productosCesta, $datos, $resumen);
    finCuerpo();

    // **********************************************************

    function cabecera() {}

    function cuerpo(array $filas, array $productosCesta, $datos, $resumen)
    {
        if (empty($productosCesta)) {
            echo "<br>" . $resumen;
        } else {
            cesta($filas, $productosCesta, $datos);
        }
    }

    function cesta(array $filas, array $productosCesta, $datos)
    {
    ?>
        <br>
        <?php
        foreach ($filas as $clave => $valor) {

        ?>
            <div class="tarjetaProductosCesta">
                <input type="text" value="" name="<?php echo $valor["nombre"] ?>" hidden>
                <img src="<?php echo "/img/productos/" . $valor["foto"]; ?>" height="100px" class="imgProCesta">
                <h3 class="nomTituCesta"><?php echo $valor["nombre"]; ?>
                    <span class="precioProCesta"><?php echo str_replace(".", ",", round(floatval($valor["precio_venta"]), 2)); ?> €</span>
                </h3>
                <p>
                    <?php
                    echo ($valor["unidades"] > 5 ?
                        "<span class=\"stock\">En stock</span> <span class=\"unidades\">Unidades disponibles: " . $valor["unidades"] . "</span><br>" : ($valor["unidades"] <= 5 ? "<span class=\"ultimasUnidades\">En stock</span> <span class=\"unidades\">Unidades disponibles: " . $valor["unidades"] . "</span><br>" :
                            "<span class=\"unidades\">Articulo no disponible</span><br>")) ?>
                </p>

                <form action="" enctype="multipart/form-data" method="post">
                    <input type="text" name="nombre" value="<?php echo $valor["nombre"]; ?>" hidden><br>
                    <input type="number" name="unidades" min="1" max="<?php echo $valor["unidades"]; ?>" value="<?php echo $productosCesta[$valor["nombre"]]; ?>" class="cantProCesta">
                    <input type="submit" name="quitarProducto" value="X" class="boton botonCesta">
                    <input type="submit" name="actualizarProducto" value="Actualizar" class="boton botonCesta botonCesta2">
                </form>
            </div>
            <br>
        <?php

        } // Cieroo el foreach

        ?>
        <form action="" enctype="multipart/form-data" method="post">
            <div class="tarjetaPagoCesta" style="text-align: center;">
                <h3 class="letrasRojitas" style="text-align: right;">Total: <span><?php echo round($datos["total"], 2) ?>€</span></h3>
                <br>
                <label class="letrasRojitas">Seleccione el modo de pago:</label>
                <select name="opcionesPago" class="opcionesPago">
                    <option value="tarjeta">Tarjeta</option>
                    <option value="transferencia">Transferencia</option>
                </select>
                <label style="margin-left: 2%;" class="letrasRojitas">Datos de cuenta:</label>
                <input type="text" class="opcionesPago" name="datosCuenta">
            </div>
            <input type="submit" class="boton botonFinalizar" name="finalizarCompra" value="Finalizar compra">
        </form>
        <br><br><br>
    <?php
    }
