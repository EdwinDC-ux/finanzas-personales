<?php
/**
 * Nucleo de funcionamiento del sistema
 * 
 * @package     src.api
 * @author      Edwin Díaz Caballero
 * @final
 * @since       03/Mayo/2025
 * @version     1.0
*/
use src\controller\UtilsController as Utils;

class Core {
    public $id;
    public $obj;

    /**
     * Constructor de la clase InputFilter. (Sólo el primer parámetro es obligatorio).
     * 
     * @access  constructor
     * @param   Mixed $classProperties
    */
    public function __construct($classProperties = null)
    {
        // Assign Class Properties
        $this->assignPropertiesValues($classProperties);
    }

    /**
     * Método para obtener una propiedad por su nombre
     * @access  public
     * @param   String $propertyName - nombre de la propiedad buscada
     * @return  Mixed propiedad encontrada
    */
    public function getProperty($propertyName)
    {
        if (property_exists(get_class($this), $propertyName)) {
            return $this->{$propertyName};
        } else {
            return null;
        }
    }

    /**
     * Método para establecer una propiedad
     * @access  public
     * @param   String $propertyName - nombre de la propiedad a insertar
     * @param   Mixed $propertyValue - valor que se asignará a la propiedad
    */
    public function setProperty($propertyName, $propertyValue)
    {
        if (property_exists(get_class($this), $propertyName)) {
            $this->{$propertyName} = trim(htmlentities($propertyValue, ENT_QUOTES, 'UTF-8'));
        }
    }

    /**
     * Método para establecer un conjunto de propiedades
     * @access  public
     * @param   Array $propertyName - conjunto de nombres y valores de propiedades
    */
    public function assignPropertiesValues($Properties_Array)
    {
        if (is_array($Properties_Array)) {
            foreach ($Properties_Array as $Property_Name => $Property_Value) {
                if (property_exists(get_class($this), $Property_Name)) {
                    if (is_array($Property_Value)) {
                        $this->{$Property_Name} = $Property_Value;
                    } elseif (is_object($Property_Value)) {
                        $this->{$Property_Name} = $Property_Value;
                    } else {
                        $this->{$Property_Name} = trim($Property_Value);
                    }
                }
            }

        }
    }

    /**
     * Método de obtención de catalogos
     * @access  public
    */
    public function Get_Catalogo() {
        $cat = Utils::Get_CatologoC($this->id);
        echo json_encode( $cat );
    }

    /**
     * Método de obtención de catalogos filtrados
     * @access  public
    */
    public function Get_Catalogos() {
        $obj = json_decode($this->obj, true);

        $cat = Utils::Get_CatologosC( $obj );
        echo json_encode( $cat );
    }
}