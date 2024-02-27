<?php 

require_once('include/SuppleBean.php');

class DropdownOptions extends SuppleBean {

    static $listValueCache = array();
    static $fullListCache = array();

	function __construct($table = '', $id = '', $light = false){

		parent::__construct($table, $id, $light);
		$this->_table = '_dropdown_lists';

    }

    function getValue($k){
        global $db;
        return $db->from('_dropdown_options')->where(array(
            'list' => $this->id,
            'key' => $k
        ))->getBean()->value;
    }

    static function getListValue($list_name, $key){
        $r = '';
        if (empty(self::$listValueCache[$list_name][$key])){
            global $db;
            $list = $db->from('_dropdown_lists')->where(array('name' => $list_name))->getBean();
            if (!empty($list->id)){
                $r = $list->getValue($key);
            }
            self::$listValueCache[$list_name][$key] = $r;
        } else {
            $r = self::$listValueCache[$list_name][$key];
        }
        return $r;
    }

    static function getList($list_name){
        $r = array();
        if (empty(self::$fullListCache[$list_name])){
            global $db;
            $list = $db->from('_dropdown_lists')->where(array('name' => $list_name))->getBean();
            if (!empty($list->id)){
                $os = $db->from('_dropdown_options')->where(array(
                    'list' => $list->id
                ))->getBeans();
                foreach ($os as $o){
                    $r[$o->key] = $o->value;
                    // feed getListValue cache
                    self::$listValueCache[$list_name][$o->key] = $o->value;
                }
            }
            self::$fullListCache[$list_name] = $r;
        } else {
            $r = self::$fullListCache[$list_name];
        }
        return $r;
    }
    
}