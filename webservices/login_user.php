<?php
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
			die("Error description: " . mysql_error($conn));
		}

		$sql = "SELECT ID FROM authentication WHERE USERNAME = '$username'";
		$result = mysql_query($sql, $conn);

		if (!$result){
			die("Error description: " . mysql_error($conn));
		}

		$result = mysql_fetch_assoc($result);
		$user_ID = $result["ID"];
		echo $user_ID;
		$conn->close();

	}else {		
		$sql = "SELECT ID, PASSWORD FROM authentication WHERE USERNAME = '$username'";
		$result = mysql_query($sql, $conn);
		
		$result = mysql_fetch_assoc($result);
		$user_ID = $result["ID"];
		$pwd = $result["PASSWORD"];

		if ($password != $pwd){
			die ("Wrong password");
		}
		
		echo $user_ID;
		$conn->close();
	}
	
}else{
	$conn->close();
	die ("Woops");
}
?>