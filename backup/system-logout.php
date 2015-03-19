<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();
	
	unset($_SESSION['SESS_MEMBER_ID']);
	unset($_SESSION['SESS_FIRST_NAME']);
	unset($_SESSION['SESS_LAST_NAME']);
	unset($_SESSION['ROLES']);
	unset($_SESSION['MENU_CACHE']);
	unset($_SESSION['breadcrumb']);
	unset($_SESSION['breadcrumbPage']);
	unset($_SESSION['SYSTEMINI.INI']);
	unset($_SESSION['DB_PREFIX']);
	unset($_SESSION['CUSTOMER_ID']);
	unset($_SESSION['WAREHOUSE_ID']);
	
	header("location: index.php");
?>
