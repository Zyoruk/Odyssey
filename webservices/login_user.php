<?php
require_once 'connect_sql.php';

if (isset($_POST['password']) and isset($_POST['username'])){
	
	$username = $_POST["username"];
	$password = md5($_POST["password"]);
	
	$sql = "SELECT ID, PASSWORD FROM authentication WHERE USERNAME = '$username'";
	$result = mysql_query($sql, $conn);
	
	if ( mysql_num_rows($result) != 0 && $password != mysql_fetch_assoc($result)['PASSWORD']){
		die ("Wrong password"); 
	}
	
	$result = mysql_query($sql, $conn);
	
	if (mysql_num_rows($result) == 0){
		
		$sql = "INSERT INTO authentication (USERNAME , PASSWORD) VALUES (' $username ','$password');";
		
		if (!mysql_query($sql, $conn)){
			die("Error description: " . mysql_error($conn));
		}
		
		#Create a view for the user.
		$sql = "CREATE VIEW ".$user_ID."_view AS SELECT NAME, ARTIST, ALBUM, YEAR, SIZE, LYRICS FROM songs WHERE OWNER =".$user_ID.";";
		if (!mysql_query($sql, $conn)){
			die("Error description: " . mysql_error($conn));
		}
		
		$sql = "SELECT ID FROM authentication WHERE USERNAME = '$username'";
		$result = mysql_query($sql, $conn);
		
		if (!$result){
			die("Error description: " . mysql_error($conn));
		}
	}
	
	$user_ID = mysql_fetch_assoc($result)['ID'];
	echo $user_ID;
	$conn->close();
	
}else{
	die ("Woops");
}
?>