<?php
	class SQLProcessToArray {
		function __construct() {
			//Include database connection details
			require_once('system-db.php');
			
			start_db();
			initialise_db();
		}
		
		/* Fetch the SQL into a JSON array. */
		public function fetch($qry) {
			$itemarray = array(); 
			$property = null;
			$result = mysql_query($qry);
			$propertyarray = array();
			
			if ($result) {
				try {
					while ($property = mysql_fetch_field($result)) {
						if (isset($property)) {
							array_push($propertyarray, $property);
						}
					}
					
					while (($member = mysql_fetch_assoc($result))) {
						$keys = array_keys($member);
						$line = array();
	
						foreach ($propertyarray as $property) {
						  	if ($property->type == "timestamp" || $property->type == "date" || $property->type == "datetime" ) {
						  		if ($member[$property->name] == null || $member[$property->name] == 0) {
									$line[$property->name] =  "";
									
						  		} else {
									$date = new DateTime($member[$property->name]);
									$line[$property->name] =  $date->format('d/m/Y');
						  		}
								
						  	} else {
								$line[$property->name] = $member[$property->name];
						  	}
						}
						
						
						array_push($itemarray, $line);
					}
					
				} catch (Exception $e) {
					logError($qry . " - " . $e->getMessage());
				}
				
			} else {
				logError($qry . " - " . mysql_error());
				
				return mysql_error();
			}
			
//			echo "<p>ITEM ARRAY<p>";
//			print_r($itemarray);
//			echo "<p>ITEM ARRAY 2<p>";
			
			return ($itemarray); 
		}
	}
?>