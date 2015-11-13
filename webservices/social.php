<?php
/**
 * 
 * @author zyoruk
 * 
 * Social class.
 * 
 * Provides the functionality for social methods.
 */
class Social {
	/**
	 * @param bf GET
	 * @param uid GET
	 * @param fid GET
	 * 
	 * Adds a friend
	 * 
	 * @return Error or nothing 
	 */
	function befriend() {
		require_once 'connect_sql.php';
		
		$userID = $_GET ['uid'];
		$friendID = $_GET ['fid'];
		
		$sql = "INSERT INTO friend_relation (user_id, friends_id) VALUES ('$userID', '$friendID')";
		
		if (! mysql_query ( $sql, $conn )) {
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );
		}
	}
	/**
	 * @param uf GET
	 * @param uid GET
	 * @param fid GET
	 *
	 * Removes a friend
	 *
	 * @return Error or nothing
	 */
	function unfriend() {
		require_once 'connect_sql.php';
		$userID = $_GET ['uid'];
		$friendID = $_GET ['fid'];
		
		$sql = "DELETE FROM friend_relation WHERE user_id = '$userID' AND song_id = '$friendID'";
		
		if (! mysql_query ( $sql, $conn )) {
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );
		}
	}
	/**
	 * @param Like GET
	 * @param sid GET
	 * @param uid GET
	 * 
	 * Adds a like to a song or removes it.
	 * 
	 * @return Error or nothing
	 */
	function like() {
		require_once 'connect_sql.php';
		
		$songID = $_GET ['sid'];
		$userID = $_GET ['uid'];
		
		// if the user had a like , then remove it.
		$sql = "SELECT 1 FROM Likes WHERE user_id = '$userID'";
		$result = mysql_query ( $sql, $conn );
		if (! $result) {
			
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
		}
		
		if (mysql_num_rows ( $result ) == 0) {
			
			$sql = "INSERT INTO Likes (song_id, user_id) VALUES('$songID' , '$userID' )";
			
			if (! mysql_query ( $sql, $conn )) {
				
				die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
			}
		} else {
			$sql = "DELETE FROM Likes WHERE user_id = '$userID'";
			
			if (! mysql_query ( $sql, $conn )) {
				
				die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
			}
		}
	}
	/**
	 * @param Dislike 
	 * @param sid GET
	 * @param uid GET
	 *
	 * Adds a dislike to a song or removes it.
	 *
	 * @return Error or nothing
	 */
	function dislike() {
		require_once 'connect_sql.php';
		
		$songID = $_GET ['sid'];
		$userID = $_GET ['uid'];
		
		// if the user had a like , then remove it.
		$sql = "SELECT 1 FROM Dislikes WHERE user_id = '$userID'";
		
		$result = mysql_query ( $sql, $conn );
		
		if (! $result) {
			
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
		}
		
		if (mysql_num_rows ( $result ) == 0) {
			
			$sql = "INSERT INTO Dislikes (song_id, user_id) VALUES('$songID' , '$userID' )";
			
			if (! mysql_query ( $sql, $conn )) {
				
				die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
			}
		} else {
			$sql = "DELETE FROM Dislikes WHERE user_id = '$userID'";
			
			if (! mysql_query ( $sql, $conn )) {
				
				die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
			}
		}
	}
	/**
	 * @param c
	 * @param sid GET
	 * @param uid GET
	 * @param comm POST
	 * 
	 * Adds a comment to a song. Stores the text in Mongo.
	 * 
	 * @return Error or nothing
	 */
	function comment() {
		require_once 'connect_sql.php';
		require_once 'connect_mongo.php';
		
		$songID = $_GET ['sid'];
		$userID = $_GET ['uid'];
		
		$text = $_POST ['comm'];
		// for mongo (store the commentary)
		$comment_collection = $db->comment;
		$commentary = array (
				'text' => $text 
		);
		$comment_collection->insert ( $commentary );
		
		$id = ( string ) $commentary ["_id"];
		
		$query = "INSERT INTO comments ( user_id, song_id, comment_id) VALUES ('$userID', '$songID','$id' );";
		
		if (! mysql_query ( $query, $conn )) {
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
		}
	}
	
	/**
	 * @param cp GET
	 * @param sid GET
	 * @param uid GET
	 * @param photo FILE
	 * 
	 * We accept png , jpg and, bmp.
	 * Store the photo in Mongo.
	 * 
	 * @return Error or nothing
	 */
	function commentPhoto() {
		require_once 'connect_sql.php';
		require_once 'connect_mongo.php';
		
		$songID = $_GET ['sid'];
		$userID = $_GET ['uid'];
		
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
				die ( "{error:'File error'}" );
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
				
				$query = "INSERT INTO comments ( user_id, song_id, comment_id) VALUES ('$userID', '$songID','$id' );";
				
				if (! mysql_query ( $query, $conn )) {
					die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
				}
			}
		}
	}
	/**
	 * @param rc GET
	 * @param cid GET
	 * @param uid GET
	 * Removes the comment
	 * @return Error or nothing
	 */
	function removeComment() {
		require_once 'connect_sql.php';
		require_once 'connect_mongo.php';
		
		$commentID = $_GET ['cid'];
		$userID = $_GET ['uid'];
		
		$query = "SELECT user_id FROM comments WHERE comment_id = '$commentID'";
		$result = mysql_query ( $query );
		
		if (! result) {
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
		}
		
		$result = mysql_fetch_assoc ( $result );
		
		if (! ($result ["user_id"] == $userID)) {
			die ( "{error:'Error: Cannot remove a comment'}" );
		}
		// for mongo (remove the commentary)
		$db->comment->remove ( array (
				'_id' => $commentID 
		) );
		
		$query = "DELETE FROM comments WHERE comment_id = '$commentID';";
		
		if (! mysql_query ( $query, $conn )) {
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
		}
	}
	/**
	 * @param gc GET
	 * @param sid GET
	 * 
	 * Gets all the comments of a song
	 * 
	 * @return List or Error
	 */
	function getComments() {
		require_once 'connect_sql.php';
		require_once 'connect_mongo.php';
		
		$songID = $_GET ['sid'];
		
		$query = "SELECT comment_id FROM comments WHERE song_id = '$songID';";
		
		$result = mysql_query ( $query, $conn );
		
		if (! $result) {
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
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
		$comments = json_encode($comments);
		echo $comments;
	}
	
	/**
	 * @param gl GET
	 * @param sid GET
	 * 
	 * Gets all likes from a song .
	 * 
	 * @return int or error
	 */
	function getLikes() {
		require_once 'connect_sql.php';
		
		$songID = $_GET ['sid'];
		
		$sql = "SELECT COUNT(user_id) AS c FROM Likes WHERE song_id = '$songID'";
		
		$result = mysql_query ( $sql, $conn );
		if (! $result) {
			
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
		}
		
		$count = mysql_fetch_assoc ( $result ) ['c'];
		
		echo "{count:'$count'}";
	}
	/**
	 * @param gdl GET
	 * @param sid GET
	 *
	 * Gets all dislikes from a song .
	 *
	 * @return int or error
	 */
	function getDislikes() {
		require_once 'connect_sql.php';
		
		$songID = $_GET ['sid'];
		
		$sql = "SELECT COUNT(user_id) AS c FROM Dislikes WHERE song_id = '$songID'";
		
		$result = mysql_query ( $sql, $conn );
		if (! $result) {
			
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
		}
		
		$count = mysql_fetch_assoc ( $result ) ['c'];
		
		echo "{count:'$count'}";
	}
	/**
	 * @param gf GET
	 * @param uid GET
	 * Gets a list of all friends
	 * @return Error or list 
	 */
	function getFriends() {
		require_once "connect_sql.php";
		
		$userID = $_GET ['uid'];
		$query = "SELECT ID, NAME, LASTNAME, GENRE, POPULARITY FROM users LEFT OUTER JOIN friends_relation ON friends_id = ID WHERE user_id = '$userID'";
		
		$result = mysql_query ( $query );
		
		if (! $result) {
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
		}
		
		$eresult = json_encode ( $result );
		echo $result;
	}
	
	/**
	 * @param gfc GET
	 * @param uid GET
	 * @return int or error
	 */
	function getFriendCount() {
		require_once "connect_sql.php";
		
		$userID = $_GET ["uid"];
		
		$query = "SELECT COUNT(friends_id) AS c FROM friends_relation WHERE user_id = '$userID'";
		
		$result = mysql_query ( $query, $conn );
		
		if (! $result) {
			
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );e($result);
		}
		
		$count = mysql_fetch_assoc ( $result ) ['c'];
		
		echo "{count:'$count'}";
	}
}

if (isset ( $_GET ['Like'] ) && isset ( $_GET ['sid'] ) && isset ( $_GET ['uid'] )) {
	
	$social = new Social ();
	$social->like ();
} 

else if (isset ( $_GET ['Dislike'] ) && isset ( $_GET ['sid'] ) && isset ( $_GET ['uid'] )) {
	
	$social = new Social ();
	$social->dislike ();
} else if (isset ( $_GET ['c'] ) && isset ( $_POST ['comm'] ) && isset ( $_GET ['sid'] ) && isset ( $_GET ['uid'] )) {
	
	$social = new Social ();
	$social->comment ();
} else if (isset ( $_GET ['cp'] ) && isset ( $_FILES ['file'] ) && isset ( $_GET ['sid'] ) && isset ( $_GET ['uid'] )) {
	
	$social = new Social ();
	$social->commentPhoto ();
} else if (isset ( $_GET ['gl'] ) && (isset ( $_GET ['sid'] ))) {
	
	$social = new Social ();
	$social->getLikes ();

} else if (isset ( $_GET ['gdl'] ) && (isset ( $_GET ['sid'] ))) {
	
	$social = new Social ();
	$social->getDislikes ();
} else if (isset ( $_GET ['rmc'] ) && isset ( $_GET ['cid'] ) && isset ( $_GET ['sid'] ) && isset ( $_GET ['uid'] )) {
	
	$social = new Social ();
	$social->removeComment ();
} else if (isset ( $_GET ['bf'] ) && isset ( $_GET ['uid'] ) && isset ( $_GET ['fid'] )) {
	
	$social = new Social ();
	$social->befriend ();
} else if (isset ( $_GET ['uf'] ) && isset ( $_GET ['uid'] ) && isset ( $_GET ['fid'] )) {
	
	$social = new Social ();
	$social->unfriend ();
} else if (isset ( $_GET ['gc'] ) && isset ( $_GET ['sid'] )) {
	$social = new Social ();
	$social->getComments ();
} else if (isset ( $_GET ['rc'] ) && isset ( $_GET ['cid'] ) && isset ( $_GET ['uid'] )) {
	$social = new Social ();
	$social->removeComment ();
} else if (isset ( $_GET ['gf'] ) && isset ( $_GET ['uid'] )) {
	
	$social = new Social ();
	$social->getFriends ();
} else if (isset ( $_GET ['gfc'] ) && isset ( $_GET ['uid'] )) {
	
	$social = new Social ();
	$social->getFriendCount ();
} else {
	die ( "{error:'Check params.'}" );
}
?>
