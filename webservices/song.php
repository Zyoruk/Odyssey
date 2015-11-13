<?php
/**
 * @author Zyoruk
 * Song class
 * Connects to mongo and mysql.
 */ 

class Song {
	/**
	 * @param f = ups GET
	 * @param song FILE.
	 * @param uid GET
	 * 
	 * Connects to mongo and mysql. Uses Mongo GridFS to store the song and mysql to store song description.
	 * only mp3 and mpeg are allowed.
	 * 
	 * @return Error or nothing
	 */
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
		$user_ID = $_GET ["uid"];
		
		if ((($type == "audio/mp3") || ($type == "audio/MP3") || ($type == "audio/mpeg")) && in_array ( $extension, $allowed_ext ) && $size > 0) {
			
			if ($_FILES ["file"] ["error"] > 0) {				
				die ( "Error: File error." );
			} else {
				
				// for sql (store song metadata)
				
				$mysql = "INSERT INTO songs ( NAME, SIZE, OWNER,TIMESTAMP) VALUES ('$filename', '$size', '$user_ID',NOW());";
				if (! mysql_query ( $mysql, $conn )) {
					die ( "{'error':Error description: ".mysql_error($conn)."}" );
				}
				
				// for mongo (store the song)
				
				// Compare hashes
				$temp = $_FILES ['file'] ['tmp_name'];
				$hash = md5($temp);
				$grid = $db->getGridFS ();
				
				$c = $grid->find(array("md5"=> $hash))->count();
				
				if ($c > 0){
					die ("{'error':Song already exists}");
				}
				
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
			die ( "{'error':Woops}" );
		}	
	}
	
	/**
	 * @param f = ds
	 * @param filename  GET
	 * @param owner GET
	 * 
	 * Connects to mongo and gets the binary data from the song.
	 * 
	 * @return Error or nothing
	 */
	function download() {
		require_once 'connect_mongo.php';
		// mongo
		$song_name = $_GET ['filename'];
		$owner = $_GET ["owner"];
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
	}
	
	/**
	 * @param f = csmd GET
	 * @param sid GET
	 * @param name POST
	 * @param year POST
	 * @param artist POST
	 * @param lyrics POST
	 * @param album POST
	 * 
	 * Connects to mysql and changes song info.
	 * 
	 * @return Error or nothing 
	 */ 
	function changemetadata() {
		require_once 'connect_sql.php';
		// Check for possible requests. ID must be specified.
		$id = $_GET ['sid'];
		$name = $_POST ['name'] or 'NULL';
		$year = $_POST ['year'] or 'NULL';
		$artist = $_POST ['artist'] or 'NULL';
		$genre = $_POST ['genre'] or 'NULL';
		$lyrics = $_POST ['lyrics'] or 'NULL';
		$album = $_POST ['album'] or 'NULL';
		
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
		if ($genre != '') {
				
			if ($multiple === TRUE){
				$sql = $sql . ", ";
			}
			$sql = $sql . "GENRE = '$genre'";
			$multiple = TRUE;
		}
		if ($lyrics != '') {
			require_once 'connect_mongo.php';
			
			$text = $lyrics;
			// for mongo (store the commentary)
			$lyrics_collection = $db->lyric;
			$lyrics = array (
					'lyrics' => $lyrics 
			);
			$lyrics_collection->insert ( $lyrics );
			
			$id = ( string ) $lyrics ["_id"];
			
			if ($multiple === TRUE){
				$sql = $sql . ", ";
			}
			
			$sql = $sql . "LYRICS = '$id'";
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
			die ( "{'error':Error description: ".mysql_error($conn)."}" );
		}
	}
	
	/**
	 * @param f = gs
	 * @param filename GET
	 * @param owner GET
	 * 
	 * Connects to mongo and returns song.
	 * 
	 * @return Bytes or Error
	 */
	function getsong() {
		require_once 'connect_mongo.php';
		// mongo
		$song_name = $_GET ['filename'];
		$owner = $_GET ["owner"];
		$grid = $db->getGridFS ();
		$song = $grid->findOne ( array (
				"filename" => $song_name,
				"owner" => $owner 
		) );
		echo $song->getBytes ();
	}
	
	/**
	 * @param f = rs
	 * @param uid GET
	 * @param sid GET
	 * 
	 * Removes song
	 * @return Error or nothing
	 */
	function removeSong() {
		require 'connect_mongo.php';
		require 'connect_sql.php';
		
		$userID = $_GET ['uid'];
		$songID = $_GET ['sid'];
		
		$query = "SELECT NAME FROM songs WHERE OWNER = '$userID' AND ID = '$songID'";
		
		$result = mysql_query ( $query );
		
		if (! $result) {
			die ( "{'error':Error description: ".mysql_error($conn)."}" );
		}
		
		if (mysql_num_rows ( $result ) == 0) {
			
			die ( "{'error':Cannot remove song}" );
		} else {
			
			$result = mysql_fetch_assoc ( $result );
			$name = $result ["NAME"];
			$grid = $db->getGridFS ();
			$grid->remove ( array (
					'filename' => $name,
					'owner' => $userID 
			) );
			
			$query = "DELETE FROM SONGS WHERE ID = '$songID'";
			
			if (! mysql_query ( $query )) {
				die ( "{'error':Error description: ".mysql_error($conn)."}" );
			}			
		}
	}
	
	/**
	 * @param f = sus
	 * @param uid GET
	 * 
	 * Shows a list of the songs
	 * 
	 * @return List or Error
	 */
	function showUserSongs() {
		require_once "connect_sql.php";
		
		$user = $_GET ["uid"];
		$sql = "SELECT ID,NAME, ARTIST, ALBUM, YEAR, SIZE FROM songs WHERE OWNER = '$user';";
		$result = mysql_query ( $conn, $sql );
		
		if (! $result) {
			
			die ( "{'error':Error description: ".mysql_error($conn)."}" );
		}else{
			$result = json_encode($result);
			echo $result;
		}
		
	}
	
	/**
	 * @param f = sch
	 * @param uid GET
	 * @param val POST
	 * 
	 * @return Error or list 
	 */
	function search (){
		require_once "connect_sql.php";
		
		$userID = $_GET['uid'];
		$val = $_POST['val'];
		
		$query = "SELECT NAME, ARTIST, ALBUM, YEAR, SIZE".
				" FROM songs".
				" WHERE OWNER = '$userID'".
				" AND NAME LIKE " . '"%"'. "'$val'" . '"%"' . 
				" OR ARTIST LIKE " . '"%"'. "'$val'" . '"%"' .
				" OR YEAR LIKE " . '"%"'. "'$val'" . '"%"' .
				" OR ALBUM LIKE " . '"%"'. "'$val'" . '"%"';
		
		$result = mysql_query($query , $conn);
		
		if (! $result) {
			
			die ( "{'error':Error description: ".mysql_error($conn)."}" );
		}else{
			$result = json_encode($result);
			echo $result;
		}
		
		
	}
	
	/**
	 * @param f = e GET
	 * @param hash GET
	 * 
	 * Checks if exists
	 * 
	 * @return Bool or Error
	 */
	
	function exists (){
		require_once 'connect_mongo.php';
		
		$hash = $_GET['hash'];
		$userID = $_GET['uid'];
		
		$c = $grid->find(array("md5"=> $hash))->count();
		
		if ($c > 0){
			echo "{exists:1}";
		}else{
			echo "{exists:0}";
		}		
	}
}

if ($_GET ['ups']) {
	
	if (isset ( $_GET ['uid'] )) {
		$song = new Song ();
		$song->upload ();
	}
} else if ($_GET ['gs']) {
	if (isset ( $_GET ['filename'] ) && isset ( $_GET ["owner"] )) {
		$song = new Song ();
		$song->getsong ();
	}
} else if ($_GET ['ds']) {
	
	if (isset ( $_GET ['filename'] ) && isset ( $_GET ["owner"] )) {
		$song = new Song ();
		$song->download ();
	}
} else if ($_GET ['csmd']) {
	
	if (isset ( $_GET ['sid'] ) && (isset ( $_POST ['name'] ) || isset ( $_POST ['artist'] ) || isset ( $_POST ['year'] ) || isset ( $_POST ['album'] ) ||isset ( $_POST ['genre'] ) ||isset ( $_POST ['lyrics'] ))) {
		$song = new Song ();
		$song->changemetadata ();
	}
} else if ($_GET ['rs']) {
	
	if (isset ( $_GET ['sid'] ) && (isset ( $_GET ['uid'] ))) {
		$song = new Song ();
		$song->removeSong ();
	}
} else if ($_GET ['sus']) {
	
	if (isset ( $_GET ['uid'] )) {
		$song = new Song ();
		$song->showUserSongs ();
	}
} else if ($_GET ['sch']) {

	if (isset ( $_GET ['uid'] ) && isset ( $_POST ['val'] )) {
		$song = new Song ();
		$song->showUserSongs ();
	}
} else if ($_GET ['e']) {

	if (isset ( $_GET ['uid'] ) && isset ( $_POST ['hash'] )) {
		$song = new Song ();
		$song->showUserSongs ();
	}

} else {
	
	die ( "{'error':Check params.}" );
}

?>