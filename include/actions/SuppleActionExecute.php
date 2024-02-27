<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');
require_once('include/actions/SuppleActionRead.php');

class SuppleActionExecute extends SuppleActionRead {

	public $name = 'execute';
	public $domain = 'table'; // bean over a table
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function performAction($table, $get, $post, &$recursion_history = array()){
        global $db;

        $r = array('error' => '', 'data' => array(), 'table' => '', 'count' => 0);

        $order = '';
		$reverse = false;
        if (isset($get['order'])) { $order = $get['order']; unset($get['order']); }
        if (isset($get['reverse'])) { $reverse = $get['reverse']; unset($get['reverse']); }
        $e = explode(' ', $order);
		if (isset($e[1]) && strtolower($e[1]) == 'reverse') {
			$order = $e[0];
			$reverse = 1;
        }
        // TODO: More than 1 order (same on read)

        // METHODS:
        $methods = array();
        if (is_array($get['method'])){
            foreach ($get['method'] as $m){
                $methods[] = $m;
            }
        } else if (isset($get['method'])) {
            $methods[] = $get['method'];
        }
        unset($get['method']);

        // RECORD IDS:
        $ids = array();
        if (is_array($get['id'])){
            foreach ($get['id'] as $m){
                $ids[] = $m;
            }
        } else if (isset($get['id'])){
            $ids[] = $get['id'];
        }
        unset($get['id']);
        if (is_array($post['id'])){
            foreach ($post['id'] as $m){
                $ids[] = $m;
            }
        } else if (isset($post['id'])){
            $ids[] = $post['id'];
        }
        unset($post['id']);

        // TODO: FILTER
        $pre_filter = array_merge($get, $post);
        $filter = $this->buildFilter($pre_filter, $table);

        if (!empty($table)){
            $r['table'] = $table;
            if (empty($ids)){
                $sdata = $this->read($table, $filter, 0, 100000, $order, $reverse);
                $data = array();
                foreach ($sdata as $row){
                    $ids[] = $row['id'];
                    $data[$row['id']] = $row;
                }
            } else {
                // GET DATA FOR EACH ID
                foreach ($ids as $id){
                    $bean = SuppleBean::getBean($table, $id);
                    $data[$id] = $bean->getData();
                }
            }
            
            foreach ($ids as $id){
                $bean = SuppleBean::getBean($table);
                $bean->populate($data[$id], false);
                //$bean = SuppleBean::getBean($table, $id);
                $sdata = array('id' => $id);
                //$sdata = $bean->getData();
				//$sdata['_nextid'] = $bean->_nextid;
                //$sdata['_previousid'] = $bean->_previousid;
                foreach ($methods as $m){
                    if (method_exists($bean, $m)){
                        $sdata[$m] = $bean->$m();
                    } else if (isset($bean->$m)){
                        $sdata[$m] = $bean->$m;
                    }
                }
                $r['data'][] = $sdata;
                $r['count']++;
            }
        }
        //print_r($r); die();
        return $r;

    }
}
