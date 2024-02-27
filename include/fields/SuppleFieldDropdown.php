<?php 

require_once('include/SuppleApplication.php');

class SuppleFieldDropdown extends SuppleField { 

    public $type_name = 'Dropdown';

    
	public function preProcess($value) {

        $r = parent::preProcess($value);
        $this->row[$this->name.'_value'] = ''; // do not save any translated value
        return $r;

    }
    

    // El nombre del valor
    public function postProcess($value) {
		$r = parent::postProcess($value);
    
        if (empty($value)){
            $this->row[$this->name.'_value'] = '';
        } else {
            $db = SuppleApplication::getdb();
            $selected_value = $db->from('_dropdown_options')->where(array('list' => $this->option_list, 'key' => $value))->getBean();
            $this->row[$this->name.'_value'] = $selected_value->value;
        }
        
		return $r;
	}

}
