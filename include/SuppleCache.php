<?php 

require_once('include/SuppleApplication.php');

class SuppleCache {

	var $cache = array();
	var $cache_log = array();
	var $cache_path = 'cache/suppleCache';
	var $cache_maxsize = 4; // in MB

	var $cache_ram = array(); // expires with request

	function __construct(){

		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'download') return;
		if (isset($_SERVER['PHP_SELF']) && substr($_SERVER['PHP_SELF'], -9) == 'index.php') return;
	
		$startTime = SuppleApplication::getlog()->getStartTime();
		if (SuppleApplication::getconfig()->getValue('persist_cache')){
			// Divided into multiple files
			$a = glob($this->cache_path."/*.php");
			if (!empty($a)){
			foreach ($a as $cache_filename){
					$t = getFileName($cache_filename);
					if (file_exists($cache_filename) && filesize($cache_filename) < (1024*1024*$this->cache_maxsize)){
						require($cache_filename);
						$this->cache[$t] = $cache;
					} elseif (filesize($cache_filename) >= (1024*1024*$this->cache_maxsize)) {
						unlink($cache_filename);
						$this->cache[$t] = array();
					}
				}
			}
		}
		SuppleApplication::getlog()->logEndTime('suppleCache_restore', $startTime);
	}
	
	function saveCache() {
	
		$startTime = SuppleApplication::getlog()->getStartTime();
		if (SuppleApplication::getconfig()->getValue('persist_cache')){
			// Create folder if it does not exists
			createFolder($this->cache_path);
			// Divide into multiple files
			foreach ($this->cache as $t => $c) {
				$cache_filename = $this->cache_path . "/" . $t . ".php";
				if ($cache_filename /*&& is_writable($cache_filename)*/){
					$content = '<?php $cache = '.var_export($c, true).'; /* DATE: '.date('Y-m-d H:i:s').' */ ?>';
					file_put_contents($cache_filename, $content);
				}
			}
		}
		SuppleApplication::getlog()->logEndTime('suppleCache_save', $startTime);

	}

	public function get($name, $key){
	
		if ($this->exists($name, $key)){
			// LOG
			if (isset($this->cache_log[$name]['hit'])){
				$this->cache_log[$name]['hit']++;
			} else {
				$this->cache_log[$name]['hit'] = 1;
			}
			
			// RETURN
			return $this->cache[$name][$key];
			
		} else {
			return false;
		}
	
	}
	
	public function set($name, $key, $value){
	
		// LOG
		if (isset($this->cache_log[$name]['miss'])){
			$this->cache_log[$name]['miss']++;
		} else {
			$this->cache_log[$name]['miss'] = 1;
		}
		
		// SET
		$this->cache[$name][$key] = $value;

	}
	
	public function exists($name, $key){
	
		return (isset($this->cache[$name]) && isset($this->cache[$name][$key]));
	
	}

	public function create_md5($data){

		return md5(print_r($data, true));

	}

	public function get_ram($name, $key){
	
		if ($this->exists_ram($name, $key)){
			// LOG
			if (isset($this->cache_log[$name]['hit'])){
				$this->cache_log[$name]['hit']++;
			} else {
				$this->cache_log[$name]['hit'] = 1;
			}
			
			// RETURN
			return $this->cache_ram[$name][$key];
			
		} else {
			return false;
		}
	
	}
	
	public function set_ram($name, $key, $value){
	
		// LOG
		if (isset($this->cache_log[$name]['miss'])){
			$this->cache_log[$name]['miss']++;
		} else {
			$this->cache_log[$name]['miss'] = 1;
		}
		
		// SET
		$this->cache_ram[$name][$key] = $value;

	}
	
	public function exists_ram($name, $key){
	
		return (isset($this->cache_ram[$name]) && isset($this->cache_ram[$name][$key]));
	
	}

	public function unset_ram($name, $key){
		unset($this->cache_ram[$name][$key]);
	}

	public function unset_ram_all($name){
		unset($this->cache_ram[$name]);
		$this->cache_ram[$name] = array();
	}

	public function getMetadata() {
		$metadata_cache_file = $this->cache_path."/metadata_cache.json";
		if (file_exists($metadata_cache_file)){
			// return file_get_contents($metadata_cache_file);
			return '{}';
		} else {
			file_put_contents($metadata_cache_file, '1');
			global $db;
			$maps = $db->mappings['PhpArrayConnection']['metadata']['tables'];
			$metadata = array();
			foreach ($maps as $table){
				$data = $db->from($table)->getBeans();
				foreach ($data as $bean){
					$metadata[$table][$bean->id] = $bean->getData();
					$metadata[$table][$bean->id]['_nextid'] = '';
					$metadata[$table][$bean->id]['_previousid'] = '';
				}
			}
			$je_metadata = json_encode($metadata);
			file_put_contents($metadata_cache_file, $je_metadata);
			
			// return $je_metadata;
			return '{}';
		}
	}

	public function updateMetadata() {
		$this->clearMetadata();
		$this->getMetadata(); // Rebuilds
	}

	public function clearMetadata() {
		$metadata_cache_file = $this->cache_path."/metadata_cache.json";
		if (file_exists($metadata_cache_file)){
			unlink($metadata_cache_file);
		}
	}




}

?>
