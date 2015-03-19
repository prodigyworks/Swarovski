<?php
	require_once("system-db.php");
	require_once('despatchreportlib.php');
	
	start_db();
	
	require_once('signature.php');
	
	checkSignature();
	
	require_once("crud.php");
	require_once("confirmdialog.php");
	
	class DespatchCrud extends Crud {
		public function postHeaderEvent() {
			addSignatureForm();
		}
		
		public function postScriptEvent() {
?>
		  
			var currentID = 0;
		    
		    function showDocument(pk) {
		    	window.open("viewdocuments.php?id=" + pk)
		    }
		    
			function presignedFormatter(el, cval, opts) {
				if (opts.presigneddocumentid != null) {
					return "<a href='javascript: showDocument(" + opts.presigneddocumentid + ")'><img src='images/document.gif' /></a>";
				}
				
				return "";
		    } 	
		    
			function signedFormatter(el, cval, opts) {
				if (opts.signeddocumentid != null) {
					return "<a href='javascript: showDocument(" + opts.signeddocumentid + ")'><img src='images/document.gif' /></a>";
				}
				
				return "";
		    } 	
			
			/* Derived address callback. */
			function fullAddress(node) {
				if (node.address == null) {
					return "";
				}
				
				return node.address.replace(/\r\n/g, " ");
			}
			
			$(document).ready(
					function() {
				      	$('.sigPad').signaturePad();
					}
				);
				
			function despatchNote(pk) {
				window.open("despatchreport.php?id=" + pk);
			}
				
			function sign(pk) {
				currentID = pk;
				
				$("#signatureid").val(pk);
				$(".sigPad").fadeIn();
		    } 	
				
		    function despatch(pk) {
		    	window.open("createdespatch.php?id=" + pk);
		    }
<?php
		}
	}
	
	$crud = new DespatchCrud();
	$crud->title = "Stock";
	$crud->table = "{$_SESSION['DB_PREFIX']}despatchheader";
	$crud->dialogwidth = 400;
	$crud->allowEdit = false;
	$crud->allowRemove = false;
	$crud->allowAdd = false;
	
	if (! isUserInRole("ADMIN")) {
		$crud->sql = 
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
				"AND A.customerid = " . $_SESSION['CUSTOMER_ID'] . " " .
				"ORDER BY AB.serialnumber";
				
	} else {
		$crud->sql = 
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
				"ORDER BY AB.serialnumber";
	}
			
	$crud->messages = array(
			array('id'		  => 'checkin_stockitemid'),
			array('id'		  => 'checkout_stockitemid'),
			array('id'		  => 'checkout_customerid'),
			array('id'		  => 'checkout_addressid'),
			array('id'		  => 'checkout_expecteddate'),
			array('id'		  => 'move_stockitemid'),
			array('id'		  => 'move_warehouseid')
		);
		
	if (! isUserInRole("ADMIN")) {
		$crud->subapplications = array(
				array(
					'title'		  => 'Sign',
					'imageurl'	  => 'images/stock.png',
					'script' 	  => 'sign'
				),
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
				'name'       => 'stockname',
				'length' 	 => 30,
				'label' 	 => 'Stock Name'
			),
			array(
				'name'       => 'serialnumber',
				'length' 	 => 30,
				'label' 	 => 'Serial Number'
			),
			array(
				'name'       => 'warehousename',
				'length' 	 => 30,
				'label' 	 => 'Current Location'
			),
			array(
				'name'       => 'customername',
				'length' 	 => 30,
				'label' 	 => 'Customer'
			),
			array(
				'name'       => 'presigned',
				'formatter'  => 'presignedFormatter',
				'length' 	 => 10,
				'label' 	 => 'Presigned Note'
			),
			array(
				'name'       => 'signed',
				'formatter'  => 'signedFormatter',
				'length' 	 => 10,
				'label' 	 => 'Signed Note'
			),
			array(
				'name'       => 'checkedoutdate',
				'datatype'   => 'timestamp',
				'length' 	 => 20,
				'label' 	 => 'Last Checked Out Date'
			),
			array(
				'name'       => 'expectedreturndate',
				'datatype'   => 'timestamp',
				'length' 	 => 20,
				'label' 	 => 'Expected Return Date'
			),
			array(
				'name'       => 'checkedindate',
				'datatype'   => 'timestamp',
				'length' 	 => 20,
				'label' 	 => 'Last Checked In Date'
			),
			array(
				'name'       => 'presigneddocumentid',
				'hidden' 	 => true,
				'editable'	 => false,
				'length' 	 => 20,
				'label' 	 => 'Presigned Document ID'
			),
			array(
				'name'       => 'signeddocumentid',
				'hidden' 	 => true,
				'editable'	 => false,
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