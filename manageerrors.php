<?php
	require_once("crud.php");
	
	function clearAllErrors() {
		mysql_query("DELETE FROM {$_SESSION['DB_PREFIX']}errors");
	}
	
	class ErrorCrud extends Crud {
		
		public function postScriptEvent() {
?>
			/* Full name callback. */
			function fullName(node) {
				return (node.firstname + " " + node.lastname);
			}
			
			function clearErrors(id) {
				post("editform", "clearAllErrors", "submitframe", {});
			}
<?php			
		}
	}

	$crud = new ErrorCrud();
	$crud->allowAdd = false;
	$crud->allowEdit = false;
	$crud->title = "Errors";
	$crud->table = "{$_SESSION['DB_PREFIX']}errors";
	$crud->dialogwidth = 900;
	$crud->sql = 
			"SELECT A.*, B.label, C.firstname, C.lastname " .
			"FROM {$_SESSION['DB_PREFIX']}errors A " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}pages B " .
			"ON B.pageid = A.pageid " .
			"INNER JOIN {$_SESSION['DB_PREFIX']}members C " .
			"ON C.member_id = A.memberid " .
			"ORDER BY A.id DESC";
	
	$crud->columns = array(
			array(
				'name'       => 'id',
				'length' 	 => 6,
				'pk'		 => true,
				'showInView' => false,
				'editable'	 => false,
				'bind' 	 	 => false,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'pageid',
				'length' 	 => 6,
				'showInView' => false,
				'editable'	 => false,
				'bind' 	 	 => false,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'createddate',
				'length' 	 => 12,
				'bind'		 => false,
				'label' 	 => 'Created Date'
			),
			array(
				'name'       => 'label',
				'length' 	 => 60,
				'label' 	 => 'Page'
			),
			array(
				'name'       => 'user',
				'type'		 => 'DERIVED',
				'length' 	 => 60,
				'function'	 => 'fullName',
				'label' 	 => 'User'
			),
			array(
				'name'       => 'description',
				'showInView' => false,
				'type'		 => 'TEXTAREA',
				'label' 	 => 'Error'
			)
		);
	$crud->applications = array(
			array(
				'title'		  => 'Clear',
				'imageurl'	  => 'images/minimize.gif',
				'script' 	  => 'clearErrors'
			)
		);
		
	$crud->run();
?>
