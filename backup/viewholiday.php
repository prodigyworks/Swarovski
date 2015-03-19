<?php 
function removeHoliday() {
	$id = $_POST['pk1'];
	$qry = "DELETE FROM {$_SESSION['DB_PREFIX']}holiday " .
			"WHERE id = $id";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " - " . mysql_error());
	}
}

function confirmHoliday() {
	$id = $_POST['pk1'];
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}holiday SET " .
			"rejectedby = null, " .
			"rejecteddate = null, " .
			"acceptedby = " . getLoggedOnMemberID() . ", " .
			"accepteddate = NOW() " .
			"WHERE id = $id";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " - " . mysql_error());
	}
}

function rejectHoliday() {
	$id = $_POST['pk1'];
	$reason = $_POST['pk2'];
	
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}holiday SET " .
			"rejectedby = " . getLoggedOnMemberID() . ", " .
			"rejecteddate = NOW(), " .
			"acceptedby = null, " .
			"accepteddate = null, " .
			"reason = '" . mysql_escape_string($reason) . "' " .
			"WHERE id = $id";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " - " . mysql_error());
	}
}

function viewHoliday($sql) { 
require_once("system-header.php"); 
require_once("confirmdialog.php");
require_once("tinymce.php");
createConfirmDialog("removedialog", "Remove holiday ?", "callRemoveHoliday");
createConfirmDialog("confirmdialog", "Confirm holiday ?", "callConfirmHoliday");
?>
<script>
	var currentHoliday = null;
	
	function removeHoliday(id) {
		currentHoliday = id;
		
		$("#removedialog .confirmdialogbody").html("You are about to remove this holiday.<br>Are you sure ?");
		$("#removedialog").dialog("open");
	}
	
	function confirmHoliday(id) {
		currentHoliday = id;
		
		$("#confirmdialog .confirmdialogbody").html("You are about to confirm this holiday.<br>Are you sure ?");
		$("#confirmdialog").dialog("open");
	}
	
	function rejectHoliday(id) {
		currentHoliday = id;
		
		$("#reasondialog").dialog("open");
	}
	
	function callRemoveHoliday() {
		call("removeHoliday", {pk1: currentHoliday});
	}
	
	function callConfirmHoliday() {
		call("confirmHoliday", {pk1: currentHoliday});
	}
	
	function callRejectHoliday() {
		call("rejectHoliday", {pk1: currentHoliday, pk2: $("#reason").val()});
	}
	
	function edit(id) {
		callAjax(
				"findholiday.php", 
				{ 
					id: id
				},
				function(data) {
					if (data.length > 0) {
						var node = data[0];
						
						$("#holidayid").val(node.id);
						$("#memberid").val(node.memberid);
						$("#startdate").val(node.startdate);
						$("#startdate_half").attr("checked", (node.startdate_half == 0) ? true : false);
						$("#enddate_half").attr("checked", (node.enddate_half == 0) ? true : false);
						$("#requesteddate").val(node.requesteddate);
						$("#enddate").val(node.enddate);
					}
				},
				false
			);
		
		$("#editdialog").dialog("open");
	}
	
	function viewReason(id) {
			callAjax(
					"findholiday.php", 
					{ 
						id: id
					},
					function(data) {
						if (data.length > 0) {
							var node = data[0];
							
							$('#reasondiv').html(node.reason); 
							$("#reasondivdialog").dialog("open");
						}
					},
					false
				);
	}
	
	$(document).ready(function() {
			$("#reasondialog").dialog({
					modal: true,
					autoOpen: false,
					title: "Reason for rejection",
					width: 810,
					height: 420,
					buttons: {
						Ok: function() {
							tinyMCE.triggerSave();
							
							callRejectHoliday();
						},
						Cancel: function() {
							$(this).dialog("close");
						}
					}
				});
				
			$("#editdialog").dialog({
					modal: true,
					autoOpen: false,
					width: 970,
					show:"fade",
					hide:"fade",
					title:"Edit holiday",
					open: function(event, ui){
						
					},
					buttons: {
						Ok: function() {
							$("#editpanel").appendTo("#dummypanel");
							$("#editform").submit();
						},
						Cancel: function() {
							$(this).dialog("close");
						}
					}
				});
				
			$("#reasondivdialog").dialog({
					modal: true,
					autoOpen: false,
					title: "Reason for rejection",
					width: 810,
					height: 420,
					buttons: {
						Ok: function() {
							$(this).dialog("close");
						}
					}
				});
		});
</script>
<div id="reasondialog" class="modal">
	<label>Reason</label>
	<textarea id="reason" name="reason" class="tinyMCE" style='width:770px; height: 300px'></textarea>
</div>
<div id="reasondivdialog" class="modal">
	<h5>Reason</h5>
	<br>
	<div id="reasondiv" style='width:770px; height: 290px; border: 1px solid black'></div>
</div>
<div class="modal" id="editdialog">
	<div id="editpanel">
		<table width='100%' style='table-layout:fixed' cellspacing=5>
			<tr>
				<td width='200px'>Employee</td>
				<td>
					<?php createUserCombo("memberid"); ?>
				</td>
			</tr>
			<tr>
				<td width='200px'>Requested Date</td>
				<td><input class="datepicker" type="text" id="requesteddate" name="requesteddate" /></td>
			</tr>
			<tr>
				<td width='200px'>First Day Of Holiday</td>
				<td>
					<input class="datepicker" type="text" id="startdate" name="startdate" />
					<input type="checkbox" id="startdate_half" name="startdate_half" checked>&nbsp;Full day</input>
				</td>
			</tr>
			<tr>
				<td width='200px'>Last Day Of Holiday</td>
				<td>
					<input class="datepicker" type="text" id="enddate" name="enddate" />
					<input type="checkbox" id="enddate_half" name="enddate_half" checked>&nbsp;Full day</input>
				</td>
			</tr>
		</table>
	</div>
</div>
<form id="editform" action="addholiday.php?callee=<?php echo base64_encode($_SERVER['PHP_SELF']); ?>" method="POST">
	<input type="hidden" id="holidayid" name="holidayid" value="" />
	
	<div id="dummypanel" style="display:none"></div>
</form>
<table width='100%' cellpadding=0 cellspacing=0 class="grid list" id="dummy2">
	<thead>
		<tr>
			<td>Name</td>
			<td align=center>Days Remainings</td>
			<td>Request Date</td>
			<td>Start Date</td>
			<td>End Date</td>
			<td align=center>Duration</td>
			<td>Status</td>
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
				echo "<tr>\n";
				echo "<td>" . $member['firstname'] . " " . $member['lastname'] . "</td>\n";
				echo "<td align=center>" . $member['daysremaining'] . "</td>\n";
				echo "<td>" . $member['requesteddate'] . "</td>\n";
				echo "<td>" . $member['startdate'] . "</td>\n";
				echo "<td>" . $member['enddate'] . "</td>\n";
				echo "<td align=center>" . number_format($member['daystaken'], 1) . "</td>\n";
					
				if ($member['rejectedby'] != null) {
					echo "<td><a href='javascript: viewReason(" . $member['id'] . ")'>Rejected</a></td>\n";
					
				} else if ($member['taken'] == 1) {
					if ($member['acceptedby'] != null) {
						echo "<td>Taken</td>\n";
						
					} else {
						echo "<td>Pending</td>\n";
					}
				
				} else if ($member['acceptedby'] != null) {
					echo "<td>Accepted</td>\n";
					
				} else {
					echo "<td>Pending</td>\n";
				}
				
				echo "<td><img title='View holiday request' src='images/view.png' onclick='edit(" . $member['id'] .")' /></td>\n";
				
				if (! isUserInRole("ADMIN") && (isUserInRole("OFFICE") || $member['memberid'] == getLoggedOnMemberID())) {
					echo "<td>&nbsp;</td>\n";
					
				} else {
					echo "<td><img title='Edit holiday request' src='images/edit.png' onclick='edit(" . $member['id'] .")' /></td>\n";
				}
				
				if (! isUserInRole("ADMIN") && (isUserInRole("OFFICE") || $member['memberid'] == getLoggedOnMemberID())) {
					echo "<td>&nbsp;</td>\n";
					echo "<td>&nbsp;</td>\n";
					echo "<td>&nbsp;</td>\n";
					
				} else {
					if ($member['acceptedby'] == null) {
						echo "<td><img title='Approve holiday request' src='images/approve.png' onclick='confirmHoliday(" . $member['id'] . ")' /></td>\n";
						
					} else {
						echo "<td>&nbsp;</td>\n";
					}
					
					if ($member['rejectedby'] == null) {
						echo "<td><img title='Reject holiday request' src='images/cancel.png' onclick='rejectHoliday(" . $member['id'] . ")' /></td>\n";
						
					} else {
						echo "<td>&nbsp;</td>\n";
					}
				
					echo "<td><img title='Remove holiday request' src='images/delete.png' onclick='removeHoliday(" . $member['id'] . ")' /></td>\n";
				}
				
				echo "</tr>\n";
			}
			
		} else {
			logError($sql . " - " . mysql_error());
		}
	?>
</table>
<?php 

require_once("system-footer.php"); 
}
?>