<?php

require_once('include/SuppleAction.php');
require_once('include/SuppleApplication.php');
require_once('include/SuppleFile.php');

class SuppleActionMassive extends SuppleAction {

	public $name = 'massive';
	public $domain = 'table';
	
	function __construct(){
	
		parent::__construct();
	
	}
	
	public function performAction($table, $filter, $data, &$recursion_history = array()){

		$r = array('error' => '');
	
		$gettable = $table; // (o sea, la tabla que me pasan por get)

		$rdata = array();

		// Primero armo un registro por cada valor. Espero arreglos siempre
		foreach ($data as $nombre => $array){
			if (is_array($array)){
				foreach ($array as $index => $dato){
					$rdata[$index][$nombre] = $dato;
				}
			}
		}
		
		$ids = array();

		foreach ($rdata as $index => $rd){

			// $table tranquilamente podría ser un array pasado en el post...
			if (isset($rd['_table'])){
				$table = $rd['_table'];
				unset($rd['_table']);
			} else {
				$table = $gettable;
			}


/*
			// ¿Hay registros con este id?
			if (isset($rd['id']) && $rd['id'] != ''){

				// La condición es el id nada más.
				$filtro = array('id' => $rd['id']);
				$exists = $this->db->from($table)->where($filtro)->getCount();

			} else {

				$exists = 0;

			}

			// Me fijo si inserto o updateo.
			if ($exists){

				// UPDATE!!!
				$ids = $this->db->update($table, $rd, $filtro);

			} else {

				// INSERT!!!
				$id = $this->db->insert($table, $rd);

			}
*/
			$id = (isset($rd['id']))?$rd['id']:'';

			$bean = SuppleBean::getBean($table, $id, true); // light

			$bean->populate($rd, false);

			$id = $bean->save();

			$ids[] = $id;

			if ($_FILES){

				$sf = new SuppleFile();

				if ($bean->_isnew){

					// INSERT
					// Upload each file
					foreach ($_FILES as $clave => $file){
						$sf->upload($file['tmp_name'][$index], $file['name'][$index], $id, $table, $clave);
					}

				} else {

					// UPDATE
					if ($id){
						// Upload each file for each affected record
						foreach ($_FILES as $clave => $file){
							//foreach ($ids as $id){
								$sf->update($file['tmp_name'][$index], $file['name'][$index], $id, $table, $clave);
							//}
						}
					}

				}

			} // end if $_FILES

		} // end foreach $rdata

		$r['id'] = $ids;
		return $r;
	
	} // end function
	
} // end class


?>