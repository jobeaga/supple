<?php

require_once('include/SuppleConnection.php');
require_once('include/util.php');

class PhpArrayConnection extends SuppleConnection {

	var $dataDef;
	var $data;
	var $index = array();
	var $dataBaseName;
	var $opConversions = array(
			'/^(.*[^=!><])=([^=].*)$/' 	=> '\1 == \2',
			'/^(.*)<>(.*)$/' 			=> '\1 != \2',
			'/([a-zA-Z0-9_%]+)[ ]+LIKE[ ]+([\'"][^\'"]+[\'"])/' 			=> 'php_like_cmp(\1, \2)',
		);
	var $simpleConversions = array();
	var $useCoreData = true;
	// static $cache = array();
/*
	function __construct(){
		//echo "INSTANCE 1 <br>";
	}
*/
	static function test(){
		return true; // Returns true if connection type is available.
	}

	function __construct($dataBaseName = 'default', $user = '', $password = '', $host = ''){
		// "Connect" with DB (open data definition)
		if ($dataBaseName != ''){
			$dataDefFile = 'phpArrayDB/'.$dataBaseName.'datadef.php';
			$varname = $dataBaseName.'datadef';

			if (file_exists($dataDefFile)){
				require($dataDefFile);
			} else {
				$$varname = array(); 
			}

			$this->dataDef = $$varname; 
			$this->data = array();
			$this->dataBaseName = $dataBaseName;
		} else {
			// Throw exception????
		}
	}

	function dataTypeConversionArray(){
		// example: return array('integer' => 'int', 'boolean' => 'bool', 'character' => 'char', 'string' => 'str');
		return array();
	}
	

	// Podría devolver la tabla... ¿será mucha carga? ¿Hará una copia o una referencia? (probar!)
	function getTableData($table){
		$startTime = SuppleApplication::getlog()->getStartTime();
		$type = '';
		if ($table != ''){
			if ($table == '__tests'){
				
				$this->data[$table] = array();
				// OPTIMIZACION: No correr los tests.
				/*
				$tests = SuppleApplication::gettests(); // this executes the tests
				$i = 1;
				foreach ($tests as $clss => $ms){
					foreach ($ms as $method => $res){
						$res['class'] = $clss;
						$res['method'] = $method;
						$res['id'] = $i;
						$this->data[$table][] = $res;
						$i++;
					}
				}
				*/
			} elseif ($table == '__config'){

				$this->data[$table] = array();
				$c = SuppleApplication::getconfig();
				$config = $c->getValues();
				$i = 1;
				foreach ($config as $key => $value){
					$this->data[$table][] = array(
						'id' => $i,
						'key' => $key,
						'value' => $value,
					);
					$i++;
				}
			} elseif($table == '__fields'){
					
				$this->data[$table] = SuppleApplication::getdb()->getFields();
				
			} elseif($table == '__mappings'){
				
				$this->data[$table] = SuppleApplication::getdb()->getMappings();
				
			} elseif($table == '__connectiontypes'){
				
				$this->data[$table] = array();
				foreach(SuppleApplication::getdb()->getConnectionTypes() as $ctype){
					$this->data[$table][] = array('name' => $ctype, 'value' => $ctype);
				}
				
			} elseif($table == '__tables'){
				
				$this->data[$table] = SuppleApplication::getdb()->getTables();

			} elseif($table == '__actions'){

				$this->data[$table] = SuppleAction::getActionList();

			} elseif (!isset($this->data[$table])){
				$type = '_MISS';
				$tableFile = 'phpArrayDB/'.$this->dataBaseName.'/'.$table.'.php';
				$coreFile = 'phpArrayDBcore/'.$table.'.php';
				if (file_exists($coreFile) && $this->useCoreData){
					//  1. Get standard data
					$std_data = readArray($tableFile);
					//	2. Get core data
					$core_data = readArray($coreFile);
					//  3. MIX! (by id)
					/*foreach ($core_data as $i => $row){
						$this->data[$table][$i] = $row;
					}*/
					$this->data[$table] = $core_data;
					
					foreach ($std_data as $i => $row){
						// get index for this info:
						$index = '';
						if (isset($row['id'])){
							foreach ($this->data[$table] as $j => $tr){
								if (isset($tr['id']) && $tr['id'] == $row['id'] && strlen($tr['id']) == strlen($row['id'])){
									$index = $j;
								}
							}
						}
						if ($index==''){
							$this->data[$table][] = $row; // $this->data[$table][$i] = $row; ????
						} else {
							// $this->data[$table][$index] = $row;
							foreach ($row as $f => $v){
								$this->data[$table][$index][$f] = $v;
							}
						}						
						
					}
					//  Do this for all the db types??? (sounds good, doesn't work)
				} else {
					$this->data[$table] = readArray($tableFile); //$$table;
				}
			}
		}
		SuppleApplication::getlog()->logEndTime('phparray_getTableData'.$type, $startTime);
		$startTime = SuppleApplication::getlog()->getStartTime();
		$type = '';
		if (isset($this->data[$table]) && (!isset($this->index[$table]))){
			$type = '_MISS';
			// BUILD INDEXES
			$this->rebuildIndexes($table);
		}
		
		SuppleApplication::getlog()->logEndTime('phparray_getTableData_buildIndex'.$type, $startTime);
	}

	function rebuildIndexes($table) {
		// check for memmory limit
		if (memory_get_usage() > 200000000){ // 200MB
			return;
		}
		$indexes = $this->possibleIndexes($table);
		foreach ($indexes as $field){
			foreach($this->data[$table] as $i => $row){
				if (isset($row[$field]) && ($row[$field] != '') && !is_array($row[$field])){
					if (!isset($this->index[$table][$field][$row[$field]]))
						$this->index[$table][$field][$row[$field]] = array();
					$this->index[$table][$field][$row[$field]][] = $i;
				}
			}
		}
	}
	
	function possibleIndexes($table){
		// return array('id');
		$idxs = array();
		$maxs = array();
		// Get maxs
		foreach($this->data[$table] as $index => $row){
			foreach ($row as $field => $value){
				if (is_string($value)){
					if (isset($maxs[$field])){
						$maxs[$field] = max($maxs[$field], strlen($value));
					} else {
						$maxs[$field] = strlen($value);
					}
				} elseif (is_numeric($value)) {
					if (!isset($maxs[$field])){
						$maxs[$field] = 1;
					}
				}
			}
		}
		// Get idxs
		foreach ($maxs as $field => $maxlen){
			if ($maxlen < 37) $idxs[] = $field;
		}
		return $idxs;
	}

	function saveTableData($table){
		$startTime = SuppleApplication::getlog()->getStartTime();
		if ($table != ''){
			if (isset($this->data[$table])){
				$tableFile = 'phpArrayDB/'.$this->dataBaseName.'/'.$table.'.php';
				$coreFile = 'phpArrayDBcore/'.$table.'.php';				
				// Remove core data that had no changes
				$data_to_write = $this->data[$table];
				if (file_exists($coreFile) && $this->useCoreData){
					$core_data = readArray($coreFile);
					foreach ($core_data as $core_index => $core_row){
						if (isset($core_row['id'])){
							$id = $core_row['id'];
							// search for index of this record:
							foreach ($data_to_write as $index => $row_to_write){
								if (isset($row_to_write['id']) && $row_to_write['id'] == $id){
									// found it!
									if ($row_to_write == $core_row){
										// same info:
										unset($data_to_write[$index]);
									}
									// compare field by field:
									/*
									foreach ($row_to_write as $field => $value){
										if ($field != 'id' && isset($core_row[$field]) && $value == $core_row[$field]){
											unset($data_to_write[$index][$field]);
										}
									}
									*/
								}
							}
						}
					}
				}
				// Write all the data into the file
				writeArray($tableFile, $data_to_write);
				
				if ($this->dataBaseName == 'metadata'){
					SuppleApplication::getcache()->clearMetadata();
				}
			}
		}
		SuppleApplication::getlog()->logEndTime('phparray_saveTableData', $startTime);
	}

	function getNewIndex($table){
		$startTime = SuppleApplication::getlog()->getStartTime();
		$this->getTableData($table);
		
		$i = 1;
		while (isset($this->data[$table][$i])) $i++;
		SuppleApplication::getlog()->logEndTime('phparray_getNewIndex', $startTime);
		return $i;
	}

	function setValue($table, $index, $value, $insert = false){
		$startTime = SuppleApplication::getlog()->getStartTime();
		$this->getTableData($table);

		$tableFile = 'phpArrayDB/'.$this->dataBaseName.'/'.$table.'.php';
		foreach ($value as $k => $v){
			$this->data[$table][$index][$k] = $v;
		}
		appendArray($tableFile, array($index => $value), $insert);
		$this->rebuildIndexes($table);
		if ($this->dataBaseName == 'metadata'){
			SuppleApplication::getcache()->clearMetadata();
		}
		SuppleApplication::getlog()->logEndTime('phparray_setValue', $startTime);
	}


	function getNewId($table){

		$use_uuid = SuppleApplication::getconfig()->getValue('universally_unique_identifier');
		if ($use_uuid) {
			return uuid();
		} else {
			$this->getTableData($table);
			$newId = 1;
			foreach ($this->data[$table] as $row){
				if ($row['id'] >= $newId && is_numeric($row['id']) && strlen($row['id']) < 10) $newId = $row['id'] + 1;
			}
			return $newId;
		}
	}

	function getTables(){
		$path = 'phpArrayDB/'.$this->dataBaseName.'/';
		$pattern = '*.php';
		$c = folderContent($path, $pattern);
		$tables = array();

		foreach ($c as $index => $table){
			$tables[$index]['name'] = substrfromto($table, 0, strlen($table) - 5);
		}

		return $tables;
	}

	function getFields($table = ''){
		
		if ($table) $tables = array(0 => array('name' => $table));
		else $tables = $this->getTables();
		
		$fields = array();
		foreach($tables as $tableRow){
			$table = $tableRow['name'];
			$thisTableFields = array();
			$this->getTableData($table);
			if (isset($this->data[$table]) && is_array($this->data[$table])){
				foreach ($this->data[$table] as $index => $row){
					foreach ($row as $field => $data){
						if (!in_array($field, $thisTableFields)){
							$fields[]['name'] = $field;
							$fields[count($fields) - 1]['table'] = $table;
							$thisTableFields[] = $field;
						}
					}
				}
			}
		}
		return $fields;
	}

	// ##########################   Implement inherited methods #############################

	function insertRow($table, $values){
		
		// Insert the "row" (append the array)
		$newIndex = $this->getNewIndex($table);
		$this->setValue($table, $newIndex, $values, true);

		// UPDATE CACHE
		global $cache;
		$cache->unset_ram_all('phparray_getarray_'.$table);
		
	}

	function update($table, $values, $filter){
		$some = false;
		// explore the entire table... 
		$this->getTableData($table);

		$ids = array();

		foreach ($this->data[$table] as $index => $record){

			$cond = $this->evaluateCondition($filter, $record);

			if ($cond){
				//$values = $this->updateFields($table, $values, $record);
				$some = true;
				// UPDATE VALUES: only values changing
				$update_values = array();
				foreach ($values as $k => $v){
					if ($record[$k] != $v || strlen($record[$k]) != strlen($v)){
						$update_values[$k] = $v;
					}
				}
				if (count($update_values) > 0){
					// ADD ID to update. ID is needed when there is a core table
					if (!isset($update_values['id']) && isset($record['id'])) $update_values['id'] = $record['id'];
					// get index				
					$index_in_file = $this->getIndexOnTable($table, $update_values['id']);

					if ($index_in_file === ''){
						$this->setValue($table, $index, $update_values, false);
					} else {
						unset($update_values['id']);
						$this->setValue($table, $index_in_file, 
						$update_values, false);
					}
					// return id of updated records:
					$ids[] = $this->data[$table][$index]['id'];
				}
			}
		}
		
		// Write the entire file (is this being done twice?)
		if ($some){
			// its not needed
			// $this->saveTableData($table);
		}

		// UPDATE CACHE
		global $cache;
		$cache->unset_ram_all('phparray_getarray_'.$table);

		return $ids;
	}

	function getIndexOnTable($table, $id){
		$r = '';
		$filename = 'phpArrayDB/'.$this->dataBaseName.'/'.$table.'.php';
		if (file_exists($filename)){
			$contents = readArray($filename, $table);
			foreach ($contents as $index => $row){
				if (isset($row['id']) && $row['id'] == $id && strlen($row['id']) == strlen($id)){
					return $index;
				}
			}
		}
		return $r;
	}

	function delete($table, $filter){
		$some = false;
		// explore the entire table... 
		$this->getTableData($table);

		$ids = array();

		foreach ($this->data[$table] as $index => $record){

			$cond = $this->evaluateCondition($filter, $record);

			if ($cond){
				$some = true;
				$ids[] = $this->data[$table][$index]['id'];
				// Unset the value
				unset($this->data[$table][$index]);
			}
		}
		// Write the entire file
		if ($some){
			$this->saveTableData($table);
		}

		// UPDATE CACHE
		global $cache;
		$cache->unset_ram_all('phparray_getarray_'.$table);
		
		// return old ids
		return $ids;
	}

	function from($table){
		// Create a new resultSet with a reference to this connection
		return (new PhpArrayResultset($this, $table));
	}

}

// Debería extender una superclase abstracta
class PhpArrayResultset extends SuppleResultset {
	
	var $resultSet;
	var $resultCount;
	var $resultSetIndex;
	
	function __construct($connection, $table){
		
		parent::__construct($connection, $table);
		
	}

	function getRow(){
		if (is_null($this->resultSet)) $this->getArray();
		if ($this->resultSet && $this->resultSet[$this->resultSetIndex]){
			return $this->resultSet[$this->resultSetIndex++];
		} else {
			return array();
		}
	}
	
	function clear(){
		$this->resultSet = null;
		$this->resultCount = 0;
	}

	function reset(){
		$this->resultSetIndex = 0;
	}

	function eof(){
		if (is_null($this->resultSet)) $this->getArray();
		return empty($this->resultSet[$this->resultSetIndex]);
		// return ($this->resultSetIndex >= $this->resultCount);
	}

	function getArray($sort = true){
		
		$key = array( 
			$this->table,
			$this->filter,
			$this->limitStart,
			$this->limitCount,
			$this->groupByFields,
			$this->funtions,
			$this->orderFields,
			$sort,
			$this->reverse, );
		
		$cache = SuppleApplication::getcache();
		$cache_key = $cache->create_md5($key);
		// UPGRADE: UNCOMMENTED this: 
		if ($cache->exists_ram('phparray_getarray_'.$this->table, $cache_key)){

			$this->resultSet = $cache->get_ram('phparray_getarray_'.$this->table, $cache_key);
			$this->resultCount = count($this->resultSet);

			SuppleApplication::getlog()->logEndTime('phparray_getArray_cacheHit', $allTime);

		} else {
	
			$allTime = SuppleApplication::getlog()->getStartTime();

			$startTime = SuppleApplication::getlog()->getStartTime();

			$this->resultSet = array();
			$this->resultCount = 0;
			$this->conn->getTableData($this->table);
			
			SuppleApplication::getlog()->logEndTime('phparray_getArray_getTableData', $startTime);
			$startTime = SuppleApplication::getlog()->getStartTime();
			$type = '';

			// USING INDEX
			if (false /*$idx = $this->getIndexForFilter($this->filter, $this->table)*/){
				$type = 'withIndex';
				// Using filter $idx
				$data = $this->getDataFromIndex($idx, $this->filter, $this->table);

				// RANDOM
				if ($this->random){
					$data = $this->array_random($data);
					$this->random = false;
				}

				// Using the rest of the filters
				if (count($this->filter) > 1){
					foreach($data as $key => $row){
						// Condición de escape: hago uno de más por lo de anterior/siguiente.
						if (($this->resultCount == ($this->limitStart + $this->limitCount + 1)) && ($this->limitCount > 0)
							&& (!($this->orderFields && $sort))) break;
						// Si verifica la condición, lo agrego.
						$cond = $this->conn->evaluateCondition($this->filter, $row);
						if ($cond){
							$this->resultSet[] = $row;
							$this->resultCount++;
						}
					}
				} else {
					$this->resultSet = $data;
					$this->resultCount = count($data);
				}
			
			} else {
				// WITHOUT INDEXES
				$type = 'noIndex';
				// filtro con filter. Si no hay orden puedo cortar al alcanzar el limite.
				if (isset($this->conn->data[$this->table])){

					// RANDOM
					if ($this->random){
						$data = $this->array_random($this->conn->data[$this->table]);
						$this->random = false;
					} else {
						$data = $this->conn->data[$this->table];
					}

					// echo $this->table." -- ".print_r($this->filter,true)."<br>";
					$exit_limit = ($this->limitStart + $this->limitCount + 1);
					foreach($data as $key => $row){

						// Condición de escape: hago uno de más por lo de anterior/siguiente.
						if (($this->resultCount == $exit_limit) && ($this->limitCount > 0)
							&& (!($this->orderFields && $sort))) break;
						
						// Si verifica la condición, lo agrego.
						$cond = $this->conn->evaluateCondition($this->filter, $row);

						if ($cond){
							$this->resultSet[] = $row;
							$this->resultCount++;
						}
					}
				}
			}
			
			SuppleApplication::getlog()->logEndTime('phparray_getArray_filter_'.$type, $startTime);
			$startTime = SuppleApplication::getlog()->getStartTime();

			// si hay grupos, los voy armando
			if ($this->groupByFields){
				$groupRows = array();
				// ¿Mas de un metodo para agrupar?
				foreach ($this->groupByFields as $field){
					// Ordeno
					sortArrayByField($this->resultSet, $field);
				}
				
				unset($lastgroup);
				// y voy eliminando lo que sobra
				foreach ($this->resultSet as $index => $row){
					// build array representing the group
					$group = array();
					foreach ($this->groupByFields as $field){
						$group[$field] = (isset($row[$field]))?$row[$field]:'';
					}
					// Erase rows with same group as previous
					if (isset($lastgroup) && $lastgroup == $group){
						unset($this->resultSet[$index]);
					} else {
						$lastgroup = $group;
					}
				}
				
			}

			SuppleApplication::getlog()->logEndTime('phparray_getArray_group', $startTime);
			$startTime = SuppleApplication::getlog()->getStartTime();

			// dentro de cada grupo aplico funciones (si no hay grupos, el grupo es uno solo)
			if ($this->funtions){

					if (!isset($groupRows)){
						
						$this->groupByFields = array('id');
						
						// Implementacion de un grupo solo
						if ($this->limitCount > 0){
							$this->resultCount = $this->limitCount;
							// $this->resultSet = array_splice($this->resultSet, $this->limitStart, $this->limitCount);
							$groupRows[0] = array_splice($this->resultSet, $this->limitStart, $this->limitCount);
						} else {
							$groupRows[0] = $this->resultSet;
						}
					}
					
					$returnResults = array();
					
					foreach($groupRows as $campoGrupo => $resultSet){
					
					// Hago la operacion
					$functionResults = array();
					$avgCount = 0;
					unset($max);

					foreach($resultSet as $key => $row){
						foreach($this->funtions as $field => $func){
							switch ($func) {
								case 'sum':
									if (isset($functionResults[$field])){
										$functionResults[$field] = $functionResults[$field] + $row[$field];
									} else {
										$functionResults[$field] = $row[$field];
									}
									break;
								case 'count':
									if (isset($functionResults[$field])){
										$functionResults[$field] = $functionResults[$field] + 1;
									} else {
										$functionResults[$field] = 1;
									}
									break;
								case 'max':
									if (isset($functionResults[$field])){
										if ($functionResults[$field] < $row[$field])
											$functionResults[$field] = $row[$field];
									} else {
										$functionResults[$field] = $row[$field];
									}
									break;
								case 'avg':
									if (isset($functionResults[$field])){
										$functionResults[$field] = (($functionResults[$field] * $avgCount) + $row[$field]) / ($avgCount + 1);
										$avgCount++;
									} else {
										$functionResults[$field] = $row[$field];
										$avgCount = 1;
									}
									break;
							} 
						}
					}
					
					// También me gustaría ver los campos por los que agrupé ??? Rta: Sólo en caso de que no sea func de agreg.
					foreach ($this->groupByFields as $campo){
						if (isset($resultSet[0])){
							if (!in_array($campo, array_flip($this->funtions)))
								$functionResults[$campo] = $resultSet[0][$campo];
						} else {
							$functionResults[$campo] = 0;
						}
					}
					$returnResults[] = $functionResults;
					
					}	

					$this->resultSet = $returnResults;

			}
			
			SuppleApplication::getlog()->logEndTime('phparray_getArray_functions', $startTime);
			$startTime = SuppleApplication::getlog()->getStartTime();

			// si no hay funciones y agrupé, en el resultado queda el primer registro de cada grupo
			// si hay funciones y no agrupé, en el resultado queda sólo un registro
			
			

			// si el resultado tiene que estar ordenado, ordeno ahora
			if ($this->orderFields && $sort){
				
				// Ordeno desde atrás para adelante
				$ordenes = array_reverse($this->orderFields);
				foreach($ordenes as $campo){
					// print_r($this->resultSet);
					sortArrayByField($this->resultSet, $campo);
				}
				// print_r($this->resultSet);

			}
			
			
			if ($this->reverse){
				$this->resultSet = array_reverse($this->resultSet);
			}

			
			SuppleApplication::getlog()->logEndTime('phparray_getArray_order', $startTime);
			$startTime = SuppleApplication::getlog()->getStartTime();

			// Completo la información que me falta de cada uno
			$id_anterior = '';
			foreach($this->resultSet as $key => $row){
				
				if (empty($row['id'])) $row['id'] = $key + 1;

				if ($id_anterior !== ''){
					$this->resultSet[$key_anterior]['_nextid'] = $row['id'];
				}

				$this->resultSet[$key]['_previousid'] = $id_anterior;
				$this->resultSet[$key]['_nextid'] = '';

				$id_anterior = $row['id'];
				$key_anterior = $key;
			}
			
			SuppleApplication::getlog()->logEndTime('phparray_getArray_additionalInfo', $startTime);
			$startTime = SuppleApplication::getlog()->getStartTime();

			// ahora sí, tomo los indicados por el limite.
			if ($this->limitCount > 0){
				$this->resultCount = $this->limitCount;
				$this->resultSet = array_splice($this->resultSet, $this->limitStart, $this->limitCount);
			}
			
			SuppleApplication::getlog()->logEndTime('phparray_getArray_limit', $startTime);

			$this->reverse = false; // para que la proxima no salga al reves.

			/*
			// CACHE...
			PhpArrayConnection::$cache[$cache_key] = $this->resultSet;
			*/
			SuppleApplication::getlog()->logEndTime('phparray_getArray_cacheMiss', $allTime);

			$this->complementData($this->resultSet);

			$cache->set_ram('phparray_getarray_'.$this->table, $cache_key, $this->resultSet);

		}
		
		// devuelvo el resultset. Los indices del array no corresponden con los de la tabla original
		// sino que son nuevos.
		return $this->resultSet;

	}

	function getCount(){
		// tendría que hacer algo como el getArray pero sin el order.
		if (is_null($this->resultSet)) $this->getArray(false);
		return $this->resultCount;
	}
	
	function getIndexForFilter($filter, $table){
		
		if (!empty($table)){
			foreach ($filter as $possible_index => $value){
				
				if ((!empty($possible_index)) &&
					(!empty($value)) && 
					(is_numeric($value) || is_string($value)) &&
					isset($this->conn->index[$table][$possible_index]) && 
					isset($this->conn->index[$table][$possible_index][$value])){
						return $possible_index;
				}
			}
		}
		return false;
		
	}
  
	function getDataFromIndex($idx, $filter, $table){
		
		$used_filter = $filter[$idx];
		$i = $this->conn->index[$table][$idx][$used_filter];
		if (is_array($i)){
			$rows = array();
			foreach ($i as $j){
				if (isset($this->conn->data[$table][$j]))
					$rows[] = $this->conn->data[$table][$j];
			}
		} else {
			$rows = array($this->conn->data[$table][$i]);
		}
		return $rows;

	}
	
}

