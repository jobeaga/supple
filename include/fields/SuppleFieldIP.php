<?php

class SuppleFieldIP extends SuppleField {

	public $type_name = 'IP';

	public function preProcess($value) {

		$r = parent::preProcess($value);

		if (empty($r)) {
			$r = $_SERVER['REMOTE_ADDR'];
		}

		return $r;

	}

}