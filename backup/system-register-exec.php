<?php
	//Include database connection details
	require_once('system-db.php');
	
	start_db();
	initialise_db();
	
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
	$imageid = 0;
	
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
	    
	}	
	
	//Array to store validation errors
	$errmsg_arr = array();
	
	//Validation error flag
	$errflag = false;
	
	//Function to sanitize values received from the form. Prevents SQL injection
	function clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mysql_real_escape_string($str);
	}
	
	//Sanitize the POST values
	$fname = clean($_POST['fname']);
	$lname = clean($_POST['lname']);
	$password = clean($_POST['password']);
	$cpassword = clean($_POST['cpassword']);
	$email = clean($_POST['email']);
	$mobile = clean($_POST['phone']);
	$cemail = clean($_POST['confirmemail']);
	$customerid = $_POST['customerid'];
	$usertype = $_POST['usertype'];
	$warehouseid = null;
	
	if (isset($_POST['warehouseid'])) {
		$warehouseid = $_POST['warehouseid'];
		
		if ($warehouseid == null || warehouseid == "") {
			$warehouseid = null;
		}
	}
	
	//Input Validations
	if($fname == '') {
		$errmsg_arr[] = 'First name missing';
		$errflag = true;
	}
	if($lname == '') {
		$errmsg_arr[] = 'Last name missing';
		$errflag = true;
	}
	
	if (! isset($_GET['id'])) {
		$login = clean($_POST['login']);
		if($login == '') {
			$errmsg_arr[] = 'Login ID missing';
			$errflag = true;
		}
	}
	
	if($password == '') {
		$errmsg_arr[] = 'Password missing';
		$errflag = true;
	}
	
	if($cpassword == '') {
		$errmsg_arr[] = 'Confirm password missing';
		$errflag = true;
	}
	
	if( strcmp($password, $cpassword) != 0 ) {
		$errmsg_arr[] = 'Passwords do not match';
		$errflag = true;
	}
	
	if( strcmp($email, $cemail) != 0 ) {
		$errmsg_arr[] = 'Email addresses do not match';
		$errflag = true;
	}
	
	$matches = null;
	
	$guid = uniqid();
	$memberid = 0;
	//If there are input validations, redirect back to the registration form
	if($errflag) {
		$_SESSION['ERRMSG_ARR'] = $errmsg_arr;
		session_write_close();
		header("location: " . $_SERVER['HTTP_REFERER']);
		exit();
	}
	
	if (! isset($_GET['id'])) {
		//Check for duplicate login ID
		if($login != '') {
			$qry = "SELECT * FROM {$_SESSION['DB_PREFIX']}members WHERE login='$login'";
			$result = mysql_query($qry);
			if($result) {
				if(mysql_num_rows($result) > 0) {
					$errmsg_arr[] = 'Login ID already in use';
					$errflag = true;
				}
				@mysql_free_result($result);
			}
		}
		
		$startdate = convertStringToDate($_POST['startdate']);
		
		//Create INSERT query
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}members " .
				"(firstname, lastname, customerid, warehouseid, login, uepwd, passwd, mobile, email, imageid, initialised, accepted, guid) " .
				"VALUES" .
				"('$fname','$lname', $customerid, $warehouseid, '$login', '$password', '".md5($password)."', '$mobile', '$email', $imageid, 'N', 'Y', '$guid')";
		$result = @mysql_query($qry);
		$memberid = mysql_insert_id();
		
		if (! $result) {
			logError("INSERT INTO {$_SESSION['DB_PREFIX']}members failed:" . mysql_error());
		}
	
		//Create INSERT query
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}userroles(memberid, roleid) VALUES($memberid, '$usertype')";
		$result = @mysql_query($qry);
		
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}userroles(memberid, roleid) VALUES($memberid, 'PUBLIC')";
		$result = @mysql_query($qry);
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}userroles(memberid, roleid) VALUES($memberid, 'USER')";
		$result = @mysql_query($qry);
		
		$url = getSiteConfigData()->domainurl;
		
		sendRoleMessage("SUPERADMIN", "User Registration", "User " . $login . " has registered as a user");
		
		if ($usertype == "ADMIN") {
			sendUserMessage($memberid, "User Registration", getSiteConfigData()->registrationemail . "<br><br>Information you need to know.<br><br>Login : " . $login . "<br>Password : " . $password . "<br>Click to login : $url");
		}
		
		if($result) {
			header("location: system-register-success.php?type=$usertype");
	
		} else {
			logError("Query failed:" . mysql_error());
		}
		
	} else {
		$memberid = $_GET['id'];
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}members " .
				"SET email = '$email', " .
				"mobile = '$mobile', " .
				"firstname = '$fname', " .
				"lastname = '$lname', " .
				"customerid = '$customerid', " .
				"imageid = $imageid, " .
				"uepwd = '$password', " .
				"passwd = '" . md5($password) . "' " .
				"WHERE member_id = " . $_GET['id'];
		$result = @mysql_query($qry);

		if (! $result) {
			logError("UPDATE members failed:" . mysql_error());
		}
		
		sendRoleMessage("SUPERADMIN", "User Amendment", "<h3>User amendment.</h3><br>Your details have been amended by the System Administration.<br>Your password has been changed to: <i>$password</i>.");
		sendUserMessage($memberid, "User Amendment", "<h3>User amendment.</h3><br>Your details have been amended by the System Administration.<br>Your password has been changed to: <i>$password</i>.");
		
		if($result) {
			header("location: system-register-amend.php");
	
		} else {
			logError("Query failed:" . mysql_error());
		}
	}
	
	//Check whether the query was successful or not
?>