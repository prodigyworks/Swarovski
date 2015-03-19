<?php
   	sendRoleMessage("ALERT", "Daily alert task schedule", "Information: Alerts task schedule run at " . date("d/m/Y"));
   	
	$qry = "SELECT DATE_FORMAT(A.expectedreturndate, '%d/%m/%Y') AS expectedreturndate, B.name, C.serialnumber, D.name AS stockname " .
			"FROM {$_SESSION['DB_PREFIX']}despatchitem A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}despatchheader AA " .
			"ON AA.id = A.despatchid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}customers B " .
			"ON B.id = AA.customerid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}stockitem C " .
			"ON C.id = A.stockitemid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}stock D " .
			"ON D.id = C.stockid " .
			"WHERE A.expectedreturndate < (DATE_ADD(CURDATE(), INTERVAL -7 DAY)) " .
			"AND AA.despatched  = 'Y' " .
			"AND AA.checkedindate IS NULL";
			
	
	$result = mysql_query($qry);
	
	//Check whether the query was successful or not
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$details = "<p>Despatch from customer: " . $member['name'] . " is overdue, was expected back on " . $member['expectedreturndate'] . "</p>";
			$details .= "<p>Stock : " . $member['stockname'] . "</p>";
			$details .= "<p>Serial : " . $member['serialnumber'] . "</p>";
			
	    	sendRoleMessage("ALERT", "Daily Alert", $details);
		}
		
	} else {
		logError($qry . mysql_error());
	}
	
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}siteconfig SET lastschedulerun = CURDATE()";
	$result = mysql_query($qry);
	
	if (! $result) logError("Error: " . mysql_error());
   	
	$qry = "SELECT AA.memberid, DATE_FORMAT(A.expectedreturndate, '%d/%m/%Y') AS expectedreturndate, B.name, C.serialnumber, D.name AS stockname " .
			"FROM {$_SESSION['DB_PREFIX']}despatchitem A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}despatchheader AA " .
			"ON AA.id = A.despatchid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}customers B " .
			"ON B.id = AA.customerid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}stockitem C " .
			"ON C.id = A.stockitemid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}stock D " .
			"ON D.id = C.stockid " .
			"WHERE A.expectedreturndate <= (DATE_ADD(CURDATE(), INTERVAL 5 DAY)) " .
			"AND  A.expectedreturndate >= CURDATE() " .
			"AND AA.despatched  = 'Y' " .
			"AND AA.checkedindate IS NULL";
	
	$result = mysql_query($qry);
	
	//Check whether the query was successful or not
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$details = "<p>Despatch from customer: " . $member['name'] . " is expected back on " . $member['expectedreturndate'] . ", please chase up.</p>";
			$details .= "<p>Stock : " . $member['stockname'] . "</p>";
			$details .= "<p>Serial : " . $member['serialnumber'] . "</p>";
			
	    	sendUserMessage($member['memberid'], "Expected stock return", $details);
		}
		
	} else {
		logError($qry . mysql_error());
	}
	
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}siteconfig SET lastschedulerun = CURDATE()";
	$result = mysql_query($qry);
	
	if (! $result) logError("Error: " . mysql_error());
?>