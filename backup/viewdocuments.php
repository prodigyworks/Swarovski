<?php
	require_once('system-db.php');
	
	start_db();

	$id = $_GET['id'];
	
	if(!isset($id)){
	     logError("Please select your image!");
	     
	} else {
		$query = mysql_query("SELECT mimetype, image, size FROM {$_SESSION['DB_PREFIX']}documents WHERE id= ". $id);
		$row = mysql_fetch_array($query);
		
		if ($row) {
			$content = $row['image'];
			
			ob_clean(); 
			
			$expires = 60*60*24*14;
			header("Pragma: public");
			header("Cache-Control: maxage=".$expires);
			header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
	  		header("Content-type: " . $row['mimetype']);
	//		header("Content-Length: " . $row['size']);
			
		    echo $content;
		    
		    flush();
		    close();
			ob_end_flush(); 
			
		} else {
			include("system-header.php");
			echo "<h1>Document not found</h1>";
			include("system-footer.php");
		}
	}
?> 