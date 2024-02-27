<?php 

require_once('include/SuppleApplication.php');
require_once('include/global/SuppleLanguage.php');

class SuppleFieldRelationshipDropdown extends SuppleField { 

    public $type_name = 'RelationshipDropdown';

	public function preProcess($value) {

        $r = parent::preProcess($value);

        // TODO: Is this mysql? Does this have an index? It should!

        // don't save anything on the label field:
        $this->row[$this->name.'_'.$this->related_field] = '';

        if ($this->save_related_name){
            if (empty($value)){
                $this->row[$this->save_related_name] = '';
            } else {
                $db = SuppleApplication::getdb();
                $subrow = $db->from($this->related_table)->where(array('id' => $value))->getRow();
                $name = $subrow[$this->related_field];
                $this->row[$this->save_related_name] = $name;
            }
        }

        return $r;

    }

    // El nombre del relacionado, para las vistas de detalle y lista.
    public function postProcess($value) {
		$r = parent::postProcess($value);
        
        if (empty($this->save_related_name) || empty($this->row[$this->save_related_name])){
            if (empty($value)){
                $this->row[$this->name.'_'.$this->related_field] = '';
            } else {
                $db = SuppleApplication::getdb();
                $subrow = $db->from($this->related_table)->where(array('id' => $value))->getRow(); // getBean here causes infinite recursion
                $name = $subrow[$this->related_field];
                $name_lang = $subrow[$this->related_field.'_'.SuppleLanguage::getLanguage()];
                if ($name != null){
                    $this->row[$this->name.'_'.$this->related_field] = $name;
                } else if ($name_lang != null) {
                    $this->row[$this->name.'_'.$this->related_field] = $name_lang;
                } else {
                    $this->row[$this->name.'_'.$this->related_field] = $GLOBALS['lang']->getValue('LBL_NO_NAME');
                }
            }
        } else {
            $this->row[$this->name.'_'.$this->related_field] = $this->row[$this->save_related_name];
        }
        
		return $r;
	}

}