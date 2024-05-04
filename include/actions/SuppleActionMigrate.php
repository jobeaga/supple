<?php

require_once('include/util.php');
require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');
require_once('include/SuppleFile.php');

class SuppleActionMigrate extends SuppleAction {

	public $name = 'migrate';
	public $domain = 'global';
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function perform(){

		$r = array('error' => '');
	
		if (SuppleApplication::prepareValues('', $table, $post, $get)){
		
			set_time_limit(0);

			set_progress('Moving data', 0);

			$dbtable = $post['dbtable'];
			$dbname = $post['dbname'];
			$dbtype = $post['dbtype'];
			$dbhost = $post['dbhost'];
			$dbuser = $post['dbuser'];
			$dbpass = $post['dbpass'];
			
			$target = $this->db->getConnection($dbtype, $dbname, $dbuser, $dbpass, $dbhost);
			$source = $this->db->getConnectionFor($dbtable);
			
				
			// Row by row... we don't want to overload the server
			$result = $source->from($dbtable);
			$total = $result->getCount();
			
			$result = $source->from($dbtable);
			$count = 0;
			while ($row = $result->getRow()){
				unset($row['_previousid']);
				unset($row['_nextid']);
				$target->insert($dbtable, $row);
				
				if (round($count / $total * 100) < round(($count + 1) / $total * 100)){
					
					set_progress('Moving data', round(($count + 1) / $total * 100));
					
				}
				$count++;
			}

			// New mapping!
			$this->db->setMappings($post);
			
		}

		return $r;

	}

}


?>
