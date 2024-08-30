<?php

class SuppleFieldOrder extends SuppleField {

	public $type_name = 'Order';

	public function preProcess($value) {

		$r = parent::preProcess($value);

		if (empty($r)) {
			// sería hermoso, pero por ahora no
			/*$a = $this->db->from($this->table)->applyFunction(array($this->name => 'max'))->getRow();
			if (empty($a[$this->name])){
				$r = 1;
			} else {
				$r = $a[$this->name] + 1;
			}*/
			// BARRIDO DE TODA LA TABLA
			$r = 1;
			$as = $this->db->from($this->table)->getArray();
			foreach ($as as $a){
				if ($a[$this->name] >= $r){
					$r = $a[$this->name] + 1;
				}
			}
		}

		return $r;

	}

	public function fixOrderValues(){
		global $db;

		$date_format = 'Y-m-d H:i';
		$date_offset = SuppleApplication::getconfig()->getValue('gmt_offset');
		$today = date($date_format, strtotime(gmdate($date_format)) + ($date_offset * 60 * 60));

		// fields of type 'Order'
		$fields = $db->from('_fields')->where(array('type' => 21))->getBeans();
		foreach ($fields as $field){
			$entity_id = $field->parent;
			// entity
			$entity = SuppleBean::getBean('_entities', $entity_id);
			echo "FIXING: {$entity->table} <br>";
			// all the data from that table
			$data = $db->from($entity->table)->orderBy($field->name)->getArray();
			// renumbering:
			$new_order = 1;
			$fixed = 0;
			foreach ($data as $row){
				if ($row[$field->name] != $new_order && !empty($row['id'])){
					//   update($table, $values, $filter)
					
					$db->update($entity->table, array($field->name => $new_order, 'date_modified' => $today), array('id' => $row['id']));
					$fixed++;
				}
				$new_order++;
			}
			echo "FIXED: $fixed VALUES <br>";
		}
	}

	public function orderSwapAndShift($table, $from_id, $to_id, $field_name){
		global $db;
		$values = array();

		$date_format = 'Y-m-d H:i';
		$date_offset = SuppleApplication::getconfig()->getValue('gmt_offset');
		$today = date($date_format, strtotime(gmdate($date_format)) + ($date_offset * 60 * 60));

		// GET VALUES
		$from_bean = SuppleBean::getBean($table, $from_id);
		$to_bean = SuppleBean::getBean($table, $to_id);
		$from_value = $from_bean->$field_name;
		$to_value = $to_bean->$field_name;
		$values[$from_id] = $from_value;
		$values[$to_id] = $to_value;

		if ($from_value < $to_value){
			$where = "$from_value < $field_name && $field_name < $to_value";
		} else {
			$where = "$to_value < $field_name && $field_name < $from_value";
		}
		$vs = $db->from($table)->where(array($where => ''))->orderBy($field_name)->getArray();
		foreach ($vs as $v){
			$values[$v['id']] = $v[$field_name];
		}

		// SHIFT values
		foreach ($values as $id => $v){
			if ($from_value < $to_value){
				$values[$id] = $v - 1;
			} else {
				$values[$id] = $v + 1;
			}
		}

		// SWAP $from_id value
		$values[$from_id] = $to_value;

		// UPDATE all!
		foreach ($values as $id => $v){
			$db->update($table, array($field_name => $v, 'date_modified' => $today), array('id' => $id));
		}

		// RETURN NEW VALUES
		return $values;
	}

}