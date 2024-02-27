<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionDownloadPackage extends SuppleAction {

	public $name = 'downloadpackage';
	public $domain = 'global'; // TODO: admin only
    private $metadata_cache = array();

    function __construct(){
	
		parent::__construct();
	
	}

	public function performAction($table, $filter, $data, &$recursion_history = array()){
        global $db;

        // GENERATE LOCAL PACKAGE        
        if (!empty($data['package_name'])){
            $p = SuppleBean::getBean('_packages');
            $p->name = $data['package_name'];
            $p->save();

            foreach ($data as $key => $record_ids){
                if (substr($key, 0, 5) == 'data_'){
                    $table = substr($key, 5);
                    foreach ($record_ids as $record_id){
                        $pd = SuppleBean::getBean('_package_data');            
                        $pd->table = $table;
                        $pd->record_id = $record_id;
                        $pd->package_id = $p->id;
                        $pd->save();
                    }
                }
            }

            foreach ($data['files'] as $filename){
                $pf = SuppleBean::getBean('_package_files');            
                $pf->filename = $filename;
                $pf->package_id = $p->id;
                $pf->save();
            }            
            
        }

        if (!empty($filter['package_id'])){
            echo $filter['package_id'];
            $p = SuppleBean::getBean('_packages', $filter['package_id']);
            if (!empty($p->id)){
                // TODO: COMPLETE $data['data_'.$table]
                $a = $db->from('_package_data')->where(array('package_id' => $p->id))->getArray();
                foreach ($a as $row){
                    $table = $row['table'];
                    if (!isset($data['data_'.$table])) $data['data_'.$table] = array();
                    $data['data_'.$table][] = $row['record_id'];
                }

                // TODO: COMPLETE $data['files']
                $data['files'] = array();
                $b = $db->from('_package_files')->where(array('package_id' => $p->id))->getArray();
                foreach ($b as $row){
                    $data['files'][] = $row['filename'];
                }

                $data['download'] = 1;
                $data['package_name'] = $p->name;
            }
            
        }


        $p_date = date('Ymd_His');
        if (empty($data['package_name'])){
            $p_filename = 'package_'.$p_date.'.zip';
        } else {
            $p_filename = $this->generatePackageName($data['package_name']).'_'.$p_date.'.zip';
        }

        // DOWNLOAD PACKAGE
        if (!empty($data['download'])){
            /*
            Windows: As of PHP 8.2.0, php_zip.dll DLL must be enabled in php.ini. Previously, this extension was built-in.
            */
            // CREATE ZIP
            $file = tempnam("tmp", "zip");
            $zip = new ZipArchive();
            $res = $zip->open($file, ZipArchive::OVERWRITE);

            if ($res === TRUE){
                // COLLECT DATA
                $d = array();
                foreach ($data as $key => $record_ids){
                    if (substr($key, 0, 5) == 'data_'){
                        $table = substr($key, 5);
                        $d[$table] = array();
                        foreach ($record_ids as $record_id){
                            $row = $db->from($table)->where(array('id' => $record_id))->getRow();
                            unset($row['_previousid']);
                            unset($row['_nextid']);
                            $d[$table][$record_id] = $row;
                        }
                    }
                }
                $content = '<?php $data = '.var_export($d, true).'; ?>';

                // ADD DATA
                $zip->addFromString('data.php', $content);
                
                // ADD FILES
                foreach ($data['files'] as $filename){
                    $zip->addFile($filename);
                }
                // CLOSE ZIP
                $zip->close();
                
                // DOWNLOAD
                ob_clean();
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                // header('Content-Type: application/zip');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                header('Content-Disposition: attachment; filename="'.$p_filename.'"');
                readfile($file);
                unlink($file); 
                exit;

            } else {
                echo "Zip ERROR:" . $res ;
                // TODO: SEND EMAIL WARNING???
            }
        } else {
            if (!empty($p->id)){
                SuppleApplication::redirect('admin.php?entity=41&view=4&id='.$p->id);
            }
        }

    }

    function generatePackageName($package_name){ 
        return preg_replace('/[^a-zA-Z0-9]/', '_', $package_name);
    }

}