<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionInstallPackage extends SuppleAction {

	public $name = 'installpackage';
	public $domain = 'global'; // TODO: admin only
    private $metadata_cache = array();

    function __construct(){
	
		parent::__construct();
	
	}

	public function performAction($table, $filter, $data, &$recursion_history = array()){
        global $db;

        // TODO: 
        //  - CHECK ERRORS $_FILES['error']
        if ($_FILES['package']['error'] == 0){
            //  - CREATE TEMP FOLDER
            emptyFolder('temp/');
            if (!file_exists('temp')) mkdir('temp');
            
            //  - UNZIP $_FILES['tmp_name'] on TEMP FOLDER
            $zip = new ZipArchive;
            if ($zip->open($_FILES['package']['tmp_name']) === TRUE) {
                $zip->extractTo('temp');
                $zip->close();

                //  - COPY AND REPLACE (unlink before?)
                $this->copyAndReplace('temp/', './');

                //  - INSERT DATA (insert/update)
                if (file_exists('temp/data.php')){
                    require('temp/data.php');
                    foreach ($data as $table => $records){
                        foreach ($records as $row){
                            if (isset($row['id'])){
                                $old_row = $db->from($table)->where(array('id' => $row['id']))->getRow();
                                if (empty($old_row)){
                                    $db->insert($table, $row);    
                                } else {
                                    $db->update($table, $row, array('id' => $row['id']));
                                }
                            } else {
                                // SIMPLE INSERT
                                $db->insert($table, $row);
                            }
                        }
                    }
                }
                
            } else {
                echo 'failed<br>';
                die();
            }

            // REMOVE TEMP FILE and TEMP FOLDER
            emptyFolder('temp/');
            if (file_exists($_FILES['package']['tmp_name'])) 
                unlink($_FILES['package']['tmp_name']);

            // REDIRECT? Back to packages?
            SuppleApplication::redirect('admin.php?entity=41&view=2');
            die();

        } else {
            echo "FILE ERROR ".$_FILES['package']['error']; die();
        }

    }

    function copyAndReplace($from, $to, $first = true){
        if (file_exists($from) && file_exists($to) && is_dir($from) && is_dir($to)){
            $a = glob($from.'*');
            foreach ($a as $filename){
                $target = $to.substr($filename, strlen($from));
                if (is_dir($filename)){
                    // RECURSIVE!!!
                    //echo "RECU: $filename/, $target/ <br>";
                    $this->copyAndReplace($filename.'/', $target.'/', false);
                } else {
                    if (!$first){
                        // UNLINK?
                        if (file_exists($target)) unlink($target);
                        // COPY!!
                        // echo "COPY $filename, $target<br>";
                        copy($filename, $target);
                    }
                }
            }
        }
        
    }

}