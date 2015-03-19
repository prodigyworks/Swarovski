<?php
	require_once("crud.php");
	

	$crud = new Crud();
	$crud->dialogwidth = 400;
	$crud->title = "Customers";
	$crud->table = "{$_SESSION['DB_PREFIX']}customers";
	$crud->sql = "SELECT * FROM {$_SESSION['DB_PREFIX']}customers ORDER BY name";
	$crud->subapplications = array(
			array(
				'title'		  => 'Addresses',
				'imageurl'	  => 'images/stock.png',
				'application' => 'managecustomeraddresses.php'
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
