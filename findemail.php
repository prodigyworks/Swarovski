<?php
	require_once("sqlprocesstoarray.php");
	
	$json = new SQLProcessToArray();
	$email = $_POST['email'];
	
	if (isset($_POST['login'])) {
		$qry = "SELECT * " .
				"FROM {$_SESSION['DB_PREFIX']}members " .
				"WHERE email = '$email' AND member_id != " . $_POST['login'];
		
	} else {
		$qry = "SELECT * " .
				"FROM {$_SESSION['DB_PREFIX']}members " .
				"WHERE email = '$email'";
	}
	
	echo json_encode($json->fetch($qry));
?>