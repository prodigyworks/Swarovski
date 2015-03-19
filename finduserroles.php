<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();
	
	$json = array();
	
	if (isset($_GET['memberid'])) {
		$memberid = $_GET['memberid'];
		$qry = "SELECT DISTINCT A.memberid, A.roleid, 'X' AS login " .
				"FROM {$_SESSION['DB_PREFIX']}userroles A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}roles D " .
				"ON D.roleid = A.roleid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
				"ON B.member_id = A.memberid " .
				"WHERE A.memberid = $memberid " .
				"ORDER BY B.login";
		
	} else {
		$roleid = $_GET['id'];
		$qry = "SELECT DISTINCT A.memberid, B.login, A.roleid " .
				"FROM {$_SESSION['DB_PREFIX']}userroles A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}roles D " .
				"ON D.roleid = A.roleid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
				"ON B.member_id = A.memberid " .
				"WHERE D.id = $roleid " .
				"ORDER BY B.login";
	}
	
	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$line = array(
					"id" => $member['memberid'], 
					"name" => $member['login'],
					"roleid" => $member['roleid']
				);  
			
			array_push($json, $line);
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	echo json_encode($json); 
?>