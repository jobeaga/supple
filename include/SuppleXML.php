<?php 

// TO TEST!!!


class SuppleXML extends SimpleXMLElement {

	function __construct(){

		parent::SimpleXMLElement();

	}

	function addChild($name, $value = '', $namespace = ''){

		if (empty($value) && empty($namespace)){
			$r = parent::addChild($name);
		} else if (empty($namespace)) {
			$r = parent::addChild($name, $value);
		} else {
			$r = parent::addChild($name, $value, $namespace);
		}
		return ((SuppleXML) ($r));

	}

	// TO TEST!
	function addArrayChild($array){

		if (is_array($array)){

		    foreach($array as $key => $value) {
			if(is_array($value)) {
			    if(!is_numeric($key)){
				$subnode = $this->addChild("$key");
				$subnode->addArrayChild($value);
			    }
			    else{
				$this->addArrayChild($value);
			    }
			}
			else {
			    $this->addChild("$key","$value");
			}
		    }
		}
	}

}

?>
