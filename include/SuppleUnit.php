<?php 

require_once('include/SuppleApplication.php');
require_once('include/SuppleUnitTestCase.php');

class SuppleUnit {

	private $testResults = array();

	public function runAll(){
	
		// Gather all tests and run them...
		$a = glob('include/tests/*.php');
		foreach ($a as $file){
		
			require_once($file);
			$className = getFileName($file);
			if (class_exists($className)){
				$object = new $className();
				// if the method is implemented...
				if (method_exists($object, 'run'))
					$this->testResults[$className] = $object->run();
			}
		
		}
		
		SuppleApplication::ob_clean();
		
		return $this->testResults;
	
	}

	public function displayResults($results){

		// SHOW TESTS!
		$failed_count = 0;
		$total_count = 0;
		$passed_count = 0;
		$error_count = 0;
		$errors = "";

		foreach ($results as $test_class => $tests){
			foreach ($tests as $test_name => $result){
				$failed_count += $result['failed'];
				$total_count += $result['failed'] + $result['errorCount'] + $result['passed'];
				$passed_count += $result['passed'];
				$error_count += $result['errorCount'];		
				if (empty($result['failed']) && empty($result['errorCount'])){
					//??
				} else {
					$errors .= "<tr><td>$test_class</td><td>$test_name</td>";
					foreach ($result as $key => $value){
						$errors .= "<td>$key = $value</td>";
					}
					$errors .= "</tr>";
				}
			}
		}

		return "
		<style>
		  table { border: 1px solid black; }
		  tr { border: 1px solid black; }
		  td { border: 1px solid black; }
		</style>
		<table>
			<tr><td colspan=8>TOTAL: $total_count</td></tr>
			<tr><td colspan=8>Passed: $passed_count</td></tr>
			<tr><td colspan=8>Failed: $failed_count</td></tr>
			<tr><td colspan=8>Errors: $error_count</td></tr>
			$errors
		</table>";

	}
	


}


?>