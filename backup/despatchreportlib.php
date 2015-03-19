<?php
	require_once('fpdf.php');
	require_once('system-db.php');
	
	class DespatchReport extends FPDF
	{
		// private variables
		var $colonnes;
		var $format;
		var $angle=0;
		
		function __construct($orientation, $metric, $size, $id) {
	        parent::__construct($orientation, $metric, $size);
	                  
			//Include database connection details
			
			start_db();
		
			$this->AddPage();
			
			$margin = 7;
			$sql = "SELECT A.*, DATE_FORMAT(A.signeddate, '%d/%m/%Y') AS signeddate,  " .
					"AB.serialnumber, B.name AS customername, D.name AS warehousename, " .
					"A.address, AC.name AS stockname, DATE_FORMAT(AA.expectedreturndate, '%d/%m/%Y') AS expectedreturndate  " .
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
					"WHERE A.id = $id " .
					"ORDER BY AB.serialnumber";
			$result = mysql_query($sql);
			
			if ($result) {
				while (($member = mysql_fetch_assoc($result))) {
					$this->addAddress( $member['customername'],
					                  $member['address'], 90, 79);
					                  
					                  
					$this->addHeading(45, 60 , "CONTRACT FOR LOAN OF DEMONSTRATION STOCK");
					$this->addLine(45, 70, "Our Reference");
					$this->addLine(45, 74, $member['ourref']);
					$this->addLine(130, 70, "Your Reference");
					$this->addLine(130, 74, $member['yourref']);
					$this->addHeading(45, 108 , "STOCK");
					$this->addLine(45, 118, $member['stockname']);
					$this->addHeading(120, 108 , "SERIAL NUMBER");
					$this->addLine(120, 118, $member['serialnumber']);
					$this->addAddress( "REASON FOR LOAN", $member['reason'], 90, 126);
					$this->addHeading(90, 162, "TERMS OF LOAN");
					$this->addLine(45, 170, "FROM  " . date("d/m/Y") . " UNTIL " . $member['expectedreturndate'] . " – which as discussed will generate an automatic ");
					$this->addLine(45, 174, "reminder or if the loan relates to a repair – Until your own unit is back from the ");
					$this->addLine(45, 178, "factory");
					$this->addLine(45, 184, "BY ACCEPTING AND SIGNING THIS DOCUMENT YOU AGREE TO BE ");
					$this->addLine(45, 188, "RESPONSIBLE FOR THE ITEM(S) THAT WILL BE SENT TO YOU ON LOAN");
					$this->addHeading(45, 220, "DATE CONTRACT SIGNED");
					$this->addHeading(120, 220, "SIGNATURE OF BORROWER");
					$this->addLine(45, 230, $member['signeddate']);
					
					if ($member['imageid'] != null && $member['imageid'] != 0) {
						$this->Image("uploads/signature_" . $member['imageid'] . ".png", 120, 225);
					}
		 		}
				
			} else {
				logError($sql . " - " . mysql_error());
			}
		
			$this->Image("images/swarovski.png", 134.6, 1);
		
			$this->addSubAddress(" ", 
							  "Swarovski UK Ltd\n" .
			                  "Perrywood Business Pk\n" .
			                  "Salfords, Surrey\n" .
			                  "RH1 5JQ" , 10, 260);
		
			$this->addSubAddress(" ",
							  "Phone: 01737-856812\n" .
			                  "Fax: 01737-856885\n" .
			                  "info@swarovskioptik.co.uk\n" .
			                  "www.swarovskioptik.com" , 170, 260);
		}

		// public functions
		function sizeOfText( $texte, $largeur )
		{
		    $index    = 0;
		    $nb_lines = 0;
		    $loop     = TRUE;
		    while ( $loop )
		    {
		        $pos = strpos($texte, "\n");
		        if (!$pos)
		        {
		            $loop  = FALSE;
		            $ligne = $texte;
		        }
		        else
		        {
		            $ligne  = substr( $texte, $index, $pos);
		            $texte = substr( $texte, $pos+1 );
		        }
		        $length = floor( $this->GetStringWidth( $ligne ) );
		        $res = 1 + floor( $length / $largeur) ;
		        $nb_lines += $res;
		    }
		    return $nb_lines;
		}
		
		// Company
		function addAddress( $nom, $adresse , $x1, $y1) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','B',10);
		    $length = $this->GetStringWidth( $nom );
		    $this->Cell( $length, 2, $nom);
		    $this->SetXY( $x1, $y1 + 4 );
		    $this->SetFont('Arial','',10);
		    
		    $length = $this->GetStringWidth( $adresse );
		    //Coordonnées de la société
		    $lignes = $this->sizeOfText( $adresse, $length) ;
		    $this->MultiCell($length, 3, $adresse);
		}
		
		// Company
		function addSubAddress( $nom, $adresse , $x1, $y1) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','',6);
		    $this->SetTextColor(200, 200, 200);
		    $length = $this->GetStringWidth( $nom );
		    $this->Cell( $length, 2, $nom);
		    $this->SetXY( $x1, $y1 + 4 );
		    $this->SetFont('Arial','',6);
		    $this->SetTextColor(200, 200, 200);
		    
		    $length = $this->GetStringWidth( $adresse );
		    //Coordonnées de la société
		    $lignes = $this->sizeOfText( $adresse, $length) ;
		    $this->MultiCell($length, 3, $adresse);
		}
		
		function addLine( $x1, $y1, $value, $font="Arial", $size = 10, $style="") {
		    $this->SetXY( $x1, $y1);
		    $this->SetFont($font,$style,$size);
		    $length = $this->GetStringWidth( $value );
		    $this->Cell( $length, 2, $value);
		}
		
		// Company
		function addHeading( $x1, $y1, $heading) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','BU',11);
		    $length = $this->GetStringWidth( $heading );
		    $this->Cell( $length, 2, $heading);
		}
	}
?>