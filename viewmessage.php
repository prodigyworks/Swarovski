<?php
	//Include database connection details
	require_once("sqlprocesstoarray.php");
	
	$json = new SQLProcessToArray();
	$id = $_POST['id'];
	
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}messages SET status = 'R' WHERE id = $id";
	$result = mysql_query($qry);
	
	
	$qry = "SELECT COUNT(*) AS messages " .
			"FROM {$_SESSION['DB_PREFIX']}messages A " .
			"WHERE A.to_member_id = " . getLoggedOnMemberID() . " " .
			"AND status = 'N'";

	echo json_encode($json->fetch($qry));
	
?>