<?php

require_once "connect_mongo.php";

if (isset($_FILES["file"])&& isset($_REQUEST["uid"])){
	
	$user = $_REQUEST["uid"];
	$photo_name = $_FILES["file"]["name"];
	$photo_bin = $_FILES['file']['tmp_name'];
	
	
	$db->getGridFS();
			
}

?>