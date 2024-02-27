<?php 

require_once('include/SuppleObject.php');
require_once('include/SuppleGlobal.php');

class SuppleGlobalArray extends SuppleGlobal {
	
	public $values;
	public $name;

	function setValue($attribute, $value){
		$this->values[$attribute] = $value;
		$this->save();	
	}

	function getValue($attribute){
		$r = '';
		if (isset($this->values[$attribute])){
			$r = $this->values[$attribute];
		}
		return $r;
	}
	
	function getValues(){
		return $this->values;
	}
	
	function setValues($values){
		$this->values = $values;
		$this->save();
	}

	function load(){
		$file_name = $this->getFilename(false);
		$custom_file_name = $this->getFilename(true);
		
		$this->values = readArray($file_name, $this->name);
		if (file_exists($custom_file_name)){
			$custom_values = readArray($custom_file_name, $this->name);
			foreach ($custom_values as $att => $val){
				$this->setValue($att, $val);
			}
		}

	}

	function getFilename($custom = true){
		$file_name = "data/".$this->name.".php";
		if ($custom){
			$file_name = "custom/".$this->name.".php";
		}
		return $file_name;
	}

	function save(){
		// SAVE ON CUSTOM
		$file_name = $this->getFilename(false);
		$custom_file_name = $this->getFilename(true);
		$std_values = readArray($file_name, $this->name);
		$cstm_values = array();
		foreach ($this->values as $att => $val){
			if (!isset($std_values[$att]) || $std_values[$att] != $val){
				$cstm_values[$att] = $val;
			}
		}
		$old_cstm_values = $this->getOldValues(true);
		if ($old_cstm_values != $cstm_values){
			$custom_content = '<?php $'.$this->name.' = '.var_export($cstm_values, true).'; ?>';
			file_put_contents($custom_file_name, $custom_content);
		}
	}

	function getOldValues($custom = true){
		$file_name = $this->getFilename($custom);
		return readArray($file_name, $this->name);
	}

}