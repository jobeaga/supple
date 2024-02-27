<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionRead extends SuppleAction {

	public $name = 'read';
	public $domain = 'table'; // bean over a table
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function performAction($table, $get, $post, &$recursion_history = array()){
		global $db;

		$r = array('error' => '', 'data' => array(), 'table' => '', 'count' => 0);

		$offset = 0;
		$count = 100;
		$order = '';
		$reverse = false;

		if (isset($get['offset'])) { $offset = $get['offset']; unset($get['offset']); }
		if (isset($get['count'])) { $count = $get['count']; unset($get['count']); }
		if (isset($get['order'])) { $order = $get['order']; unset($get['order']); }
		if (isset($get['reverse'])) { $reverse = $get['reverse']; unset($get['reverse']); }
		// order has reverse in it
		$e = explode(' ', $order);
		if (isset($e[1]) && strtolower($e[1]) == 'reverse') {
			$order = $e[0];
			$reverse = 1;
		}

		$pre_filter = array_merge($get, $post);
		$filter = $this->buildFilter($pre_filter, $table);

		//print_r($filter);
		
		// print_r($f);
		// Check if $table is a type 1 relationship
		$rel = $db->from('_relationships')->where(array('type' => 1, 'rel_table' => $table))->getBean();
		if (empty($rel->id) || (empty($pre_filter['id_a']) && empty($pre_filter['id_b']))){
			$r['data'] = $this->read($table, $filter, $offset, $count, $order, $reverse);
			$r['table'] = $table;
			$r['count'] = $this->getCount($table, $filter);
		} else {
			// echo "TEST $table";
			// CASO DE relaci�n con entidad
			$ent_rel = $db->from('_entities')->where(array('table' => $table))->getBean();
			// CASO DE same_entity:
			$same_entity = false;
			if (!empty($rel->id) && $rel->id_a == $rel->id_b && $rel->label_a == $rel->label_b) {
				$same_entity = true;
			} else if (!empty($pre_filter['id_a']) && !empty($pre_filter['id_b'])) {
				$same_entity = true;
			}
			if ($same_entity){
				// re-adapt the filter to OR condition
				if ($rel->type == 1){
					$field1 = 'id_a';
					$field2 = 'id_b';
				} else {
					$field1 = $rel->entity_a_field;
					$field2 = $rel->entity_b_field;
				}
				$value1 = $pre_filter[$field1];
				$value2 = $pre_filter[$field2];
				unset($pre_filter[$field1]);
				unset($pre_filter[$field2]);
				$filter = $this->buildFilter($pre_filter, $table);
				$filter["($field1=='$value1' || $field2=='$value2')"] = '';
			}
			// END CASO DE same_entity
			$data = $this->read($table, $filter, $offset, $count, $order, $reverse);
			
			$r['count'] = $this->getCount($table, $filter);
			$r['data'] = array();
			$r['table'] = ''; // en realidad es $etable, pero no lo vamos a calcular al cuete!, �o si?
			if ($data && empty($ent_rel->id)){
				if (empty($pre_filter['id_a'])){
					$rid = 'id_a';
					$rentity = $rel->id_a;
				} else {
					$rid = 'id_b';
					$rentity = $rel->id_b;
				}
				$ent = $db->from('_entities')->where(array('id' => $rentity))->getBean();
				$etable = $ent->table;
				// GET REAL DATA!
				$r['table'] = $etable;
				foreach ($data as $row){
					// CASO DE same_entity
					if ($same_entity){
						if ($row[$field1] == $value1){
							$id = $row[$field2];
						} else {
							$id = $row[$field1];
						}
						// END CASO DE same_entity
					} else {
						$id = $row[$rid];
					}
					$bean = $db->from($etable)->where(array('id' => $id))->getBean();
					if (!empty($bean->id)){
						$rdata = $bean->getData();
						$rdata['_nextid'] = '';
						$rdata['_previousid'] = '';
						$r['data'][] = $rdata;
					}
				}
				// SORT
				if ($order) sortArrayByField($r['data'], $order);
				if ($order && $reverse){
					$r['data'] = array_reverse($r['data']);
				}
				
				// CALCULATE NEXT AND PREV
				foreach ($r['data'] as $i => $row){
					if (isset($r['data'][$i-1])) $r['data'][$i-1]['_nextid'] = $row['id'];
					if (isset($r['data'][$i+1])) $r['data'][$i+1]['_previousid'] = $row['id'];
				}
			} else if (!empty($ent_rel->id)){
				// CASO DE relacion con entidad
				$r['data'] = $data;
				$r['table'] = $table;
			}
		}

		//echo json_encode($r);
		//die();
		
		return $r;

	}

	public function read($table, $filter, $offset, $count, $order, $reverse){
		$db = SuppleApplication::getdb();
		$r = array();

		if (!empty($table)){
			
			$q = $db->from($table);

			$q->limits($offset, $count);

			if (!empty($filter)){ $q->where($filter); }

			if (!empty($order)){ $q->orderBy($order); }

			if (!empty($reverse)){ $q->reverse(); }

			$beans = $q->getBeans(true);

			foreach ($beans as $bean){
				$data = $bean->getData();
				$data['_nextid'] = $bean->_nextid;
				$data['_previousid'] = $bean->_previousid;
				$r[] = $data;
			}

		}
		return $r;
	}

	public function getCount($table, $filter){
		$db = SuppleApplication::getdb();
		$c = 0;

		if (!empty($table)){
			
			$q = $db->from($table);

			$q->limits($offset, $count);

			if (!empty($filter)){ $q->where($filter); }

			$c = $q->getCount();

		}
		return $c;
	}

	function buildFilter($raw_filter, $table){
		$filter = array();
		foreach ($raw_filter as $field_name => $value) {
			if (substr($field_name, 0, 1) != '_' && substr($field_name, -1) != '_'){
				$field = SuppleField::getField($field_name, $table);
				$field->table = $table;
				if (isset($raw_filter['_op_'.$field_name])){
					$op = $raw_filter['_op_'.$field_name];
					$new_filters = $field->processFilter($value, $raw_filter, $op);
				} else {
					$new_filters = $field->processFilter($value, $raw_filter);
				}
				foreach ($new_filters as $k => $v){
					$filter[$k] = $v; // but, why?
				}
			}
		}
		//print_r($filter); die();
		return $filter;
	}
	
}


?>