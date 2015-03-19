<?php
	require_once("sqlprocesstoarray.php");
	
	$json = new SQLProcessToArray();
	$pageid = $_GET['pageid'];
	$qry = "SELECT roleid AS id, roleid AS name " .
			"FROM {$_SESSION['DB_PREFIX']}pageroles " .
			"WHERE pageid = $pageid " .
			"ORDER BY roleid";
	
	echo json_encode($json->fetch($qry));
?>
