<?php 
	include("system-header.php"); 
?>
<h2>Select customer, leave blank for a list of all customers.</h2>
<br>
<form id="loginForm" class="entryform2" name="loginForm" method="post" action="customerstockreportdata.php">
	<label>Customer</label>
	<?php createCombo("customerid", "id", "name", "{$_SESSION['DB_PREFIX']}customers"); ?>
	<br>
	<br>
	<a href="javascript:$('#loginForm').submit()" class='link1'><em><b>Run report</b></em></a>
</form>
<?php 
	include("system-footer.php"); 
?>