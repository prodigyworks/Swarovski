<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<?php 
	//Include database connection details
	require_once('system-config.php');
?>

<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="css/jquery-ui.css" rel="stylesheet" type="text/css" />

<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/jquery-ui.min.js" type="text/javascript"></script>
<script src="js/jcarousellite.js" type="text/javascript"></script>
<script src='js/jquery.hoverIntent.minified.js' type='text/javascript'></script>
<script src='js/jquery.dcmegamenu.1.3.3.js' type='text/javascript'></script>
<script src="js/oraclelogs.js" language="javascript" ></script> 
<title>DTi - Project Tracker</title>
<script>
	var autoLoad = false;
</script>
</head>
<body>
	<?php
		if (isset($_POST['command'])) {
			$_POST['command']();
		}
	?>
	
	<form method="POST" id="commandForm" name="commandForm">
		<input type="hidden" id="command" name="command" />
		<input type="hidden" id="pk1" name="pk1" />
		<input type="hidden" id="pk2" name="pk2" />
	</form>
		<div id="embeddedcontent">
			<div class="embeddedpage">

			