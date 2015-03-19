<?php
	require_once('system-db.php');
	
	start_db();

	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
	
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

	
	
	if (!$link) {
		logError('Failed to connect to server: ' . mysql_error());
	}
	
	
	define ('MAX_FILE_SIZE', 1024 * 500); 
	 
	// make sure it's a genuine file upload
	if (is_uploaded_file($_FILES['image']['tmp_name'])) {
	  // replace any spaces in original filename with underscores
	  $filename = str_replace(' ', '_', $_FILES['image']['name']);
	  // get the MIME type 
	  $mimetype = $_FILES['image']['type'];
	  
	  if ($mimetype == 'image/pjpeg') {
	    $mimetype= 'image/jpeg';
	  }
	  
	  // create an array of permitted MIME types
	  
	  $permitted = array('image/gif', 'image/jpeg', 'image/png', 'image/x-png');
	
	 // upload if file is OK
	 if (in_array($mimetype, $permitted)
	     && $_FILES['image']['size'] > 0
	     && $_FILES['image']['size'] <= MAX_FILE_SIZE) {
	     	
	   switch ($_FILES['image']['error']) {
	     case 0:
	       // get the file contents

	      // Temporary file name stored on the server
	      $tmpName  = $_FILES['image']['tmp_name'];  
	       
	      // Read the file 
	      $fp = fopen($tmpName, 'r');
	      $image = fread($fp, filesize($tmpName));
	      fclose($fp);
      
	       
	       // get the width and height
	       $size = getimagesize($_FILES['image']['tmp_name']);
	       $width = $size[0];
	       $height = $size[1];
	       $binimage = file_get_contents($_FILES['image']['tmp_name']);
	       $image = mysql_real_escape_string($binimage);
	       $filename = $_FILES['image']['name'];
	       $description = $_POST['description'];
	       $callback = $_POST['callback'];
	       
//	       mysql_real_escape_string
			$stmt = mysqli_prepare($link, "INSERT INTO {$_SESSION['DB_PREFIX']}images " .
					"(description, name, mimetype, image, imgwidth, imgheight) " .
					"VALUES " .
					"(?, ?, ?, ?, ?, ?)");
					
			if ( !$stmt) {   
				logError('mysqli error: '.mysqli_error($link)); 
			} 
			
			
			mysqli_stmt_bind_param($stmt, "ssssss", $description, $filename, $mimetype, $binimage, $width, $height);
		   mysqli_stmt_execute($stmt);

    		$imageid = $link->insert_id;

		   
       	header("location: " . $callback . "?imageid=" . $imageid);	
          break;
        case 3:
        case 6:
        case 7:
        case 8:
          $result = "Error uploading $filename. Please try again.";
          break;
        case 4:
          $result = "You didn't select a file to be uploaded.";
      }
    } else {
      	$result = "$filename is either too big or not an image.";
    }
    
    // if the form has been submitted, display result
	if (isset($result)) {
	  echo "<p><strong>$result</strong></p>";
	}
}
	
	       
 ?>