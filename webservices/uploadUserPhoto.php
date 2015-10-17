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

$temp = explode ( '.', strtolower($_FILES ['file'] ['name']) );
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
		#for sql (store song metadata)
		$filename = $_FILES ['file'] ['name'];
		$temp = $_FILES ['file'] ['tmp_name'];
		
		
		#for mongo (store the song)
		$grid  = $db->getGridFS();
		$storedFile = $grid->storeFile($temp);
		$image = $grid->findOne(array("_id" => $storedFile));
		$image->file['filename'] = $filename;
		$image->file['owner'] = $user;
		$grid->save($song->file); 
		
		$connection->close();
		
		if (!mysqli_query($conn, $mysql )) {
			$connection->close();
			die("Error description: ".mysqli_error($conn));
			
		}
	}
}else{
	$connection->close();
	die("Error");
}

?>