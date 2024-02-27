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

}