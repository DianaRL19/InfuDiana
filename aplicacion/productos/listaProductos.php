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
            "LINK" => "/listaProductos.php"
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

    //__________________________________________________

    $errores = [];

    //____ _______ _____ _
    //   Filtros de sesión

    if (!isset($_SESSION["filtro_nombre"])) {
        $_SESSION["filtro_nombre"] = "";
    }
    if (!isset($_SESSION["filtro_categoria"])) {
        $_SESSION["filtro_categoria"] = "Todos";
    }

    //____________ _ _ _

    $filas = [];

    // ___ Sentencia Cons_Productos

    $sentSelect = "nombre, fabricante, unidades, nombre_categoria, precio_venta, foto";
    $sentFrom = "cons_productos";
    $sentWhere = "";


    if (isset($_POST["filtrar"])) {


        //__ Nombre del producto ___
        $nom_product = "";

        if (isset($_POST["nombre"]) && $_POST["nombre"] != "") {
            $nom_product = trim($_POST["nombre"]);

            if (!validaCadena($nom_product, 30, "")) {
                $errores["nombre"][] = "Indique el nombre del producto.";
            }
        }

        if ($_SESSION["filtro_nombre"] == "" &&  !(isset($_POST["nombre"]))) {
            $_SESSION["filtro_nombre"] = "";
        } else {
            $_SESSION["filtro_nombre"] = $nom_product;
        }

        //__ Nombre de la categoria ___
        $nom_cat = "";

        if (isset($_POST["categoria"]) && $_POST["categoria"] != "") {
            $nom_cat = trim($_POST["categoria"]);

            if (!validaCadena($nom_cat, 50, "")) {
                $errores["categoria"][] = "Indique el nombre de la categoría.";
            }
        }

        if ($_SESSION["filtro_categoria"] == "" &&  !(isset($_POST["categoria"]))) {
            $_SESSION["filtro_categoria"] = "Todos";
        } else {
            $_SESSION["filtro_categoria"] = $nom_cat;
        }
    }

    if (isset($_POST["exportar"])) { // → Lo descargo desde otra pagina para que no me imprima literalemte todo el codigo de esta 
        header("location: /aplicacion/productos/descargar.php");
    }

    if (isset($_POST["cargar"])) {
        if (isset($_FILES["fichero"])) {
            $rutaTemporal = $_FILES["fichero"]["tmp_name"];
            cargar($rutaTemporal, $conex);
        } else {
            $errores["fichero"][] = "No se ha recibido ningún archivo o hubo un error en la subida";
        }
    }

    //_________ BUSCAR POR NOMBRE

    $nombre = $_SESSION["filtro_nombre"];

    if ($nombre != "") {

        $nombre = $conex->escape_string($nombre);

        if ($sentWhere != "") {
            $sentWhere .= " and ";
        } else {
            $sentWhere .= "where ";
        }

        // → Con expresion regular para que no se busque como literal si no que contenga el nombre
        $sentWhere .= "nombre regexp '.*{$nombre}.*'";
    }

    //_________ BUSCAR POR NOMBRE DE CATEGORIA

    $categoria = $_SESSION["filtro_categoria"];

    if ($categoria != "Todos") {

        $categoria = $conex->escape_string($categoria);

        if ($sentWhere != "") {
            $sentWhere .= " and ";
        } else {
            $sentWhere .= "where ";
        }

        // → Con expresion regular para que no se busque como literal si no que contenga el nombre de categoria
        $sentWhere .= "nombre_categoria regexp '.*{$categoria}.*'";
    }

    //_______________

    $query = "select $sentSelect from $sentFrom $sentWhere";
    $consulta = $conex->query($query);

    $categorias = [];

    while ($fila = $consulta->fetch_assoc()) {
        $fila["operacion"] = "<a href='/aplicacion/productos/verProducto.php?nombre=" . $fila["nombre"] . "'><img src='../../img/24x24/ver.png'></a>
                            <a href='/aplicacion/productos/modificarProducto.php?nombre=" . $fila["nombre"] . "'><img src='../../img/24x24/modificar.png'></a>
                            <a href='/aplicacion/productos/borrarProducto.php?nombre=" . $fila["nombre"] . "'><img src='../../img/24x24/borrar.png'></a>";

        $filas[] = $fila;

        if ($fila["nombre_categoria"] == $categoria) {
            $errores["categoria"] = "La categoria indicada no esta disponible o no existe.";
        }

        // sacamos las categorias
        if (!validaRango($fila["nombre_categoria"], $categorias, 1))
            $categorias[] = $fila["nombre_categoria"];
    }

    //___________________________________________
    //dibuja la plantilla de vista
    inicioCabecera("Lista de Productos");
    cabecera();
    finCabecera();

    inicioCuerpo("InfuDiana",  $barraUbi);
    // ____________ ____ _ _

    cuerpo($filas, $categorias, $errores);
    finCuerpo();

    // **********************************************************

    // vista
    function cabecera() {}

    function cuerpo(array $datos, array $categorias, array $errores)
    {
    ?>
        <br>
        <section class="bienvenida">
            <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Listado de Productos<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
        </section>
        <div class="formsListaProductos">
            <!-- Filtrados -->
            <fieldset class="formularioFiltradoProducto">
                <legend>Filtrado de Productos</legend>
                <form method="post" enctype="multipart/form-data">
                    <?php
                    //mostrar el error de Nombre
                    if ($errores) {
                        foreach ($errores as $clave => $valor) {
                            if ($clave == "nombre") {
                                if (is_array($valor)) { //si tiene más de un error
                                    foreach ($valor as $error)
                                        echo "<p class=\"mensaError\">" . $error . "</p>" . PHP_EOL;
                                } else {
                                    echo $valor . "<br>" . PHP_EOL;
                                }
                            }
                        }
                    }
                    ?>
                    <label for="nombre">Nombre: <input type="text" name="nombre" id="nombre" value="<?php echo $_SESSION["filtro_nombre"] ?>"><br></label>

                    <?php
                    //mostrar el error de Nombre Categoria
                    if ($errores) {
                        foreach ($errores as $clave => $valor) {
                            if ($clave == "nombre_categoria") {
                                if (is_array($valor)) { //si tiene más de un error
                                    foreach ($valor as $error)
                                        echo "<p class=\"mensaError\">" . $error . "</p>" . PHP_EOL;
                                } else {
                                    echo $valor . "<br>" . PHP_EOL;
                                }
                            }
                        }
                    }
                    ?>
                    <label for="Categoria">Categoría:

                        <select name="categoria" id="categoria" style="width: 30%;">

                            <option value="Todos"
                                <?php if ($_SESSION["filtro_categoria"] == "Todos")
                                    echo "selected" ?>>Todos</option>
                            <?php

                            foreach ($categorias as $value) {
                                echo "<option value=\"{$value}\"";

                                if ($_SESSION["filtro_categoria"] == $value)
                                    echo "selected";

                                echo ">$value</option>";
                            }
                            ?>
                        </select>

                    </label>

                    <br><br>
                    <input type="submit" value="Filtrar" id="filtrar" name="filtrar" class="boton">
                    <input type="submit" value="Descargar productos" name="exportar" class="boton">
                    <br><br>

                </form>
            </fieldset>
            <br>

            <!-- Cargar -->
            <fieldset class="formularioFiltradoProducto">
                <legend>Cargar fichero</legend>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="file" name="fichero">
                    <input type="submit" value="Cargar fichero" name="cargar" class="boton">
                </form>
            </fieldset>
            <br><br>
        </div>
        <table class="tabla">
            <tr>
                <th>Nombre</th>
                <th>Fabricante</th>
                <th>Unidades</th>
                <th>Categoría</th>
                <th>Precio €</th>
                <th>Foto</th>
                <th>Operaciones</th>
            </tr>
            <?php
            for ($i = 0; $i < count($datos); $i++) {
                echo "<tr>";
                foreach ($datos[$i] as $clave => $valor) {
                    if ($clave != "foto")
                        echo "<td>$valor</td>";
                    else {
                        $imagen = "/img/productos/" . $valor;
                        echo "<td><img src='$imagen' class=\"imgProducto\"></td>";
                    }
                }
                echo "</tr>";
            }
            ?>
        </table>
        <br><br>
        <a href="./nuevoProducto.php" class="bAnadir boton">Añadir <img src="/img/16x16/nuevo.png"></a>
        <br><br><br><br>
    <?php
    }

    function cargar(string $rutaTemporal, $conex): bool
    {
        // ___ Abrimos el archivo desde la ruta temporal
        $fich = fopen($rutaTemporal, "r");

        // ___ Leemos el fichero línea a línea
        while ($linea = fgets($fich)) {

            // Quitamos los saltos de linea y cosas que sobran
            $linea = str_replace(["\r", "\n", "PRODUCTO: "], "", $linea);

            if ($linea != "") {
                $datosCarga = [
                    "cod_categoria" => 0,
                    "nombre" => "",
                    "fabricante" => "",
                    "fecha_alta" => "",
                    "unidades" => 0,
                    "precio" => 0.0,
                    "iva" => 21,
                    "precio_iva" => 0.0,
                    "precio_venta" => 0.0,
                    "foto" => "productoDefault.png"
                ];

                $datosFich = explode(",", $linea);

                // __________ _____ _ _                 
                // Cargo un array con los datos del fichero

                foreach ($datosFich as $value) {

                    $aux = explode(" → ", $value);

                    if (count($aux) >= 2) {
                        $indice = trim($aux[0]);
                        $datoValor = trim($aux[1]);
                        $datosCarga[$indice] = $datoValor;
                    }
                }

                // __ Si no hay nombre y/o fabricante salta la pagina de error
                // __ ya que son datos obligatorios

                if ($datosCarga["nombre"] == "" || $datosCarga["fabricante"] == "") {
                    paginaError("Nombre y fabricante son obligatorios");
                    exit;
                }

                $fechaHoy = new DateTime();

                if ($datosCarga["fecha_alta"] == "") {
                    $datosCarga["fecha_alta"] = $fechaHoy->format("Y-m-d");
                } else {

                    if (!validaFecha($datosCarga["fecha_alta"], $fechaHoy->format("d/m/Y"))) {
                        paginaError("Formato de fecha incorrecto");
                        exit;
                    }

                    $fecha = DateTime::createFromFormat("Y-m-d", $datosCarga["fecha_alta"]);
                    $fechaMinima = DateTime::createFromFormat("Y-m-d", "2010-02-28");

                    if ($fecha < $fechaMinima) {
                        $errores["fecha_alta"][] = "La fecha debe ser posterior al 28/02/2010.";
                    }

                    if ($fecha > $fechaHoy) {
                        $errores["fecha_alta"][] = "La fecha no puede ser posterior a la de hoy.";
                    }

                    $datosCarga["fecha_alta"] = $fecha->format("Y-m-d");
                }

                // ___ Evitamos la inyeccion de codigo
                $datosCarga["nombre"] = $conex->escape_string($datosCarga["nombre"]);
                $datosCarga["nombre_categoria"] = $conex->escape_string($datosCarga["nombre_categoria"]);

                // ____ Preparo la sentencia

                $sentSelect = "*";
                $sentFrom = "cons_productos";

                $query = "SELECT $sentSelect FROM $sentFrom;";
                $consulta = $conex->query($query);

                // _____ _ _

                $productos = [];
                $categorias = [];

                while ($fila = $consulta->fetch_assoc()) {
                    $productos[] = $fila["nombre"];
                    $categorias[$fila["cod_categoria"]] = $fila["nombre_categoria"];
                }

                // Comprobacion de que el nombre no se repita
                if (validaRango($datosCarga["nombre"], $productos, 1)) {
                    paginaError("Producto ya existente");
                    exit;
                }

                // Comprobacion de que la categoria exista
                if (!validaRango($datosCarga["nombre_categoria"], $categorias, 1)) {
                    paginaError("Categoria no existente");
                    exit;
                }

                foreach ($categorias as $clave => $valor) {
                    if ($valor == $datosCarga["nombre_categoria"]) {
                        $datosCarga["cod_categoria"] = $clave;
                    }
                }

                // Comprobacion de que el prcio no sea negativo
                if (!validaReal($datosCarga["precio"], 0, 10000, 0.0)) {
                    paginaError("El precio_base no puede ser negativo");
                    exit;
                }

                // Calcular el precio con IVA
                $datosCarga["precio_iva"] = $datosCarga["precio"] * ($datosCarga["iva"] / 100);
                $datosCarga["precio_venta"]  = $datosCarga["precio"] + $datosCarga["precio_iva"];

                // Comprobacion de que el precio_venta y el precio_iva no sea negativo
                if (!validaReal($datosCarga["precio_iva"], 0, 10000, 0.0) || (!validaReal($datosCarga["precio_venta"], 0, 10000, 0.0))) {
                    paginaError("El precio_iva/precio_venta no puede ser negativo");
                    exit;
                }

                $sentencia = "INSERT INTO productos (cod_categoria, nombre, fabricante, fecha_alta, unidades, 
                    precio_base, iva, precio_iva, precio_venta, foto, borrado)" .
                    " VALUES (" .
                    intval($datosCarga["cod_categoria"]) . ",'" .
                    $datosCarga['nombre'] . "','" .
                    $datosCarga['fabricante'] . "','" .
                    $datosCarga['fecha_alta'] . "'," .
                    intval($datosCarga['unidades']) . "," .
                    floatval($datosCarga['precio']) . "," .
                    floatval($datosCarga['iva']) . "," .
                    floatval($datosCarga['precio_iva']) . "," .
                    floatval($datosCarga['precio_venta']) . ",'" .
                    $datosCarga['foto'] . "'," . intval("0") . ");";

                $consultaultado = $conex->query($sentencia);

                if (!$consultaultado) {
                    paginaError("Error al insertar el producto");
                    exit;
                }
            }
        }

        fclose($fich);
        header("location:/aplicacion/productos/listaProductos.php");
        exit;
    }
