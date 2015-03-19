<?php 
	class ActionConstants {
		public static $enums = array(
				"EditItem" 				=> "Edit item",
				"AddItem" 				=> "Add item",
				"RemoveItem"			=> "Remove item",
				"ViewItem"				=> "View item",
				"EditHeader" 			=> "Edit header",
				"AddHeader" 			=> "Add header",
				"ViewHeader" 			=> "Viiew header",
				"RemoveHeader"			=> "Remove header",
				"RemoveHeader"			=> "Remove header",
				"RemoveHeader"			=> "Remove header",
				"Appraisals"			=> "Show appraisals",
				"Absences"				=> "Show absences",
				"Holidays"				=> "Show annual holidays",
				"Approve"				=> "Approve",
				"Reject"				=> "Reject",
				"Filter"				=> "Filter",
				"Training"				=> "Show training delivered"
			);
			
		public static function getActionDescription($action) {
			return self::$enums[$action];
		}
	}
?>
