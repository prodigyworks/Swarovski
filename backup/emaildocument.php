<?php
	require_once('system-db.php');
	
	start_db();
	
	error_reporting(0);

	$id = $_POST['id'];
	$emailaddress = $_POST['emailaddress'];
	$subject = $_POST['subject'];
	$body = $_POST['body'];
	
	if(!isset($id)){
	     logError("Please select your image!");
	     
	} else {
		$query = mysql_query("SELECT * FROM {$_SESSION['DB_PREFIX']}documents WHERE id= ". $id);
		$row = mysql_fetch_array($query);
		$content = $row['image'];
		$file = "uploads/despatch_" . uniqid() . ".pdf";
		
		$out = fopen($file, "wb");
		fwrite($out, $content);
		fclose($out);
		
		smtpmailer($emailaddress, "system@optiks.inter-cloud.co.uk", "System Manager", $subject, $body . "<br>". getSiteConfigData()->emailfooter, array($file));
	}
	
	echo json_encode(array("root => 'ok'"));
?> 