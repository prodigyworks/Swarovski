<?php
	require_once('system-db.php');
	
	start_db();

	$id = $_GET['id'];
	
	if(!isset($id)){
	     logError("Please select your image!");
	     
	} else {
		$query = mysql_query("SELECT * FROM {$_SESSION['DB_PREFIX']}documents WHERE id= ". $id);
		$row = mysql_fetch_array($query);
		$content = $row['image'];
		
//		header("Content-disposition: attachment; filename=Booking.pdf");
//		$expires = 60*60*24*14;
//		header("Pragma: public");
//		header("Cache-Control: maxage=".$expires);
//		header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
  		header("Content-type: " . $row['mimetype']);
//		header("Pragma: ");
//		header("Content-length: ". strlen($content));
		
	    echo $content;
	}
?> 