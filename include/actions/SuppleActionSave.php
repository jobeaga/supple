<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');
require_once('include/SuppleFile.php');

class SuppleActionSave extends SuppleAction {

	public $name = 'save';
	public $domain = 'table'; // bean over a table
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function performAction($table, $filter, $data, &$recursion_history = array()){
		global $db;

		$r = array('error' => '');

		// SAVE
		$id = (isset($filter['id']))?$filter['id']:'';

		$bean = SuppleBean::getBean($table, $id, true); // light

		$bean->populate($data, false);

		// TODO: VALIDATE BEFORE SAVE???
		// $valid = $bean->validate();
		// if ($valid !== true) { 
			// give $valid as error message 
			// $r['error'] = $valid; return $r;
		//}

		$id = $bean->save();

		$r['id'] = $bean->id;
		$r['record'] = $bean->getData();

		// UPLOAD FILES
		// TODO: REMOVE FILES (file remove happens on record elimination)
		if ($_FILES) {

			//require_once('include/SuppleFile.php');
			//$sf = new SuppleFile();
			
			// Upload each file 
			foreach ($_FILES as $key => $file){

				if (is_array($file['tmp_name'])){
					// MULTI-FIELD
					$key_index = 1; // TODO: previously loaded images?
					foreach ($file['tmp_name'] as $i => $v){
						$tempf = array(
							'tmp_name' => $file['tmp_name'][$i],
							'name' => $file['name'][$i],
							'error' => $file['error'][$i],
							'type' => $file['type'][$i],
							'size' => $file['size'][$i],
						);
						$d = $this->fileSave($tempf, $table, $id, $key.'_'.$key_index);
						if (is_array($d)){
							foreach ($d as $k => $v){
								$r['record'][$k] = $v;
							}
						} else {
							$r['error'] = $d;
						}
						$key_index++;
					}
					
					// para que no sólo se guarde cada imagen en su indice.
					$r['record'][$key] = ''; 
					$db->update($table, array($key => ''), array('id' => $id));


				} else {
					// SINGLE FIELD
					$d = $this->fileSave($file, $table, $id, $key);
					if (is_array($d)){
						foreach ($d as $k => $v){
							$r['record'][$k] = $v;
						}
					} else {
						$r['error'] = $d;
					}
				}
				

				/*
				// ERROR PROCESSING
				if ($file['error'] > 0){
					if ($file['error'] != UPLOAD_ERR_NO_FILE){
						$message = $this->codeToMessage($file['error']);
						//SuppleApplication::setError("Error: $message ($key: {$file['name']})");
						$r['error'] = $message;
					}
				} else {
					$data = $sf->update($file['tmp_name'], $file['name'], $id, $table, $key);
					// Set data on the response
					foreach ($data as $k => $v){
						$r['record'][$k] = $v;
					}
				}
				*/
			}
		}

		// check if a sync is needed
		//$action = SuppleAction::getAction('syncmirrors');
		//$action->checkAndPerformIfNeeded();

		return $r;

	}

	private function fileSave($file, $table, $id, $key){
		
		$sf = new SuppleFile();
		
		// ERROR PROCESSING
		if ($file['error'] > 0){
			if ($file['error'] != UPLOAD_ERR_NO_FILE){
				$message = $this->codeToMessage($file['error']);
				// Return error msg
				return $message;
			}
		} else {
			$data = $sf->update($file['tmp_name'], $file['name'], $id, $table, $key);
			// Set data on the response
			return $data;
		}
	}

	private function codeToMessage($code) 
    { 
		// TODO: TRANSLATE
        switch ($code) { 
            case UPLOAD_ERR_INI_SIZE: 
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini"; 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form"; 
                break; 
            case UPLOAD_ERR_PARTIAL: 
                $message = "The uploaded file was only partially uploaded"; 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $message = "No file was uploaded"; 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                $message = "Missing a temporary folder"; 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                $message = "Failed to write file to disk"; 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $message = "File upload stopped by extension"; 
                break; 
            default: 
                $message = "Unknown upload error (".print_r($code, true).")"; 
                break; 
        } 
        return $message; 
    } 
	
}


?>
