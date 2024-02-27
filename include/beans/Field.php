<?php 

require_once('include/SuppleBean.php');

class Field extends SuppleBean {

	function __construct($table = '', $id = '', $light = false){

		parent::__construct($table, $id, $light);
		$this->_table = '_fields';

	}

	// deprecated!!!
	function getEditTemplate($value){
		
		$userInfo = SuppleApplication::getUserInfo();
		$templates = $this->db->from('_datatypes')->where(array('id' => $this->type))->getRow();
		
		if ($this->access == 'write') 
			return $templates['edittemplate'];

		if ($this->access == 'writeonce' && (trim($value) == '' || trim($value) == '""')) 
			return $templates['edittemplate'];

		if ($this->access == 'writeonce' && trim($value) != '' && trim($value) != '""') 
			return $templates['viewtemplate'];

		if ($this->access == 'onlyadmin' && $userInfo['isadmin'] == 1) 
			return $templates['edittemplate'];

		if ($this->access == 'onlyadmin' && $userInfo['isadmin'] == 0) 
			return $templates['viewtemplate'];

		if ($this->access == 'readonly') 
			return $templates['viewtemplate'];

		return $templates['edittemplate'];
	}



}