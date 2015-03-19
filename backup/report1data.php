<?php
	/** Error reporting */
	error_reporting(E_ALL);
	
	/** Include path **/
	/** PHPExcel */
	include 'system-db.php';
	include 'PHPExcel.php';
	include 'PHPExcel/Writer/Excel2007.php';
	
	start_db();
	initialise_db();
	
	$year = $_POST['year'];
	$month = $_POST['month'];
	
	header('Content-type: application/excel');
	header('Content-disposition: attachment; filename="' . getMonthName($month) . ' ' . $year. '.xlsx;');
	
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();
	
	// Set properties
	$objPHPExcel->getProperties()->setCreator("John A Cove");
	$objPHPExcel->getProperties()->setLastModifiedBy("John A Cove");
	$objPHPExcel->getProperties()->setTitle(getMonthName($month) . ' ' . $year);
	$objPHPExcel->getProperties()->setSubject(getMonthName($month) . ' ' . $year);
	$objPHPExcel->getProperties()->setDescription(getMonthName($month) . ' ' . $year);
	
	$objPHPExcel->setActiveSheetIndex(0);
	
	$qry = "SELECT A.*, DATE_FORMAT(A.approveddate, '%d/%m/%Y') AS approvaldate, B.firstname, B.lastname, C.name " .
			"FROM datatech_quoteheader A " .
			"INNER JOIN datatech_members B " .
			"ON B.member_id = A.contactid " .
			"INNER JOIN datatech_sites C " .
			"ON C.id = A.siteid " .
			"WHERE YEAR(A.approveddate) = $year " .
			"AND MONTH(A.approveddate) = $month " .
			"ORDER BY A.approveddate";
	$result = mysql_query($qry);
	
	if (! $result) {
		die($qry . " - " . mysql_error());
	}
	
	$row = 1;
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(8);
	
	$headerArray = array(	
			'font' => array(		'bold' => true),
			'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_THIN
		    )
		  )
		);
	
	$styleArray = array(
		  'borders' => array(
		    'allborders' => array(
		      'style' => PHPExcel_Style_Border::BORDER_THIN
		    )
		  )
		);
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(120);
	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);
	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(19);
	$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(14);
	$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(50);
	
	$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'JOB NUMBER');
	$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'VERIFIED DATE');
	$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'COST');
	$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'COMMENTS');
	$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'EXPEDITED');
	$objPHPExcel->getActiveSheet()->SetCellValue('F1', 'EMERGENCY');
	$objPHPExcel->getActiveSheet()->SetCellValue('G1', 'COST');
	$objPHPExcel->getActiveSheet()->SetCellValue('H1', 'PM');
	$objPHPExcel->getActiveSheet()->SetCellValue('I1', 'COST CODE');
	$objPHPExcel->getActiveSheet()->SetCellValue('J1', 'SG-PO');
	$objPHPExcel->getActiveSheet()->SetCellValue('K1', 'SITE');
	$objPHPExcel->getActiveSheet()->SetCellValue('L1', 'QA\'d');
	$objPHPExcel->getActiveSheet()->SetCellValue('M1', 'RACK DEPENDANT');
	$objPHPExcel->getActiveSheet()->SetCellValue('N1', 'INVOICE Y/N');
	$objPHPExcel->getActiveSheet()->SetCellValue('O1', 'COMMENT');
	
	$objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	$objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	$objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	
	$numberEmergency = 0;
	$numberExpedite = 0;
	$missingPO = 0;
	$weeklyTotalArray = array();
	$colourArray = array("FF95B3D7", "FFDA9694", "FFC4D79B", "FFB1A0C7", "FF92CDDC");
	$week = date("W", strtotime($year . "-" . $month . "-01"));	

	for ($i = 0; $i < 5; $i++) {
		$weeklyTotalArray[$i] = array(
				"total" 	=> 0,
				"expedite"	=> 0,
				"emergency"	=> 0,
				"charge"	=> 0,
				"missingpo"	=> 0
			);
	}	

	while (($member = mysql_fetch_assoc($result))) {
		$row++;
		
		
		$qry = "SELECT A.* " .
				"FROM datatech_quoteitem A " .
				"WHERE A.headerid = " . $member['id'] . " " .
				"ORDER BY A.id";
		$itemresult = mysql_query($qry);
		
		if (! $itemresult) {
			die($qry . " - " . mysql_error());
		}
		
		$charge = 0;
		$total = 0;
		$expedite = false;
		$emergency = false;
		$description = "";
		
		while (($itemmember = mysql_fetch_assoc($itemresult))) {
			$colour = date("W", strtotime($member['approveddate'])) - $week;
			
			if ($itemmember['description'] == "Expedite Charge") {
				$expedite = true;
				$charge = $charge + $itemmember['total'];
				$numberExpedite++;
				$weeklyTotalArray[$colour]['charge'] = $weeklyTotalArray[$colour]['charge'] + $itemmember['total']; 
				$weeklyTotalArray[$colour]['expedite']++; 
				
			} else if ($itemmember['description'] == "Emergency Charge") {
				$emergency = true;
				$numberEmergency++;
				$charge = $charge + $itemmember['total'];
				$weeklyTotalArray[$colour]['charge'] = $weeklyTotalArray[$colour]['charge'] + $itemmember['total']; 
				$weeklyTotalArray[$colour]['emergency']++; 
				
			} else {
				if ($description != "") {
					$description .= " / ";
				}
				
				$description .= $itemmember['qty'] . " x " . $itemmember['description'];
				
				$total = $total + $itemmember['total'];
				$weeklyTotalArray[$colour]['total'] = $weeklyTotalArray[$colour]['total'] + $itemmember['total']; 
			}
		}
		
		
		$objPHPExcel->getActiveSheet()->SetCellValue('A' . $row, $member['prefix'] . sprintf("%04d", $member['id']));
		$objPHPExcel->getActiveSheet()->SetCellValue('B' . $row, $member['approvaldate']);
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . $row, (number_format($total, 2, '.', '')));
		$objPHPExcel->getActiveSheet()->SetCellValue('D' . $row, $description);
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . $row, ($expedite == true ? "Y" : " "));
		$objPHPExcel->getActiveSheet()->SetCellValue('F' . $row, ($emergency == true ? "Y" : " "));
		$objPHPExcel->getActiveSheet()->SetCellValue('G' . $row, ($expedite == true || $emergency ? (number_format($charge, 2, '.', '')) : ""));
		$objPHPExcel->getActiveSheet()->SetCellValue('H' . $row, $member['firstname'] . " " . $member['lastname']);
		$objPHPExcel->getActiveSheet()->SetCellValue('I' . $row, getCostCodeName($member['costcode']));
		$objPHPExcel->getActiveSheet()->SetCellValue('J' . $row, $member['sungardpo']);
		$objPHPExcel->getActiveSheet()->SetCellValue('K' . $row, $member['name']);
		$objPHPExcel->getActiveSheet()->SetCellValue('L' . $row, $member['status'] == 'Q' ? "Y" : " ");
		$objPHPExcel->getActiveSheet()->SetCellValue('M' . $row, ($member['requiredbymode'] == "R" ? "Y" : ($member['requiredbymode'] == "N" ? "N" : "TBA")));
		$objPHPExcel->getActiveSheet()->SetCellValue('N' . $row, '');
		$objPHPExcel->getActiveSheet()->SetCellValue('O' . $row, '');
		
		$objPHPExcel->getActiveSheet()->getStyle('E' . $row . ':F' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		$objPHPExcel->getActiveSheet()->getStyle('E' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$objPHPExcel->getActiveSheet()->getStyle('G' . $row)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		
		$objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('B' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('B' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('C' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('C' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('D' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('D' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('E' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('E' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('F' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('G' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('G' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('H' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('H' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('I' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('I' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		
		if ($member['sungardpo'] == null || trim($member['sungardpo']) == "") {
			$missingPO++;
			$weeklyTotalArray[$colour]['missingpo']++; 
			$objPHPExcel->getActiveSheet()->getStyle('J' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('J' . $row)->getFill()->getStartColor()->setARGB('FFFF0000');
			
		} else {
			$objPHPExcel->getActiveSheet()->getStyle('J' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle('J' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		}
		
		$objPHPExcel->getActiveSheet()->getStyle('K' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('K' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('L' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('L' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('M' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('M' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('N' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('N' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
		$objPHPExcel->getActiveSheet()->getStyle('O' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('O' . $row)->getFill()->getStartColor()->setARGB($colourArray[$colour]);
	}


	$objPHPExcel->getActiveSheet()->getStyle('A1:O' . $row)->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('A1:O1')->applyFromArray($headerArray);
	
	$objPHPExcel->getActiveSheet()->setTitle(getMonthName($month) . " $year");
	
	for ($i = 0; $i < 5; $i++) {
		$objPHPExcel->getActiveSheet()->SetCellValue('C' . ($row + ($i + 2)), number_format($weeklyTotalArray[$i]['total'], 2, '.', ''));
		$objPHPExcel->getActiveSheet()->SetCellValue('G' . ($row + ($i + 2)), number_format($weeklyTotalArray[$i]['charge'], 2, '.', ''));
		$objPHPExcel->getActiveSheet()->SetCellValue('E' . ($row + ($i + 2)), $weeklyTotalArray[$i]['expedite']);
		$objPHPExcel->getActiveSheet()->SetCellValue('F' . ($row + ($i + 2)), $weeklyTotalArray[$i]['emergency']);
		$objPHPExcel->getActiveSheet()->SetCellValue('J' . ($row + ($i + 2)), $weeklyTotalArray[$i]['missingpo']);
		$objPHPExcel->getActiveSheet()->getStyle('A' . ($row + ($i + 2)) . ':O' . ($row + ($i + 2)))->applyFromArray($headerArray);
		$objPHPExcel->getActiveSheet()->getStyle('E' . ($row + ($i + 2)))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$objPHPExcel->getActiveSheet()->getStyle('F' . ($row + ($i + 2)))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$objPHPExcel->getActiveSheet()->getStyle('G' . ($row + ($i + 2)))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
		$objPHPExcel->getActiveSheet()->getStyle('J' . ($row + ($i + 2)))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);

		$objPHPExcel->getActiveSheet()->getStyle('C' . ($row + ($i + 2)))->getFill()->getStartColor()->setARGB($colourArray[$i]);
		$objPHPExcel->getActiveSheet()->getStyle('C' . ($row + ($i + 2)))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('G' . ($row + ($i + 2)))->getFill()->getStartColor()->setARGB($colourArray[$i]);
		$objPHPExcel->getActiveSheet()->getStyle('G' . ($row + ($i + 2)))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('E' . ($row + ($i + 2)))->getFill()->getStartColor()->setARGB($colourArray[$i]);
		$objPHPExcel->getActiveSheet()->getStyle('E' . ($row + ($i + 2)))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('F' . ($row + ($i + 2)))->getFill()->getStartColor()->setARGB($colourArray[$i]);
		$objPHPExcel->getActiveSheet()->getStyle('F' . ($row + ($i + 2)))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle('J' . ($row + ($i + 2)))->getFill()->getStartColor()->setARGB($colourArray[$i]);
		$objPHPExcel->getActiveSheet()->getStyle('J' . ($row + ($i + 2)))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
	}
	
	$objPHPExcel->getActiveSheet()->SetCellValue('A' . ($row + 9), "Totals");
	$objPHPExcel->getActiveSheet()->SetCellValue('C' . ($row + 9), "=SUM(B1:B" . $row . ")");
	$objPHPExcel->getActiveSheet()->SetCellValue('G' . ($row + 9), "=SUM(F1:F" . $row . ")");
	$objPHPExcel->getActiveSheet()->SetCellValue('E' . ($row + 9), $numberExpedite);
	$objPHPExcel->getActiveSheet()->SetCellValue('F' . ($row + 9), $numberEmergency);
	$objPHPExcel->getActiveSheet()->SetCellValue('J' . ($row + 9), $missingPO);
	$objPHPExcel->getActiveSheet()->getStyle('A' . ($row + 9) . ':O' . ($row + 9))->applyFromArray($headerArray);
	$objPHPExcel->getActiveSheet()->getStyle('E' . ($row + 9))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	$objPHPExcel->getActiveSheet()->getStyle('F' . ($row + 9))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	$objPHPExcel->getActiveSheet()->getStyle('G' . ($row + 9))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	$objPHPExcel->getActiveSheet()->getStyle('J' . ($row + 9))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	
	$objPHPExcel->getActiveSheet()->SetCellValue('A' . ($row + 2), "Weekly Totals");
	
			
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save('php://output');
	
	function getMonthName($month) {
		if ($month == 1) return "January";
		if ($month == 2) return "February";
		if ($month == 3) return "March";
		if ($month == 4) return "April";
		if ($month == 5) return "May";
		if ($month == 6) return "June";
		if ($month == 7) return "July";
		if ($month == 8) return "August";
		if ($month == 9) return "September";
		if ($month == 10) return "October";
		if ($month == 11) return "November";
		if ($month == 12) return "December";
	}
	
	function getCostCodeName($costcode) {
		if ($costcode == "CAPEXCCF") return "CAPEX DEAL RELATED CCF";
		if ($costcode == "CAPEXBESPOKE") return "CAPEX - BESPOKE";
		if ($costcode == "OPEXBESPOKE") return "OPEX - BESPOKE";
		if ($costcode == "OPEXCUSTOMERPO") return "OPEX - Customer PO";
		if ($costcode == "OPEXCONSULTANCY") return "OPEX - Consultancy";
	}
?>
