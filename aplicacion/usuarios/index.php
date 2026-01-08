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
            "LINK" => "/index.php"
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

    // _______ __ _
    // Definir la sentencia por partes 

    $sentSelect = "*";
    $sentFrom = "usuarios";
    $sentWhere = "";
    $sentOrder = "cod_usuario asc";

    // _______ __ _
    //Recogemos los criterios y validamos los datos de filtrado

    $datosFiltrado = [
        "cod_usuario" => "",
        "provincia" => "",
        "borrado" => ""
    ];

    $errores = [];

    // VALIDAMOS LOS DATOS CON LOS QUE VAMOS A FILTRAR
    if (isset($_POST["filtrar"])) {

        //__ cod_usuario ___
        $cod_usuario = "";

        if (isset($_POST["cod_usuario"]) && $_POST["cod_usuario"] != "") {
            $cod_usuario = intval($_POST["cod_usuario"]);

            if ($cod_usuario == "") {
                $errores["cod_usuario"][] = "No se ha indicado un código.";
            }

            if ($cod_usuario < 1) {
                $errores["cod_usuario"][] = "El código introducio no es válido.";
            }

            if (!$acl->existeCodUsuario($cod_usuario)) {
                $errores["cod_usuario"][] = "El cod_usuario indicado no existe.";
            }

            // CONTROLAMOS LOS ATAQUES DE INYECCION
            $cod_usuario = $conex->escape_string($cod_usuario);
            $datosFiltrado["cod_usuario"] = $cod_usuario;


            // CONTROLAMOS LOS ATAQUES DE INYECCION
            $cod_usuario = $conex->escape_string($cod_usuario);

            if ($sentWhere != "") {
                $sentWhere .= " and ";
            }

            $sentWhere .= " cod_usuario = '{$cod_usuario}' ";
        } else
            $errores["cod_usuario"][] = "No se ha indicado un código.";


        //__ POBLACION ___
        $provincia = "";

        if (isset($_POST["provincia"]) && $_POST["provincia"] != "") {

            $provincia = $_POST["provincia"];
            $datosFiltrado["provincia"] = $provincia;

            // CONTROLAMOS LOS ATAQUES DE INYECCION
            $provincia = $conex->escape_string($provincia);

            if ($sentWhere != "") {
                $sentWhere .= " and ";
            }

            $sentWhere .= " provincia = '{$provincia}'";
        }

        //__ BORRADO ___
        $borrado = 0;

        if (isset($_POST["borrado"]) && in_array($_POST["borrado"], range(0, 1))) {

            $borrado = intval($_POST["borrado"]);

            $datosFiltrado["borrado"] = "$borrado";

            if ($sentWhere != '')
                $sentWhere .= ' and ';

            $sentWhere .= " borrado = {$borrado}";
        }
    }

    // ________ _____ __ _
    // Construimos la sentencia
    $sentencia = "select $sentSelect" . " from $sentFrom" .
        (($sentWhere != "") ? " where " . $sentWhere : "") .
        (($sentOrder != "") ? " order by " . $sentOrder : "");

    //____________ _______ _
    // Comprobamos si se ha establecido o no la conexión.
    if ($conex->connect_errno) {
        paginaError("Fallo al conectar a la Base de Datos"); // → Llamamos a la pagina de error para que nos muestre el mensaje de error
        exit;
    }

    // Si no hay errores establezco la consulta

    // Ejecutamos la sentancia
    $consulta = $conex->query($sentencia);

    if (!$consulta) {
        paginaError("Fallo en el acceso a la Base de Datos");
        exit;
    }

    $filas = [];
    // fetch_assoc → procesa los datos de la fila que te devulve
    while ($fila = $consulta->fetch_assoc()) { // → se recorre fila a fila, devolviéndola como array asociativo

        // AÑADIMOS LAS IMAGENES DE OPERACIONES DEPENDIENDO DE LOS PERMISOS DEL USUARIO
        $fila["operaciones"] = "";
        $fila["operaciones"] .= "<a href='verUsuario.php?cod_usuario={$fila["cod_usuario"]}'>" . "<img src='/img/24x24/ver.png'></a>";

        //if ($acceso->puedePermiso(2) && $acceso->puedePermiso(3)) {

        $fila["operaciones"] .=
            "<a href='modificarUsuario.php?cod_usuario={$fila["cod_usuario"]}'> " . "<img src='/img/24x24/modificar.png'>" . "</a>" .
            "<a href='borrarUsuario.php?cod_usuario={$fila["cod_usuario"]}'> " . "<img src='/img/24x24/borrar.png'>" . "</a>";
        //}
        $filas[] = $fila;
    }

    //___________________________________________
    //dibuja la plantilla de vista
    inicioCabecera("Lista de Usuarios");
    cabecera();
    finCabecera();

    inicioCuerpo("InfuDiana",  $barraUbi);
    // ____________ ____ _ _

    cuerpo($filas, $datosFiltrado, $errores);
    finCuerpo();

    // **********************************************************

    // vista
    function cabecera() {}

    function cuerpo(array $filas, array $datosFiltrado, array $errores)
    {
    ?>
        <br>
        <section class="bienvenida">
            <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Listado de Usuarios<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
        </section>
        <!-- Filtrados -->
        <fieldset class="formularioFiltrado">
            <legend>Filtrado de Usuarios</legend>
            <form method="post">
                <?php
                //mostrar el error de Codigo de usuario
                if ($errores) {
                    foreach ($errores as $clave => $valor) {
                        if ($clave == "cod_usuario") {
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
                <label for="">Código: </label>
                <input type="text" name="cod_usuario" value="<?php echo $datosFiltrado["cod_usuario"] ?>">
                <br>
                <?php
                //mostrar el error de Provincia
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
                <label for="">Provincia: </label>
                <input type="text" name="provincia" value="<?php echo $datosFiltrado["provincia"] ?>">
                <br>
                <?php
                //mostrar el error de Borrado
                if ($errores) {
                    foreach ($errores as $clave => $valor) {
                        if ($clave == "borrado") {
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
                <label for="">Borrado: </label>
                <select name="borrado" style="width: 50%">
                    <option value="">Todos</option>
                    <option value="0" <?php echo $datosFiltrado["borrado"] == '0' ? "selected" : ""; ?>>No</option>
                    <option value="1" <?php echo $datosFiltrado["borrado"] == '1' ? "selected" : ""; ?>>Si</option>
                </select>
                <br><br>
                <input type="submit" value="Filtrar" name="filtrar" class="boton">
            </form>
        </fieldset>
        <br><br>

        <!-- MOSTRAMOS LA TABLA CON LOS DATOS-->
        <table class="tabla">
            <thead>
                <tr>
                    <th>Nick</th>
                    <th>Nombre</th>
                    <th>Nif</th>
                    <th>Dirección</th>
                    <th>Población</th>
                    <th>Provincia</th>
                    <th>Código postal</th>
                    <th>Fecha Naciemiento</th>
                    <th>Borrado</th>
                    <th>Operaciones</th>
                </tr>
            </thead>
            <?php
            foreach ($filas as $fila) {
                echo "<tr>";
                foreach ($fila as $nomCampo => $dato) {

                    // No mostramos el cod_usuario y quito algunos campos porque no caben en la interfaz
                    if (($nomCampo != "cod_usuario") && ($nomCampo != "foto") && ($nomCampo != "contrasenia")) {

                        if ($nomCampo == "borrado") {
                            echo ($dato == 0 ? "<td>No</td>" : "<td>Si</td>");
                        } else {
                            echo "<td>" . $dato . "</td>";
                        }
                    }
                }
            }
            echo "</tr>";

            ?>
        </table>
        <br>
        <a href="./nuevoUsuario.php" class="bAnadir boton">Añadir <img src="/img/16x16/nuevo.png"></a>
        <br><br><br><br>
    <?php
    }
