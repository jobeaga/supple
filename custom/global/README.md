# Custom global information

You can declare an object to store and retrieve globally available information. Example:

```
<?php

require_once('include/SuppleGlobal.php');
class SuppleCustomGlobalInfo extends SuppleGlobal {

	public $name = 'custom_global_info';

	function getValues(){
		// Fetch, retrieve or calculate ALL data.
		return $data;
	}

	function getValue($attribute){
		// get only one value. For example:
        $values = $this->getValues();
		return (isset($values[$attribute]))?$values[$attribute]:'';
	}

	function setValue($attribute, $value){
		// Save $value under $attribute
	}

	function load(){
		// code executed every time the object is created in every http request, even when the values are not used
	}

	function save(){
		// code executed every time the request is over
	}
}

```