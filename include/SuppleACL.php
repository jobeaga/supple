<?php

// Clase destinada a gestionar los permisos a usuarios, 
// y a restringir el acceso en el caso correspondiente, 
// logeando cada intento.
/*require_once('include/SuppleLog.php');
require_once('include/util.php');*/

require_once('include/SuppleApplication.php');
require_once('include/SuppleObject.php');

class SuppleACL extends SuppleObject {

	var $connection;
	var $log;

	function __construct(){
	
		$this->connection = SuppleApplication::getdb();
		$this->log = SuppleApplication::getlog();
	
	}
	
	function checkAccess($action){
	
		$current_user_id = SuppleApplication::getUser();
		
		if ((!empty($action->domain)) && isset($_GET[$action->domain])){
			$domain = $action->domain;
			$domainObject = $_GET[$action->domain];
		} else {
			$domain = '';
			$domainObject = '';
		}
		
		if ($action->needACL == false){
		
			return true;
		
		} elseif ($current_user_id){
		
			$user = $this->connection->from('users')->where(array('id' => $current_user_id))->getRow();
			
			$this->log->info("User ".$user['user']." Performs $action->name on $domainObject. \n");
			
			if ($user['isadmin']) return true;
			
			// TODO: check user permissions
			return true;

		} else {
		
			$this->log->acl("Web User tries to Perform $action->name on $domainObject. \n");
		
			// TODO: check web users permissions
			return false;
		
		}
	
	}

}

?>