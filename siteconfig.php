<?php 
	require_once("system-header.php"); 
?>

<!--  Start of content -->
<?php
	if (isset($_POST['domainurl'])) {
		$domainurl = mysql_escape_string($_POST['domainurl']) ;
		$welcometext = mysql_escape_string($_POST['welcometext']);
		$emailfooter = mysql_escape_string($_POST['emailfooter']);
		$registrationemail = mysql_escape_string($_POST['registrationemail']);
		$loadextensionemail = mysql_escape_string($_POST['loadextensionemail']);
		$signatureemail = mysql_escape_string(str_replace("\r\n", "", $_POST['signatureemail']));
		$runscheduledays = mysql_escape_string($_POST['runscheduledays']);
		
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}siteconfig SET " .
				"domainurl = '$domainurl', " .
				"welcometext = '$welcometext', " .
				"registrationemail = '$registrationemail', " .
				"loadextensionemail = '$loadextensionemail', " .
				"signatureemail = '$signatureemail', " .
				"runscheduledays = '$runscheduledays', " .
				"emailfooter = '$emailfooter'";
		$result = mysql_query($qry);
		
	   	if (! $result) {
	   		logError("UPDATE {$_SESSION['DB_PREFIX']}siteconfig:" . $qry . " - " . mysql_error());
	   	}
	   	
	   	unset($_SESSION['SITE_CONFIG']);
	}
	
	$qry = "SELECT *, DATE_FORMAT(lastschedulerun, '%d/%m/%Y') AS lastschedulerun FROM {$_SESSION['DB_PREFIX']}siteconfig";
	$result = mysql_query($qry);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
?>
<form id="contentForm" name="contentForm" method="post" class="entryform">
	<label>Domain URL</label>
	<input required="true" type="text" class="textbox90" id="domainurl" name="domainurl" value="<?php echo $member['domainurl']; ?>" />

	<label>Run Alert Schedule Cycle (Days)</label>
	<input required="true" type="text" class="textbox20" id="runscheduledays" name="runscheduledays" value="<?php echo $member['runscheduledays']; ?>" />

	<label>Last Schedule Date Run</label>
	<input readonly type="text" class="textbox20" id="lastschedulerun" name="lastschedulerun" value="<?php echo $member['lastschedulerun']; ?>" />
	
	<label>Welcome Text</label>
	<textarea id="welcometext" name="welcometext" rows="15" cols="60" style="height:340px;width: 340px" class="tinyMCE"></textarea>
	
	<label>E-mail Footer</label>
	<textarea id="emailfooter" name="emailfooter" rows="15" cols="60" style="height:340px;width: 340px" class="tinyMCE"></textarea>
	
	<label>Signature E-mail</label>
	<textarea id="signatureemail" name="signatureemail" rows="15" cols="60" style="height:340px;width: 340px" class="tinyMCE"></textarea>
	
	<label>Registration E-mail</label>
	<textarea id="registrationemail" name="registrationemail" rows="15" cols="60" style="height:340px;width: 340px" class="tinyMCE"></textarea>
	
	<label>Loan Extension E-mail</label>
	<textarea id="loadextensionemail" name="loadextensionemail" rows="15" cols="60" style="height:340px;width: 340px" class="tinyMCE"></textarea>
	
	<br>
	<br>
	<span class="wrapper"><a class='link1' href="javascript:if (verifyStandardForm('#contentForm')) $('#contentForm').submit();"><em><b>Update</b></em></a></span>
</form>
<script type="text/javascript">
	$(document).ready(function() {
			$("#signatureemail").val("<?php echo escape_notes($member['signatureemail']); ?>");
			$("#registrationemail").val("<?php echo escape_notes($member['registrationemail']); ?>");
			$("#loadextensionemail").val("<?php echo escape_notes($member['loadextensionemail']); ?>");
			$("#emailfooter").val("<?php echo escape_notes($member['emailfooter']); ?>");
			$("#welcometext").val("<?php echo escape_notes($member['welcometext']); ?>");
		});
</script>
	<?php
			}
		}
	?>
<!--  End of content -->

<?php include("system-footer.php"); ?>
