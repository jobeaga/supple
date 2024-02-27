<?php

/*
	Se encarga de gestionar cada diferente acci�n, independientemente del script que invoc� la acci�n.
	Clase abstracta: Implementa Factory y define m�todos reusables.
*/
require_once('include/SuppleApplication.php');
require_once('include/SuppleObject.php');

abstract class SuppleAction extends SuppleObject {

	private static $action_list;
	
	public $name = '';
	public $domain = 'global'; // global = everybody can, user = any logged user can, table = needs to have permission and depends on table
	public $needACL = true;
	
	function __construct(){

		parent::__construct();

	}	
	
	public static function getAction($action){
	
		$list = self::getActionList();
		
		if (isset($list[$action])){
		
			$fileName = $list[$action]['fileName'];
			$className = $list[$action]['className'];
		
			// require
			require_once($fileName);
			
			// new
			$action = new $className();
			
			// return
			return $action;
			
		} else {
		
			// SuppleApplication::getlog()->fatal("Action not found: $action");
			return null;
		
		}
	
	}
	

	public static function getActionList(){
	
		if (empty(self::$action_list)){
	
			self::$action_list = array();
		
			$a = glob('include/actions/*.php');
			foreach ($a as $file){
			
				require_once($file);
				$className = getFileName($file);
				if (class_exists($className)){
					$object = new $className();
					if (isset($object->name)){
						$actionName = $object->name;
						self::$action_list[$actionName]['fileName'] = $file;
						self::$action_list[$actionName]['className'] = $className;
						self::$action_list[$actionName]['actionName'] = $actionName;
						self::$action_list[$actionName]['id'] = $actionName;
						self::$action_list[$actionName]['domain'] = (isset($object->domain))?$object->domain:'';
						self::$action_list[$actionName]['needACL'] = (isset($object->needACL))?$object->needACL:true;
						
					}
				}
			}

			// CUSTOM ACTIONS: Adds and Overrides
			$a = glob('custom/actions/*.php');
			foreach ($a as $file){
				require_once($file);
				$className = getFileName($file);
				if (class_exists($className)){
					$object = new $className();
					if (isset($object->name)){
						$actionName = $object->name;
						self::$action_list[$actionName]['fileName'] = $file;
						self::$action_list[$actionName]['className'] = $className;
						self::$action_list[$actionName]['actionName'] = $actionName;
						self::$action_list[$actionName]['id'] = $actionName;
						self::$action_list[$actionName]['domain'] = (isset($object->domain))?$object->domain:'';
						self::$action_list[$actionName]['needACL'] = (isset($object->needACL))?$object->needACL:true;
					}
				}
			}
		}
		
		return self::$action_list;
		
	}
	
	public function perform(){
	
		if (SuppleApplication::prepareValues('table', $table, $post, $get)){
	
			return $this->performWith($table, $get, $post);

		}
	
	}

	public function performWith($table, $filter, $data, &$recursion_history = array()){

		if (!$this->inRecursionHistory($table, $filter, $data, $recursion_history)){

			$recursion_history = $this->addRecursionHistory($table, $filter, $data, $recursion_history);

			return $this->performAction($table, $filter, $data, $recursion_history);

		}

	}

	public function processDependencies($table, $processed_data, $data, &$recursion_history){
		
		$dependencies = $this->getDependencies($table);

		foreach ($dependencies as $i => $rel_data){

			foreach ($processed_data as $row){

				$initial_field = $rel_data['initial_field'];

				if ($initial_field){
					
					$final_field = $rel_data['final_field'];
					$final_table = $rel_data['final_table'];
					$initial_id = (isset($row[$initial_field]))?$row[$initial_field]:'';

					if ($initial_id){

						// Process from final table
						if ($final_field && $final_table){
							$final_where = array($final_field => $initial_id);
							// echo $final_table . " -!- ". print_r($final_where, true). "<br>";
							$this->performWith($final_table, $final_where, $data, $recursion_history);
						}
					}
				} 
			}
		}

		/*foreach ($dependencies as $rel_table => $rel_data){

			foreach ($processed_data as $row){

				$initial_field = $rel_data['initial_field'];

				if ($initial_field){
					
					$rel_column = $rel_data['rel_column'];
					$final_field = $rel_data['final_field'];
					$final_table = $rel_data['final_table'];
					$initial_id = (isset($row[$initial_field]))?$row[$initial_field]:'';

					if ($initial_id){

						// Process from relationship table
						if ($rel_column && $rel_table){
							$rel_where = array($rel_column => $initial_id);
							echo $rel_table . " -- ". print_r($rel_where, true). "<br>";
							$this->performWith($rel_table, $rel_where, $data, $recursion_history);
						}

						// Process from final table
						if ($final_field && $final_table){
							$final_where = array($final_field => $initial_id);
							echo $final_table . " -- ". print_r($final_where, true). "<br>";
							$this->performWith($final_table, $final_where, $data, $recursion_history);
						}
					}
				} 
			}
		}
		*/
	}

	public function getDependencies($table){
		$r = array();
		
		$this->db->generateEntityRelCache();

		foreach ($this->db->rel_cache as $rtable => $rels) {

			if (isset($rels[$table])) {

				$rel = $rels[$table];
				$final_table = $rel['final_table']; // rhs table

				// Find relationships for those entities with triggers
				if ($rel['trigger_delete'] == 1) {
					$r[$rtable] = array(
						'final_table' => $final_table,
						'final_field' => $rel['final_field'],
						'initial_field' => $rel['initial_field'],
					);
				}

				// Find relationships where rel table name is different from lhs and rhs tables
				if ($table != $rtable && $rtable != $final_table) {

					$r[$rtable] = array(
						'final_table' => $rtable,
						'final_field' => $rel['rel_column'],
						'initial_field' => $rel['initial_field'],	
					);
					
				}

			}
		}

		return $r;

/*
		// Find entities for this table
		$entities = $this->db->from('_entities')->where(array('table' => $table))->getArray();
		foreach ($entities as $entity_row){
			$entity_id = $entity_row['id'];

			$deps = $this->db->from('_relationships')->getArray();

			foreach ($deps as $d){

				$rtable = $d['rel_table'];

				if (isset($this->db->rel_cache[$rtable][$table])) {

					$rel = $this->db->rel_cache[$rtable][$table];
					$final_table = $rel['final_table']; // rhs table

					// Find relationships for those entities with triggers
					if ($d['id_a'] == $entity_id && $d['trigger_delete'] == 1) 
						$r[$rtable] = array(
							'final_table' => $final_table,
							'final_field' => $rel['final_field'],
							'initial_field' => $rel['initial_field'],
						);
				
					// Find relationships where table name is different from lhs and rhs tables	
					if ($table != $rtable && $table != $final_table && $rtable != $final_table) {

						if ($d['id_a'] == $entity_id) {
							$r[$rtable] = array(
								'final_table' => $rtable,
								'final_field' => $rel['rel_column'],
								'initial_field' => $rel['initial_field'],	
							);
						} else if ($d['id_b'] == $entity_id) {
							$r[$rtable] = array(
								'final_table' => $rtable,
								'final_field' => $rel['final_column'],
								'initial_field' => $rel['final_field'],	
							);
						}

					}

				}

			}

			

		}
		

		return $r;
		*/
	}
	
	public function addRecursionHistory($table, $filter, $data, $recursion_history){
		$recursion_history[] = $this->generateRecursionHistory($table, $filter, $data);
	}

	public function inRecursionHistory($table, $filter, $data, $recursion_history){
		if (empty($recursion_history)) return false;
		$newRH = $this->generateRecursionHistory($table, $filter, $data);
		$r = false;
		foreach ($recursion_history as $rh){
			if ($rh == $newRH) $r = true;
		}
		return $r;
	}

	private function generateRecursionHistory($table, $filter, $data){
		return md5(print_r(array($table, $filter, $data),true));
	}
	


}


?>