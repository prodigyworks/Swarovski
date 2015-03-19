<?php
	require_once("absencelib.php");
	
	$crud = new AbsenceCrud();
	$crud->allowAdd = false;
	$crud->allowEdit = false;
	$crud->sql = 
		"SELECT A.*, " .
		"B.firstname, B.lastname " .
		"FROM {$_SESSION['DB_PREFIX']}absence A " .
		"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
		"ON B.member_id = A.memberid  " .
		"WHERE A.absencetype = 'Sick'";
	
	$crud->sql = getFilteredData($crud->sql);
	$crud->run();
?>