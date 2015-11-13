<?php
/**
 * 
 * @author zyoruk
 * User class.
 */
class User {
	/**
	 * @param up GET 
	 * @param uid which is the user id that was returned when logged in. GET
	 * @param file which is the photo to be uploaded FILE
	 * It will connect to mongo and store a photo using GridFS. Extra metadata added.
	 * type and owner.
	 * @return Error or nothing.
	 */
	function uploadPhoto() {
		require_once "connect_mongo.php";
		
		$user = $_GET ["uid"];
		
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
				$connection->close ();
				die ( 'Error' );
			} else {
				
				// for mongo (store the photo)
				$filename = $_FILES ['file'] ['name'];
				$temp = $_FILES ['file'] ['tmp_name'];
				$grid = $db->getGridFS ();
				// if a photo already exists, remove the old one.
				$old_photo = $grid->findOne ( array (
						"owner" => $user,
						"type" => "image" 
				) );
				
				if ($old_photo != NULL) {
					$old_photo->remove ();
				}
				
				$storedFile = $grid->storeFile ( $temp );
				$image = $grid->findOne ( array (
						"_id" => $storedFile 
				) );
				$image->file ['filename'] = $filename;
				$image->file ['owner'] = $user;
				$image->file ['type'] = "image";
				$grid->save ( $image->file );
				$connection->close ();
			}
		} else {
			$connection->close ();
			die ( "Error" );
		}
	}
	
	/**
	 * @param dp GET
	 * @param uid  GET 
	 * It will display a window to download the photo.
	 * @return image or Error
	 */
	function downloadPhoto() {
		require_once 'connect_mongo.php';
		
		// mongo
		$user = $_GET ['uid'];
		$grid = $db->getGridFS ();
		$photo = $grid->findOne ( array (
				"owner" => $user,
				"type" => "image" 
		) );
		
		header ( 'Content-Description: File Transfer' );
		header ( 'Content-Type: application/octet-stream' );
		header ( 'Content-Disposition: attachment; filename="user_photo"' );
		header ( 'Expires: 0' );
		header ( 'Cache-Control: must-revalidate' );
		header ( 'Pragma: public' );
		header ( 'Content-Length: ' . $photo->file ['length'] );
		
		echo $photo->getBytes ();
		
		$connection->close ();
		exit ();
	}
	
	/**
	 * @param gp GET 
	 * @param uid GET
	 * @return image or Error
	 */
	function getPhoto() {
		require_once 'connect_mongo.php';
		
		$owner = $_GET ['uid'];
		$grid = $db->getGridFS ();
		$song = $grid->findOne ( array (
				"owner" => $owner,
				"type" => "image" 
		) );
		
		echo $song->getBytes ();
		
		$connection->close ();
	}
	
	/**
	 * @param rp GET
	 * @param uid GET
	 * Removes the photo of the user.
	 * @return Error or nothing 
	 */
	function removePhoto() {
		require_once 'connect_mongo.php';
		
		$owner = $_GET ['uid'];
		$grid = $db->getGridFS ();
		$song = $grid->remove ( array (
				"owner" => $owner,
				"type" => "image" 
		) );
		
		$connection->close ();
	}
	/**
	 * @param cui GET
	 * @param uid GET
	 * @param name POST
	 * @param lastname POST
	 * 
	 * Expected name , lastname or both .
	 * 
	 *  @return Error or nothing
	 */
	function changeUserInfo() {
		require_once "connect_sql.php";
		
		$userID = $_GET ["uid"];
		$name = $_POST ["name"];
		$lastname = $_POST ["lastname"];
		
		$query = "UPDATE users SET ";
		$multiple = False;
		
		if ($name != "") {
			if ($multiple === True) {
				$query = $query . ", ";
			}
			
			$query = $query . "NAME = '$name'";
			$multiple = True;
		}
		
		if ($lastname != "") {
			if ($multiple === True) {
				$query = $query . ", ";
			}
			
			$query = $query . "LASTNAME = '$lastname'";
			$multiple = True;
		}
		
		$query = $query . "WHERE ID = '$userID'";
		
		if (! mysql_query ( $query )) {
			die ( "Error description: " . mysql_error ( $conn ) );
		}
	}
}

$user = new User ();

if (isset ( $_REQUEST ['up'] ) && isset ( $_FILES ["file"] ) && isset ( $_REQUEST ["uid"] )) {
	$user->uploadPhoto ();
} else if (isset ( $_REQUEST ['dp'] ) && isset ( $_REQUEST ['uid'] )) {
	$user->downloadPhoto ();
} else if (isset ( $_REQUEST ['gp'] ) && isset ( $_REQUEST ['uid'] )) {
	$user->getPhoto ();
} else if (isset ( $_REQUEST ['rp'] ) && isset ( $_REQUEST ['uid'] )) {
	$user->removePhoto ();
} else if (isset ( $_REQUEST ['cui'] ) && isset ( $_REQUEST ['uid'] ) && (isset ( $_REQUEST ['name'] ) || isset ( $_REQUEST ['lastname'] ))) {
	$user->changeUserInfo ();
} else {
	die ( "Woops" );
}
?>