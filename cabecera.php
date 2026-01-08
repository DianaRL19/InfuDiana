<?php
define("RUTABASE", dirname(__FILE__));
//define("MODO_TRABAJO","produccion"); //en "produccion o en desarrollo
define("MODO_TRABAJO", "desarrollo"); //en "produccion o en desarrollo

if (MODO_TRABAJO == "produccion")
    error_reporting(0);
else
    error_reporting(E_ALL);


spl_autoload_register(function ($clase) {
    $ruta = RUTABASE . "/scripts/clases/";
    $fichero = $ruta . "$clase.php";

    if (file_exists($fichero)) {
        require_once($fichero);
    } else {
        throw new Exception("La clase $clase no se ha encontrado.");
    }
});

include(RUTABASE . "/aplicacion/plantilla/plantilla.php");
include(RUTABASE . "/aplicacion/config/acceso_bd.php");
include(RUTABASE . "/scripts/librerias/validacion.php");

include(RUTABASE . "/scripts/clases/ACLBase.php");
include(RUTABASE . "/scripts/clases/ACLBD.php");
include(RUTABASE . "/scripts/clases/Acceso.php");

// Creo todos los objetos que necesita mi aplicación

// Iniciar la sesion
session_start();

mysqli_report(MYSQLI_REPORT_ERROR);

$conex = new mysqli($servidor, $usuario, $contrasenia, $baseDeDatos);

$acceso = new Acceso();
$acl = new ACLBD($servidor, $usuario, $contrasenia, $baseDeDatos);