<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionLogout extends SuppleAction {

	public $name = 'logout';
	public $domain = 'global';
	public $needACL = false;
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function perform(){
	
		$logoutResult = $this->logout();
		
		$this->handleRedirect($logoutResult);
	
	}

	public function logout(){
		global $ses, $db;

		$ses->unsetValue('user');
		$ses->unsetValue('pass');
		
		if (!empty($_REQUEST['auth_key']) || $ses->isSet('auth_key')){
			if (empty($_REQUEST['auth_key'])){
				$auth_key = $ses->getValue('auth_key');
			} else {
				$auth_key = $_REQUEST['auth_key'];
			}
			$db->delete('auth_keys', array('auth_key' => $auth_key));
			$ses->unsetValue('auth_key');
		}
		
		return true;
	
	}

	public function handleRedirect($result){
	
		$redirect = SuppleApplication::getredirect();
		
		if ($redirect){
			$r = $redirect;
			if (strpos(strtolower($r), ".php")){
				if (strpos($r, '?')){
					$r .= '&loginResult='.$result;
				} else {
					$r .= '?loginResult='.$result;
				}
			}
			SuppleApplication::redirect($r);
			
		} else {
			echo $result;
			die(); // Preventivo
		}
		
	}
	
}


?>