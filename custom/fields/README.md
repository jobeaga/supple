# Custom Data Types

Datatypes and HTML/js templates are defined in the CMS. Data types needs a Class only if there is something to process server-side before or after saving the value to DB.

Example:
```
<?php 
require_once('include/SuppleApplication.php');

class SuppleFieldCustom extends SuppleField { 
    public $type_name = 'CustomDatatype'; // matches by name

	public function preProcess($value) {
        
        // do something before saving the value
        
        $r = parent::preProcess($value);
        return $r;
    }

    public function postProcess($value) {
		$r = parent::postProcess($value);
        
        // do something after retieve
        // $this->name is the name of the field in DB, and $this->table is the name of the table
        // you can also use $this->row to access (and write) other values of the same row
		
        return $r;
	}

}
```