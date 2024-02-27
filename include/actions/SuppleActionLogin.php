<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionLogin extends SuppleAction {

	public $name = 'login';
	public $domain = 'global';
	public $needACL = false;
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function perform(){
	
		$loginResult = $this->authenticateUser();
		
		$this->handleRedirect($loginResult);
	
	}

	public function authenticateUser(){
		global $ses, $db;

		// $session_time = self::getconfig()->getValue('ses_duration_min') * 60; 
		
		// TODO: Refactor names
		$user = (isset($_POST['nombre'])) ? $_POST['nombre'] : '';
		$pass = (isset($_POST['password'])) ? $_POST['password'] : '';
		$auth_key = (isset($_POST['auth_key'])) ? $_POST['auth_key'] : '';
		$auth_id = '';

		if (!empty($user) && !empty($pass)) {

			$pass = md5($pass);
			
		} elseif ($ses->isSet('user')) {

			$pass = $ses->getValue('pass'); // md5 already applied
			$user = $ses->getValue('user');

		} elseif (!empty($auth_key) || $ses->isSet('auth_key')) {

			if (empty($auth_key)){
				$auth_key = $ses->getValue('auth_key');
			}

			// fetch auth_key, get pass and user from there
			if (!empty($auth_key)){
				$auth_bean = $db->from('auth_keys')->where(array('auth_key' => $auth_key))->getBean();
				if (!empty($auth_bean->id)){
					$pass = $auth_bean->pass; // md5 already applied
					$user = $auth_bean->user;
					$auth_id = $auth_bean->id;
				}
			}

		} 
		
		// a esta altura ya tengo user y pass. Los casos de auth_key ya se contemplaron, si no hay, es porque no va a haber.
		if (empty($user) || empty($pass)) {
			return -1;
		}
		
		if (!empty($auth_key) && empty($auth_id)){
			$auth_id = $db->from('auth_keys')->where(array('auth_key' => $auth_key))->getBean()->id;
		}
		
		// fetch...
		$condition = array(
			'user' => $user,
			'pass' => $pass,
		);
		$row = $db->from('users')->where($condition)->getRow();
		
		if ($row){
			$ses->setValue('pass', $pass);
			$ses->setValue('user', $user);
			if (!empty($auth_key)){
				// set in session:
				$ses->setValue('auth_key', $auth_key);
				// set auth_key in DB
				$last_login = date('Y-m-d H:i:s');
				if (empty($auth_id)){
					$db->insert('auth_keys', array('auth_key' => $auth_key, 'user' => $user, 'pass' => $pass, 'last_login' => $last_login));
				} else {
					$db->update('auth_keys', array('auth_key' => $auth_key, 'user' => $user, 'pass' => $pass, 'last_login' => $last_login), array('id' => $auth_id));
				}			

			} 
			// DARK MODE
			if ($row && isset($_POST['nombre']) && isset($_POST['nombre']) && isset($_POST['dark_mode']) && $_POST['dark_mode'] != ''){
				$db->update('users', array('dark_mode' => $_POST['dark_mode']), $condition);
			}
		}
		
		if ($row){
			return 1;
		} else {
			return -1; 
		}
	}

	public function handleRedirect($result){
	
		$redirect = SuppleApplication::getredirect();
		
		if ($redirect){
			$r = $redirect;
			if ($result && strpos(strtolower($r), ".php")){
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