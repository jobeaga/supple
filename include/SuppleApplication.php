<?php

header("Cache-Control: no-cache, must-revalidate");
ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_DEPRECATED);

session_cache_expire(10080); // 7 days in minutes

SuppleApplication::ob_start(); // debería o no?
session_start();

// MAKE SHURE TO DISABLE ALL CACHE: ZEND OPCACHE, APC CACHE, etc.

require_once('include/util.php');
require_once('include/SuppleObject.php');
require_once('include/SuppleBean.php');
require_once('include/SuppleDBManager.php');
require_once('include/SuppleLog.php');
require_once('include/global/SuppleConfig.php');
require_once('include/global/SuppleSession.php');
require_once('include/SuppleTemplate.php');
require_once('include/SuppleAction.php');
require_once('include/SuppleACL.php');
require_once('include/SuppleUnit.php');
require_once('include/SuppleCache.php');
require_once('include/SuppleMail.php');
require_once('include/SuppleField.php');
require_once('include/SuppleGlobal.php');
require_once('include/global/SuppleLanguage.php');

// GLOBALS: 
$GLOBALS['db'] = SuppleApplication::getdb();
$GLOBALS['log'] = SuppleApplication::getlog();
$GLOBALS['config'] = SuppleApplication::getconfig();
$GLOBALS['cache'] = SuppleApplication::getcache();
$GLOBALS['ses'] = SuppleApplication::getsession();
$GLOBALS['lang'] = SuppleLanguage::getInstance();

class SuppleApplication {

	// Static vars
	private static $db;
	private static $log;
	private static $config;
	private static $tests;
	private static $cache;
	
	// DB Singleton
	public static function getdb(){
		if (empty(self::$db)){
			self::$db = new SuppleDBManager();
		}
		return self::$db;
	}
	// Log Singleton
	public static function getlog(){
		if (empty(self::$log)){
			self::$log = new SuppleLog();
		}
		return self::$log;
	}
	// Config Singleton
	public static function getconfig(){
		return SuppleConfig::getInstance();
	}
	// Tests cache:
	public static function gettests(){
		if (empty(self::$tests)){
			$t = new SuppleUnit();
			self::$tests = $t->runAll();
		}
		return self::$tests;
	}
	// SuppleCache
	public static function getcache(){
		if (empty(self::$cache)){
			self::$cache = new SuppleCache();
		}
		return self::$cache;
	}
	// SuppleSession
	public static function getsession(){
		require_once('include/global/SuppleSession.php');
		return SuppleSession::getInstance();
	}

	public static function getScriptName(){
		return substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/')+1);
	}

	
	public static function checkAndPerform($action){
	
		// TODO: Uses SuppleAction, SuppleACL, obtain the object and performs the action.
		
		$a = SuppleAction::getAction($action);
	
		if (!empty($a)){
		
			$suppleACL = new SuppleACL();
			$access = $suppleACL->checkAccess($a);

			if (!$access){
				/*self::getlog()->fatal("Invalid action: $action");
				echo self::getErrorToDisplay('invalid_action', "Invalid action.");
				die();*/

				return array('error' => "Invalid action: $action");

			} else {

				return $a->perform();
				
			}
			
		} else {
		
			self::getlog()->fatal("Action not found: $action");
			return array('error' => "Action not found: $action");
			
		}
		
	}
	
	public static function prepareValues($varname, &$var, &$post, &$get){

		$post = desCuotar($_POST);
		$get = $_GET;

		if (!empty($varname)){
		
			if (isset($_GET[$varname])) $var = $_GET[$varname];
			unset($get[$varname]);
			
		} else {
			// $var = ''; // should I?
		}
		
		
		if (self::getredirect()){
			unset($get['redirect']);
		}
		
		if (self::getactionname()){
			unset($get['action']);
		}
		
		if (isset($get['entity']))
			unset($get['entity']);


		return true;

	}
	
	public static function getUser(){
		global $db, $ses;
	
		$db = self::getdb();
		$r = 0;
		
		if ($ses->isSet('user')) {
		
			$p = $ses->getValue('pass'); // md5 already applied
			$u = $ses->getValue('user');
	
		} elseif ($ses->isSet('auth_key') || isset($_REQUEST['auth_key'])) {

			if (isset($_REQUEST['auth_key'])){
				$auth_key = $_REQUEST['auth_key'];
			} else {
				$auth_key = $ses->getValue('auth_key');
			}

			if (!empty($auth_key)){
				$auth_bean = $db->from('auth_keys')->where(array('auth_key' => $auth_key))->getBean();
				if (!empty($auth_bean->id)){
					$p = $auth_bean->pass; // md5 already applied
					$u = $auth_bean->user;
				}
			}

		}

		if (empty($p) || empty($u)){
			return $r;
		}

		$condition = array(
			'user' => $u,
			'pass' => $p,
		);
		
		$row = $db->from('users')->where($condition)->getRow();
		if ($row){
		
			$r = $row['id'];
		
		}
		
		return $r;

	}

	public static function getUserBean(){
		$user_id = self::getUser();
		return self::getdb()->from('users')->where(array('id' => $user_id))->getBean();
	}
	
	public static function getUserInfo(){
	
		$user_id = self::getUser();
		if ($user_id){
			$row = self::getdb()->from('users')->where(array('id' => $user_id))->getRow();
			
		} else {
			$row['isadmin'] = 0;
			$row['id'] = 0;
		}
	
		return array('isadmin' => $row['isadmin'], 'user_id' => $row['id']);
	
	}
	

	public static function redirect($url){
		header("Location: $url");
		die();
	}
	

	
	// PARSING TEMPLATE
	private static function handleParse($template, $data, $isstring = false){
		// refresh session data with auth_key
		self::processAuthKey();

		// Si tengo acción, la hago primero
		$action = self::getactionname();
		if ($action){
			$r = self::checkAndPerform($action);
			$redirect = self::getredirect();
			// las acciones deberíá redireccionar siempre <= OLD!
			$web = '';
			if ($redirect){
				self::redirect($redirect);
			} else {
				if (is_array($r)){
					// TODO: Add login status to return value.
					$current_user = self::getUser();
					$r['login'] = !empty($current_user);
				}
				// Return values
				ob_clean();
				echo json_encode($r);
				die();
			}
		} elseif (file_exists($template) || $isstring){
			// Si no tengo que realizar accion, y hay template, lo parseo.
			$data = mixArrays($data, self::generateDataArray());
			$t = new SuppleTemplate();
			if ($isstring){
				$web = $t->parseString($template, $data);
			} else {
				$web = $t->parseFile($template, $data);	
			}
			// Errors: 
			$web = self::getErrorsGUI().$web;
		} else {
			$web = self::getErrorToDisplay('template_not_found', 'Template not found');
		}
		
		return $web;		
	}

	private static function handleParseTemplate($template_file, $data){

		return self::handleParse($template_file, $data, false);
	
	}
	
	private static function handleParseString($template_string, $data){
		
		return self::handleParse($template_string, $data, true);

	}
	
	public static function parseTemplate($template, $data = array(), $return = false, $file = 'index'){
	
		if (file_exists('custom/templates/'.$template.'/'.$file.'.html')){
			$web = self::handleParseTemplate('custom/templates/'.$template.'/'.$file.'.html', $data);	
		} else if (file_exists('templates/'.$template.'/'.$file.'.html')){
			$web = self::handleParseTemplate('templates/'.$template.'/'.$file.'.html', $data);	
		} else {
			$web = "Missing template";
		}
		
		$config = self::getconfig();
		if (strtolower($config->getValue('utf-8')) == 'utf-8'){
			$web = utf8_decode($web);
		}
		if (preg_match('!!u', $web)) $web = utf8_decode($web);
		$web = cutes($web);
		//if (ord(substr($web, 0, 1)) == 63) $web = substr($web, 1);

		$log = self::getlog();		
		$log->getPhpErrors();

		// SuppleApplication::ob_clean();
		if ($return){
			return $web;
		} else {
			echo $web;
		}
		// SuppleApplication::ob_end_flush();
		
		$current_user = self::getUser();
		$user_info = self::getUserInfo();
		
		if (!self::get_ismobile() && !self::get_isajax() && !empty($current_user) && $config->getValue('show_log_console') && $user_info['isadmin'] == 1 && $template == 'admin' && !empty($web)){
			//SuppleApplication::ob_clean();
			echo $log->getConsoleHTML();
			//SuppleApplication::ob_flush();
		} else {
			// TODO: Save the php errors log	
		}
		
		self::getcache()->saveCache(); // ???
	
	}
	
	public static function getredirect(){
	
		return ((isset($_GET['redirect']))?$_GET['redirect']:'');
	
	}
	
	public static function getactionname(){
	
		return ((isset($_GET['action']))?$_GET['action']:'');
	
	}
	
	public static function get_ismobile(){
	
		return ((isset($_GET['_ismobile']))?$_GET['_ismobile']:is_mobile_browser());
	
	}
	
	public static function get_isajax(){
	
		return ((isset($_GET['_isajax']))?$_GET['_isajax']:0);
	
	}
	
	public static function getDateVars(){
	
		$gmt_offset = self::getconfig()->getValue('gmt_offset');
		$time = time() + (60 * 60 * $gmt_offset); 
		
		$date_format = self::getconfig()->getValue('php_date_format');
		$datetime_format = self::getconfig()->getValue('php_datetime_format');
		
		return array('_today' => gmdate($date_format, $time), '_dbdate' => gmdate('Y-m-d', $time), '_today_time' => gmdate($datetime_format, $time), '_day' => gmdate('d', $time), '_month' => gmdate('m', $time), '_year' => gmdate('Y', $time), '_hours' => gmdate('H', $time), '_minutes' => gmdate('i', $time));
	
	}
	
	public static function generateDataArray(){
	
		$data = array();
		
		// GET
		$data = mixArrays($data, $_GET);
		
		// SESSION
		// $data['session_id'] = self::getSessionId(); // ???
		
		// USER
		$data = mixArrays($data, self::getUserInfo());
		
		// MOBILE BROWSER
		$data['_ismobile'] = self::get_ismobile();
		
		// AJAX REQUEST
		$data['_isajax'] = self::get_isajax();
		
		// DATE
		$data = mixArrays($data, self::getDateVars());
			
		return $data;
	
	}

	private static function processAuthKey(){
		global $ses, $db;
		if (isset($_POST['_auth_key'])) {
			$auth_key = $_POST['_auth_key'];
			if (!empty($auth_key)){
				$auth_bean = $db->from('auth_keys')->where(array('auth_key' => $auth_key))->getBean();
				if (!empty($auth_bean->id)){
					$ses->setValue('pass', $auth_bean->pass); // md5 already applied
					$ses->setValue('user', $auth_bean->user);
					$ses->setValue('auth_key', $auth_key);
					// keep alive:
					$last_login = date('Y-m-d H:i:s');
					$db->update('auth_keys', array('last_login' => $last_login), array('id' => $auth_bean->id));
				}
			}
			unset($_POST['_auth_key']);
		}
	}

	// APP ERRORS IN GUI
	public static function setError($message){
		global $ses;
		if ($ses->isSet('error_messages')) {
			$error_messages = $ses->getValue('error_messages');
		} else {
			$error_messages = array();
		}
		$error_messages[] = $message;
		$ses->setValue('error_messages', $error_messages);
	}

	public static function readErrors(){
		global $ses;
		if ($ses->isSet('error_messages')) {
			return $ses->getValue('error_messages');
		} else {
			return array();			
		}
	}

	public static function getErrors(){
		global $ses;
		$r = self::readErrors();
		$ses->unsetValue('error_messages');
		return $r;
	}

	public static function getErrorsGUI(){
		$errors = self::getErrors();
		$html = '';
		foreach ($errors as $message){
			$html .= '<div class="message"><a class="message_close" onclick="this.parentNode.parentNode.removeChild(this.parentNode)">&times;</a>'.$message.'</div>';
		}
		return $html;
	}
	
	// SESSION DATA
	public static function getSessionId(){
		
		$connection = self::getdb();
		
		$session_id = false;
		$session_duration = 21600; // 6 hours TODO: Get this value from config
		$time = time(); // Now!
		
		// And return
		return $session_id;
		
	}
	/*
	public static function incrementCounter(){
	
		$config = self::getconfig();
	
		// get
		$counter = $config->getValue('counter');
		// increment
		$counter = (int)$counter + 1;
		// set
		$config->setValue('counter', $counter);
		
	}
	*/
/*
	public static function getCounter(){
		
		self::getSessionId();
		
		$value = self::getconfig()->getValue('counter');
		
		if ($value){
			return (int)$value;
		} else {
			return 0;
		}
	}
	*/

	public static function getErrorToDisplay($error_file, $alternative_string){
		if (file_exists('custom/templates/error/'.$error_file.'.html')){
			return file_get_contents('custom/templates/error/'.$error_file.'.html');
		} else if (file_exists('templates/error/'.$error_file.'.html')){
			return file_get_contents('templates/error/'.$error_file.'.html');
		} else {
			return $alternative_string;
		}
	}

	public static function getJsScripts() {
		$r = '';

		$js = glob('include/js/*.js');
		$i = array_search('include/js/jquery.min.js', $js);
		if ($i !== false){
			$r .= '<script type="text/javascript" src="'.$js[$i].'"></script>';
			unset($js[$i]);
		}
		foreach ($js as $js_file){
			$r .= '<script type="text/javascript" src="'.$js_file.'"></script>';
		}
		
		$css = glob('include/js/*.css');
		foreach ($css as $css_file){
			$r .= '<link href="'.$css_file.'" rel="stylesheet" type="text/css">';
		}

		// CUSTOM!!!
		$js = glob('custom/js/*.js');
		foreach ($js as $js_file){
			$r .= '<script type="text/javascript" src="'.$js_file.'"></script>';
		}
		$css = glob('custom/js/*.css');
		foreach ($css as $css_file){
			$r .= '<link href="'.$css_file.'" rel="stylesheet" type="text/css">';
		}

		return $r;
	}

	public static function getMetadata() {
		return self::getcache()->getMetadata();
	}

	public static function getCacheData() {
		global $db;
		global $config;
		$tables = $config->getValue('cache_tables');
		$row_limit = $config->getValue('cache_tables_row_limit');
		$r = array();
		if (trim($tables) != ''){
			$ts = explode(',', $tables);
			foreach ($ts as $t){
				$data = $db->from($t)->limit($row_limit)->getBeans();
				foreach ($data as $bean){
					$r[$t][$bean->id] = $bean->getData();
					$r[$t][$bean->id]['_nextid'] = '';
					$r[$t][$bean->id]['_previousid'] = '';
				}
			}
		}
		return $r;
	}

	public static function getExtraParameters() {
		$data = $_GET;
		unset($data['entity']);
		unset($data['view']);
		unset($data['id']);
		return $data;
	}
	
	// OB Functions override:	
	public static function ob_get_contents(){
	
		$r = "";
		if (function_exists('ob_get_contents')){
			$r = ob_get_contents();
		}
		return $r;
	
	}
	
	public static function ob_get_clean(){
	
		$r = "";
		if (function_exists('ob_get_clean')){
			$r = ob_get_clean();
		}
		return $r;
	
	}
	
	public static function ob_clean(){
	
		if (function_exists('ob_clean')){
			ob_clean();
		}
	
	}
		
	public static function ob_flush(){
	
		if (function_exists('ob_flush')){
			ob_flush();
		}
	
	}
	
	public static function ob_start(){
	
		if (function_exists('ob_start')){
			ob_start();
		}
	
	}
	
	public static function ob_end_clean(){
	
		if (function_exists('ob_end_clean')){
			ob_end_clean();
		}
	
	}
	
	public static function ob_end_flush(){
	
		if (function_exists('ob_end_flush')){
			ob_end_flush();
		}
	
	}
	

}


