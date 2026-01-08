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
            "TEXTO" => "Añadir",
            "LINK" => "./nuevoUsuario.php"
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

    //__________________________________________________

    $datos = [
        "nick" => "",
        "nombre" => "",
        "contrasenia" => "",
        "nif" => "",
        "direccion" => "",
        "poblacion" => "",
        "provincia" => "",
        "cp" => "",
        "fechaNacimiento" => "",
        "foto" => ""
    ];

    $errores = [];

    $roles = $acl->dameRoles();


    // ____ VALIDAMOS LOS DATOS QUE NOS LLEGAN DEL FORMULARIO ___
    if (isset($_POST["añadir"])) {

        // ____ VALIDACION CAMPO NICK ____

        $nick = "";

        if (isset($_POST["nick"])) {
            $nick = $_POST["nick"];

            if ($nick == "") {
                $errores["nick"][] = "Debe introducir un nick.";
            }

            if (!validaCadena($nick, 50, "")) {
                $errores["nick"][] = "No puede contener más de 50 caracteres.";
            }

            // Commprobamos si ya existe o no el nick indicado ya que deben ser unios
            $nick = mb_strtolower($nick);

            if ($acl->existeUsuario($nick)) {
                $errores["nick"][] = "El nick indicado ya esta en uso, pruebe con otro.";
            }
        }

        $datos["nick"] = $nick;

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

        // ____ VALIDACION CP (CODIGO POSTAL) ____
        $cp = "";
        if (isset($_POST["cp"])) {

            $cp = $_POST["cp"];

            if ($direccion == "") {
                $errores["cp"][] = "Debe indicar un código postal.";
            }

            if (!validaCadena($cp, 5, $datos["cp"])) {
                $errores["cp"][] = "No puede contener más de 5 caracteres.";
            }
        }

        $datos["cp"] = $cp;

        // ____ VALIDACION DE LA FECHA DE NACIMIENTO ____
        $fNac = "";

        if (isset($_POST["fNac"])) {

            $fNac = $_POST["fNac"];

            if ($fNac == "") {
                $errores["fechaNacimiento"][] = "Debe indicar una fecha de nacimiento";
            }

            $fecha = DateTime::createFromFormat("d/m/Y", $fNac);

            if (!validaFecha($fNac, $datos["fechaNacimiento"])) {
                $errores["fechaNacimiento"][] = "Formato de fecha incorrecto";
            }
        }
        $fechaFormateada = $fecha->format("Y-m-d");
        $datos["fechaNacimiento"] = $fechaFormateada;

        // ____ VALIDACION DE LA FOTO ____

        $foto = $nick . ".png";
        $imagenOrigen = "";

        if (isset($_FILES["foto"])) {
            if (($_FILES["foto"]["error"] != 0) || ($_FILES["foto"]["type"] != "image/png")) {
                $datos["foto"] = "UsuarioDefault.png";
            } else {
                $imagenOrigen = $_FILES["foto"]["tmp_name"];
                $datos["foto"] = $_POST["nick"] . ".png";
            }
        } else {
            $datos["foto"] = "UsuarioDefault.png";
        }

        $archivo_temporal = $_FILES["foto"]["tmp_name"];

        // Ruta donde se guardará la foto
        $rutaFoto = $_SERVER["DOCUMENT_ROOT"] . "/imagenes/fotos/" . $datos["foto"];
        move_uploaded_file($archivo_temporal, $rutaFoto);

        // SI NO HAY ERRORES
        if (!$errores) {
            $datos["nick"] = trim($datos["nick"]);
            $datos["nombre"] = trim($datos["nombre"]);
            $datos["nif"] = trim($datos["nif"]);
            $datos["direccion"] = trim($datos["direccion"]);
            $datos["poblacion"] = trim($datos["poblacion"]);
            $datos["provincia"] = trim($datos["provincia"]);
            $datos["cp"] = trim($datos["cp"]);
            $datos["fechaNacimiento"] = trim($datos["fechaNacimiento"]);

            $acl->anadirUsuario($datos["nombre"], $datos["nick"], $datos["contrasenia"], $acl->getCodRole($_POST["rol"]));

            // _______________ OPERACIONES EN LA BASE DE DATOS ____________________________

            //___ ESTABLECEMOS una conexión a la Base de Datos ___

            // INSERTAR USUARIO

            // TABLA USUARIOS
            $sentencia = "INSERT INTO usuarios (nick,nombre,nif,direccion,poblacion,provincia,codigo_postal,fecha_nacimiento,borrado,foto)" .
                " VALUES ('" .
                $datos['nick'] . "','" .
                $datos['nombre'] . "','" .
                $datos['nif'] . "','" .
                $datos['direccion'] . "','" .
                $datos['poblacion'] . "','" .
                $datos['provincia'] . "','" .
                $datos['cp'] . "','" .
                $datos['fechaNacimiento'] .
                "',false,'" .
                $datos['foto'] .
                "')";

            $consulta = $conex->query($sentencia);

            if (!$consulta) {
                paginaError("Error al añadir el usuario.");
                exit;
            }

            $ruta = RUTABASE . "/imagenes/fotos/" . "$foto";
            if (move_uploaded_file($imagenOrigen, $ruta)) {
                $consultaFoto = "UPDATE usuarios SET foto='$foto'" .
                    "   where nick='$nick'";

                $consultaFoto = $conex->query($consultaFoto);
            }
            header("Location: /aplicacion/usuarios/index.php");
            exit;
        }
    }
    //___________________________________________
    //dibuja la plantilla de vista
    inicioCabecera("Añadir");
    cabecera();
    finCabecera();

    inicioCuerpo("InfuDiana",  $barraUbi);
    // ____________ ____ _ _

    cuerpo($datos, $errores, $roles);
    finCuerpo();

    // **********************************************************

    // vista
    function cabecera() {}

    function cuerpo(array $datos, array $errores, array $roles)
    {

    ?>
        <br>
        <section class="bienvenida">
            <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Añadir Usuario<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
        </section>
        <form action="" enctype="multipart/form-data" method="post" class="formularioFiltrado">
            <label for="">Nick: </label>
            <input type="text" name="nick" value="<?php echo $datos["nick"] ?>">
            <?php
            //mostrar el error de NICK
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "nick") {
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

            <label for="">Contraseña: </label>
            <input type="password" name="contrasenia" value="<?php echo $datos["contrasenia"] ?>">
            <?php
            //mostrar el error de CONTRASEÑA
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "contrasenia") {
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
            <label for="">Rol: </label>
            <select name="rol">
                <?php
                foreach ($roles as $indice => $rol) {
                    echo "<option value='$rol'>$rol</option>";
                }

                ?>
            </select>
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
            <input type="text" name="cp" value="<?php echo $datos["cp"] ?>">
            <?php
            //mostrar el error de CODIGO POSTAL (CP)
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "cp") {
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
            <input type="text" name="fNac" value="<?php DateTime::createFromFormat("d/m/Y", $datos["fechaNacimiento"]); ?>">
            <?php
            //mostrar el error de FECHA NACIMIENTO
            if ($errores) {
                foreach ($errores as $clave => $valor) {
                    if ($clave == "fechaNacimiento") {
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
            <input type="file" name="foto">
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

            <input type="submit" value="Añadir" name="añadir" class="boton">
            <a href="/aplicacion/usuarios/index.php" class="boton">Cancelar</a>
        </form>
    <?php
    }
