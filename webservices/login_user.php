<?php
require_once 'connect_sql.php';

if ($healthy === FALSE){
	echo "Odyssey DB is not set.<br>";
}

if (isset($_REQUEST['password']) and isset($_REQUEST['username'])){
	
	$username = $_GET["username"];
	$password = $_GET["password"];
	$sql = "SELECT ID FROM authentication WHERE USERNAME = '$username'";
	$result = mysql_query($conn, $sql);

	if (mysql_num_rows($result) == 0){
		
		$sql = "INSERT INTO authentication (USERNAME , PASSWORD) VALUES ('" . $username ."','".$password."');";
		
		if (!mysql_query($conn, $sql)){
			die("Error description: " . mysqli_error($conn));
		}
		
		$sql = "SELECT ID FROM authentication WHERE USERNAME = '$username'";
		$result = mysql_query($conn, $sql);
		
		if (!$result){
			die("Error description: " . mysqli_error($conn));
		}
	}
	$user_ID = mysql_fetch_assoc($result)['ID'];
	
	#Create a view for the user.
	$sql = "CREATE VIEW ".$user_ID."_view AS SELECT NAME, ARTIST, ALBUM, YEAR, SIZE, LYRICS FROM songs WHERE OWNER =".$user_ID.";";
	if (!mysql_query($conn, $sql)){
		die("Error description: " . mysql_error($conn));
	}
	echo $user_ID;
	$conn->close();
}
?>