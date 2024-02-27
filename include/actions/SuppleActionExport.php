<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');
require_once('include/SuppleField.php');


class SuppleActionExport extends SuppleAction {

	public $name = 'export';
	public $domain = 'table';
	public $needACL = true;
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function performAction($table, $filter, $post_data, &$recursion_history = array()){
		global $config;

		$format = (isset($filter['_format']))?$filter['_format']:'csv';
		unset($filter['_format']);
		$current_user = SuppleApplication::getUserBean();

		// TODO: REEMPLAZAR POR UN INIT, en un objeto exportador
		if ($format == 'php'){
			$content = '<?php '."\n\n";
		} elseif ($format == 'csv') {
			$delimiter = $config->getValue('csv_separator');
			$enclosure = $config->getValue('csv_enclosure');
            if (empty($delimiter)) $delimiter = ';';
			$content = '';
		} elseif ($format == 'json') {
			$all_data = array();
		} elseif ($format == 'xls' || $format == 'xlsx') {
			$all_data = array();
		} else {
			// FORMATO NO RECONOCIDO
		}

		if (empty($table)){
			if ($current_user->isadmin){

				$tables = $this->db->getTables();
				$filename = 'dump';

			} else {
				echo "Invalid user to perform this action.";
				die();
			}
		} else {

			$tables = array(array('name' => $table));
			$filename = $table;

		}

		$sar = new SuppleActionRead();

		foreach ($tables as $tabledef) {
			$table_name = $tabledef['name'];

			$fields = SuppleField::getFields($table_name);

			$filter['count'] = 1000000; // MAX EXPORT FILE SIZE
			$s_data = $sar->performAction($table_name, $filter, $post_data);
			$data = $s_data['data'];
			foreach ($data as $i => $d){
				unset($d['_nextid']);
				unset($d['_previousid']);
				foreach ($d as $k => $v){
					// TODO: ONLY FIELDS WIDTH view8 == 1 (if any, else all)
					if (isset($fields[$k])){
						$f = $fields[$k];
					} else if (isset($fields[substr($k, 0, -6)]) && substr($k, -6) == '_value'){
						$f = $fields[substr($k, 0, -6)];
					}
					if ($f->view8 == 1){
						// IMPLODE ARRAY VALUES
						if (is_array($v)){
							$v = implode(',', $v);
						} 
						// REMOVE newlines FROM VALUE
						$d[$k] = str_replace(array("\n", "\r"), '', $v);
					} else {
						// REMOVE VALUE FROM RESULT
						unset($d[$k]);
						unset($data[$i][$k]);
					}					
				}				
				$data[$i] = $d;
			}

			// TODO: REEMPLAZAR POR UN ADD DATA ($table, $data)
			if ($format == 'php'){
				$content .= "\$$table_name = ".var_export($data, true)."; \n\n";
			} elseif ($format == 'csv') {
				if (empty($table)) $content .= "// $table_name \n";
				// HEADER
				$head = array();
				$labels = array();
				$c = 100000;
                foreach ($data as $i => $row){	
                    foreach ($row as $field => $value){
                        if (substr($field, 0, 1) != '_' && empty($head[$field]) ){
							// $f = SuppleField::getField($field, $table_name);
							if (isset($fields[$field])){
								$f = $fields[$field];
								if ($f->view8 == 1){
									$field_order = (empty($f->order))?$c:$f->order;
									// $head[$field_order * 3] = $field;
									$head[$field] = $field_order * 3;
									if (!empty($f->save_related_name)){
										// $head[($field_order * 3) + 1] = $f->save_related_name;
										$head[$f->save_related_name] = ($field_order * 3) + 1;
										$labels[$field] = $f->getLabel() . " (name)";
										$labels[$f->save_related_name] = $f->getLabel();
									} else {
										$labels[$field] = $f->getLabel();
									}
									$c++;
								} else {
									unset($data[$i][$field]);
								}
							} else {
								unset($data[$i][$field]);
							}						
						} 
						$field_value = $field.'_value';
						if (substr($field, 0, 1) != '_' && empty($head[$field_value]) && isset($row[$field_value])){
							// $f = SuppleField::getField($field, $table_name);
							if (isset($fields[$field])){
								$f = $fields[$field];
								if ($f->view8 == 1){
									$field_order = (empty($f->order))?$c:$f->order;
									// $head[($field_order * 3) + 2] = $field_value;
									$head[$field_value] = ($field_order * 3) + 2;
									$labels[$field] = $f->getLabel()." (value)";
									$labels[$field_value] = $f->getLabel();
									$c++;
								} else {
									unset($data[$i][$field]);
								}
							} else {
								unset($data[$i][$field]);
							}
						}
                    }
				}
				//ksort($head);
				asort($head);
				//$content .= '"'.implode('"'.$delimiter.'"', array_keys($head)).'"'."\n";
				
				$ls = array();
				foreach ($head as $key => $o){
					if (isset($labels[$key])) {
						$ls[] = $labels[$key];
					} else {
						$ls[] = '';
					}
				}
				$content .= implode($delimiter, $ls)."\n";
                // DATA
				foreach ($data as $row){

					$values = array();
					foreach ($head as $key => $o){
						if (isset($row[$key])) {
							$value = str_replace('"', '""', $row[$key]);
							if ($value != '') $value = '"'.$value.'"';
							$values[] = $value;	
							unset($row[$key]);
						} else {
							$values[] = '';
						}
					}
                    // Nothing should remain...???
					foreach ($row as $field => $value){
						if (substr($field, 0, 1) != '_') {
							$value = str_replace('"', '""', $value);
							if ($value != '') $value = '"'.$value.'"';
							$values[] = $value;
						}
					}
					$content .= implode($delimiter, $values)."\n";
				}
			} elseif ($format == 'json') {
				/*foreach ($data as $i => $row){
					foreach ($row as $field => $value) {
						$data[$i][$field] = utf8_decode($value);
					}
				} */
				$all_data[$table_name] = $data;
			} elseif ($format == 'xls' || $format == 'xlsx') {
				$all_data[$table_name] = $data;
			} else {
				// FORMATO NO RECONOCIDO
			}
		}

		if ($format == 'json') {
			$content = json_encode($all_data);
		}

		//print_r($content); die();

		// TODO: REEMPLAZAR POR UN OUTPUT
		/*
		// DEBUG:
		echo "<pre>";
		echo htmlentities($content);
		echo "</pre>";
		die();
		*/
		if ($format == 'xls' || $format == 'xlsx') {
			$this->xlsArray($all_data, "$filename.$format", $format);
		} else {
			ob_clean();
			header("Content-type: application/force-download");
			header("Content-disposition: attachment; filename=\"$filename.$format\";");
			echo utf8_decode($content);
		}
		die();
		
	}

	function xlsArray($array, $filename, $format){
		require_once('include/PHPExcel/PHPExcel.php');
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("Supple")
							 ->setLastModifiedBy("Supple")
							 ->setTitle("XLSExport")
							 ->setSubject("XLSExport")
							 ->setDescription("")
							 ->setKeywords("")
							 ->setCategory("");
		
		// HEADER
		$header = array();
		foreach ($array as $table => $data){
			$header[$table] = array();
			foreach ($data as $i => $row){
				foreach ($row as $field => $value){
					$header[$table][$field] = $field;
				}
			}
		}
		// DATA
		$s = 0;
		foreach ($array as $table => $data){
			// DRAW HEADER:
			$j = 0;
			foreach ($header[$table] as $f){
				$celda = $this->xlsLetra($j).'1';
				$objPHPExcel->setActiveSheetIndex($s)->setCellValue($celda, $f);
				$j++;
			}
			// DRAW DATA:
			foreach ($data as $i => $row){
				$j = 0;
				foreach ($header[$table] as $f){
				//foreach ($row as $field => $value){
					$celda = $this->xlsLetra($j).($i + 2);
					$value = (isset($row[$f]))?$row[$f]:'';
					$objPHPExcel->setActiveSheetIndex($s)->setCellValue($celda, $value);
					$j++;
				}
			}
			$objPHPExcel->getActiveSheet()->setTitle($table);
			$objPHPExcel->setActiveSheetIndex($s);
			$s++;
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'"');
		header('Cache-Control: max-age=0');
		//header('Cache-Control: max-age=1');
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, ($format=='xls')?'Excel5':'Excel2007');
		$objWriter->save('php://output');
		
		
	}

	function xlsLetra($j){
		$k = $j % 26;
		$l = (int) ($j / 26);
		$s = ($l==0)?'':$this->xlsLetra($l-1);
		return  $s.chr($k+65);
	}

	
}
