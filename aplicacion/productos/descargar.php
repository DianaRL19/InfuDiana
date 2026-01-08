<?php
include_once(dirname(__FILE__) . "/../../cabecera.php");


//_____________________________________________________________

$nombreSalida = "ProductosFiltrados";

$filas = [];

$sentSelect = "nombre,fabricante,nombre_categoria,precio_venta";
$sentFrom = "cons_productos";
$sentWhere = "";

// ______ SACAMOS LOS DATOS DE FILTRADO DE LA SESION
// ___ _ Nombre de producto

$nombre = $_SESSION['filtro_nombre'];

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

// ___ _ Nombre de categoria

$categoria = $_SESSION['filtro_categoria'];

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

// ____ Ejecutamos la sentencia
$query = "select $sentSelect from $sentFrom $sentWhere";

$consulta = $conex->query($query);

while ($fila = $consulta->fetch_assoc()) {
    $filas[] = $fila;
}

// ____ Preparamos el fichero txt que se va a descargar

header("Content-Type: text/plain");
header('Content-Disposition:attachement;filename="' . $nombreSalida . ".txt" . '"');

// ____ Construimos la cadena de cada producto
foreach ($filas as $value) {
    $string = "PRODUCTO: ";
    foreach ($value as $clave => $valor) {
        $string .= $clave . " → " . $valor . ", ";
    }
    $string = substr($string, 0, -1); // → para quitar la ultima " , " que queda suelta
    echo $string . "\n";
}
