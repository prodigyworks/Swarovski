<?php
	require_once("crud.php");
	
	class MessageCrud extends Crud {
		
		public function postScriptEvent() {
?>
			/* Full name callback. */
			function toFullName(node) {
				return (node.tofirstname + " " + node.tolastname);
			}
			
			/* Full name callback. */
			function fromFullName(node) {
				return (node.fromfirstname + " " + node.fromlastname);
			}
			
			function viewMessages(id) {
				
				callAjax(
						"viewmessage.php", 
						{
							id: id
						},
						function(data) {
							var node = data[0];
							
							if (node.messages == 0) {
								$("#messagecontainer").hide();
								
							} else {
								$("#messagecount").html(node.messages);
								$("#messagecontainer").show();
							}
							
							refreshData();
						}
					);
					
				view(id);
			}
<?php			
		}
	}

	$crud = new MessageCrud();
	$crud->allowView = false;
	$crud->allowEdit = false;
	$crud->dialogwidth = 950;
	$crud->title = "Messages";
	$crud->table = "{$_SESSION['DB_PREFIX']}messages";
	$crud->sql = "SELECT A.id, A.status, A.subject, A.message, A.from_member_id, A.to_member_id, A.createddate, " .
				 "B.firstname AS fromfirstname, B.lastname AS fromlastname, " .
				 "C.firstname AS tofirstname, C.lastname AS tolastname " .
				 "FROM  {$_SESSION['DB_PREFIX']}messages A " .
				 "LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members C " .
				 "ON C.member_id = A.to_member_id " .
				 "LEFT OUTER JOIN {$_SESSION['DB_PREFIX']}members B " .
				 "ON B.member_id = A.from_member_id " .
				 "WHERE A.to_member_id = " . getLoggedOnMemberID() . " " .
				 "ORDER BY A.status, A.createddate DESC";
	$crud->subapplications = array(
			array(
				'title'		  => 'View',
				'imageurl'	  => 'images/view.png',
				'script' 	  => 'viewMessages'
			),
			array(
				'title'		  => 'Reply',
				'imageurl'	  => 'images/mail.png',
				'script' 	  => 'reply'
			)
		);
	$crud->columns = array(
			array(
				'name'       => 'id',
				'viewname'   => 'uniqueid',
				'length' 	 => 6,
				'showInView' => false,
				'bind' 	 	 => false,
				'editable' 	 => false,
				'pk'		 => true,
				'label' 	 => 'ID'
			),
			array(
				'name'       => 'from_member_id',
				'datatype'	 => 'user',
				'length' 	 => 10,
				'showInView' => false,
				'align'		 => 'center',
				'label' 	 => 'From'
			),
			array(
				'name'       => 'from',
				'type'		 => 'DERIVED',
				'length' 	 => 30,
				'bind'		 => false,
				'editable' 	 => false,
				'function'   => 'fromFullName',
				'label' 	 => 'From'
			),
			array(
				'name'       => 'to_member_id',
				'datatype'	 => 'user',
				'length' 	 => 10,
				'showInView' => false,
				'align'		 => 'center',
				'label' 	 => 'From'
			),
			array(
				'name'       => 'to',
				'type'		 => 'DERIVED',
				'length' 	 => 30,
				'bind'		 => false,
				'editable' 	 => false,
				'function'   => 'toFullName',
				'label' 	 => 'To'
			),
			array(
				'name'       => 'subject',
				'length' 	 => 60,
				'sortby'	 => true,
				'label' 	 => 'Subject'
			),
			array(
				'name'       => 'message',
				'type'		 => 'TEXTAREA',
				'showInView' => false,
				'filter'	 => false,
				'label' 	 => 'Message'
			),
			array(
				'name'       => 'createddate',
				'datatype'	 => 'timestamp',
				'length' 	 => 10,
				'label' 	 => 'Date'
			),
			array(
				'name'       => 'status',
				'length' 	 => 4,
				'label' 	 => 'Status',
				'editable'	 => false,
				'type'       => 'COMBO',
				'options'    => array(
						array(
							'value'		=> 'R',
							'text'		=> 'Read'
						),
						array(
							'value'		=> 'N',
							'text'		=> 'Unread'
						)
					)
			)
		);
		
	$crud->run();
?>
