<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionValidate extends SuppleAction {

	public $name = 'validate';
	public $domain = 'table'; // bean over a table
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function performAction($table, $filter, $data, &$recursion_history = array()){
		global $db;

		$r = array('error' => '');

		$id = (isset($filter['id']))?$filter['id']:'';

		$bean = SuppleBean::getBean($table, $id, true); // light

		$bean->populate($data, false);

		$valid = $bean->validate();

        if ($valid === true){
            $r['valid'] = true;
            $r['messages'] = array();
        } else {
            $r['valid'] = false;
            $r['messages'] = $valid;
        }

        return $r;


    }
}