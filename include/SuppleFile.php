<?php

require_once('include/SuppleApplication.php');
require_once('include/util.php');

class SuppleFile {

	var $pc; // DB Connection
	
	var $thumb_max_width = 200; // a goood preview
	var $thumb_max_height = 200; // a goood preview
	var $thumb_quality = 75;

	var $img_max_width = 1920; 
	var $img_max_height = 1280; 
	var $img_quality = 95;
	
  	var $img_extensions = array('JPG', 'JPEG', 'PNG', 'GIF' );
    // var $img_nothumb_extensions = array('PNG', 'GIF');

	function __construct(){
		// TODO: use $db global variable.
		$this->pc = SuppleApplication::getdb(); // TODO: Subclass SuppleObject
		// TODO: get max width, height, and quality form images and thumbnails from config
	}

	function upload($source, $name, $id, $table, $key){
		return $this->updo($source, $name, $id, $table, false, $key);
	}

	function update($source, $name, $id, $table, $key){
		return $this->updo($source, $name, $id, $table, true, $key);
	}

	// TODO: Size should be calculated at runtime
	function updo($source, $name, $id, $table, $update, $key){

		if ($id == '') return;

		// destination filename
		$filename = $this->get_full_filename($name, $id, $table, $key);
		
		// UPLOAD THE FILE!!!
		if ($update){
			$row = $this->pc->from($table)->where(array('id' => $id))->getRow();
			if (empty($row[$key])){
				// IS NEW!
				$newFilename = $this->uploadFile($source, $filename);
			} else {
				$oldfile = $row[$key];
				$newFilename = $this->updateFile($oldfile ,$source, $filename);
			}
		} else {
			$newFilename = $this->uploadFile($source, $filename);
		}

		// Update in DB
		if ($newFilename){

			// Calculate Filesize in KB
			$file_size = (int)(filesize($newFilename) / 1024);
			if ($file_size == 0) $file_size = 1;
			
			//$this->pc->update($table, array($key.'_size' => $file_size), array('id' => $id));
			
      		// IF is image
			if ($this->is_image($newFilename) && file_exists($source)){
				$ext = strtoupper(substr($newFilename, strrpos($newFilename,'.')+1));  
				// Create thumbnail
				$thumb = $this->createThumbnail($source, $name, $id, $table, $key, $ext);
				if ($ext != 'PNG' && $ext != 'GIF'){
					// Converting to jpg, so:
					$oldFilename = $newFilename;
					$newFilename = substr($newFilename, 0, strrpos($newFilename, '.')).'.jpg';
					if ($oldFilename != $newFilename && file_exists($oldFilename)) unlink($oldFilename);
					// Resize image
					$this->resizeToFile($source, $this->img_max_width, $this->img_max_height, $newFilename, $this->img_quality, $ext);
					// Evaluates the size of the resulting file: keep the smallest.
					if (file_exists($source) && file_exists($newFilename) && ((filesize($source) > 0 && filesize($source) < filesize($newFilename)) || $ext == 'PNG' || $ext == 'GIF') && $source != $newFilename){
						unlink($newFilename);
						copy($source, $oldFilename);
						$newFilename = $oldFilename; /// AHHH, PERO QUE QUILOMBOOO
					}
				} else if (file_exists($source)) {
					$thumb = $this->createNoThumbnail($source, $name, $id, $table, $key);
				}
				// } elseif ($this->is_image_nothumb($newFilename) && file_exists($source)) {	
					// Copy only
				//	$thumb = $this->createNoThumbnail($source, $name, $id, $table, $key);
			}

			// UPDATE!
			$data = array($key => $newFilename, $key.'_size' => $file_size);
			if ($thumb) $data[$key.'_thumb'] = $thumb;
			$this->pc->update($table, $data, array('id' => $id));

		} else {

			// Empty data
			$data = array($key => '', $key.'_size' => 0);
			$this->pc->update($table, $data, array('id' => $id));

		}

		// print_r($data); die();

		return $data;

	}

	function delete($id, $table){
		// Delete the file
		$path = 'uploaded/'.$table.'/';
		$files = folderContent($path, $id.'_*.*');
		foreach ($files as $file){
			$this->deleteFile($path.$file); 
		}
		
		// ... and the thumbnails
		$path = 'uploaded/'.$table.'/thumbs/';
		$files = folderContent($path, $id.'_*.*');
		foreach ($files as $file){
			$this->deleteFile($path.$file); 
		}
	}

	function get_full_filename($name, $id, $table, $key){
		$folder = 'uploaded/'.$table;
		createFolder($folder);
		return  $folder.'/'.$id.'_'.$key.'_'.preg_replace('/[^A-Za-z0-9.]+/', '_', $name); //str_replace(' ', '_', $name);
	}
	
	function get_thumb_filename($name, $id, $table, $key){
		$ext = strtoupper(substr($name, strrpos($name,'.')+1));
		$nm = substr($name, 0, strrpos($name, '.'));
		$folder = 'uploaded/'.$table.'/thumbs';
		createFolder($folder);
		return  $folder.'/'.$id.'_'.$key.'_'.preg_replace('/[^A-Za-z0-9.]+/', '_', $nm).'.jpg'; //str_replace(' ', '_', $name);
	}

	function is_image($name){
		// based in filename
	  	if (is_string($name) && (strlen($name) > 3)){
		  	foreach ($this->img_extensions as $format){
			  	if ( substr(strtoupper($name), strlen($name) - strlen($format)) == strtoupper($format) ){
				  	return true;
			  	}
		  	}
	  	} 
		return false;
	}

	/*
	function is_image_nothumb($name){
		// based in filename
	  $is = false;

	  if (is_string($name) && (strlen($name) > 3)){
		  foreach ($this->img_nothumb_extensions as $format){
			  if ( substr(strtoupper($name), strlen($name) - strlen($format)) == strtoupper($format) ){
				  $is = true;
			  }
		  }
	  } 

	  return $is;
	}
	*/

	// THUMBNAILS!!
	function createThumbnail($source, $name, $id, $table, $key, $ext = ''){
		// generate thumb name
		$thumbName = $this->get_thumb_filename($name, $id, $table, $key);
		// makes a resized copy
		$this->resizeToFile($source, $this->thumb_max_width, $this->thumb_max_height, $thumbName, $this->thumb_quality, $ext);
		// save the name of the thumbnail
		if (file_exists($thumbName)){
			return $thumbName;
		}
		return false;
	}

	function createNoThumbnail($source, $name, $id, $table, $key){
		// Copy with no resize
		$thumbName = $this->get_thumb_filename($name, $id, $table, $key);
		copy($source, $thumbName);
		if (file_exists($thumbName)){
			return $thumbName;
		}
		return false;
	}

	function resizeToFile($sourcefile, $max_x, $max_y, $targetfile, $jpegqual, $ext = ''){

		if (function_exists('getimagesize') && 
		    function_exists('imageCreateFromJPEG') && 
			function_exists('imagecreatetruecolor') && 
			function_exists('imagecopyresampled') && 
			function_exists('imagejpeg')){

			/*$picsize = getimagesize("$sourcefile");
			$source_x = $picsize[0];
			$source_y  = $picsize[1];*/
			
			if ($ext == 'GIF'){
				$image = imagecreatefromgif("$sourcefile");
				$source_id = imagecreatetruecolor(imagesx($image), imagesy($image));
				imagefill($source_id, 0, 0, imagecolorallocate($source_id, 128, 128, 128));
				imagealphablending($source_id, TRUE);
				imagecopy($source_id, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
				imagedestroy($image);
			} else if ($ext == 'PNG'){
				$image = imagecreatefrompng("$sourcefile");
				$source_id = imagecreatetruecolor(imagesx($image), imagesy($image));
				imagefill($source_id, 0, 0, imagecolorallocate($source_id, 128, 128, 128));
				imagealphablending($source_id, TRUE);
				imagecopy($source_id, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
				imagedestroy($image);
			} else {
				$source_id = imageCreateFromJPEG("$sourcefile");
			}			
			$source_x = imagesx($source_id);
			$source_y = imagesy($source_id);
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

			$target_id=imagecreatetruecolor($dest_x, $dest_y); // empty image!
			$target_pic=imagecopyresampled($target_id,$source_id, 0,0,0,0, $dest_x,$dest_y, $source_x,$source_y);
			imagejpeg ($target_id,"$targetfile",$jpegqual);

			// echo "END $ext $targetfile - $dest_x $dest_y"; die();

			return true;
		} else {
			return false;
		}
	} 

	// FILE MANAGEMENT
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

	function updateFile($oldFile, $source, $target){
		if (file_exists($source)){
			$this->deleteFile($oldFile);
			$r = $this->uploadFile($source, $target);
		} else {
			$r = $oldFile;
		}
		return $r;
	}

}

?>
