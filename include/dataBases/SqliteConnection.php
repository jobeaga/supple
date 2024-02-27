<?php

require_once('include/SQLConnection.php');
require_once('include/util.php');

class SqliteConnection extends SQLConnection {
	
	var $dataTypes = array(
			'default' => 'TEXT',
			'id' => 'TEXT',
			'numeric' => 'INTEGER',
			'decimal' => 'REAL',
			'text' => 'TEXT',
		);
	var $dataSizes = array(
			'TEXT' => 255,
			'INTEGER' => 255,
			'REAL' => 255,
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
	var $enabled;

	static function test(){
		return class_exists('SQLite3');
	}
	
	function __construct($dataBaseName = 'default', $user = '', $password = '', $host = 'localhost'){

		$this->enabled = $this->test();

		if ($this->enabled) {
			$this->link = new SQLite3('sqliteDB/'.$dataBaseName.'.db');
			if (!$this->link) {
				die('Could not connect sqlite.');
			}
			
			$this->dataBaseName = $dataBaseName;
		}
	}
	
	// PRIVATE
	function getTables($for_query = true){
	
		if (empty($this->cache['tables'])){
			$tables = array();
			$query = "SELECT name FROM sqlite_master WHERE type='table'";
			$result = $this->execute($query);
			$data = array();
			while ($row = $this->fetch($result)){
				$data[] = $row;
			}
			$this->cache['tables'] = $data;
		} else {
			$data = $this->cache['tables'];
		}

		foreach ($data as $row){
			if ($for_query) $tables[]['name'] = $row['name'];
			else $tables[] = $row['name'];
		}

		return $tables;
	}

	function getFields($table = '', $for_query = true){
	
		if ($table) $tables = array(0 => array('name' => $table));
		else $tables = $this->getTables();
		$fields = array();

		foreach ($tables as $index => $t){
			$table_name = $t['name'];			
			if (isset($this->cache['getFields'][$table_name])){
				$data = $this->cache['getFields'][$table_name];
			} else {
				$data = array();
				$query = "PRAGMA table_info(".$t['name'].");";
				$result = $this->execute($query);
				while ($row = $this->fetch($result)){
					$data[] = $row;
				}
				$this->cache['getFields'][$table_name] = $data;
			}

			foreach ($data as $row){
				if ($for_query){
					$fields[] = array(
						'name' => $row['name'],
						'table' => $t['name'],
						'type' => $row['type'],
					);
					/*
					$fields[$row['name']]['name'] = $row['name'];
					$fields[$row['name']]['table'] = $t['name'];
					$fields[$row['name']]['type'] = $row['type'];
					*/
				} else {
					$fields[] = $row['name'];
				}
			}
		}
		
		return $fields;
	}

	function createTableIfDontExists($table){
		
		if (!$this->tableExists($table)){
			$query = "CREATE TABLE $table (id ".$this->dataTypes['id'].")";
			$this->execute($query);
			$this->resetcache();
			// TODO: Add id index
			// TODO: Add index to relationships???
		}

	}

	function fieldSize($field, $table = ''){

		$size = 1000000000;

		/*
		// TODO: CACHE
		$type = '';
		$size = 0;
		if (empty($table)){
			$type = $field;
		} else {
			$fields = $this->getFields($table);
			$type = $fields[$field]['type'];
		}
		// REGEXP
		*/
		
		return $size;
	}

	// strictly private!!!
	function execute($query){
		//echo "$query <br>";
		if ($this->enabled) {
			$result = $this->link->query($query);
			if (!$result) {
				die('Invalid query: ' . $query . ' Last SQLite3 Error: ' . $this->link->lastErrorMsg());
			}
			return $result;
		} else {
			return null;
		}
	}

	function fetch($result) {
		if ($this->enabled) {
			return $result->fetchArray(SQLITE3_ASSOC);
		} else {
			return array();
		}
	}

	function isNumericColumn($table, $field){
		$result = $this->execute("SELECT COUNT(*) c FROM `$table` WHERE `$field` GLOB '*[0-9]*';");
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

	
	function getNewId($table){
		
		$use_uuid = SuppleApplication::getconfig()->getValue('universally_unique_identifier');
		if ($use_uuid) {
			return uuid();
		} else {
			// SOMETHING LIKE "SELECT MAX(id) FROM $table" + 1
			$this->createTableIfDontExists($table);
			$this->flexField($table, 'id', '1');
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
		$this->createTableIfDontExists($table);
		return (new SqliteResultset($this, $table));
	}
	

	
	
}

class SqliteResultset extends SQLResultset {
	
	var $result;
	
	function __construct($connection, $table){
		parent::__construct($connection, $table);
		$this->result = null;
	}
	
	function getCount(){
		/*
		if (is_null($this->result)) $this->result = $this->conn->execute($this->getQuery());
		if (empty($this->result)){
			return 0;
		} else {
			return $this->result->numRows();
		}
		*/
		$c = 0;
		$query = $this->getQuery();
		$result = $this->conn->execute($query);
		while ($row = $this->conn->fetch($result)){
			$c++;
		}
		return $c;
	}
}



