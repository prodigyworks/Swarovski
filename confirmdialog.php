<?php
function createConfirmDialog($name, $title, $scriptCallBack) {
?>
	
<div class="modal" id="<?php echo $name; ?>">
	<table>
		<tr>
			<td width=40><img src="images/alert.png" /></td>
			<td><p class='confirmdialogbody'></p></td>
		</tr>
	</table>
</div>
<script>
	$(document).ready(function() {
			$("#<?php echo $name; ?>").dialog({
					modal: true,
					autoOpen: false,
					show:"fade",
					hide:"fade",
					title: "<?php echo $title; ?>",
					buttons: {
						"Yes": function() {
							<?php echo $scriptCallBack . "();\n"; ?>
						},
						"No": function() {
							$(this).dialog("close");
						}
					}
				});
		});
</script>
<?php
}
?>
