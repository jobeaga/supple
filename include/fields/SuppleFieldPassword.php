<?php

class SuppleFieldPassword extends SuppleField {

	public $type_name = 'Password';

	public function preProcess($value) {

		$r = parent::preProcess($value);

		if (!empty($r) && !isMd5($r)) {
			$r = md5($r);
		}

		return $r;

	}

}