<?php
	include("system-header.php"); 
	
	if (isset($_POST['user'])) {
		$guid = $_GET['key'];
		$login = $_POST['user'];
		$passwd = md5($_POST['password']) ;
		$qry = "SELECT * " .
				"FROM {$_SESSION['DB_PREFIX']}members " .
				"WHERE accepted = 'N' " .
				"AND login = '$login' " .
				"AND passwd = '$passwd' " .
				"AND guid = '$guid'";
		$result = mysql_query($qry);
		
		if ($result) {
			while (($member = mysql_fetch_assoc($result))) {
				$memberid = $member['member_id'];
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}members " .
					   "SET accepted = 'Y' " .
					   "WHERE member_id = $memberid";
				$itemresult = mysql_query($qry);
				
				if (! $itemresult) {
					logError($qry . " = " . mysql_error());
				}
				
				sendUserMessage($memberid, "User Registration", "Welcome to Oracle logs.<br>Your user registration has been accepted.");
				
				echo "<h4>Welcome to Oracle logs.<br>Your user registration has been accepted.</h4>";
			}
		}
	} else {
?>
<form method="POST" id="activateform" name="activateform" class="entryform">
	<table>
		<tr>
			<td>Login</td>
			<td>
				<input required="true" type="text" id="user" name="user" />
			</td>
		</tr>
		<tr>
			<td>Password</td>
			<td>
				<input required="true" type="password" id="password" name="password" />
			</td>
		</tr>
		<tr>
			<td>   	
				<span class="wrapper"><a class='link1' href="javascript:if (verifyStandardForm('#activateform')) $('#activateform').submit();"><em><b>Activate</b></em></a></span>
			</td>
			
		</tr>
	</table>
</form>
<?php
	}

	include("system-footer.php"); 
?>