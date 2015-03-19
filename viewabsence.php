<?php 
function removeAbsence() {
	$id = $_POST['pk1'];
	$qry = "DELETE FROM {$_SESSION['DB_PREFIX']}absence " .
			"WHERE id = $id";
	$result = mysql_query($qry);
	
	if (! $result) {
		logError($qry . " - " . mysql_error());
	}
}
function confirmAbsence() {
	$id = $_POST['pk1'];
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}absence SET " .
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

function rejectAbsence() {
	$id = $_POST['pk1'];
	$reason = $_POST['pk2'];
	
	$qry = "UPDATE {$_SESSION['DB_PREFIX']}absence SET " .
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

function viewAbsence($sql) { 
require_once("system-header.php"); 
require_once("confirmdialog.php");
require_once("tinymce.php");
createConfirmDialog("removedialog", "Remove absence ?", "callRemoveAbsence");
createConfirmDialog("confirmdialog", "Confirm absence ?", "callConfirmAbsence");
?>
<script>
	var currentAbsence = null;
	
	function removeAbsence(id) {
		currentAbsence = id;
		
		$("#removedialog .confirmdialogbody").html("You are about to remove this absence.<br>Are you sure ?");
		$("#removedialog").dialog("open");
	}
	
	function confirmAbsence(id) {
		currentAbsence = id;
		
		$("#confirmdialog .confirmdialogbody").html("You are about to confirm this absence.<br>Are you sure ?");
		$("#confirmdialog").dialog("open");
	}
	
	function rejectAbsence(id) {
		currentAbsence = id;
		
		$("#reasondialog").dialog("open");
	}
	
	function callRemoveAbsence() {
		call("removeAbsence", {pk1: currentAbsence});
	}
	
	function callConfirmAbsence() {
		call("confirmAbsence", {pk1: currentAbsence});
	}
	
	function callRejectAbsence() {
		call("rejectAbsence", {pk1: currentAbsence, pk2: $("#reason").val()});
	}
	
	function edit(id) {
		callAjax(
				"findabsence.php", 
				{ 
					id: id
				},
				function(data) {
					if (data.length > 0) {
						var node = data[0];
						
						$("#absenceid").val(node.id);
						$("#memberid").val(node.memberid);
						$("#startdate").val(node.startdate);
						$("#startdate_half").attr("checked", (node.startdate_half == 0) ? true : false);
						$("#enddate_half").attr("checked", (node.enddate_half == 0) ? true : false);
						$("#requesteddate").val(node.requesteddate);
						$("#enddate").val(node.enddate);
						$("#absencetype").val(node.absencetype);
						tinyMCE.get('absentreason').setContent(node.absentreason); 
					}
				},
				false
			);
		
		$("#editdialog").dialog("open");
	}
	
	function viewReason(id) {
			callAjax(
					"findabsence.php", 
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
							
							callRejectAbsence();
						},
						Cancel: function() {
							$(this).dialog("close");
						}
					}
				});
				
			$("#editdialog").dialog({
					modal: true,
					autoOpen: false,
					width: 1020,
					show:"fade",
					hide:"fade",
					title:"Edit absence",
					open: function(event, ui){
						
					},
					buttons: {
						Ok: function() {
							tinyMCE.triggerSave();
							
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
				<td width='200px'>First Day Of Absence</td>
				<td>
					<input class="datepicker" type="text" id="startdate" name="startdate" />
					<input type="checkbox" id="startdate_half" name="startdate_half" checked>&nbsp;Full day</input>
				</td>
			</tr>
			<tr>
				<td width='200px'>Last Day Of Absence</td>
				<td>
					<input class="datepicker" type="text" id="enddate" name="enddate" />
					<input type="checkbox" id="enddate_half" name="enddate_half" checked>&nbsp;Full day</input>
				</td>
			</tr>
			<tr>
				<td width='200px'>Absence Type</td>
				<td>
					<SELECT id='absencetype' name='absencetype'>
						<OPTION value='Unauthorised'>Unauthorised</OPTION>
						<OPTION value='Authorised'>Authorised</OPTION>
						<OPTION value='Sick'>Sick</OPTION>
						<OPTION value='Family Matter'>Family Matter</OPTION>
						<OPTION value='Not In'>Not In</OPTION>
					</SELECT>
				</td>
			</tr>
			<tr>
				<td width='200px'>Reason</td>
				<td><textarea class="tinyMCE" style='width:700px; height: 200px;' id="absentreason" name="absentreason"></textarea></td>
			</tr>
		</table>
	</div>
</div>
<form id="editform" action="addabsence.php?callee=<?php echo base64_encode($_SERVER['PHP_SELF']); ?>" method="POST">
	<input type="hidden" id="absenceid" name="absenceid" value="" />
	
	<div id="dummypanel" style="display:none"></div>
</form>
<table width='100%' cellpadding=0 cellspacing=0 class="grid list" id="dummy2">
	<thead>
		<tr>
			<td>Name</td>
			<td>Request Date</td>
			<td>Start Date</td>
			<td>End Date</td>
			<td align=center>Duration</td>
			<td>Type</td>
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
				echo "<td>" . $member['requesteddate'] . "</td>\n";
				echo "<td>" . $member['startdate'] . "</td>\n";
				echo "<td>" . $member['enddate'] . "</td>\n";
				echo "<td align=center>" . number_format($member['daystaken'], 1) . "</td>\n";
				echo "<td>" . $member['absencetype'] . "</td>\n";
				
					
				if ($member['rejectedby'] != null) {
					echo "<td><a href='javascript: viewReason(" . $member['id'] . ")'>Rejected</a></td>\n";
					
				} else if ($member['acceptedby'] != null) {
					echo "<td>Accepted</td>\n";
					
				} else {
					echo "<td>Pending</td>\n";
				}
				
				echo "<td><img title='View absence request' src='images/view.png' onclick='edit(" . $member['id'] .")' /></td>\n";

				if (isUserInRole("OFFICE") || $member['memberid'] == getLoggedOnMemberID()) {
					echo "<td>&nbsp;</td>";
					echo "<td>&nbsp;</td>";
					echo "<td>&nbsp;</td>";
					echo "<td>&nbsp;</td>";
					
				} else {
					echo "<td><img title='Edit absence request' src='images/edit.png' onclick='edit(" . $member['id'] .")' /></td>\n";
					
					if ($member['acceptedby'] == null) {
						echo "<td><img title='Approve absence request' src='images/approve.png' onclick='confirmAbsence(" . $member['id'] . ")' /></td>\n";
						
					} else {
						echo "<td>&nbsp;</td>\n";
					}
					
					if ($member['rejectedby'] == null) {
						echo "<td><img title='Reject absence request' src='images/cancel.png' onclick='rejectAbsence(" . $member['id'] . ")' /></td>\n";
						
					} else {
						echo "<td>&nbsp;</td>\n";
					}
					
					echo "<td><img title='Remove absence request' src='images/delete.png' onclick='removeAbsence(" . $member['id'] . ")' /></td>\n";
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