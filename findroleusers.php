<?php
	require_once("sqlprocesstoarray.php");
	
	$json = new SQLProcessToArray();
	$memberid = $_GET['memberid'];
	$qry = "SELECT roleid AS id, roleid AS name " .
			"FROM {$_SESSION['DB_PREFIX']}userroles " .
			"WHERE memberid = $memberid " .
			"ORDER BY roleid";
	
	echo json_encode($json->fetch($qry));
?>