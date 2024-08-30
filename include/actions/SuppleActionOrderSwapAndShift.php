<?php

require_once('include/util.php');
require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');
require_once('include/fields/SuppleFieldOrder.php');

class SuppleActionOrderSwapAndShift extends SuppleAction {

    public $name = 'orderSwapAndShift';
	public $domain = 'table'; 

    public function performAction($table, $get, $post, &$recursion_history = array()){
        
        $r = array('error' => '', 'data' => array(), 'table' => '', 'count' => 0);

        $r['table'] = $table;

        $from_id = '';
        $to_id = '';
        $field_name = '';
         
        if (isset($get['source_id'])) $from_id = $get['source_id']; 
		if (isset($get['target_id']))  $to_id = $get['target_id']; 
		if (isset($get['order']))  $field_name = $get['order'];
		
        if ($from_id != '' && $to_id != '' && $field_name != ''){
            $sfo = new SuppleFieldOrder();
            $r['data'] = $sfo->orderSwapAndShift($table, $from_id, $to_id, $field_name);
            $r['count'] = count($r['data']);
        } else {
            // ERROR: missing parameters
            $r['error'] = 'missing parameters';
        }

        return $r;

    }

}