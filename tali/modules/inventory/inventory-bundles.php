<?php
//Open form used for Checkout function and build table of inventory
echo "			
			<div class=\"tali-page-frame\">
				<h1>Bundles/Specific Locations</h1>
				<form method=\"POST\" action=\"inventory.php\">
				<input type=\"hidden\" id=\"checkoutPurpose_Bundle\" name=\"checkoutPurpose_Bundle\">
				<input type=\"hidden\" id=\"checkoutItemId_Bundle\" name=\"checkoutItemId_Bundle\">
				<table id=\"inventoryTable\" class=\"inventoryBrowseTable\">
					<col width=\"20%\">
					<col width=\"70%\">
					<col width=\"10%\">
					<tr>
						<th onclick=\"sortTable(0)\">Name</th>
						<th>Contents</th>
						<th>Check Out</th>
					</tr>
";

//Pull location_specific only when it is used more than once ("bundle"), and skip when undefined
$SQL = "SELECT location_specific FROM inventory WHERE location_specific != '' GROUP BY location_specific HAVING COUNT(id) > 1";
$result = mysqli_query($db_handle, $SQL);
$num_rows = mysqli_num_rows($result);

while ($db_field = mysqli_fetch_assoc($result)) {
	$specificLocation=$db_field['location_specific'];
	
	//Grab an id to use for referencing the name instead of potentially dealing with a string in GET
	$idSQL = "SELECT id FROM inventory WHERE location_specific = '$specificLocation' LIMIT 1";
	$idresult = mysqli_query($db_handle, $idSQL);
	$iddb_field = mysqli_fetch_assoc($idresult);
	$one_id = $iddb_field['id'];
	
	//By default, assume the defined specific location is a string name
	$specificLocationName = $specificLocation;
	
	//Alternatively, grab the specific location's name if it is standardized
	if (is_numeric($specificLocationName)) {
		$nameSQL = "SELECT name FROM inventory_location_specific WHERE id = '$specificLocation'";
		$nameresult = mysqli_query($db_handle, $nameSQL);
		$namedb_field = mysqli_fetch_assoc($nameresult);
		$specificLocationName = $namedb_field['name'];
	}
	
	//Get name and ID for listing individual contents with hyperlink and colored based on status
	$contentsSQL = "SELECT name, id, checkedoutby FROM inventory WHERE NOT status=2 AND location_specific = '$specificLocation'";
	$contentsresult = mysqli_query($db_handle, $contentsSQL);
	$contents = "";
	
	while ($contentsdb_field = mysqli_fetch_assoc($contentsresult)) {
		
		//Color green if available, red if checked out
		if ($contentsdb_field['checkedoutby'] == 0) {
			$color = "green";
		}
		else
		{
			$color = "red";
		}
		
		//Add item to the list
		$contents = $contents . "<a style=\"color:$color\"; href=\"inventory.php?id=".$contentsdb_field['id']."\">".$contentsdb_field['name']."</a>" . "; ";
	}
	echo "
					<tr>
						<td><input type=\"hidden\" value=\"$specificLocationName\"</input><a href=\"inventory.php?specificLocation=$specificLocation\">$specificLocationName</a></td>			
						<td>$contents</td>
						<td style=\"text-align:center;\">
							<input type=\"Submit\" name=\"inventoryCheckOut\" value=\"Check Out\" onclick=\"checkoutPurposeFnc($one_id)\">
						</td>
					</tr>
	";
}
echo "
				</table>
				</form>
			</div>
		</div>
	</main>
"; 

?>

<script>
//Script from W3 to sort table
function sortTable(n) {
	var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
	table = document.getElementById("inventoryTable");
	switching = true;
	// Set the sorting direction to ascending:
	dir = "asc";
	/* Make a loop that will continue until no switching has been done: */
	while (switching) {
		// Start by saying: no switching is done:
		switching = false;
		rows = table.rows;
		/* Loop through all table rows (except the
		first, which contains table headers): */
		for (i = 1; i < (rows.length - 1); i++) {
			// Start by saying there should be no switching:
			shouldSwitch = false;
			/* Get the two elements you want to compare,
			one from current row and one from the next: */
			x = rows[i].getElementsByTagName("td")[n];
			y = rows[i + 1].getElementsByTagName("td")[n];
			/* Check if the two rows should switch place,
			based on the direction, asc or desc: */
			if (dir == "asc") {
				if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
					// If so, mark as a switch and break the loop:
					shouldSwitch = true;
					break;
				}
			} else if (dir == "desc") {
				if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
					// If so, mark as a switch and break the loop:
					shouldSwitch = true;
					break;
				}
			}
		}
		if (shouldSwitch) {
			/* If a switch has been marked, make the switch
			and mark that a switch has been done: */
			rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
			switching = true;
			// Each time a switch is done, increase this count by 1:
			switchcount ++;
		} else {
			/* If no switching has been done AND the direction is "asc",
			set the direction to "desc" and run the while loop again. */
			if (switchcount == 0 && dir == "asc") {
				dir = "desc";
				switching = true;
			}
		}
	}
}

function checkoutPurposeFnc(itemid) {
	var outputPurpose;
	var purposeResponse = prompt("Please enter the purpose of this check out:");
	if (purposeResponse == null || purposeResponse == "") {
		outputPurpose = false;
	} else {
		outputPurpose = purposeResponse;
	}
	document.getElementById("checkoutPurpose_Bundle").value = outputPurpose;
	document.getElementById("checkoutItemId_Bundle").value = itemid;
}
</script>