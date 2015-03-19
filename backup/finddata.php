<?php
	require_once("sqlprocesstoarray.php");
	require_once('php-sql-parser.php');
	require_once('php-sql-creator.php');
	
	$json = new SQLProcessToArray();
	
	if (isset($_POST['sql'])) {
		$qry = $_POST['sql'];
	}
	if (isset($_GET['sql'])) {
		$qry = $_GET['sql'];
	}
	
	$qry = str_replace("\\'", "'", $qry);
		
	if (isset($_POST['orderby'])) {
		$parser = new PHPSQLParser($qry);

		if (isset($parser->parsed['ORDER'])) {
			unset($parser->parsed['ORDER']);
		}
		
		if ($_POST['orderby'] != "") {
			$parser->parsed['ORDER'] = array();
			$parser->parsed['ORDER'][] = 
				array(
						"expr_type" 		=> "colref",
						"base_expr"			=> $_POST['orderby'],
						"sub_tree"			=> "",
						"direction"         => $_POST['direction']
					);
					
			$creator = new PHPSQLCreator($parser->parsed);
			$qry = $creator->created;
		}
		
		if (isset($_POST['from'])) {
			$qry .= " LIMIT " . $_POST['from'] . ", " . $_POST['to'];
		}
	}
		
//	echo $qry;
		
	echo json_encode($json->fetch($qry));
?>