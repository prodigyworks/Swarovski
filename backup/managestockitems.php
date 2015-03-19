<?php
	require_once("crud.php");
	require_once("confirmdialog.php");
	require_once('despatchreportlib.php');
	
	function regenerate() {
		$id = $_POST['checkout_stockitemid'];
		$qry = "SELECT A.despatchheaderid, B.customerid " .
				"FROM {$_SESSION['DB_PREFIX']}stockitem A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}despatchheader B " .
				"ON B.id = A.despatchheaderid " .
				"WHERE A.id = $id";
		$result = mysql_query($qry);
		
		if ($result) {
			while (($member = mysql_fetch_assoc($result))) {
				$deliveryheaderid = $member['despatchheaderid'];
				$customerid = $member['customerid'];
			}
			
		} else {
			logError($qry .  " - " . mysql_error());
		}
		
		ob_start();
		$pdf = new DespatchReport( 'P', 'mm', 'A4', $deliveryheaderid);
		$pdf->Output("", "S");
		$imgstring = mysql_escape_string(ob_get_contents());
        ob_end_clean();
		
		$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}documents " .
			"(name, filename, mimetype, image, size, createdby, createddate) " .
			"VALUES " .
			"('Despatch note : $deliveryheaderid', '$deliveryheaderid.pdf', 'application/pdf', '$imgstring', 0, " . getLoggedOnMemberID() . ", NOW())";

		$result = mysql_query($qry);
		$documentid = mysql_insert_id();

		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
		
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}despatchheader SET " .
				"presigneddocumentid = $documentid " .
				"WHERE id = $deliveryheaderid";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
		
		sendCustomerMessage($customerid, "Signature required", getSiteConfigData()->signatureemail);
	}
	
	function checkout() {
		$id = $_POST['checkout_stockitemid'];
		$customerid = $_POST['checkout_customerid'];
		$address = mysql_escape_string($_POST['checkout_address']);
		$ourref = mysql_escape_string($_POST['checkout_ourref']);
		$yourref = mysql_escape_string($_POST['checkout_yourref']);
		$reason = mysql_escape_string($_POST['checkout_reason']);
		$expecteddate = convertStringToDate($_POST['checkout_expecteddate']);
		$memberid = getLoggedOnMemberID();
		
		$qry = "SELECT A.* FROM {$_SESSION['DB_PREFIX']}stockitem A " .
				"WHERE A.id = $id";
		$result = mysql_query($qry);
		
		if ($result) {
			while (($member = mysql_fetch_assoc($result))) {
				$instructions = $member['instructions'];
				$stockitemid = $member['id'];
				
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}despatchheader " .
						"(" .
						"customerid, address, memberid, despatchdate, checkedoutdate, reason, instructions, ourref, yourref, signed, despatched " .
						") " .
						" VALUES " .
						"(" .
						"'$customerid', '$address', '$memberid', NOW(), NOW(), '$reason', '$instructions', '$ourref', '$yourref', 'N', 'N' " .
						")";
				$insertresult = mysql_query($qry);
	
				if (! $insertresult) {
					logError($qry . " - " . mysql_error());
				}
				
				$deliveryheaderid = mysql_insert_id();
				
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}despatchitem " .
						"(" .
						"despatchid, stockitemid, expectedreturndate " .
						") " .
						" VALUES " .
						"(" .
						"$deliveryheaderid, '$stockitemid', '$expecteddate' " .
						")";
				$insertresult = mysql_query($qry);
	
				if (! $insertresult) {
					logError($qry . " - " . mysql_error());
				}
		
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}stockitem SET " .
						"despatchheaderid = $deliveryheaderid " .
						"WHERE id = $id";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				ob_start();
				$pdf = new DespatchReport( 'P', 'mm', 'A4', $deliveryheaderid);
				$pdf->Output("", "S");
				$imgstring = mysql_escape_string(ob_get_contents());
                ob_end_clean();
				
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}documents " .
					"(name, filename, mimetype, image, size, createdby, createddate) " .
					"VALUES " .
					"('Despatch note : $deliveryheaderid', '$deliveryheaderid.pdf', 'application/pdf', '$imgstring', 0, " . getLoggedOnMemberID() . ", NOW())";

				$result = mysql_query($qry);
				$documentid = mysql_insert_id();

				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				$qry = "UPDATE {$_SESSION['DB_PREFIX']}despatchheader SET " .
						"presigneddocumentid = $documentid " .
						"WHERE id = $deliveryheaderid";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError($qry . " - " . mysql_error());
				}
				
				
				$qry = "SELECT member_id, uepwd, login FROM {$_SESSION['DB_PREFIX']}members " .
						"WHERE customerid = $customerid " .
						"AND initialised = 'N'";
				$itemresult = mysql_query($qry);
				
				if ($itemresult) {
					while (($itemmember = mysql_fetch_assoc($itemresult))) {
						$qry = "UPDATE {$_SESSION['DB_PREFIX']}members SET " .
								"initialised = 'Y' " .
								"WHERE member_id = " . $itemmember['member_id'];
						$updateresult = mysql_query($qry);
						
						if (! $updateresult) {
							logError($qry . " - " . mysql_error());
						}
						
						sendUserMessage($itemmember['member_id'], "User Registration", getSiteConfigData()->registrationemail . "<br><br>Information you need to know.<br><br>Login : " . $itemmember['login'] . "<br>Password : " . $itemmember['uepwd'] . "<br>Click to login : " . getSiteConfigData()->domainurl);
					}
					
				} else {
					logError(mysql_error());
				}
				
				
				sendCustomerMessage($customerid, "Signature required", getSiteConfigData()->signatureemail);
			}
			
		} else {
			logError($qry . " - " . mysql_error());
		}
	}
	
	function checkin() {
		$id = $_POST['checkin_stockitemid'];
		
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}stockitem SET " .
				"despatchheaderid = null " .
				"WHERE id = $id";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
	}
	
	function move() {
		$id = $_POST['move_stockitemid'];
		$warehouseid = $_POST['move_warehouseid'];
		
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}warehousestock SET " .
				"warehouseid = $warehouseid " .
				"WHERE stockitemid = $id";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
	}
	
	class StockItemCrud extends Crud {
		public function postHeaderEvent() {
			createConfirmDialog("confirmcheckoutdialog", "Confirm check out ?", "confirmcheckout");
			createConfirmDialog("confirmmovedialog", "Confirm stock movement ?", "confirmstockmovement");
			createConfirmDialog("confirmcheckindialog", "Confirm check in ?", "confirmcheckin");
			
			?>
				<div id="emaildialog" class="entryform modal">
					<label>To</label>
					<input type="text" id="email_to" name="email_to" style='width:500px' />
					<label>Message</label>
					<textarea id="email_message" name="email_message" class="tinyMCE"></textarea>
				</div>
				<div id="checkoutdialog"  class="entryform modal">
					<label>Customer</label>
					<?php createCombo("cocustomerid", "id", "name", "{$_SESSION['DB_PREFIX']}customers"); ?>
					<label>Deliver To</label>
					<SELECT id="coaddressid" name="coaddressid" style='width:200px'>
						<OPTION value=""></OPTION>
					</SELECT>

					<div id="adhoc" style="margin-top:5px">
						<textarea id="adhocaddress" name="adhocaddress" cols=60 rows=5></textarea>
					</div>
					
					<label>Expected Return Date</label>
					<input type="text" class="datepicker" id="coexpecteddate" name="coexpecteddate" />
					<label>Our Reference</label>
					<input type="text" id="ourref" name="ourref" />
					<label>Your Reference</label>
					<input type="text" id="yourref" name="yourref" />
					<label>Reason For Loan</label>
					<textarea id="reason" name="reason" cols=80 rows=6></textarea>
				</td>
			</tr>
				</div>
				<div id="movedialog" class="modal">
					<label>Warehouse</label><br>
					<?php createCombo("mowarehouseid", "id", "name", "{$_SESSION['DB_PREFIX']}warehouses"); ?>
				</div>
			<?php
		}
		
		public function postInsertEvent() {
			$stockitemid = mysql_insert_id();
			$warehouseid = $_POST['warehouseid'];
			$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}warehousestock " .
					"(warehouseid, stockitemid) " .
					"VALUES " .
					"($warehouseid, $stockitemid)";
			
			$result = mysql_query($qry);
		}
		
		public function postScriptEvent() {
?>
			var currentID = 0;
			
			/* Derived address callback. */
			function fullAddress(node) {
				if (node.address == null) {
					return "";
				}
				
				return node.address.replace(/\r\n/g, " ");
			}
			
			$(document).ready(
					function() {
						$("#cocustomerid").change(
								function() {
									if ($(this).val() != "") {
										getJSONData('findaddress.php?id=' + $(this).val(), "#coaddressid", function() {
											var select = $("#coaddressid");
											var options = select.attr('options');
											  
									         options[options.length] = new Option("109998 - Repairs", "109998");  
											
											$("#coaddressid").val("").trigger("change");
											
										}, true);
									}
								}
							);
						$("#coaddressid").change(
								function() {
									if ($("#coaddressid").val() == "") {
										return;
									}
									
									if ($("#coaddressid").val() == "109998") {
										$("#adhocaddress").val("109998 - Repairs");
										return;
									}
									
									callAjax(
											"finddata.php", 
											{ 
												sql: "SELECT * FROM <?php echo $_SESSION['DB_PREFIX'];?>customeraddresses WHERE id = " + $("#coaddressid").val()
											},
											function(data) {
												if (data.length > 0) {
													var node = data[0];
													var address = "";
													
													if ((node.street) != "") {
														address = address + node.street;
													} 
													
													if ((node.town) != "") {
														if (address != "") {
															address = address + ",\n";
														}
														
														address = address + node.town;
													} 
													
													if ((node.city) != "") {
														if (address != "") {
															address = address + ",\n";
														}
														
														address = address + node.city;
													} 
													
													if ((node.county) != "") {
														if (address != "") {
															address = address + ",\n";
														}
														
														address = address + node.county;
													} 
													
													if ((node.postcode) != "") {
														if (address != "") {
															address = address + ",\n";
														}
														
														address = address + node.postcode;
													} 
													
													$("#adhocaddress").val(address);
													
												} else {
													$("#adhocaddress").val("");
												}
											}
										);
								}
							);
						
						$("#checkoutdialog").dialog({
								modal: true,
								autoOpen: false,
								title: "Customer Check Out",
								width: 810,
								buttons: {
									Ok: function() {
										$(this).dialog("close");
										
										$("#confirmcheckoutdialog .confirmdialogbody").html("You are about to check out this stock item.<br>Are you sure ?");
										$("#confirmcheckoutdialog").dialog("open");
									},
									Cancel: function() {
										$(this).dialog("close");
									}
								}
							});
							
						$("#movedialog").dialog({
								modal: true,
								autoOpen: false,
								title: "Move Stock Item",
								width: 110,
								height: 180,
								buttons: {
									Ok: function() {
										$(this).dialog("close");
										
										$("#confirmmovedialog .confirmdialogbody").html("You are about to move this stock item.<br>Are you sure ?");
										$("#confirmmovedialog").dialog("open");
									},
									Cancel: function() {
										$(this).dialog("close");
									}
								}
							});
							
						$("#emaildialog").dialog({
								modal: true,
								autoOpen: false,
								title: "Email",
								width: 810,
								buttons: {
									Ok: function() {
										$(this).dialog("close");
										
										callAjax(
												"emaildocument.php", 
												{ 
													id: currentID,
													emailaddress: $("#email_to").val(),
													body: tinyMCE.get("email_message").getContent(),
													subject: "Despatch Note"
												},
												function(data) {
												}
											);
									},
									Cancel: function() {
										$(this).dialog("close");
									}
								}
							});
					}
				);
				
			function checkin(pk) {
				currentID = pk;
				
				$("#confirmcheckindialog .confirmdialogbody").html("You are about to check in this stock item.<br>Are you sure ?");
				$("#confirmcheckindialog").dialog("open");
			}
				
			function checkout(pk) {
				currentID = pk;
				
				$("#coaddressid").val("0");
				$("#cocustomerid").val("0");
				$("#adhocaddress").val("");
				$("#coexpecteddate").val("<?php echo date("d/m/Y"); ?>");
				$("#ourref").val("");
				$("#yourref").val("");
				$("#reason").val(""); 
				
				$("#checkoutdialog").dialog("open");
		    } 	
				
			function movestock(pk) {
				currentID = pk;
				
				$("#movedialog").dialog("open");
		    } 	
		    
		    function despatch(pk) {
		    	window.open("createdespatch.php?id=" + pk);
		    }
		    
		    function confirmcheckout() {
		    	$("#confirmcheckoutdialog").dialog("close");

				post("editform", "checkout", "submitframe", 
						{ 
							checkout_stockitemid: currentID, 
							checkout_customerid: $("#cocustomerid").val(),
							checkout_address: $("#adhocaddress").val(),
							checkout_ourref: $("#ourref").val(),
							checkout_yourref: $("#yourref").val(),
							checkout_reason: $("#reason").val(),
							checkout_expecteddate: $("#coexpecteddate").val()
						}
					);
		    }
		    
		    function confirmcheckin() {
		    	$("#confirmcheckindialog").dialog("close");
		    	
				post("editform", "checkin", "submitframe", 
						{ 
							checkin_stockitemid: currentID, 
						}
					);
		    }
		    
		    function confirmstockmovement() {
		    	$("#confirmmovedialog").dialog("close");
		    	
				post("editform", "move", "submitframe", 
						{ 
							move_stockitemid: currentID, 
							move_warehouseid: $("#mowarehouseid").val()
						}
					);
		    }
		    
		    function showDocument(pk) {
		    	window.open("viewdocuments.php?id=" + pk)
		    }
		    
		    function regenerate(pk) {
				post("editform", "regenerate", "submitframe", 
						{ 
							checkout_stockitemid: pk
						}
					);
		    }
		    
		    function emailDocument(pk) {
		    	currentID = pk;
		    	
		    	$("#emaildialog").dialog("open");
		    }
		    
			function presignedFormatter(el, cval, opts) {
				if (opts.presigneddocumentid != null) {
					return "<a href='javascript: showDocument(" + opts.presigneddocumentid + ")'><img src='images/document.gif' /></a><a href='javascript: emailDocument(" + opts.presigneddocumentid + ")'><img height=16 src='images/mail.png' /></a>";
				}
				
				return "";
		    } 	
		    
			function signedFormatter(el, cval, opts) {
				if (opts.signeddocumentid != null) {
					return "<a href='javascript: showDocument(" + opts.signeddocumentid + ")'><img src='images/document.gif' /></a><a href='javascript: emailDocument(" + opts.signeddocumentid + ")'><img height=16 src='images/mail.png' /></a>";
				}
				
				return "";
		    } 	
<?php
		}
	}
	
	$crud = new StockItemCrud();
	$crud->title = "Stock";
	$crud->table = "{$_SESSION['DB_PREFIX']}stockitem";
	$crud->dialogwidth = 400;
	
	if ($_SESSION['WAREHOUSE_ID'] != null && $_SESSION['WAREHOUSE_ID'] != "0") {
		$crud->sql = 
				"SELECT A.*, AA.presigneddocumentid, AA.signeddocumentid, AA.signeddate, AA.checkedoutdate, " .
				"AB.expectedreturndate, B.name AS customername, D.name AS warehousename, " .
				"AA.address, C.warehouseid " .
				"FROM {$_SESSION['DB_PREFIX']}stockitem A " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}despatchheader AA " .
				"ON AA.id = A.despatchheaderid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}despatchitem AB " .
				"ON AB.despatchid = AA.id " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}customers B " .
				"ON B.id = AA.customerid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}warehousestock C " .
				"ON C.stockitemid = A.id " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}warehouses D " .
				"ON D.id = C.warehouseid " .
				"WHERE A.stockid = " . $_GET['id'] . " " .
				"AND C.warehouseid = " . $_SESSION['WAREHOUSE_ID'] . " " .
				"ORDER BY A.serialnumber";
		
	} else {
		$crud->sql = 
				"SELECT A.*, AA.presigneddocumentid, AA.signeddocumentid, AA.signeddate, AA.checkedoutdate, " .
				"AB.expectedreturndate, B.name AS customername, D.name AS warehousename, " .
				"AA.address, C.warehouseid " .
				"FROM {$_SESSION['DB_PREFIX']}stockitem A " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}despatchheader AA " .
				"ON AA.id = A.despatchheaderid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}despatchitem AB " .
				"ON AB.despatchid = AA.id " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}customers B " .
				"ON B.id = AA.customerid " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}warehousestock C " .
				"ON C.stockitemid = A.id " .
				"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}warehouses D " .
				"ON D.id = C.warehouseid " .
				"WHERE A.stockid = " . $_GET['id'] . " " .
				"ORDER BY A.serialnumber";
	}
			
	$crud->messages = array(
			array('id'		  => 'checkin_stockitemid'),
			array('id'		  => 'checkout_stockitemid'),
			array('id'		  => 'checkout_customerid'),
			array('id'		  => 'checkout_address'),
			array('id'		  => 'checkout_ourref'),
			array('id'		  => 'checkout_yourref'),
			array('id'		  => 'checkout_reason'),
			array('id'		  => 'checkout_expecteddate'),
			array('id'		  => 'move_stockitemid'),
			array('id'		  => 'move_warehouseid')
		);
		
	$crud->subapplications = array(
			array(
				'title'		  => 'Check Out',
				'imageurl'	  => 'images/stock.png',
				'script' 	  => 'checkout'
			),
			array(
				'title'		  => 'Check In',
				'imageurl'	  => 'images/stock.png',
				'script' 	  => 'checkin'
			),
			array(
				'title'		  => 'Move Stock',
				'imageurl'	  => 'images/stock.png',
				'script' 	  => 'movestock'
			)
		);
		
	if (isUserInRole("SUPERADMIN")) {
		$crud->subapplications[] = array(
				'title'		  => 'Regenerate',
				'imageurl'	  => 'images/reset.png',
				'script' 	  => 'regenerate'
			);
	}
	$crud->columns = array(
			array(
				'name'       => 'id',
				'viewname'   => 'uniqueid',
				'length' 	 => 6,
				'showInView' => false,
				'filter'	 => false,
				'bind' 	 	 => false,
				'editable' 	 => false,
				'pk'		 => true,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'stockid',
				'length' 	 => 6,
				'showInView' => false,
				'filter'	 => false,
				'editable' 	 => false,
				'default'	 => $_GET['id'],
				'label' 	 => 'Stock ID'
			),
			array(
				'name'       => 'serialnumber',
				'length' 	 => 30,
				'label' 	 => 'Serial Number'
			),
			array(
				'name'       => 'warehouseid',
				'type'		 => 'DATACOMBO',
				'table'		 => 'warehouses',
				'table_id'	 => 'id',
				'table_name' => 'name',
				'bind'		 => false,
				'alias'		 => 'warehousename',
				'length' 	 => 30,
				'required'	 => true,
				'label' 	 => 'Current Location'
			),
			array(
				'name'       => 'customername',
				'length' 	 => 30,
				'bind'		 => false,
				'editable' 	 => false,
				'label' 	 => 'Customer'
			),
			array(
				'name'       => 'presigned',
				'formatter'  => 'presignedFormatter',
				'bind'	 	 => false,
				'length' 	 => 10,
				'editable' 	 => false,
				'label' 	 => 'Presigned Note'
			),
			array(
				'name'       => 'signed',
				'formatter'  => 'signedFormatter',
				'length' 	 => 10,
				'bind'	 	 => false,
				'editable' 	 => false,
				'label' 	 => 'Signed Note'
			),
			array(
				'name'       => 'checkedoutdate',
				'datatype'   => 'timestamp',
				'bind'	 	 => false,
				'length' 	 => 20,
				'editable' 	 => false,
				'label' 	 => 'Last Checked Out Date'
			),
			array(
				'name'       => 'expectedreturndate',
				'datatype'   => 'timestamp',
				'bind'	 	 => false,
				'length' 	 => 20,
				'editable' 	 => false,
				'label' 	 => 'Expected Return Date'
			),
			array(
				'name'       => 'signeddate',
				'datatype'   => 'timestamp',
				'bind'	 	 => false,
				'length' 	 => 20,
				'editable' 	 => false,
				'label' 	 => 'Signed Date'
			),
			array(
				'name'       => 'checkedindate',
				'datatype'   => 'timestamp',
				'length' 	 => 20,
				'bind'	 	 => false,
				'editable' 	 => false,
				'label' 	 => 'Last Checked In Date'
			),
			array(
				'name'       => 'presigneddocumentid',
				'hidden' 	 => true,
				'editable'	 => false,
				'bind'	 	 => false,
				'length' 	 => 20,
				'label' 	 => 'Presigned Document ID'
			),
			array(
				'name'       => 'signeddocumentid',
				'hidden' 	 => true,
				'editable'	 => false,
				'bind'	 	 => false,
				'length' 	 => 20,
				'label' 	 => 'Signed Document ID'
			),
			array(
				'name'       => 'straddress',
				'length' 	 => 70,
				'editable'   => false,
				'bind'		 => false,
				'type'		 => 'DERIVED',
				'function'	 => 'fullAddress',
				'label' 	 => 'Address'
			)
		);
		
	$crud->run();
	
?>