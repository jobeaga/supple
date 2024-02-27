<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionDownload extends SuppleAction {

	public $name = 'download';
	public $domain = 'table';
	public $needACL = true; 
	
	function __construct(){
	
		parent::__construct();
	
	}

	public function performAction($table, $get, $post, &$recursion_history = array()){

		ini_set('display_errors', '0');

		$field = $get['field'];
		$id = $get['id'];
		$image = isset($get['image']); // indicates whether is an image or not

		$row = $this->db->from($table)->where(array('id' => $id))->getRow();
		$file = (empty($row[$field]))?'':$row[$field];



		// filter ../ from the filename
		if (strtolower(substr($file, 0, 8)) != 'uploaded') $file = '';
		if (strpos($file, '..') !== false) $file = '';


		$file = $this->similarName($file);

		if ($file){
			
			if ($image){
				header("Content-type: image/jpeg");
			} else {
				header("Content-type: application/force-download");
				header("Content-disposition: attachment; filename=\"".$file."\";");
			}
			header("Content-Length: " . filesize($file));
			// header("Content-disposition: attachment; filename=\"".$file."\";");
			echo file_get_contents($file);
			SuppleApplication::getlog()->info("DOWNLOADED = ".$file);
			SuppleApplication::ob_flush(); // por las dudas
			
			die();
			
			// better for large files:
			// header('Location: '.$file);
				
		} else {

			SuppleApplication::getlog()->warn("Trying to download non-existent file $file TABLE=$table ID=$id FIELD=$field");
			
			// output the error message
			echo "Error: File not found.";
			die();
			
		}

	}
	
	
	function similarName($file){
		// existencia
		if (file_exists($file)) return $file;
		
		// insensible a mayusculas
		$folder = getFilePath($file);
		$a = glob($folder . "/*");
		foreach ($a as $eachFile){
			if (strtolower($eachFile) == strtolower($file)) return $eachFile;
		}
		
		// no encontrado
		return "";
	}

	
}


?>