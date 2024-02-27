<?php

require_once('include/util.php');
require_once('include/SuppleGlobalArray.php');
require_once('include/global/SuppleSession.php');

class SuppleLanguage extends SuppleGlobalArray {

	public $name = 'lang';
	public static $lang_list;

	public static function getLangList(){
		if (!isset(self::$lang_list)){
			$db = SuppleApplication::getdb();
			$langs = $db->from('_languages')->getBeans();
			foreach ($langs as $lang){
				self::$lang_list[$lang->code] = $lang;
			}
		}
		return self::$lang_list;
	}

	public static function getLanguage(){
		$r = 'es';
		// Returns language: defined by session, or by config
		$ses = SuppleApplication::getsession();
		$ses_language = $ses->getValue('language');
		if (empty($ses_language)){
			$conf_language = SuppleApplication::getconfig()->getValue('language');
			if (!empty($conf_language)){
				$r = $conf_language;
			}
			$ses->setValue('language', $r);
		} else {
			$r = $ses_language;
		}
		return $r;
	}

	public static function setLanguage($code) {
		// Set on session!
		SuppleApplication::getsession()->setValue('language', $code);
	}
	
	function load(){
		$file_name = 'data/'.self::getLanguage() . "." . $this->name . '.php';
		$this->values = readArray($file_name, $this->name);
		$this->values['code'] = self::getLanguage();
	}

	function save(){
		// Save the entire configuration
		$file_name = "data/".self::getLanguage() . "." . $this->name.".php";
		writeArray($file_name, $this->values, $this->name);
	}
}

?>
