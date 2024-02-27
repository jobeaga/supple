# Custom beans

The system will create an instance of this class when retrieving rows from the table. You can declare your own functions on this object (to get used by templates or as data in the CMS), or redefine functions to add your own behaviour. Example of a custom bean:

```
<?php 
require_once('include/SuppleBean.php');
class CustomBean extends SuppleBean {

	function __construct($table = '', $id = '', $light = false){
		parent::__construct($table, $id, $light);
		$this->_table = 'custom_table_name';
    }

    function save(){
        // thing to do before save
        return parent::save();
        // or after saving
    }

    function populate($row, $do_post = true){
        $r = parent::populate($row, $do_post);
        // you can set or calculate values here:
        // $this->custom_value = 5;
        return $r;
    }

    // ...
}
```