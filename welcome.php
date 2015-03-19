<?php 
	require_once("system-db.php");
	require_once('despatchreportlib.php');
	
	start_db();
	
	require_once('signature.php');
	
	checkSignature();
	
	require_once("system-header.php"); 
	
	addSignatureForm();
?>
<script>
	$(document).ready(
			function() {
		      	$('.sigPad').signaturePad();
			}
		);
		
	function sign(pk) {
		$("#signatureid").val(pk);
		$(".sigPad").fadeIn();
    } 	
</script>
<table width='100%'>
	<tr valign=top>
		<?php
		if (! isUserInRole("ADMIN")) {
		?>
		<td style='border: 1px solid #CCCCCC; padding: 10px'>
			<h4>Awaiting Signature</h4>
			<table  width='400px' class='grid view'>
				<thead>
					<tr>
						<td>Stock</td>
						<td>Item</td>
						<td>&nbsp;</td>
					</tr>
				</thead>
			<?php
				$sql = 
						"SELECT A.*, AA.expectedreturndate, " .
						"AB.serialnumber, B.name AS customername, D.name AS warehousename, " .
						"A.address, AC.name AS stockname " .
						"FROM {$_SESSION['DB_PREFIX']}despatchheader A " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}despatchitem AA " .
						"ON AA.despatchid = A.id " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}stockitem AB " .
						"ON AB.id = AA.stockitemid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}stock AC " .
						"ON AC.id = AB.stockid " .
						"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}customers B " .
						"ON B.id = A.customerid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}warehousestock C " .
						"ON C.stockitemid = AB.id " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}warehouses D " .
						"ON D.id = C.warehouseid " .
						"WHERE A.signed = 'N' " .
						"AND A.checkedindate IS NULL " .
						"AND A.customerid = " . $_SESSION['CUSTOMER_ID'] . " " .
						"ORDER BY AB.serialnumber";
				
				$result = mysql_query($sql);
				if (! $result) die("Error: " . mysql_error());
				
				//Check whether the query was successful or not
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						echo "<tr>\n";
						echo "<td>" . $member['stockname'] . "</td>";
						echo "<td>" . $member['serialnumber'] . "</td>";
						echo "<td><img title='Click to sign' src='images/stock.png' onclick='sign(" . $member['id'] . ")'/></td>";
						echo "</tr>\n";
					}
				}
			?>
			</table>
		</td>
		<?php
		}
		?>
		<td style='border: 1px solid #CCCCCC; padding: 10px'>
			<div class="welcome"	>
				<div class="fright welcome">
				<img src='images/logo-welcome.png' />
				</div>
				<?php 
					if (isUserInRole("ADMIN")) {
						echo getSiteConfigData()->welcometext; 
					}
				?>
			</div>
		</td>
	</tr>
</table>
<?php
	if (! isUserInRole("ADMIN")) {
?>
<p>Please click on the icon above to sign your loan agreement</p>
<?php
	}
?>
<?php include("system-footer.php"); ?>
