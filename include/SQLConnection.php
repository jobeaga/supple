<?php

require_once('include/SuppleConnection.php');
require_once('include/util.php');

class SQLConnection extends SuppleConnection {
	
	var $dataTypes = array(
			'default' => 'VARCHAR(50)',
			'id' => 'VARCHAR(36)',
			'numeric' => 'INT',
			'decimal' => 'DOUBLE',
			'text' => 'TEXT',
		);
	var $link;
	var $opConversions = array();
	var $simpleConversions = array();
	var $dataBaseName;
	var $cache = array();
	var $numericCache = array();
	
	// PRIVATE
	function getTables($for_query = true){
		return array();
	}

	function getFields($table = '', $for_query = true){
		return array();
	}

	function createTableIfDontExists($table){
		
	}

	function resetcache() {
		$this->cache = array();
	}

	function fieldExists($table, $field){
		$fields = $this->getFields($table, false);
		// CASO DE FUNCIONES MYSQL APLICADAS SOBRE CAMPOS
		$fs = $this->getVarNames($field); // splits
		$r = true;
		foreach ($fs as $f){
			$r = $r && in_array($f, $fields);
		}
		// OLD: whole $field
		//return in_array($field, $fields);
		// NEW: BOTH
		return ($r || in_array($field, $fields));
	}

	function singleFieldExists($table, $field){
		$fields = $this->getFields($table, false);
		return in_array($field, $fields);
	}

	function isNumeric($table, $field){
		if (!isset($this->numericCache[$table][$field])){
			$this->numericCache[$table][$field] = $this->isNumericColumn($table, $field);
		}
		return $this->numericCache[$table][$field];
	}

	function isNumericColumn($table, $field){
		return false;
	}

	function tableExists($table){
		$tables = $this->getTables();
		foreach ($tables as $index => $t){
			if ($t['name'] == $table) return true;
		}
		return false;
	}

	function flexField($table, $field, $value){
	
		if ($this->fieldExists($table, $field)){

			$size = $this->fieldSize($field, $table);

			if ((strlen($value) > $size) && ($size > 0)){
				// "text" is the biggest
				$query = "ALTER TABLE `$table` MODIFY `$field` ".$this->dataTypes['text'];
				$this->execute($query);
				$this->resetcache();
			}
			
		} else if ($field != ''){
			// Not exists: create the smallest.
			// TODO: ID, it has to be autoincrement
			$defaultSize = $this->fieldSize($this->dataTypes['default']);
			
			if (strlen($value) > $defaultSize && $defaultSize > 0) 
				$dataType = $this->dataTypes['text'];
			else 
				$dataType = $this->dataTypes['default'];
			
			$query = "ALTER TABLE `$table` ADD `$field` $dataType";
			$this->execute($query);
			$this->resetcache();
			
		}
	}

	function fieldSize($field, $table){
		return 0;
	}

	function buildWhere($filters, $table = ''){
		// we have to change things like = to ==
		// it's usefull to have an array describing transformations 
		// with regular expressions and use abstract methods to do this.
		$where = '';
		foreach ($filters as $expression => $value){
			// echo "$expression => $value <br>";
			if ($where) $where .= ' AND ';

			$exp = $this->convertOp($expression);
			$exp = $this->removeMissingFields($exp, $table);

			// multiple values in one condition (implemented with OR).
			if (is_array($value)){

				$sub_where = '';
				foreach ($value as $v){
				
					if ($sub_where) $sub_where .= ' OR ';
				
					if ((substr($v, 0, 1) == "'" && substr($v, -1) == "'") || (substr($v, 0, 1) == '"' && substr($v, -1) == '"')) $val = "$v";
					else $val = "'". str_replace("'", "\\'", $v) . "'";
					
					if (strpos($exp, ' ')  || strpos($exp, '=') || strpos($exp, '<') || strpos($exp, '>') || strpos($exp, '+') || strpos($exp, '-') || strpos($exp, '/') || strpos($exp, '*')) $sub_where .= "($exp) = $val";
					else $sub_where .= "$exp = $val";
				
				}
				$where .= "($sub_where)";
			
			} else if (trim($value) == ''){
				$where .= "($exp)";
			} else {
				//$value = $this->removeMissingFields($value, $table); // NO!, ver TODO de #136
				if ((substr($value, 0, 1) == "'" && substr($value, -1) == "'") || (substr($value, 0, 1) == '"' && substr($value, -1) == '"')) $val = "$value";
				else $val = "'". str_replace("'", "\\'", $value) . "'";

				if (strpos($exp, ' ')  || strpos($exp, '=') || strpos($exp, '<') || strpos($exp, '>') || strpos($exp, '+') || strpos($exp, '-') || strpos($exp, '/') || strpos($exp, '*')){
					$where .= "($exp) = $val";
				} else {
					// TODO:
					// $this->flexField($table, $exp, '');
					$where .= "$exp = $val";
				}
			}
		}
		
		return $where;
	}

	function removeMissingFields($exp, $table){
		$fields = $this->getFields($table, false);
		$ops = array('AND', 'OR', 'LIKE', 'BETWEEN', 'DATE', 'DAY', 'MONTH', 'YEAR', 'WEEK', 'IS', 'NOT', 'NULL');

		$temp_exp = $exp;
		$temp_exp = str_replace('  ',' ', $temp_exp);
		$temp_exp = str_replace(' (','(', $temp_exp);
		$temp_exp = " $temp_exp ";

		$var = 'A-Za-z0-9_';
		$matches = preg_match_all_recursive("/[^$var%'\"]([A-Za-z_][$var]+)[^$var%'\"\(]/", $temp_exp);

		$new_string = '';
		$last_pos = 0;
		foreach ($matches as $p => $s) {
			$new_string .= substr($temp_exp, $last_pos, $p - $last_pos);
			if (in_array($s, $fields) || in_array(strtoupper($s), $ops) ) {
				$new_string .= $s;
			} else {
				$new_string .= "''";
			}
			
			$last_pos = $p + strlen($s);
		}
		$new_string .= substr($temp_exp, $last_pos);
		// quito los espacios
		$new_string = substr($new_string, 1, -1);

		return $new_string;
	}

	// strictly private!!!
	function execute($query){
		return null;
	}

	// PUBLIC

	function insertRow($table, $values){
		
		$this->createTableIfDontExists($table);
		
		$fields = '';
		$vals = '';

		// TODO: unset ID, it has to be autoincrement???
		
		foreach ($values as $f => $v){
			//if ($f != ''){ // esto no deber�a pasar, si sucede es porque hay un error en otro lugar.
			
				$this->flexField($table, $f, $v);
				
				if ($fields) $fields .= ', `'.$f.'`';
				else $fields .= '`'.$f.'`';
				
				$encvalue = str_replace("'", "\\'", $v);
				
				if (/*!is_numeric($encvalue)*/ true) $encvalue = "'".$encvalue."'";
				
				if ($vals) $vals .= ", ".$encvalue;
				else $vals = $encvalue;
			// }
			
		}
	
		$query = "INSERT INTO `$table` ($fields) VALUES ($vals)";
		
		$this->execute($query);
		
	}
	
	function getNewId($table){
		// WE NEED ID BEFORE INSERT
		return 0;
	}

	function update($table, $values, $filter){

		if ($this->tableExists($table)){
	
			// Build set
			$set = '';
			foreach ($values as $field => $value){

				$this->flexField($table, $field, $value);

				if ($set) $set .= ', ';

				//if (is_numeric($value)) $set .= "`$field` = $value";
				//else {
					$encvalue = str_replace("'", "\\'", $value);
					$set .= "`$field` = '$encvalue'";
				//}
			}

			// Build Where
			$where = $this->buildWhere($filter, $table);

			// Affected ids
			$selected_ids = $this->from($table)->where($filter)->getArray();
			$ids = array();
			foreach ($selected_ids as $row){
				$ids[] = $row['id'];
			}

			$query = "UPDATE `$table` SET $set WHERE $where";
			// global $log; $log->fatal($query);

			// EXECUTE!
			$this->execute($query);

			// Return affected ids!
			return $ids;
		} else {
			return array();
		}

	}

	function delete($table, $filter){

		if ($this->tableExists($table)){
			// Build Where
			$where = $this->buildWhere($filter, $table);

			// Affected ids
			$selected_ids = $this->from($table)->where($filter)->getArray();
			$ids = array();
			foreach ($selected_ids as $row){
				$ids[] = $row['id'];
			}

			$query = "DELETE FROM `$table` WHERE $where";

			// EXECUTE!
			$this->execute($query);

			// Return affected ids!
			return $ids;
		} else {
			return array();
		}
	}

	// Query functions...
	function from($table){
		$this->createTableIfDontExists($table);
		return (new SQLResultset($this, $table));
	}
	

	
	
}

class SQLResultset extends SuppleResultset {
	
	var $result;
	
	function __construct($connection, $table){
		parent::__construct($connection, $table);
	}
	
	// Get a row and wait for the next 
	function getRow(){

		if (!$this->conn->tableExists($this->table)) return array();
		
		if (is_null($this->result)) $this->result = $this->conn->execute($this->getQuery());
		if ($row = $this->conn->fetch($this->result)){
			return $row;
		} else {
			return array();
		}
		
	}
	
	// Erase the obtained data
	function clear(){}
	
	// Place the pointer at the beginning
	function reset(){}
	
	// Are we there yet?
	function eof(){}
	
	// return a php array with the full result.
	function getArray($sort = true){

		if (!$this->conn->tableExists($this->table)) return array();
		
		$query = $this->getQuery();
		
		// echo "QUERY = ".$query."<br>";
		// $GLOBALS['log']->fatal($query);

		$resultSet = array();
		$result = $this->conn->execute($query);
		while ($row = $this->conn->fetch($result)){
			$resultSet[] = $row;
		}

		// Agrego previous y next:
		if ($this->limitCount){
		  // Conservo:
		  $old_start = $this->limitStart;
		  $old_count = $this->limitCount;

		  // El siguiente:
		  $this->limitStart = $old_start + $old_count;
		  $this->limitCount = 1;
		  $row = $this->getRow();
		  $last = (isset($row['id']))?$row['id']:'';

		  $this->result = null;

		  // El anterior:
		  $this->limitStart = $old_start - 1;
		  $this->limitCount = 1;
		  if ($old_start - 1 >= 0){
			$row = $this->getRow();
			$first = (isset($row['id']))?$row['id']:'';
		  } else {
			$first = '';
		  }

		  // Recupero
		  $this->limitStart = $old_start;
		  $this->limitCount = $old_count;
		} else {
		  $first = '';
		  $last = '';
		}


		foreach ($resultSet as $index => $row){
		  if (isset($resultSet[$index - 1])){
			$resultSet[$index]['_previousid'] = $resultSet[$index - 1]['id'];
		  } else {
			$resultSet[$index]['_previousid'] = $first;
		  }
		  if (isset($resultSet[$index + 1])){
			$resultSet[$index]['_nextid'] = $resultSet[$index + 1]['id'];
		  } else {
			$resultSet[$index]['_nextid'] = $last;
		  }
		}

		// RANDOM... mhhhhh
		if ($this->random){
			$resultSet = $this->array_random($resultSet);
			$this->random = false;
		}
		
		$this->complementData($resultSet);
		
		return $resultSet;
	}
	
	function getCount(){
		$c = $this->applyFunction(array('*' => 'count'))->getRow();
		return $c['count'];
	}
	
	// PRIVATE!!!
	function getQuery(){
		//echo "GET QUERY {$this->table}<br>";
		$additionalGroups = array();
		if ($this->funtions){
			$functions = "";
			foreach($this->funtions as $campo => $funcion){
				if ($this->conn->fieldExists($this->table, $campo) || $campo == '*'){
					if ($functions) $functions .= ", ";
					if ($campo == "*"){
						$functions .= strtoupper($funcion)."(*) `count`";
					} else {
						$functions .= strtoupper($funcion)."(`".$campo."`) `$campo`";
					}
				}
				// $additionalGroups[] = $campo;
			}
			
		} else {
			$functions = "";
		}
		
		if ($functions && (!$this->groupByFields)){
			$query = "SELECT $functions FROM ".$this->table;
		} else {
			if ($functions) $query = "SELECT *, $functions FROM ".$this->table;
			else $query = "SELECT * FROM ".$this->table;
		}

		if ($this->filter){
			$where = $this->conn->buildWhere($this->filter, $this->table);
			$query .= " WHERE $where";
		}

		if ($this->groupByFields || $additionalGroups){
			$gs = mixArrays($this->groupByFields, $additionalGroups);
			$group = '';
			foreach ($gs as $g){
				if ($this->conn->fieldExists($this->table, $g)){
					if ($this->conn->singleFieldExists($this->table, $g)){
						if ($group=='') $group .= "`$g`";
						else $group .= ", `$g`";
					} else {
						if ($group=='') $group .= "$g";
						else $group .= ", $g";
					}
				}
			}
			if ($group != '') $query .= " GROUP BY $group";
			// $GLOBALS['log']->fatal($this->groupByFields);
		}

		if ($this->orderFields){
			if ($this->reverse){
				$extra = ' desc';
			} else {
				$extra = '';
			}
			$order = '';
			foreach ($this->orderFields as $g){
				if ($this->conn->fieldExists($this->table, $g)){
					if ($this->conn->isNumeric($this->table, $g)){
						$gf = "(`$g` * 1)";
					} else {
						$gf = "`$g`";
					}
					if ($order=='') $order .= $gf.$extra;
					else $order .= ", ".$gf.$extra;
				}
			}
			if ($order != '') $query .= " ORDER BY $order";
		}

		if ($this->limitCount){
			$query .= " LIMIT ".$this->limitStart.", ".$this->limitCount;
		}
		
		$GLOBALS['log']->fatal($query);
		//echo "$query \n";

		return $query;
	}
	
}


?>
