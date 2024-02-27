<?php 

require_once('include/SuppleBean.php');

class PackageFile extends SuppleBean {

	function __construct($table = '', $id = '', $light = false){

		parent::__construct($table, $id, $light);
		$this->_table = '_package_files';
    }

    function save(){

        $this->filename = str_replace('\\', '/', $this->filename);

        $r = parent::save();

		return $r;
    }

}