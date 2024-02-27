<?php 

class SuppleUnitTestCase {

	private $testResults = array();
	private $actualMethod = '';

	public function run(){
	
		// Gather test methods
		$methods = get_class_methods($this);
		foreach ($methods as $method){
			if (substr($method, 0, 4) == 'test'){
				$this->actualMethod = $method;
				$this->testResults[$method]['passed'] = 0;
				$this->testResults[$method]['failed'] = 0;
				$this->testResults[$method]['errorCount'] = 0;
				$this->testResults[$method]['errors'] = '';
				$this->testResults[$method]['testLog'] = '';
				$this->testResults[$method]['time'] = microtime(true);
				
				$this->setUp();
				$this->$method();
				$this->tearDown();
				
				$this->testResults[$method]['time'] = round(microtime(true) - $this->testResults[$method]['time'], 2);
			}
		}
		
		return $this->testResults;
	
	}
	
	public function setUp(){
		// Subclass responsibility
	}
	
	public function tearDown(){
		// Subclass responsibility
	}
	
	public function assert($e){
	
		$r = (bool) $e;
		if ($r){
			$this->testResults[$this->actualMethod]['passed']++;
		} else {
			$this->testResults[$this->actualMethod]['failed']++;
		}
		// Errors...
		$log = SuppleApplication::getlog();
		$errors = $log->getLastsPhpErrors();
		$has_errors = $log->hasPhpErrors();
		if ($has_errors){
			$this->testResults[$this->actualMethod]['errorCount']++;
			$this->testResults[$this->actualMethod]['errors'] .= implode('<br>', $errors)."<br>";
		}
		// return!?
		return $r;
	
	}
	
	public function addToLog($string){
		if ($this->testResults[$this->actualMethod]['testLog']) $this->testResults[$this->actualMethod]['testLog'] .= '<br>';
		$this->testResults[$this->actualMethod]['testLog'] .= $string;
	}
	
	public function assertEmpty($e){
		$this->addToLog("Empty <strong>$e</strong>");
		return $this->assert(empty($e));
	}
	
	public function assertNotEmpty($e){
		$this->addToLog("Not Empty <strong>$e</strong>");
		return $this->assert(!empty($e));
	}
	
	public function assertEquals($e, $v){
		$this->addToLog("Equals <strong>$e</strong> VS <strong>$v</strong>");
		return $this->assert($e == $v);
	}
	
	public function assertNotEquals($e, $v){
		$this->addToLog("Not Equals <strong>$e</strong> VS <strong>$v</strong>");
		return $this->assert($e != $v);
	}
	
	// etc...

}


?>