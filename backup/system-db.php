<?php
class SiteConfigClass {
	public $domainurl;
	public $emailfooter;
	public $registrationemail;
	public $signatureemail;
	public $welcometext;
	public $lastschedulerun;
	public $runscheduledays;
}

function start_db() {
	if(!isset($_SESSION)) {
		session_start();
	}
	
	date_default_timezone_set('Europe/London'); 
//	error_reporting(E_ALL ^ E_DEPRECATED);

	if (! isset($_SESSION['SYSTEMINI.INI'])) {
		$_SESSION['SYSTEMINI.INI'] = parse_ini_file("systemini.ini");
		$_SESSION['DB_PREFIX'] = $_SESSION['SYSTEMINI.INI']['DB_PREFIX']; 
	}
	
	if (! defined('DB_HOST')) {
		$iniFile = $_SESSION['SYSTEMINI.INI'];
		
		define('DB_HOST', $iniFile['DB_HOST']);
	    define('DB_USER', $iniFile['DB_USER']);
	    define('DB_PASSWORD', $iniFile['DB_PASSWORD']);
	    define('DB_DATABASE', $iniFile['DB_DATABASE']);
	    
		//Connect to mysql server
		$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
		
		if (!$link) {
			logError('Failed to connect to server: ' . mysql_error());
		}
		
		//Select database
		$db = mysql_select_db(DB_DATABASE);
		
		if(!$db) {
			logError("Unable to select database:" . DB_DATABASE);
		}
		
		mysql_query("BEGIN");
	
		if (! isset($_SESSION['SITE_CONFIG'])) {
			$qry = "SELECT * FROM {$_SESSION['DB_PREFIX']}siteconfig";
			$result = mysql_query($qry);
	
			//Check whether the query was successful or not
			if ($result) {
				if (mysql_num_rows($result) == 1) {
					$member = mysql_fetch_assoc($result);
					
					$data = new SiteConfigClass();
					$data->domainurl = $member['domainurl'];
					$data->emailfooter = $member['emailfooter'];
					$data->registrationemail = $member['registrationemail'];
					$data->signatureemail = $member['signatureemail'];
					$data->welcometext = $member['welcometext'];
					$data->lastschedulerun = $member['lastschedulerun'];
					$data->runscheduledays = $member['runscheduledays'];
					
					$_SESSION['SITE_CONFIG'] = $data;
				}
					
			} else {
				header("location: system-access-denied.php");
			}
		}
	    
	}
}

function GetUserName($userid = "") {
	if ($userid == "") {
		return $_SESSION['SESS_FIRST_NAME'] . " " . $_SESSION['SESS_LAST_NAME'];
		
	} else {
		$qry = "SELECT * FROM {$_SESSION['DB_PREFIX']}members A " .
				"WHERE A.member_id = $userid ";
		$result = mysql_query($qry);
		$name = "Unknown";
	
		//Check whether the query was successful or not
		if($result) {
			while (($member = mysql_fetch_assoc($result))) {
				$name = $member['firstname'] . " " . $member['lastname'];
			}
		}
		
		return $name;
	}
}

function getLoggedOnTeamID() {
	$qry = "SELECT teamid FROM {$_SESSION['DB_PREFIX']}members A " .
			"WHERE A.member_id = " . getLoggedOnMemberID();
	$result = mysql_query($qry);
	$teamid = 0;

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$teamid = $member['teamid'];
		}
	}
	
	return $teamid;
}

function getLoggedOnRegionID() {
	$qry = "SELECT B.regionid " .
			"FROM {$_SESSION['DB_PREFIX']}members A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}team B " .
			"ON B.id = A.teamid " .
			"WHERE A.member_id = " . getLoggedOnMemberID();
	$result = mysql_query($qry);
	$regionid = 0;

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$regionid = $member['regionid'];
		}
	}
	
	return $regionid;
}

function GetEmail($userid) {
	$qry = "SELECT email FROM {$_SESSION['DB_PREFIX']}members A " .
			"WHERE A.member_id = $userid ";
	$result = mysql_query($qry);
	$name = "Unknown";

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$name = $member['email'];
		}
	}
	
	return $name;
}

function GetSiteName($siteid) {
	$qry = "SELECT * FROM {$_SESSION['DB_PREFIX']}sites A " .
			"WHERE A.id = $siteid ";
	$result = mysql_query($qry);
	$name = "Unknown";

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$name = $member['name'];
		}
	}
	
	return $name;
}


function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")  
{ 
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue; 
 
  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue); 
 
  switch ($theType) { 
    case "text": 
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL"; 
      break;     
    case "long": 
    case "int": 
      $theValue = ($theValue != "") ? intval($theValue) : "NULL"; 
      break; 
    case "double": 
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL"; 
      break; 
    case "date": 
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL"; 
      break; 
    case "defined": 
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue; 
      break; 
  } 
  return $theValue; 
} 

function initialise_db() {
}
	
function dateStampString($oldnotes, $newnotes, $prefix = "") {
	if ($newnotes == $oldnotes) {
		return $oldnotes;
	}
	
	return 
		mysql_escape_string (
				$oldnotes . "\n\n" .
				$prefix . " - " . 
				date("F j, Y, g:i a") . " : " . 
				$_SESSION['SESS_FIRST_NAME'] . " " . 
				$_SESSION['SESS_LAST_NAME'] . "\n" . 
				$newnotes
			);
}
	

function smtpmailer6($to, $from, $from_name, $subject, $body, $attachments = array()) { 
	global $error;
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
	// Additional headers
	$headers .= "To: <$to>" . "\r\n";
	$headers .= "From: $from_name <$from>" . "\r\n";
	
	mail(
			$to,
			$subject,
			$body,
			$headers
		);
}
	
function smtpmailer5($to, $from, $from_name, $subject, $body, $attachments = array()) { 
	global $error;
	
	
	
	
	
	
	
	
	
//define the receiver of the email 
//define the subject of the email 
//create a boundary string. It must be unique 
//so we use the MD5 algorithm to generate a random hash 
$random_hash = md5(date('r', time())); 
//define the headers we want passed. Note that they are separated with \r\n 
$headers = "From: $from_name <$from>\r\nReply-To: $from"; 
//add boundary string and mime type specification 
$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\""; 
//read the atachment file contents into a string,
//encode it with MIME base64,
//and split it into smaller chunks
//define the body of the message. 
ob_start(); //Turn on output buffering 
?> 
--PHP-mixed-<?php echo $random_hash; ?>  
Content-Type: multipart/alternative; boundary="PHP-alt-<?php echo $random_hash; ?>" 

--PHP-alt-<?php echo $random_hash; ?>  
Content-Type: text/html; charset="iso-8859-1" 
Content-Transfer-Encoding: 7bit

<?php echo $body; ?>

--PHP-alt-<?php echo $random_hash; ?>-- 

--PHP-mixed-<?php echo $random_hash; ?>  
<?php
		for($x=0;$x<count($attachments);$x++){
			$file = fopen($attachments[$x],"rb");
			$data = fread($file,filesize($attachments[$x]));
			fclose($file);
//$attachment = chunk_split(base64_encode(file_get_contents($attachments[$x]))); 
?>
Content-Type: application/octet-stream; name="<?php echo basename($attachments[$x]); ?>"  
Content-Transfer-Encoding: base64  
Content-Disposition: attachment;

<?php echo base64_encode($data); ?> 
--PHP-mixed-<?php echo $random_hash; ?>-- 
<?php
		}
?>

<?php 
//copy current buffer contents into $message variable and delete current output buffer 
$message = ob_get_clean(); 
//send the email 
$mail_sent = @mail( $to, $subject, $message, $headers ); 

}

function smtpmailer4($to, $from, $from_name, $subject, $body, $attachments = array()) { 
	require_once('phpmailer/class.phpmailer.php');
	
	global $error;
	
	$array = explode(',', $to);
	
	$mail = new PHPMailer();  // create a new object
	$mail->IsSMTP(); // enable SMTP
	$mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
	$mail->SMTPAuth = true;  // authentication enabled
	$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
	$mail->Host = 'smtp.gmail.com';
	$mail->Port = 465; 
	$mail->Username = "istudentcontrol@gmail.com";  
	$mail->Password = "istudent";           
	$mail->SetFrom($from, $from_name);
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->AddReplyTo($from, $from_name);
	
	for ($i = 0; $i < count($attachments); $i++) {
		$mail->AddAttachment($attachments[$i]);
	}
	
	for ($i = 0; $i < count($array); $i++) {
		$mail->AddAddress($array[$i]);
	}
	
	if(!$mail->Send()) {
		$error = 'Mail error: '.$mail->ErrorInfo; 
		logError($error, false);
		return false;
		
	} else {
		$error = 'Mail sent'; 
		logError($error, false);
		return true;
	}
}

function smtpmailer($to, $from, $from_name, $subject, $body, $attachments = array()) { 
	require_once('phpmailer/class.phpmailer.php');

	global $error;
	
	$array = explode(',', $to);
	
	
	$mail = new PHPMailer();  // create a new object
	$mail->SetFrom($from, $from_name);
	$mail->IsHTML(true);
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->AddReplyTo($from, $from_name);
	
	for ($i = 0; $i < count($attachments); $i++) {
		$mail->AddAttachment($attachments[$i]);
	}
	
	for ($i = 0; $i < count($array); $i++) {
		$mail->AddAddress($array[$i]);
	}
	
	if(!$mail->Send()) {
		$error = 'Mail error: '.$mail->ErrorInfo; 
		logError($error, false);
		return false;
		
	} else {
		$error = 'Mail sent'; 
		logError($error, false);
		return true;
	}
}

function sendRoleMessage($role, $subject, $message, $attachments = array()) {
	$qry = "SELECT B.email, B.firstname, B.member_id FROM {$_SESSION['DB_PREFIX']}userroles A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
			"ON B.member_id = A.memberid " .
			"WHERE A.roleid = '$role' ";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer($member['email'], 'admin@optiks.inter-cloud.co.uk', 'Swarovski Administration', $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(), $attachments);
			
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
			
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}messages " .
					"(from_member_id, to_member_id, subject, message, createddate, status) " .
					"VALUES " .
					"(" . "1" . ", ". $member['member_id'] . ", '$subject', '$message', NOW(), 'N') ";
			
			if (! mysql_query($qry)) {
				logError($qry . " - " . mysql_error());
			}
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if (!empty($error)) echo $error;
}

function sendCustomerMessage($customerid, $subject, $message, $attachments = array()) {
	$qry = "SELECT B.email, B.firstname, B.member_id FROM {$_SESSION['DB_PREFIX']}members B " .
			"WHERE B.customerid = $customerid";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
			
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}messages " .
					"(from_member_id, to_member_id, subject, message, createddate, status) " .
					"VALUES " .
					"(" . "1" . ", ". $member['member_id'] . ", '$subject', '$message', NOW(), 'N') ";
			
			if (! mysql_query($qry)) {
				logError($qry . " - " . mysql_error());
			}
			
			smtpmailer($member['email'], 'admin@optiks.inter-cloud.co.uk', 'Swarovski Administration', $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(), $attachments);
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if (!empty($error)) echo $error;
}


function sendInternalRoleMessage($role, $subject, $message, $attachments = array()) {
	$from = "info@optiks.inter-cloud.co.uk";
	$fromName = "Swarovski Administration";
	$qry = "SELECT B.email, B.firstname, B.lastname FROM {$_SESSION['DB_PREFIX']}members B " .
			"WHERE B.member_id = " . getLoggedOnMemberID();
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$from = $member['email'];
			$fromName = $member['firstname'] . " " . $member['lastname'];
		}
	}

	$qry = "SELECT B.email, B.firstname, B.member_id FROM {$_SESSION['DB_PREFIX']}userroles A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
			"ON B.member_id = A.memberid " .
			"WHERE A.roleid = '$role' ";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer($member['email'], $from, $fromName, $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(), $attachments);
			
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
			
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}messages " .
					"(from_member_id, to_member_id, subject, message, createddate, status) " .
					"VALUES " .
					"(" . "1" . ", ". $member['member_id'] . ", '$subject', '$message', NOW(), 'N') ";
			
			if (! mysql_query($qry)) {
				logError($qry . " - " . mysql_error());
			}
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if (!empty($error)) echo $error;
}
	
function endsWith( $str, $sub ) {
	return ( substr( $str, strlen( $str ) - strlen( $sub ) ) == $sub );
}

function isAuthenticated() {
	return ! (!isset($_SESSION['SESS_MEMBER_ID']) || (trim($_SESSION['SESS_MEMBER_ID']) == ''));
}

function sendUserMessage($id, $subject, $message, $footer = "", $attachments = array()) {
	$qry = "SELECT B.email, B.firstname FROM {$_SESSION['DB_PREFIX']}members B " .
			"WHERE B.member_id = $id ";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer($member['email'], 'admin@optiks.inter-cloud.co.uk', 'Swarovski Administration', $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(). $footer, $attachments);
			
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
			
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}messages " .
					"(from_member_id, to_member_id, subject, message, createddate, status) " .
					"VALUES " .
					"(" . "1" . ", ". $id . ", '$subject', '$message', NOW(), 'N') ";
			
			if (! mysql_query($qry)) {
				logError($qry . " - " . mysql_error());
			}
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if (!empty($error)) echo $error;
}

function sendInternalUserMessage($id, $subject, $message, $footer = "", $attachments = array()) {
	$from = "admin@optiks.inter-cloud.co.uk";
	$fromName = "Swarovski Administration";
	$qry = "SELECT B.email, B.firstname, B.lastname FROM {$_SESSION['DB_PREFIX']}members B " .
			"WHERE B.member_id = " . getLoggedOnMemberID();
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$from = $member['email'];
			$fromName = $member['firstname'] . " " . $member['lastname'];
		}
	}

	$qry = "SELECT B.email, B.firstname FROM {$_SESSION['DB_PREFIX']}members B " .
			"WHERE B.member_id = $id ";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			smtpmailer($member['email'], $from, $fromName, $subject, getEmailHeader() . "<h4>Dear " . $member['firstname'] . ",</h4><p>" . $message . "</p>" . getEmailFooter(). $footer, $attachments);
			
			$subject = mysql_escape_string($subject);
			$message = mysql_escape_string($message);
			
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}messages " .
					"(from_member_id, to_member_id, subject, message, createddate, status) " .
					"VALUES " .
					"(" . "1" . ", ". $id . ", '$subject', '$message', NOW(), 'N') ";
			
			if (! mysql_query($qry)) {
				logError($qry . " - " . mysql_error());
			}
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if (!empty($error)) echo $error;
}

function createCombo($id, $value, $name, $table, $where = " ", $required = true, $isarray = false, $attributeArray = array()) {
	echo "<select required='" . $required . "' id='" . $id . "' ";
	
	foreach ($attributeArray as $i => $val) {
	    echo "$i='$val' ";
	}
	
	if (! $isarray) {
		echo "name='" . $id . "'>";

	} else {
		echo "name='" . $id . "[]'>";
	}
	
	createComboOptions($value, $name, $table, $where);
	
	echo "</select>";
}
	


function createComboOptions($value, $name, $table, $where = " ", $blank = true) {
	if ($blank) {
		echo "<option value='0'></option>";
	}
		
	$qry = "SELECT A.* " .
			"FROM $table A " .
			$where . " " . 
			"ORDER BY A.$name";
	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			echo "<option value=" . $member[$value] . ">" . $member[$name] . "</option>";
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
}
	
function escape_notes($notes) {
	return str_replace("\r", "", str_replace("'", "\\'", str_replace("\n", "\\n", str_replace("\"", "\\\"", str_replace("\\", "\\\\", $notes)))));
}

function isUserAccessPermitted($action, $description = "") {
	require_once("constants.php");
	
	if ($description == "") {
		$desc = ActionConstants::getActionDescription($action);
		
	} else {
		$desc = $description;
	}
	
	$pageid = $_SESSION['pageid'];
	$found = 0;
	$actionid = 0;
	$qry = "SELECT A.id " .
			"FROM {$_SESSION['DB_PREFIX']}applicationactions A  " .
			"WHERE A.pageid = $pageid " .
			"AND A.code = '$action'";
	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$found = 1;
			$actionid = $member['id'];
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
	
	if ($found == 0) {
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}applicationactions (pageid, code, description) VALUES($pageid, '$action', '$desc')";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
		
		$actionid = mysql_insert_id();
		
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}applicationactionroles (actionid, roleid) VALUES($actionid, 'PUBLIC')";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
	}
	
	$found = 0;
	$qry = "SELECT A.* " .
			"FROM {$_SESSION['DB_PREFIX']}applicationactionroles A  " .
			"WHERE A.actionid = $actionid " .
			"AND A.roleid IN (" . ArrayToInClause($_SESSION['ROLES']) . ")";
	$result = mysql_query($qry);

	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			$found = 1;
		}
		
	} else {
		logError($qry . " - " . mysql_error());
	}
		
	return $found == 1;
}
    
function isUserInRole($roleid) {
	for ($i = 0; $i < count($_SESSION['ROLES']); $i++) {
		if ($roleid == $_SESSION['ROLES'][$i]) {
			return true;
		}
	}
	
	return false;
}

function lastIndexOf($string, $item) {
	$index = strpos(strrev($string), strrev($item));

	if ($index) {
		$index = strlen($string) - strlen($item) - $index;
		
		return $index;
		
	} else {
		return -1;
	}
}

function getSiteConfigData() {
	return $_SESSION['SITE_CONFIG'];
}

function redirectWithoutRole($role, $location) {
	start_db();
	initialise_db();
	
	if (! isUserInRole($role)) {
		header("location: $location");
	}
}

function getEmailHeader() {
	return "<img src='" . getSiteConfigData()->domainurl . "/images/logo.png' />";
}

function getEmailFooter() {
	return getSiteConfigData()->emailfooter;
}

function getLoggedOnMemberID() {
	start_db();
	
	if (! isset($_SESSION['SESS_MEMBER_ID'])) {
		return 0;
	}
	
	return $_SESSION['SESS_MEMBER_ID'];
}

function authenticate() {
	start_db();
	initialise_db();
	
	if (! isAuthenticated()) {
		header("location: system-login.php?callback=" . base64_encode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING']));
		exit();
	}
}

function networkdays($s, $e, $holidays = array()) {
    // If the start and end dates are given in the wrong order, flip them.    
    if ($s > $e)
        return networkdays($e, $s, $holidays);

    // Find the ISO-8601 day of the week for the two dates.
    $sd = date("N", $s);
    $ed = date("N", $e);

    // Find the number of weeks between the dates.
    $w = floor(($e - $s)/(86400*7));    # Divide the difference in the two times by seven days to get the number of weeks.
    if ($ed >= $sd) { $w--; }        # If the end date falls on the same day of the week or a later day of the week than the start date, subtract a week.

    // Calculate net working days.
    $nwd = max(6 - $sd, 0);    # If the start day is Saturday or Sunday, add zero, otherewise add six minus the weekday number.
    $nwd += min($ed, 5);    # If the end day is Saturday or Sunday, add five, otherwise add the weekday number.
    $nwd += $w * 5;        # Add five days for each week in between.

    // Iterate through the array of holidays. For each holiday between the start and end dates that isn't a Saturday or a Sunday, remove one day.
    foreach ($holidays as $h) {
        $h = strtotime($h);
        if ($h > $s && $h < $e && date("N", $h) < 6)
            $nwd--;
    }

    return $nwd;
}

function logError($description, $kill = true) {
	if ($kill) {
		mysql_query("ROLLBACK");
	}
	
	$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}errors (pageid, memberid, description) VALUES (" . $_SESSION['pageid'] . ", " . getLoggedOnMemberID() . ", '" . mysql_escape_string($description) . "')";
	$result = mysql_query($qry);
	
	if ($kill) {
		die($description);
	}
}

function convertStringToDate($str) {
	return substr($str, 6, 4 ) . "-" . substr($str, 3, 2 ) . "-" . substr($str, 0, 2 );
}

?>