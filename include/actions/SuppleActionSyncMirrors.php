<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionSyncMirrors extends SuppleAction {

    public $name = 'syncmirrors';
	public $domain = 'global'; 
    public $needACL = false;
    private $client_id;

    public function performAction($table, $filter, $data, &$recursion_history = array()){

        set_time_limit(0);

        if ($filter['probe'] == 1){
            return 1;
        }

        $r = array();
        $sync_log = $this->getClientId().' - '.date('Y-m-d H:i:s')."\r\n\r\n";
        $this_is_mirror = $this->isMirror();

        if ($this_is_mirror){

            $mirror_def = $this->getThisMirror();
            if ($mirror_def['type'] == 'http'){
                $r = $this->syncFromHttp($filter, $data, $sync_log);
            } else if ($mirror_def['type'] == 'ftp'){
                $r = $this->syncFromFtp($sync_log);
            }

        } else {

            // SAVE LAST SYNC TIME:
            $this->saveLastSyncTime();
            // GENERATE tables MD5 
            $tables_md5 = $this->getTablesMd5();
            // for each mirror... 
            $mirrors = $this->getMirrors();
            foreach ($mirrors as $cid => $def){

                if ($def['type'] == 'http'){

                    $r = $this->syncToHttp($def, $sync_log);

                } else if ($def['type'] == 'ftp'){

                    $r = $this->syncToFtp($def, $sync_log);

                }
            }
        }

        file_put_contents('sync.log', $sync_log); // NO APPEND
        return $r;

    }

    function syncToHttp($def, &$sync_log){

        $r = array();

        // test using probe=1
        $p = $this->probeServer($def['host']);
        if ($p == 1) {
            // using a $key based on actual time and target host key
            $key = $this->generateKey($def['key']);
            // SEND step=1 TO MIRRORS
            $mirror_records = $this->curlSend($def['host'], 'step=1&key='.$key, $tables_md5);

            if (isset($mirror_records->tables)){
                // RECEIVE DIFFERENCES and COMPARE
                // GENERATE UPDATE REQUEST
                $ur = $this->generateUpdateRequest($mirror_records->tables);

                // SEND step=2 :
                $key = $this->generateKey($def['key']);
                $r = $this->curlSend($def['host'], 'step=2&key='.$key, $ur);
                
                // return $r;
            } else {
                $sync_log .= 'EMPTY RESPONSE: '.$def['host']."\r\n";
            }
        } else {
            $sync_log .= 'PROBE FAILED: '.$def['host']."\r\n";
        }
    
        return $r;
        
    }

    function syncFromHttp($filter, $data, &$sync_log){

        $r = array();

        if ($filter['step'] == 1) {
            
            // VALIDATE KEY
            if (isset($filter['key']) && $this->validateKey($filter['key'])){
                // GENERATE local tables MD5 
                $tables_md5 = $this->getTablesMd5();

                $diff = array();
                // RECEIVE COMPARE tables MD5
                foreach ($data as $table => $md5){
                    if (!isset($tables_md5[$table]) || $tables_md5[$table] != $md5){
                        $diff[$table] = $this->getSingleTableMd5($table);
                        // $diff[$table] = "$md5 VS ".$tables_md5[$table];
                    }
                }
                // RETURN DIFFERENCES FOR EACH TABLE
                $r = array('tables' => $diff);
                
            } else {
                $sync_log .= 'INVALID KEY: '.$filter['key']."\r\n";
            }

        } else if ($filter['step'] == 2) {

            // VALIDATE KEY
            if (isset($filter['key']) && $this->validateKey($filter['key'])){
                // RECEIVE UPDATES
                // APPLY INSERT, UPDATE or DELETE
                $this->applyIUD($data);

                // UPDATE metadata_cache
                SuppleApplication::getcache()->updateMetadata();

            } else {
                $sync_log .= 'INVALID KEY: '.$filter['key']."\r\n";
            }
        }

        return $r;
    }

    function syncToFtp($def, &$sync_log){

        $r = array();

        // SEND VIA FTP
        if (isset($def['port'])){
            $conn_id = ftp_connect($def['host'], $def['port']); 
        } else {
            $conn_id = ftp_connect($def['host']); 
        }        

        // start session with user and password
        $login_result = ftp_login($conn_id, $def['user'], $def['pass']); 

        // check connection
        if ((!$conn_id) || (!$login_result)) {  
            $sync_log .= "CONNECTION FAILED \r\n";
            $sync_log .= "Trying to connect to {$def['host']} with user {$def['user']} \r\n"; 
            return $r; // TODO: return error code
        } else {
            $sync_log .=  "Connection to {$def['host']} successful, with user {$def['user']} \r\n";
        }

        // FOREACH FILE
        $ps = $this->getSyncFolders();
        $target_folders = array();
        foreach ($ps as $p){
            $fs = glob($p);
            foreach ($fs as $source_file){
                if (!is_dir($source_file)){
                    $destination_file = $def['folder'].$source_file;
                    $slash = strrpos($destination_file, '/');
                    $destination_folder = substr($destination_file, 0, $slash + 1);
                    $destination_filename = substr($destination_file, $slash+1);
                    $sync = true;

                    // get target folder contents
                    if (!isset($target_folders[$destination_folder])){
                        $folder_contents = ftp_mlsd($conn_id, $destination_folder);
                        $target_folders[$destination_folder] = $folder_contents;
                    } else {
                        $folder_contents = $target_folders[$destination_folder];
                    }

                    // find modification time:
                    $local_mdtm = date('YmdHis',filemtime($source_file));
                    $local_size = filesize($source_file);
                    foreach ($folder_contents as $ff){
                        if ($ff['type'] == 'file' && $ff['name'] == $destination_filename){
                            // $sync = $local_mdtm > $ff['modify']; // WARNING! modify is human readable format, format may change
                            // COMPARE SIZES:
                            $sync = (!isset($ff['size']) || $local_size != $ff['size']);
                        }
                    }
                    
                    if ($sync){
                        // upload files
                        $upload = ftp_put($conn_id, $destination_file, $source_file, FTP_BINARY);

                        // check upload status
                        if (!$upload) {  
                            $sync_log .= "Upload failed -- $destination_file \n\r";
                        } else {
                            $sync_log .= "Upload success -- $destination_file \n\r";
                        }
                    } else {
                        $sync_log .= "File not changed -- $destination_file \n\r";
                    }
                }
                file_put_contents('sync.log', $sync_log); // NO APPEND
            }
        }

        // close ftp connection
        ftp_close($conn_id);
        $sync_log .= "Connection closed. \n\r";

        return $r;

    }

    function syncFromFtp(&$sync_log){

        // INFO IS ALREADY HERE
        // UPDATE metadata_cache
        SuppleApplication::getcache()->updateMetadata();

    }

    function getSyncFolders(){
        return array(
            'phpArrayDB/default/*.*',
            'phpArrayDB/metadata/*.*',
            'sqliteDB/*.*',
        );
    }

    function probeServer($host){
        // return $this->curlSend($host, 'probe=1');
        // return curlPost($host.'probe.txt', array()); // include/util.php
        $r = curlPost($host.'admin.php?action=syncmirrors&probe=1', array(), false);
        return ($r != '');
    }

    function curlSend($host, $parameters, $data = array()){
        $url = $host.'admin.php?action=syncmirrors';
        if ($parameters != '') $url .= '&'.$parameters;
        return curlPost($url, $data, true); // include/util.php
    }

    function applyIUD($data){
        global $db;
        if (isset($data['insert'])){
            foreach ($data['insert'] as $table => $records){
                foreach ($records as $id => $row){
                    $db->insert($table, $row);
                }
            }
        }

        if (isset($data['update'])){
            foreach ($data['update'] as $table => $records){
                foreach ($records as $id => $row){
                    if (!empty($id)){
                        $db->update($table, $row, array('id' => $id));
                    }
                }
            }
        }

        if (isset($data['delete'])){
            foreach ($data['delete'] as $table => $records){
                foreach ($records as $id => $one){
                    if (!empty($id)){
                        $db->delete($table, array('id' => $id));
                    }
                }
            }
        }
    }

    function getTablesMd5(){
        global $db;
        $r = array();
        // only getting info for declarated tables
        $entities = $db->from('_entities')->getArray();
        foreach ($entities as $e){
            // note: only for non-core data
            $table_name = $e['table'];
            if (substr($table_name, 0, 2) != '__'){ // avoid meta_meta_tables
                $conn = $db->from($table_name);
                $conn->useCoreData = false; // little hack for phpArrayDB
                $data = $conn->getArray();
                // generate hash: record_count + max_date_modified
                $max_date_modified = '';
                $have_id_field = true;
                foreach ($data as $row){
                    if (!isset($row['id'])) {
                        $have_id_field = false;
                        break;
                    }
                    if (isset($row['date_modified']) && $row['date_modified'] > $max_date_modified){
                        $max_date_modified = $row['date_modified'];
                    }
                }
                if ($have_id_field){
                    $md5 = md5(count($data).'_'.$max_date_modified); 
                    $r[$table_name] = $md5; 
                }
            }
        }
        return $r;
    }

    function getSingleTableMd5($table_name){
        global $db;
        $r = array();
        $conn = $db->from($table_name);
        $conn->useCoreData = false; // little hack for phpArrayDB
        $data = $conn->getArray();
        foreach ($data as $row){
            $id = $row['id'];
            $r[$id] = $this->getRecordMd5($table_name, $id, $row);
        }
        return $r;
    }

    function getRecordMd5($table_name, $id, $row = array()){
        global $db;
        $r = '';
        if ($id == '') return $r;
        if (empty($row)){
            $conn = $db->from($table_name);
            $conn->useCoreData = false; // little hack for phpArrayDB
            $row = $conn->where(array('id' => $id))->getRow();
        }
        $row_len = 0;
        foreach ($row as $field => $value){
            $row_len += strlen($value);
        }
        $r = md5(count($row).'_'.$row_len);
        return $r;
    }

    function generateUpdateRequest($mirror_records){
        global $db;
        $r = array();
        $ld = array();
        foreach ($mirror_records as $table => $remote_records){
            $conn = $db->from($table);
            $conn->useCoreData = false; // little hack for phpArrayDB
            $local_data = $conn->getArray();
            foreach ($local_data as $row){
                $id = $row['id'];
                $ld[$id] = 1;
                if (!isset($remote_records->$id)){
                    unset($row['_nextid']);
                    unset($row['_previousid']);
                    // INSERT:
                    $r['insert'][$table][$id] = $row;
                } else {
                    $local_md5 = $this->getRecordMd5($table, $id, $row);
                    if ($local_md5 != $remote_records->$id){
                        unset($row['_nextid']);
                        unset($row['_previousid']);
                        // UPDATE
                        $r['update'][$table][$id] = $row;
                    }
                }
            }
            // DELETES:
            foreach ($remote_records as $id => $md5){
                if (!isset($ld[$id])){
                    $r['delete'][$table][$id] = 1;
                }
            }
        }
        return $r;

    }


    function getMirrors(){
        $r = array();
        if (file_exists('data/mirror.php')){
            require('data/mirror.php');
            foreach ($mirror_servers as $i => $m){
                $r[$i] = $m;
            }
        }
        unset($mirror_servers);
        if (file_exists('custom/mirror.php')){
            require('custom/mirror.php');
            foreach ($mirror_servers as $i => $m){
                $r[$i] = $m;
            }
        }
        return $r;
    }

    function getClientId(){
        if (empty($this->client_id)){
            if (function_exists('shell_exec')){
                $r = shell_exec('hostname');
                $this->client_id = trim($r);
            } else {
                $this->client_id = $_SERVER['HTTP_HOST'];
            }            
        }
        return $this->client_id;
    }

    function isMirror(){
        $cid = $this->getClientId();
        $ms = $this->getMirrors();
        return isset($ms[$cid]);
    }

    function getThisMirror(){
        $cid = $this->getClientId();
        $ms = $this->getMirrors();
        return $ms[$cid];
    }

    function validateKey($key){
        $def = $this->getThisMirror();
        $k = $this->generateKey($def['key']);
        return ($key == $k);
    }

    function generateKey($key){
        return md5($key.gmdate('Y-m-d H:i'));
    }

    function saveLastSyncTime(){
        $last_sync_file = 'cache/suppleCache/last_sync.txt';
        if (file_exists($last_sync_file)){
            unlink($last_sync_file);
        }
        file_put_contents($last_sync_file, time());
    }

    function getLastSyncTime(){
        $last_sync_file = 'cache/suppleCache/last_sync.txt';
        if (file_exists($last_sync_file)){
            return file_get_contents($last_sync_file);
        } else {
            return 0;
        }
    }

    function checkAndPerformIfNeeded(){
        // IF ... $this->perform(); ???
        $max_time = 30 * 60; // 30 minutes
        $lst = $this->getLastSyncTime();
        if (time() - $lst > $max_time){
            $this->perform();
        }
    }

}