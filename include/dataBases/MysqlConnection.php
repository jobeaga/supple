<?php

require_once('include/SQLConnection.php');
require_once('include/util.php');

class MysqlConnection extends SQLConnection {
	
	var $dataTypes = array(
			'default' => 'VARCHAR(50)',
			'id' => 'VARCHAR(36)',
			'numeric' => 'INT',
			'decimal' => 'DOUBLE',
			'text' => 'TEXT',
		);
	var $link;
	var $opConversions = array();
	var $simpleConversions = array(
		'==' 			=> ' = ',
		//'!=' 			=> ' != ',
		'&&' 			=> ' AND ',
		'||' 			=> ' OR ',
	);
	var $dataBaseName;
	
	static function test(){
		return function_exists('mysqli_connect');
	}

	function __construct($dataBaseName = 'default', $user = '', $password = '', $host = 'localhost'){

		// $this->link = mysql_connect($host, $user, $password);
		$this->link = mysqli_connect($host, $user, $password, $dataBaseName);
		if (!$this->link) {
    		die('Could not connect: ' . mysqli_connect_errno()  . " - ". mysqli_connect_error());
		}
		
		$this->dataBaseName = $dataBaseName;

	}
	
	// PRIVATE
	function getTables($for_query = true){
	
		if (empty($this->cache['tables'])){
			$tables = array();
			$query = "SHOW TABLES";
			$result = $this->execute($query);
			$data = array();
			while ($row = mysqli_fetch_row($result)){
				$data[] = $row;
			}
			$this->cache['tables'] = $data;
		} else {
			$data = $this->cache['tables'];
		}

		foreach ($data as $row){
			if ($for_query) $tables[]['name'] = $row[0];
			else $tables[] = $row[0];
		}

		return $tables;
	}

	function getFields($table = '', $for_query = true){
	
		$tables = $this->getTables();
		if ($table != '' && $this->tableExists($table)){
			$tables = array(0 => array('name' => $table));
		} elseif ($table != '') {
			$tables = array();
		}		
		
		$fields = array();

		foreach ($tables as $index => $t){
			$table_name = $t['name'];
			if (isset($this->cache['getFields'][$table_name])){
				$data = $this->cache['getFields'][$table_name];
			} else {
				$data = array();
				$query = "SHOW COLUMNS FROM ".$t['name'];
				$result = $this->execute($query);
				while ($row = $this->fetch($result)){
					$data[] = $row;
				}
				$this->cache['getFields'][$table_name] = $data;
			}

			foreach ($data as $row){
				if ($for_query){
					$fields[] = array(
						'name' => $row['Field'],
						'table' => $t['name'],
						'type' => $row['Type'],
					);
					/*
					$fields[$row['Field']]['name'] = $row['Field'];
					$fields[$row['Field']]['table'] = $t['name'];
					$fields[$row['Field']]['type'] = $row['Type'];
					*/
				} else {
					$fields[] = $row['Field'];
				}
			}
		}
		
		return $fields;
	}

	function createTableIfDontExists($table){
		
		if (!$this->tableExists($table)){
			$query = "CREATE TABLE $table (id ".$this->dataTypes['id'].",
			CONSTRAINT id_pk PRIMARY KEY (id))";
			$this->execute($query);
			$this->resetcache();
			// Add id index
			// $this->addIndex($table, 'idx_id', 'id');
			// TODO: Add index to relationships???
		}

	}

	function fieldSize($field, $table = ''){
		// CACHE
		if (isset($this->cache['fieldSize'][$table.'_'.$field])){
			$size = $this->cache['fieldSize'][$table.'_'.$field];
		} else {

			$type = '';
			$size = 0;
			if (empty($table)){
				$type = $field;
			} else {
				$query = "SHOW COLUMNS FROM $table WHERE FIELD = '$field'";
				$result = $this->execute($query);
				if ($row = $this->fetch($result)){
					$type = $row['Type'];
				}
			}
			// REGEXP
			preg_match('/\(([0-9]*)\)/', $type, $r);
			if (isset($r[1])) $size = (int)$r[1];

			$this->cache['fieldSize'][$table.'_'.$field] = $size;

		}
		
		return $size;
	}

	// strictly private!!!
	function execute($query){
		
		// $db_selected = mysql_select_db($this->dataBaseName, $this->link);
		if (!$this->link) { 
			// die ("Can't use $this->dataBaseName !!: " . mysql_error()); 
			// Creamos la base de datos
			if (empty($this->dataBaseName)){
				die("Empty Database Name - ");
			} else {
				$q = "CREATE DATABASE $this->dataBaseName";
				mysqli_query($this->link, $q);
				// $db_selected = mysql_select_db($this->dataBaseName, $this->link);
			} 
		} 
		// $GLOBALS['log']->fatal($query);
		$result = mysqli_query($this->link, $query);
		if (!$result) {
		    die('Invalid query: ' . $query . '<br> Mysqli Error: ' . mysqli_connect_error());
		}
		
		return $result;
		
	}

	function fetch($result) {
		return mysqli_fetch_assoc($result);
	}

	function isNumericColumn($table, $field){
		$result = $this->execute("SELECT COUNT(*) c FROM `$table` WHERE `$field` REGEXP '^[0-9]+$';");
		if ($row = $this->fetch($result)){
			$c1 = $row['c'];
		}
		$result = $this->execute("SELECT COUNT(*) c FROM `$table` WHERE `$field` != '';");
		if ($row = $this->fetch($result)){
			$c2 = $row['c'];
		}
		return $c1 == $c2;
	}

	// PUBLIC
	function addIndex($table, $idx_name, $columns){
		// $table exists?
		$t = $this->getTables(false);
		if (in_array($table, $t)){
			// $idx_name exists?
			$i = $this->getIndexes($table);
			if (!in_array($idx_name, $i)){
				// is each $column a valid column for this table?
				// we'll never know.

				// $columns has at least one column?
				if (!empty($columns)){
					// $columns is string or array?
					if (is_array($columns)){
						$cols = '`'.implode($columns, '`, `').'`';
					} else {
						$cols = $columns;
					}
					// CREATE INDEX!!!
					$this->execute("ALTER TABLE `$table` ADD INDEX `$idx_name` ($cols)");
				}
			}
		}
		return $this;
	}

	function getIndexes($table){
		$result = array();
		$r = $this->execute("SHOW INDEX FROM $table");
		while ($row = $this->fetch($r)){
			$result[] = $row['Key_name'];
		}
		return $result;
	}

	
	function getNewId($table){
		
		$use_uuid = SuppleApplication::getconfig()->getValue('universally_unique_identifier');
		if ($use_uuid) {
			return uuid();
		} else {
			// SOMETHING LIKE "SELECT MAX(id) FROM $table" + 1
			$this->createTableIfDontExists($table);
			$this->flexField($table, 'id', '1');
			// $query = "SELECT MAX(id) maxid FROM `$table`";
			$query = "SELECT MAX(CAST(id AS INT)) maxid FROM `$table`";
			$result = $this->execute($query);
			if ($row = $this->fetch($result)){
				return $row['maxid'] + 1;
			} else {
				return 1;
			}
		}
	}

	// Query functions...
	function from($table){
		//$this->createTableIfDontExists($table);
		return (new MysqlResultset($this, $table));
	}
	

	
	
}

class MysqlResultset extends SQLResultset {
	
	var $result;
	
	function __construct($connection, $table){
		parent::__construct($connection, $table);
		$this->result = null;
	}
	
	/*function getCount(){
		if (!$this->conn->tableExists($this->table)) return 0;
		
		if (is_null($this->result)) $this->result = $this->conn->execute($this->getQuery());
		if (empty($this->result)){
			return 0;
		} else {
			return mysqli_num_rows($this->result);
		}
	}*/
}


