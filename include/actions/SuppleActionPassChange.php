<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionPassChange extends SuppleAction {

	public $name = 'pass_change';
	public $domain = 'user';
	public $needACL = false;
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function perform(){

		$r = array('error' => '');
	
		if (SuppleApplication::prepareValues('', $table, $post, $get)){
		
			$usuarios = $this->db->from('users')->getArray();
			$userIndex = authenticate($usuarios, '', '', '');

			if ($userIndex === false){

				$r['error'] = "Debe estar logeado para cambiar la contrase&ntilde;a.";
				// TODO: Tirar un error

			} else {

				// ¿Las pass que envió son iguales?
				$same = $_REQUEST['pass1'] == $_REQUEST['pass2'];
				
				if ($same){

					$user = $usuarios[$userIndex]['user'];
					$password = $_REQUEST['pass1'];
					
					// Actualizo la pass
					$set = array('pass' => $password);
					$where = array('id' => $usuarios[$userIndex]['id']);
					$this->db->update('users', $set, $where);

					// Vuelvo a logearlo
					$redirect = (isset($_GET['redirect']))?$_GET['redirect']:'';
					$usuarios = $this->db->from('users')->getArray();
					$userIndex = authenticate($usuarios, $redirect, $user, $password);
					
					if ($redirect) redirect($redirect);
					
					$r['error'] = "Su contrase&ntilde;a ha cambiado correctamente. Debe salir de la sesi&oacute;n y volver a ingresar con su nueva contrase&ntilde;a.";

				} else {
				
					// Error, no son iguales
					$r['error'] = "Las contrase&ntilde;as no son iguales";
				
				} // end if same
				
			} // end if userindex

			
		} // end if preparvalues

		return $r;
	} // end function
	
}


?>