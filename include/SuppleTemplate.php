<?php

require_once('include/util.php');
require_once('include/SuppleApplication.php');
require_once('include/SuppleParser.php');

class SuppleTemplate {

	var $parseTree;
	var $parseString;
	var $vars;
	var $beans;
	var $connection;
	var $parser;

	function __construct(){

		// $this->parseTree = array();
		$this->vars = array();
		$this->beans = array();
		// ¿Y si le pongo las variables por get?
		foreach ($_GET as $key => $value){ $this->setValue("_get.".$key, $value); }
		// ¿Y post? ¿por qué no?
		foreach ($_POST as $key => $value){ $this->setValue("_post.".$key, $value); }

		$global_data = SuppleGlobal::getData();
		foreach ($global_data as $name => $data){
			foreach ($data as $key => $value){ $this->setValue('_'.$name.'.'.$key, $value); }
		}
		
		$this->connection = SuppleApplication::getdb();
		
		$this->parser = new SuppleParser();
		
	}

	public function parseFile($htmlFilename, $data = array()){

		// La data puede incluir informacion global, u obtenida de varios registros,
		// o datos de la sesion, o lo que sea.

		$result = '';

		if (file_exists($htmlFilename)){
		
			$string = file_get_contents($htmlFilename, 'FILE_BINARY');
		
			if (ord(substr($string, 0, 1)) == 239)
				$string = utf8_decode($string); // Perdón, pero quiero todo en ascii!

			$result = $this->parseString($string, $data, $htmlFilename);

		}

		return $result;

	}

	function parseString($string, $data = array(), $htmlFilename = ''){

		$cache = SuppleApplication::getcache();
		
		$startTime = SuppleApplication::getlog()->getStartTime();

		$tree_key = $cache->create_md5($string);
		if ($cache->exists('parseString_generateParseTree', $tree_key)){
			$this->parseTree = $cache->get('parseString_generateParseTree', $tree_key);
			$status = 'hit'; 
		} else {
			$this->parseString = $string;
			$this->generateParseTree();
			$cache->set('parseString_generateParseTree', $tree_key, $this->parseTree);
			$status = 'miss';
		}

		SuppleApplication::getlog()->logEndTime('generateParseTree_'.$status, $startTime);


		// TODO: this parameter to config, and the output to log
		/*
		if (isset($_GET['mode']) && $_GET['mode'] == 'debug'){
			echo "<pre>";
			print_r($this->parseTree);
			echo "</pre> ===============>";
		} 
		*/

		$startTime = SuppleApplication::getlog()->getStartTime();
		
		$this->setValues($data);

		$key = $cache->create_md5(array($this->getData(), $tree_key)); // tricky!
		if ($cache->exists_ram('parseString_parseTemplate', $key)){
			$r = $cache->get_ram('parseString_parseTemplate', $key);
			$status = 'hit';
		} else {
			$r = $this->parseTemplate($this->parseTree);
			$cache->set_ram('parseString_parseTemplate', $key, $r);
			$status = 'miss';
		}
		
		SuppleApplication::getlog()->logEndTime('parseTemplate_'.$status, $startTime);

		return $r;

	}
	
	function generateParseTree(){
		$this->parseTree = $this->parser->parse($this->parseString);
	}
	
	function parseTemplate(&$subtree, $parent_table = '', $parent_id = '', $is_condition = false, $safequotes = false){
		return $this->parser->parseTemplate($subtree, $this, $parent_table, $parent_id, $is_condition, $safequotes);
	}

	// VALUES
	function issetValue($varname){
		return isset($this->vars[$varname]);
	}
	function setValue($varname, $value, $table = '', $alias = ''){
		$new_value = array('value' => $value);
		if ($this->issetValue($varname)){
			$new_value['previous'] = $this->vars[$varname];
		}
		$this->vars[$varname] = $new_value;
		if ($table && is_string($table)) $this->setValue($table.".".$varname, $value);
		if ($alias && is_string($alias)) $this->setValue($alias.".".$varname, $value);
	}
	function setValues($array, $table = '', $alias = ''){
		$data = array();
		if (is_object($array)) {
			$data = $array->getData(true, true);
		} else if (is_array($array)) {
			$data = $array;
		}
		foreach ($data as $varname => $value){
			$this->setValue($varname, $value, $table, $alias);
		}
	}
	function getValue($varname){
		if ($this->issetValue($varname)){
			return $this->vars[$varname]['value'];
		} else {
			return '';
		}
	}
	function unsetValue($varname, $table = '', $alias = ''){
		if ($this->issetValue($varname)){
			if (isset($this->vars[$varname]['previous'])){
				$this->vars[$varname] = $this->vars[$varname]['previous'];
			} else {
				unset($this->vars[$varname]);
			}
		} // else nothing to do!
		if ($table && is_string($table)) $this->unsetValue($table.".".$varname);
		if ($alias && is_string($alias)) $this->unsetValue($alias.".".$varname);
	}
	function unsetValues($array, $table = '', $alias = ''){
		$data = array();
		if (is_object($array)) {
			$data = $array->getData(true, true);
		} else if (is_array($array)) {
			$data = $array;
		}
		foreach ($data as $varname => $value){
			$this->unsetValue($varname, $table, $alias);
		}
	}
	function getData(){
		$data = array();
		foreach ($this->vars as $varname => $v){
			$data[$varname] = $this->getValue($varname);
		}
		return $data;
	}
	// END VALUES
	// BEANS
	function issetBean($table){
		if (is_object($table) || is_array($table)){
			return false;
		} else {
			return isset($this->beans[$table]);
		}
	}
	function setBean($bean, $table, $alias = ''){
		if (is_object($table) || is_array($table)) return;
		$new_value = array('bean' => $bean);
		if ($this->issetBean($table)){
			$new_value['previous'] = $this->beans[$table];
		}
		$this->beans[$table] = $new_value;
		if ($alias && is_string($alias)) $this->setBean($bean, $alias);
	}
	function getBean($table){
		if ($this->issetBean($table)){
			return $this->beans[$table]['bean'];
		} else {
			return SuppleBean::getBean($table); // new bean!
		}
	}
	function unsetBean($table, $alias = ''){
		if ($this->issetBean($table)){
			if (isset($this->beans[$table]['previous'])){
				$this->beans[$table] = $this->beans[$table]['previous'];
			} else {
				unset($this->beans[$table]);
			}
		} // else nothing to do!
		if ($alias && is_string($alias)) $this->unsetBean($alias);
	}
	// END BEANS

	function parseConditionIntoArray($cond){
		$a = explode("&&", $cond);

		for ($i = 0; $i < count($a) - 1; $i++){
			if ((substr_count($a[$i], "'") % 2) == 1 || (substr_count($a[$i], '"') % 2) == 1){
				$a[$i] = $a[$i]."&&".$a[$i+1];
				unset($a[$i+1]);
			}
		}
		$where = array();
		foreach ($a as $each_condition){
			$b = explode("==", $each_condition);
			if (count($b) == 2 && (substr_count($b[0], "'") % 2) == 0 && (substr_count($b[0], '"') % 2) == 0 && (!isset($where[trim($b[0])]))){
				// echo "EC: ".$each_condition."<br>";
				
				// $where[$each_condition] = '';
				$where[trim($b[0])] = trim($b[1]);

			} else {
				$where[$each_condition] = '';
			}
		}
		return $where;
		
	}
	
	// PROCESS METHODS
	function processMultiIteration($table, $condition, $block, $mods, $parent_table = '', $parent_id = ''){

		// TODO???: SPECIAL TABLES! Metatables (parse, eval, include), values arrays, properties arrays, values iterations (numbers)

		$startTime = SuppleApplication::getlog()->getStartTime();
		$type = '';
		
		list($table, $alias) = $this->splitTableAlias($table);

		$table = $this->parser->getVariable($this, $table);
		$iteration_data = array();

		$subparent_table = '';
		$subparent_id = '';
		$result = '';

		if ($this->isSpecialTable($table)){
			$type = 'specialTable'.$table;

			$result = $this->processSpecialTable($table, $condition, $block, array(), $mods, $parent_table, $parent_id);

		} else if (is_array($this->getValue($table))){
			$type = 'arrayValue';
			// ARRAY: ¿array of rows or properties?
			// TODO: Process with a connection. 
			$d = $this->getValue($table);
			$v = array_values($d);
			$first_value = array_shift($v);
			if (is_array($first_value)){
				$iteration_data  = $d;
			} else {
				foreach ($d as $key => $value){
					$iteration_data[] = array('key' => $key, 'value' => $value,);
				}
			}

		} else if (is_numeric($table) || is_numeric($this->getValue($table))) {
			$type = 'numeric';
			// NUMBER: that number of iterations
			$number = (is_numeric($table))?$table:$this->getValue($table);
			for ($i = 0; $i < $number; $i++) {
				$iteration_data[] = array('number' => $i);
			}
		} else {
			$type = 'query';
			// Query!!!
			$where = $this->prepareWhere($table, $condition);
		
			$q = $this->connection->from($table, $parent_table, $parent_id);
		
			if ($where){
				$q->where($where);
			}
		
			$this->prepareMods($q, $mods, $parent_table, $parent_id);

			$iteration_data = $q->getBeans(true);

			$subparent_table = $table;
		}

		
		foreach ($iteration_data as $bean){
			
			if (is_object($bean)) {
				$subparent_id = $bean->id;
				$this->setBean($bean, $table, $alias);
			} else if (is_array($bean)) {
				$subparent_id = (isset($bean['id']))?$bean['id']:'';
			} else {
				$subparent_id = '';
			}
			$this->setValues($bean, $table, $alias);

			SuppleApplication::getlog()->logEndTime('template_process_'.$type, $startTime); // LOG

			$result .= $this->parseTemplate($block, $subparent_table, $subparent_id); // DO NOT RECORD TIME FOR SUBCALL

			$startTime = SuppleApplication::getlog()->getStartTime();

			$this->unsetValues($bean, $table, $alias);
			$this->unsetBean($table, $alias);

		}
		
		SuppleApplication::getlog()->logEndTime('template_process_'.$type, $startTime); // LOG

		return $result;
	
	}
	
	
	function processSingleElseIteration($table, $condition, $block, $block_else, $mods, $parent_table = '', $parent_id = ''){

		$startTime = SuppleApplication::getlog()->getStartTime();
		$type = '';

		list($table, $alias) = $this->splitTableAlias($table);

		$table = $this->parser->getVariable($this, $table);
		$row = array();

		$result = '';
		$subparent_table = '';
		$subparent_id = '';

		if ($this->isSpecialTable($table)){
			$type = 'specialTable'.$table;

			$result = $this->processSpecialTable($table, $condition, $block, $block_else, $mods, $parent_table, $parent_id);

		} else if (is_array($this->getValue($table))){
			$type = 'arrayValue';
			// ARRAY: ¿array of rows or properties?
			// TODO: Process with a connection. 
			$key = $this->getValue($table);
			if (is_array($table) && is_array(array_shift(array_values($table)))){
				$row  = array_shift(array_values($table));
			} else if (is_array($table)) {
				foreach ($table as $key => $value){
					$bean = new SuppleBean();
					$bean->id = 1;
					$bean->key = $key;
					$bean->value = $value;
					break;
				}
			}

		} else if (is_numeric($table) || is_numeric($this->getValue($table))) {
			$type = 'numeric';
			// NUMBER: that number of iterations
			$number = (is_numeric($table))?$table:$this->getValue($table);
			if ($number) {
				$bean = new SuppleBean();
				$bean->id = 1;
				$bean->number = $i;
			}
		} else {
			$type = 'query';
			// Query!!!
			$where = $this->prepareWhere($table, $condition);
		
			$q = $this->connection->from($table, $parent_table, $parent_id);
		
			if ($where){
				$q->where($where);
			}
		
			$this->prepareMods($q, $mods, $parent_table, $parent_id); 

			$bean = $q->getBean(true);

			$subparent_table = $table;
		}

		SuppleApplication::getlog()->logEndTime('template_process_'.$type, $startTime); // LOG

		if (isset($bean)){
			if ($bean->id){
				$subparent_id = $bean->id;
				$this->setValues($bean, $table, $alias);
				$result .= $this->parseTemplate($block, $subparent_table, $subparent_id);
				$this->unsetValues($bean, $table, $alias);
			} else {
				$result .= $this->parseTemplate($block_else, $parent_table, $parent_id);
			}
		}
		
		return $result;
	}
	
	function processSingleIteration($table, $condition, $block, $mods, $parent_table = '', $parent_id = ''){
		return $this->processSingleElseIteration($table, $condition, $block, array(), $mods, $parent_table, $parent_id);
	}
	
	function processIfElse($condition, $block, $block_else, $mods, $parent_table = '', $parent_id = ''){

		
		$type = 'ifelse';

		$r = "";
		
		// $condition can be a very complex template
		$c = $this->parseTemplate($condition, $parent_table, $parent_id, true);

		
		$startTime = SuppleApplication::getlog()->getStartTime();
		if ($this->connection->evaluateCondition(array($c => ''), $this->getData())) {
			SuppleApplication::getlog()->logEndTime('template_process_'.$type, $startTime); // LOG		

			$r = $this->parseTemplate($block, $parent_table, $parent_id);
		
		} else {
			SuppleApplication::getlog()->logEndTime('template_process_'.$type, $startTime); // LOG

			$r = $this->parseTemplate($block_else, $parent_table, $parent_id);

		}

		// Q: Process mods??? A: No!


		
		return $r;

	}
	
	function processIf($condition, $block, $mods, $parent_table = '', $parent_id = ''){
		$this->processIfElse($condition, $block, array(), $mods, $parent_table, $parent_id);		
	}

	function processMethod($table, $condition, $mods, $parent_table = '', $parent_id = ''){
		$r = '';
		$method = substr($table, strrpos($table, '.')+1);
		$table = substr($table, 1, strrpos($table, '.')-1);
		$bean = $this->getBean($table); // registered or new
		if (method_exists($bean, $method)){
			$rf = new ReflectionMethod(get_class($bean), $method);
			if ($rf->getNumberOfParameters() > 0){
				$c = $this->parseTemplate($condition, $parent_table, $parent_id, true);
				return $bean->$method($c);
			} else {
				return $bean->$method();
			}
		}
		return $r;
	}

	function processFetch($table, $condition, $mods, $parent_table = '', $parent_id = ''){
		
		list($table, $alias) = $this->splitTableAlias($table);

		$table = $this->parser->getVariable($this, $table);
		$row = array();

		$result = '';
		$subparent_table = '';
		$subparent_id = '';

		if (is_array($this->getValue($table))){
			$type = 'arrayValue';
			// ARRAY: ¿array of rows or properties?
			// TODO: Process with a connection. 
			$key = $this->getValue($table);
			if (is_array(array_shift(array_values($table)))){
				$row  = array_shift(array_values($table));
			} else {
				foreach ($table as $key => $value){
					$row = array('key' => $key, 'value' => $value,);
					break;
				}
			}

		} else if (is_numeric($table)) {
			$type = 'numeric';
			// NUMBER: that number of iterations
			for ($i = 0; $i < $table; $i++) {
				$row = array('number' => $i);
				break;
			}
		} else {
			$type = 'query';
			// Query!!!
			$where = $this->prepareWhere($table, $condition);
		
			$q = $this->connection->from($table, $parent_table, $parent_id);
		
			if ($where){
				$q->where($where);
			}
		
			$this->prepareMods($q, $mods, $parent_table, $parent_id); 

			$bean = $q->getBean(true);

			$subparent_table = $table;
		}

		if (isset($bean)){
			if ($bean->id){
				// $subparent_id = $bean->id;
				if (is_object($bean)) {
					$this->setBean($bean, $table, $alias);
				}
				$this->setValues($bean, $table, $alias);
				// NO BODY, AND NO UNSET:
				//$result .= $this->parseTemplate($block, $subparent_table, $subparent_id);
				//$this->unsetValues($bean, $table, $alias);
			}
		}
		
		return $result;
	}
	
	function prepareWhere(&$table, $condition, $parent_table = '', $parent_id = ''){
		
		$startTime = SuppleApplication::getlog()->getStartTime();

		$filter = $this->parseTemplate($condition, $parent_table, $parent_id);
		
		if ($condition){
			if (is_numeric($filter)){
				$where = array("'$filter'" => '');
			} else {
				$where = $this->parseConditionIntoArray($filter);
			}
		} else {
			$where = array();
		}

		SuppleApplication::getlog()->logEndTime('template_prepareWhere', $startTime);
		
		return $where;
	
	}
	
	function prepareMods(&$q, $mods, $parent_table = '', $parent_id = ''){

		$startTime = SuppleApplication::getlog()->getStartTime();

		$ops = array(
			'.' => 'count',
			'+' => 'sum',
		);
		$functions = array();
		foreach ($mods as $type => $mds){
			if (isset($ops[$type])) {
				foreach ($mds as $mod){
					$pmod = $this->parseTemplate($mod, $parent_table, $parent_id);
					// TODO: ALIAS de los campos agregados
					$functions[$pmod] = $ops[$type];
				}
			} else if ($type == 'REVERSE') {
				$q->reverse();

				// RANDOM
			} else if ($type == 'RANDOM') {
				$q->random();
			} else if ($type == 'GROUP BY') {
				$grupos = array();
				foreach ($mds as $mod){
					$grupos[] = $this->parseTemplate($mod, $parent_table, $parent_id);
				}
				$q->groupBy($grupos);
			} else if ($type == ',') {
				foreach ($mds as $mod){
					$lower_limit = $this->parseTemplate($mod['lower_limit'], $parent_table, $parent_id);
					$upper_limit = $this->parseTemplate($mod['upper_limit'], $parent_table, $parent_id);
				}
				$q = $q->limits($lower_limit, $upper_limit);
			} else if ($type != '=') { // ORDER!!
				$order = array();
				foreach ($mds as $mod){
					$m = $this->parseTemplate($mod, $parent_table, $parent_id);
					$parts = explode(' ', $m);
					if (count($parts) == 2 && strtoupper($parts[1]) == 'REVERSE'){
						$m = $parts[0];
						$q->reverse();
					}
					$order[] = $m;
				}

				$q->orderBy($order);
			}
		}
		if ($functions){
			$q->applyFunction($functions);
		}
		SuppleApplication::getlog()->logEndTime('template_prepareMods', $startTime);
		
	}

	function splitTableAlias($table){
		$a = strpos($table, ':');
		if ($a === false){
			return array($table, '');
		} else {
			// 0123456789
			// jorge:a
			return array(substr($table, 0, $a), substr($table, $a+1));
		}
	}

	function isSpecialTable($table){
		$special_tables = array('_parse', '_eval', '_include', '_htmlentities', '_');
		return (is_string($table) && in_array($table, $special_tables));

	}

	function processSpecialTable($table, $condition, $block, $block_else = array(), $mods = array(), $parent_table = '', $parent_id = ''){

		$r = '';

		if ($table == '_parse'){

			// PARSE TWICE!!!
			$parsed = $this->parseTemplate($block, $parent_table, $parent_id);
			$parsed = str_replace('\\', '', $parsed);
			$new_st = new SuppleTemplate();
			$r = $new_st->parseString($parsed, $this->getData());


		} else if ($table == '_eval'){

			// if (is_array($block)) echo "BLOCK: ".print_r($block);
			$exp = trim($this->parseTemplate($block, $parent_table, $parent_id, false, true));// trim(implode('', $block)); // DON'T PARSE???
			$r = $this->evalWith($exp, $this->getData());
			$r = unsafequotes($r);

		} else if ($table == '_include'){

			$filename = trim($this->parseTemplate($block, $parent_table, $parent_id));
			$folder = 'templates/include/';
			if (file_exists('custom/'.$folder.$filename)){
				$new_st = new SuppleTemplate();
				$r = $new_st->parseFile('custom/'.$folder.$filename, $this->getData());
			} else if (file_exists($folder.$filename)){
				$new_st = new SuppleTemplate();
				$r = $new_st->parseFile($folder.$filename, $this->getData());
			}

		} else if ($table == '_htmlentities'){

			$parsed = $this->parseTemplate($block, $parent_table, $parent_id);
			$chars = $this->parseTemplate($condition, $parent_table, $parent_id);
			$r = custom_htmlentities($parsed, $chars);

		} else if (!empty($condition)) {

			$r = $this->processIfElse($condition, $block, $block_else, $mods, $parent_table, $parent_id); // for IF and IF_ELSE as well
			
		} else {

			$r = $this->parseTemplate($block, $parent_table, $parent_id);

		}

		return $r;

	}

	function evalWith($_exp, $_data){
		global $log;
		// TODO: Apply security!!!
		foreach ($_data as $_k => $_v){
			$$_k = $_v;
		}
		//echo 'return '.$_exp.';';
		$_code = 'return '.$_exp.';';
		// echo htmlentities($_code)."<br>";
		//$log->fatal(htmlentities($_code));
		$r = eval($_code);

		// SuppleApplication::getlog()->fatal('EXPRESSION: '.$_exp);
		
		$log = SuppleApplication::getlog();
		$last_error = $log->getLastPhpError();
		if ($last_error && strpos($last_error, 'SuppleTemplate.php')){
			$log->fatal("Error eval CODE <strong>$_code</strong> EXPRESSION <strong>$_exp</strong>");
		}

		return $r;

	}


}
