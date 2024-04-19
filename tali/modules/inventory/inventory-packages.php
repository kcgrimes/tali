<?php
//Open form used for Checkout function and build table of inventory
echo "			
			<div class=\"tali-page-frame\">
				<h1>Packages</h1>
				<form method=\"POST\" action=\"inventory.php\">
				<input type=\"hidden\" id=\"checkoutPurpose_Package\" name=\"checkoutPurpose_Package\">
				<input type=\"hidden\" id=\"checkoutItemId_Package\" name=\"checkoutItemId_Package\">
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

//Get package data
$SQL = "SELECT * FROM inventory_packages";
$result = mysqli_query($db_handle, $SQL);
while ($db_field = mysqli_fetch_assoc($result)) {
	$packageId=$db_field['id'];
	$packageName=$db_field['name'];
	$packageContents=$db_field['contents'];
	$packageContents_NameString = "";
	if ($packageContents != "") {
		//Replace item id's with names as another display format
		$packageContents_IDArray = explode(",", $packageContents);
			//Must use ","
		forEach ($packageContents_IDArray as $id) {
			//Take name and hyperlink
			$pacSQL = "SELECT name, checkedoutby FROM inventory WHERE id=$id";
			$pacresult = mysqli_query($db_handle, $pacSQL);
			$pacdb_field = mysqli_fetch_assoc($pacresult);
			
			//Color green if available, red if checked out
			if ($pacdb_field['checkedoutby'] == 0) {
				$color = "green";
			}
			else
			{
				$color = "red";
			}
			
			$packageContents_NameString = $packageContents_NameString . "<a style=\"color:".$color."\"; href=\"inventory.php?id=".$id."\">" . $name = $pacdb_field['name'] . "</a>; ";
		}
	}
	else
	{
		//Package is empty
		continue;
	}

	echo "
					<tr>
						<td><input type=\"hidden\" value=\"$packageName\"</input><a href=\"inventory.php?package=$packageId\">$packageName</a></td>
						<td>$packageContents_NameString</td>
						<td style=\"text-align:center;\">
							<input type=\"Submit\" name=\"inventoryCheckOut\" value=\"Check Out\" onclick=\"checkoutPurposeFnc($packageId)\">
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
	document.getElementById("checkoutPurpose_Package").value = outputPurpose;
	document.getElementById("checkoutItemId_Package").value = itemid;
}
</script>