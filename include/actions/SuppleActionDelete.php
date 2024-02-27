<?php


require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');
require_once('include/SuppleFile.php');

class SuppleActionDelete extends SuppleAction {

	public $name = 'delete';
	public $domain = 'table';
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function performAction($table, $filter, $data, &$recursion_history = array()){

		$r = array('error' => '');

		if (empty($filter)){
			$filter = $data;
		}
		$deleted_data = $this->db->from($table)->where($filter)->getArray();
		
		$ids = $this->db->delete($table, $filter); // TODO: $bean->remove

		$r['id'] = $ids;
		
		// SuppleApplication::getlog()->info("DELETED IDs = ".print_r($ids,true));
	
		if ($ids){
			$sf = new SuppleFile();			
			foreach ($ids as $id){
				$sf->delete($id, $table); 
			}
		}

		// Dependencies???
		// $this->processDependencies($table, $deleted_data, $data, $recursion_history);

		return $r;
		
	}
	
}


?>