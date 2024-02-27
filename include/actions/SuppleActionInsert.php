<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');
require_once('include/SuppleFile.php');

class SuppleActionInsert extends SuppleAction {

	public $name = 'insert';
	public $domain = 'table';
	
	function __construct(){
	
		parent::__construct();
	
	}

	public function performAction($table, $filter, $data, &$recursion_history = array()){

		$r = array('error' => '');

/*		if ($table != "_entities"){
			foreach ($data as $clave => $valor){
				//$data[$clave] = str_replace('"', '&quot;', $valor); 
			}
		}
*/		
		// Guardo la ip, si me lo piden
		if (isset($data['ip']) && $data['ip'] == ''){
			$data['ip'] = $_SERVER['REMOTE_ADDR'];
		}
		// TODO: AND OTHER VALUES (eg date_entered, date_modified, user_entered, user_modified, ip_entered, ip_modified)

		// INSERT!!!
		$id = $this->db->insert($table, $data);

		$r['id'] = $id;
		
		SuppleApplication::getlog()->info("INSERTED ID = ".$id);
			
		// UPLOAD FILES
		if ($_FILES){
			
			require_once('include/SuppleFile.php');
			$sf = new SuppleFile();
			
			// Upload each file
			foreach ($_FILES as $key => $file){
				$sf->upload($file['tmp_name'], $file['name'], $id, $table, $key);
			}
		}

		return $r;

	}
	
}


?>