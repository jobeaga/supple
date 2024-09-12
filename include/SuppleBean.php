<?php

require_once('include/SuppleApplication.php');
require_once('include/SuppleObject.php');
require_once('include/SuppleFile.php');

class SuppleBean extends SuppleObject {

	private static $bean_list;
	private static $metadata_cache;
	
	public $_table = '';
	public $_isnew = true;
	public $id;

	public static function getBean($table, $id = '', $light = false){

		$list = self::getBeanList();
		
		if (isset($list[$table])){
		
			$fileName = $list[$table]['fileName'];
			$className = $list[$table]['className'];
		
			// require
			require_once($fileName);
			
			// echo "$className($table, $id, $light)";
			// die();

			// new
			$bean = new $className($table, $id, $light);
			
			// return
			return $bean;
			
		} else {

			// echo "DEFAULT ($table, $id, $light)";		
			// die();

			return new SuppleBean($table, $id, $light);
		
		}

	}

	public static function getBeanList(){

		if (empty(self::$bean_list)){
	
			self::$bean_list = array();
		
			$a = glob('include/beans/*.php');
			foreach ($a as $file){
				require_once($file);
				$className = getFileName($file);
				if (class_exists($className)){
					$object = new $className();
					if (method_exists($object, 'tableName')) {
						$tableName = $object->tableName();
						if (!empty($tableName)) {
							self::$bean_list[$tableName]['fileName'] = $file;
							self::$bean_list[$tableName]['className'] = $className;
							self::$bean_list[$tableName]['tableName'] = $tableName;
						}
					}
				}
			}

			// CUSTOM ACTIONS: Adds and Overrides
			$a = glob('custom/beans/*.php');
			foreach ($a as $file){
				require_once($file);
				$className = getFileName($file);
				if (class_exists($className)){
					$object = new $className();
					if (!empty($object->_table)) {
						$tableName = $object->_table;
						if (!empty($tableName)) {
							self::$bean_list[$tableName]['fileName'] = $file;
							self::$bean_list[$tableName]['className'] = $className;
							self::$bean_list[$tableName]['tableName'] = $tableName;
						}
					}
				}
			}
		}
		
		return self::$bean_list;
	}
	
	function __construct($table = '', $id = '', $light = false){

		parent::__construct();

		$this->_table = $table;

		if ($id) $this->retrieve($id, $light);
		
	}

	function save(){

		if ($this->tableName() == '') return; // DONT SAVE!!!

		$startTime = SuppleApplication::getlog()->getStartTime();

		// AUDIT DATA
		$date_format = 'Y-m-d H:i';
		$date_offset = SuppleApplication::getconfig()->getValue('gmt_offset');
		$current_user_id = SuppleApplication::getUser();
		if (empty($this->id)){ 
			//$this->date_entered = date($date_format, time() + ($date_offset * 60 * 60));
			//$this->date_entered = gmdate($date_format);
			$this->date_entered = date($date_format, strtotime(gmdate($date_format)) + ($date_offset * 60 * 60));
			$this->created_by = $current_user_id;
		}
		// $this->date_modified = date($date_format, time() + ($date_offset * 60 * 60));
		// $this->date_modified = gmdate($date_format);
		$this->date_modified = date($date_format, strtotime(gmdate($date_format)) + ($date_offset * 60 * 60));
		$this->modified_by = $current_user_id;

		// Supple motto!: save each attribute, no matter if it is a declared Field or not.
		$data = $this->getData();
		unset($data['id']);

		// pre-processing
		$data = SuppleField::preProcessRow($data, $this->tableName(), $this->_fetched_row);

		if (empty($this->id)){ 
			
			$this->id = SuppleApplication::getdb()->insert($this->tableName(), $data);
			
		} else {
			
			$ids = SuppleApplication::getdb()->update($this->tableName(), $data, array('id' => $this->id));

			if (empty($ids)) {
				// new with ID:
				$data['id'] = $this->id;
				$this->id = SuppleApplication::getdb()->insert($this->tableName(), $data);	
			}
			
		}

		// Save related data
		// $this->save_relationships();

		// UPDATE BEAN CACHE:
		$cache = SuppleApplication::getcache();
		$cache_key = $cache->create_md5(array($this->tableName(), $this->id));
		$cache->unset_ram('bean_cache', $cache_key);

		$this->retrieve($this->id, true); // light!, updates cache

		SuppleApplication::getlog()->logEndTime('beanSave', $startTime);
		
		return $this->id;

	}

	function getData($return_arrays = true, $return_hidden = false) {
		$data = array();
		foreach ($this as $key => $value){
			if ((!is_array($value) || $return_arrays) && !is_object($value) && (substr($key, 0, 1) != '_' || $return_hidden) && $key != '_table' && !$this->isRelationship($key)) {
				$data[$key] = $value;
			}
		}
		return $data;
	}

	function tableName(){
		return $this->_table;
	}

	
	function retrieve($id, $light = false) {

		$startTime = SuppleApplication::getlog()->getStartTime();

		$cache = SuppleApplication::getcache();
		$cache_key = $cache->create_md5(array($this->tableName(), $id));
		//echo "CACHE KEY: $cache_key <br>";
		if ($cache->exists_ram('bean_cache', $cache_key)) {
			$row = $cache->get_ram('bean_cache', $cache_key);
			// echo "From cache<br>";
		} else {
			if (empty($id)){
				$row = array();
				// echo "Empty<br>";
			} else {
				$row = SuppleApplication::getdb()->from($this->tableName())->where(array('id' => $id))->getRow();
				$this->_fetched_row = $row;
				//echo "From DB<br>";
			}

			if (empty($row)){ // empty id, or missing data on DB
				$row = SuppleField::getDefaultValues($this->tableName()); // TODO: ¿Se hace un retrieve cuando se crea un registro?
			} else {
				$this->_isnew = false;
			}

			$row = SuppleField::postProcessRow($row, $this->tableName());
			
			$cache->set_ram('bean_cache', $cache_key, $row);
		}

		$this->populate($row, true);

		// Related data
		if (!$light){
			//$this->load_relationships();
		}

		SuppleApplication::getlog()->logEndTime('beanRetrieve', $startTime);

	}

	function populate($row, $do_post = true) {

		// post-processing
		if ($do_post) $row = SuppleField::postProcessRow($row, $this->tableName());

		foreach ($row as $key => $value){
			$this->$key = $value;
		}
	}

	function validate(){

		$data = $this->getData();

		// pre-processing
		$data = SuppleField::preProcessRow($data, $this->tableName(), $this->_fetched_row);

		// process each field
		return SuppleField::validateRow($data, $this->tableName(), $this->_fetched_row);

	}

	function needsAsyncValidation(){
		return false;
	}

	function isRelationship($rel_name) {
		SuppleApplication::getdb()->generateEntityRelCache();
		return isset(SuppleApplication::getdb()->rel_cache[$rel_name][$this->tableName()]);
	}

	function load_relationships(){
		SuppleApplication::getdb()->generateEntityRelCache();
		foreach (SuppleApplication::getdb()->rel_cache as $rel_table => $tables){
			foreach ($tables as $table => $def){
				if ($table == $this->tableName()) {
					$this->load_relationship($rel_table);
				}
			}
		}
	}

	function load_relationship($rel_table){
		SuppleApplication::getdb()->generateEntityRelCache();

		if (!isset(SuppleApplication::getdb()->rel_cache[$rel_table][$this->tableName()])) return;

		$def = SuppleApplication::getdb()->rel_cache[$rel_table][$this->tableName()];
		$final_table = $def['final_table']; 
		$ids = $this->get_relationship_ids($rel_table);
		
		$beans = array();
		foreach ($ids as $id){
			$beans[] = new SuppleBean($final_table, $id, true); // light!
		}
		$this->$rel_table = $beans;

	}

	function get_relationship_ids($rel_table) {
		SuppleApplication::getdb()->generateEntityRelCache();
		if (!isset(SuppleApplication::getdb()->rel_cache[$rel_table][$this->tableName()])) return;
		if (!isset($this->id)) return array();
		$def = SuppleApplication::getdb()->rel_cache[$rel_table][$this->tableName()];
		
		// $rel_table
		$rel_column = $def['rel_column'];
		$final_column = $def['final_column'];

		$final_table = $def['final_table']; // fetch this SuppleBean
		$final_field = $def['final_field']; // where this field
		$initial_field = $def['initial_field']; // has this value

		$ids = array();
		if (empty($rel_column)){
			// FROM $final_table WHERE $final_field = $initial_field
			$array = SuppleApplication::getdb()->from($final_table)->where(array($final_field => $this->$initial_field))->getArray();
			foreach ($array as $row){
				$ids[] = $row['id'];
			}
		} else {
			// FROM $rel_table WHERE $rel_column = $initial_field
			$rel = SuppleApplication::getdb()->from($rel_table)->where(array($rel_column => $this->$initial_field))->getArray();
			// FROM $final_table WHERE $final_field = $final_column
			foreach ($rel as $rel_row){
				 $ids[] = $rel_row[$final_column];
			}
		}
		return $ids;

	}

	function save_relationships(){
		SuppleApplication::getdb()->generateEntityRelCache();
		// print_r(SuppleApplication::getdb()->rel_cache); die();
		foreach (SuppleApplication::getdb()->rel_cache as $rel_table => $tables){
			foreach ($tables as $table => $def){
				if ($table == $this->tableName()) {
					$this->save_relationship($rel_table);
				}
			}
		}
	}

	function save_relationship($rel_table){
		// foreach ($this->$rel_table)
		// Compare SuppleBeans IDs with related IDs in DB: Add NEW and Remove missing
		
		if (!isset($this->$rel_table) || !is_array($this->$rel_table)) return;

		// TODO: ARE THEY IDs (strings) or OBJECTS???
		$ids = $this->get_relationship_ids($rel_table);

		$related_ids = array();
		foreach ($this->$rel_table as $bean){
			$id = $bean->save();
			$related_ids[] = $id;
			if (!in_array($id, $ids)){
				$this->add_to_relationship($rel_table, $id);
			}
		}

		foreach ($ids as $id){
			if (!in_array($id, $related_ids)){
				// remove from relationship		
				$this->remove_from_relationship($rel_table, $id);
			}
		}

	}

	function add_to_relationship($rel_table, $id){
		SuppleApplication::getdb()->generateEntityRelCache();
		if (!isset(SuppleApplication::getdb()->rel_cache[$rel_table][$this->tableName()])) return;
		$def = SuppleApplication::getdb()->rel_cache[$rel_table][$this->tableName()];

		$rel_column = $def['rel_column'];
		$final_table = $def['final_table'];
		$final_field = $def['final_field'];
		$initial_field = $def['initial_field'];
		$final_column = $def['final_column'];

		if (empty($rel_column)){
			return SuppleApplication::getdb()->update($final_table, array($final_field => $this->$initial_field), array('id' => $id));
		} else {
			$row = SuppleApplication::getdb()->from($final_table)->where(array('id' => $id))->getRow();
			return SuppleApplication::getdb()->insert($rel_table, array( 
				$rel_column => $this->$initial_field,
				$final_column => $row[$final_field],
			));
		}
	}

	function remove_from_relationship($rel_table, $id, $def){
		//SuppleApplication::getdb()->generateEntityRelCache();
		//if (!isset(SuppleApplication::getdb()->rel_cache[$rel_table][$this->tableName()])) return;
		// $def = SuppleApplication::getdb()->rel_cache[$rel_table][$this->tableName()];
		if (empty($def)) return;

		$rel_column = $def['rel_column'];
		$final_table = $def['final_table'];
		$final_field = $def['final_field'];
		$initial_field = $def['initial_field'];
		$final_column = $def['final_column'];

		// echo "$rel_column -- $final_table -- $final_field -- $initial_field -- $final_column <br>\n";

		if (empty($rel_column)){
			if ($final_field == 'id'){
				return array();
			} else {
				return SuppleApplication::getdb()->update($final_table, array($final_field => ''), array('id' => $id));
			}
		} else {
			$row = SuppleApplication::getdb()->from($final_table)->where(array('id' => $id))->getRow();
			return SuppleApplication::getdb()->delete($rel_table, array( 
				$rel_column => $this->$initial_field,
				$final_column => $row[$final_field],
			));
		}
	}

	function unload_relationship($rel_table) {
		unset($this->$rel_table);
	}

	function remove(){
		global $db;
		$deleted_id = $this->id;
		if (!empty($this->id)){
			// Delete from cache.
			$cache = SuppleApplication::getcache();
			$cache_key = $cache->create_md5(array($this->tableName(), $id));
			$cache->unset_ram('bean_cache', $cache_key);
			// FILES DELETE:
			$sf = new SuppleFile();
			$sf->delete($this->id, $this->tableName()); 
			// RECORD DELETE
			$db->delete($this->tableName(), array('id' => $this->id));
			
			
			// GET the entity for this object:
			$entity = $db->from('_entities')->where(array('table' => $this->tableName()))->getBean();
			if (!empty($entity->id)){
				// GET relationships for this entity
				$rels = $db->from('_relationships')->where(array('id_a' => $entity->id, 'trigger_delete' => 1))->getBeans();
				foreach ($rels as $rel_bean){
					// GET related entities
					$rel_entity = $db->from('_entities')->where(array('id' => $rel_bean->id_b))->getBean();
					if (!empty($rel_entity->id)){
						// GET ALL RELATED BEANS
						$to_delete = $db->from($rel_entity->table)->where(array($rel_bean->entity_b_field => $deleted_id))->getBeans();
						foreach ($to_delete as $dbean){
							$dbean->remove();
						}
					}
				}
			}
		}
		return $deleted_id;
	}

	function remove_relationships(){
		SuppleApplication::getdb()->generateEntityRelCache();
		foreach (SuppleApplication::getdb()->rel_cache as $rel_table => $tables){
			foreach ($tables as $table => $def){
				if ($table == $this->tableName()) {
					// echo "$table <br>\n";
					$ids = $this->get_relationship_ids($rel_table);
					foreach ($ids as $id){
						// echo "$rel_table -> ID $id<br>\n";
						$this->remove_from_relationship($rel_table, $id, $def);
					} 
				}
			}
		}
	}

	function parseTemplate($template_id, $extra_data = array()){
		$template = SuppleBean::getBean('_templates', $template_id);
		// fuerza a completar campos extra
		$row = $this->getData();
		$row = SuppleField::postProcessRow($row, $this->tableName());
		$this->populate($row, true);
		// parse propiamente dicho:
		return $template->parse($this, $extra_data);
	}

	function sendTemplate($template_id, $email, $name = '', $extra_data = array(), $smtp_data = array()){
		if (empty($name)) $name = $email;
		$texts = $this->parseTemplate($template_id, $extra_data);
		// echo "{$texts['subject']}<br><br>{$texts['body']}"; die();

		require_once('include/SuppleMail.php');
        $sm = new SuppleMail();
        $m = $sm->simpleSend($email, $name, $texts['subject'], $texts['body'], array(), $smtp_data);

		return $m;
	}

	function isInCore(){
		global $db;
		return ($db->isInCore($this->id, $this->tableName(), $this->date_modified) !== false);
	}

	function getRecordDescription(){
        global $db;
        $field_max_size = 20;
        $description = '';
		// GET ENTITY ID
		$entity = $db->from('_entities')->where(array('table' => $this->tableName()))->getBean();
		if (empty($entity->id)){
			$description_data = array_slice($this->getData(), 0, 3);
			$description = implode(' - ', $description_data);
			return $description;
		} 
        // CACHE METADATA
        if (empty(self::$metadata_cache[$entity->id])){
            self::$metadata_cache[$entity->id] = array();
            $fields = $db->from('_fields')->where(array('parent' => $entity->id, 'view2' => 1))->orderBy('order')->limit(3)->getArray();
            foreach ($fields as $f){
                self::$metadata_cache[$entity->id][] = $f['name'];
            }
        }
        // COLLECT DATA
        $description_data = array();
        foreach (self::$metadata_cache[$entity->id] as $fieldname){
			$fn = $this->$fieldname;
			if (is_array($fn)) $fn = reset($fn);
            if (strlen($fn) > $field_max_size){
                $description_data[] = substr($fn, 0, 20).'...';
            } else {
                $description_data[] = $fn;
            }
        }
        // ADD DATE_MODIFIED:
        $description_data[] = '('.formatDate($this->date_modified, 'd/m/Y H:i').')';
        $description = implode(' - ', $description_data);
        return $description;
    }

}



