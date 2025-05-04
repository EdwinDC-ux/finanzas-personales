<?php
/**
 * Clase de sistema
 * 
 * @package     src.api.Sistema
 * @author      Edwin Díaz Caballero
 * @final
 * @since       03/Mayo/2025
 * @version     1.0
*/

use src\controller\Sistema\Sistema as SistemaC;

session_start();

class Sistema extends Core {
    protected $usuario;
    protected $contrasenia;
    protected $idSubsistema;
    protected $proceso;

    /**
     * Constructor de la clase Sistema
     * 
     * @access  constructor
     * @param   Array $classProperties - Lista de propiedades de la clase
    */
    public function __construct($classProperties = Null) {
		parent::__construct($classProperties);
	}

    /**
     * ========== INICIO DE SESIÓN ==========
	 * Método que realiza el proceso de inicio de sesión
	 * 
     * @access  public
	 * ======================================
    */
    

	/**
	 * ========== COMPROBACIÓN DE SESIÓN ==========
	 * Método que realiza la comprobación de la sesión en el servidor.
	 * 
	 * @access	public
	 * ============================================
	*/
	public function comprobarSesion () {
		if(!isset($_SESSION['finanzas-personales']['estadoSesion']))
			$_SESSION['finanzas-personales']['estadoSesion'] = false;

		$_SESSION['finanzas-personales']['estadoSesion'] ? $resultado = ['ESTADO' => 'Active'] : $resultado = ['ESTADO' => 'Inactive'];

		echo json_encode($resultado);
	}
}