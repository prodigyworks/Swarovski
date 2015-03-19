<?php
	require('pdfreport.php');
	
	$customername = "";
	
	function newPage($pdf, $customername) {
		$pdf->AddPage("Customer Stock Report");
		
//		$pdf->Image("images/pestokilllogomini.png", 170.6, 1);
//		$pdf->Image("images/footer.png", 54, 280);
		$pdf->addHeading( 15, 13, "Customer : ", $customername);
	    $pdf->SetFont('Arial','', 6);
			
		$cols=array( 
					"Stock"    => 51,
		             "Serial Number"  => 30,
		             "Warehouse"  => 40,
		             "Checked Out Date"     => 23,
		             "Expected Return Date"     => 23,
		             "Checked In Date"     => 23
				);
	
		$pdf->addCols(20, $cols);
		$cols=array( 
					"Stock"    => "L",
		             "Serial Number"  => "L",
		             "Warehouse"  => "L",
		             "Checked Out Date"     => "L",
		             "Expected Return Date"     => "L",
		             "Checked In Date"     => "L"
				);
		$pdf->addLineFormat( $cols);
		
		return 29;
	}
	
	$pdf = new PDFReport( 'P', 'mm', 'A4' );
	
	if (isset($_POST['customerid']) && $_POST['customerid'] != 0) {
		$where = "WHERE B.id = " . $_POST['customerid'] . " ";
		
	} else {
		$where = "";
	}

	$sql =  "SELECT A.*, B.name AS customername, D.name AS warehousename, E.name AS stockname, " .
			"DATE_FORMAT(AB.checkedoutdate, '%d/%m/%Y') AS checkedoutdate, " .
			"DATE_FORMAT(AC.expectedreturndate, '%d/%m/%Y') AS expectedreturndate, " .
			"DATE_FORMAT(AB.checkedindate, '%d/%m/%Y') AS checkedindate " .
			"FROM {$_SESSION['DB_PREFIX']}stockitem A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}despatchheader AB " .
			"ON AB.id = A.despatchheaderid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}despatchitem AC " .
			"ON AC.despatchid = AB.id " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}customers B " .
			"ON B.id = AB.customerid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}warehousestock C " .
			"ON C.stockitemid = A.id " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}warehouses D " .
			"ON D.id = C.warehouseid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}stock E " .
			"ON E.id = A.stockid " .
			$where .
			"ORDER BY B.name, D.name, A.serialnumber";
	$result = mysql_query($sql);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			if ($customername != $member['customername']) {
				$y = newPage($pdf, $member['customername']);
			}
			
			$customername = $member['customername'];
			$line=array( "Stock"    => $member['stockname'] . " ",
			             "Serial Number"  => $member['serialnumber'] . " ",
			             "Warehouse"  => $member['warehousename'] . " ",
			             "Checked Out Date"     => $member['checkedoutdate'] . " ",
			             "Expected Return Date"     => $member['expectedreturndate'] . " ",
			             "Checked In Date"     => $member['checkedindate'] . " "
		             );
			             
			$size = $pdf->addLine( $y, $line );
			$y += $size;
			
			if ($y > 260) {
				$y = newPage($pdf, $member['customername']);
			}
		}
		
	} else {
		logError($sql . " - " . mysql_error());
	}
	
	$pdf->Output();
?>