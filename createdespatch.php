<?php
	require_once('system-db.php');
	
	start_db();
	
	$id = $_GET['id'];
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}despatchheader SET " .
			"despatched = 'Y' " .
			"WHERE id = $id";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " - " . mysql_error());
	}
	
	header("location: despatchreport.php?id=$id");
?>