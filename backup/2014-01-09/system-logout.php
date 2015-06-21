<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();
	
	session_destroy();

	header("location: index.php");
?>
