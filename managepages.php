<?php
	require_once("crud.php");
	
	class PageEdit extends Crud {
		
		/* Pre command event. */
		public function preCommandEvent() {
			if (isset($_POST['rolecmd'])) {
				if (isset($_POST['roles'])) {
					$counter = count($_POST['roles']);
		
				} else {
					$counter = 0;
				}
				
				$pageid = $_POST['pageid'];
				$qry = "DELETE FROM {$_SESSION['DB_PREFIX']}pageroles WHERE pageid = $pageid";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError(mysql_error());
				}
		
				for ($i = 0; $i < $counter; $i++) {
					$roleid = $_POST['roles'][$i];
					
					$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}pageroles (pageid, roleid) VALUES ($pageid, '$roleid')";
					$result = mysql_query($qry);
				};
			}
		}

		/* Post header event. */
		public function postHeaderEvent() {
?>
			<script src='js/jquery.picklists.js' type='text/javascript'></script>
			
			<div id="roleDialog" class="modal">
				<form id="rolesForm" name="rolesForm" method="post">
					<input type="hidden" id="pageid" name="pageid" />
					<input type="hidden" id="rolecmd" name="rolecmd" value="X" />
					<select class="listpicker" name="roles[]" multiple="true" id="roles" >
						<?php createComboOptions("roleid", "roleid", "{$_SESSION['DB_PREFIX']}roles", "", false); ?>
					</select>
				</form>
			</div>
<?php
		}
		
		/* Post script event. */
		public function postScriptEvent() {
?>
			var currentRole = null;
			
			$(document).ready(function() {
					$("#roles").pickList({
							removeText: 'Remove Role',
							addText: 'Add Role',
							testMode: false
						});
					
					$("#roleDialog").dialog({
							autoOpen: false,
							modal: true,
							width: 800,
							title: "Roles",
							buttons: {
								Ok: function() {
									$("#rolesForm").submit();
								},
								Cancel: function() {
									$(this).dialog("close");
								}
							}
						});
				});
				
			function pageRoles(pageid) {
				getJSONData('findpageroles.php?pageid=' + pageid, "#roles", function() {
					$("#pageid").val(pageid);
					$("#roleDialog").dialog("open");
				});
			}
<?php
		}
	}
	
	$crud = new PageEdit();
	$crud->title = "Pages";
	$crud->table = "{$_SESSION['DB_PREFIX']}pages";
	$crud->dialogwidth = 500;
	
	if (isset($_GET['id'])) {
		$crud->sql = 
				"SELECT A.*, B.pagetype " .
				"FROM {$_SESSION['DB_PREFIX']}pages A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}pagenavigation B " .
				"ON B.childpageid = A.pageid " .
				"AND B.pagetype != 'P' " .
				"WHERE B.pageid = " . $_GET['id']; 
		
	} else {
		$crud->sql = 
				"SELECT A.*, B.pagetype " .
				"FROM {$_SESSION['DB_PREFIX']}pages A " .
				"INNER JOIN {$_SESSION['DB_PREFIX']}pagenavigation B " .
				"ON B.childpageid = A.pageid " .
				"AND B.pagetype = 'P' " .
				"WHERE B.pageid = 1"; 
	}
	
	$crud->subapplications = array(
			array(
				'title'		  => 'Down',
				'imageurl'	  => 'images/minimize.gif',
				'action'	  => 'Down',
				'application' => 'managepages.php'
			),
			array(
				'title'		  => 'User Roles',
				'imageurl'	  => 'images/user.png',
				'script' 	  => 'pageRoles'
			),
			array(
				'title'		  => 'Actions',
				'imageurl'	  => 'images/action.png',
				'action'	  => 'Actions',
				'application' => 'manageactions.php'
			)
		);
	$crud->columns = array(
			array(
				'name'       => 'pageid',
				'length' 	 => 6,
				'pk'		 => true,
				'showInView' => false,
				'editable'	 => false,
				'bind' 	 	 => false,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'label',
				'length' 	 => 60,
				'sortby'	 => true,
				'label' 	 => 'Application'
			),
			array(
				'name'       => 'pagename',
				'length' 	 => 30,
				'label' 	 => 'Page Name'
			)
		);
		
	$crud->run();
?>
