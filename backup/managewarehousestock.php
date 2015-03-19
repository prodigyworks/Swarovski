<?php
	require_once("crud.php");
	

	$crud = new Crud();
	$crud->dialogwidth = 400;
	$crud->title = "Warehouses";
	$crud->table = "{$_SESSION['DB_PREFIX']}warehousestock";
	$crud->sql = "SELECT A.*, B.serialnumber , C.name, D.name AS warehousename " .
				"FROM {$_SESSION['DB_PREFIX']}warehousestock A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}stockitem B " .
				"ON B.id = A.stockitemid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}warehouses D " .
				"ON D.id = A.warehouseid " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}stock C " .
				"ON C.id = B.stockid " .
				"WHERE A.warehouseid = " . $_GET['id'] . " ORDER BY B.serialnumber";
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
				'name'       => 'warehouseid',
				'type'       => 'DATACOMBO',
				'default'	 => $_GET['id'],
				'length' 	 => 35,
				'label' 	 => 'Warehouse ID',
				'table'		 => 'warehouses',
				'table_id'	 => 'id',
				'readonly'	 => true,
				'alias'		 => 'warehousename',
				'table_name' => 'name'
			),
			array(
				'name'       => 'name',
				'length' 	 => 50,
				'label' 	 => 'Name'
			),
			array(
				'name'       => 'serialnumber',
				'length' 	 => 50,
				'label' 	 => 'Serial Number'
			)
		);
		
	$crud->run();
?>
