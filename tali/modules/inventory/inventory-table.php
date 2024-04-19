<?php
//Open page's div
echo "			
			<div class=\"tali-page-frame\">
";

//Set page header, default as Active Items for main table of active items
$headerText = "Active Items";

//Main archived items table
if (isset($_GET['archive'])) {
	$headerText = "Archived Items"; 
}

//Main archived items table
if (isset($_GET['generalLocation'])) {
	$SQL = "SELECT name FROM inventory_location_general WHERE id=".$_GET['generalLocation']."";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);
	$headerText = "Active Items - General Location - ".$db_field['name'].""; 
}

//Main archived items table
if (isset($_GET['specificLocation'])) {
	//Make array of existing id's
	$specificLocationId = $_GET['specificLocation'];
	$SQL = "SELECT id FROM inventory_location_specific";
	$result = mysqli_query($db_handle, $SQL);
	$specificIdArray = []; 
	while ($db_field = mysqli_fetch_assoc($result)) {
		$specificIdArray[] = $db_field['id'];
	}
	
	if (in_array($_GET['specificLocation'],$specificIdArray)) {
		//id exists, so get the name and use it
		$SQL = "SELECT name FROM inventory_location_specific WHERE id=".$_GET['specificLocation']."";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		$useThisName = $db_field['name'];
	}
	else
	{
		//id doesn't exist, so this is actually the name, so use it
		$useThisName = $_GET['specificLocation'];
	}
	
	$headerText = "Active Items - Bundle/Specific Location - $useThisName"; 
}

//Main archived items table
if (isset($_GET['package'])) {
	$SQL = "SELECT name FROM inventory_packages WHERE id=".$_GET['package']."";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);
	$headerText = "Active Items - Package - ".$db_field['name'].""; 
}

//Display page header
echo "
				<h1>$headerText</h1>
";

//Open form used for Checkout function and build table of inventory
echo "
				<form method=\"POST\" action=\"inventory.php\">
				<input type=\"hidden\" id=\"checkoutPurpose\" name=\"checkoutPurpose\">
				<input type=\"hidden\" id=\"checkoutItemId\" name=\"checkoutItemId\">
				<table id=\"inventoryTable\" class=\"inventoryBrowseTable\">
					<col width=\"5%\">
					<col width=\"15%\">
					<col width=\"30%\">
					<col width=\"20%\">
					<col width=\"20%\">
					<col width=\"10%\">
					<tr>
						<th onclick=\"sortTable_num(0)\">ID</th>
						<th onclick=\"sortTable(1)\">Name</th>
						<th onclick=\"sortTable(2)\">Description</th>
						<th onclick=\"sortTable(3)\">General Location</th>
						<th onclick=\"sortTable(4)\">Specific Location</th>
						<th onclick=\"sortTable(5)\">Check Out</th>
					</tr>
";

//Handle Archive - Show only archived items if selected, otherwise show non-archived items
if (isset($_GET['archive'])) {
	//Show archived items
	$SQL = "SELECT * FROM inventory WHERE status=2 ORDER BY id ASC";
}
else
{
	//Show non-archived items
	$SQL = "SELECT * FROM inventory WHERE NOT status=2 ORDER BY id ASC";
}

//Handle Location Filter - Show only items in the same general or specific (whichever was clicked) location as the clicked item id
if (isset($_GET['generalLocation'])) {
	$SQL = "SELECT * FROM inventory WHERE NOT status=2 AND location_general=".$_GET['generalLocation']." ORDER BY id ASC";
}

if (isset($_GET['specificLocation'])) {
	$SQL = "SELECT id FROM inventory_location_specific";
	$result = mysqli_query($db_handle, $SQL);
	$specificIdArray = []; 
	while ($db_field = mysqli_fetch_assoc($result)) {
		$specificIdArray[] = $db_field['id'];
	}
	
	if (in_array($_GET['specificLocation'],$specificIdArray)) {
		//Manage based on existing specific location options in settings
		$SQL = "SELECT * FROM inventory WHERE NOT status=2 AND location_specific=".$_GET['specificLocation']." ORDER BY id ASC";
	}
	else
	{
		//Manage based on fill-in specific location
		$specificStr = htmlspecialchars($_GET['specificLocation']);
		$specificStr = TALI_quote_smart($specificStr, $db_handle);
		$SQL = "SELECT * FROM inventory WHERE NOT status=2 AND location_specific=$specificStr ORDER BY id ASC";
	}
}

//Handle if viewing Package - Note package contents are only stored in the package entry itself, not in the individual items as in the general and specific location groups
if (isset($_GET['package'])) {
	//Get array of item id's in the package
	$SQL = "SELECT contents FROM inventory_packages WHERE id=".$_GET['package']."";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);
	
	//If package is empty, have to skip below SQL loop to avoid error
	if ($db_field['contents'] == "") {
		goto PackageEmpty;
	}
	
	//Select those item id's from the inventory dataset
	$SQL = "SELECT * FROM inventory WHERE NOT status=2 AND id IN (".$db_field['contents'].") ORDER BY id ASC";
}

//Proceed with appropriate filter
$result = mysqli_query($db_handle, $SQL);
$num_rows = mysqli_num_rows($result);

while ($db_field = mysqli_fetch_assoc($result)) {
	$id=$db_field['id'];
	$name=$db_field['name'];
	$description=$db_field['description'];
	$checkedoutby=$db_field['checkedoutby'];
	$generalLocation=$db_field['location_general'];
	
	$generalLocationName = $generalLocation;
	//Grab the specific location's name if it was chosen
	if ($generalLocationName > 0) {
		$namegenSQL = "SELECT name FROM inventory_location_general WHERE id = $generalLocation";
		$namegenresult = mysqli_query($db_handle, $namegenSQL);
		$namegendb_field = mysqli_fetch_assoc($namegenresult);
		$generalLocationName = $namegendb_field['name'];
	}
	else
	{
		$generalLocationName = "";
	}
	
	$specificLocation=$db_field['location_specific'];
	
	$specificLocationName = $specificLocation;
	//Grab the specific location's name if it is standardized
	if (is_numeric($specificLocationName)) {
		$nameSQL = "SELECT name FROM inventory_location_specific WHERE id = $specificLocation";
		$nameresult = mysqli_query($db_handle, $nameSQL);
		$namedb_field = mysqli_fetch_assoc($nameresult);
		$specificLocationName = $namedb_field['name'];
	}
	
	echo "
					<tr>
						<td>$id</td>
						<td><input type=\"hidden\" value=\"$name\"</input><a href=\"inventory.php?id=$id\">$name</a></td>
						<td>$description</td>
						<td><input type=\"hidden\" value=\"$generalLocationName\"</input><a href=\"inventory.php?generalLocation=$generalLocation\">$generalLocationName</a></td>
						<td><input type=\"hidden\" value=\"$specificLocationName\"</input><a href=\"inventory.php?specificLocation=$specificLocation\">$specificLocationName</a></td>			
						<td style=\"text-align:center;\">
	";
	//If item isn't checked out, allow the option, otherwise put the name of the user who has it
	if ($checkedoutby == 0) {
		if (isset($_GET['archive'])) {
			//Item is archived, so can't checkout
			echo "
								Archived
			";
		}
		else
		{
			//Item not archived, can checkout
			echo "
								<input type=\"Submit\" name=\"inventoryCheckOut\" value=\"Check Out\" onclick=\"checkoutPurposeFnc($id)\">
			"; 
		}
	}
	else
	{
		$whoSQL = "SELECT username FROM tali_admin_accounts WHERE id = $checkedoutby";
		$whoresult = mysqli_query($db_handle, $whoSQL);
		$whodb_field = mysqli_fetch_assoc($whoresult);
		$whousername=$whodb_field['username'];
		echo "
								<a href=\"inventory.php?userid=$checkedoutby\">$whousername</a>
		";
	}
	echo "
						</td>
					</tr>
	";
}

PackageEmpty:

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

function sortTable_num(n) {
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
				if (Number(x.innerHTML) > Number(y.innerHTML)) {
					// If so, mark as a switch and break the loop:
					shouldSwitch = true;
					break;
				}
			} else if (dir == "desc") {
				if (Number(x.innerHTML) < Number(y.innerHTML)) {
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
	document.getElementById("checkoutPurpose").value = outputPurpose;
	document.getElementById("checkoutItemId").value = itemid;
}
</script>