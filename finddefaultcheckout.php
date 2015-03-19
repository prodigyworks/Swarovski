<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();
	
	$json = array();
	
	if (isset($_SESSION['CHECKOUT_CUSTOMER'])) {
		$line = array(
				"address" => $_SESSION['CHECKOUT_ADDRESS'], 
				"cocustomerid" => $_SESSION['CHECKOUT_CUSTOMER'], 
				"reason" => $_SESSION['CHECKOUT_REASON'], 
				"yourref" => $_SESSION['CHECKOUT_YOURREF'], 
				"ourref" => $_SESSION['CHECKOUT_OURREF'], 
				"coexpecteddate" => $_SESSION['CHECKOUT_EXPECTEDDATE']
			);  
		
	} else {
		$line = array(
				"address" => "", 
				"cocustomerid" => "0", 
				"reason" => "", 
				"yourref" => "", 
				"ourref" => "", 
				"coexpecteddate" => date("d/m/Y")
			);  
	}
	
	array_push($json, $line);
	
	echo json_encode($json); 
?>