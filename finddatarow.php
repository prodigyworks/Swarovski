<?php
	require_once("sqlprocesstoarray.php");
	require_once('php-sql-parser.php');
	require_once('php-sql-creator.php');
	
	$json = new SQLProcessToArray();
	$id = $_POST['id'];
	
	if (isset($_POST['table'])) {
		$table = $_POST['table'];
	}
	
	$pkname = $_POST['pkname'];
	$qry = "";
	
	if (isset($_POST['sql'])) {
		$qry = $_POST['sql'];
		$qry = str_replace("\\'", "'", $qry);
		
		$parser = new PHPSQLParser($qry);
		
//		print_r($parser->parsed);
		
		if ($parser->parsed['FROM'][0]['alias'] != "") {
			$pkname = $parser->parsed['FROM'][0]['alias']['name'] . "." . $pkname;
		}
		
		if (! isset($parser->parsed['WHERE'])) {
			/* Create where clause. */
			$parser->parsed['WHERE'] = array();
						
		} else {
			/* Add to the where clause. */
			$parser->parsed['WHERE'][] = 
					array(
							"expr_type" 		=> "operator",
							"base_expr"			=> "AND",
							"sub_tree"			=> ""
						);
		}
					
		$parser->parsed['WHERE'][] = 
				array(
						"expr_type" 		=> "colref",
						"base_expr"			=> $pkname,
						"sub_tree"			=> ""
					);
					
		$parser->parsed['WHERE'][] = 
				array(
						"expr_type" 		=> "operator",
						"base_expr"			=> "=",
						"sub_tree"			=> ""
					);
					
		$parser->parsed['WHERE'][] = 
				array(
						"expr_type" 		=> "const",
						"base_expr"			=> "'$id'",
						"sub_tree"			=> ""
					);
			
		$creator = new PHPSQLCreator($parser->parsed);
		$created = $creator->created;			
		
		$qry = $created;

	} else {
		$qry = "SELECT * FROM " . $table . " " . "WHERE $pkname = $id";
	}
//			print_r($qry);
	
//	dir($qry);

	echo json_encode($json->fetch($qry));
?>