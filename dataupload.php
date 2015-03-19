<?php
	include("system-header.php"); 
	
	if (isset($_FILES['warehousecsv']) && $_FILES['warehousecsv']['tmp_name'] != "") {
		$qry = mysql_query("DELETE FROM {$_SESSION['DB_PREFIX']}prodgroup ");
		$qry = mysql_query("DELETE FROM {$_SESSION['DB_PREFIX']}warehouses ");
		$qry = mysql_query("DELETE FROM {$_SESSION['DB_PREFIX']}stock ");
		$qry = mysql_query("DELETE FROM {$_SESSION['DB_PREFIX']}stockitem ");
		$qry = mysql_query("DELETE FROM {$_SESSION['DB_PREFIX']}despatchheader ");
		$qry = mysql_query("DELETE FROM {$_SESSION['DB_PREFIX']}despatchitem ");
		$qry = mysql_query("DELETE FROM {$_SESSION['DB_PREFIX']}warehousestock ");
		
		if ($_FILES["warehousecsv"]["error"] > 0) {
			echo "Error: " . $_FILES["warehousecsv"]["error"] . "<br />";
			
		} else {
		  	echo "Upload: " . $_FILES["warehousecsv"]["name"] . "<br />";
		  	echo "Type: " . $_FILES["warehousecsv"]["type"] . "<br />";
		  	echo "Size: " . ($_FILES["warehousecsv"]["size"] / 1024) . " Kb<br />";
		  	echo "Stored in: " . $_FILES["warehousecsv"]["tmp_name"] . "<br>";
		}
		
		$row = 1;
		
		if (($handle = fopen($_FILES['warehousecsv']['tmp_name'], "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		        if ($row++ == 1) {
		        	continue;
		        }
		        
		        $num = count($data);
		        
		        if ($num < 2) {
		        	continue;
		        }
		        
		        $prodgroup = $data[0];
		        $serial = $data[2];
		        $name = $data[1];
		        $warehouse = $data[3];
		        $prodgroupid = 0;
		        
		        if (trim($prodgroup) == "") {
		        	continue;
		        }
		        
		        if (trim($name) == "") {
		        	continue;
		        }
		        
		        if (trim($serial) == "") {
		        	continue;
		        }
		
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}warehouses " .
						"(name) " .
						"VALUES " .
						"('$warehouse')";
						
				$result = mysql_query($qry);
				$warehouseid = 0;
				$qry = "SELECT id FROM {$_SESSION['DB_PREFIX']}warehouses " .
						"WHERE name = '$warehouse'";
				
				$result = mysql_query($qry);
				
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						$warehouseid = $member['id'];
					}
				}
		        
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}prodgroup " .
						"(name) " .
						"VALUES " .
						"('" . $prodgroup . "')";
						
				$result = mysql_query($qry);
				
				$qry = "SELECT id FROM {$_SESSION['DB_PREFIX']}prodgroup " .
						"WHERE name = '$prodgroup'";
				
				$result = mysql_query($qry);
				
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						$prodgroupid = $member['id'];
					}
				}
				
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}stock " .
						"(prodgroupid, name) " .
						"VALUES " .
						"($prodgroupid, '" . $name . "')";
						
				$result = mysql_query($qry);
				
				$qry = "SELECT id FROM {$_SESSION['DB_PREFIX']}stock " .
						"WHERE name = '$name'";
				
				$result = mysql_query($qry);
				
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						$stockid = $member['id'];
					}
				}
				
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}stockitem " .
						"(stockid, serialnumber, status) " .
						"VALUES " .
						"($stockid, '" . $serial . "', 'I')";
						
				$result = mysql_query($qry);
				
				$qry = "SELECT id FROM {$_SESSION['DB_PREFIX']}stockitem " .
						"WHERE stockid = $stockid AND serialnumber = '$serial'";
				
				$result = mysql_query($qry);
				
				if ($result) {
					while (($member = mysql_fetch_assoc($result))) {
						$stockitemid = $member['id'];
					}
				}
				
				$qry = "INSERT INTO {$_SESSION['DB_PREFIX']}warehousestock " .
						"(warehouseid, stockitemid) " .
						"VALUES " .
						"($warehouseid, '" . $stockitemid . "')";
						
				$result = mysql_query($qry);
		    }
		    
		    
		    fclose($handle);
			echo "<h1>" . $row . " downloaded</h1>";
		}
	}
	
	if (! isset($_FILES['warehousecsv'])) {
?>	
		
<form class="contentform" method="post" enctype="multipart/form-data">
	<label>Upload Stock Warehouse CSV file </label>
	<input type="file" name="warehousecsv" id="warehousecsv" style='width: 500px'/> 
	
	<br />
	<div id="submit" class="show">
		<input type="submit" value="Upload" />
	</div>
</form>
<?php
	}
	
	include("system-footer.php"); 
?>