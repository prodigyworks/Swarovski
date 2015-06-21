<?php
	require_once("system-db.php");
	
	start_db();
	
	function alter($sql) {
		echo "<p>$sql</p>";
		$result = mysql_query($sql);
		
		if (! $result) {
			echo "<h1>Error : " . mysql_error() . "</h1>";
			logError(mysql_error(), false);
		}
	}

	alter("ALTER TABLE swarovski_siteconfig ADD COLUMN loadextensionemail TEXT NULL AFTER registrationemail;");
?>
