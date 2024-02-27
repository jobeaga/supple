<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionMappings extends SuppleAction {

	public $name = 'mappings';
	public $domain = 'global';
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function perform(){

		$r = array('error' => '');
	
		if (SuppleApplication::prepareValues('', $table, $post, $get)){
	
			SuppleApplication::getdb()->setMappings($post);
	
		}	

		return $r;
	}
	
}


?>