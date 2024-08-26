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

}