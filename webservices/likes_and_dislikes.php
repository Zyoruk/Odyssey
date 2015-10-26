<?php

require_once 'connect_sql.php';

if (isset ( $_POST['Like']) && isset($_POST['songid']) && isset ($_POST['uid']))
{
	$songID = $_POST['songid'];
	$userID = $_POST['uid'];

	$sql = "INSERT Likes (song_id, user_id) VALUES('$songID' , '$userID' )";
	if (!mysql_query($sql , $connection)){
		$connection->close();
		die("Error description: ", mysql_error($connection);
	}
}

else if(isset ( $_POST['Dislike']) && isset($_POST['songid']) && isset ($_POST['uid']))
{
	$songID = $_POST['songid'];
	$userID = $_POST['uid'];

	$sql = "INSERT Dislikes (song_id, user_id) VALUES('$songID' , '$userID' )";
	if (!mysql_query($sql , $connection)){
		$connection->close();
		die("Error description: ", mysql_error($connection);
	}
}

$connection->close();

?>
