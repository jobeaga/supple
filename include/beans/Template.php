<?php 
require_once('include/SuppleBean.php');
class Template extends SuppleBean {
	function __construct($table = '', $id = '', $light = false){
		parent::__construct($table, $id, $light);

		$this->_table = '_templates';
    }

    function parse($bean, $extra_data = array()){
        $r = array();

        require_once('include/SuppleTemplate.php');
        $t = new SuppleTemplate();

        // BEAN DATA
        $d = $bean->getData();
        $data = $d;
        foreach ($d as $k => $v){
            $data[$bean->tableName().'.'.$k] = $v;
        }

        // RELATED DATA
        // OPCION 1: 
        // <!-- $table(id=='record_id') -->
        // <!-- $table(rel_field=='related_value') -->
        // OPCION 2:
        // $data[$table.$field] = $value
        
        // EXTRA DATA
        $data = array_merge($data, $extra_data);
        // PARSE DATES:
        foreach ($data as $i => $v){
            $data[$i] = $this->parseDate($v);
        }
        
        $r['subject'] = $t->parseString($this->subject, $data);
        $r['body'] = $t->parseString($this->body, $data);
        return $r;
    }

    function parseDate($string){
        global $config;
        $datetime_pattern = '/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}$/';
        $date_pattern = '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/';
        $date_format = $config->getValue('php_date_format');
        $datetime_format = $config->getValue('php_datetime_format');

        if (strlen($string) == 16 && preg_match($datetime_pattern, $string)){
            return date($datetime_format, strtotime($string));
        } else if (strlen($string) == 10 && preg_match($datetime_pattern, $string)){
            return date($date_format, strtotime($string));
        } else {
            return $string;
        }
    }

}