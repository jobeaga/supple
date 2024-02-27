<?php

require_once('include/util.php');
require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');

class SuppleActionConvertDropdown extends SuppleAction {

	public $name = 'convertDropdown';
    public $domain = 'global';
 
    public function perform(){
        global $db;

        $fields = $db->from('_fields')->where(array('type' => '14'))->getBeans();
        $langs = SuppleLanguage::getLangList();
        $dwl_table = '_dropdown_lists';
        $dwo_table = '_dropdown_options';
        
        $c = 0;
        foreach ($fields as $f){
            if (empty($f->option_list)){
                // GET PARENT ENTITY
                $e = SuppleBean::getBean('_entities', $f->parent);
                if (!empty($e->id)){
                    // GENERATE DWLIST NAME
                    $dwlist_name = "{$e->table}_{$f->name}";
                    // CHECK IF LIST EXISTS
                    $list_exists = $db->from($dwl_table)->where(array('name' => $dwlist_name))->getCount();
                    if (!$list_exists && !empty($f->options)){
                        // CREATE LIST
                        $list = SuppleBean::getBean($dwl_table);
                        $list->name = $dwlist_name;
                        $list->save();

                        // CREATE OPTIONS: do not create empty options.
                        $s1 = chr($f->sep1);
                        $s2 = chr($f->sep2);
                        $ops = explode($s1, $f->options);
                        foreach ($ops as $o){
                            $parts = explode($s2, $o);

                            if ($parts[0] != ''){
                                $option = SuppleBean::getBean($dwo_table);
                                $option->list = $list->id;
                                $option->key = $parts[0];
                                $option->value = '';
                                foreach ($langs as $code => $lang){
                                    $mf = 'value_'.$code;
                                    $option->$mf = $parts[1];
                                }
                                $option->save();
                            }
                        }
                        
                        // ASSIGN LIST TO FIELD
                        $f->option_list = $list->id;
                        // SAVE FIELD
                        $f->save();
                        $c++;
                    }
                }
            }
        }

        return "$c lists created";

    }

}
	