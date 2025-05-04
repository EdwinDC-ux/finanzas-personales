<?php
/**
 * Conector de clases
 * 
 * Administra las clases, funciones y data de las peticiones
 * 
 * @package     src.controller
 * @author      Edwin Díaz Caballero
 * @final
 * @since       03/Mayo/2025
 * @version     1.0
*/
namespace   src\controller;
use \InputFilter as InputFilter;
use Exception;

require_once "../../vendor/autoload.php";
define('CLASS_DIRECTORY',[
    'Sistema' => __DIR__ . '/Sistema/Sistema.php',
]);

/**
 * @property spl_autoload_register: funcion anónima que incluye las clases al momento de las peticiones
*/
spl_autoload_register( function ($ClassName) {
    if(!array_key_exists($ClassName, CLASS_DIRECTORY)) {
        $file = str_replace('\\', '/', $ClassName) . '.php';
        $ClassPath = __DIR__ . '/' . $file;
    } else 
        $ClassPath = CLASS_DIRECTORY[$ClassName];

    if (file_exists($ClassPath))
        include $ClassPath;
    else
        throw new Exception("Class $ClassName not found");
});

// Filtrado de las peticiones
$InputFilter = new InputFilter();
$_SERVER['PHP_SELF'] = htmlspecialchars($InputFilter->process($_SERVER['PHP_SELF']));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST = $InputFilter->process($_POST);
    $Arguments = $_POST;
} else {
    $_GET = $InputFilter->process($_GET);
    $Arguments = $_GET;
}

// Buscamos que existan las llaves obligatorias de las peticiones (Class y Action)
if (!array_key_exists('Class', $Arguments))
    return false;
if (!array_key_exists('Action', $Arguments))
    return false;

// Si el elemeto Class está definido agregamos la clase y el metodo y sanitizamos el array
if (isset($_POST['Class'])) {
    $ClassName = $_POST['Class'];
    $ClassMethod = $_POST['Action'];
    sanitizeArray($_POST);

    $Arguments = $_POST;
} else {
    $ClassName = $_REQUEST['Class'];
    $ClassMethod = $_REQUEST['Action'];
    sanitizeArray($_GET);
    
    $Arguments = $_GET;
}

/**
 * Método que nos ayuda a sanitizar los elementos recibidos como parámetros.
 * @param   Array $array - Arreglo de los datos recibidos en la petición.
*/
function sanitizeArray(&$array) {
    global $InputFilter;

    foreach ($array as $key => &$value) {
        $tempValue = $InputFilter->process($value);
        
        $value = $tempValue;
        $GLOBALS[$key] = $tempValue;
    }
}

/**
 * Método que realiza el llamado a la clase solicitada
 * 
 * @param   String $nameClass - Nombre de la clase a ser llamada
 * @param   String $classMethod - Nombre del método de la clase a ser ejecutado.
 * @param   Array $argumentos - Argumentos que son necesarios para la ejecución del método
*/
function startClassManager($nameClass, $classMethod, $argumentos){
	unset($argumentos['Class']);
	unset($argumentos['Action']);

	if (!class_exists($nameClass)) {
		throw new Exception("Class $nameClass not found");
	}

	$ClassInstance = new $nameClass($argumentos);

	$classMethod = $classMethod;

	if (!method_exists($ClassInstance, $classMethod)) {
		throw new Exception("Method $classMethod not found");
	}
	$ClassInstance->$classMethod();
}

startClassManager($ClassName, $ClassMethod, $Arguments);
