# Custom actions

Example of a custom action:
```
<?php
require_once('include/SuppleAction.php');

class CustomAction extends SuppleAction {

	public $name = 'custom_action';
    public $needACL = true;
	
	function __construct(){
		parent::__construct();
	}
	
	public function performAction($table, $get, $post, &$recursion_history = array()){
        global $db;

        // The action is invoked using index.php?action=custom_action
        // or admin.php?action=custom_action
        // When needACL == false, the system does not requires a Supple login

    }
}
```
