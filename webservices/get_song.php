<?php

require_once "connect_mongo.php";

if (isset($_REQUEST['song'])&& isset($_REQUEST['uid'])){
	$song = $_REQUEST['song'];
	$owner = $_REQUEST['uid'];
	$grid = $db->getGridFS();
	$song = $grid->findOne(array("filename" => $song_name, "onwer" => $owner));
	
}