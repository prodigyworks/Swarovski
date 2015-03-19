<?php 
	include("system-header.php"); 
?>
<h2>Select customer, leave blank for a list of all customers.</h2>
<br>
<form id="loginForm" class="entryform2"  method="post" action="customerstockreportdata.php"  target="_new"> 
	<label>Customer</label>
	<?php createCombo("customerid", "id", "name", "{$_SESSION['DB_PREFIX']}customers"); ?>
	<br>
	<br>
</form>
	<a href="javascript: submit();" class='link1'><em><b>Run report</b></em></a>
<script>
	function submit(e) {
		$('#loginForm').submit();
		e.preventDefault();
		
	}
</script>
<?php 
	include("system-footer.php"); 
?>