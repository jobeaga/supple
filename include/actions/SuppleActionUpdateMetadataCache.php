<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionUpdateMetadataCache extends SuppleAction {

	public $name = 'update_metadata_cache';
	public $domain = 'global'; // TODO: admin only

    function __construct(){
	
		parent::__construct();
	
	}

	public function performAction($table, $filter, $data, &$recursion_history = array()){
        $r = array('error' => '');
    
        SuppleApplication::getcache()->updateMetadata();

        return $r;
        
    }

}

// 