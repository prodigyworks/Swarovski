<?php
	require_once("crud.php");
	
	function testemail() {
		sendUserMessage($_POST['expiredmemberid'], "Test", getEmailHeader() . "Test email");
	}
	
	function expire() {
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}members SET status = 'N' WHERE member_id = " . $_POST['expiredmemberid'];
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " = " . mysql_error());
		}
	}
	
	function live() {
		$qry = "UPDATE {$_SESSION['DB_PREFIX']}members SET status = 'Y' WHERE member_id = " . $_POST['expiredmemberid'];
		$result = mysql_query($qry);
		
		if (! $result) {
			logError($qry . " = " . mysql_error());
		}
	}
	
	class UserCrud extends Crud {
		
		/* Pre command event. */
		public function preCommandEvent() {
			if (isset($_POST['rolecmd'])) {
				if (isset($_POST['roles'])) {
					$counter = count($_POST['roles']);
		
				} else {
					$counter = 0;
				}
				
				$memberid = $_POST['memberid'];
				$qry = "DELETE FROM {$_SESSION['DB_PREFIX']}userroles WHERE memberid = $memberid";
				$result = mysql_query($qry);
				
				if (! $result) {
					logError(mysql_error());
				}
		
				for ($i = 0; $i < $counter; $i++) {
					$roleid = $_POST['roles'][$i];
					
					$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}userroles (memberid, roleid) VALUES ($memberid, '$roleid')";
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
					<input type="hidden" id="memberid" name="memberid" />
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
			
			function fullName(node) {
				return (node.firstname + " " + node.lastname);
			}
			
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
				
			function userRoles(memberid) {
				getJSONData('findroleusers.php?memberid=' + memberid, "#roles", function() {
					$("#memberid").val(memberid);
					$("#roleDialog").dialog("open");
				});
			}
				
			function expire(memberid) {
				post("editform", "expire", "submitframe", 
						{ 
							expiredmemberid: memberid
						}
					);
			}
				
			function live(memberid) {
				post("editform", "live", "submitframe", 
						{ 
							expiredmemberid: memberid
						}
					);
			}
			
			function testEmail(memberid) {
				post("editform", "testemail", "submitframe", 
						{ 
							expiredmemberid: memberid
						}
					);
			}
<?php
		}
	}

	$crud = new UserCrud();
	$crud->messages = array(
			array('id'		  => 'expiredmemberid')
		);
	$crud->subapplications = array(
			array(
				'title'		  => 'User Roles',
				'imageurl'	  => 'images/user.png',
				'script' 	  => 'userRoles'
			),
			array(
				'title'		  => 'Test email',
				'imageurl'	  => 'images/mail.png',
				'script' 	  => 'testEmail'
			)
		);
	$crud->allowAdd = false;
	$crud->dialogwidth = 950;
	$crud->title = "Staff";
	$crud->table = "{$_SESSION['DB_PREFIX']}members";
	$crud->sql = 
			"SELECT A.*, B.name AS customername, C.name AS warehousename " .
			"FROM {$_SESSION['DB_PREFIX']}members A " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}customers B " .
			"ON B.id = A.customerid " .
			"LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}warehouses C " .
			"ON C.id = A.warehouseid " .
			"ORDER BY A.firstname, A.lastname"; 
			
	$crud->columns = array(
			array(
				'name'       => 'member_id',
				'length' 	 => 6,
				'showInView' => false,
				'bind' 	 	 => false,
				'filter'	 => false,
				'editable' 	 => false,
				'pk'		 => true,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'login',
				'length' 	 => 30,
				'label' 	 => 'Login ID'
			),
			array(
				'name'       => 'staffname',
				'type'		 => 'DERIVED',
				'length' 	 => 60,
				'bind'		 => false,
				'function'   => 'fullName',
				'label' 	 => 'Name'
			),
			array(
				'name'       => 'firstname',
				'length' 	 => 30,
				'showInView' => false,
				'label' 	 => 'First Name'
			),
			array(
				'name'       => 'lastname',
				'length' 	 => 30,
				'showInView' => false,
				'label' 	 => 'Last Name'
			),
			array(
				'name'       => 'email',
				'length' 	 => 60,
				'label' 	 => 'Email'
			),
			array(
				'name'       => 'mobile',
				'length' 	 => 13,
				'label' 	 => 'Phone'
			),
			array(
				'name'       => 'imageid',
				'type'		 => 'IMAGE',
				'length' 	 => 64,
				'required'	 => false,
				'showInView' => false,
				'label' 	 => 'Image'
			),
			array(
				'name'       => 'customerid',
				'type'       => 'DATACOMBO',
				'length' 	 => 35,
				'label' 	 => 'Customer',
				'table'		 => 'customers',
				'table_id'	 => 'id',
				'required'	 => false,
				'alias'		 => 'customername',
				'table_name' => 'name'
			),
			array(
				'name'       => 'warehouseid',
				'type'       => 'DATACOMBO',
				'length' 	 => 35,
				'label' 	 => 'Warehouse',
				'table'		 => 'warehouses',
				'table_id'	 => 'id',
				'required'	 => false,
				'alias'		 => 'warehousename',
				'table_name' => 'name'
			),
			array(
				'name'       => 'passwd',
				'type'		 => 'PASSWORD',
				'length' 	 => 30,
				'showInView' => false,
				'label' 	 => 'Password'
			),
			array(
				'name'       => 'cpassword',
				'type'		 => 'PASSWORD',
				'length' 	 => 30,
				'bind' 	 	 => false,
				'showInView' => false,
				'label' 	 => 'Confirm Password'
			)
		);
		
	$crud->run();
?>
