<?php 

require_once('include/SuppleBean.php');

class Entity extends SuppleBean {

	private $basic_fields = array(
		array('name' => 'id', 'label' => 'ID', 'order' => '', 'default_value' => '', 'required' => '0', 'access' => 'readonly', 'view1' => '0', 'view2' => '0', 'view3' => '0', 'view4' => '0', 'view5' => '0', 'view6' => '0', 'view7' => '0', 'type' => '1',  'autoincrement' => '0', 'regex' => '', 'multiple' => '0', 'size' => '5', 'label_es' => 'ID', 'label_en' => 'ID'),

		array('name' => 'date_entered', 'label' => 'Date Entered', 'order' => '', 'default_value' => '', 'required' => '0', 'access' => 'readonly', 'view1' => '0', 'view2' => '0', 'view3' => '0', 'view4' => '0', 'view5' => '0', 'view6' => '0', 'view7' => '0', 'type' => '20',  'autoincrement' => '0', 'regex' => '', 'multiple' => '0', 'label_es' => 'Fecha de Creacion', 'label_en' => 'Date Entered'),

		array('name' => 'date_modified', 'label' => 'Date Modified', 'order' => '', 'default_value' => '', 'required' => '0', 'access' => 'readonly', 'view1' => '0', 'view2' => '0', 'view3' => '0', 'view4' => '0', 'view5' => '0', 'view6' => '0', 'view7' => '0', 'type' => '20',  'autoincrement' => '0', 'regex' => '', 'multiple' => '0', 'label_es' => 'Fecha de Modificacion', 'label_en' => 'Date Modified'),

		array('name' => 'created_by', 'label' => 'Created By', 'order' => '', 'default_value' => '', 'required' => '0', 'access' => 'readonly', 'view1' => '0', 'view2' => '0', 'view3' => '0', 'view4' => '0', 'view5' => '0', 'view6' => '0', 'view7' => '0', 'type' => '7',  'autoincrement' => '0', 'regex' => '', 'multiple' => '0', 'label_es' => 'Creado por', 'label_en' => 'Created By', 'related_entity' => '3', 'related_field_id' => '89', 'related_field' => 'name', 'related_table' => 'users', 'order_field' => 'name', 'order_field_id' => '89', 'save_related_name' => ''),

		array('name' => 'modified_by', 'label' => 'Modified By', 'order' => '', 'default_value' => '', 'required' => '0', 'access' => 'readonly', 'view1' => '0', 'view2' => '0', 'view3' => '0', 'view4' => '0', 'view5' => '0', 'view6' => '0', 'view7' => '0', 'type' => '7',  'autoincrement' => '0', 'regex' => '', 'multiple' => '0', 'label_es' => 'Modificado por', 'label_en' => 'Modified By', 'related_entity' => '3', 'related_field_id' => '89', 'related_field' => 'name', 'related_table' => 'users', 'order_field' => 'name', 'order_field_id' => '89', 'save_related_name' => ''),

		//array('name' => 'assigned_user_id', 'label' => 'Usuario Asignado', 'label_es' => 'Usuario Asignado', 'label_en' => 'Assigned User', 'default_value' => '$_current_user.id', 'required' => '0', 'access' => 'write', 'autoincrement' => '0', 'multilanguage' => '0', 'multiple' => '0', 'regex' => '', 'view1' => '1', 'view2' => '1', 'view3' => '1', 'view4' => '1', 'view5' => '0', 'view6' => '0', 'view7' => '0', 'type' => '7', 'related_entity' => '3', 'related_field_id' => '89', 'related_field' => 'name', 'related_table' => 'users', 'order_field_id' => '89', 'order_field' => 'name', 'save_related_name' => ''),

	);

	function __construct($table = '', $id = '', $light = false){

		parent::__construct($table, $id, $light);
		$this->_table = '_entities';

	}

	function save(){

		$is_new = empty($this->id);

		unset($this->needs_async_validation);

		$r = parent::save();

		if ($is_new){
			$this->createBasicFields();
			$this->_update_metadata = true;
		}

		return $r;

	}

	function createBasicFields(){
		// Default fields:
		foreach ($this->basic_fields as $f) {
			$field = SuppleBean::getBean('_fields'); 
			$field->populate($f, false);
			$field->parent = $this->id;
			$field->save();
		}

		// And relationship
		//$this->createTeamsRelationship();

	}

	function createMissingFields(){
		foreach ($this->basic_fields as $f) {
			$name = $f['name'];
			$row = $this->db->from('_fields')->where(array('name' => $name, 'parent' => $this->id))->getRow();
			if (empty($row)){
				// echo "Creating $name <br>";
				$this->createField($f);
			}
		}
		// Rel!
		$row = $this->db->from('_relationships')->where(array('rel_table' => '_teams_records', 'id_b' => $this->id))->getRow();
		if (empty($row)) {
			// echo "Creating Teams Relationship <br>";
			$this->createTeamsRelationship();
		}
	}

	function createField($data){
		$field = SuppleBean::getBean('_fields'); 
		$field->populate($data, false);
		$field->parent = $this->id;
		$field->save();
		return $field;
	}

	function createTeamsRelationship(){
		$rel = SuppleBean::getBean('_relationships');
		$rel->name = 'teams';
		$rel->rel_table = $this->table.'_teams';
		$rel->template = 2; // checkbox list!
		$rel->trigger_delete = 0;
		
		$team_entity = $this->db->from('_entities')->where(array('table' => '_teams'))->getBean();

		$rel->id_a = $team_entity->id; // teams entity
		$rel->field_a_id = SuppleField::getField('id', $team_entity->table)->id; // id field
		$rel->entity_a_field = 'id'; // id field name
		$rel->relationship_field_a = 'id_a';
		$rel->label_a = $team_entity->name;

		$rel->id_b = $this->id; // entity ID
		
		$fff = $this->db->from('_fields')->where(array('name' => 'id', 'parent' => $this->id))->getRow();
		$rel->field_b_id = $fff['id'];
		// $rel->field_b_id = SuppleField::getField('id', $this->table)->id;

		$rel->entity_b_field = 'id';
		$rel->relationship_field_b = 'id_b';
		$rel->label_b = $this->name;

		$rel->save(); 
/*
		// And parameter
		$param = SuppleBean::getBean('_relationships_parameters');
		$param->name = 'table';
		$param->value = $this->table;
		$param->parent = $rel->id;
		$param->save();
		*/
	}

	function populate($row, $do_post = true) {

		parent::populate($row, $do_post);

		// does this entity need async validation before submit?
		// TODO check: 
		//		- has fields that needs validation (unique fields)
		//		- has validation method defined on custom bean
		$this->needs_async_validation = ($this->needsAsyncValidation())?1:0;

	}

	function needsAsyncValidation(){
		// TODO Indicates wether the entity (the entity itself, and all its fields) needs to execute async validation before submit.
		$r = false;

		// FIELDS:
		$fs = SuppleField::getFields($this->table, $this->id);
		foreach ($fs as $f){
			if ($f->unique == 1) return true;
		}

		// BEAN:
		$b = SuppleBean::getBean($this->table);
		$r = $r || $b->needsAsyncValidation();

		return $r;
	}

}
