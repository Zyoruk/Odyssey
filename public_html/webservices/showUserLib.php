<?php

require_once 'connect_sql.php';

 if (isset ($_REQUEST['uid'])){
 	
 	$user = $_REQUEST ["uid"];
 	$sql = "SELECT NAME, ARTIST, ALBUM, YEAR, SIZE FROM songs WHERE OWNER = '$user';";
 	$result = mysql_query($conn, $sql);
 	
 	if (!$result){
 		$conn->close();
 		die ("Error description: ".mysql_errno($conn));
 	}
 	
 	while ($row = mysql_fetch_assoc($result)){
 		echo "".$row["NAME"].$row["ARTIST"].$row["ALBUM"].$row["YEAR"].$row["SIZE"]."<br>";
 	}
 }
 
 $conn->close();
 
?>