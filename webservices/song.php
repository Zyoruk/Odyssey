<?php
// Song class
// Connects to mongo and mysql.
class Song {
	function upload() {
		require_once 'connect_sql.php';
		require_once 'connect_mongo.php';
		
		$allowed_ext = array (
				'mp3',
				'MP3',
				'mpeg' 
		);
		
		$filename = $_FILES ['file'] ['name'];
		$temp = explode ( '.', $_FILES ['file'] ['name'] );
		$extension = end ( $temp );
		$size = $_FILES ['file'] ['size'];
		$type = $_FILES ["file"] ["type"];
		$user_ID = $_REQUEST ["uid"];
		
		if ((($type == "audio/mp3") || ($type == "audio/MP3") || ($type == "audio/mpeg")) && in_array ( $extension, $allowed_ext ) && $size > 0) {
			
			if ($_FILES ["file"] ["error"] > 0) {
				$conn->close ();
				$connection->close ();
				die ( "Error: File error." );
			} else {

				// for sql (store song metadata)
				
				$mysql = "INSERT INTO songs ( NAME, SIZE, OWNER,TIMESTAMP) VALUES ('$filename', '$size', '$user_ID',NOW());";
				if (! mysql_query ( $mysql, $conn )) {
					die ( "Error description: " . mysql_error ( $conn ) );
				}
				
				// for mongo (store the song)
				$temp = $_FILES ['file'] ['tmp_name'];
				$grid = $db->getGridFS ();
				$storedFile = $grid->storeFile ( $temp );
				$song = $grid->findOne ( array (
						"_id" => $storedFile 
				) );
				$song->file ['filename'] = $filename;
				$song->file ['owner'] = $user_ID;
				$song->file ['type'] = "song";
				$grid->save ( $song->file );

			}
		} else {
			$conn->close ();
			$connection->close ();
			die ( 'Woops' );
		}
		
		$conn->close ();
		$connection->close ();
		exit();
	}
	
	function download() {
		require_once  'connect_mongo.php';
		// mongo
		$song_name = $_REQUEST ['filename'];
		$owner = $_REQUEST ["owner"];
		$grid = $db->getGridFS ();
		$song = $grid->findOne ( array (
				"filename" => $song_name,
				"owner" => $owner 
		) );
		
		header ( 'Content-Description: File Transfer' );
		header ( 'Content-Type: application/octet-stream' );
		header ( 'Content-Disposition: attachment; filename="' . $song_name . '"' );
		header ( 'Expires: 0' );
		header ( 'Cache-Control: must-revalidate' );
		header ( 'Pragma: public' );
		header ( 'Content-Length: ' . $song->file ['length'] );
		echo $song->getBytes ();
		$conn->close ();
		exit ();
	}
	
	function changemetadata() {
		require_once 'connect_sql.php';
		// Check for possible requests. ID must be specified.
		$id = $_REQUEST ['sid'];
		$name = $_REQUEST ['name'] or 'NULL';
		$year = $_REQUEST ['year'] or 'NULL';
		$artist = $_REQUEST ['artist'] or 'NULL';
		$lyrics = $_REQUEST ['lyrics'] or 'NULL';
		$album = $_REQUEST ['album'] or 'NULL';
		
		$multiple = FALSE;
		
		// Build the query
		$sql = "UPDATE songs SET ";
		
		if ($name != '') {
			
			if ($multiple === TRUE)
				$sql = $sql . ", ";
			$sql = $sql . "NAME = '$name'";
			$multiple = TRUE;
		}
		if ($year != '') {
			
			if ($multiple === TRUE)
				$sql = $sql . ", ";
			$sql = $sql . "YEAR = '$year'";
			$multiple = TRUE;
		}
		if ($artist != '') {
			
			if ($multiple === TRUE)
				$sql = $sql . ", ";
			$sql = $sql . "ARTIST = '$artist'";
			$multiple = TRUE;
		}
		if ($lyrics != '') {
			
			if ($multiple === TRUE)
				$sql = $sql . ", ";
			$sql = $sql . "LYRICS = '$lyrics'";
			$multiple = TRUE;
		}
		
		if ($album != '') {
			
			if ($multiple === TRUE)
				$sql = $sql . ", ";
			$sql = $sql . "ALBUM = '$album'";
			$multiple = TRUE;
		}
		
		$sql = $sql . ", TIMESTAMP = NOW()";
		$sql = $sql . "\n WHERE ID = '$id';";
		
		if (! mysql_query ( $sql, $conn )) {
			die ( 'Error description: ' . mysql_error ( $conn ) );
		}
		$conn->close ();
		exit();
	}
	
	function getsong() {
		require_once 'connect_mongo.php';
		// mongo
		$song_name = $_REQUEST ['filename'];
		$owner = $_REQUEST ["owner"];
		$grid = $db->getGridFS ();
		$song = $grid->findOne ( array (
				"filename" => $song_name,
				"owner" => $owner 
		) );
		echo $song->getBytes ();
		$conn->close ();
		exit();
	}
}

if (isset ( $_REQUEST ['f'] )) {
	
	$fun = $_REQUEST ['f'];
	$song = new Song ();

	if ($fun == 'ups') {
		
		if (isset ( $_REQUEST ['uid'] )) {
			
			$song->upload ();
		}
	} else if ($fun == 'gs') {
		if (isset ( $_REQUEST ['filename'] ) && isset ( $_REQUEST ["owner"] )) {
			
			$song->getsong ();
		}
	} else if ($fun =='dds') {
		
		if (isset ( $_REQUEST ['filename'] ) && isset ( $_REQUEST ["owner"] )) {
			$song->download ();
		}
	} else if ($fun == 'csmd') {
		
		if (isset ( $_REQUEST ['sid'] ) && (isset ( $_REQUEST ['name'] ) || isset ( $_REQUEST ['artist'] ) || isset ( $_REQUEST ['year'] ) || isset ( $_REQUEST ['album'] ) || isset ( $_REQUEST ['lyrics'] ))) {
			$song->changemetadata ();
		}
	} else {
		
		die ( "Check params." );
	}
} else {
	
	die ( 'Check params.' );
}

?>