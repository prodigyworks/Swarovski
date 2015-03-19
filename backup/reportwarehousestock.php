<?php
	require('pdfreport.php');
	
	$warehouse = "";
	
	function newPage($pdf, $warehouse) {
		$pdf->AddPage();
		
//		$pdf->Image("images/pestokilllogomini.png", 170.6, 1);
//		$pdf->Image("images/footer.png", 54, 280);
		$pdf->addHeading( 15, 13, "Warehouse : ", $warehouse);
	    $pdf->SetFont('Arial','', 6);
			
		$cols=array( 
					"Stock"    => 51,
		             "Serial Number"  => 30,
		             "Customer"  => 40,
		             "Checked Out Date"     => 23,
		             "Expected Return Date"     => 23,
		             "Checked In Date"     => 23
				);
	
		$pdf->addCols(20, $cols);
		$cols=array( 
					"Stock"    => "L",
		             "Serial Number"  => "L",
		             "Customer"  => "L",
		             "Checked Out Date"     => "L",
		             "Expected Return Date"     => "L",
		             "Checked In Date"     => "L"
				);
		$pdf->addLineFormat( $cols);
		
		return 29;
	}
	
	$pdf = new PDFReport( 'P', 'mm', 'A4' );

	$sql =  "SELECT A.*, B.name AS customername, D.name AS warehousename, E.name AS stockname, " .
			"DATE_FORMAT(AB.checkedoutdate, '%d/%m/%Y') AS checkedoutdate, " .
			"DATE_FORMAT(AC.expectedreturndate, '%d/%m/%Y') AS expectedreturndate, " .
			"DATE_FORMAT(AB.checkedindate, '%d/%m/%Y') AS checkedindate " .
			"FROM {$_SESSION['DB_PREFIX']}stockitem A " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}despatchheader AB " .
			"ON AB.id = A.despatchheaderid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}despatchitem AC " .
			"ON AC.despatchid = AB.id " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}customers B " .
			"ON B.id = AB.customerid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}warehousestock C " .
			"ON C.stockitemid = A.id " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}warehouses D " .
			"ON D.id = C.warehouseid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}stock E " .
			"ON E.id = A.stockid " .
			"ORDER BY C.warehouseid, A.serialnumber";
	$result = mysql_query($sql);
	
	if ($result) {
		while (($member = mysql_fetch_assoc($result))) {
			if ($warehouse != $member['warehousename']) {
				$y = newPage($pdf, $member['warehousename']);
			}
			
			$warehouse = $member['warehousename'];
			$line=array( "Stock"    => $member['stockname'] . " ",
			             "Serial Number"  => $member['serialnumber'] . " ",
			             "Customer"  => $member['customername'] . " ",
			             "Checked Out Date"     => $member['checkedoutdate'] . " ",
			             "Expected Return Date"     => $member['expectedreturndate'] . " ",
			             "Checked In Date"     => $member['checkedindate'] . " "
		             );
			             
			$size = $pdf->addLine( $y, $line );
			$y += $size;
			
			if ($y > 260) {
				$y = newPage($pdf, $member['warehousename']);
			}
		}
		
	} else {
		logError($sql . " - " . mysql_error());
	}
	
	$pdf->Output();
?>