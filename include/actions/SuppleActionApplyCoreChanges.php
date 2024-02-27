<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionApplyCoreChanges extends SuppleAction {

	public $name = 'applycorechanges';
	public $domain = 'global'; // TODO: admin only
    private $metadata_cache = array();

    function __construct(){
	
		parent::__construct();
	
	}

	public function performAction($table, $filter, $data, &$recursion_history = array()){
        global $db;
        $refactor_tables = array();

        foreach ($data['record_id'] as $i => $record_id){
            $table = $data['table'][$i];
            $action = $data['action'][$i];
            
            echo "$table - $record_id - $action <br>";
            if ($action == 'to_core'){ 
                if ($db->isWritableCore($table)){

                    // READ ROW
                    $row = $db->from($table)->where(array('id' => $record_id))->getRow();
                    unset($row['_previousid']);
                    unset($row['_nextid']);
                    // WRITE IN CORE
                    $index = $db->isInCore($record_id, $table);
                    $db->writeRowCore($table, $index, $row);

                    // REMOVE FROM CUSTOM
                    $db->delete($table, array('id' => $record_id)); // DELETE OR REMOVE BEAN?

                    // MARK TABLE FOR REFACTOR:
                    $refactor_tables[$table] = true;

                }
            }
            if ($action == 'revert'){
                // REMOVE ROW
                $db->delete($table, array('id' => $record_id)); // DELETE OR REMOVE BEAN?
                // MARK TABLE FOR REFACTOR:
                $refactor_tables[$table] = true;
            }

        }
        // REFACTOR TABLES:
        foreach ($refactor_tables as $table => $b){
            if ($db->isWritableCore($table)){
                //$data = $db->readCoreTable($table);
                //$db->writeCoreTable($table, $data);
                $filename = 'phpArrayDBcore/'.$table.'.php';
                require($filename);
                file_put_contents($filename, '<?php $'.$table.' = '.var_export($$table, true).'; ?>');
            }
        }

        // REDIRECT
        SuppleApplication::redirect('admin.php?entity=41&view=5&custom_view_id=3');
        die();

    }

}