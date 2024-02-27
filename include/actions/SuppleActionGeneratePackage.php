<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionGeneratePackage extends SuppleAction {

	public $name = 'genpackage';
	public $domain = 'global'; // TODO: admin only
    private $metadata_cache = array();

    function __construct(){
	
		parent::__construct();
	
	}

	public function performAction($table, $filter, $data, &$recursion_history = array()){

        $r = array('error' => '');

        $from = $filter['from'];
        $to = $filter['to'];

        if (empty($from) || empty($to)){
            $r['error'] = 'Error: Empty Dates';
        } else if (!validDate($from) || !validDate($to)) {
            $r['error'] = 'Error: Invalid Dates';
        } else if ($from >= $to) {
            $r['error'] = 'Error: Empty date interval ';
        } else {
            // OFFSET CORRECTION:
            $cfg = SuppleApplication::getconfig();
	        $offset = $cfg->getValue('gmt_offset');
            $from = date('Y-m-d H:i:s', strtotime("$from 00:00") - ($offset * 60 * 60));
            $to = date('Y-m-d H:i:s', strtotime("$to 23:59") - ($offset * 60 * 60));

            $r['data'] = $this->getData($from, $to);
            $r['files'] = $this->getFiles(strtotime($from), strtotime($to));
        }

        return $r;

    }

    private function getData($from, $to){
        global $db;
        define("date_modified", "''"); // para los casos de registros que no tienen date_modified definida.
        $r = array();
        $entities = $db->from('_entities')->getArray();
        foreach ($entities as $e => $e_def){
            $table = $e_def['table'];
            //echo "$table<br>";
            $data = $db->from($table)->where(array("date_modified < '$to' && date_modified > '$from'" => ''))->getBeans();
            //$data = $db->from($table)->where(array("'date_modified' < '$to' && 'date_modified' > '$from'" => ''))->getBeans();
            foreach ($data as $bean){
                if (!$bean->isInCore()){
                    if (!isset($r[$table])) $r[$table] = array();
                    $r[$table][$bean->id] = $bean->getRecordDescription(); // $this->getDataDescription($e_def['id'], $bean);
                }                
            }
        }

        return $r;
    }

    /*
    private function isInCore($bean){
        $r = false;
        $table = $bean->tableName();
        $filename = 'phpArrayDBcore/'.$table.'.php';
        if (file_exists($filename)){
            require($filename);
            if (is_array($$table)){
                foreach ($$table as $row){
                    if ($row['id'] == $bean->id){
                        $r = ($row['date_modified'] == $bean->date_modified);
                        return $r;
                    }
                }
            }
        }
        return $r;
    }
    */

    /*
    private function getDataDescription($entity_id, $bean){
        global $db;
        $field_max_size = 20;
        $description = '';
        // CACHE METADATA
        if (empty($this->metadata_cache[$entity_id])){
            $this->metadata_cache[$entity_id] = array();
            $fields = $db->from('_fields')->where(array('parent' => $entity_id, 'view2' => 1))->orderBy('order')->limit(3)->getArray();
            foreach ($fields as $f){
                $this->metadata_cache[$entity_id][] = $f['name'];
            }
        }
        // COLLECT DATA
        $description_data = array();
        foreach ($this->metadata_cache[$entity_id] as $fieldname){
            if (strlen($bean->$fieldname) > $field_max_size){
                $description_data[] = substr($bean->$fieldname, 0, 20).'...';
            } else {
                $description_data[] = $bean->$fieldname;
            }
        }
        // ADD DATE_MODIFIED:
        $description_data[] = '('.formatDate($bean->date_modified, 'd/m/Y H:i').')';
        $description = implode(' - ', $description_data);
        return $description;
    }
    */

    private function getFiles($from, $to, $path = '.'){
        $r = array();

        $r = array_merge(
            $this->getFilesFrom($from, $to, 'custom'),
            $this->getFilesFrom($from, $to, 'data'),
            $this->getFilesFrom($from, $to, 'include'),
            $this->getFilesFrom($from, $to, 'phpArrayDBcore'),
            $this->getFilesFrom($from, $to, 'templates')
        );

        return $r;
    }

    private function getFilesFrom($from, $to, $path){
        $r = array();
        $a = glob($path.'/*');
        foreach ($a as $filename){
            if (is_dir($filename)){
                $r = array_merge($r, $this->getFilesFrom($from, $to, $filename));
            } else {
                $mt = filemtime($filename);
                if ($from <= $mt && $mt <=$to){
                    $r[] = $filename;
                }
            }
        }
        return $r;
    }

}
	


