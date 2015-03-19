<?php
	function checkSignature() {
		if (isset($_POST['output'])) {
			require_once('signature-to-image.php');
			
			try {
				$img = null;
				
				if (isset($_POST['output']) && $_POST['output'] != "") {
					$img = sigJsonToImage($_POST['output']);
				
				} else {
					// Create the image
					$img = imagecreatetruecolor(400, 30);
					
					// Create some colors
					$white = imagecolorallocate($img, 255, 255, 255);
					$grey = imagecolorallocate($img, 128, 128, 128);
					$black = imagecolorallocate($img, 0, 0, 0);
					imagefilledrectangle($img, 0, 0, 399, 29, $white);
					
					// The text to draw
					$text = $_POST['name'];
					// Replace path by your own font path
					$font = 'build/journal.ttf';
					
					// Add some shadow to the text
					imagettftext($img, 20, 0, 11, 21, $grey, $font, $text);
					
					// Add the text
					imagettftext($img, 20, 0, 10, 20, $black, $font, $text);
					
					// Using imagepng() results in clearer text compared with imagejpeg()
				}
				
				ob_start();
				imagepng($img);
				$imgstring = ob_get_contents(); 
		        ob_end_clean();
				
				$escimgstring = mysql_escape_string($imgstring);
				$id = $_POST['signatureid'];
				
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}images " .
						"(" .
						"mimetype, name, image, createddate" .
						") " .
						"VALUES " .
						"(" .
						"'image/png', 'Signature for despatch $id', '$escimgstring', NOW()" .
						")";
				$result = mysql_query($qry);
				$imageid = mysql_insert_id();
				
				file_put_contents("uploads/signature_" . $imageid . ".png", $imgstring);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}despatchheader SET " .
						"signed = 'Y', " .
						"signeddate = NOW(), " .
						"imageid = $imageid " .
						"WHERE id = $id";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				ob_start();
				$pdf = new DespatchReport( 'P', 'mm', 'A4', $id);
				$pdf->Output("", "S");
				$imgstring = mysql_escape_string(ob_get_contents());
		        ob_end_clean();
				
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}documents " .
					"(name, filename, mimetype, image, size, createdby, createddate) " .
					"VALUES " .
					"('Despatch note : $id', '$id.pdf', 'application/pdf', '$imgstring', 0, " . getLoggedOnMemberID() . ", NOW())";
		
				$result = mysql_query($qry);
				$documentid = mysql_insert_id();
		
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}despatchheader SET " .
						"signeddocumentid = $documentid " .
						"WHERE id = $id";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				$qry = "SELECT AA.memberid, DATE_FORMAT(A.expectedreturndate, '%d/%m/%Y') AS expectedreturndate, B.name, C.serialnumber, D.name AS stockname " .
						"FROM {$_SESSION['DB_PREFIX']}despatchitem A " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}despatchheader AA " .
						"ON AA.id = A.despatchid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}customers B " .
						"ON B.id = AA.customerid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}stockitem C " .
						"ON C.id = A.stockitemid " .
						"INNER JOIN {$_SESSION['DB_PREFIX']}stock D " .
						"ON D.id = C.stockid " .
						"WHERE A.despatchid = $id";
				
				$result = mysql_query($qry);
				
				//Check whether the query was successful or not
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						$customer = $member['name'];
						$stock = $member['stockname'];
						$serial = $member['serialnumber'];
						$expectedreturndate = $member['expectedreturndate'];
				
						$details = "<p>Despatch from customer: $customer has been signed.</p>";
						$details .= "<p>Stock : $stock</p>";
						$details .= "<p>Serial : $serial</p>";
						$details .= "<p>Expected Return Date : $expectedreturndate</p>";
						
				    	sendUserMessage($member['memberid'], "Despatch note signed", $details);
					}
				}
				
				
			} catch (Exception $e) {
				logError("Signing image: " . $e->getMessage());
			}

		}
	}
	
	function addSignatureForm() {
	?>
		  <link rel="stylesheet" href="build/jquery.signaturepad.css">
		  <!--[if lt IE 9]><script src="build/flashcanvas.js"></script><![endif]-->
		  <script src="build/jquery.signaturepad.min.js"></script>
		  <script src="build/json2.min.js"></script>
			  <form method="post" action="" class="sigPad">
			    <label for="name">Print your name</label>
			    <input type="text" name="name" id="name" class="name">
			    <p class="typeItDesc">Review your signature</p>
			    <p class="drawItDesc">Draw your signature</p>
			    <ul class="sigNav">
			      <li class="typeIt"><a href="#type-it" class="current">Type It</a></li>
			      <li class="drawIt"><a href="#draw-it" >Draw It</a></li>
			      <li class="clearButton"><a href="#clear">Clear</a></li>
			    </ul>
			    <div class="sig sigWrapper">
			      <div class="typed"></div>
			      <canvas class="pad" width="198" height="55"></canvas>
			      <input type="hidden" name="output" class="output">
			      <input type="hidden" id="signatureid" name="signatureid">
			    </div>
			    <button type="submit">I accept the terms of this agreement.</button>
			  </form>
		<script>
<?php
			if (isset($_POST['output'])) {
?>
			$(document).ready(function() {
					$("#messageDialog").dialog({
							autoOpen: true,
							modal: true,
							title: "Signed",
							buttons: {
								Ok: function() {
									$(this).dialog("close");
								}
							}
						});
				});
<?php
			}
?>				
		</script>
<?php
		if (isset($_POST['output'])) {
?>
			<div id="messageDialog" class="modal">
				Many thanks for signing the despatch note.  Your item will be despatched as soon as possible.
			</div>
<?php			
				
		}
	}
?>
