<?php

class Social {
	
	function befriend(){
		require_once 'connect_sql.php';
		
		$userID = $_REQUEST['uid'];
		$otherID = $_REQUEST['oid'];
		
		$sql = "INSERT INTO friend_relation (user_id, friends_id) VALUES ('$userID', '$otherID')";
		
		if (!mysql_query($sql , $conn)){
			die ("Error description: ". mysql_errno($conn));
		}
		
		$conn->close();
	}
	
	function unfriend(){
		require_once 'connect_sql.php';
		$userID = $_REQUEST['uid'];
		$otherID = $_REQUEST['oid'];
		
		$sql = "DELETE FROM friend_relation WHERE user_id = '$userID' AND song_id = '$otherID'";
		
		if (!mysql_query($sql , $conn)){
			die ("Error description: ". mysql_errno($conn));
		}
		
		$conn->close();
	}
	
	function like() {
		require_once 'connect_sql.php';
		
		$songID = $_REQUEST ['sid'];
		$userID = $_REQUEST ['uid'];
		
		// if the user had a like , then remove it.
		$sql = "SELECT 1 FROM Likes WHERE user_id = '$userID'";
		$result = mysql_query ( $sql, $conn );
		if (! $result) {
				
			die ( "Error description: " . mysql_error ( $conn ) );
		}
		
		if (mysql_num_rows($result) == 0){
		
			$sql = "INSERT INTO Likes (song_id, user_id) VALUES('$songID' , '$userID' )";
			
			if (! mysql_query ( $sql, $conn )) {
				
				die ( "Error description: " . mysql_error ( $conn ) );
			}
		}else{
			$sql = "DELETE FROM Likes WHERE user_id = '$userID'";
				
			if (! mysql_query ( $sql, $conn )) {
			
				die ( "Error description: " . mysql_error ( $conn ) );
			}
		}
		
		$conn->close();
	}
	
	function dislike() {
		require_once 'connect_sql.php';
		
		$songID = $_REQUEST ['sid'];
		$userID = $_REQUEST ['uid'];
		
		// if the user had a like , then remove it.
		$sql = "SELECT 1 FROM Dislikes WHERE user_id = '$userID'";
		
		$result = mysql_query ( $sql, $conn );
		
		if (! $result) {
							
			die ( "Error description: " . mysql_error ( $conn ) );
		}
		
		if (mysql_num_rows($result) == 0){
		
			$sql = "INSERT INTO Dislikes (song_id, user_id) VALUES('$songID' , '$userID' )";
			
			if (! mysql_query ( $sql, $conn )) {
				
				die ( "Error description: " . mysql_error ( $conn ) );
			}
		}else{
			$sql = "DELETE FROM Dislikes WHERE user_id = '$userID'";
				
			if (! mysql_query ( $sql, $conn )) {
			
				die ( "Error description: " . mysql_error ( $conn ) );
			}
		}
		
		$conn->close();
	}
	
	
	function comment() {
		require_once 'connect_sql.php';
		require_once 'connect_mongo.php';
		
		
		$songID = $_REQUEST ['sid'];
		$userID = $_REQUEST ['uid'];
		
		$text = $_POST ['comm'];
		// for mongo (store the commentary)
		$comment_collection = $db->comment;
		$commentary = array (
				'text' => $text 
		);
		$comment_collection->insert ( $commentary );
		
		$id =  (string) $commentary ["_id"];
		
		$mysql = "INSERT INTO comments ( user_id, song_id, comment_id) VALUES ('$userID', '$songID','$id' );";
		
		if (! mysql_query ( $mysql, $conn )) {
			die ( "Error description: " . mysql_error ( $conn ) );
		}
		
		$conn->close ();
		$connection->close ();
	}
	
	function commentPhoto() {
		require_once 'connect_sql.php';
		require_once 'connect_mongo.php';
		
		$songID = $_REQUEST ['sid'];
		$userID = $_REQUEST ['uid'];
		
		$allowed_ext = array (
				'png',
				'jpg',
				'bmp' 
		);
		
		$temp = explode ( '.', $_FILES ['file'] ['name'] );
		$extension = end ( $temp );
		$size = $_FILES ['file'] ['size'];
		$type = $_FILES ["file"] ["type"];
		
		if ((($type == "image/png") || ($type == "image/jpg") || ($type == "image/bmp")) && in_array ( $extension, $allowed_ext ) && $size > 0) {
			
			if ($_FILES ["file"] ["error"] > 0) {
				die ( 'File error' );
			} else {
				
				// for mongo (store the photo)
				$filename = $_FILES ['file'] ['name'];
				$temp = $_FILES ['file'] ['tmp_name'];
				$grid = $db->getGridFS ();
				
				$storedFile = $grid->storeFile ( $temp );
				$image = $grid->findOne ( array (
						"_id" => $storedFile 
				) );
				$image->file ['filename'] = $filename;
				$image->file ['owner'] = $user;
				$image->file ['type'] = "image";
				$grid->save ( $image->file );
				
				$id = ( string ) $storedFile;
				
				$mysql = "INSERT INTO comments ( user_id, song_id, comment_id) VALUES ('$userID', '$songID','$id' );";
				
				if (! mysql_query ( $mysql, $conn )) {
					die ( "Error description: " . mysql_error ( $conn ) );
				}
				
				$conn->close ();
				$connection->close ();
			}
		}
	}
	
	function removeComment(){
		require_once 'connect_sql.php';
		require_once 'connect_mongo.php';

		$commentID = $_REQUEST ['cid'];
		$userID = $_REQUEST['uid'];
		
		$mysql = "SELECT user_id FROM comments WHERE comment_id = '$commentID'";
		$result = mysql_query($mysql);
		
		if (!result){
			die ("Error description: ". mysql_error($conn)); 
		}
		
		$result = mysql_fetch_assoc($result);
		
		if (!($result["user_id"] == $userID)){
			die ("Error: Cannot remove a comment");
		}
		// for mongo (remove the commentary)
		$db->comment->remove( array (
				'_id' => $commentID
		));

		$mysql = "DELETE FROM comments WHERE comment_id = '$commentID';";
		
		if (! mysql_query ( $mysql, $conn )) {
			die ( "Error description: " . mysql_error ( $conn ) );
		}
		
		$conn->close ();
		$connection->close ();
	}
	
	function getComments(){
		require_once 'connect_sql.php';
		require_once 'connect_mongo.php';
		
		
		$songID = $_REQUEST ['sid'];
		
		$commentID = $_REQUEST ['cid'];
		
		$mysql = "SELECT comment_id FROM comments WHERE song_id = '$songID';";
		
		$result = mysql_query($mysql , $conn);
		
		if (! $result) {
			die ( "Error description: " . mysql_error ( $conn ) );
		}
		
		$comments = [ ];
		
		while ( $row = mysql_fetch_assoc ( $result ) ) {
			
			$comments += $db->comment->find ( array (
					'_id' => $row ['comment_id'] 
			) );
			
			$grid = $db->getGridFS ();
			
			$comments += $grid->find ( array (
					'_id' => $row ['comment_id'] 
			) );
			
		}
		
		echo $comments;
		
		$conn->close ();
		$connection->close ();
	}
	
	function getLikes(){
		$songID = $_REQUEST['sid'];
		
		$sql = "SELECT COUNT(user_id) AS c FROM Likes WHERE song_id = '$songID'";
		
		$result = mysql_query ( $sql, $conn );
		if (! $result) {

			die ( "Error description: " . mysql_error ( $conn ) );
		}
		
		$count = mysql_fetch_assoc($result)['c'];
		
		echo $count;
		exit();
	}
	
	function getDislikes(){
		$songID = $_REQUEST['sid'];
	
		$sql = "SELECT COUNT(user_id) AS c FROM Dislikes WHERE song_id = '$songID'";
	
		$result = mysql_query ( $sql, $conn );
		if (! $result) {

			die ( "Error description: " . mysql_error ( $conn ) );
		}
	
		$count = mysql_fetch_assoc($result)['c'];
	
		echo $count;
		exit();
	}
}

if (isset ( $_REQUEST ['Like'] ) && isset ( $_REQUEST ['sid'] ) && isset ( $_REQUEST ['uid'] )) {
	
	$social = new Social ();
	$social -> like ();
} 

else if (isset ( $_REQUEST ['Dislike'] ) && isset ( $_REQUEST ['sid'] ) && isset ( $_REQUEST ['uid'] )) {
	
	$social = new Social ();
	$social -> dislike ();
} 
else if (isset ($_REQUEST['AddC']) && isset ( $_POST ['comm'] ) && isset ( $_REQUEST ['sid'] ) && isset ( $_REQUEST ['uid'] )) {
	
	$social = new Social ();
	$social -> comment ();
	
}else if (isset ($_REQUEST['AddCP']) && isset ( $_POST ['file'] ) && isset ( $_REQUEST ['sid'] ) && isset ( $_REQUEST ['uid'] )) {

	$social = new Social ();
	$social -> commentPhoto();

}else if (isset ($_REQUEST['gl']) &&  (isset ($_REQUEST['sid']))){
	
	$social = new Social ();
	$social -> getLikes ();
	
}else if (isset ($_REQUEST['gdl']) &&  (isset ($_REQUEST['sid']))){
	
	$social = new Social ();
	$social -> getDislikes ();

}else if (isset ($_REQUEST['rmc']) && isset ( $_REQUEST ['cid'] ) && isset ( $_REQUEST ['sid'] ) && isset ( $_REQUEST ['uid'] )) {
	
	$social = new Social ();
	$social -> removeComment();
		
}else if (isset ($_REQUEST['bf']) && isset ( $_REQUEST ['uid'] ) && isset ( $_REQUEST ['oid'] )) {

	$social = new Social ();
	$social -> befriend();
	
}else if (isset ($_REQUEST['uf']) && isset ( $_REQUEST ['uid'] ) && isset ( $_REQUEST ['oid'] )) {

	$social = new Social ();
	$social -> unfriend();
	
}else if (isset ($_REQUEST['gc']) && isset ( $_REQUEST ['sid'] )) {
	$social = new Social ();
	$social -> getComments();

}else if (isset ($_REQUEST['rc']) && isset ( $_REQUEST ['cid'] ) && isset ( $_REQUEST ['uid'] )) {
	$social = new Social ();
	$social -> removeComment();
	
}else {
	die ( "Woops" );
}
?>
