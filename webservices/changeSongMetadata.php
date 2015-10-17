<?php

require_once 'connect_sql.php'; // connect to mysql

//Check for possible requests. ID must be specified.
if (isset ( $_REQUEST ['id'] ) 
		&& (isset ( $_REQUEST ['name'] ) 
				|| isset ( $_REQUEST ['artist'] ) 
				|| isset ( $_REQUEST ['year'] ) 
				|| isset ( $_REQUEST ['album'] ) 
				|| isset ( $_REQUEST ['lyrics'] ))) {
					 				
	$id = $_REQUEST ['id'];
	$name = $_REQUEST ['name'] or 'NULL';
	$year = $_REQUEST ['year'] or 'NULL';
	$artist = $_REQUEST ['artist'] or 'NULL';
	$lyrics = $_REQUEST ['lyrics'] or 'NULL';
	$album = $_REQUEST ['album'] or 'NULL';
	
	$multiple = FALSE;
	
	#Build the query
	$sql = "UPDATE songs SET ";
	
	if ($name != ''){
		
		if ($multiple === TRUE) $sql = $sql .", ";	
		$sql = $sql . "NAME = '$name'";
		$multiple = TRUE;
		
	}
	if ($year != ''){
		
		if ($multiple === TRUE) $sql = $sql .", ";	
		$sql = $sql . "YEAR = '$year'";
		$multiple = TRUE;
		
	}
	if ($artist != ''){
		
		if ($multiple === TRUE) $sql = $sql .", ";	
		$sql = $sql . "ARTIST = '$artist'";
		$multiple = TRUE;
		
	}
	if ($lyrics != ''){
		
		if ($multiple === TRUE) $sql = $sql .", ";	
		$sql = $sql . "LYRICS = '$lyrics'";
		$multiple = TRUE;
		
	}
	
	if ($album != ''){
		
		if ($multiple === TRUE) $sql = $sql .", ";	
		$sql = $sql . "ALBUM = '$album'";
		$multiple = TRUE;
		
	}
	
	$sql = $sql."\n WHERE ID = '$id';";
	
	if (! mysql_query ( $conn, $sql )) {
		$conn->close();
		die ( 'Error description: ' . mysql_error ( $conn ) );
	}
	
	$conn->close();
} 
		