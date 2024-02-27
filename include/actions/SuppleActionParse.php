<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionParse extends SuppleAction {

	public $name = 'parse';
	public $domain = 'table'; // bean over a table
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function performAction($table, $filter, $data, &$recursion_history = array()) {

        $r = array('error' => '');

        global $db;

		$field = '';
        if (!empty($filter['field'])) {
            $field = $filter['field'];
        }
        $template_id = '';
        if (!empty($filter['template_id'])) {
            $template_id = $filter['template_id'];
        }

        // REMOVE???
        /*foreach ($filter as $k => $v) {
			if (substr($k, 0, 1) == '_') {
				unset($filter[$k]);
			}
		}*/

        $template = '';
        if (!empty($table) && !empty($field) && !empty($template_id)) {
            $row = $db->from($table)->where(array('id' => $template_id))->getRow();
            if (isset($row[$field])) $template = $row[$field];
        } 

        require_once('include/SuppleTemplate.php');
        $st = new SuppleTemplate();
        
        $r['parsed'] = $st->parseString($template, $data);

        return $r;

	}

}


?>
