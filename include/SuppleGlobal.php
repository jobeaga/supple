<?php 

require_once('include/SuppleObject.php');

class SuppleGlobal extends SuppleObject {
	
	private static $_instances = array();
	public $name;

	// SINGLETON!!!
	public static function getInstance()
	{
		$calledClass = get_called_class();
	    if (!isset(self::$_instances[$calledClass])) {
	        self::$_instances[$calledClass] = new $calledClass();
	    }
	    return self::$_instances[$calledClass];
	}

	public static function loadInstances() {

		$files = array();
		$a = glob('include/global/*.php');
		foreach ($a as $file){
			$className = getFileName($file);
			$files[$className] = $file;
		}
		$a = glob('custom/global/*.php');
		foreach ($a as $file){
			$className = getFileName($file);
			$files[$className] = $file;
		}

		foreach ($files as $className => $file){
			require_once($file);
			if (class_exists($className)){
				$className::getInstance();
			}
		}

		return self::$_instances;
	}

	public static function getData() {
		// Get instance of each class
		$instances = SuppleGlobal::loadInstances();

		// Use getValues
		$data = array();
		foreach ($instances as $i) {
			$data[$i->name] = $i->getValues();
		}

		// Return array
		return $data;
	}

	function __construct(){
		$this->load();
	}
	
	function getValues(){
		return array();
	}
	
	function load(){
	
	}

	function save(){
	
	}

}