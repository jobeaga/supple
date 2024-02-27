<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');
require_once('include/SuppleFile.php');

class SuppleActionUpdate extends SuppleAction {

	public $name = 'update';
	public $domain = 'table';
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function performAction($table, $filter, $data, &$recursion_history = array()){

		$r = array('error' => '');

		// Guardo la ip, si me lo piden
		if (isset($data['ip']) && $data['ip'] == ''){
			$data['ip'] = $_SERVER['REMOTE_ADDR'];
		}

		// UPDATE!!!
		$ids = $this->db->update($table, $data, $filter);

		$r['id'] = $ids;

		// UPLOAD FILES
		if (($_FILES) && ($ids)){

			require_once('include/SuppleFile.php');
			$sf = new SuppleFile();
			
			// Upload each file for each affected record
			foreach ($_FILES as $key => $file){
				foreach ($ids as $id){

					$sf->update($file['tmp_name'], $file['name'], $id, $table, $key);
					
				}
			}
		}

		return $r;
	}
	
}


?>