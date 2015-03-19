<?php
	require_once("system-db.php");
	
	start_db();
	initialise_db();
	
	$errmsg_arr = null;
	
	if (! isset($_POST['login']) || $_POST['login'] == "") {
		$errmsg_arr[] = "Missing User ID";
		
	} else {
		$word = "";
		$memberid = 0;
		$login = $_POST['login'];
		
		$_SESSION['ERR_USER'] = $_POST['login'];
		
		$qry = "SELECT * FROM {$_SESSION['DB_PREFIX']}members WHERE login='$login'";
		$result=mysql_query($qry);
		
		//Check whether the query was successful or not
		if($result) {
			if(mysql_num_rows($result) == 1) {
				$member = mysql_fetch_assoc($result);
				$memberid = $member['member_id'];
				
				srand(time());
				
				for ($i = 0; $i < 10; $i++) {
					$random = (rand()%52);
					
					if ($random > 26) {
						$random = $random - 26;
						$random = $random + 32;
					}
					
					$word = $word . chr($random + 65);
				}
				
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}members " .
						"SET passwd = '" . md5($word) . "' " .
						"WHERE member_id = $memberid";
				$result = mysql_query($qry);
				
			   	if (! $result) {
			   		logError("Error RESET PASSWORD:" . $qry . " - " . mysql_error());
			   	}
			   	
				$errmsg_arr[] = "An email has been sent with a reset password.";
				
				sendUserMessage(
						$memberid, 
						"Password reset", 
						"Your password has been reset to $word.<br>Please contact your system administrator if you have any problems."
					);
				
				sendRoleMessage(
						"SUPERADMIN", 
						"Password reset", 
						"User $login has had the password reset to $word."
					);
				
			} else {
				$errmsg_arr[] = "Invalid user.";
			}
			
		} else {
			$errmsg_arr[] = "Invalid user.";
		}
	}
	
	$_SESSION['ERRMSG_ARR'] = $errmsg_arr;
	
	header("location: passwordchanged.php");
?>