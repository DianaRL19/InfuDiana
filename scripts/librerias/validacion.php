<!-- Diana Romero León -->
<?php

/**
 * Esta función comprueba que $var contiene un entero cuyo valor está entre $min y $max.
 *
 * @param integer $var -> Devuelve el entero saneado (en caso de no cumplir las condiciones devuelve $defecto).
 * @param integer $min -> Valor minimo
 * @param integer $max -> Valor maximo
 * @param integer $defecto -> valor por defecto
 * @return boolean -> True si es correcto y false en caso contrario
 */
function validaEntero(int &$var, int $min, int $max, int $defecto): bool
{
    $options = ['options' => ['min_range' => $min, 'max_range' => $max]];
    if (filter_var($var, FILTER_VALIDATE_INT, $options))
        return true;
    else {
        $var = $defecto;
        return false;
    }
}

/**
 * Esta función comprueba que $var contiene un real cuyo valor está entre $min y $max. 
 *
 * @param float $var -> Devuelve el entero saneado (en caso de no cumplir las condiciones devuelve $defecto).
 * @param float $min -> Valor minimo
 * @param float $max -> Valor maximo
 * @param float $defecto -> valor por defecto
 * @return boolean -> True si es correcto y false en caso contrario
 */
function validaReal(float &$var, float $min, float $max, float $defecto): bool
{
    $options = ['options' => ['min_range' => $min, 'max_range' => $max]];
    if (filter_var($var, FILTER_VALIDATE_FLOAT, $options))
        return true;
    else {
        $var = $defecto;
        return false;
    }
}

/**
 * Esta función comprueba que $var contiene una fecha correcta en el formato dd/mm/aaaa. 
 * 
 * @param string $var -> Devuelve la fecha saneada (Ej 01/02/2025), si no cumple las condiciones devuelve $defecto
 * @param string $defecto -> Valor por defecto
 * @return boolean -> True si es correcto y false en caso contrario
 */
function validaFecha(string &$var, string $defecto): bool
{
    $expReg = '/^([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})$/';

    if (filter_var($var, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $expReg)))) {

        $fecha = explode("/", $var);

        if (checkdate($fecha[1], $fecha[0], $fecha[2])) {
            $var = date("d/m/Y", mktime(0,0,0,$fecha[1], $fecha[0], $fecha[2]));
            return true;
        } else {
            $var = $defecto;
            return false;
        }
    } else {
        $var = $defecto;
        return false;
    }
}

/**
 * Esta función comprueba que $var contiene una hora correcta en el formato hh:mm:ss . 
 * 
 * @param string $var -> Devuelve la hora saneada (Ej 00:05:01), si no cumple las condiciones devuelve $defecto
 * @param string $defecto -> valor por defecto
 * @return boolean -> True si es correcto y false en caso contrario
 */
function validaHora(string &$var, string $defecto): bool
{
    $expReg = '/^(\d{1,2}):(\d{1,2}):(\d{1,2})$/';

    if (filter_var($var, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $expReg)))) {
        $horaDada = explode(":", $var);

        $hora = intval($horaDada[0]);
        $min = intval($horaDada[1]);
        $seg = intval($horaDada[2]);

        if (($hora >= 0 && $hora < 24) && ($min >= 0 && $min < 60) && ($seg >= 0 && $seg < 60)) {
            $var = date("H:i:s", mktime($hora, $min, $seg));
            return true;
        } else {
            $var = $defecto;
            return false;
        }
    } else {
        $var = $defecto;
        return false;
    }
}

/**
 * Esta función comprueba que $var contiene un email correcto en el formato aaaaa@bbbb.ccc. 
 *
 * @param string $var -> Devuelve el email saneado, si no cumple las condiciones devuelve $defecto
 * @param string $defecto -> valor por defecto
 * @return boolean -> True si es correcto y false en caso contrario
 */

function validaEmail(string &$var, string $defecto): bool
{
    if (filter_var($var, FILTER_VALIDATE_EMAIL))
        return true;
    else {
        $var = filter_var($var, FILTER_SANITIZE_EMAIL);
        return false;
    }
}

/**
 * Esta función comprueba que $var contiene una cadena de longitud máxima $longitud. 
 * 
 * @param string $var -> Si no cumple las condiciones devuelve $defecto
 * @param integer $longitud -> longitud maxima que puede tener la cadena
 * @param string $defecto -> valor por defecto
 * @return boolean -> True si es correcto y false en caso contrario
 */
function validaCadena(string &$var, int $longitud, string $defecto): bool
{
    $options = ['options' => ['min_range' => 1, 'max_range' => $longitud]];
    if (filter_var(strlen($var), FILTER_VALIDATE_INT, $options)) {
        return true;
    } else {
        $var = $defecto;
        return false;
    }
}

/**
 * Esta función comprueba que $var cumple con la expresión regular $expresion. 
 *
 * @param string $var -> Si no cumple las condiciones devuelve $defecto
 * @param string $expresion -> formato que debe cumplir
 * @param string $defecto -> valor por defecto
 * @return boolean -> True si es correcto y false en caso contrario
 */
function validaExpresion(string &$var, string $expresion, string $defecto): bool
{

    if (filter_var($var, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => $expresion)))) {
        return true;
    } else {
        $var = $defecto;
        return false;
    }
}

/**
 * Esta función comprueba que $var sea igual a uno de los elementos del array $posibles ($tipo=1) 
 * o a una de las claves del array $posibles ($tipo=2). 
 *
 * @param mixed $var -> elemento a buscar
 * @param array $posibles -> array donde se busca la coincidencia
 * @param integer $tipo -> operacion a realizar
 * @return boolean -> True si es correcto y false en caso contrario
 */
function validaRango(mixed $var, array $posibles, int $tipo = 1): bool {
    switch($tipo) {
        case 1: return in_array($var, $posibles, true); break;
        case 2: return array_key_exists($var, $posibles); break;
    }
    return false;
}
