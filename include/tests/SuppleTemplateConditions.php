<?php 

class SuppleTemplateConditions extends SuppleUnitTestCase {

	public $t;
	
	public function setUp(){
	
		$this->t = new SuppleTemplate();
	
	}
	
	public function testBoolConditions(){
		
		$data = array('test' => 1);
		$templates = array(
			'$($test)[jorge]',
			);
		
		$expectedValue = 'jorge';

		foreach ($templates as $template){
			$result = $this->t->parseString($template, $data);
			$this->assertEquals($result, $expectedValue);
		}
		
		// ADITIONAL TEST:
		$template = 'TEST$($test)[jorge]TEST';
		$expectedValue = 'TESTjorgeTEST';
		$result = $this->t->parseString($template, $data);
		$this->assertEquals($result, $expectedValue);
		
	}
	
	public function testStringConditions(){
		
		$cases = array(
			// basic
			array(
				'data' => array('string' => 'jorge'),
				'templates' => array(
					' $($string == "jorge")[jorge] ',
					' $($string == \'jorge\')[jorge] ',
					' $("jorge" == $string)[jorge] ',
					' $(\'jorge\' == $string)[jorge] ',
				),
				'expectedValue' => ' jorge ',
			),
			// with spaces
			array(
				'data' => array('string' => 'one string'),
				'templates' => array(
					' $($string == "one string")[jorge] ',
					' $($string == \'one string\')[jorge] ',
					' $("one string" == $string)[jorge] ',
					' $(\'one string\' == $string)[jorge] ',
				),
				'expectedValue' => ' jorge ',
			),
			// with quotes
			array(
				'data' => array('string' => "one's string"),
				'templates' => array(
					' $($string == "one\'s string")[jorge] ',
					' $("one\'s string" == $string)[jorge] ',
				),
				'expectedValue' => ' jorge ',
			),
		
		);
		
		foreach ($cases as $case){
			$data = $case['data'];
			$templates = $case['templates'];
			$expectedValue = $case['expectedValue'];
			foreach ($templates as $template){
				$result = $this->t->parseString($template, $data);
				$this->assertEquals($result, $expectedValue);
			}
		}
		
	}
	
	public function testVariablesAsBool(){
	
		$data = array('string' => 'http://thecomplicated.value/string\'s "trace"',
					  'number' => '5',
					  'zero' => '0',
					  'nothing' => '',
					  'name' => 'Shorsh',
		);
		$templates = array(
			' $($string)[jorge] ',
			' $($foo)[][jorge] ',
			' $($number)[jorge] ',
			' $($zero)[][jorge] ',
			' $($nothing)[][jorge] ',
			' $($name)[jorge] ',
			);
		$expectedValue = ' jorge ';
		
		foreach ($templates as $template){
			$result = $this->t->parseString($template, $data);
			$this->assertEquals($result, $expectedValue);
		}
	
	
	}
	
	public function testMetaVariables(){
	
		// DEPRECATED
		return;

		$data = array(
			'string' => 'jorge',
			'val1' => 'jorge',
			'val2' => 'error',
		);
		$templates = array(
			'${"\$string"=="jorge"}(=condicion) $(${$condicion})[jorge][error] ',
			'${"\$string"=="error"}(=condicion) $(${$condicion})[error][jorge] ',
			'${"\$string"=="jorge"}(=condicion) $($condicion)[jorge][error] ',
			'${"\$string"=="error"}(=condicion) $($condicion)[jorge][error] ',
			'${"\$string"=="$val1"}(=condicion) $(${$condicion})[jorge][error] ',
			'${"\$string"=="$val2"}(=condicion) $(${$condicion})[error][jorge] ',
			'${"\$string"=="\$val1"}(=condicion) $($condicion)[jorge][error] ',
			'${"\$string"=="\$val2"}(=condicion) $($condicion)[jorge][error] ',
			'${"\$string"=="\$string"}(=condicion) $(${$condicion})[jorge][error] ',
		);
		$expectedValue = ' jorge ';
		
		foreach ($templates as $template){
			$result = $this->t->parseString($template, $data);
			$this->assertEquals($result, $expectedValue);
		}
		
		
	
	}

}

// TODO: Try the same but with tables...

?>
