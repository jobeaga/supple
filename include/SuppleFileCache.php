<?php

require_once('include/util.php');

// Objetivo: hace un file_get_contents de un recurso utilizando una cache local antes.
//   Si el recurso es local, genera una sobrecarga innecesaria.
//   Se puede desactivar
//	 Uses a "cache" table to store cached files.

class SuppleFileCache {

  var $active = true;
  var $connection;

  function __construct() {

    $this->connection = SuppleApplication::getdb();

  }

  function fileGet($filename){

    $fname = $this->fileCachefilename($filename);
    return file_get_contents($fname);

  }

  function fileSize($filename){

    $fname = $this->fileCachefilename($filename);
    return filesize($fname);

  }

  function fileResolution($filename){

    $fname = $this->fileCachefilename($filename);
    $arr = getimagesize($fname);
    return $arr; 

  }

  function fileCachefilename($filename){

    if ($this->active){

      // Primero me fijo si ya está en la bd
      $where = array('filename' => $filename);
      $row = $this->connection->from('cache')->where($where)->getRow();

      if (empty($row) || (!file_exists($row['fname']))){

        $id = md5($filename);
        $ext = getFileExt($filename);
        $fname = "uploaded/cache/".$id."_file.".(($ext)?$ext:'tmp');

        createFolder('uploaded/cache'); // por las dudas
        // Obtengo el archivo
        $contenido = file_get_contents($filename);
        // Guardo archivo local
        file_put_contents($fname, $contenido); 

        $this->connection->insert('cache', array('filename' => $filename, 'fname' => $fname, 'id' => $id));

      } else {

        // Saco de la DB
        $fname = $row['fname'];

      }

    } else {

      // return file_get_contents($filename);
      $fname = $filename;

    }

    return $fname;

  }

  function clean(){
		// Delete the file
		$path = 'uploaded/cache/';
		$files = folderContent($path, '*.*');
		foreach ($files as $file){
			deleteFile($path.$file); 
		}
		
		// ... and the thumbnails
		$path = 'uploaded/cache/thumbs/';
		$files = folderContent($path, '*.*');
		foreach ($files as $file){
			deleteFile($path.$file); 
		}

    // and the db
    $this->connection->delete('cache', array(1 => 1));
  }

  function inCache($filename){

    if ($this->active){

      // Me fijo si ya está en la bd
      $where = array('filename' => $filename);
      $row = $this->connection->from('cache')->where($where)->getRow();

      if (empty($row) || (!file_exists($row['fname']))){

        return false;

      } else {

        return true;

      }

    } else {

      return false;

    }

  }

}


?>
