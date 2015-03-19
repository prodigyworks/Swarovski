<?php
	require_once("sqlprocesstoarray.php");
	
	$json = new SQLProcessToArray();
	$actionid = $_GET['actionid'];
	$qry = "SELECT roleid AS id, roleid AS name " .
			"FROM {$_SESSION['DB_PREFIX']}applicationactionroles " .
			"WHERE actionid = $actionid " .
			"ORDER BY roleid";
	
	echo json_encode($json->fetch($qry));
?>
