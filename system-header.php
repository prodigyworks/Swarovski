<?php
	require_once('system-db.php');
	
	if(!isset($_SESSION)) {
		session_start();
	}
	
	if (! isAuthenticated() && ! endsWith($_SERVER['PHP_SELF'], "/system-login.php")) {
		header("location: system-login.php?session=" . urlencode(base64_encode($_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] )));
		exit();
	}
?>
<?php 
	//Include database connection details
	require_once('system-config.php');
	require_once("confirmdialog.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>SWAROVSKI OPTIKS LTD</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=8" />
<link rel="shortcut icon" href="favicon.ico">

<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="css/dcmegamenu.css" rel="stylesheet" type="text/css" />
<link href="css/skins/white.css" rel="stylesheet" type="text/css" />

<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery-ui.min.js" type="text/javascript"></script>
<script src="js/jcarousellite.js" type="text/javascript"></script>
<script src='js/jquery.hoverIntent.minified.js' type='text/javascript'></script>
<script src='js/jquery.dcmegamenu.1.3.3.js' type='text/javascript'></script>
<script src="js/oraclelogs.js" language="javascript" ></script> 
<!--[if lt IE 7]>
<script type="text/javascript" src="js/ie_png.js"></script>
<script type="text/javascript">
	ie_png.fix('.png, .carousel-box .next img, .carousel-box .prev img');
</script>
<link href="css/ie6.css" rel="stylesheet" type="text/css" />
<![endif]-->
</head>
<body id="page1">
<?php
	createConfirmDialog("passworddialog", "Forgot password ?", "forgotPassword");
	
	if (isset($_POST['command'])) {
		$_POST['command']();
	}
?>
<div id="devdialog" class="entryform modal">
	<label>Suggestion</label>
	<textarea id="suggestion_message" name="suggestion_message" class="tinyMCE"></textarea>
</div>
<form method="POST" id="commandForm" name="commandForm">
	<input type="hidden" id="command" name="command" />
	<input type="hidden" id="pk1" name="pk1" />
	<input type="hidden" id="pk2" name="pk2" />
</form>
<div class="tail-top-left"></div>
<div class="tail-top">
<!-- header -->
<?php 
	if (isAuthenticated()) {
?>
	<div id="header" class='header1'>
		<?php		
			$qry = "UPDATE {$_SESSION['DB_PREFIX']}members SET " .
					"lastaccessdate = NOW() " .
					"WHERE member_id = " . $_SESSION['SESS_MEMBER_ID'] . "";
			$result = mysql_query($qry);
		?>
		<div id="toppanel">
			<label>logged on: </label>
			<label>
			<a href='profile.php'>
				<?php 
					if (isUserInRole("ADMIN")) {
						if ($_SESSION['WAREHOUSE_NAME'] != "") {
							echo $_SESSION['SESS_FIRST_NAME'] . " " . $_SESSION['SESS_LAST_NAME'] . " (" . $_SESSION['WAREHOUSE_NAME'] . ")"; 
							
						} else {
							echo $_SESSION['SESS_FIRST_NAME'] . " " . $_SESSION['SESS_LAST_NAME']; 
						}
						
					} else {
						if (isUserInRole("CUSTOMER") && $_SESSION['CUSTOMER_NAME'] != "") {
							echo $_SESSION['SESS_FIRST_NAME'] . " " . $_SESSION['SESS_LAST_NAME'] . " (" . $_SESSION['CUSTOMER_NAME'] . ")"; 
							
						} else {
							echo $_SESSION['SESS_FIRST_NAME'] . " " . $_SESSION['SESS_LAST_NAME']; 
						}
					}
				?>
			</a>
			<span>
			<?php
				$qry = "SELECT COUNT(*) AS messages " .
						"FROM {$_SESSION['DB_PREFIX']}messages A " .
						"WHERE A.to_member_id = " . getLoggedOnMemberID() . " " .
						"AND status = 'N'";
				$result = mysql_query($qry);
			
				//Check whether the query was successful or not
				if($result) {
					while (($member = mysql_fetch_assoc($result))) {
						echo "<a id='messagecontainer' title='Unread messages' style='text-decoration:none; color: black' href='messages.php'>";
						
						if ($member['messages'] > 0) {
							echo "&nbsp;<img border=0 height=16 src='images/mail.png' /> (<span id='messagecount'>" . $member['messages'] . "</span>)";
						}
						
						echo "</a>";
					}
					
				} else {
					logError($qry . " - " . mysql_error());
				}
			?>
			</span> 
			</label>
		</div>
		<div class='login'>
			<div id='logoutbutton'></div>
			<?php
			if (isUserInRole("ADMIN")) {
			?>
			<a class='link1' href='javascript:suggestions()' id='devbutton'><em><b>Development</b></em></a>
			<?php
			}
			?>
			<script>
				function suggestions() {
					$("#devdialog").dialog("open");
				}
				
				$(document).ready(function() {
					$("#devdialog").dialog({
							modal: true,
							autoOpen: false,
							title: "Suggestions",
							width: 810,
							buttons: {
								Ok: function() {
									$(this).dialog("close");
									
									callAjax(
											"emailsuggestion.php", 
											{ 
												body: tinyMCE.get("suggestion_message").getContent()
											},
											function(data) {
												alert("Thank you. Your suggestion has been forwarded.");
											}
										);
								},
								Cancel: function() {
									$(this).dialog("close");
								}
							}
						});
						
					$("#logoutbutton").click(function() {
						window.location.href = "system-logout.php";
					});
				});
			</script>
		</div>
		<div class="logo">
			<img id="logo" src="images/logo.png" alt="" />
		</div>
	</div>
<?php		
	}
	require_once('tinymce.php');
?>
<!-- content -->
	<div id="content">
		<div class="row-1">
			<div class="inside">
				<div class="container">
					<div class="menu2">
						<div>
							<?php
								if (isAuthenticated()) {
									showMenu();
								}
							?>
						</div>
					</div>
					<?php 
						if (isAuthenticated()) {
							BreadCrumbManager::showBreadcrumbTrail();
						
							echo "<hr>\n";
						}
					?>
					<div class="content">
						<div id="title">
							<h4><?php echo $_SESSION['title']; ?></h4>
						</div>
						<hr />