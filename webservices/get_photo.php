<?php

require_once "connect_mongo.php";

if ( isset($_REQUEST["owner"])){
	#mongo
	#$owner = $_REQUEST["owner"];
	$owner = "7";
	$grid = $db->getGridFS();
	$song = $grid->findOne(array("filename" => $song_name, "owner" => $owner));	

	echo $song->getBytes();
	
}
?>