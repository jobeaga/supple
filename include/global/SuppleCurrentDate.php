<?php

require_once('include/SuppleGlobal.php');

class SuppleCurrentDate extends SuppleGlobal {

	public $name = 'current_date';
	private $dateVars;

	function getValues(){
		if (empty($this->dateVars)) {
			$this->dateVars = SuppleApplication::getDateVars();
		}
		return $this->dateVars;
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
