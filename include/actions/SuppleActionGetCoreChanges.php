<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionGetCoreChanges extends SuppleAction {

	public $name = 'getcorechanges';
	public $domain = 'global'; // TODO: admin only
    private $metadata_cache = array();

    function __construct(){
	
		parent::__construct();
	
	}

	public function performAction($table, $filter, $data, &$recursion_history = array()){
        global $db;
        
        $r = array('error' => '', 'data' => array());

        $tables = $db->coreTables();
        foreach ($tables as $t){
            $fulltable = $db->from($t)->getBeans();
            foreach ($fulltable as $bean){
                if (isNumericId($bean->id) && !$bean->isInCore()){
                    if (!isset($r['data'][$t])){
                        $r['data'][$t] = array();
                        $r['status'][$t] = array();
                    } 
                    $r['data'][$t][$bean->id] = $bean->getRecordDescription();
                    $r['stat'][$t][$bean->id] = ($db->isInCore($bean->id, $bean->tableName()) !== false);
                }
            }
        }
        return $r;
    }

    

}