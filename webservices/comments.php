<?php

require_once 'connect_sql.php';
require_once 'connect_mongo.php';

if (isset ( $_POST['Commentary']) && isset($_REQUEST['songid']) && isset ($_REQUEST['uid']))
{
	$songID = $_POST['songid'];
	$userID = $_POST['uid'];
	
	$text = $_POST['Commentary'];
	// for mongo (store the commentary)
	$comment_collection = $db->comment;
	$commentary = array ('text' => $text);
	$id = $comment_collection->insert($commentary);
	
	$id = (string) $id;
	$mysql = "INSERT INTO comments ( user_id, song_id, comment_id) VALUES ('$userID', '$songID','$id' );";
	if (! mysql_query ( $mysql, $conn )) {
		die ( "Error description: " . mysql_error ( $conn ) );
	}
}
?>
