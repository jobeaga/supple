<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionPassForgot extends SuppleAction {

	public $name = 'pass_forgot';
	public $domain = 'global';
	public $needACL = false;
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function perform(){
	
		if (SuppleApplication::prepareValues('', $table, $post, $get)){
		
			// TODO: Send email with code. Save code in DB
			
		}
	}
	
}


?>