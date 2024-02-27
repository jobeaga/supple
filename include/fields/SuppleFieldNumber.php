<?php 

require_once('include/SuppleApplication.php');

class SuppleFieldNumber extends SuppleField { 

    public $type_name = 'Number';

    function validateValue($value, $table, $row, $fetched_row){
		global $lang, $db;

        // parent call
        $r = parent::validateValue($value, $table, $row, $fetched_row);
        if ($r === true) $r = ''; // convert response value
        
        // validate value:
        if ($value != '' && $this->min != '' && $value < $this->min){
            $r .= $this->getLabel() . $lang->getValue('LBL_INVALID_NUMBER')." (min:{$this->min})";
        }
        if ($value != '' && $this->max != '' && $value > $this->max){
            $r .= $this->getLabel() . $lang->getValue('LBL_INVALID_NUMBER')." (max:{$this->max})";
        }

        // return response
        if ($r === ''){
			return true;
		} else {
			return $r;
		}

    }

}