<?php

require_once('include/SuppleGlobal.php');

class SuppleCurrentUser extends SuppleGlobal {

	public $name = 'current_user';
	private $current_user;

	function getValues(){
		if (empty($this->current_user)) {
			$user_id = SuppleApplication::getUser();
			$this->current_user = $this->db->from('users')->where(array('id' => $user_id))->getRow();
		}
		return $this->current_user;
	}

	function getValue($attribute){
		$values = $this->getValues();
		return (isset($values[$attribute]))?$values[$attribute]:'';
	}

	function setValue($attribute, $value){
		// current user is read only!
	}

	function load(){
		$this->db = SuppleApplication::getdb();
		// none
	}

	function save(){
		// none
	}
}