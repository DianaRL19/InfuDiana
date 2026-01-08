<?php
include_once(dirname(__FILE__) . "/../../cabecera.php");


$acceso->quitarRegistroUsuario();

header("location: /index.php");