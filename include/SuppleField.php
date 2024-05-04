<?php 

require_once('include/SuppleApplication.php');

class SuppleField extends SuppleObject {

	private static $data_type_list;
	
	public $type_name = '';
	public $table;
	public $row;
	
	function __construct(){

		parent::__construct();

	}
	
	public static function getField($field, $table, $entity_id = ''){
	
		$cache = SuppleApplication::getcache();
		$field_key = $cache->create_md5(array($field, $table));
		if ($cache->exists_ram('SuppleField_field', $field_key)){
			return $cache->get_ram('SuppleField_field', $field_key);
		}

		// TODO: CHANGE FOR:
		$fields = self::getFields($table, $entity_id);
		if (isset($fields[$field])){
			return $fields[$field];
		} else {
			$f = new SuppleField();
			$f->name = $field;
			$f->type_name = '';
			$cache->set_ram('SuppleField_field', $field_key, $f);
			return $f;
		}
		

/*
		$db = SuppleApplication::getdb();
		// ¿Datatype?
		if (empty($entity_id)) $db->generateEntityRelCache();
		if (isset($db->entity_cache[$table]) || !empty($entity_id)) {
			if (empty($entity_id)) $entity_id = $db->entity_cache[$table];
			$field_row = $db->from('_fields')->where(array('name' => $field, 'parent' => $entity_id))->getRow();
			if (!empty($field_row)){
				$datatype_row = $db->from('_datatypes')->where(array('id' => $field_row['type']))->getRow();
				if (!empty($datatype_row)) {
					$datatype = $datatype_row['typename'];
				} else {
					$datatype = '';
				}
			} else {
				$datatype = '';
			}
		} else {
			$datatype = '';
		}

		$list = self::getFieldList();
		if (!empty($datatype) && isset($list[$datatype])){
		
			$fileName = $list[$datatype]['fileName'];
			$className = $list[$datatype]['className'];
		
			// require
			require_once($fileName);
			
			// new
			$field = new $className();
			
		} else {
		
			$field = new SuppleField();
			$field->type_name = $datatype;

		}
			

		// Set info
		if (!empty($field_row)){
			foreach ($field_row as $prop => $value) {
				if (substr($prop, 0,1) != '_') {
					$field->$prop = $value;
				}
			}
		}

		$cache->set_ram('SuppleField_field', $field_key, $field);

		return $field;
*/
	}

	public static function getFields($table, $entity_id = ''){
		$cache = SuppleApplication::getcache();
		$cache_key = $cache->create_md5(array($table));
		if ($cache->exists_ram('SuppleField_fields', $cache_key)){
			return $cache->get_ram('SuppleField_fields', $cache_key);
		}

		$fields = array();

		$db = SuppleApplication::getdb();
		// Datatype?
		$list = self::getFieldList();
		if (empty($entity_id)) $db->generateEntityRelCache();
		if (isset($db->entity_cache[$table]) || !empty($entity_id)) {
			if (empty($entity_id)) $entity_id = $db->entity_cache[$table];
			// Can't use getBean() or getBeans(), due to ciclyc references to fields.
			$field_rows = $db->from('_fields')->where(array('parent' => $entity_id))->getArray();
			foreach ($field_rows as $i => $field_row){
				$name = $field_row['name'];
				$datatype_row = $db->from('_datatypes')->where(array('id' => $field_row['type']))->getRow();
				if (!empty($datatype_row)) {
					$datatype = $datatype_row['typename'];
				} else {
					$datatype = '';
				} 
				if (!empty($datatype) && isset($list[$datatype]) ){
					$fileName = $list[$datatype]['fileName'];
					$className = $list[$datatype]['className'];
				
					// require
					require_once($fileName);
					
					// new
					$fields[$name] = new $className();

				} else {

					$fields[$name] = new SuppleField();
					$fields[$name]->type_name = $datatype;

				}
				
				// Set info
				foreach ($field_row as $prop => $value) {
					if (substr($prop, 0,1) != '_') {
						$fields[$name]->$prop = $value;
					}
				}

				// Set cache for getField();
				$field_key = $cache->create_md5(array($name, $table));
				if (!$cache->exists_ram('SuppleField_field', $field_key)){
					$cache->set_ram('SuppleField_field', $field_key, $fields[$name]);
				}
			} 
		} 

		// Set cache for this method
		$cache->set_ram('SuppleField_fields', $cache_key, $fields);

		return $fields;
	}
	

	public static function getFieldList(){
	
		if (empty(self::$data_type_list)){
	
			self::$data_type_list = array();
		
			$a = glob('include/fields/*.php');
			foreach ($a as $file){
				require_once($file);
				$className = getFileName($file);
				if (class_exists($className)){
					$object = new $className();
					if (isset($object->type_name)){
						$dataTypeName = $object->type_name;
						self::$data_type_list[$dataTypeName]['fileName'] = $file;
						self::$data_type_list[$dataTypeName]['className'] = $className;
						self::$data_type_list[$dataTypeName]['fieldName'] = $dataTypeName;
					}
				}
			}

			$a = glob('custom/fields/*.php');
			foreach ($a as $file){
				require_once($file);
				$className = getFileName($file);
				if (class_exists($className)){
					$object = new $className();
					if (isset($object->type_name)){
						$dataTypeName = $object->type_name;
						self::$data_type_list[$dataTypeName]['fileName'] = $file;
						self::$data_type_list[$dataTypeName]['className'] = $className;
						self::$data_type_list[$dataTypeName]['fieldName'] = $dataTypeName;
					}
				}
			}

		}
		
		return self::$data_type_list;
		
	}

	public static function getDefaultValues($table){
		$db = SuppleApplication::getdb();
		$r = array();
		$db->generateEntityRelCache();
		if (isset($db->entity_cache[$table])) {
			$entity_id = $db->entity_cache[$table];
			$fields = $db->from('_fields')->where(array('parent' => $entity_id))->getArray();
			foreach ($fields as $field_row){
				if (isset($field_row['name'])){
					if (isset($field_row['default_value'])){
						$r[$field_row['name']] = $field_row['default_value'];
					} else {
						$r[$field_row['name']] = '';
					}
				}
			}
		}
		unset($r['id']);
		return $r;
	}

	public static function validateRow($row, $table, $fetched_row){
		$r = array();

		// process each field
		$fs = self::getFields($table);
		foreach ($fs as $f){
			$value = '';
			if (isset($row[$f->name])){
				$value = $row[$f->name];
			}
			$f->table = $table;

			if ($f->multilanguage && $f->multiple){
				// MULTI MULTI
				$langs = SuppleLanguage::getLangList();
				foreach ($langs as $code => $lang){
					$field_name = $f->name . "_" . $code;
					foreach ($row as $key => $v){
						if (substr($key,0,strlen($field_name)) == $field_name 
							&& substr($key,strlen($field_name), 1) == '_' // change: underscore for multivalues
							&& is_numeric(substr($key,strlen($field_name)+1))
							&& substr($key,strlen($field_name)+1) != '' ) {
								$rs = $f->validateValue($v, $table, $row, $fetched_row);
								if ($rs !== true){ 
									$r[$field_name] = $rs; 
								}
						}
					}

				}

			} else if ($f->multilanguage){
				// MULTILANGUAGE
				$langs = SuppleLanguage::getLangList();
				foreach ($langs as $code => $lang){
					$field_name = $f->name . "_" . $code;
					$lang_value = (isset($row[$field_name]))?$row[$field_name]:'';
					$rs = $f->validateValue($lang_value, $table, $row, $fetched_row);
					if ($rs !== true){ 
						$r[$field_name] = $rs. ' ('. $lang->name .'). '; 
					}
				}

			} else if ($f->multiple){
				// MULTIPLE
				foreach ($row as $key => $row_value){
					if (substr($key,0,strlen($f->name)) == $f->name 
						&& substr($key,strlen($f->name), 1) == '_' // change: underscore for multivalues
						&& is_numeric(substr($key,strlen($f->name)+1))
						&& substr($key,strlen($f->name)+1) != '' ) {
							$rs = $f->validateValue($row_value, $table, $row, $fetched_row);
							if ($rs !== true){ 
								$r[$f->name] = $rs.'. '; 
							}
					}
				}
			} else {
				// SIMPLE VALUE
				$rs = $f->validateValue($value, $table, $row, $fetched_row);
				if ($rs !== true){ 
					$r[$f->name] = $rs.'. '; 
				}
			}

		}
	
		if (count($r) == 0){
			return true;
		} else {
			return $r;
		}
	}

	function getLabel(){
		$label_field = 'label_'.SuppleLanguage::getLanguage();
		return $this->$label_field;
	}

	function needsAsyncValidation(){
		// Indicates wether the field (and the entity) needs to execute async validation before submit.
		return ($this->unique);
	}

	function validateValue($value, $table, $row, $fetched_row){
		global $lang, $db;
		$r = '';

		// validate basic restrictions (required, unique, regex)

		// REQUIRED
		if ($this->required == 1 && trim($value) == '' && $value !== 0){
			$r .= $this->getLabel() . $lang->getValue('LBL_IS_REQUIRED');
		}
		// UNIQUE
		if ($this->unique == 1 && trim($value) != ''){
			$id = $row['id'];
			$c = $db->from($table)->where(array($this->name => $value, "id != '$id'" => ''))->getCount();
			if ($c){
				$r .= $this->getLabel() . $lang->getValue('LBL_IS_UNIQUE');
			}			
		}
		// REGEX
		// preg_match() devuelve 1 si pattern coincide con el subject dado, 0 si no, o false si ocurrió un error.
		if (!empty($this->regex) && trim($value) != ''){
			if (preg_match('/'.$this->regex.'/', $value) != 1){
				$r .= $this->getLabel() . $lang->getValue('LBL_IS_REGEX');
			}
		}

		if ($r === ''){
			return true;
		} else {
			return $r;
		}
		return true;
	}

	// before save
	public static function preProcessRow($row, $table, $fetched_row = array()) {

		// Process fields not in row: AUTOINC and ORDER
		$fs = self::getFields($table);
		foreach ($fs as $f){
			if ($f->type_name == 'Order' || $f->autoincrement == 1){
				if (!isset($row[$f->name])){
					$row[$f->name] = '';
				} 
			}
		}

		$r = $row;
		
		// Pre process each value
		foreach ($row as $field => $old_value) {
			$value = $r[$field];
			$f = self::getField($field, $table);
			$f->table = $table;
			$f->row = $r;
			$value = $f->preProcess($value);
			$r = $f->row;
			$r[$field] = $value;

			// writeonce, onlyadmin, readonly
			// TODO: Rewrite this as validations????
			if ($f->access == 'writeonce' && !empty($fetched_row)){
				/*
				if (isset($fetched_row[$field]) && $fetched_row[$field] != ''){
					$r[$field] = $fetched_row[$field]; // don't set a new value, keep the old value.
				}
				*/
			}
			if ($f->access == 'onlyadmin'){
				/*
				$user_info = SuppleApplication::getUserInfo();
				if ($user_info['isadmin'] == 0){
					if (isset($fetched_row[$field]) && $fetched_row[$field] != ''){
						$r[$field] = $fetched_row[$field]; // don't set a new value, keep the old value.
					} else {
						unset($r[$field]);
					}
				}
				*/
			}
			if ($f->access == 'readonly'){
				//unset($r[$field]); // don't! it affects code writings too. We only need to affect UI
			}
			
		}
		
		return $r;
	}

	// after retrieve
	public static function postProcessRow($row, $table) {

		// multiple and multilanguage
		$fs = self::getFields($table);
		foreach ($fs as $f){
			if ((isset($f->multiple) && $f->multiple == 1) || (isset($f->multilanguage) && $f->multilanguage == 1)){
				if (!isset($row[$f->name])){
					$row[$f->name] = '';
				} 
			}
		}

		$r = $row;
		foreach ($row as $field => $old_value) {
			$value = $r[$field];
			$f = self::getField($field, $table);
			$f->table = $table;
			$f->row = $r;
			$value = $f->postProcess($value);
			$r = $f->row;
			$r[$field] = $value;
		}
		
		return $r;

	}

	public function preProcess($value) {

		$value = $this->processAutoincrement($value);

		$value = $this->preProcessMultiple($value);

		$value = $this->preProcessMultiMulti($value);

		return $value;

	}

	public function postProcess($value) {

		$value = $this->postProcessMultiple($value);

		$value = $this->postProcessMultilanguage($value);

		$value = $this->postProcessMultiMulti($value);

		return $value;

	}

	public function processAutoincrement($value){
		if (empty($value) && isset($this->autoincrement) && $this->autoincrement == 1){
			// seek the max value
			$a = $this->db->from($this->table)->applyFunction(array($this->name => 'max'))->getRow();
			if (empty($a[$this->name])){
				$next_val = 1;
			} else {
				$next_val = $a[$this->name] + 1;
			}
			return $next_val;
		} else {
			return $value;
		}
	}

	public function preProcessMultiple($value){

		if (isset($this->multiple) && $this->multiple == 1){
			// REMOVE PREVIOUS VALUES
			foreach ($this->row as $key => $v){
				if (substr($key,0,strlen($this->name)) == $this->name 
					&& substr($key,strlen($this->name), 1) == '_' // change: underscore for multivalues
					&& is_numeric(substr($key,strlen($this->name)+1))
					&& substr($key,strlen($this->name)+1) != '' ) {
					$this->row[$key] = '';
				}
			}
			// SET NEW VALUES
			if (is_array($value)) {
				$c = 1;
				foreach ($value as $v){
					$this->row[$this->name . '_' . $c] = $v; // change: underscore for multivalues
					$c++;
				}
			}
			return '';
		} else {
			return $value;
		}

	}

	public function postProcessMultiple($value){
		if (isset($this->multiple) && $this->multiple == 1 && empty($this->multilanguage)){
			$r = array();
			foreach ($this->row as $key => $value){
				if (substr($key,0,strlen($this->name)) == $this->name 
					&& substr($key,strlen($this->name), 1) == '_' // change: underscore for multivalues
					&& is_numeric(substr($key,strlen($this->name)+1))
					&& substr($key,strlen($this->name)+1) != '' ) {
					if (!empty($value)) $r[] = $value;
				}
			}
			if (empty($r) && !empty($value)){
				$r = array($value);
			}
			return $r;
		} else {
			return $value;
		}
	}

	public function postProcessMultilanguage($value){
		if (isset($this->multilanguage) && $this->multilanguage == 1 && empty($this->multiple)){
			require_once('include/global/SuppleLanguage.php');
			
			// AUTOCOMPLETE EMPTY
			$langs = SuppleLanguage::getLangList();
			foreach ($langs as $code => $lang){
				$field_name = $this->name . "_" . $code;
				if (!isset($this->row[$field_name])){
					$this->row[$field_name] = $value;
					//echo "AUTOCOMPLETE: $field_name = $value<br>";
				}
			}

			$code = SuppleLanguage::getLanguage();
			$field_name = $this->name . "_" . $code;
			return $this->row[$field_name];
		}
		return $value;
	}

	public function preProcessMultiMulti($value){
		if (isset($this->multilanguage) && $this->multilanguage == 1 && isset($this->multiple) && $this->multiple == 1){

			require_once('include/global/SuppleLanguage.php');
			$langs = SuppleLanguage::getLangList();
			foreach ($langs as $code => $lang){
				$field_name = $this->name . "_" . $code;

				// REMOVE PREVIOUS VALUES
				foreach ($this->row as $key => $v){
					if (substr($key,0,strlen($field_name)) == $field_name 
						&& substr($key,strlen($field_name), 1) == '_' // change: underscore for multivalues
						&& is_numeric(substr($key,strlen($field_name)+1))
						&& substr($key,strlen($field_name)+1) != '' ) {
						$this->row[$key] = '';
					}
				}
				
				$vs = $this->row[$field_name];
				// SET NEW VALUES
				if (is_array($vs)) {
					$c = 1;
					foreach ($vs as $v){
						$this->row[$field_name . '_'. $c] = $v; // change: underscore for multivalues
						$c++;
					}
				}
				unset($this->row[$field_name]);
			}
			return '';
		} else {
			return $value;
		}
	}

	public function postProcessMultiMulti($value){
		if (isset($this->multilanguage) && $this->multilanguage == 1 && isset($this->multiple) && $this->multiple == 1){
			require_once('include/global/SuppleLanguage.php');
			
			$langs = SuppleLanguage::getLangList();
			foreach ($langs as $code => $lang){
				$field_name = $this->name . "_" . $code;
				$this->row[$field_name] = array();

				foreach ($this->row as $key => $value){
					if (substr($key,0,strlen($field_name)) == $field_name 
						&& substr($key,strlen($field_name), 1) == '_' // change: underscore for multivalues
						&& is_numeric(substr($key,strlen($field_name)+1))
						&& substr($key,strlen($field_name)+1) != '' ) {
						if (!empty($value)) $this->row[$field_name][] = $value;
					}
				}

			}

			// FOR THIS LANGUAGE
			$r = array(); 
			$code = SuppleLanguage::getLanguage();
			$field_name = $this->name . "_" . $code;
			foreach ($this->row as $key => $value){
				if (substr($key,0,strlen($field_name)) == $field_name 
					&& substr($key,strlen($field_name), 1) == '_' // change: underscore for multivalues
					&& is_numeric(substr($key,strlen($field_name)+1))
					&& substr($key,strlen($field_name)+1) != '' ) {
					if (!empty($value)) $r[] = $value;
				}
			}
			return $r;

		} else {
			return $value;
		}
	}

	public function processFilter($value, $raw_filter, $op = 'exact'){
		global $db;
		$r = array();

		$function_name = 'processFilter_'.$op;
		if (!method_exists($this, $function_name)){
			$function_name = '';
		}
		// echo "FUNC: $function_name \n";

		$lang_names = array();
		if (isset($this->multilanguage) && $this->multilanguage == 1){
			$langs = SuppleLanguage::getLangList();
			foreach ($langs as $code => $lang){
				$lang_names[] = $this->name.'_'.$code;
			}
		} else {
			$lang_names[] = $this->name;
		}
		$condition = '';
		foreach ($lang_names as $field_name){
			if (isset($this->multiple) && $this->multiple == 1){
				
				$table_fields = $db->getFields($table);

				foreach ($table_fields as $def){
					$name = $def['name'];
					if (substr($name, 0, strlen($field_name)) == $field_name){
						$post = substr($name, strlen($field_name));
						if (is_numeric($post) && $post != '') {
							// add multi to condition
							if (!empty($condition)) $condition .= " || ";
							if (empty($function_name)){
								$condition .= "$name == '$value'";
							} else {
								$c = $this->$function_name($name, $value, $raw_filter);
								if (!empty($c)) $condition .= $c;
							}							
						}
					}
				}

			} else {
				if (isset($this->multilanguage) && $this->multilanguage == 1){
					// add multilang condition
					if (!empty($condition)) $condition .= " || ";
					if (empty($function_name)){
						$condition .= "$field_name == '$value'";
					} else {
						$c = $this->$function_name($field_name, $value, $raw_filter);
						if (!empty($c)) $condition .= $c;
					}
				} else {
					// simple field condition:
					if (empty($function_name)){
						$r[$field_name] = $value;
					} else {
						$c = $this->$function_name($field_name, $value, $raw_filter);
						if (!empty($c)) $condition .= $c;
					}
				}
			}
		}

		if (!empty($condition)) $r["($condition)"] = '';

		return $r;
	}

	// TODO: filter for specific ops
	function processFilter_exact($field_name, $value, $raw_filter){
		// PARCHE
		if (!empty($this->table)){
			global $db;
			$conn = $db->from($this->table);
			if (get_class($conn) == 'PhpArrayResultset'){
				return "($field_name == '$value' && is_numeric($field_name) == is_numeric('$value'))";
			}
		}
		// GENERAL:
		return "$field_name == '$value'";
	}

	function processFilter_contains($field_name, $value, $raw_filter){
		return "$field_name LIKE '%$value%'";
	}

	function processFilter_starts($field_name, $value, $raw_filter){
		return "$field_name LIKE '$value%'";
	}

	function processFilter_ends($field_name, $value, $raw_filter){
		return "$field_name LIKE '%$value'";
	}
	
	function processFilter_isnot($field_name, $value, $raw_filter){
		return "$field_name != '$value'";
	}

	function processFilter_between($field_name, $value, $raw_filter){
		if (!empty($raw_filter[$field_name."_2_"]) && !empty($value)){
			$value2 = $raw_filter[$field_name."_2_"];
			return "($field_name > '$value' || $field_name == '$value') &&  ($field_name < '$value2' || $field_name == '$value2')";
		} else {
			return "";
		}		
	}

	function processFilter_greater($field_name, $value, $raw_filter){
		return "$field_name > '$value'";
	}

	function processFilter_less($field_name, $value, $raw_filter){
		return "$field_name < '$value'";
	}

	function processFilter_empty($field_name, $value, $raw_filter){
		if ($value == ''){
			return '';
		} else if ($value == '1'){
			return "$field_name == ''";
		} else if ($value == '0'){
			return "$field_name != ''";
		}
	}

	function processFilter_notempty($field_name, $value, $raw_filter){
		if ($value == ''){
			return '';
		} else if ($value == '1'){
			return "$field_name != ''";
		} else if ($value == '0'){
			return "$field_name == ''";
		}
	}

	function processFilter_oneof($field_name, $value, $raw_filter){
		// COLLECT VALUES
		$values = array();
		foreach ($raw_filter as $k => $v){
			if ($k == $this->name || substr($k, 0, strlen($this->name)) == $this->name && !empty($v)){
				$values[] = "$field_name == '$v'";
			}
		}
		// CREATE CONDITION
		return implode(' || ', $values);
	}

	// DATE FILTERS. TODO: �datetime?
	function processFilter_day($field_name, $value, $raw_filter){
		$v = str_pad($value, 2, "0", STR_PAD_LEFT);
		return "$field_name LIKE '%-%-$v'";
	}
	function processFilter_month($field_name, $value, $raw_filter){
		$v = str_pad($value, 2, "0", STR_PAD_LEFT);
		return "$field_name LIKE '%-$v-%'";
	}
	function processFilter_year($field_name, $value, $raw_filter){
		return "$field_name LIKE '$value-%-%'";
	}
}
