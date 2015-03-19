<?php
	require_once("crud.php");
	
	class ManageStockCrud extends Crud {
		public function postScriptEvent() {
?>
		    function navigateDown(pk) {
		    	subApp('managestock.php', pk);
		    }
<?php
		}
	}
	
	$crud = new ManageStockCrud();
	$crud->title = "Product Group";
	$crud->table = "{$_SESSION['DB_PREFIX']}prodgroup";
	$crud->onDblClick = "navigateDown";
	
	if (isset($_GET['id'])) {
		$crud->sql = "SELECT * FROM {$_SESSION['DB_PREFIX']}prodgroup WHERE parentid = " . $_GET['id'] . " ORDER BY name";
		
	} else {
		$crud->sql = "SELECT * FROM {$_SESSION['DB_PREFIX']}prodgroup WHERE parentid = 0 ORDER BY name";
	}
	
	$crud->subapplications = array(
			array(
				'title'		  => 'Down',
				'imageurl'	  => 'images/minimize.gif',
				'action'	  => 'Down',
				'application' => 'manageprodgroups.php'
			),
			array(
				'title'		  => 'Stock',
				'imageurl'	  => 'images/stock.png',
				'action'	  => 'Stock',
				'application' => 'managestock.php'
			)
		);
	$crud->columns = array(
			array(
				'name'       => 'id',
				'length' 	 => 6,
				'showInView' => false,
				'bind' 	 	 => false,
				'editable' 	 => false,
				'pk'		 => true,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'parentid',
				'datatype'	 => 'integer',
				'length' 	 => 6,
				'showInView' => false,
				'filter'	 => false,
				'editable' 	 => false,
				'default'	 => (isset($_GET['id']) ? $_GET['id'] : '0'),
				'label' 	 => 'Parent ID'
			),
			array(
				'name'       => 'name',
				'length' 	 => 60,
				'label' 	 => 'Name'
			)
		);
		
	$crud->run();
	
?>
