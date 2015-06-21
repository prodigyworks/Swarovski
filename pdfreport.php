<?php
	require('fpdf.php');
	
	class PDFReport extends FPDF
	{
		// private variables
		var $colonnes;
		var $format;
		var $angle=0;
		
		
		
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
		
		function addHeading( $x1, $y1, $heading, $value) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','B',8);
		    $length = $this->GetStringWidth( $heading );
		    $this->Cell( $length, 2, $heading);
		    
		    $this->SetXY( $x1 + 36, $y1);
		    $this->SetFont('Arial','',7);
		    $length = $this->GetStringWidth( $value );
		    $this->Cell( $length, 2, $value);
		}

		function addParagraph( $x1, $y1, $value, $fontsize = 7) {
		    //Positionnement en bas
		    $this->SetXY( $x1, $y1 );
		    $this->SetFont('Arial','',$fontsize);
		    $length = $this->GetStringWidth( $value );
		    $this->Cell( $length, 2, $value);
		}
		
		function addCols($y1, $tab )
		{
		    global $colonnes;
		    
		    $r1  = 10;
		    $r2  = $this->w - ($r1 * 2) ;
		    $y2  = $this->h - 25 - $y1;
		    $this->SetXY( $r1, $y1 );
		    $this->Rect( $r1, $y1, $r2, $y2, "D");
		    $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
		    $colX = $r1;
		    $colonnes = $tab;
		    while ( list( $lib, $pos ) = each ($tab) )
		    {
		        $this->SetXY( $colX, $y1+2 );
		        $this->Cell( $pos, 1, $lib, 0, 0, "C");
		        $colX += $pos;
		        $this->Line( $colX, $y1, $colX, $y1+$y2);
		    }
		}
		
		function addLineFormat( $tab )
		{
		    global $format, $colonnes;
		    
		    while ( list( $lib, $pos ) = each ($colonnes) )
		    {
		        if ( isset( $tab["$lib"] ) )
		            $format[ $lib ] = $tab["$lib"];
		    }
		}
		
		function addLine( $ligne, $tab )
		{
		    global $colonnes, $format;
		
		    $ordonnee     = 10;
		    $maxSize      = $ligne;
		
		    reset( $colonnes );
		    while ( list( $lib, $pos ) = each ($colonnes) )
		    {
		        $longCell  = $pos -2;
		        $texte     = $tab[ $lib ];
		        $length    = $this->GetStringWidth( $texte );
		        $tailleTexte = $this->sizeOfText( $texte, $length );
		        $formText  = $format[ $lib ];
		        $this->SetXY( $ordonnee, $ligne-1);
		        $this->MultiCell( $longCell, 4 , $texte, 0, $formText);
		        if ( $maxSize < ($this->GetY()  ) )
		            $maxSize = $this->GetY() ;
		        $ordonnee += $pos;
		    }
		    return ( $maxSize - $ligne );
		}
		
		function AddPage($orientation='', $size='') {
			parent::AddPage($orientation, $size);
			
			$this->addParagraph(8, 5, $_SESSION['title'], 12);
			$this->addParagraph(165, 5, "Swarovski Optiks", 12);
		}
	
		function __construct($orientation, $metric, $size) {
	        parent::__construct($orientation, $metric, $size);
	        
			require_once('system-config.php');
			
//			start_db();
//			initialise_db();
		}
	}
	                  
?>