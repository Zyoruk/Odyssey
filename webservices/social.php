<?php

class Social {
	
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
	
	function removeComment(){
		require_once 'connect_sql.php';
		require_once 'connect_mongo.php';
		
		
		$songID = $_REQUEST ['sid'];
		$userID = $_REQUEST ['uid'];
		
		$commentID = $_REQUEST ['cid'];
		// for mongo (remove the commentary)
		$db->comment->remove( array (
				'_id' => $commentID
		));

		$mysql = "DELETE FROM comments WHERE comment_id = '$commentID';";
		
		if (! mysql_query ( $mysql, $conn )) {
			die ( "Error description: " . mysql_error ( $conn ) );
		}
		
		echo "LOL3";
		$conn->close ();
		$connection->close ();
	}
	
	function getLikes(){
		require_once 'connect_sql.php';
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
		require_once 'connect_sql.php';
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
	
}else if (isset ($_REQUEST['gl']) &&  (isset ($_REQUEST['sid']))){
	
	$social = new Social ();
	$social -> getLikes ();
	
}else if (isset ($_REQUEST['gdl']) &&  (isset ($_REQUEST['sid']))){
	
	$social = new Social ();
	$social -> getDislikes ();

}else if (isset ($_REQUEST['rmc']) && isset ( $_REQUEST ['cid'] ) && isset ( $_REQUEST ['sid'] ) && isset ( $_REQUEST ['uid'] )) {
	
	$social = new Social ();
	$social -> removeComment();
	
}else {
	die ( "Woops" );
}
?>
