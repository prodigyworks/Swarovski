<?php
	require_once("crud.php");
	

	$crud = new Crud();
	$crud->dialogwidth = 400;
	$crud->title = "Warehouses";
	$crud->table = "{$_SESSION['DB_PREFIX']}warehouses";
	
	if ($_SESSION['WAREHOUSE_ID'] != null && $_SESSION['WAREHOUSE_ID'] != "0") {
		$crud->sql = "SELECT * FROM {$_SESSION['DB_PREFIX']}warehouses WHERE id = " . $_SESSION['WAREHOUSE_ID'] . " ORDER BY name";
		
	} else {
		$crud->sql = "SELECT * FROM {$_SESSION['DB_PREFIX']}warehouses ORDER BY name";
	}
	
	$crud->subapplications = array(
			array(
				'title'		  => 'Stock',
				'imageurl'	  => 'images/stock.png',
				'application' => 'managewarehousestock.php'
			)
		);
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
				'name'       => 'name',
				'length' 	 => 50,
				'label' 	 => 'Name'
			)
		);
		
	$crud->run();
?>
