<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionRemove extends SuppleAction {

	public $name = 'remove';
	public $domain = 'table'; // bean over a table
	
	public function performAction($table, $filter, $data, &$recursion_history = array()){

		$r = array('error' => '');

		// TODO: Use this action, or delete instead?
		$id = (isset($filter['id']))?$filter['id']:'';
		$bean = SuppleBean::getBean($table, $id); // with Relationships
		if (!empty($bean->id)){
			$bean->remove(); // this triggers delete and process dependencies
			$r['id'] = $id;
		} else {
			$r['error'] = 'Missing record.';
		}

		return $r;
		
	}
	
}


?>