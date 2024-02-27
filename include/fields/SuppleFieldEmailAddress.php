<?php 

require_once('include/SuppleApplication.php');

class SuppleFieldEmailAddress extends SuppleField { 

    public $type_name = 'EmailAddress';

    function validateValue($value, $table, $row, $fetched_row){
		global $lang, $db;

        // parent call
        $r = parent::validateValue($value, $table, $row, $fetched_row);
        if ($r === true) $r = ''; // convert response value
        
        // validate value:
        if ($value != '' && !$this->isValidEmailAddress($value)){
            $r .= $this->getLabel() . $lang->getValue('LBL_INVALID_EMAIL');
        }

        // return response
        if ($r === ''){
			return true;
		} else {
			return $r;
		}

    }

    function isValidEmailAddress($email){

        return preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $email);
    
    }

}