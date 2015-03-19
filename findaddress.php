<?php
	require_once("sqlprocesstoarray.php");
	
	$json = new SQLProcessToArray();
	$id = $_GET['id'];
	
	$qry = "SELECT * " .
			"FROM {$_SESSION['DB_PREFIX']}customeraddresses " .
			"WHERE customerid = $id";
	
	echo json_encode($json->fetch($qry));
?>