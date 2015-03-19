<?php
	//Include database connection details
	require_once('system-config.php');
	
	login($_GET['login'], $_GET['password']);
?>