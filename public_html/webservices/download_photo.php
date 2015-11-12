<?php

require_once 'connect_mongo.php';

if (isset($_REQUEST['uid'])){
	#mongo
	$user  = $_REQUEST['uid'];
	$grid = $db->getGridFS();
	$photo = $grid->findOne(array("owner" => $user
			
	));	
	
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$song_name.'"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . $photo->file['length']);

	echo $photo->getBytes();
	
	$connection->close();
	exit;
	
}
?>
