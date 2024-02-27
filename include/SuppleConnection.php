<?php

require_once('include/SuppleApplication.php');

class SuppleConnection {

	// private $cachel1 = array();
	// private $cachel2 = array();
	var $opConversions = array(
		'/^(.*[^=!])=([^=].*)$/' 	=> '\1 == \2',
		'/^(.*)<>(.*)$/' 			=> '\1 != \2',
		'/([a-zA-Z0-9_%]+)[ ]+LIKE[ ]+([\'"][^\'"]+[\'"])/' 			=> 'php_like_cmp(\1, \2)',
	);
	var $simpleConversions = array();

	static function test(){
		return false; // Returns true if connection type is available.
	}
	
	function insert($table, $values){
		$isarray = false;
		$isarrayarray = false;
		
		// Detect what kind of value do we have.
		if (is_array($values)){
			// We have an array of values, or an array of arrays of values
			foreach ($values as $value){
				if (is_array($value)) $isarrayarray = true;
				else $isarray = true;
			}
		}
		

		if ($isarray && $isarrayarray){
			// mixed???  

		} elseif ($isarray) {
			
			if (empty($values['id'])) $values['id'] = $this->getNewId($table);
			$this->insertRow($table, $values);
			return $values['id'];

		} elseif ($isarrayarray) {

			// Insert all the arrays as values
			// What shall I do with return values?
			$ids = array();
			foreach ($values as $value){
				$ids[] = $this->insert($table, $value);
			}
			return $ids;

		} else {

			// Insert only one value?
			return $this->insert($table, array($value));

		}
	}
	
	function convertOp($subject){
		//echo "$subject <br>";
		$convs = $this->getOpConversions();
		// print_r($convs); echo "<br>";
		foreach ($convs as $pattern => $replacement){
			$count = 0;
			while (preg_match($pattern, $subject)){
				$subject = preg_replace($pattern, $replacement, $subject);
				$count++;
				if ($count > 100) break;
			}
		}

		//echo "$subject <br>";

		$convs = $this->getSimpleConversions();
		//print_r($convs);
		foreach ($convs as $pattern => $replacement){
			$subject = str_replace($pattern, $replacement, $subject);
			//echo "$pattern => $replacement => $subject \n\n";
		}

		//echo $subject;

		return $subject;
	}

	function phpConvertOp($subject){
		//echo "$subject <br>";
		$convs = array(
			'/^(.*[^=!])=([^=].*)$/' 	=> '\1 == \2',
			'/^(.*)<>(.*)$/' 			=> '\1 != \2',
			'/([a-zA-Z0-9_%]+)[ ]+LIKE[ ]+([\'"][^\'"]+[\'"])/' 			=> 'php_like_cmp(\1, \2)',
		);

		foreach ($convs as $pattern => $replacement){
			$count = 0;
			while (preg_match($pattern, $subject)){
				$subject = preg_replace($pattern, $replacement, $subject);
				$count++;
				if ($count > 100) break;
			}
		}
		return $subject;
	}

	function getOpConversions(){
		return $this->opConversions;
	}

	function getSimpleConversions(){
		return $this->simpleConversions;
	}

	function insertRow($table, $values){}
	
	function getNewId($table){}

	function update($table, $values, $filter){}

	function updateFields($table, $values, $row) {
		
	}

	function delete($table, $filter){}

	// Query functions...

	function from($table){}

	function addIndex($table, $idx_name, $columns){
		return $this;
	}

	function getIndexes($table){
		return array();
	}

 // DataTypes? Since when?!!!
	function convertDataTypes(&$fields){
		// Working!

		$conversionArray = $this->dataTypeConversionArray();

		// For each field, convert the assigned data type
		foreach ($fields as $field => $datat){
			if (isset($conversionArray[$datat]))
				$fields[$field] = $conversionArray[$datat];
		}

	}

	function dataTypeConversionArray(){
		return array();
	}
	
	function close(){
		return true;
	}
	
	function getVarNames($string){
		global $cache;

		$key = md5($string);
		if ($cache->exists('suppleConnection_getVarNames', $key)){
			return $cache->get('suppleConnection_getVarNames', $key);
		}

		$r = array();
		// NORMALIZE A BIT
		$s = trim($string); //  a nice trim
		$s = str_replace('  ', ' ', $s); $s = str_replace('  ', ' ', $s); // double, triple and quad spaces
		$s = str_replace(' (', '(', $s); // space before parentheses
		$s = " $s "; // spaces after and before
		
		// MATCH!, whole words not inside quotes nor before parentheses (funcion names)
		preg_match_all('/[^a-zA-Z0-9_\']([a-zA-Z_][a-zA-Z0-9_]+)[^a-zA-Z0-9_(\']/', $s, $vars, PREG_OFFSET_CAPTURE);
		
		foreach ($vars[1] as $v){
			// quotes count before $v[1]
			if ( (substr_count($s, "'", 0, $v[1]) % 2 == 0) && (substr_count($s, '"', 0, $v[1]) % 2 == 0) ) {
				$r[] = $v[0]; // add match
			}			
		}

		$cache->set('suppleConnection_getVarNames', $key, $r);

		return $r;
	}
	
	function evaluate($condition, $rec){
		global $cache;

		// get variable names from condition
		$varnames = $this->getVarNames($condition);

		// TODO: generate $record only with values used on condition. This will improve caching
		$record = $rec;
		
		// set empty values where $rec doesn't have a value
		foreach ($varnames as $v){
			if (!isset($record[$v])){
				$record[$v] = '';
			}
		}
	
		// CACHE evaluation
		$key = md5(var_export(array($condition, $record),true));
		if ($cache->exists('suppleConnection_evaluate', $key)){
			return $cache->get('suppleConnection_evaluate', $key);
		}

		$allTime = SuppleApplication::getlog()->getStartTime(); // Log only misses

		//$startTime = SuppleApplication::getlog()->getStartTime();

		if (isset($rec[$condition])){
			$r = $rec[$condition];
		} else {

		// STEP 1: LEN SORT (deprecated, not needed)
		//$record = lenSort($rec);

		// STEP 2: Separate quoted strings. We're going to replace outside the quotes:
		$strings = separate_quoted($condition);
		// print_r($strings); echo "<br><br>";

		// STEP 3: Prepare keys and values
		$fieldNames = array_keys($record);
		$fieldValues = array_values($record);

		//SuppleApplication::getlog()->logEndTime('suppleConnection_evaluate_123', $startTime);
		
		//$startTime = SuppleApplication::getlog()->getStartTime();
		// STEP 4: Add quotes to no-number values
		foreach ($fieldValues as $i => $fieldValue){
			if (is_array($fieldValue)) {
				$fieldValues[$i] = '';
			} else if (!is_numeric($fieldValue)) {
				$fieldValues[$i] = "'".str_replace("'", "\\'", $fieldValue)."'";
			}
			
			// CHANGE FOR regexp: (NOT VAR NOR ')$fieldName(NOT VAR NOR ')
			$fieldNames[$i] = '/([^a-zA-Z0-9_\'])'.$fieldNames[$i].'([^a-zA-Z0-9_\'])/';
			$fieldValues[$i] = '$01'.$fieldValues[$i].'$02';
		}
		//SuppleApplication::getlog()->logEndTime('suppleConnection_evaluate_quoting', $startTime);
		
		// STEP 5: Replace
		//$startTime = SuppleApplication::getlog()->getStartTime();
		$even = true;
		$exp = '';
		foreach ($strings as $index => $string){
			if ($even){	
				//$strings[$index] = str_replace($fieldNames, $fieldValues, $string);
				$exp .= substr(preg_replace($fieldNames, $fieldValues, ' '.$string.' '), 1, -1);
			} else {
				$exp .= $string;
			}
			$even = !$even;
		}
		//SuppleApplication::getlog()->logEndTime('suppleConnection_evaluate_replace', $startTime);

		// STEP 6: Create exp
		//$exp = implode('', $strings);

		if ($cache->exists('suppleConnection_evaluate_exp', $exp)){
			SuppleApplication::getlog()->logEndTime('suppleConnection_evaluate_exp_cacheHit', $allTime);
			return $cache->get('suppleConnection_evaluate_exp', $exp);
		} else {
		
			if (($exp == $condition) && is_variable($exp)){
				$r = ""; // OR SOMETHING LIKE THIS:  "'$exp'" ???
			} else {
				// STEP 7: EVALUATE
				//echo htmlentities(print_r($rec, true));
				//echo "EXP: $exp  COND: $condition <br>";
/*
				$bt = debug_backtrace();
				foreach ($bt as $dt) {
					// [file] => C:\Users\Shorsh\Dropbox\CrazyAnt\Supple2\include\SuppleConnection.php [line] => 279 [function] => evaluate [class] => SuppleConnection
					echo "FILE: ". $dt['file']."<br>";
					echo "LINE: ". $dt['line']."<br>";
					echo "FUNC: ". $dt['function']."<br>";
					echo "CLASS: ". $dt['class']."<br><br>";
				}
				echo "==================================";
				*/

				$r = eval("return ($exp);");
				//echo "$r <br>";
				$last_error = SuppleApplication::getlog()->getLastPhpError();
				//if ($last_error) echo "LAST ERROR $last_error <br>";
			}
			$cache->set('suppleConnection_evaluate_exp', $exp, $r);
		}
		
		
		// ERROR LOG
		/*
		$log = SuppleApplication::getlog();
		$last_error = $log->getLastPhpError();
		if ($last_error && strpos($last_error, 'SuppleConnection.php')){
			$log->fatal("Error parsing CONDITION <strong>$condition</strong> EXPRESSION <strong>$exp</strong>");
		}
		*/
		// echo "Parsing CONDITION <strong>$condition</strong> EXPRESSION <strong>$exp</strong><br>";
		}
		
		// OLD STORE IN CACHE:
		// $this->cachel1[$condition_string][$rec_string] = $r;
		// NEW STORE IN CACHE:
		$cache->set('suppleConnection_evaluate', $key, $r);

		SuppleApplication::getlog()->logEndTime('suppleConnection_evaluate_cacheMiss', $allTime);
		//file_put_contents('suppleConnection_evaluate.log', $key.var_export(array($condition, $rec),true)."\n", FILE_APPEND);

		return $r;

	}

	function evaluateCondition($filters, $record){
	
		// NEW CACHE
		$cache = SuppleApplication::getcache();
		// UPGRADE commented-out
		$key = md5(var_export(array($filters, $record),true));
		if ($cache->exists('suppleConnection_evaluateCondition', $key)){
			return $cache->get('suppleConnection_evaluateCondition', $key);
		}

		$startTime = SuppleApplication::getlog()->getStartTime(); // log only misses
	
		$cond = true;

		if (is_array($filters)){
			foreach ($filters as $condition => $val){
				// CONVERT OP!
				$condition = $this->phpConvertOp($condition);

				if ($condition == ''){
					$cond = false;
				} elseif (is_array($val)){
				
					$sub_cond = false;
					foreach ($val as $v){
						$sub_cond = $sub_cond || ($this->evaluate($condition, $record) == $v);
						if ($sub_cond == true) break;
					}
					$cond = $cond && $sub_cond;
				
				} elseif ($val == ''){
					$cond = $cond && ($this->evaluate($condition, $record));
				} else {
					$evaluated = $this->evaluate($condition, $record);
					$cond = $cond && (($evaluated == $val && is_numeric($evaluated) === is_numeric($val)) || "'$evaluated'" === $val || '"'.$evaluated.'"' === $val);

					//var_export($filters); echo "<br>";
					//echo $condition."<br>";
					//echo var_export($evaluated,true)." VS ".var_export($val,true)." <br>";
					//echo $cond."<br><br>";
					
					
				}
				if ($cond == false) break;
			}
		} else {
			// Condition is not array! is a value
			$cond = ($filters == true);

		}
		
		// OLD
		// $this->cachel2[$filters_string][$record_string] = $cond;
		// NEW STORE IN CACHE:
		$cache->set('suppleConnection_evaluateCondition', $key, $cond);
		
		SuppleApplication::getlog()->logEndTime('suppleConnection_evaluateCondition_cacheMiss', $startTime);
		//file_put_contents('suppleConnection_evaluateCondition_cacheMiss.log', $key.var_export(array($filters, $record),true)."\n", FILE_APPEND);

		return $cond;
	}

}

class SuppleResultset {
	
	var $conn;
	var $table;
	var $filter;
	var $orderFields;
	var $groupByFields;
	var $funtions;
	var $limitStart;
	var $limitCount;
	var $reverse;
	
	function __construct($connection, $table){
		$this->conn = $connection;
		$this->table = $table;
		$this->filter = array();
		$this->orderFields = array();
		$this->groupByFields = array();
		$this->funtions = array();
		$this->limitCount = 0; // Implica poner todos.
		$this->limitStart = 0; 
		$this->reverse = false;
		
		$this->clear();
		$this->reset();
		
	}

	function where($filter){
		// Example where(array('nombre' => 'Jorge', 'edad > 18' => true))
		// Mix the filter with the existing filters.
		$this->filter = mixArrays($this->filter, $filter);
		$this->clear();
		return $this;
	}

	function orderBy($fields){
		if (is_array($fields)){
			// Example orderBy(array('nombre','edad'))
			$this->orderFields = mixArrays($this->orderFields, $fields);
		} else {
			// Example orderBy('nombre')
			$this->orderFields = mixArrays($this->orderFields, array($fields));
		}
		$this->clear();
		return $this;
	}

	function groupBy($fields){
		// Example groupby(array('edad'))
		$this->groupByFields = mixArrays($this->groupByFields, $fields);
		$this->clear();
		return $this;
	}

	// Algo como array('edad' => 'avg', 'precio' => 'max', 'cantidad' => 'sum')
	function applyFunction($functions){
		$this->funtions = mixArrays($this->funtions, $functions);
		$this->clear();
		return $this;
	}
	
	//
	function reverse(){
		$this->reverse = true;
		$this->clear();
		return $this;
	}

	// RANDOM
	function random(){
		$this->random = true;
		$this->clear(); // why?
		return $this;
	}

	function array_random($a){
		shuffle($a);
		/*
		$len = count($a);
		$indexes = array_keys($a);
		for ($x=0;$x<2;$x++){
			foreach ($a as $i => $v){
				$r = rand(0, $len-1);
				$t = $indexes[$r];
				// echo "$i => $t <br>";
				$aux = $a[$i];
				$a[$i] = $a[$t];
				$a[$t] = $aux;
			}
		}
		*/
		return $a;
	}

	// Sinónimo de $this->limits(x,y); ¿Cuál es x e y? (0, $count) ó (1, $count + 1)
	// RTA: (0, $count)
	function limit($count){
		return $this->limits(0, $count);
	}

	function limits($start, $count){
		if (is_numeric($start) && is_numeric($count)){
			$this->limitStart = (int) $start;
			$this->limitCount = (int) $count;
		}
		$this->clear();
		return $this;
	}
	
	
	function getValue($field){
		$this->limit(1);
		$r = $this->getRow();
		return $r[$field];
	}
	
	// Get a row and wait for the next (always used with eof)
	function getRow(){}
	
	// Erase the obtained data
	function clear(){}
	
	// Place the pointer at the beginning
	function reset(){}
	
	// Are we there yet?
	function eof(){}
	
	// return a php array with the full result.
	function getArray($sort = true){}
	
	function getCount(){}

	function getBean($light = false){

		$startTime = SuppleApplication::getlog()->getStartTime();
		$row = $this->getRow();
		SuppleApplication::getlog()->logEndTime('suppleConnection_getrowForBean', $startTime);

		$startTime = SuppleApplication::getlog()->getStartTime();
		$bean = SuppleBean::getBean($this->table);// new SuppleBean($this->table);
		$bean->populate($row);
		SuppleApplication::getlog()->logEndTime('suppleConnection_prepareBean', $startTime);
		return $bean;
	}

	function getBeans($light = false){
		
		$startTime = SuppleApplication::getlog()->getStartTime();
		$rows = $this->getArray();
		SuppleApplication::getlog()->logEndTime('suppleConnection_getarrayForBean', $startTime);

		$startTime = SuppleApplication::getlog()->getStartTime();
		$r = array();
		foreach ($rows as $row){
			$bean = SuppleBean::getBean($this->table); // new SuppleBean($this->table);
			$bean->populate($row);
			$r[] = $bean;
		}
		SuppleApplication::getlog()->logEndTime('suppleConnection_prepareBean', $startTime);
		return $r;
	}
	
	// TODO!: Move to SuppleFieldDropdown at after_retrieve
	function complementData(&$data){
	
		// avoid infinite loop
		if (substr($this->table, 0, 1) == '_') return;
	
		$con = SuppleApplication::getdb();
		// Complement Dropdown fields from $this->table
		$entities = $con->from('_entities')->where(array('table' => $this->table))->getArray();
		foreach ($entities as $ent){
			$fields = $con->from('_fields')->where(array('parent' => $ent['id'], 'type' => 14))->getArray();
			foreach ($fields as $field){
				foreach ($data as $i => $row){
					foreach ($row as $f => $v){
						if ($f == $field['name'] && (!empty($v))){
							$data[$i][$f . "_value"] = translateOptions(chr($field['sep2']), chr($field['sep1']), $field['options'], $v );
						}
					}
				}
			}
		}

	}
	
}


