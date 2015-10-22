<?php

require_once "connect_mongo.php";

if (!isset($_FILES["file"]) || !isset($_REQUEST["uid"])){
	$connection->close();
	die("Error");
}


$user = $_REQUEST["uid"];

$allowed_ext = array (
		'png',
		'jpg',
		'bmp' 
);

$temp = explode ( '.',$_FILES ['file'] ['name'] );
$extension = end ( $temp );
$size = $_FILES ['file'] ['size'];
$type = $_FILES ["file"] ["type"];

if ((($type == "image/png") 
		|| ($type == "image/jpg")
		||  ($type == "image/bmp"))
		&& in_array ( $extension, $allowed_ext ) 
		&& $size > 0) {
			
	if ($_FILES ["file"] ["error"] > 0) {
		$connection->close();
		die( 'Error');
		
	} else {
		
		#for mongo (store the photo)
		$filename = $_FILES ['file'] ['name'];
		$temp = $_FILES ['file'] ['tmp_name'];
		$grid  = $db->getGridFS();
		#if a photo already exists, remove the old one.
		$old_photo = $grid->findOne(array("owner"=>$user, "type"=>"image"));

		if ($old_photo != NULL){
			$old_photo->remove();	
		}

		$storedFile = $grid->storeFile($temp);
		$image = $grid->findOne(array("_id" => $storedFile));
		$image->file['filename'] = $filename;
		$image->file['owner'] = $user;
		$image->file['type'] = "image";
		$grid->save($image->file); 
		$connection->close();
	
	}
}else{
	$connection->close();
	die("Error");
}

?>