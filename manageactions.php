<?php
	require_once("crud.php");
	
	class ActionEdit extends Crud {
		
		/* Pre command event. */
		public function preCommandEvent() {
			if (isset($_POST['rolecmd'])) {
				if (isset($_POST['roles'])) {
					$counter = count($_POST['roles']);
		
				} else {
					$counter = 0;
				}
				
				$actionid = $_POST['actionid'];
				$qry = "DELETE FROM {$_SESSION['DB_PREFIX']}applicationactionroles WHERE actionid = $actionid";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError(mysql_error());
				}
		
				for ($i = 0; $i < $counter; $i++) {
					$roleid = $_POST['roles'][$i];
					
					$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}applicationactionroles (actionid, roleid) VALUES ($actionid, '$roleid')";
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
					<input type="hidden" id="actionid" name="actionid" />
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
				
			function actionRoles(actionid) {
				getJSONData('findactionroles.php?actionid=' + actionid, "#roles", function() {
					$("#actionid").val(actionid);
					$("#roleDialog").dialog("open");
				});
			}
<?php
		}
	}
		
	$crud = new ActionEdit();
	$crud->title = "Actions";
	$crud->table = "{$_SESSION['DB_PREFIX']}applicationactions";
	$crud->allowAdd = false;
	$crud->dialogwidth = 500;
	
	if (isset($_GET['id'])) {
		$crud->sql = "SELECT * FROM {$_SESSION['DB_PREFIX']}applicationactions WHERE pageid = " . $_GET['id'] . " ORDER BY id";
		
	} else {
		$crud->sql = "SELECT * FROM {$_SESSION['DB_PREFIX']}applicationactions ORDER BY id";
	}
	
	$crud->subapplications = array(
			array(
				'title'		  => 'User Roles',
				'imageurl'	  => 'images/user.png',
				'script' 	  => 'actionRoles'
			)
		);
	$crud->columns = array(
			array(
				'name'       => 'id',
				'length' 	 => 6,
				'pk'		 => true,
				'showInView' => false,
				'bind' 	 	 => false,
				'editable' 	 => false,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'pageid',
				'length' 	 => 6,
				'parentid'	 => true,
				'editable'   => false,
				'showInView' => false,
				'label' 	 => 'Page ID'
			),
			array(
				'name'       => 'description',
				'length' 	 => 60,
				'sortby'	 => true,
				'label' 	 => 'Description'
			)
		);
		
	$crud->run();
?>
