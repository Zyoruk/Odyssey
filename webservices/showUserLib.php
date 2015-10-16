<?php

require_once 'connect_sql.php';

 if (isset ($_REQUEST['uid'])){
 	
 	$user = $_REQUEST ["uid"];
 	$sql = "SELECT NAME, ARTIST, ALBUM, YEAR, SIZE FROM songs WHERE OWNER = '$user';";
 	$result = mysqli_query($conn, $sql);
 	
 	if (!$result){
 		$conn->close();
 		die ("Error description: ".mysqli_errno($conn));
 	}
 	
 	while ($row = mysqli_fetch_assoc($result)){
 		echo "".$row["NAME"].$row["ARTIST"].$row["ALBUM"].$row["YEAR"].$row["SIZE"]."<br>";
 	}
 }
 
 $conn->close();
 
?>