<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');
require_once('include/global/SuppleLanguage.php');

class SuppleActionSetLanguage extends SuppleAction {

	public $name = 'setlanguage';
	public $domain = 'global';
	public $needACL = false;
	
	public function perform(){
	
		// Get instance of SuppleLanguage and setLanguage
		$sl = SuppleLanguage::getInstance();
		$sl->setLanguage($_REQUEST['code']);
		
		$this->handleRedirect();
	
	}

	public function handleRedirect(){
	
		$redirect = SuppleApplication::getredirect();
		
		if (empty($redirect)){
			$redirect = 'index.php';
		}

		SuppleApplication::redirect($redirect);
		
	}
	
}


?>