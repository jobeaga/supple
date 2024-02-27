<?php 

class SuppleTemplateParsing extends SuppleUnitTestCase {

	public $t;
	
	public function setUp(){
	
		$this->t = new SuppleTemplate();
	
	}

	public function testSimpleValueParsing(){
		
		$value = 'jorge';
		$data = array('test' => $value);
		$template = '$test';
		
		$result = $this->t->parseString($template, $data);
		
		$this->assertEquals($result, $value);
		
	}
	
	public function testConditionedParsing(){
	
		$value = 'jorge';
		$data = array('test' => $value);
		$template = '$($test)[$test]';
		
		$result = $this->t->parseString($template, $data);
		
		$this->assertEquals($result, $value);
	
	}
	
	public function testConditionedAssign(){
	
		$value = 'jorge';
		$data = array('test' => $value);
		$templates = array(
			'$($test)[$test](=result)!$result',
			'$(0)[][$test](=result)!$result',
		);
		$expected_value = '!jorge';
		
		foreach ($templates as $template){
			$result = $this->t->parseString($template, $data);
			$this->assertEquals($result, $expected_value);
		}

	}
	
	public function testParsedAssign(){
	
		$value = 'jorge';
		$data = array('test' => $value);
		$templates = array(
			'${$test}(=result)!$result',
		);
		$expected_value = '!jorge';
		
		foreach ($templates as $template){
			$result = $this->t->parseString($template, $data);
			$this->assertEquals($result, $expected_value);
		}
	
	}
	
}


?>
