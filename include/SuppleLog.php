<?php

// Clase muy simple para guardar info en supple.log
// $suppleLog = new SuppleLog();

class SuppleLog {

	public $logfile = 'supple.log';
	public $level = 4; // warn o mayor
	private $times = array();
	private $startTime;
	private $phperrors = array();
	private $phperrors_visited = array();
	private $phperrors_count = 0;
	private $log_times = 1;
	
	public function __construct(){
		$this->log_times = SuppleApplication::getconfig()->getValue('log_times');
		$this->startTime = microtime(true); // always log this time
	}
	
	private function log($string, $level){
	
		$date = date("Y-m-d H:i:s");
		$ip = $_SERVER['REMOTE_ADDR'];
		$url = $_SERVER['REQUEST_URI'];

		if (is_array($string)) $string = print_r($string, true);
	
		$s = "$date $ip [$level] - $string - $url \n";
	
		file_put_contents($this->logfile, $s, FILE_APPEND);
	
	}
	
	public function info($string){
		if ($this->level <= 1) $this->log($string, "Info");
	}
	public function debug($string){
		if ($this->level <= 2) $this->log($string, "Debug");
	}
	public function notice($string){
		if ($this->level <= 3) $this->log($string, "Notice");
	}
	public function warn($string){
		if ($this->level <= 4) $this->log($string, "Warn");
	}
	public function acl($string){
		if ($this->level <= 5) $this->log($string, "ACL");
	}
	public function fatal($string){
		if ($this->level <= 6) $this->log($string, "Fatal");
	}
	
	public function getStartTime(){
		if ($this->log_times){
			return microtime(true);
		}
	}
	public function logEndTime($name, $startTime){
		if ($this->log_times){
			if (!empty($startTime)){
				$time = microtime(true) - $startTime;
				$this->times[$name]['sum'] = ((isset($this->times[$name]['sum']))?$this->times[$name]['sum']:0) + $time;
				$this->times[$name]['count'] = ((isset($this->times[$name]['count']))?$this->times[$name]['count']:0) + 1;
				if (!isset($this->times[$name]['max']) || $this->times[$name]['max'] < $time) $this->times[$name]['max'] = $time;
			}
		}
	}
	public function getTimeLog(){
	
		$this->log_times = 1;
		$this->logEndTime('_total', $this->startTime);
		
		ksort($this->times);
		
		$r = "<table>";
		$r .= "<tr><th> tag </th><th> count </th><th> sum </th><th> avg </th><th> max </th></tr>";
		foreach ($this->times as $name => $ts){
			$sum = $ts['sum'];
			$count = $ts['count'];
			$max = $ts['max'];
			$avg = fix_round($sum / $count, 3);
			$sum = fix_round($sum, 3);
			$max = fix_round($max, 3);
			
			$r .= "<tr><td> $name </td><td> $count </td><td> $sum </td><td> $avg </td><td> $max </td></tr>";
		}
		$r .= "</table>";
		return $r;
	}
	
	public function getLogTail(){
		$first = true; // discard  the first line
		$r = "";
		if (file_exists($this->logfile)){
			$fp = fopen($this->logfile, 'r');
			fseek($fp, -2000, SEEK_END);
			while (($data = fgets($fp)) !== false){
				if ($first){ $first = false; } else { $r .= htmlentities($data) . "<br>"; }
			}
			fclose($fp);
		}
		//return htmlentities($r);
		return $r;
	
	}
	
	public function getConsoleHTML(){
	
		if (empty($this->phperrors)){
			$errorlog = '';
		} else {
			$errorlog = implode('<br /><br />', $this->phperrors);
		}
	
		$r = "<div id=debug_tools>";

		// TIME LOG
		$timelog = $this->getTimeLog();
		$r .= "<li><a href=\"javascript:toggleElement('timelog', 'timelog_button', 'hide timelog', 'show timelog', true)\" id=timelog_button>timelog</a></li>
				  <div id=timelog> $timelog </div>
				  <script>toggleElement('timelog', 'timelog_button', 'hide timelog', 'show timelog', true); </script>";

		// PHP ERRORS
		$r .= "<li><a href=\"javascript:toggleElement('phperrors', 'phperrors_button', 'hide php errors', 'show php errors', true)\" id=phperrors_button>hide php errors</a></li>
				  <div id=phperrors style=z-index:2> $errorlog </div> ";
				  
		if (empty($errorlog)) $r .= "<script> toggleElement('phperrors', 'phperrors_button', 'hide php errors', 'show php errors', true); </script>";

		// SUPPLE LOG
		$slog = $this->getLogTail();
		$r .= "<li><a href=\"javascript:toggleElement('supplelog', 'supplelog_button', 'hide supple log', 'show supple log', true)\" id=supplelog_button>supplelog</a></li>
				  <div id=supplelog style=z-index:2><pre> $slog </pre></div> 
				  <script> toggleElement('supplelog', 'supplelog_button', 'hide supple log', 'show supple log', true); </script>";
		
		$r .= "</div>";
		
		return $r;
	
	}
	
	public function getLastPhpError(){
		
		$errorlog = SuppleApplication::ob_get_contents();
		//SuppleApplication::ob_clean(); // ???
		$errors = preg_split('/<[bB][rR][ ]*\/>/', $errorlog);
		$phperrors = array();
		foreach ($errors as $error){
		  if (trim($error) != '') $phperrors[] = trim($error);
		}
		
		$lastindex = count($phperrors) - 1;
		
		if (isset($phperrors[$lastindex]) && empty($this->phperrors_visited[$lastindex])){
		
			$this->phperrors_visited[$lastindex] = 1;
			return $phperrors[$lastindex];
		
		} else {
		
			return '';
		
		}
		
	}
	
	public function getLastsPhpErrors(){
	
		$errorlog = SuppleApplication::ob_get_contents();
		$errors = preg_split('/<[bB][rR][ ]*\/>/', $errorlog);
		$phperrors = array();
		foreach ($errors as $error){
		  if (trim($error) != '') $phperrors[] = trim($error);
		}
		
		$r = array();
		if ($this->phperrors_count != count($phperrors)){
			for ($i = $this->phperrors_count; $i < count($phperrors); $i++){
				$r[] = $phperrors[$i];
			}
		}
		return $r;
	
	}
	
	public function hasPhpErrors(){
	
		$errorlog = SuppleApplication::ob_get_contents();
		$errors = preg_split('/<[bB][rR][ ]*\/>/', $errorlog);
		$phperrors = array();
		foreach ($errors as $error){
		  if (trim($error) != '') $phperrors[] = trim($error);
		}
		
		$r = ($this->phperrors_count != count($phperrors));
		$this->phperrors_count = count($phperrors);
		return $r;
	
	}
	
	public function getPhpErrors(){
	
		$errorlog = SuppleApplication::ob_get_clean();
		
		$errors = preg_split('/<[bB][rR][ ]*\/>/', $errorlog);
		
		// $this->phperrors = array();

		foreach ($errors as $error){
		  if (trim($error) != '') $this->phperrors[] = trim($error);
		}
	
	}


}


