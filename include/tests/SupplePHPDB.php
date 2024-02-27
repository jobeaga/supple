<?php 

class SupplePHPDB extends SuppleUnitTestCase {

	public $t;
	public $db;
	public $table_name = 'suppleunit_test';
	public $base_name = 'suppleunit_testing';
	
	public function setUp(){
	
		$this->t = new SuppleTemplate();
		$this->db = SuppleApplication::getdb();
		// Create a empty table
		$this->db->setMapping($this->table_name, 'PhpArrayConnection', $this->base_name);
		$this->db->delete($this->table_name, array()); // empty table!
	
	}
	
	public function testValueInsertion(){
	
		$this->db->insert($this->table_name, array('test' => 'jorge'));
		$rows = $this->db->from($this->table_name)->getArray();
		$this->assertEquals(count($rows), 1);
	
	}
	
	public function testValueUpdateAll(){
	
		// UPDATE ALL
		$this->db->insert($this->table_name, array('test' => 'jorge'));
		$this->db->insert($this->table_name, array('test' => 'obeaga'));
		$this->db->update($this->table_name, array('test' => 'test'), array());
		
		$rows = $this->db->from($this->table_name)->getArray();
		foreach($rows as $row){
			$this->assertEquals($row['test'], 'test');
		}	
	}

	public function testValueUpdateOne(){
		
		// UPDATE ONE
		$this->db->insert($this->table_name, array('name' => 'jorge'));
		$this->db->insert($this->table_name, array('name' => 'obeaga'));
		$this->db->update($this->table_name, array('sets' => 'sets'), array('name' => 'jorge'));
		
		$rows = $this->db->from($this->table_name)->getArray();
		foreach($rows as $row){
			if (isset($row['name']) && $row['name'] == 'jorge'){
				$this->assertEquals($row['sets'], 'sets');
			}
		}
	
	}
	
	public function testNewColumn(){
	
	}
	
	public function testConditions(){
	
	
	}
	
	public function testMetaConditions(){
	
		$data = array(
			'table' => $this->table_name,
			'condition' => 'value == 1',
		);
		
		$this->db->insert($this->table_name, array('name' => 'jorge', 'value' => '1'));
		$this->db->insert($this->table_name, array('name' => 'error', 'value' => '2'));
	
		$template = '$$table(${$condition}){$name!}';
		
		$result = $this->t->parseString($template, $data);
	
		$this->assertEquals($result, 'jorge!');
	
	}

}

?>
