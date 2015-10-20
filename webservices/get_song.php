<?php

require_once "connect_mongo.php";

if (isset($_REQUEST['filename']) && isset($_REQUEST["owner"])){
	#mongo
	#$song_name  = $_REQUEST['filename'];
	#$owner = $_REQUEST["owner"];
	$song_name = "AUD-20150402-WA0001.mp3";
	$owner = 4;
	$grid = $db->getGridFS();
	$song = $grid->findOne(array("filename" => $song_name, "owner" => $owner));	

	echo $song->getBytes();
	
	$connection->close();
	exit;
	
}
?>