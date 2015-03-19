<?php
	//Include database connection details
	require_once('system-config.php');

	login($_POST['login'], $_POST['password']);
?>