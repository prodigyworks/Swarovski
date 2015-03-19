<?php
	require('despatchreportlib.php');
	
	$pdf = new DespatchReport( 'P', 'mm', 'A4', $_GET['id']);
	$pdf->Output();
?>