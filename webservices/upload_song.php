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

$filename = $_FILES ['file'] ['name'];
$temp = explode ( '.', $_FILES ['file'] ['name'] );
$extension = end ( $temp );
$size = $_FILES ['file'] ['size'];
$type = $_FILES ["file"] ["type"];
$user_ID = $_REQUEST["uid"];

if ((($type == "audio/mp3") 
		|| ($type == "audio/MP3")) 
		&& in_array ( $extension, $allowed_ext ) 
		&& $size > 0) {
			
	if ($_FILES ["file"] ["error"] > 0) {
		
		die ("Error: File error.");
		
	} else {
		
		#for sql (store song metadata)		
		$mysql = "INSERT INTO songs (NAME, SIZE, OWNER,TIMESTAMP) VALUES ('$filename', '$size', '$user_ID',NOW());";
		
		if (!mysql_query($mysql, $conn )) {
			die("Error description: ".mysql_error($conn));
				
		}else{
			#for mongo (store the song)
			$temp = $_FILES ['file'] ['tmp_name'];
			$grid  = $db->getGridFS();
			$storedFile = $grid->storeFile($temp);
			$song = $grid->findOne(array("_id" => $storedFile));
			$song->file['filename'] = $filename;
			$song->file['owner'] = $user_ID;
			$song->file['type'] = "song";
			$grid->save($song->file);
			
		}
		
	}
}else{
	echo "Error";
}
$connection->close();
$conn->close();
?>

