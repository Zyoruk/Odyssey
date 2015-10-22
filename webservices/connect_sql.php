<?php

$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = 'odyssey';

// Create connection
$conn = mysql_connect ( $servername, $username, $password, TRUE );
mysql_select_db ($dbname, $conn );

// Check connection
if (! $conn) {
	die ( "Connection failed: " . mysql_connect_error ());
}

?>