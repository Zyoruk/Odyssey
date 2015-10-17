<?php
$servername = "odissey.cltdmejxzxq6.us-west-2.rds.amazonaws.com:3306";
$username = "jeukel";
$password = "1q2w3e4r5t";
$dbname = 'odyssey';

// Create connection
$conn = mysql_connect ( $servername, $username, $password, TRUE);
mysql_select_db($conn, $dbname);

// Check connection
if (! $conn) {
	die ( "Connection failed: " . mysqli_connect_error () . "<br>" );
}
?>