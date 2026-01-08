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
            "TEXTO" => "Modificar",
            "LINK" => "./modificarProducto.php"
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

    $nom_product = ($_GET["nombre"]);

    //__________________________________________________

    $datos = [
        "nombre" => "",
        "fabricante" => "",
        "categoria" => "",
        "fecha_alta" => "",
        "unidades" => "",
        "precio_base" => "",
        "iva" => "",
        "foto" => ""
    ];

    $errores = [];


    // SENTENCIA Sacar los datos del Producto
    $sentSelect = "*";
    $sentFrom = "cons_productos";
    $sentWhere = "where nombre = '$nom_product'";

    $query = "select $sentSelect from $sentFrom $sentWhere";

    $consulta = $conex->query($query);

    //_____________________

    foreach ($consulta->fetch_assoc() as $clave => $valor) {
        if ($clave == "fecha_alta") {
            $partes = explode("-", $valor);
            $partes[2] = mb_substr($partes[2], 0, 2);
            $datos["fecha_alta"] = $partes[2] . "/" . $partes[1] . "/" . $partes[0];
        } else if ($clave == "cod_categoria") {
            $datos["categoria"] = $valor;
        } else {
            $datos[$clave] = $valor;
        }
    }

    // ___ Consulta rapida para obtener el nombre de los productos y el codigo y nombre de las categorias _________
    //____________ _ _ _

    $filas = [];

    //________
    // ___ Sentencia Cons_Productos

    $sentSelect = "nombre,cod_categoria,nombre_categoria";
    $sentFrom = "cons_productos";
    $sentWhere = "";

    $query = "select $sentSelect from $sentFrom";
    $consulta = $conex->query($query);

    $categorias = [];
    $productos = [];

    while ($fila = $consulta->fetch_assoc()) {
        $productos[] = $fila["nombre"];
        $categorias[$fila["cod_categoria"]] = $fila["nombre_categoria"];
    }

    //_______________ _ _ __ _
    // ____ VALIDAMOS LOS DATOS QUE NOS LLEGAN DEL FORMULARIO ___

    if (isset($_POST["modificar"])) {

        // ____ VALIDACION CAMPO NOMBRE ____
        $nombre = "";

        if (isset($_POST["nombre"])) {
            $nombre = $_POST["nombre"];

            if ($nombre == "") {
                $errores["nombre"][] = "Debe indicar el nombre del producto.";
            }

            if (validaRango($nombre, $productos, 1)) {
                $errores["nombre"][] = "Ya existe un producto con ese nombre.";
            }

            if (!validaCadena($nombre, 30, $datos["nombre"])) {
                $errores["nombre"][] = "El nombre no puede contener más de 30 caracteres.";
            }
        } else {
            $errores["nombre"][] = "Debe indicar el nombre del producto.";
        }

        $datos["nombre"] = $nombre;

        // ____ VALIDACION CAMPO FABRICANTE ____
        $fabricante = "";

        if (isset($_POST["fabricante"])) {
            $fabricante = $_POST["fabricante"];

            if ($fabricante == "") {
                $errores["fabricante"][] = "Debe indicar un fabricante.";
            }

            if (!validaCadena($fabricante, 30, $datos["fabricante"])) {
                $errores["fabricante"][] = "No puede contener más de 50 caracteres.";
            }
        }

        $datos["fabricante"] = $fabricante;

        // ____ VALIDACION CAMPO CATEGORIA ____
        $categoria = "";

        if (isset($_POST["categoria"])) {

            $categoria = $_POST["categoria"];

            if ($categoria == "") {
                $errores["categoria"][] = "Debe indicar un categoria";
            }

            if (!validaRango($categoria, $categorias, 2)) {
                $errores["categoria"][] = "La categoria seleccionada no es válida.";
            }
        }

        $datos["categoria"] = $categoria;

        // ____ VALIDACION DE LA FECHA DE ALTA ____
        $fecha_alta = "";

        if (isset($_POST["fecha_alta"])) {

            $fecha_alta = $_POST["fecha_alta"];

            if ($fecha_alta == "") {
                $errores["fecha_alta"][] = "Debe indicar una fecha de alta";
            }

            if (!validaFecha($fecha_alta, $datos["fecha_alta"])) {
                $errores["fecha_alta"][] = "Formato de fecha incorrecto";
            }

            $partes = explode("/", $fecha_alta);
            $fecha_alta = $partes[2] . "-" . $partes[1] . "-" . $partes[0];

            $fecha = DateTime::createFromFormat("Y-m-d", $fecha_alta);
            $fechaMinima = DateTime::createFromFormat("Y-m-d", "2010-02-28");
            $fechaHoy = new DateTime();

            if ($fecha < $fechaMinima) {
                $errores["fecha_alta"][] = "La fecha debe ser posterior al 28/02/2010.";
            }

            if ($fecha > $fechaHoy) {
                $errores["fecha_alta"][] = "La fecha no puede ser posterior a la de hoy.";
            }
        }

        $datos["fecha_alta"] = $fecha->format("Y-m-d");

        // ____ VALIDACION CAMPO UNIDADES ____
        $unidades = "";

        if (isset($_POST["unidades"])) {

            $unidades = $_POST["unidades"];

            if ($unidades == "") {
                $errores["unidades"][] = "Debe indicar una catidad de articulos.";
            }

            if (!validaEntero($unidades, 0, 1000, 0)) {
                $errores["unidades"][] = "Debe indicar una catidad válida.";
            }
        }

        $datos["unidades"] = $unidades;

        // ____ VALIDACION CAMPO PRECIO BASE ____
        $precio_base = 0.0;

        if (isset($_POST["precio_base"])) {

            if ($_POST["precio_base"] == "") {
                $errores["precio_base"][] = "Debe indicar un precio base.";
            } else {
                $precio_base = floatval($_POST["precio_base"]);
            }

            if (!validaReal($precio_base, 0, 10000, $_POST["precio_base"])) {
                $errores["precio_base"][] = "El precio indicado no es válido.";
            }
        }

        $datos["precio_base"] = $precio_base;


        // ____ VALIDACION CAMPO IVA ____
        $iva = 0;

        if (isset($_POST["iva"])) {

            if ($_POST["iva"] == "") {
                $errores["iva"][] = "Debe indicar un iva.";
            } else {
                $iva = floatval($_POST["iva"]);
            }

            if (!validaReal($iva, 0, 100, $_POST["iva"])) {
                $errores["iva"][] = "El iva indicado no es válido";
            }

            $datos["iva"] = $iva;
        }

        // ____ VALIDACION DE LA FOTO ____

        $foto =  (str_replace(" ", "", trim($datos["nombre"]))) . "_" . $datos["fabricante"] . ".png";
        $imagenOrigen = "";

        if (isset($_FILES["foto"])) {
            if (($_FILES["foto"]["error"] == 0) || ($_FILES["foto"]["type"] == "image/png")) {
                $imagenOrigen = $_FILES["foto"]["tmp_name"];
                $datos["foto"] = $foto;
            }
        }

        $archivo_temporal = $_FILES["foto"]["tmp_name"];

        // Ruta donde se guardará la foto
        $rutaFoto = $_SERVER["DOCUMENT_ROOT"] . "/img/productos/" . $datos["foto"];
        move_uploaded_file($archivo_temporal, $rutaFoto);

        // SI NO HAY ERRORES
        if (!$errores) {
            $datos["categoria"] = intval($datos["categoria"]);
            $datos["fabricante"] = trim($datos["fabricante"]);
            $datos["fecha_alta"] = $datos["fecha_alta"];
            $datos["unidades"] = intval($datos["unidades"]);
            $datos["precio_base"] = floatval($datos["precio_base"]);
            $datos["iva"] = floatval($datos["iva"]);
            $datos["precio_iva"] = floatval(($datos["precio_base"] * $datos["iva"]) / 100);
            $datos["precio_venta"] = floatval($datos["precio_iva"] + $datos["precio_base"]);
            $datos["foto"] = trim($datos["foto"]);
        }

        // ______ SENTENCIA PARA ACTUALIZAR _______

        // ACTUALIZAR PRODUCTO

        $sentenciaUpdate = "UPDATE productos 
                            SET
                            cod_categoria = '$datos[categoria]',
                            fabricante = '$datos[fabricante]',
                            fecha_alta = '$datos[fecha_alta]',
                            unidades = '$datos[unidades]',
                            precio_base = '$datos[precio_base]',
                            iva = '$datos[iva]',
                            precio_iva = '$datos[precio_iva]',
                            precio_venta = '$datos[precio_venta]',
                            borrado = " .
            0
            . ",
                            foto = '$datos[foto]'
                            WHERE nombre = '" . $nom_product . "'";

        $consulta = $conex->query($sentenciaUpdate);

        if (!$consulta) {
            paginaError("Error al modificar el usuario.");
            exit;
        }

        $ruta = RUTABASE . "/img/productos/" . "$foto";
        if (move_uploaded_file($imagenOrigen, $ruta)) {
            $consultaFoto = "UPDATE productos SET foto='$foto'" .
                "   where nombre='" . $datos["nombre"] . "'";

            $consultaFoto = $conex->query($consultaFoto);
        }
        header("Location: /aplicacion/productos/verProducto.php?nombre=$nom_product");
        exit;
    }
    //___________________________________________
    //dibuja la plantilla de vista
    inicioCabecera("Modificar");
    cabecera();
    finCabecera();

    inicioCuerpo("InfuDiana",  $barraUbi);
    // ____________ ____ _ _

    cuerpo($datos, $errores, $categorias);
    finCuerpo();

    // **********************************************************

    // vista
    function cabecera() {}

    function cuerpo(array $datos, array $errores, array $categorias)
    {

    ?>
        <br>
        <section class="bienvenida">
            <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Modificar Producto<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
        </section>
        <form action="" enctype="multipart/form-data" method="post" class="formularioFiltrado">
            <?php
            if ($datos["foto"] != "")
                echo "<img src=\"../../img/productos/" . $datos["foto"] . "\" class=\"imgUsuario2\"><br>";
            ?>

            <br>
            <?php
            //mostrar el error de NOMBRE
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
            <label for="">Nombre: </label>
            <input type="text" name="nombre" value="<?php echo $datos["nombre"] ?>" disabled>
            <br>

            <?php
            //mostrar el error de Fabricante
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "fabricante") {
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
            <label for="">Fabricante: </label>
            <input type="text" name="fabricante" value="<?php echo $datos["fabricante"] ?>">
            <br>

            <?php
            //mostrar el error de CATEGORIAS
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "categoria") {
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
            <label for="">Categoría: </label>
            <select name="categoria">
                <option value="" selected>Seleccione una categoria</option>
                <?php
                foreach ($categorias as $cod => $nom) {
                    echo "<option value=\"{$cod}\"";
                    if ($cod == $datos["categoria"]) {
                        echo "selected>$nom</option>";
                    } else
                        echo ">$nom</option>";
                }
                ?>
            </select>
            <br>

            <?php
            //mostrar el error de FECHA DE ALTA
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "fecha_alta") {
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
            <label for="">Fecha de alta: </label>
            <input type="text" name="fecha_alta" value="<?php echo $datos["fecha_alta"]; ?>">
            <br>

            <?php
            //mostrar el error de UNIDADES
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "unidades") {
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
            <label for="">Unidades: </label>
            <input type="number" name="unidades" value="<?php echo $datos["unidades"]; ?>">
            <br>

            <?php
            //mostrar el error de Precio bse
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "precio_base") {
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
            <label for="">Precio base: </label>
            <input type="number_format" name="precio_base" value="<?php echo $datos["precio_base"]; ?>">
            <br>

            <?php
            //mostrar el error de IVA
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "iva") {
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
            <label for="">IVA: </label>
            <input type="number_format" name="iva" value="<?php echo $datos["iva"]; ?>" placeholder="%">
            <br>

            <?php
            //mostrar el error de FOTO
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "foto") {
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
            <label for="">Foto: </label>
            <input type="file" name="foto">

            <br><br>

            <input type="submit" value="Modificar" name="modificar" class="boton">
            <a href="/aplicacion/productos/listaProductos.php" class="boton">Cancelar</a>
        </form>
        <br><br><br>
    <?php
    }
