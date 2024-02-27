<?php

require_once('include/SuppleApplication.php');

abstract class SuppleObject {

	public $db;

	function __construct(){

		$this->db = SuppleApplication::getdb();

	}	

}