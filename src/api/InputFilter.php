<?php
/**
 * Filtro de contenido en la URL
 * 
 * @package     src.api
 * @author      Edwin Díaz Caballero
 * @final
 * @since       03/Mayo/2025
 * @version     1.0
*/

class InputFilter {
    protected $tagsArray;   // default = empty array
    protected $attrArray;   // default = empty array

    protected $tagsMethod;  // default = 0
    protected $attrMethod;  // default = 0

    protected $xssAuto;     // default = 1
    protected $tagBlackList = array('applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml');
    protected $attrBlackList = array('action', 'background', 'codebase', 'dynsrc', 'lowsrc');  // Tambien eliminará todos los controladores de eventos

    /**
     * Constructor de la clase InputFilter. (Sólo el primer parámetro es obligatorio).
     * 
     * @access  constructor
     * @param   Array $tagsArray - Lista de etiquetas definidas por el usuario.
     * @param   Array $attrArray - Lista de atributos definidos por el usuario.
     * @param   int $tagsMethodd - 0 = Permitir solo efinidos por el usuario, 1 = Permitir todos excepto los definidos por el usuario.
     * @param   int $attrMethod - 0 = Permitir solo efinidos por el usuario, 1 = Permitir todos excepto los definidos por el usuario.
     * @param   int $xssAuto - 0 = Solo elementos esenciales para la limpieza automática, 1 = Permitir tags/attr limpios en la lista negra
    */
    public function __construct($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1) {
        // Aseguramos que las listas definidas por los usuarios están en 'lowercase'.
        for ($i = 0; $i < count($tagsArray); $i++) $tagsArray[$i] = strtolower($tagsArray[$i]);
		for ($i = 0; $i < count($attrArray); $i++) $attrArray[$i] = strtolower($attrArray[$i]);

        // Asignamos a las variables miembro
        $this->tagsArray    = (array) $tagsArray;
		$this->attrArray    = (array) $attrArray;
		$this->tagsMethod   = $tagsMethod;
		$this->attrMethod   = $attrMethod;
		$this->xssAuto      = $xssAuto;
    }

    /**
     * Método que será llamado por otro script php. Procesos para XSS y código incorrecto especificado
     * @access  public
     * @param   Mixed $source - string de entrada/array de strings a ser 'limpiados'
     * @return  String $source - versión 'limpia' de los parametros recibidos
    */
    public function process($source) {
        // Limpiamos todos los elemtos del array
        if (is_array($source)) {
            foreach($source as $key => $value)
                // Filtramos el elemento para XSS u otro codigo 'malicioso'.
                if (is_string($value)) $source[$key] = $this->remove($this->decode($value));
            return $source;
        // Limpiamos el string
        } else if (is_string($source))
            // Filtramos el elemento para XSS u otro codigo 'malicioso'.
            return $this->remove($this->decode($source));
        else return $source;
    }

    /** 
	  * Método interno que trata de convertir a texto plano
	  * @access protected
	  * @param String $source
	  * @return String $source
	  */
	protected function decode($source) {
		// url decode
		$source = html_entity_decode($source, ENT_QUOTES, "UTF-8");
		/*
		// convert decimal
		$source = @preg_replace('/&#(\d+);/me',"chr(\\1)", $source); // decimal notation

		// convert hex
		$source = @preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)", $source); // hex notation
		*/
		return $source;
	}

	/** 
     * Método interno que remueve iterativamente todas las etiquetas y atributos no deseados.
	  * @access protected
	  * @param String $source - String de entrada a ser 'limpiado'
	  * @return String $source - Versión 'limpia' del parámetro de entrada
	*/
	protected function remove($source) {
		$loopCounter=0;
		// Proporciona protección de etiquetas anidadas
		while($source != $this->filterTags($source)) {
			$source = $this->filterTags($source);
			$loopCounter++;
		}
		return $source;
	}	
	
	/** 
	  * Internal method to strip a string of certain tags
	  * @access protected
	  * @param String $source - input string to be 'cleaned'
	  * @return String $source - 'cleaned' version of input parameter
	  */
	protected function filterTags($source) {
		// filter pass setup
		$preTag = NULL;
		$postTag = $source;
		// find initial tag's position
		$tagOpen_start = strpos($source, '<');
		// interate through string until no tags left
		while($tagOpen_start !== FALSE) {
			// process tag interatively
			$preTag .= substr($postTag, 0, $tagOpen_start);
			$postTag = substr($postTag, $tagOpen_start);
			$fromTagOpen = substr($postTag, 1);
			// end of tag
			$tagOpen_end = strpos($fromTagOpen, '>');
			if ($tagOpen_end === false) break;
			// next start of tag (for nested tag assessment)
			$tagOpen_nested = strpos($fromTagOpen, '<');
			if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end)) {
				$preTag .= substr($postTag, 0, ($tagOpen_nested+1));
				$postTag = substr($postTag, ($tagOpen_nested+1));
				$tagOpen_start = strpos($postTag, '<');
				continue;
			} 
			$tagOpen_nested = (strpos($fromTagOpen, '<') + $tagOpen_start + 1);
			$currentTag = substr($fromTagOpen, 0, $tagOpen_end);
			$tagLength = strlen($currentTag);
			if (!$tagOpen_end) {
				$preTag .= $postTag;
				$tagOpen_start = strpos($postTag, '<');			
			}
			// iterate through tag finding attribute pairs - setup
			$tagLeft = $currentTag;
			$attrSet = array();
			$currentSpace = strpos($tagLeft, ' ');
			// is end tag
			if (substr($currentTag, 0, 1) == "/") {
				$isCloseTag = TRUE;
				list($tagName) = explode(' ', $currentTag);
				$tagName = substr($tagName, 1);
			// is start tag
			} else {
				$isCloseTag = FALSE;
				list($tagName) = explode(' ', $currentTag);
			}		
			// excludes all "non-regular" tagnames OR no tagname OR remove if xssauto is on and tag is blacklisted
			if ((!preg_match("/^[a-z][a-z0-9]*$/i",$tagName)) || (!$tagName) || ((in_array(strtolower($tagName), $this->tagBlacklist)) && ($this->xssAuto))) { 				
				$postTag = substr($postTag, ($tagLength + 2));
				$tagOpen_start = strpos($postTag, '<');
				// don't append this tag
				continue;
			}
			// this while is needed to support attribute values with spaces in!
			while ($currentSpace !== FALSE) {
				$fromSpace = substr($tagLeft, ($currentSpace+1));
				$nextSpace = strpos($fromSpace, ' ');
				$openQuotes = strpos($fromSpace, '"');
				$closeQuotes = strpos(substr($fromSpace, ($openQuotes+1)), '"') + $openQuotes + 1;
				// another equals exists
				if (strpos($fromSpace, '=') !== FALSE) {
					// opening and closing quotes exists
					if (($openQuotes !== FALSE) && (strpos(substr($fromSpace, ($openQuotes+1)), '"') !== FALSE))
						$attr = substr($fromSpace, 0, ($closeQuotes+1));
					// one or neither exist
					else $attr = substr($fromSpace, 0, $nextSpace);
				// no more equals exist
				} else $attr = substr($fromSpace, 0, $nextSpace);
				// last attr pair
				if (!$attr) $attr = $fromSpace;
				// add to attribute pairs array
				$attrSet[] = $attr;
				// next inc
				$tagLeft = substr($fromSpace, strlen($attr));
				$currentSpace = strpos($tagLeft, ' ');
			}
			// appears in array specified by user
			$tagFound = in_array(strtolower($tagName), $this->tagsArray);			
			// remove this tag on condition
			if ((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod)) {
				// reconstruct tag with allowed attributes
				if (!$isCloseTag) {
					$attrSet = $this->filterAttr($attrSet);
					$preTag .= '<' . $tagName;
					for ($i = 0; $i < count($attrSet); $i++)
						$preTag .= ' ' . $attrSet[$i];
					// reformat single tags to XHTML
					if (strpos($fromTagOpen, "</" . $tagName)) $preTag .= '>';
					else $preTag .= ' />';
				// just the tagname
			    } else $preTag .= '</' . $tagName . '>';
			}
			// find next tag's start
			$postTag = substr($postTag, ($tagLength + 2));
			$tagOpen_start = strpos($postTag, '<');			
		}
		// append any code after end of tags
		$preTag .= $postTag;
		return $preTag;
	}

	/** 
	  * Internal method to strip a tag of certain attributes
	  * @access protected
	  * @param Array $attrSet
	  * @return Array $newSet
	  */
	protected function filterAttr($attrSet) {	
		$newSet = array();
		// process attributes
		for ($i = 0; $i <count($attrSet); $i++) {
			// skip blank spaces in tag
			if (!$attrSet[$i]) continue;
			// split into attr name and value
			$attrSubSet = explode('=', trim($attrSet[$i]));
			list($attrSubSet[0]) = explode(' ', $attrSubSet[0]);
			// removes all "non-regular" attr names AND also attr blacklisted
			if ((!eregi("^[a-z]*$",$attrSubSet[0])) || (($this->xssAuto) && ((in_array(strtolower($attrSubSet[0]), $this->attrBlacklist)) || (substr($attrSubSet[0], 0, 2) == 'on')))) 
				continue;
			// xss attr value filtering
			if ($attrSubSet[1]) {
				// strips unicode, hex, etc
				$attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);
				// strip normal newline within attr value
				$attrSubSet[1] = preg_replace('/\s+/', '', $attrSubSet[1]);
				// strip double quotes
				$attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);
				// [requested feature] convert single quotes from either side to doubles (Single quotes shouldn't be used to pad attr value)
				if ((substr($attrSubSet[1], 0, 1) == "'") && (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'"))
					$attrSubSet[1] = substr($attrSubSet[1], 1, (strlen($attrSubSet[1]) - 2));
				// strip slashes
				$attrSubSet[1] = stripslashes($attrSubSet[1]);
			}
			// auto strip attr's with "javascript:
			if (	((strpos(strtolower($attrSubSet[1]), 'expression') !== false) &&	(strtolower($attrSubSet[0]) == 'style')) ||
					(strpos(strtolower($attrSubSet[1]), 'javascript:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'behaviour:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'vbscript:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'mocha:') !== false) ||
					(strpos(strtolower($attrSubSet[1]), 'livescript:') !== false) 
			) continue;

			// if matches user defined array
			$attrFound = in_array(strtolower($attrSubSet[0]), $this->attrArray);
			// keep this attr on condition
			if ((!$attrFound && $this->attrMethod) || ($attrFound && !$this->attrMethod)) {
				// attr has value
				if ($attrSubSet[1]) $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
				// attr has decimal zero as value
				else if ($attrSubSet[1] == "0") $newSet[] = $attrSubSet[0] . '="0"';
				// reformat single attributes to XHTML
				else $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[0] . '"';
			}	
		}
		return $newSet;
	}

	/** 
	  * Method to be called by another php script. Processes for SQL injection
	  * @access public
	  * @param Mixed $source - input string/array-of-string to be 'cleaned'
	  * @param Buffer $connection - An open MySQL connection
	  * @return String $source - 'cleaned' version of input parameter
	  */
	public function safeSQL($source, &$connection) {
		// clean all elements in this array
		if (is_array($source)) {
			foreach($source as $key => $value)
				// filter element for SQL injection
				if (is_string($value)) $source[$key] = $this->quoteSmart($this->decode($value), $connection);
			return $source;
		// clean this string
		} else if (is_string($source)) {
			// filter source for SQL injection
			if (is_string($source)) return $this->quoteSmart($this->decode($source), $connection);
		// return parameter as given
		} else return $source;	
	}

	/** 
	  * @author Chris Tobin
	  * @author Daniel Morris
	  * @access protected
	  * @param String $source
	  * @param Resource $connection - An open MySQL connection
	  * @return String $source
	  */
	protected function quoteSmart($source, &$connection) {
		// strip slashes
		if (get_magic_quotes_gpc()) $source = stripslashes($source);
		// quote both numeric and text
		$source = $this->escapeString($source, $connection);
		return $source;
	}
	
	/** 
	  * @author N/A 
	  * @access protected
	  * @param String $source
	  * @param Resource $connection - An open MySQL connection
	  * @return String $source
	  */	
	protected function escapeString($string, &$connection) {
		// depreciated function
		if (version_compare(phpversion(),"4.3.0", "<")) mysql_escape_string($string);
		/*
		if (version_compare(phpversion(),"4.3.0", "<")) {
			$connection->real_escape_string($string);
			/* echo '<script>echo("mysql_escape_string")</script>';
			}
		*/
		// current function
		else mysql_real_escape_string($string);
		/*
		else {
			$connection->real_escape_string($string);
			/* echo '<script>echo("real_escape_string")</script>';
			}
		*/
		return $string;
	}
}