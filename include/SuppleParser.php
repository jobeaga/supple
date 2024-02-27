<?php 

class SuppleParser {

	const OPEN_PAR = '(';
	const CLOSE_PAR = ')';
	const OPEN_CUR = '{';
	const CLOSE_CUR = '}';
	const OPEN_BRA = '[';
	const CLOSE_BRA = ']';

	var $tokens;
	var $usedVars = array();

	function tokenize($text) {

		//brackets, parentheses
		$r = '[\(\)\{\}\[\]]';
		  
		// Variables!
		$r .= '|[\\\\]*[\\$]+[a-zA-Z_]*[a-zA-Z0-9_]*\\.[a-zA-Z_]*[a-zA-Z0-9_]*';
		$r .= '|[\\\\]*[\\$]+[a-zA-Z_]*[a-zA-Z0-9_:]*';


		// text!
		$r .= '|[^$\(\)\[\]\{\}\\\\]*';

		preg_match_all('/' . $r . '/sm', $text, $result);
		  
		/*
		echo "<pre>";
		print_r($result[0]);
		echo "</pre><br>";
			*/
		return $result[0];
	  
	}
	
	function parse($text){
	
		$this->tokens = $this->tokenize($text);
		list($tree, $i) = $this->parseTree(); // parseTree(0, '', '');
		return $tree;
	
	}
	
	function parseTree($from = 0, $opening = '', $closing = ''){

		$subtree = array();
		$freeopen = 0;
	
		for ($i = $from; $i < count($this->tokens); $i++) {	
			$t = $this->tokens[$i];
			$next_t = (isset($this->tokens[$i+1]))?$this->tokens[$i+1]:'';
			$is_var = $this->isVar($t);

			if ($t == '$') $t = '$_';
			
			// TODO: Para qué se usa???? RTA: Para saber cuales son los parámetros determinantes (importante para la cache). El resultado del templateo se podría guardar siempre y cuando los determinantes no cambien ;)
			if ($is_var && !in_array($t, $this->usedVars)){
				$this->usedVars[] = $t;
			}
			
			if ($is_var && $next_t == self::OPEN_PAR){
			
				list($condition, $i) = $this->parseTree($i+2, self::OPEN_PAR, self::CLOSE_PAR);
				$subt = (isset($this->tokens[$i+1]))?$this->tokens[$i+1]:'';
				
				if ($subt == self::OPEN_CUR){
					list($block, $i) = $this->parseTree($i+2, self::OPEN_CUR, self::CLOSE_CUR);
					// iteration
					$this_node = array(
						'variable' => $t,
						'condition' => $condition,
						'multiple' => true,
						'block' => $block,
						'method' => false,
					);
					
					
				} else if ($subt == self::OPEN_BRA){
					list($block, $i) = $this->parseTree($i+2, self::OPEN_BRA, self::CLOSE_BRA);
					$subt = (isset($this->tokens[$i+1]))?$this->tokens[$i+1]:'';
					
					if ($subt == self::OPEN_BRA){
						list($block_else, $i) = $this->parseTree($i+2, self::OPEN_BRA, self::CLOSE_BRA);
						// then with else
						$this_node = array(
							'variable' => $t,
							'condition' => $condition,
							'multiple' => false,
							'block' => $block,
							'block_else' => $block_else,
							'method' => false,
						);
					
					} else {
						// then without else
						$this_node = array(
							'variable' => $t,
							'condition' => $condition,
							'multiple' => false,
							'block' => $block,
							'method' => false,
						);
					}
				} else {
					// TODO: Method invocation
					if (strpos($t, '.') !== false){
						// Method invocation is with a dot...	
						$this_node = array(
							'variable' => $t,
							'condition' => $condition,
							'multiple' => false,
							'method' => true,
						);
					} else if ($t != '$_') {
						// Without a dot we can register variables and don't erase them afterwards
						$this_node = array(
							'variable' => $t,
							'condition' => $condition,
							'multiple' => false,
							'method' => false,
						);	
					} else {
					
						$subtree[] = '$';
						$subtree[] = self::OPEN_PAR;
						foreach ($condition as $c){
							$subtree[] = $c;
						}
						$this_node = self::CLOSE_PAR;
					
					}
				}
			
			} else if ($is_var && $next_t == self::OPEN_CUR){
				list($block, $i) = $this->parseTree($i+2, self::OPEN_CUR, self::CLOSE_CUR);
				// iteration without condition
				$this_node = array(
					'variable' => $t,
					'condition' => array(),
					'multiple' => true,
					'block' => $block,
					'method' => false,
				);
			} else if ($is_var && $next_t == self::OPEN_BRA){
				list($block, $i) = $this->parseTree($i+2, self::OPEN_BRA, self::CLOSE_BRA);

				$subt = (isset($this->tokens[$i+1]))?$this->tokens[$i+1]:'';
					
					if ($subt == self::OPEN_BRA){
						list($block_else, $i) = $this->parseTree($i+2, self::OPEN_BRA, self::CLOSE_BRA);
						// Single iteration with else
						$this_node = array(
							'variable' => $t,
							'condition' => array(),
							'multiple' => false,
							'block' => $block,
							'block_else' => $block_else,
							'method' => false,
						);
					
					} else {
						// Single iteration without condition
						$this_node = array(
							'variable' => $t,
							'condition' => array(),
							'multiple' => false,
							'block' => $block,
							'method' => false,
						);
					}
				
			} else /* if ($t == self::OPEN_PAR) {
				
				// CONDITION WITH NO VARIABLE
				$old_i = $i;
				list($condition, $i) = $this->parseTree($i+1, self::OPEN_PAR, self::CLOSE_PAR);
				$subt = (isset($this->tokens[$i+1]))?$this->tokens[$i+1]:'';

				if ($subt == self::OPEN_CUR){
					list($block, $i) = $this->parseTree($i+2,  self::OPEN_CUR, self::CLOSE_CUR);
					// iteration ???
					$this_node = array(
						'condition' => $condition,
						'multiple' => true,
						'block' => $block,
						'method' => false,
					);
					
					
				} else if ($subt == self::OPEN_BRA){
					list($block, $i) = $this->parseTree($i+2, self::OPEN_BRA, self::CLOSE_BRA);
					$subt = (isset($this->tokens[$i+1]))?$this->tokens[$i+1]:'';
					
					if ($subt == self::OPEN_BRA){
						list($block_else, $i) = $this->parseTree($i+2,  self::OPEN_BRA, self::CLOSE_BRA);
						// then with else
						$this_node = array(
							'condition' => $condition,
							'multiple' => false,
							'block' => $block,
							'block_else' => $block_else,
							'method' => false,
						);
					
					} else {
						// then without else
						$this_node = array(
							'condition' => $condition,
							'multiple' => false,
							'block' => $block,
							'block_else' => array(),
							'method' => false,
						);
					}
				} else {
					// PARENTHESIS with no body
					$subtree[] = self::OPEN_PAR;
					foreach ($condition as $c){
						$subtree[] = $c;
					}
					$this_node = self::CLOSE_PAR;
				}

			} else */ if ($t == $closing && (!empty($closing)) && $freeopen == 0){
				// closing block
				return array($subtree, $i);
			
			} else if ($is_var){
				// simple variable
				$this_node = $t;
			} else {
				// Regular text with variables:
				if ($t == $opening && (!empty($opening))) $freeopen++;
				if ($t == $closing && (!empty($closing))) $freeopen--;
				$this_node = $t; // . " FO: $freeopen OP: $opening CL: $closing";
			}
			
			// Mods???
			if (is_array($this_node)) $this_node['mods'] = array();
			$next_t = (isset($this->tokens[$i+1]))?$this->tokens[$i+1]:'';
			while ($next_t == self::OPEN_PAR && is_array($this_node)){
				list($mod, $i) = $this->parseTree($i+2,  self::OPEN_PAR, self::CLOSE_PAR);
				list($op, $mod_subtree) = $this->parseMod($mod);
				$this_node['mods'][$op][] = $mod_subtree;
				$next_t = (isset($this->tokens[$i+1]))?$this->tokens[$i+1]:'';
			}
			$subtree[] = $this_node;
		} // END for ($i = $from; $i < count($this->tokens); $i++) {	
		
		return array($subtree, $i);
	}
	
	function parseMod($mod){
		$ops = array(
			'.', '+', '=', 
		);
		// TEST FOR LIMITS FIRST
		$is_limit = false;
		$lower_limit = array();
		$upper_limit = array();
		foreach ($mod as $m){
			// get lower
			if ($is_limit) {
				$upper_limit[] = $m;		
			}
			// test
			if (is_string($m) && strrpos($m, ',') !== false && !$is_limit){
				$is_limit = true;
				$i = strrpos($m, ',');
				$lower_limit[] = substr($m, 0, $i);
				$upper_limit[] = substr($m, $i + 1);
			} else if (is_string($m) && strrpos($m, ',') !== false) {
				// double comma? ERROR!
				$is_limit = false;
				break;
			}
			// get upper
			if (!$is_limit) {
				$lower_limit[] = $m;
			}
		}

		if ($is_limit){
			$op = ',';
			$mod = array('lower_limit' => $lower_limit, 
				     'upper_limit' => $upper_limit);
		} else if (isset($mod[0]) && in_array(substr($mod[0],0,1), $ops)){
			$op = substr($mod[0],0,1);
			$mod[0] = substr($mod[0],1);
		} else if (is_string($mod[0]) && strtoupper(trim($mod[0])) == 'REVERSE') {
			$op = 'REVERSE';
			$mod = array();
			
			// RANDOM
		} else if (is_string($mod[0]) && strtoupper(trim($mod[0])) == 'RANDOM') {
			$op = 'RANDOM';
			$mod = array();
		} else if (is_string($mod[0]) && strtoupper(substr(trim($mod[0]), 0, 8)) == 'GROUP BY') {
			$op = 'GROUP BY';
			$mod[0] = trim(substr(trim($mod[0]), 8));
		} else {
			$op = 'ORDER';
		}
		return array($op, $mod);
	
	}
	
	function parseTemplate(&$subtree, &$st, $parent_table = '', $parent_id = '', $is_condition = false, $safequotes = false){
	
		$result = '';
		foreach ($subtree as $i => $node){
			if (is_array($node)){
				$subresult = '';
				if (isset($node['variable']) && strlen($node['variable'])>1){
					$table = $this->getVariable($st, $node['variable']); // ?? USE THIS IN FIRST PARAMETER???
					if ($node['multiple']){
						// ANYTHING WITH CURLY BRACKETS
						$subresult .= $st->processMultiIteration($node['variable'], $node['condition'], $node['block'], $node['mods'], $parent_table, $parent_id);
					} else if (isset($node['block_else'])) {
						// SINGLE ELSE ITERATION AND IF ELSE
						$subresult .= $st->processSingleElseIteration($node['variable'], $node['condition'], $node['block'], $node['block_else'], $node['mods'], $parent_table, $parent_id);
					} else if (isset($node['block'])){
						// SINGLE ITERATION AND IF
						$subresult .= $st->processSingleIteration($node['variable'], $node['condition'], $node['block'], $node['mods'], $parent_table, $parent_id);
					} else if ($node['method']) {
						// execute method
						$subresult .= $st->processMethod($node['variable'], $node['condition'], $node['mods'], $parent_table, $parent_id);
					} else {
						// fetch ONE row 
						$subresult .= $st->processFetch($node['variable'], $node['condition'], $node['mods'], $parent_table, $parent_id);
					}
					

				} /*else {
					if (isset($node['block_else'])){
						$subresult .= $st->processIfElse($node['condition'], $node['block'], $node['block_else'], $node['mods'], $parent_table, $parent_id);
					} else {
						$subresult .= $st->processIf($node['condition'], $node['block'], $node['mods'], $parent_table, $parent_id);
					}
				}*/
				if (!empty($node['mods']['='])){
					// Asignación
					foreach($node['mods']['='] as $asig) {
						$asigvar = $st->parseTemplate($asig);
						$st->setValue($asigvar, $subresult);
					}
				} else {
					// Concatenación al output
					$result .= $subresult;
				}
				
			} else if ($this->isVar($node)){
				if ($is_condition){
					$result .= '"'.str_replace('"', '', $this->getValue($st, $node)).'"';
				} else if ($safequotes) {
					// echo $this->getValue($st, $node)."<br><br>";
					$sq = safequotes($this->getValue($st, $node));
					if (!is_array($sq)) $result .= $sq;
				} else if ($node == '$'){
					$result .= $node;
				} else {
					$v = $this->getValue($st, $node);
					if (is_array($v)){
						$result .= 'Array';
					} else {
						$result .= $v;
					}
				}
			} else {
				$result .= $this->pseudoStripSlashes($node); // TODO: strip slashes
			}
		}

		return $result;
		
	}

	function isVar($t){
		$r = '[\\$]+[a-zA-Z_]*[a-zA-Z0-9_]*\\.[a-zA-Z_]*[a-zA-Z0-9_]*';
		$r .= '|[\\$]+[a-zA-Z_]*[a-zA-Z0-9_]*';
		return (preg_match('/' . $r . '/', $t) == 1 && substr($t, 0, 1) != '\\');
	}
	
	function getValue(&$st, $var){
		$value = '';
		$varname = $this->getVariable($st, $var);
		if ($st->issetValue($varname)){
			$value = $st->getValue($varname);
		}
		return $value;
	}
	
	function getVariable(&$st, $var){
		$varname = substr($var, 1);
		if ($this->isVar($varname)){
			$varname = $this->getValue($st, $varname);
		} 
		return $varname;
	}
	
	function pseudoStripSlashes($string){
		return str_replace('\$', '$', $string);
	}
	
}
