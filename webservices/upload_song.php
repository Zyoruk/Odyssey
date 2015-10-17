<?php

require_once 'connect_sql.php';
require_once 'connect_mongo.php';

if (!isset($_REQUEST['uid'])){
	die ("Error."); 
} 

$allowed_ext = array (
		'mp3',
		'MP3' 
);

$temp = explode ( '.', $_FILES ['file'] ['name'] );
$extension = end ( $temp );
$size = $_FILES ['file'] ['size'];
$type = $_FILES ["file"] ["type"];

if ((($type == "audio/mp3") 
		|| ($type == "audio/MP3")) 
		&& in_array ( $extension, $allowed_ext ) 
		&& $size > 0) {
			
	if ($_FILES ["file"] ["error"] > 0) {
		
		echo 'Error<br>';
		
	} else {
		#for sql (store song metadata)
		$filename = $_FILES ['file'] ['name'];
		$temp = $_FILES ['file'] ['tmp_name'];
		
		$mysql = "INSERT INTO songs (NAME, SIZE, OWNER) VALUES ('$filename', '$size', '$user_ID');";
		
		#for mongo (store the song)
		$grid  = $db->getGridFS();
		$storedFile = $grid->storeFile($temp);
		$song = $grid->findOne(array("_id" => $storedFile));
		$song->file['filename'] = $filename;
		$grid->save($song->file); 
		
		if (!mysql_query($conn, $mysql )) {
			die("Error description: ".mysql_error($conn));
			
		}
	}
}else{
	echo "Error";
}
$connection->close();
$conn->close();
?>

