<?php include("system-header.php"); ?>

<!--  Start of content -->

<?php
if ($_GET['type'] != "ADMIN") {
?>
<h4>The newly registered customer will receive login details when their first stock item is checked out.</h4>
	
<?php
} else {
?>
<h4>The newly registered user will receive login details.</h4>
<?php
}
?>
<!--  End of content -->

<?php include("system-footer.php"); ?>
