<?php
	$where = "";
	
	if (isset($_GET['id']) || isset($_GET['sessionid'])) {
		include("system-embeddedheader.php"); 
		
	} else {
		include("system-header.php"); 
	}
	
	include("confirmdialog.php");
	
	function search() {
		global $where;
		
		if ($_POST['pk1'] == "") {
			$where = "";
			
		} else {
			$where = " WHERE MATCH(name, filename) AGAINST ('" . $_POST['pk1'] . "'  IN BOOLEAN MODE) ";
		}
	}
	
	function delete() {
		if (isset($_POST['pk1'])) {
			$id = $_POST['pk1'];
			$qry = "DELETE FROM {$_SESSION['DB_PREFIX']}documents WHERE id = $id";
			$result = mysql_query($qry);
		}
	}
	
	
	if (isset($_GET['id']) || isset($_GET['sessionid'])) {
?>
<div style='background-color: white'>
<form id="documentForm" name="documentForm" onsubmit="return validate()" enctype="multipart/form-data" method="POST" action="system-documentupload.php?<?php if (isset($_GET['sessionid'])) echo "sessionid=" . $_GET['sessionid']; else echo "id=" . $_GET['id']; if (isset($_GET['documentcallback'])) echo "&documentcallback=" . $_GET['documentcallback'] . "&identifier=" . $_GET['identifier'];?>">
	<div id="documentDiv">
		<label>TITLE</label>
		<input type="text" id="title" name="title" style="width:350px" /><br>
		
		<label>DOCUMENT</label>
		<input type="file" id="document" name="document" style="width:450px" /><br>
		<br>
		<input type="submit" style="margin-left:0px" class="dataButton" value="ADD DOCUMENT" id="btnHeanerNotes" />
		<br>
	</div>
</form>
</div>
<div id="documentcontainer">
<?php
	
		createConfirmDialog("confirmdialog", "Delete document ?", "deleteDocumentFromDialog");
		
		if (isset($_GET['id'])) {
		}
		
	} else {
?>
</div>
<div id="documentDiv">
	<label>SEARCH</label>
	<input type="text" id="title" name="title" style="width:450px; " />
	<button id="search" name="search" onclick='search()' style='display:inline'>Search</button>
</div>
<?php
	}
?>

<?php
	if (isset($_GET['id']) || isset($_GET['sessionid'])) {
?>
<div style="position: absolute; top: 210px; background-color: white;width: 100%; height:420px; overflow-y: scroll">
<?php
	} else {
?>
<div style="height:490px; overflow-y: scroll">
<?php
	}
?>
	<table class='grid list' id="documentlist" <?php if (! isset($_GET['id']) && ! isset($_GET['sessionid'])) echo 'maxrows=18'; else echo 'maxrows=10';?> width=100% cellspacing=0 cellpadding=0>
		<thead>
			<tr>
				<td width='20px'></td>
<?php
	if (! isset($_GET['id']) && ! isset($_GET['sessionid'])) {
?>
				<td>Job / Quote</td>
<?php
	}
?>
				<td>Name</td>
				<td>File Name</td>
<?php
	if (isset($_GET['id']) || isset($_GET['sessionid'])) {
?>
				<td>Type</td>
<?php
	}
?>
				
				<td>Size</td>
				<td>Created</td>
				<td>Created By</td>
			</tr>
		</thead>
		<?php
			if (isset($_GET['id'])) {
				$qry = "SELECT A.*, DATE_FORMAT(A.createddate, '%d/%m/%Y') AS createddate, " .
						"DATE_FORMAT(A.lastmodifieddate, '%d/%m/%Y') AS lastmodifieddate, " .
						"B.firstname, B.lastname " .
						"FROM {$_SESSION['DB_PREFIX']}documents A " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
						"ON B.member_id = A.createdby " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}trainingdeliverydocs C " .
						"ON C.documentid = A.id " .
						"WHERE C.trainingdeliveryid = " . $_GET['id'] . " " .
						"ORDER BY A.id";
						
			} else if (isset($_GET['sessionid'])) {
				$qry = "SELECT A.*, DATE_FORMAT(A.createddate, '%d/%m/%Y') AS createddate, " .
						"DATE_FORMAT(A.lastmodifieddate, '%d/%m/%Y') AS lastmodifieddate, " .
						"B.firstname, B.lastname " .
						"FROM {$_SESSION['DB_PREFIX']}documents A " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
						"ON B.member_id = A.createdby " .
						"WHERE A.sessionid = '" . $_GET['sessionid'] . "' " .
						"ORDER BY A.id";
						
			} else {
				$qry = "SELECT A.*, DATE_FORMAT(A.createddate, '%d/%m/%Y') AS createddate, " .
						"DATE_FORMAT(A.lastmodifieddate, '%d/%m/%Y') AS lastmodifieddate, " .
						"B.firstname, B.lastname," .
						"C.prefix, C.id AS quoteid, C.status " .
						"FROM {$_SESSION['DB_PREFIX']}documents A " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}members B " .
						"ON B.member_id = A.createdby " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}trainingcode C " .
						"ON C.id = A.headerid " .
						$where . " " .
						"ORDER BY A.id";
			}

			$result = mysql_query($qry);
			
			if (! $result) logError("Error: " . mysql_error());
			
			//Check whether the query was successful or not
			if ($result) {
				while (($member = mysql_fetch_assoc($result))) {
					echo "<tr>\n";
					echo "<td width='20px' title='Delete' onclick='deleteDocument(" . $member['id'] . ")'><img src='images/delete.png' /></td>\n";
					
					echo "<td><a target='_new' href='viewdocuments.php?id=" . $member['id'] . "'>" . $member['name'] . "</a></td>\n";
					echo "<td>" . $member['filename'] . "</td>\n";
					
					if (isset($_GET['id']) || isset($_GET['sessionid'])) {
						echo "<td>" . $member['mimetype'] . "</td>\n";
					}
					
					echo "<td>" . $member['size'] . "</td>\n";
					echo "<td>" . $member['createddate'] . "</td>\n";
					echo "<td>" . $member['firstname'] . " " . $member['lastname'] . "</td>\n";
					echo "</tr>\n";
				}
			}
		?>
	</table>
</div>
<script>
	var selectedDocumentID;
	
	function deleteDocumentFromDialog() {
		call("delete", {pk1: selectedDocumentID});
	}
		
	function search() {
		call("search", {pk1: $("#title").val()});
	}
	
	function deleteDocument(id) {
		selectedDocumentID = id;
		$("#confirmdialog").dialog("open");
	}
	
	function validate() {
		if ($("#title").val() == "") {
			dtAlert("Please enter a title");
			$("#title").focus();
			return false;
		}
		
		if ($("#document").val() == "") {
			dtAlert("Please enter a file");
			$("#document").focus();
			return false;
		}
		
		return true;
	}
		
	$(document).ready(function() {
			$("#confirmdialog .confirmdialogbody").html("You are about to remove this document.<br>Are you sure ?");
		});
</script>
<?php
	if (isset($_GET['id']) || isset($_GET['sessionid']))  {
		include("system-embeddedfooter.php"); 
		
	} else {
		include("system-footer.php"); 
	}
?>
