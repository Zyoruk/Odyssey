<?php

require_once 'connect_mongo.php';

if (isset($_REQUEST['filename']) && isset($_REQUEST["owner"])){
	#mongo
	$song_name  = $_REQUEST['filename'];
	$owner = $_REQUEST["owner"];
	$grid = $db->getGridFS();
	$song = $grid->findOne(array("filename" => $song_name, "owner" => $owner));	
	
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$song_name.'"');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . $song->file['length']);

	echo $song->getBytes();
	
	$connection->close();
	exit;
	
}
?>
