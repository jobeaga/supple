<?php 

require_once('include/SuppleBean.php');

class Relationship extends SuppleBean {

	function __construct($table = '', $id = '', $light = false){

		parent::__construct($table, $id, $light);
		$this->_table = '_relationships';

    }

    function save(){

        $entity_a_field = $this->entity_a_field;
        $entity_b_field = $this->entity_b_field;
        if ($entity_a_field == 'id' && $entity_b_field == 'id'){
            $type = 1;
        } else if ($entity_a_field == 'id'){
            $type = 2;
        } else if ($entity_b_field == 'id'){
            $type = 3;
        } else {
            $type = 4;
        }
		$this->type = $type;

		$r = parent::save();

		return $r;

	}
    

}