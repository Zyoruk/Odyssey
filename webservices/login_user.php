<?php

/**
 * @author Zyoruk
 * @param username POST
 * @param password POST
 *
 * This will connect to mysql, and perform the following.
 * 
 * Check if the username and password are correct.
 * If the username doesnt exists it will create a new one. 
 * @return user ID or Error 
 */
require_once 'connect_sql.php';


if (isset($_POST['password']) and isset($_POST['username'])){
	
	$username = $_POST["username"];
	$password = md5($_POST["password"]);

	$sql = "SELECT ID, PASSWORD FROM authentication WHERE USERNAME = '$username'";
	$result = mysql_query($sql, $conn);
	
	if (!result){
		die ("Error description" . mysql_error ($conn));
	}

	if (mysql_num_rows($result) == 0){
		
		$sql = "INSERT INTO authentication (USERNAME , PASSWORD) VALUES ('$username ','$password');";
		if (!mysql_query($sql, $conn)){
			die ( "{'error':Error description: ".mysql_error($conn)."}" );
		}

		$sql = "SELECT ID FROM authentication WHERE USERNAME = '$username'";
		$result = mysql_query($sql, $conn);

		if (!$result){
			die ( "{'error':Error description: ".mysql_error($conn)."}" );
		}

		$result = mysql_fetch_assoc($result);
		$user_ID = $result["ID"];
		echo $user_ID;
		

	}else {		
		$sql = "SELECT ID, PASSWORD FROM authentication WHERE USERNAME = '$username'";
		$result = mysql_query($sql, $conn);
		
		$result = mysql_fetch_assoc($result);
		$user_ID = $result["ID"];
		$pwd = $result["PASSWORD"];

		if ($password != $pwd){
			die ("{'error':Wrong password}");
		}
		
		echo $user_ID;
		
	}
	
}else{
	
	die ("{'error':Woops}");
}
?>