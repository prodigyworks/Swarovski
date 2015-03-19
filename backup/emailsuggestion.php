<?php
	require_once('system-db.php');
	
	start_db();
	
	error_reporting(0);

	$body = GetUserName() . " has suggested the following:<br><br>";
	$body .= $_POST['body'];
	
	
	smtpmailer("john.cove@jacdigital.co.uk", "system@optiks.inter-cloud.co.uk", "System Manager", "Suggestion", $body . "<br>". getSiteConfigData()->emailfooter);
	smtpmailer("kevin.hilton@prodigyworks.co.uk", "system@optiks.inter-cloud.co.uk", "System Manager", "Suggestion", $body . "<br>". getSiteConfigData()->emailfooter);
	
	echo json_encode(array("root => 'ok'"));
?> 