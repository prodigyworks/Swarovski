<?php 
	include("system-header.php"); 
	showErrors(); 
?>
<h4>Member Profile</h4>
<!--  Start of content -->
<?php
	$memberid =  $_SESSION['SESS_MEMBER_ID'];
	
	if (isset($_GET['id'])) {
		global $memberid;
		
		$memberid = $_GET['id'];
	}
	
	$qry = "SELECT A.* " .
			"FROM {$_SESSION['DB_PREFIX']}members A " .
			"WHERE A.member_id = $memberid ";
	$result = mysql_query($qry);

	//Check whether the query was successful or not
	if($result) {
		while (($member = mysql_fetch_assoc($result))) {
			
			if ($member['imageid'] != 0) {
			
?>
	<img style='position:absolute;margin-left: 800px; top: 105px; max-height: 150px' src='system-imageviewer.php?id=<?php echo $member['imageid']; ?>' />
				
<?php
			}
?>
	<form id="loginForm" class="entryform" enctype="multipart/form-data" name="loginForm" method="post" action="system-register-exec.php?id=<?php echo $memberid; ?>">
	  <table border="0" align="left" cellpadding="2" cellspacing="5" width='100%'>
	    <tr>
	      <td>First Name </td>
	      <td><input required="true" name="fname" type="text" class="textfield" id="fname" value="<?php echo $member['firstname']; ?>" /></td>
	    </tr>
	    <tr>
	      <td>Last Name </td>
	      <td><input required="true" name="lname" type="text" class="textfield" id="lname" value="<?php echo $member['lastname']; ?>" /></td>
	    </tr>
	    <?php
	    if (isUserInRole("ADMIN")) {
	    ?>
	    <tr>
	      <td>Warehouse</td>
	      <td><?php createCombo("warehouseid", "id", "name", "{$_SESSION['DB_PREFIX']}warehouses"); ?></td>
	    </tr>
		<?php
	    } else {
	    ?>
	    <tr>
	      <td>Customer</td>
	      <td><?php createCombo("customerid", "id", "name", "{$_SESSION['DB_PREFIX']}customers"); ?></td>
	    </tr>
		<?php
	    }
	    ?>
	    <tr>
	      <td>Email</td>
	      <td>
	      	<input required="true" name="email" type="text" class="textfield60" id="email"  value="<?php echo $member['email']; ?>" />
	      	<input name="confirmemail" type="hidden" class="textfield60" id="confirmemail" />
	      </td>
	    </tr>
	    <tr>
	      <td>Phone </td>
	      <td><input required="true" name="phone" type="text" class="textfield" id="phone" value="<?php echo $member['mobile']; ?>" /></td>
	    </tr>
	    <tr>
	      <td>Image</td>
	      <td><input name="image" type="file" class="textfield60" id="image"  value="" /></td>
	    </tr>
	    <tr>
	    	<td colspan="2">
	    		<br />
	    		<h4>Security</h4>
	    		<hr />
	    	</td>
	    </tr>
	    <tr>
	      <td>Password</td>
	      <td><input required="true" name="password" class="pwd" type="password" class="textfield" id="password" /></td>
	    </tr>
	    <tr>
	      <td>Confirm Password </td>
	      <td><input required="true" name="cpassword" type="password" class="textfield" id="cpassword" /></td>
	    </tr>
	    <tr>
	      <td>&nbsp;</td>
	      <td>
		  	<span class="wrapper"><a class='link1' href="javascript:if (verify()) $('#loginForm').submit();"><em><b>Update</b></em></a></span>
	      </td>
	    </tr>
	  </table>
	  <input required="true" type="hidden" id="description" name="description" value="Profile image" />
	  <input type="hidden" id="description" name="description" value="Profile image" />
	  </table>
	  <script>
	  $(document).ready(
			function() {
<?php
	if (($memberid != $_SESSION['SESS_MEMBER_ID']) && ! isUserInRole("ADMIN")) {
?>
				$("#fname").attr("disabled", true);
				$("#lname").attr("disabled", true);
				$("#email").attr("disabled", true);
				
<?php
	}
?>	
				$(".pwd").blur(verifypassword);
				$("#email").blur(checkEmail);
				$("#cpassword").blur(verifycpassword);
				$("#fname").focus();
				$("#customerid").val("<?php echo $member['customerid']; ?>")
				$("#warehouseid").val("<?php echo $member['warehouseid']; ?>")
			});
	
	function verify() {
		var isValid = verifyStandardForm('#loginForm');
		
		if (! verifypassword()) {
			isValid = false;
		}
		
		if (! verifycpassword()) {
			isValid = false;
		}
		
		if (! checkEmail()) {
			isValid = false;
		}
		
		return isValid;
	}
	
	function verifypassword() {
		var node = $(".pwd");
		var str = $(node).val();
		
		if( str.match(/(?=.*\d{2,})(?=.*[a-zA-Z])/)) {
			$(node).removeClass("invalid");
			$(node).next().css("visibility", "hidden");
			$(node).next().attr("title", "Required field.");
			
			return true;
			
		} else {
			$(node).addClass("invalid");
			$(node).next().css("visibility", "visible");
			$(node).next().attr("title", "The password entered does not meet the minimum requirements. Passwords should be at least 6 characters in length and contain a minimum of two digits.");
			
			return false;
		}
	}
	
	function verifycpassword() {
		var node = $("#cpassword");
		var str = $(node).val();
		
		if ($(node).val() == "") {
			return false;
		}
		
		if( str == $(".pwd").val()) {
			$(node).removeClass("invalid");
			$(node).next().css("visibility", "hidden");
			$(node).next().attr("title", "Required field.");
			
			return true;
			
		} else {
			$(node).addClass("invalid");
			$(node).next().css("visibility", "visible");
			$(node).next().attr("title", "Passwords do not match.");
			
			return false;
		}
	}
	
	
	function checkEmail() {
		var node = $("#email");
		var returnValue = true;
		
		if ($(node).val() == "") {
			return false;
		}
		
		$("#confirmemail").val(node.val());
		
		callAjax(
				"findemail.php", 
				{ 
					email: $("#email").val(),
					login: <?php echo $memberid; ?>
				},
				function(data) {
					if (data.length >= 1) {
						$(node).addClass("invalid");
						$(node).next().css("visibility", "visible");
						$(node).next().attr("title", "Email address is already in use by user " + data[0].login + "(" +  data[0].firstname  + " " + data[o].lastname + ").");
						
						returnValue = false;
						
					} else {
						$(node).removeClass("invalid");
						$(node).next().css("visibility", "hidden");
						$(node).next().attr("title", "Required field.");
					}
				},
				false
			);
			
		return returnValue;
	}
</script>
	</form>
<?php
		}
	}
			
?>
<!--  End of content -->

<?php include("system-footer.php"); ?>
