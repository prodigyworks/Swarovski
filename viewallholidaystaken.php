<?php 
	require_once("holidaylib.php"); 
	
	$crud = new HolidayCrud();
	$crud->allowAdd = false;
	$crud->allowEdit = false;
	$crud->subapplications = array();
	$crud->sql = 
		getFilteredData(
				"SELECT A.*, " .
				"B.prorataholidayentitlement," .
				"B.firstname, B.lastname, " .
				"(SELECT SUM(D.daystaken) FROM {$_SESSION['DB_PREFIX']}holiday D WHERE YEAR(D.startdate) = YEAR(A.startdate) AND D.memberid = A.memberid) AS daysremaining " .
				"FROM {$_SESSION['DB_PREFIX']}holiday A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
				"ON B.member_id = A.memberid " .
				"WHERE A.acceptedby IS NOT NULL " .
				"AND A.startdate <= CURDATE() "
			);
	
	$crud->run();
?>
