<?php

require_once('include/SuppleGlobal.php');

class SuppleSession extends SuppleGlobal {

	public $name = 'session';

	function getValues(){
		//$session_id = SuppleApplication::getSessionId();
		//return $this->db->from('_sessions')->where(array('id' => $session_id))->getRow();
		return $_SESSION;
	}

	function getValue($attribute){
		$values = $this->getValues();
		return (isset($values[$attribute]))?$values[$attribute]:'';
	}

	function setValue($attribute, $value){
		//$session_id = SuppleApplication::getSessionId();
		//$this->db->update('_sessions', array($attribute => $value), array('id' => $session_id));
		$_SESSION[$attribute] = $value;
	}

	function unsetValue($attribute){
		unset($_SESSION[$attribute]);
	}

	function isSet($attribute){
		return isset($_SESSION[$attribute]);
	}

	function load(){
		// $this->db = SuppleApplication::getdb();
		// none
	}

	function save(){
		// none
	}
}
