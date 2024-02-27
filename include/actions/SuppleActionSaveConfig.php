<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionSaveConfig extends SuppleAction {

	public $name = 'save_config';
	public $domain = 'global';
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function performAction($table, $filter, $data, &$recursion_history = array()){

		$r = array('error' => '');
	
		// $post debería ser el array que le debo guardar a config
		$config = SuppleApplication::getconfig();
		$config->setValues($data);
		$config->save();
		// print_r($data); die();

		$r['config'] = $config->getValues();

		return $r;
		
	}
	
}


?>