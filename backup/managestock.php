<?php
	require_once("crud.php");
	require_once("confirmdialog.php");
	
	function move() {
		$id = $_POST['move_stockid'];
		$productid = $_POST['move_productid'];
		
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}stock SET " .
				"prodgroupid = $productid " .
				"WHERE id = $id";
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " - " . mysql_error());
		}
	}
	
	class ManageStockCrud extends Crud {
		public function postScriptEvent() {
?>
			var currentID = null;
			
		    function navigateDown(pk) {
		    	subApp('managestockitems.php', pk);
		    }
		    
			function movestock(pk) {
				currentID = pk;

				$("#movedialog").dialog("open");
		    } 	
		    
		    function confirmstockmovement() {
		    	$("#confirmmovedialog").dialog("close");
		    	
				post("editform", "move", "submitframe", 
						{ 
							move_stockid: currentID, 
							move_productid: $("#moproductgroupid").val()
						}
					);
		    }
		    
			$(document).ready(
					function() {
						$("#movedialog").dialog({
								modal: true,
								autoOpen: false,
								title: "Move Stock Product Group",
								width: 210,
								height: 180,
								buttons: {
									Ok: function() {
										$(this).dialog("close");
										
										$("#confirmmovedialog .confirmdialogbody").html("You are about to move this stock record to a different product group.<br>Are you sure ?");
										$("#confirmmovedialog").dialog("open");
									},
									Cancel: function() {
										$(this).dialog("close");
									}
								}
							});
					}
				);						
<?php
		}
		public function postHeaderEvent() {
			createConfirmDialog("confirmmovedialog", "Confirm product group movement ?", "confirmstockmovement");
			
?>
			<div id="movedialog" class="modal">
				<label>Product Group</label><br>
				<?php createCombo("moproductgroupid", "id", "name", "{$_SESSION['DB_PREFIX']}prodgroup"); ?>
			</div>
<?php
		}
	}
	
	$crud = new ManageStockCrud();
	$crud->title = "Stock";
	$crud->table = "{$_SESSION['DB_PREFIX']}stock";
	$crud->dialogwidth = 900;
	$crud->onDblClick = "navigateDown";
	

	$crud->messages = array(
			array('id'		  => 'move_stockid'),
			array('id'		  => 'move_productid')
		);
		
	if (isset($_GET['id'])) {
		$crud->sql = 
				"SELECT A.*, C.name AS prodgroupname " .
				"FROM {$_SESSION['DB_PREFIX']}stock A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}prodgroup C " .
				"ON C.id = A.prodgroupid " .
				"WHERE (C.id = " . $_GET['id'] . " OR C.parentid = " . $_GET['id'] . ") " .
				"ORDER BY A.name";
		
	} else {
		$crud->sql = 
				"SELECT A.*, C.name AS prodgroupname " .
				"FROM {$_SESSION['DB_PREFIX']}stock A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}prodgroup C " .
				"ON C.id = A.prodgroupid " .
				"ORDER BY A.name";
	}
	
	$crud->subapplications = array(
			array(
				'title'		  => 'Stock Items',
				'imageurl'	  => 'images/stock.png',
				'application' => 'managestockitems.php'
			),
			array(
				'title'		  => 'Change Group',
				'imageurl'	  => 'images/stock.png',
				'script' 	  => 'movestock'
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
				'length' 	 => 60,
				'label' 	 => 'Name'
			),
			array(
				'name'       => 'prodgroupid',
				'type'       => 'DATACOMBO',
				'length' 	 => 45,
				'label' 	 => 'Product Group',
				'table'		 => 'prodgroup',
				'table_id'	 => 'id',
				'alias'		 => 'prodgroupname',
				'table_name' => 'name',
				'default'	 => (isset($_GET['id']) ? $_GET['id'] : '')
			),
			array(
				'name'       => 'imageid',
				'type'		 => 'IMAGE',
				'length' 	 => 64,
				'required'	 => false,
				'showInView' => false,
				'filter'	 => false,
				'label' 	 => 'Image'
			),
			array(
				'name'       => 'description',
				'showInView' => false,
				'filter'	 => false,
				'type'		 => 'TEXTAREA',
				'label' 	 => 'Description'
			)
		);
		
	$crud->run();
	
?>
