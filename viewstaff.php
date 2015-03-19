<?php 
function viewStaff($sql) { 
require_once("system-header.php"); 
require_once("confirmdialog.php");
require_once("tinymce.php");
createConfirmDialog("removedialog", "Remove absence ?", "callRemoveAbsence");
?>
<table width='100%' cellpadding=0 cellspacing=0 class="grid list" id="dummy2">
	<thead>
		<tr>
			<td>Name</td>
			<td>Number</td>
			<td>Position</td>
			<td>Team</td>
			<td align=center>CPD (This)</td>
			<td align=center>CPD (Prev)</td>
			<td align=center>Entitlement</td>
			<td align=center>Booked</td>
			<td align=center>Taken</td>
			<td align=center>Remaining</td>
			<td align=center>Absent</td>
			<td width='20px'>&nbsp;</td>
			<td width='20px'>&nbsp;</td>
			<td width='20px'>&nbsp;</td>
			<td width='20px'>&nbsp;</td>
			<td width='20px'>&nbsp;</td>
			<td width='20px'>&nbsp;</td>
			<td width='20px'>&nbsp;</td>
		</tr>
	</thead>
	<?php
		$result = mysql_query($sql);
		
		if ($result) {
			while (($member = mysql_fetch_assoc($result))) {
				if ($member['member_id'] == null) {
					continue;
				}

				echo "<tr>\n";
				echo "<td>" . $member['firstname'] . " " . $member['lastname'] . "</td>\n";
				echo "<td>" . $member['staffnumber'] . "</td>\n";
				echo "<td>" . $member['positionname'] . "</td>\n";
				echo "<td>" . $member['name'] . "</td>\n";
				echo "<td align=center>" . $member['cpdthisyear'] . "</td>\n";
				echo "<td align=center>" . $member['cpdprevyear'] . "</td>\n";
				echo "<td align=center>" . $member['prorataholidayentitlement'] . "</td>\n";
				echo "<td align=center>" . $member['daysbooked'] . "</td>\n";
				echo "<td align=center>" . $member['daystaken'] . "</td>\n";
				echo "<td align=center>" . ($member['prorataholidayentitlement'] - ($member['daystaken'] + $member['daysbooked'])) . "</td>\n";
				echo "<td align=center>" . $member['absent'] . "</td>\n";
				echo "<td><img title='View staff details' src='images/view.png' onclick='window.location.href = \"profile.php?id=" . $member['member_id'] . "\";' /></td>\n";

				if (isUserInRole("OFFICE") && $member['member_id'] != getLoggedOnMemberID()) {
					echo "<td>&nbsp;</td>\n";
					
				} else {
					echo "<td><img title='Edit staff details' src='images/edit.png' onclick='window.location.href = \"profile.php?callee=" . base64_encode(basename($_SERVER['PHP_SELF']))  . "&id=" . $member['member_id'] . "\";' /></td>\n";
				}
				
				echo "<td><img title='Training Delivered' src='images/training.png' onclick='window.location.href = \"trainingdelivered.php?id=" . $member['member_id'] . "&callee=" . base64_encode(basename($_SERVER['PHP_SELF']))  . "&id=" . $member['member_id'] . "\";' /></td>\n";
				echo "<td><img title='Annual Holidays' src='images/holiday.png' onclick='window.location.href = \"holidaystaken.php?id=" . $member['member_id'] . "&callee=" . base64_encode(basename($_SERVER['PHP_SELF']))  . "&id=" . $member['member_id'] . "\";' /></td>\n";
				echo "<td><img title='Absences' src='images/absent.png' onclick='window.location.href = \"myabsences.php?id=" . $member['member_id'] . "&callee=" . base64_encode(basename($_SERVER['PHP_SELF']))  . "&id=" . $member['member_id'] . "\";' /></td>\n";
				
				if (isUserInRole("OFFICE") && $member['member_id'] != getLoggedOnMemberID()) {
					echo "<td>&nbsp;</td>\n";
					echo "<td>&nbsp;</td>\n";

				} else {
					echo "<td><img title='Appraisals' src='images/appraisal.png' onclick='window.location.href = \"myappraisals.php?id=" . $member['member_id'] . "&callee=" . base64_encode(basename($_SERVER['PHP_SELF']))  . "&id=" . $member['member_id'] . "\";' /></td>\n";
					echo "<td><img title='Remove staff details' src='images/delete.png' onclick='remove(" . $member['member_id'] . ")' /></td>\n";
				}
				
				echo "</tr>\n";
			}
			
		} else {
			logError($sql . " - " . mysql_error());
		}
	?>
</table>
<script>
	function remove(id) {
		
	}
</script>
<?php 

}
?>