<?php

if (file_exists('custom/util.php')) require_once('custom/util.php');

/*
 *          BASIC FILESYSTEM FUNCTIONS
 * */

function createFolder($path){
	
	if ($path){
		// create the folder containing the folder
		$i1 = strrpos($path, '/'); // last occurrence
		createFolder(substr($path, 0, $i1));
	
		if (!is_dir($path)){
			//echo $path;
			mkdir($path);
		}
	}
}

// TODO: REMOVE
function uploadFile($source, $target){
	$r = "";
	if (file_exists($source)){
		$filef = $target; 
		if (file_exists($filef)) unlink($filef);
		if (copy($source, $filef))
			$r = $target;
	}
	return $r;
}

function deleteFile($file){
	if ($file)
		if (file_exists($file)) 
			unlink($file);
}

function emptyFolder($path){
	if (file_exists($path)){
		$a = glob($path.'*');
		foreach ($a as $subpath){
			if (is_dir($subpath)){
				emptyFolder($subpath.'/');
				rmdir($subpath);
			} else {
				unlink($subpath);
			}
		}
	}
}

function updateFile($oldFile, $source, $target){
	if (file_exists($source)){
		deleteFile($oldFile);
		$r = uploadFile($source, $target);
	} else {
		$r = $oldFile;
	}
	return $r;
}
// TODO: REWRITE
function folderContent($path = "/", $pattern = '*.*'){

	$previous = getcwd();

	// An array containing the files within a especified folder.
	if ($path[0] == '/') $fullpath = getcwd().$path;
	else $fullpath = getcwd().'/'.$path;

	if (is_dir($fullpath)){
		chdir($fullpath);
		if ($fullpath[strlen($fullpath)-1] != '/') $fullpath .= '/';
		if (is_array($resultado = glob($fullpath.$pattern))){
			foreach ($resultado as $key => $value){
				$resultado[$key] = substr($value, strlen($fullpath));
			}
		} else {
			$resultado = array();
		}
	} else {
		$resultado = array();
	}

	// TODO: Algo como esto, pero que funcione!
	chdir($previous);

	return $resultado;
}

/*
 *          WRITING FUNCTIONS
 * */
 
function writeArray($filename, $array, $variablename = ''){
	$returnvalue = false;

	if ($variablename == ''){
		// get the variable name
		$i1 = strrpos($filename, '/'); // last occurrence
		$i2 = strrpos($filename, '.'); // NOT THE first occurrence!!! THE LAST!!!
		if ($i1 === false) $i1 = -1;
		$variablename = substr($filename, $i1 + 1, $i2 - $i1 - 1);
	}
	$variablename = str_replace('.', '_', $variablename);

	// generate the content
	$content = '<?php $'.$variablename.' = '.writeSubArray($array).'; ?>'; 
  // También sirve var_export: es más legible pero menos compacto.
   /* $content = '<?php $'.$variablename.' = '.var_export($array).'; ?>'; */

	// write the content into the file
	return writeString($filename, $content);

}

function writeSubArray($array){
	$returnvalue = '';

	if (is_string($array)){
		$array = htmlentities($array, ENT_QUOTES); // str_replace("'", "\\'", $array)
		$array = str_replace('&amp;', '&', $array);
		$returnvalue = "'".$array."'";
	} elseif (is_array($array)){

		foreach ($array as $key => $value){
			if ($returnvalue) $returnvalue .= ", ";
			$returnvalue .= "'$key' => ".writeSubArray($value);
		}
		$returnvalue = "array($returnvalue)\n";

	} elseif (is_null($array)){
		$returnvalue = 'NULL';
	} elseif (empty($array)){
		$returnvalue = "''";
	} else {
		$returnvalue = "$array";
	}

	return $returnvalue;
}

function writeString($filename, $string){
	$returnvalue = FALSE;

	// create the folder containing the file
	$i1 = strrpos($filename, '/'); // last occurrence
	createFolder(substr($filename, 0, $i1));

	// write the string into the file
	if ((!file_exists($filename)) || is_writable($filename)) {
		if ($handler = fopen($filename, 'w')) {
			if (fwrite($handler, $string) !== FALSE){
				fclose($handler);
				$returnvalue = TRUE;
			}
		}
	}

	return $returnvalue;
}

function returnJSArray($name, $array){
	$returnvalue = '';
	
	$returnvalue = 'var '.$name." = ".writeJSSubArray($array)."; ";
	
	return $returnvalue;
}

function writeJSSubArray($array){
	$returnvalue = '';

	if (is_string($array)){
		$returnvalue = "'".str_replace("'", "\\'", $array)."'";
	} elseif (is_array($array)){

		foreach ($array as $key => $value){
			if ($returnvalue) $returnvalue .= ", ";
			$returnvalue .= writeJSSubArray($value);
		}
		$returnvalue = "new Array($returnvalue)";

	} elseif (is_null($array)){
		$returnvalue = 'NULL';
	} elseif (empty($array)){
		$returnvalue = "''";
	} else {
		$returnvalue = "$array";
	}

	return $returnvalue;
}


/*
 *            APPEND FUNCTIONS
 *   */


function appendArray($filename, $array, $insert = false){
	$returnvalue = false;

	if (file_exists($filename)){

		// get the variable name
		$i1 = strrpos($filename, '/'); // last occurrence
		$i2 = strpos($filename, '.'); // first occurrence
		$variablename = substr($filename, $i1 + 1, $i2 - $i1 - 1);
		$variablename = str_replace('.', '_', $variablename);

		// generate the content
    if ($insert){
      foreach ($array as $a){
    		$content = '<?php '.appendSubArrayInsert('$'.$variablename, $a).'; ?>';
      }
    } else {
  		$content = '<?php '.appendSubArray('$'.$variablename, $array).'; ?>';
    }

		// write the content into the file
		return appendString($filename, $content);
	} else {
		return writeArray($filename, $array);
	}
}

function appendSubArray($sub, $array){
	$returnvalue = '';

	if (is_string($array)){
		$array = htmlentities($array, ENT_QUOTES); // str_replace("\'", "'", $array);
		$returnvalue .= $sub." = '".$array."';\n";
	} elseif (is_array($array)){

		foreach ($array as $key => $value){
			if (is_string($key)) 
				$returnvalue .= appendSubArray($sub."['$key']", $value);
			else
				$returnvalue .= appendSubArray($sub."[$key]", $value); 
		}

	} elseif (is_null($array)){
		$returnvalue = $sub.' = NULL;';
	} elseif (empty($array)) {
		$returnvalue = $sub." = '';";
	} else {
		$returnvalue = $sub." = $array;";
	}

	return $returnvalue;
}

function appendSubArrayInsert($sub, $array, $first = true){
	$returnvalue = '';

	if (is_string($array)){
		$array = htmlentities($array, ENT_QUOTES); // str_replace("'", "\\'", $array)
		$returnvalue = "'".$array."'";
	} elseif (is_array($array)){

		foreach ($array as $key => $value){
			if ($returnvalue) $returnvalue .= ", ";
			$returnvalue .= "'$key' => ".appendSubArrayInsert($sub, $value, false);
		}
		$returnvalue = "array($returnvalue)";

	} elseif (is_null($array)){
		$returnvalue = 'NULL';
	} else {
		$returnvalue = "$array";
	}

  if ($first){
  	return $sub."[] = ".$returnvalue;
  } else {
  	return $returnvalue;
  }
}

function appendString($filename, $string){
	$returnvalue = FALSE;

	// create the folder containing the file
	$i1 = strrpos($filename, '/'); // last occurrence
	createFolder(substr($filename, 0, $i1));

	// write the string into the file
	if ((!file_exists($filename)) || is_writable($filename)){
		if (file_exists($filename)) $atributo = 'a';
		else $atributo = 'w';
		if ($handler = fopen($filename, $atributo)) {
			if (fwrite($handler, $string) !== FALSE){
				fclose($handler);
				$returnvalue = TRUE;
			}
		}
	}

	return $returnvalue;
}

/*
 *        READING FUNCTIONS ? 
 *
*/

function readArray($filename, $variablename = ''){

	if ($variablename == ''){
		// get the variable name
		$i1 = strrpos($filename, '/'); // last occurrence
		$i2 = strrpos($filename, '.'); // NOT THE first occurrence!!! THE LAST!!!
		$variablename = substr($filename, $i1 + 1, $i2 - $i1 - 1);
	}
	$variablename = str_replace('.', '_', $variablename);
	
	if (file_exists($filename)){
		require($filename);
	} else {
		$$variablename = array();
	}
	
	return readSubArray($$variablename);
	//return $$variablename;
	
}

function readSubArray($array){
	if (is_string($array)){
		
		$array = html_entity_decode($array, ENT_QUOTES);
		
	} elseif (is_array($array)){
		foreach ($array as $key => $value){
			$array[$key] = readSubArray($value);
		}
	} 
	return $array;
}


/*
 *              STRING FUNCTIONS
 *     */

function getFileName($filename){
	$i1 = strrpos($filename, '/'); // last occurrence
	if ($i1 == "") $i1 = -1;
	$i2 = strrpos($filename, '.'); // NOT THE first occurrence!!! THE LAST!!!
	if ($i2 == "") $i2 = strlen($filename); 

	return substr($filename, $i1 + 1, $i2 - $i1 - 1);
}

function getFilePath($filename){
	$i1 = strrpos($filename, '/'); // last occurrence
	if ($i1 == "") $i1 = strlen($filename); 
	return substr($filename, 0, $i1);
}

function getFileExt($filename){
	$i2 = strrpos($filename, '.'); // NOT THE first occurrence!!! THE LAST!!!
	if ($i2 == "") return "";
	else return substr($filename, $i2 + 1);
}

// FUNCIONA CORRECTAMENTE!!!
function getOpenClose(&$string, $init, $open_exp, $close_exp){

	$openPos = 0;
	$closePos = 0;

	$openPos = getPos($string, $open_exp, $init);
	if ($openPos !== false){
		$nextPos = $openPos + 1;

		while ((getPos($string, $open_exp, $nextPos) < getPos($string, $close_exp, $nextPos)) && (getPos($string, $open_exp, $nextPos) !== false)){
			list($innerOpenPos, $innerClosePos) = getOpenClose($string, $nextPos, $open_exp, $close_exp);
			$nextPos = $innerClosePos + 1;
		}
		$closePos = getPos($string, $close_exp, $nextPos);
	}

	return array($openPos, $closePos);

}

// Omite todo lo que está salvado
function getPos(&$string, $exp, $init){
	$r = stripos($string, $exp, $init);
	while ($r !== false && $r > 0 && $string[$r - 1] == '\\'){
		$init = $r + 1;
		$r = stripos($string, $exp, $init);
	}
	return $r;
}

function substrfromto(&$string, $from, $to = -1){
	if ($to < $from) return ""; // indices cruzados
	if ($to == -1) return substr($string, $from); // Creo que no se usa :-s
	else return substr($string, $from, $to - $from + 1);
}



// str_replace($cadena_buscada, $cadena_sustituta, $cadena_original);


/*
 *              ARRAY FUNCTIONS
 *     */

function sortArrayByValue(&$array){
	// Nota: las claves se pierden
	sort($array);
}

function sortArrayByKey(&$array){
	ksort($array);
}

// TODO: Debug!!!

function sortArrayByField(&$array, $key){
	$allnumeric = true;

	// Implementación nueva: permite anidar ordenaciones
	$cent = true;
	while($cent){
		
		$cent = false;
		
		for($i=0; $i < (count($array) - 1); $i++){
			if (isset($array[$i][$key]) && isset($array[$i + 1][$key])){
				if ($array[$i][$key] > $array[$i + 1][$key]){
					
					$cent = true;
					$aux = $array[$i];
					$array[$i] = $array[$i+1];
					$array[$i+1] = $aux;
					
				}
			}
		}
		
	}

	// Implementación vieja
	/*	
	// Para reutilizar se hace así:
	// Recolectos los valores de esa clave y los guardo junto con los índices
	// después mando a ordenarlo, y después con eso creo un nuevo arreglo ordenado

	$clavesOrdenadas = array();
	foreach ($array as $indice => $fila){
		// Si no es un array, no tiene sentido.
		if (is_array($fila)){
			// ¿Las claves deberían ir trimeadas?
			$clavesOrdenadas[$indice] = $fila[$key];
			$allnumeric = $allnumeric && is_numeric(trim($fila[$key]));
			// if (!is_numeric(trim($fila[$key]))) echo $fila[$key]." NO ES";
		}
	}
	
	if ($allnumeric){
		foreach($clavesOrdenadas as $indice => $clave){
			$clavesOrdenadas[$indice] = (int) trim($clave);
		}
	}

	asort($clavesOrdenadas);
	
	// echo "<pre>"; print_r($clavesOrdenadas); echo "</pre>";

	reset($clavesOrdenadas);
	$newArray = array();
	while (list($k, $v) = each($clavesOrdenadas)) {
		$newArray[] = $array[$k];
	}
	
	
	$array = $newArray;
	*/

}

function mixArrays($array1, $array2){
	return array_merge($array1, $array2);
	/*
	$startTime = SuppleApplication::getlog()->getStartTime();
	if (is_array($array1) && is_array($array2)){
		$r = array_merge($array1, $array2);
	} elseif(is_array($array1)) {
		echo "ARRAY 2 IS NOT ARRAY!!!<br>";
		$r = $array1;
	} elseif(is_array($array2)) {
		echo "ARRAY 1 IS NOT ARRAY!!!<br>";
		$r = $array2;
	} else {
		echo "ARRAY 1 AND 2 ARE NOT ARRAY!!!<br>";
		$r = array();
	}
	SuppleApplication::getlog()->logEndTime('mixArrays', $startTime);
	return $r;
	*/
}

/*
 *              HTML CREATION
 *     */

function createTable($array, $fields){
	
	$html = '<TABLE border=0 cellpadding=0 cellspacing=0>';

	$html .= '<TR>';
	foreach ($fields as $name => $label){
		$html .= '<TH>'.$label.'</TH>';
	}
	$html .= '</TR>';

	foreach ($array as $row){
		$html .= '<TR>';
		foreach ($fields as $name => $label){
			if ($row[$name]) $html .= '<TD>'.$row[$name].'</TD>';
			else $html .= '<TD> &nbsp; </TD>';
		}
		$html .= '</TR>';
	}

	$html .= '</TABLE>';

	return $html;
}




/*
 *              OTHERS
 *     */

function redirect($url){
	header("Location: $url");
}

// TODO: Deprecar esta función
// Importante saber que el pasaje por valor funciona con strings perfectamente.
function simpleTemplate($templateString, $arrayValues, $quoted = false){

	// Sólo esto es necesario para guardar el símbolo pesos.
	$arrayValues[''] = '$';
	
	$variables = explode('$', $templateString);
	$resultado = '';
	$i = false;
	$avoid_next = false;

	foreach ($variables as $variable){
		if ($i){
			if ($avoid_next){
				$nombre = '';
				$resultado = substr($resultado, 0, strlen($resultado) - 1) . "$";
			} else {
				$partes = preg_split('/[^A-Za-z0-9_.]/', $variable);
				$nombre = $partes[0];
				if (isset($arrayValues[$nombre])){
					if ($quoted){
						$resultado .= "'".str_replace("'", "\\'", $arrayValues[$nombre])."'";
						// echo "$nombre = '".str_replace("'", "\\'", $arrayValues[$nombre])."' <br>";
						// SuppleApplication::ob_flush();
					} else {
						$resultado .= $arrayValues[$nombre];
					}
				} else {
					if ($quoted){
						$resultado .= "''";
					}
				}
			}
			
			
		} else {
			$nombre = "";
			$i = true;
		}
		$avoid_next = (substr($variable, -1) == '\\');

		$resultado .= substr($variable, strlen($nombre));

	}
	
	$resultado = str_replace('\.', '.', $resultado);

	return $resultado;

}

function simpleTemplateFile($filename, $data, $quoted = false){

	$content = file_get_contents($filename);

	return simpleTemplate($content, $data, $quoted);
}



// Should not receive an users array. Must return a SuppleBean.
// Implemented in SuppleApplication
function authenticate($users, $redirect, $user, $pass){

	$cookie_time = 6 * 60 * 60; // 6 hours

	$authorised = false;

	if (isset($user) && isset($pass) && $user != '') {

		$pass = md5($pass);
		// $user = $user;

		foreach ($users as $index => $each_user){
			
			if ($each_user['user']) $authorised = $user == $each_user['user'];
			else $authorised = true;

			// Is it a valid password?
			$authorised = $authorised && ($pass == $each_user['pass']);

			if ($authorised){

				setcookie("user", $user);
				setcookie("pass", $pass);
				return $index;
				
			}

		}
		
	} elseif (isset($_COOKIE['user'])) {

		$pass_c = $_COOKIE['pass']; // md5 already applied
		$user = $_COOKIE['user'];

		foreach ($users as $index => $each_user){
			
			if ($each_user['user']) $authorised = $user == $each_user['user'];
			else $authorised = true;

			// verify
			$authorised = $authorised && ($pass_c == $each_user['pass']);

			if ($authorised) return $index;

		}

	} 

	if (!$authorised){
		// header('WWW-Authenticate: Basic realm="'.$realm.'"');
		// header('HTTP/1.0 401 Unauthorized');

		// Login screen
		if ($redirect){
			$r = $redirect;
			if (strpos(strtolower($r), ".php")){
				if ($user){
					$m = 'Nombre de usuario o contraseña incorrectos';
				} else {
					$m = '';
				}
				if (strpos($r, '?')){
					$r .= '&mensaje='.$m;
				} else {
					$r .= '?mensaje='.$m;
				}
			}
		  redirect($r);
		} else {
			return false;
		}

	}

	if (!$authorised) exit;

}

// TODO: Should not receive an users array. Must return a SuppleBean.
function getUserId($users){

	if (isset($_COOKIE['user'])) {

		$p = $_COOKIE['pass']; // ya tiene aplicado el md5
		$u = $_COOKIE['user'];

		foreach ($users as $index => $user){
			
			if ($user['user'] && $u == $user['user'] && $p == $user['pass']) return $user['id'];

		}
		
	}

	return "";

}

function getSessionId($connection){
	
	$session_id = false;
	$session_duration = 21600; // 6 hours TODO: Get this value from config
	$time = time(); // Now!
	
	// Delete old sessions:
	$connection->delete('_sessions', array("(time + $session_duration + 10) < $time" => '1'));
	
	
	if (isset($_COOKIE['session_id'])){
		
		// get cookie time from db...
		$row = $connection->from('_sessions')->where(array('id' => $_COOKIE['session_id']))->getRow();
		
		if ($row){
			
			// get cookie time, and compare
			if ($row['time'] == $_COOKIE['time']){
				
				$session_id = $_COOKIE['session_id'];
				
				// renew
				// update...
				// $_COOKIE['time'] = $newtime
				// setcookie(time, $newtime, $newtime + $session_duration)
				
			}
			
		}

	}
	
	if (!$session_id){
				
		// New session!
		$id = $connection->insert('_sessions', array('time' => $time, 'ip' => $_SERVER['REMOTE_ADDR']));
		$session_id = $id;
		
		// Set the cookie
		setcookie("session_id", $id, $time + $session_duration); // 6 hours
		setcookie("time", $time, $time + $session_duration); // 6 hours
		
		// Set these values too
		$_COOKIE['session_id'] = $id;
		$_COOKIE['time'] = $time;
		
		// And update the counter
		//incrementCounter($connection);
		
	}
	
	// And return
	return $session_id;
	
}
/*
function incrementCounter($connection){
	
	$row = $connection->from('_config')->where(array('key' => 'counter'))->getRow(); // get
	$counter = (int)$row['value'] + 1; // increment
	if ($row){
		$connection->delete('_config', array('key' => 'counter')); // Delete the previous value
	} 
	$connection->insert('_config', array('value' => $counter, 'key' => 'counter')); // set
	
	// Una nueva visita!
	// $connection->insert('visitas', array('ip' => $_SERVER['REMOTE_ADDR'], 'fecha' => date("d/m/Y H:i"))); 
	
}
*/
/*
function getCounter($connection){
	
	getSessionId($connection);
	
	$row = $connection->from('_config')->where(array('key' => 'counter'))->getRow();
	if ($row){
		return (int)$row['value'];
	} else {
		return 0;
	}
}
*/
function addDateTimeValues(&$data, $gmt = 3){

	$time = time() - (60 * 60 * $gmt); // GMT - 3
	$data['today'] = gmdate('d/m/Y', $time);
	$data['day'] = gmdate('d', $time);
	$data['month'] = gmdate('m', $time);
	$data['year'] = gmdate('Y', $time);
	$data['hours'] = gmdate('H', $time);
	$data['minutes'] = gmdate('i', $time);

}

function desCuotar($array){
	
	if (is_string($array)){
		// descuoteo
		$array = str_replace(array('\\\'', '\"'), array("'", '"'), $array);
	} elseif(is_array($array)) {
		// subinvoco
		foreach ($array as $key => $value){
			$array[$key] = desCuotar($value);
		}
		
	}
	
	return $array;
	
}

function cutes($string){

	// esto es una bestialidad... pero los cutes ya me tienen podrido.

	$cutes = array('&iexcl;', '&cent;', '&pound;', '&curren;', '&yen;', '&brvbar;', '&sect;', '&uml;', '&copy;', '&ordf;', '&laquo;', '&not;', '&reg;', '&macr;', '&deg;', '&plusmn;', '&sup2;', '&sup3;', '&acute;', '&micro;', '&para;', '&middot;', '&cedil;', '&sup1;', '&ordm;', '&raquo;', '&frac14;', '&frac12;', '&frac34;', '&iquest;', '&times;', '&divide;', '&Agrave;', '&Aacute;', '&Acirc;', '&Atilde;', '&Auml;', '&Aring;', '&AElig;', '&Ccedil;', '&Egrave;', '&Eacute;', '&Ecirc;', '&Euml;', '&Igrave;', '&Iacute;', '&Icirc;', '&Iuml;', '&ETH;', '&Ntilde;', '&Ograve;', '&Oacute;', '&Ocirc;', '&Otilde;', '&Ouml;', '&Oslash;', '&Ugrave;', '&Uacute;', '&Ucirc;', '&Uuml;', '&Yacute;', '&THORN;', '&szlig;', '&agrave;', '&aacute;', '&acirc;', '&atilde;', '&auml;', '&aring;', '&aelig;', '&ccedil;', '&egrave;', '&eacute;', '&ecirc;', '&euml;', '&igrave;', '&iacute;', '&icirc;', '&iuml;', '&eth;', '&ntilde;', '&ograve;', '&oacute;', '&ocirc;', '&otilde;', '&ouml;', '&oslash;', '&ugrave;', '&uacute;', '&ucirc;', '&uuml;', '&yacute;', '&thorn;', '&yuml;', );

	$chars = array(chr('161'), chr('162'), chr('163'), chr('164'), chr('165'), chr('166'), chr('167'), chr('168'), chr('169'), chr('170'), chr('171'), chr('172'), chr('174'), chr('175'), chr('176'), chr('177'), chr('178'), chr('179'), chr('180'), chr('181'), chr('182'), chr('183'), chr('184'), chr('185'), chr('186'), chr('187'), chr('188'), chr('189'), chr('190'), chr('191'), chr('215'), chr('247'), chr('192'), chr('193'), chr('194'), chr('195'), chr('196'), chr('197'), chr('198'), chr('199'), chr('200'), chr('201'), chr('202'), chr('203'), chr('204'), chr('205'), chr('206'), chr('207'), chr('208'), chr('209'), chr('210'), chr('211'), chr('212'), chr('213'), chr('214'), chr('216'), chr('217'), chr('218'), chr('219'), chr('220'), chr('221'), chr('222'), chr('223'), chr('224'), chr('225'), chr('226'), chr('227'), chr('228'), chr('229'), chr('230'), chr('231'), chr('232'), chr('233'), chr('234'), chr('235'), chr('236'), chr('237'), chr('238'), chr('239'), chr('240'), chr('241'), chr('242'), chr('243'), chr('244'), chr('245'), chr('246'), chr('248'), chr('249'), chr('250'), chr('251'), chr('252'), chr('253'), chr('254'), chr('255'), );

	$cadena_convertida = str_replace($chars, $cutes, $string);

	// Luego quiero conservar sólo los caracteres que son menores a 127
	for ($i = 0; $i < strlen($cadena_convertida); $i++) {
		$ascii_num = ord(substr($cadena_convertida, $i, 1));
		if ($ascii_num > 126 /*|| $ascii_num == 63 || $ascii_num < 32*/){
			$cadena_convertida = substr($cadena_convertida, 0, $i)." ".substr($cadena_convertida, $i + 1); // echo "UNA";// 

		}
	}

	return $cadena_convertida;

}

function prepararValores(&$table, &$post, &$get){

	header("Cache-Control: no-cache, must-revalidate");

	if (isset($_GET['table'])) $table = $_GET['table'];
	if (isset($_GET['redirect'])) $redirect = $_GET['redirect'];

	unset($_GET['table']);
	unset($_GET['redirect']);

	// Lo deprecamos
	// if (isset($_GET['error'])) $error = $_GET['error'];
	// unset($_GET['error']);

	

	$post = desCuotar($_POST);
	$get = $_GET;

	//	if ($table){
	if (isset($_GET['mode']) && $_GET['mode'] == 'debug'){
		
	} else {
		if ($redirect) redirect($redirect);
		return true;
	}

	//	}
	
	// También deprecado
	/* else {

		if ($error) redirect($error);
		else echo "Error: No se especificó un nombre de Tabla.";
		return false;

	}*/

}

// FIN TODO

function is_variable($var)
{
    // return(ereg("^ *([A-Za-z_]+[A-Za-z0-9_]*)+ *$",$var)); 
	return(preg_match('/^[A-Za-z_]+[A-Za-z0-9_]*$/',$var)); 
}

function cacheTemplate($filename, $template, $get = array(), $table = ''){
	// parsea el template llamado $template, y lo outputea en $filename.
	// ver template.php
}

// MUY UTIL: IMAGENES:

/* resizeToFile resizes a picture and writes it to the harddisk
*  
* $sourcefile = the filename of the picture that is going to be resized
* $dest_x  = X-Size of the target picture in pixels
* $dest_y  = Y-Size of the target picture in pixels
* $targetfile = The name under which the resized picture will be stored
* $jpegqual   = The Compression-Rate that is to be used
*/
function resizeToFile($sourcefile, $max_x, $max_y, $targetfile, $jpegqual)
{

	if (function_exists('getimagesize') && 
	    function_exists('imageCreateFromJPEG') && 
		function_exists('imagecreatetruecolor') && 
		function_exists('imagecopyresampled') && 
		function_exists('imagejpeg')){

		/* Get the dimensions of the source picture */
		$picsize = getimagesize("$sourcefile");
		$source_x = $picsize[0];
		$source_y  = $picsize[1];
		$source_id = imageCreateFromJPEG("$sourcefile");
		/* Create a new image object (not neccessarily true colour) */
		
		/* Calculate the destination size */
		$dest_x = $source_x;
		$dest_y = $source_y;
		
		if ($dest_x > $max_x) {
			$dest_y = $max_x / $dest_x * $dest_y;
			$dest_x  = $max_x;
		}

		if ($dest_y > $max_y) {
			$dest_x  = $max_y / $dest_y * $dest_x;
			$dest_y = $max_y;
		}


		$target_id=imagecreatetruecolor($dest_x, $dest_y);
		/* resize the original picture and copy it into the just created image
		  object. Because of the lack of space I had to wrap the parameters to
		several lines. I recommend putting them in one line in order keep your
		  code clean and readable */

		$target_pic=imagecopyresampled($target_id,$source_id,
								 0,0,0,0,
								  $dest_x,$dest_y,
								  $source_x,$source_y);
		/* Create a jpeg with the quality of "$jpegqual" out of the
		  image object "$target_pic".
		  This will be saved as $targetfile */

		imagejpeg ($target_id,"$targetfile",$jpegqual);

		return true;
		
	} else {
	
		return false;
		
	}
} 

function is_jpeg($file){
	$formats = array('jpg', 'jpeg');
	$is = false;

	if (is_string($file) && (strlen($file) > 3)){
		foreach ($formats as $format){
			if ( substr(strtoupper($file), strlen($file) - strlen($format)) == strtoupper($format) ){
				$is = true;
			}
		}
	} 

	return $is;
}

function separate_quoted($string){
	
	$pos = 0;
	$strings = array();
	
	list($openQ, $closeQ) = getOpenClose($string, $pos, "'", "'");
	list($openDQ, $closeDQ) = getOpenClose($string, $pos, '"', '"');
	
	while ($openQ !== false || $openDQ !== false){
		
		if ($openDQ === false || ($openQ !== false && $openQ < $openDQ)){

			if ($closeQ === false) $closeQ = strlen($string) - 1;
			$strings[] = substr($string, $pos, $openQ - $pos);
			$strings[] = substr($string, $openQ, $closeQ - $openQ + 1);
			$pos = $closeQ + 1;
			 
		} else {

			

			if ($closeDQ === false) $closeDQ = strlen($string) - 1;
			$strings[] = substr($string, $pos, $openDQ - $pos);
			$strings[] = substr($string, $openDQ, $closeDQ - $openDQ + 1);
			$pos = $closeDQ + 1;
			
			
			
		}

		list($openQ, $closeQ) = getOpenClose($string, $pos, "'", "'");
		list($openDQ, $closeDQ) = getOpenClose($string, $pos, '"', '"');
		
	}
	
	$strings[] = substr($string, $pos);
	
	return $strings;
	
}

function is_mobile_browser(){

  $useragent=$_SERVER['HTTP_USER_AGENT'];

  return (preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));

}

function protectEmail($email, $text = ''){
	
	if (empty($_GET['_isajax'])){

		$i = strpos($email, '@');
		$nombre = substr($email, 0, $i);
		$dominio = substr($email, $i + 1);
		if (!empty($text)) $text = str_replace("'", "\\"."'", $text);

		$r = "<SCRIPT TYPE=\"text/javascript\">\n
		var email =('$nombre' + '@' + '$dominio');\n";
	
		if (empty($text)){
			$r .= "var text = email;\n";
		} else {
			$r .= "var text = '$text';\n";
		}
		$r .= "document.write('<A href=\"mailto:' + email + '\">' + text + '</a>');\n
		</SCRIPT>";
	} else {
		// Por ajax no me ejecuta el javascript:
		if (empty($text)) $text = $email;
		$r = "<a href=\"mailto:$email\">$text</a>";
	}
	
	return $r;

}

function esEmail($email){

	return preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $email);

}

function createOptions($char1, $char2, $options, $selected){

	$r = "";
	if ($_GET['view'] == 2)
		$options = ''.$char1.''.$char2.$options;
	
	$opciones = explode($char2, $options);
	foreach ($opciones as $opcion){
		$claveValor = explode($char1, $opcion);
		if (isset($claveValor[1])){
			$clave = $claveValor[0];
			$valor = $claveValor[1];
		} else {
			$clave = $claveValor[0];
			$valor = $claveValor[0];
		}
		
		$clave = trim($clave);
		
		if ($clave == $selected){
			$s = " SELECTED";
		} else {
			$s = "";
		}
		
		$r .= "<option value=\"$clave\"$s>$valor</option> ";
		
	}

	return $r;
	
}

function translateOptions($char1, $char2, $options, $value){

	$r = "";
	$opciones = explode($char2, $options);
	foreach ($opciones as $opcion){
		$claveValor = explode($char1, $opcion);
		if (isset($claveValor[1])){
			$clave = $claveValor[0];
			$valor = $claveValor[1];
		} else {
			$clave = $claveValor[0];
			$valor = $claveValor[0];
		}
		
		if ($clave == $value){
			$r = $valor;
		}		
	}

	return $r;
}

function smileysReplace($string){
	
	$reps = array(
		':)' => '01',
		':-)' => '01',
		
		':D' => '03',
		':-D' => '03',
		':d' => '03',
		':-d' => '03',
		
		';)' => '22',
		';-)' => '22',
		
		'XD' => '23',
		'xd' => '23',
		'Xd' => '23',
		'xD' => '23',
		
		'(k)' => '32',
		'(K)' => '32',
		
		'BD' => '33',
		'8D' => '33',
		'B)' => '33',
		'8)' => '33',
		'B-)' => '33',
		'8-)' => '33',
		
		':o' => '38',
		':O' => '38',
		':0' => '38',
		
		':\'(' => '39', // no va a llegar nunca :'(
		':"(' => '39',
		':,(' => '39',
		
		':s' => '49',
		':S' => '49',
		':-s' => '49',
		':-S' => '49',
		
		'(h)' => '58',
		'(H)' => '58',
		
		':p' => '75',
		':P' => '75',
		':-p' => '75',
		':-P' => '75',
		
		':(' => '124',
		':-(' => '124',
		
		':/' => '126',
		':\\' => '126',
		':|' => '126',
		
	);
	
	$r = $string;
	
	foreach ($reps as $chars => $num){
		$r = str_replace($chars, "<img src=smileys/$num.png title=$chars>", $r);
	}
	
	return $r;
	
	
}

function fix_round($number, $d){

	$n = round($number, $d);
	
	if (strlen($n) == 1){
		$n .= ".";
	}
	
	while (strlen($n) < $d + 2){
		$n .= "0";
	}
	
	return $n;

}

function lenSort($rec){

		// ALT1: Working, but too slow.
		/*
		$countRec = count($rec);
		for ($i = 0; $i < $countRec; $i++){
			$max = '';
			foreach ($rec as $fn => $fv){
				if (strlen($fn) > strlen($max)) $max = $fn;
			}
			$record[$max] = $rec[$max];
			unset($rec[$max]);
		}*/

		// ALT2: WRONG!
		// ksort($rec);
		// $record = array_reverse($rec, true);
		$rec_len = array();
		// ALT3: Using php functions
		foreach ($rec as $k => $v){
		  $rec_len[$k] = strlen($k);
		}
		arsort($rec_len);
		$record = array();
		foreach ($rec_len as $k => $v){
		  $record[$k] = $rec[$k];
		}

		return $record;
		
}

function safetextarea($string){
	$r = '';
	if (is_array($string)){
		foreach ($string as $k => $s){
			$r[$k] = safetextarea($s);
		}
	} else {
		$r = str_ireplace('/textarea', '/safetextarea', $string);
	}
	return $r;
}

function unsafetextarea($string){
	$r = '';
	if (is_array($string)){
		foreach ($string as $k => $s){
			$r[$k] = unsafetextarea($s);
		}
	} else {
		$r = str_ireplace('/safetextarea', '/textarea', $string);
	}
	return $r;
}

function safequotes($string){
	$r = '';
	if (is_array($string)){
		foreach ($string as $k => $s){
			$r[$k] = safequotes($s);
		}
	} else {
		$r = str_ireplace("'", "|||", $string);
	}
	return $r;
}

function unsafequotes($string){
	$r = '';
	if (is_array($string)){
		foreach ($string as $k => $s){
			$r[$k] = unsafequotes($s);
		}
	} else {
		$r = str_ireplace("|||", "'", $string);
	}
	return $r;
}

function php_like_cmp($a, $b){

	$pattern = '/^'.str_replace('%', '.*', $b).'$/i';
	return preg_match($pattern, $a);
	
}

function es_valor_quotado($value){
	$v = trim($value);
	return (substr($v,0,1) == substr($v,-1) && (substr($v,0,1) == "'" || substr($v,0,1) == '"'));
}

function valor_desquotado($value){
	if (es_valor_quotado($value)){
		return substr(trim($value),1,-1);
	} else {
		return $value;
	}
}



function custom_htmlentities($string, $chars = ''){
	if ($chars == ''){
		return htmlentities($string);
	} else {
		$from = array();
		$to = array();
		// collect chars and their htmlentity
		for ($i = 0; $i < strlen($chars);$i++){
			$char = $chars[$i];
			$htmle = htmlentities($char);
			if ($char == $htmle){
				$htmle = '&#'.ord($char).';';
			}
			if ($char == ' '){
				$htmle = '&nbsp;';
			}
			$from[] = $char;
			$to[] = $htmle;
		}
		// REPLACE!!!
		return str_replace($from, $to, $string);
	}
	
}

function formatDate($date, $format = 'd/m/Y', $adjust_offset = true){
	$cfg = SuppleApplication::getconfig();
	if ($adjust_offset && strlen(trim($date)) > 10){
		$offset = $cfg->getValue('gmt_offset');
	} else {
		$offset = 0;
	}	
	if (empty($date)) return '';
	return date($format, strtotime($date) + ($offset * 60 * 60));
}

function onlyupper($string){
	$r = '';
	for ($i=0;$i<strlen($string);$i++){
	  if ($string[$i] == strtoupper($string[$i])) $r .= $string[$i];
	}
	return $r;	
}

function quoteTrim($string) {
	$t = trim($string);
	if (substr($t, 0, 1) == "'" && substr($t, -1) == "'") {
		$t = substr($t, 1, -1);
	} else if (substr($t, 0, 1) == '"' && substr($t, -1) == '"') {
		$t = substr($t, 1, -1);
	}
	return $t;
}

function isMd5($string){
	return preg_match('/^[a-f0-9]{32}$/', $string);
}

function uuid(){
	return uniqid(); // TODO: Use UUID v5
}

function curlPost($url, $data, $json = true){
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,$url);
	if (!empty($data)){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
					http_build_query($data));
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

	$result = curl_exec ($ch);
	if (curl_errno($ch)) {
		$error_msg = curl_error($ch);
	}
	curl_close ($ch);

	if (isset($error_msg)) {
		// echo $error_msg; die();
	} else {
		// echo $result; die();
	}

	if ($json) {
		return json_decode($result);
	} else {
		return $result;
	}
}

function recaptcha($secret) {
	$r = false;
	if (isset($_POST['g-recaptcha-response'])){
		$verif = array(
			'secret' => $secret,
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $_SERVER['REMOTE_ADDR'],
		);
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$resp = curlPost($url, $verif);
		$r = $resp->success;
		unset($_POST['g-recaptcha-response']);
	}
	return $r;
}

function getBaseUrl() {
	
	$u = $_SERVER['REQUEST_URI'];
	$u = substr($u, 0, strrpos($u, '/')+1);
	$p = ($_SERVER['SERVER_PORT'] == 443)?'https':'http';
	return $p.'://'.$_SERVER['HTTP_HOST'].$u;

}

function preg_match_all_recursive($pattern, $subject, $offset = 0) {

	$result = array();
		
	preg_match($pattern, $subject, $m, PREG_OFFSET_CAPTURE, $offset);
	
	if (!empty($m) && isset($m[1][1])){
		$pos = $m[1][1];
		$str = $m[1][0];
		$result = preg_match_all_recursive($pattern, $subject, $pos + strlen($str));
		$result[$pos] = $str;
	}

	if ($offset == 0) ksort($result);

	return $result;

}

function set_progress($description = '', $progress = 0) {
	$progress = array('description' => $description, 'progress' => $progress);
	file_put_contents('cache/progress.txt', json_encode($progress));
}

function cleanHTML($value){

	/* if (substr($value,0,3) == '<p>' && substr($value,-4) == '</p>'){
		$r = substr($value, 3, -4);
	} */ 
	
	$r = str_replace(array('<p>','</p>'), '', $value);
	
	return $r;

}

function validDate($value){
	return strtotime($value) !== false;
}

function isNumericId($value){
	return (strlen($value)<12 && is_numeric($value));
}
