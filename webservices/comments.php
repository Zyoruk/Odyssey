<?php

require_once 'connect_sql.php';
require_once 'connect_mongo.php';

if (isset ( $_POST['Comments']) && isset($_POST['songid']) && isset ($_POST['uid']))
{
	$songID = $_POST['songid'];
	$userID = $_POST['uid'];

}
?>
