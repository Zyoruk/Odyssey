<?php

class Admin {
	
	/**
	 * @param gt GET
	 * Get top 100
	 * @return JSON 
	 */
	function getTop100(){
		require_once 'connect_sql.php';
		$query = 
				"SELECT songs.NAME, COUNT(Likes.user_id) AS Likes, COUNT(Dislikes.user_id) AS Dislikes
				FROM Likes
				INNER JOIN Dislikes ON Likes.song_id = Dislikes.song_id
				INNER JOIN songs ON songs.ID = Likes.song_id";
		
		$result = mysql_query($query, $conn);
		
		if (!$result){
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );
		}else{
			$result = mysql_fetch_assoc($result);
			$result = json_encode($result);
			echo $result;
		}
	}

	/**
	 * @param cu GET
	 * return the list of all the connected users.
	 * @return JSON
	 */
	function connectedUsers(){
		require_once 'connect_sql.php';
		$query =
			"SELECT USERNAME
			FROM authentication
			WHERE STATUS = 0";
		
		$result = mysql_query($query, $conn);
		
		if (!$result){
			die ( "{error:'Error description: ".mysql_error($conn)."'}" );
		}else{
			$result = mysql_fetch_assoc($result);
			$result = json_encode($result);
			echo $result;
		}
	}
}

if (isset ($_GET['gtl'])){
	$admin = new Admin();
	$admin->getTop100();
}else if (isset ($_GET['cu'])){
	$admin = new Admin();
	$admin->connectedUsers();
}else{
	die ( "{error:'Check params.'}" );
}

?>