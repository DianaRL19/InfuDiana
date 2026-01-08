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
            "TEXTO" => "Modificar",
            "LINK" => "./modificarUsuario.php"
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

    $codUsu = (intval($_GET["cod_usuario"]));

    //__________________________________________________

    $datos = [
        "cod_usuario" => "",
        "nick" => "",
        "nombre" => "",
        "contrasenia" => "",
        "nif" => "",
        "direccion" => "",
        "poblacion" => "",
        "provincia" => "",
        "codigo_postal" => "",
        "fecha_nacimiento" => "",
        "foto" => "",
    ];

    $errores = [];

    $codUsu = (intval($_GET["cod_usuario"]));

    // SENTENCIA USurarios
    $sentSelect = "*";
    $sentFrom = "usuarios";
    $sentWhere = "where cod_usuario = '$codUsu'";

    $query = "select $sentSelect from $sentFrom $sentWhere";

    $consulta = $conex->query($query);


    //_____________________


    foreach ($consulta->fetch_assoc() as $clave => $valor) {
        if ($clave == "nick") {
            $datos["nick"] = $valor;
        } else if ($clave == "fecha_nacimiento") {
            $partes = explode("-", $valor);
            $datos["fecha_nacimiento"] = $partes[2] . "/" . $partes[1] . "/" . $partes[0];
        } else

            $datos[$clave] = $valor;
    }

    //_________
    if (isset($_POST['Modificar'])) {

        // ____ VALIDACION CAMPO NOMBRE ____
        $nombre = "";

        if (isset($_POST["nombre"])) {
            $nombre = $_POST["nombre"];

            if ($nombre == "") {
                $errores["nombre"][] = "Debe indicar un nombre.";
            }

            if (!validaCadena($nombre, 50, $datos["nombre"])) {
                $errores["nombre"][] = "No puede contener más de 50 caracteres.";
            }
        }

        $datos["nombre"] = $nombre;

        // ____ VALIDACION CAMPO CONTRASEÑA ____
        $contrasenia = "";

        if (isset($_POST["contrasenia"])) {
            $contrasenia = $_POST["contrasenia"];

            if ($contrasenia == "") {
                $errores["contrasenia"][] = "Debe indicar un nombre.";
            }

            if (!validaCadena($contrasenia, 50, $datos["contrasenia"])) {
                $errores["contrasenia"][] = "No puede contener más de 50 caracteres.";
            }
        }

        $datos["contrasenia"] = $contrasenia;

        // ____ VALIDACION CAMPO NIF ____
        $nif = "";

        if (isset($_POST["nif"])) {

            $nif = $_POST["nif"];

            if ($nif == "") {
                $errores["nif"][] = "Debe indicar un nif";
            }

            if (!validaCadena($nif, 10, $datos["nif"])) {
                $errores["nif"][] = "No puede contener más de 10 caracteres.";
            }
        }

        $datos["nif"] = $nif;

        // ____ VALIDACION CAMPO DIRECCION ____
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

        // ____ VALIDACION CAMPO POBLACION ____
        $poblacion = "";

        if (isset($_POST["poblacion"])) {

            $poblacion = $_POST["poblacion"];

            if ($poblacion == "") {
                $errores["poblacion"][] = "Debe indicar una poblacion.";
            }

            if (!validaCadena($poblacion, 30, $datos["poblacion"])) {
                $errores["poblacion"][] = "No puede contener más de 30 caracteres";
            }
        }

        $datos["poblacion"] = $poblacion;

        // ____ VALIDACION CAMPO PROVINCIA ____
        $provincia = "";
        if (isset($_POST["provincia"])) {

            $provincia = $_POST["provincia"];

            if ($provincia == "") {
                $errores["provincia"][] = "Debe indicar una provincia.";
            }

            if (!validaCadena($provincia, 30, $datos["provincia"])) {
                $errores["provincia"][] = "No puede contener más de 30 caracteres.";
            }
        }

        $datos["provincia"] = $provincia;

        // ____ VALIDACION codigo_postal (CODIGO POSTAL) ____
        $codigo_postal = "";
        if (isset($_POST["codigo_postal"])) {

            $codigo_postal = $_POST["codigo_postal"];

            if ($direccion == "") {
                $errores["codigo_postal"][] = "Debe indicar un código postal.";
            }

            if (!validaCadena($codigo_postal, 5, $datos["codigo_postal"])) {
                $errores["codigo_postal"][] = "No puede contener más de 5 caracteres.";
            }
        }

        $datos["codigo_postal"] = $codigo_postal;

        // ____ VALIDACION DE LA FECHA DE NACIMIENTO ____

        if (isset($_POST["fecha_nacimiento"]) && $_POST["fecha_nacimiento"] != "") {

            if (!validaFecha($_POST["fecha_nacimiento"], $datos["fecha_nacimiento"])) {
                $errores["fecha_nacimiento"][] = "Formato de fecha incorrecto";
            }

            $fechaOriginal = DateTime::createFromFormat("d/m/Y", $_POST["fecha_nacimiento"]);

            $fechaFormateada = $fechaOriginal->format("Y-m-d");

            $datos["fecha_nacimiento"] = $fechaFormateada;
        } else {
            $errores["fecha_nacimiento"][] = "Debe indicar una fecha de nacimiento";
        }


        // ____ VALIDACION DE LA FOTO ____

        $foto = $nick . ".png";
        $imagenOrigen = "";

        if (isset($_FILES["foto"])) {
            if (($_FILES["foto"]["error"] == 0) || ($_FILES["foto"]["type"] == "image/png")) {

                $imagenOrigen = $_FILES["foto"]["tmp_name"];
                $datos["foto"] = $nick . ".png";

                $archivo_temporal = $_FILES["foto"]["tmp_name"];

                // Ruta donde se guardará la foto
                $rutaFoto = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/fotos/" . $datos["foto"];
                move_uploaded_file($archivo_temporal, $rutaFoto);
            }
        }


        // SI NO HAY ERRORES
        if (!$errores) {
            $datos["nombre"] = trim($datos["nombre"]);
            $datos["nif"] = trim($datos["nif"]);
            $datos["direccion"] = trim($datos["direccion"]);
            $datos["poblacion"] = trim($datos["poblacion"]);
            $datos["provincia"] = trim($datos["provincia"]);
            $datos["codigo_postal"] = trim($datos["codigo_postal"]);
            $datos["fecha_nacimiento"] = trim($datos["fecha_nacimiento"]);
        }

        // ______ SENTENCIA PARA ACTUALIZAR _______

        // ACTUALIZAR USUARIO

        $sentenciaUpdate = "UPDATE usuarios 
                            SET
                            nombre = '$datos[nombre]',
                            nif = '$datos[nif]',
                            direccion = '$datos[direccion]',
                            poblacion = '$datos[poblacion]',
                            provincia = '$datos[provincia]',
                            codigo_postal = '$datos[codigo_postal]',
                            fecha_nacimiento = '$datos[fecha_nacimiento]',
                            borrado = false,
                            foto = '$datos[foto]'
                            WHERE cod_usuario = " . $codUsu;

        $consulta = $conex->query($sentenciaUpdate);

        if (!$consulta) {
            paginaError("Error al modificar el usuario.");
            exit;
        }

        $ruta = RUTABASE . "/imagenes/fotos/" . "$foto";
        if (move_uploaded_file($imagenOrigen, $ruta)) {
            $consultaFoto = "UPDATE usuarios SET foto='$foto'" .
                "   where nick='" . $datos["nick"] . "'";

            $consultaFoto = $conex->query($consultaFoto);
        }
        header("Location: /aplicacion/usuarios/verUsuario.php?cod_usuario=$datos[cod_usuario]");
        exit;
    }


    //___________________________________________
    //dibuja la plantilla de vista
    inicioCabecera("Modificar");
    cabecera();
    finCabecera();

    inicioCuerpo("InfuDiana",  $barraUbi);
    // ____________ ____ _ _

    cuerpo($datos, $errores);
    finCuerpo();

    // **********************************************************

    // vista
    function cabecera() {}

    function cuerpo(array $datos, array $errores)
    {

    ?>
        <br>
        <section class="bienvenida">
            <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Modificar Usuario<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
        </section>
        <form action="" method="post" class="formularioFiltrado" enctype="multipart/form-data">
            <?php
            if ($datos["foto"] != "")
                echo "<img src=\"../../imagenes/fotos/" . $datos["foto"] . "\" class=\"imgUsuario2\"><br>";
            ?>

            <br>
            <label for="">Nick: </label>
            <input type="text" name="nick" value="<?php echo $datos["nick"] ?>" disabled>

            <br>

            <label for="">Nombre: </label>
            <input type="text" name="nombre" value="<?php echo $datos["nombre"] ?>">
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

            <br>

            <label for="">Nif: </label>
            <input type="text" name="nif" value="<?php echo $datos["nif"] ?>">
            <?php
            //mostrar el error de NIF
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "nif") {
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

            <br>

            <label for="">Direccion: </label>
            <input type="text" name="direccion" value="<?php echo $datos["direccion"] ?>">
            <?php
            //mostrar el error de DIRECCION
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "direccion") {
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

            <br>

            <label for="">Poblacion: </label>
            <input type="text" name="poblacion" value="<?php echo $datos["poblacion"] ?>">
            <?php
            //mostrar el error de POBLACION
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "poblacion") {
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

            <br>

            <label for="">Provincia: </label>
            <input type="text" name="provincia" value="<?php echo $datos["provincia"] ?>">
            <?php
            //mostrar el error de PROVINCIA
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "provincia") {
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

            <br>

            <label for="">Código Postal: </label>
            <input type="text" name="codigo_postal" value="<?php echo $datos["codigo_postal"] ?>">
            <?php
            //mostrar el error de CODIGO POSTAL (codigo_postal)
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "codigo_postal") {
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

            <br>

            <label for="">Fecha Nacimiento: </label>
            <input type="text" name="fecha_nacimiento" value="<?php echo $datos["fecha_nacimiento"] ?>">
            <?php
            //mostrar el error de FECHA NACIMIENTO
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "fecha_nacimiento") {
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

            <br>

            <label for="">Foto: </label>
            <input type="file" name="foto" id="foto">
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
            <br><br>
            <input type="submit" value="Modificar" name="Modificar" class="boton">
            <a href="/aplicacion/usuarios/index.php" class="boton">Cancelar</a>

        </form>
        <br><br>
    <?php
    }
