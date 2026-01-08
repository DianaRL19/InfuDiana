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
            "TEXTO" => "Ver",
            "LINK" => "./verUsuario.php"
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

    $errores = [];
    $filas = [];

    $codUsu = (intval($_GET["cod_usuario"]));

    // SENTENCIA USurarios
    $sentSelect = "*";
    $sentFrom = "usuarios";
    $sentWhere = "where cod_usuario = '$codUsu'";

    $query = "select $sentSelect from $sentFrom $sentWhere";

    $consulta = $conex->query($query);

    //_____________________

    while ($fila = $consulta->fetch_assoc()) {
        $partes = explode("-", $fila["fecha_nacimiento"]);
        $fila["fecha_nacimiento"] = $partes[2] . "/" . $partes[1] . "/" . $partes[0];

        $fila["operaciones"] = "";

        $fila["operaciones"] .=
            "<a href='modificarUsuario.php?cod_usuario={$fila["cod_usuario"]}'> " . "<img src='/img/24x24/modificar.png'>" . "</a>" .
            "<a href='borrarUsuario.php?cod_usuario={$fila["cod_usuario"]}'> " . "<img src='/img/24x24/borrar.png'>" . "</a>";
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
            <h2><img src="../../img/hoja1.png" class="hojaDecorativa">Datos Usuario<img src="../../img/hoja2.png" class="hojaDecorativa"></h2>
        </section>
        <table class="tabla">
            <tr>
                <th>Código usuario</th>
                <th>Nick</th>
                <th>Nombre</th>
                <th>Nif</th>
                <th>Dirección</th>
                <th>Población</th>
                <th>Provincia</th>
                <th>Código postal</th>
                <th>Nacimiento</th>
                <th>Borrado</th>
                <th>Foto</th>
                <th>Operaciones</th>
            </tr>
            <?php
            for ($i = 0; $i < count($datos); $i++) {
                echo "<tr>";
                foreach ($datos[$i] as $clave => $valor) {
                    if ($clave == "foto") {
                        $imagen = "../../imagenes/fotos/" . $valor;
                        echo "<td><img src='$imagen' class=\"imgUsuario\"></td>";
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
