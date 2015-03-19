<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();

	$json = array();
	$qry = ""; 
	
	if (isset($_POST['login'])) {
		$login = $_POST['login'];
		$qry = "SELECT A.*  " .
				"FROM {$_SESSION['DB_PREFIX']}members A " .
				"WHERE A.login = '$login'";
				
	} else if (isset($_POST['member_id'])) {
		$memberid = $_POST['member_id'];
		$qry = "SELECT A.* " .
				"FROM {$_SESSION['DB_PREFIX']}members A " .
				"WHERE A.member_id = $memberid";
	}

	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$line = array(
					"member_id" => $member['member_id'], 
					"firstname" => $member['firstname'], 
					"lastname" => $member['lastname'], 
					"email" => $member['email']
				);  
			
			array_push($json, $line);
		}
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	echo json_encode($json); 
?>