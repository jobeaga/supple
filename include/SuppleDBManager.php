<?php

require_once('include/util.php');
require_once('include/SuppleApplication.php');

class SuppleDBManager {

	var $connections;
	public $mappings;
	var $default_baseType = 'PhpArrayConnection';      //SuppleApplication::getconfig()->getValue('default_baseType');
	var $default_baseName = 'default';                 //SuppleApplication::getconfig()->getValue('default_baseName');
	var $default_baseUser = '';                        //SuppleApplication::getconfig()->getValue('default_baseUser');
	var $default_basePassword = '';                    //SuppleApplication::getconfig()->getValue('default_basePassword');
	var $default_baseHost = 'localhost';               //SuppleApplication::getconfig()->getValue('default_baseHost');

	var $entity_cache = array();
	var $entity_cache_rev = array();
	var $rel_cache = array();

	function __construct(){
		// global $suppleConfig;
		$conf = SuppleApplication::getconfig();
		$this->default_baseType = $conf->getValue('default_baseType');
		$this->default_baseName = $conf->getValue('default_baseName');
		$this->default_baseUser = $conf->getValue('default_baseUser');
		$this->default_basePassword = $conf->getValue('default_basePassword');
		$this->default_baseHost = $conf->getValue('default_baseHost');

		// Loads the mappings
		$this->loadMappings();

		// Initialize the connections
		$this->connections = array();

		// Connect with the default connection
		$this->connectWith($this->default_baseType, $this->default_baseName, $this->default_baseUser, $this->default_basePassword, $this->default_baseHost);

	}

	function loadMappings(){
		if (file_exists("data/mappings.php")){
			require("data/mappings.php");
		} else {
			$mappings = array();
		}
		$this->mappings = $mappings;
		// CUSTOM:
		unset($mappings);
		if (file_exists("custom/mappings.php")){
			require("custom/mappings.php");
			foreach ($mappings as $dbtype => $dbnames){
				if (!isset($this->mappings[$dbtype])) $this->mappings[$dbtype] = array();
				foreach ($dbnames as $dbname => $dbinfo){
					if (!isset($this->mappings[$dbtype][$dbname])) $this->mappings[$dbtype][$dbname] = array();
					// override user, password, host
					if (isset($dbinfo['user'])) $this->mappings[$dbtype][$dbname]['user'] = $dbinfo['user'];
					if (isset($dbinfo['password'])) $this->mappings[$dbtype][$dbname]['password'] = $dbinfo['password'];
					if (isset($dbinfo['host'])) $this->mappings[$dbtype][$dbname]['host'] = $dbinfo['host'];

					// add tables
					if (!isset($this->mappings[$dbtype][$dbname]['tables'])){
						$this->mappings[$dbtype][$dbname]['tables'] = array();
					}
					foreach ($dbinfo['tables'] as $tablename){
						if (!in_array($tablename, $this->mappings[$dbtype][$dbname]['tables'])){
							$this->mappings[$dbtype][$dbname]['tables'][] = $tablename;
						}
					}
				}
			}
		}
	}

	function saveMappings(){
		// writeArray("data/mappings.php", $this->mappings);
		
		$file_name = "data/mappings.php";
		$custom_file_name = "custom/mappings.php";

		require($file_name); // $mappings (std)
		$custom_mappings = array();
		foreach ($this->mappings as $dbtype => $dbnames){
			if (!isset($mappings[$dbtype])) $custom_mappings[$dbtype] = array();

			foreach ($dbnames as $dbname => $dbinfo){
				if (!isset($mappings[$dbtype][$dbname])) $custom_mappings[$dbtype][$dbname] = array();
				// user, password, host
				$f = array('user', 'password', 'host');
				foreach ($f as $g){
					if (!isset($mappings[$dbtype][$dbname][$g]) && isset($this->mappings[$dbtype][$dbname][$g])) $custom_mappings[$dbtype][$dbname][$g] = $dbinfo[$g];
				}
				// tables
				$t = array();
				foreach ($dbinfo['tables'] as $tablename){
					if (!isset($mappings[$dbtype][$dbname]['tables'])){
						$mappings[$dbtype][$dbname]['tables'] = array();
					}
					if (!in_array($tablename, $mappings[$dbtype][$dbname]['tables'])){
						$t[] = $tablename;
					}
				}
				if (!empty($t)){
					$custom_mappings[$dbtype][$dbname]['tables'] = $t;
				}
			}
		}

		$content = '<?php $mappings = '.var_export($custom_mappings, true).'; ?>';
		// echo "<pre>"; var_export($custom_mappings); echo "</pre>"; die();
		file_put_contents($custom_file_name, $content);
	}
	
	function isSetMapping($table){
		
		foreach ($this->mappings as $baseType => $baset){
			foreach ($baset as $baseName => $basen){
				foreach ($basen['tables'] as $key => $existingTable){
					if ($table == $existingTable){
						
						return true;
						
					}
				}
			}
		}
		
		return false;
		
	}

	function setMapping($table, $baseType, $baseName, $user = '', $password = '', $host = 'localhost'){

		//if (isSetMapping($table)){
			$this->delMapping($table); // you cannot map a table twice.
			// echo "Error: Table is already mapped."
		//}
		
		// CReo que tuve problemas con esto, por eso lo comenté
		// $baseType = strtolower($baseType);

		$appendArray = array();
		
		// If database exists within mappings, add the table, otherwise add database, with user and password
		if (isset($this->mappings[$baseType][$baseName])){
			if ($user || (!$this->mappings[$baseType][$baseName]['user'])){
				$this->mappings[$baseType][$baseName]['user'] = $user;
				$appendArray[$baseType][$baseName]['user'] = $user;
			}
			if ($password || (!$this->mappings[$baseType][$baseName]['password'])){
				$this->mappings[$baseType][$baseName]['password'] = $password;
				$appendArray[$baseType][$baseName]['password'] = $password;
			}
			if ($host != 'localhost'){
				$this->mappings[$baseType][$baseName]['host'] = $host;
				$appendArray[$baseType][$baseName]['host'] = $host;
			}

		} else {
			$this->mappings[$baseType][$baseName]['user'] = $user;
			$this->mappings[$baseType][$baseName]['password'] = $password;
			$this->mappings[$baseType][$baseName]['host'] = $host;
			$this->mappings[$baseType][$baseName]['tables'] = array();

			$appendArray[$baseType][$baseName]['user'] = $user;
			$appendArray[$baseType][$baseName]['password'] = $password;
			$appendArray[$baseType][$baseName]['host'] = $host;
		}

		// Append the new mapping (it's always new!)
		if ($table){
			$this->mappings[$baseType][$baseName]['tables'][] = $table;
			$appendArray[$baseType][$baseName]['tables'] = array($table);
		}

		// Write!
		// appendArray('data/mappings.php', $appendArray);
		// Write the entire file!!!
		// writeArray("data/mappings.php", $this->mappings); 
		$this->saveMappings();

	}
	
	function getMappings(){
		
		$maps = array();
		
		foreach ($this->mappings as $baseType => $baset){
			foreach ($baset as $baseName => $basen){
				foreach ($basen['tables'] as $key => $existingTable){
					$m['dbtype'] = $baseType;
					$m['dbname'] = $baseName;
					$m['dbuser'] = $basen['user'];
					$m['dbhost'] = $basen['host'];
					$m['dbpass'] = $basen['password'];
					$m['dbtable'] = $existingTable;
					$maps[] = $m;
				}
			}
		}
		
		return $maps;
		
	}
	
	function setMappings($data){
		
		if (isset($data['dbtable']) && is_array($data['dbtable'])){
			
			foreach($data['dbtable'] as $index => $table){
				
				// Collect data
				$d = array();
				$d['dbtable'] = $table;
				foreach ($data as $name => $values){
					$d[$name] = (isset($values[$index])) ? $values[$index] : '';
				}
				// setMapping
				$this->setMappings($d);
				
			}
			
		} elseif(isset($data['dbtable']) && $data['dbtable']) {
			
			$table = $data['dbtable'];
			$baseType = (isset($data['dbtype'])) ? $data['dbtype'] : '';
			$baseName = (isset($data['dbname'])) ? $data['dbname'] : '';
			$user = (isset($data['dbuser'])) ? $data['dbuser'] : '';
			$password = (isset($data['dbpass'])) ? $data['dbpass'] : '';
			$host = (isset($data['dbhost'])) ? $data['dbhost'] : '';
			
			if ($baseType) $this->setMapping($table, $baseType, $baseName, $user, $password, $host);			
			else $this->delMapping($table);
			
		} else {
			
			// Invalid data!
			
		}
		
	}

	function delMapping($table){

		foreach ($this->mappings as $baseType => $baset){
			foreach ($baset as $baseName => $basen){
				foreach ($basen['tables'] as $key => $existingTable){
					if ($table == $existingTable){
						
						// Erase...
						unset($this->mappings[$baseType][$baseName]['tables'][$key]);
						
						// And rewrite the entire array (if something was deleted)
						$this->saveMappings();
						
						return true;
					}
				}
			}
		}
		
		return false;
	}

	function connectWith($baseType, $baseName, $user = '', $password = '', $host = ''){

		// Connect using the provided info
		if (file_exists('include/dataBases/'.$baseType.'.php')){
			require_once('include/dataBases/'.$baseType.'.php');
			$connection = new $baseType($baseName, $user, $password, $host);
			// Save the connection
			$this->connections[$baseType][$baseName] = $connection;
			
		} else {
			
			// Error: Unavaliable connection
			echo "Error: Unavaliable connection for $baseType ($baseName)";
			
		}

	}

	function getConnectionTypes(){
		$classFiles = folderContent('include/dataBases', '*.php');
		foreach ($classFiles as $key => $filename){
			$classFiles[$key] = getFileName($filename); // str_replace('.', '_', getFileName($filename));
		}
    // print_r($classFiles); die();
		return $classFiles;
	}

	function getConnection($baseType, $baseName, $user, $password, $host){
		
		// If we're connected, then return the connection, else connectWith 
		if (!isset($this->connections[$baseType][$baseName])){
			$this->connectWith($baseType, $baseName, $user, $password, $host);
		}
		return $this->connections[$baseType][$baseName];
	}

	function closeConnection($baseType, $baseName){
		if (isset($this->connections[$baseType][$baseName])){
			$this->connections[$baseType][$baseName]->close();
			unset($this->connections[$baseType][$baseName]);
		}
	}

	function closeAllConnections(){
		// TODO

	}

	function getConnectionFor($table){

		if (substr($table, 0, 2) == '__'){
			return $this->getConnection('PhpArrayConnection', 'default', '', '', 'localhost');
		}

		foreach ($this->mappings as $baseType => $baset){
			foreach ($baset as $baseName => $basen){
				foreach ($basen['tables'] as $key => $existingTable){
					if ($table == $existingTable){
						
						$user = $this->mappings[$baseType][$baseName]['user'];
						$password = $this->mappings[$baseType][$baseName]['password'];
						$host = $this->mappings[$baseType][$baseName]['host'];
						
						// echo "MAPEO ENCONTRADO: $baseType - $baseName <br>";
						
						return $this->getConnection($baseType, $baseName, $user, $password, $host);

					}
				}
			}
		}
		
		// Return default connection
		return $this->getConnection($this->default_baseType, $this->default_baseName, $this->default_baseUser, $this->default_basePassword, $this->default_baseHost);

	}
	
	function getTables(){
		
		// Get tables from default connection
		$c = $this->getConnection($this->default_baseType, $this->default_baseName, $this->default_baseUser, $this->default_basePassword, $this->default_baseHost);
		$tables = $c->getTables();

		foreach ($tables as $i => $table){
			$tables[$i]['basename'] = $this->default_baseName;
		}
		
		// And append with the rest of the bases
		foreach ($this->mappings as $baseType => $baset){
			foreach ($baset as $baseName => $basen){
				foreach ($basen['tables'] as $table){
					$index = count($tables);
					foreach ($tables as $i => $table_in_array){
						if ($table_in_array['name'] == $table) $index = $i;
					}
					$tables[$index]['name'] = $table;
					$tables[$index]['basename'] = $baseName;
				}
			}
		}

		foreach ($tables as $i => $table){
			$tables[$i]['id'] = $i + 1; // is not fixed, but is needed.
		}
		
		return $tables;
		
	}
	
	
	function getFields($table = ''){
		// Esto puede llegar a ser muy drástico, ¿podríamos tener un filtro por tabla?
		
		// Get fields from default connection
		$c = $this->getConnection($this->default_baseType, $this->default_baseName, $this->default_baseUser, $this->default_basePassword, $this->default_baseHost);
		$fields = $c->getFields($table);
		
		// And append with the rest of the bases
		foreach ($this->mappings as $baseType => $baset){
			foreach ($baset as $baseName => $basen){
				foreach ($basen['tables'] as $key => $existingTable){
					if (($baseType != $this->default_baseType || $baseName != $this->default_baseName) && (empty($table) || $table == $existingTable)){
							
						$user = $this->mappings[$baseType][$baseName]['user'];
						$password = $this->mappings[$baseType][$baseName]['password'];
						$host = $this->mappings[$baseType][$baseName]['host'];
						
						$c = $this->getConnection($baseType, $baseName, $user, $password, $host);
						// append
						$fields = array_merge($fields, $c->getFields($existingTable));
					
					}
				}
			}
		}
		foreach ($fields as $i => $field){
			$fields[$i]['id'] = $i + 1; // is not fixed, but is needed.
		}
		// return
		return $fields;
		
	}

	function insert($table, $values){
		$con = $this->getConnectionFor($table);
		//$values = unsafetextarea($values); // no hace falta, ya se hace en js
		return $con->insert($table, $values);
	}

	function update($table, $values, $filter){
		$con = $this->getConnectionFor($table);
		//$values = unsafetextarea($values); // no hace falta, ya se hace en js
		return $con->update($table, $values, $filter);
	}

	function delete($table, $filter){
		$con = $this->getConnectionFor($table);
		return $con->delete($table, $filter);
	}

	// Query functions...

	function from($table, $parent_table = '', $parent_id = ''){
		
		// echo "$table --- $parent_table --- $parent_id <br>";
		
		if ($parent_table && $parent_id){
			list($rel_table, $rel_where) = $this->getRelTable($table, $parent_table, $parent_id);
		}

		if (!empty($rel_table)){
			$con = $this->getConnectionFor($rel_table);
			return $con->from($rel_table)->where($rel_where);
		} else {
			$con = $this->getConnectionFor($table);
			return $con->from($table);
		}
	}

	function getRelTable($table, $parent_table, $parent_id, $is_relationship = false){
		// Check metada for relationships
		$final_table = '';
		$rel_where = array();

		$this->generateEntityRelCache();

		// IS IT AN ENTITY TABLE???
		if ((!isset($this->entity_cache[$table]) && isset($this->rel_cache[$table][$parent_table])) || $is_relationship){
			$final_table = $this->rel_cache[$table][$parent_table]['final_table'];
			$rel_column = $this->rel_cache[$table][$parent_table]['rel_column'];
			$final_field = $this->rel_cache[$table][$parent_table]['final_field'];
			$final_column = $this->rel_cache[$table][$parent_table]['final_column'];
			$rel_ids = $this->from($table)->where(array($rel_column => $parent_id))->getArray();
			$rel_where[$final_field] = array();
			foreach ($rel_ids as $rel_row){
				$rel_where[$final_field][] = $rel_row[$final_column];
			}

			/*
			// DEBUG
			if ($table == '_tab_group_entities'){
				echo "<br> parent_id: $parent_id";
				echo "<br> rel_cache: ";
				print_r($this->rel_cache[$table][$parent_table]);
				echo "<br> rel_where: ";
				print_r($rel_where);
				// die();
			}
			*/
		}

		return array($final_table, $rel_where);

	}

	function generateEntityRelCache(){
		$ent_rev = array();

		if (empty($this->entity_cache)){
			$ens = $this->from('_entities')->getArray();
			foreach ($ens as $en){
				$this->entity_cache[$en['table']] = $en['id'];
				$this->entity_cache_rev[$en['id']] = $en['table'];
			}
		}
		if (empty($this->rel_cache)){
			$rels = $this->from('_relationships')->getArray();
			foreach ($rels as $rel){
				$rel_table = $rel['rel_table'];
				$table_a = $this->entity_cache_rev[$rel['id_a']];
				$table_b = $this->entity_cache_rev[$rel['id_b']];
				if ($rel['type'] == 1){
					$rel['relationship_field_a'] = 'id_a';
					$rel['relationship_field_b'] = 'id_b';
				}
				
				$this->rel_cache[$rel_table][$table_a] = array(
					'final_table' => $table_b,
					'initial_field' => $rel['entity_a_field'],
					'rel_column' => $rel['relationship_field_a'],
					'final_column' => $rel['relationship_field_b'],
					'final_field' => $rel['entity_b_field'],
					'trigger_delete' => $rel['trigger_delete'],
				);

				$this->rel_cache[$rel_table][$table_b] = array(
					'final_table' => $table_a,
					'initial_field' => $rel['entity_b_field'],
					'rel_column' => $rel['relationship_field_b'],
					'final_column' => $rel['relationship_field_a'],
					'final_field' => $rel['entity_a_field'],
					'trigger_delete' => 0,
				);
			}
		}

	}
 
	
	function evaluateCondition($where, $data){
		
		// Everyone can answer this...
		$c = $this->getConnectionFor("");
		return $c->evaluateCondition($where, $data);
		
	}

	function persistTables(){

		// TODO: Por cada mapeo, guarda las tablas completas en archivos de texto en la base default.
		foreach ($this->mappings as $baseType => $baset){
			foreach ($baset as $baseName => $basen){
				foreach ($basen['tables'] as $key => $existingTable){
					$this->persistTable($existingTable);
				}
			}
		}

	}

	function persistTable($table){

		$source = getConnectionFor($table);
		$target = getConnectionFor('');

		$result = $source->from($table);
		$count = 0;
			while ($row = $result->getRow()){
				unset($row['_previousid']);
				unset($row['_nextid']);
				$target->insert($table, $row);
				$count++;
			}

		// TODO: Prune table

		return $count;

	}

	// HANDLE CORE TABLES

	function coreTables(){
		$r = array();
		$tables = glob('phpArrayDBcore/*.php');
		foreach ($tables as $t){
			$i = strrpos($t, '/');
			$j = strrpos($t, '.');
			$r[] =  substr($t, $i + 1, $j - $i -1);
		}
		return $r;
	}

	function isInCore($record_id, $table, $date_modified = ''){
		$r = false;
		$index = false;
		$filename = 'phpArrayDBcore/'.$table.'.php';
		if (file_exists($filename)){
			require($filename);
			if (is_array($$table)){
				foreach ($$table as $i => $row){
					if ($row['id'] == $record_id && strlen($row['id']) == strlen($record_id)){
						$r = ($row['date_modified'] == $date_modified) || ($date_modified == '');
						if ($r) $index = $i;
						// $GLOBALS['log']->fatal("$table - $record_id - {$row['id']}");
						//return $r;
					}
				}
			}
		}
		return $index;
	}

	function isWritableCore($table){
		return is_writable('phpArrayDBcore/'.$table.'.php');
	}

	function writeRowCore($table, $index, $row){
		$filename = 'phpArrayDBcore/'.$table.'.php';
		if ($index === false){
			// ADD
			appendArray($filename, array($row), true);
		} else { 
			// MODIFY
			appendArray($filename, array($index => $row));			
		}
		
		// file_put_contents($filename, $content, FILE_APPEND);
	}

	function readCoreTable($table){
		$filename = 'phpArrayDBcore/'.$table.'.php';
		return readArray($filename);
	}

	function writeCoreTable($table, $data){
		$filename = 'phpArrayDBcore/'.$table.'.php';
		return writeArray($filename, $data);
	}

}

