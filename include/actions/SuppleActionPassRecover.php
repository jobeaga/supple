<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionPassRecover extends SuppleAction {

	public $name = 'pass_recover';
	public $domain = 'global';
	public $needACL = false;
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function perform(){
	
		if (SuppleApplication::prepareValues('', $table, $post, $get)){
		
			// TODO: Check received code. Compare dates. Reset password
			
		}
	}
	
}


?>