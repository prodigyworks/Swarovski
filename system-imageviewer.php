<?php
	require_once('system-db.php');
	
	start_db();
	initialise_db();

	$id = $_GET['id'];
	
	if(!isset($id)){
	     logError("Please select your image!");
	     
	} else {
		$query = mysql_query("SELECT * FROM {$_SESSION['DB_PREFIX']}images WHERE id= ". $id);
		$row = mysql_fetch_array($query);
		$content = $row['image'];
		
		header('Content-type: ' . $row['mimetype']);
		
	    echo $content;
	}
?> 