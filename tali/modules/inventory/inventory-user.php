<?php
//Collect user's data
$username_id = $_GET['userid']; 
$SQL = "SELECT * FROM tali_admin_accounts WHERE id = $username_id";
$result = mysqli_query($db_handle, $SQL);
$db_field = mysqli_fetch_assoc($result);
$username=$db_field['username'];

echo "
			<div class=\"tali-page-frame\">
				<h1>Items Checked Out by $username</h1>
				<form method=\"POST\" action=\"inventory.php\">
				<input type=\"hidden\" id=\"checkinUpdate\" name=\"checkinUpdate\">
				<input type=\"hidden\" id=\"checkinItemId\" name=\"checkinItemId\">
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
						<th>Check In</th>
					</tr>
";

//Show items checked out by user
$SQL = "SELECT * FROM inventory WHERE checkedoutby=$username_id";
$result = mysqli_query($db_handle, $SQL);
$num_rows = mysqli_num_rows($result);

while ($db_field = mysqli_fetch_assoc($result)) {
	$id=$db_field['id'];
	$name=$db_field['name'];
	$description=$db_field['description'];
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
						<td><input type=\"hidden\" value=\"$generalLocation\"</input><a href=\"inventory.php?generalLocation=$generalLocation\">$generalLocationName</a></td>
						<td><input type=\"hidden\" value=\"$specificLocation\"</input><a href=\"inventory.php?specificLocation=$specificLocation\">$specificLocationName</a></td>
						<td style=\"text-align:center;\">
	";
	if (($username_id == $_SESSION['TALI_Username_ID']) || (($_SESSION['TALI_User_Level']) < 3)) {
		//This is the user or admin, so allow check in
		echo "
							<input type=\"Submit\" name=\"inventoryCheckIn\" value=\"Check In\" onclick=\"checkinUpdateFnc($id)\">
		";
	}
	else
	{
		//Not the user, don't allow check in
		echo "
							Contact User
		";
	}
	echo "
						</td>
					</tr>
	";
}
echo "
				</table>
			</div>
";

//New table to list bundles and packages checked out to this user
echo "
			<div class=\"tali-page-frame\">
				<h1>Bundles/Packages Checked Out by $username</h1>
				<form method=\"POST\" action=\"inventory.php\">
				<input type=\"hidden\" id=\"checkinUpdate_Group\" name=\"checkinUpdate_Group\">
				<input type=\"hidden\" id=\"checkinItemId_Group\" name=\"checkinItemId_Group\">
				<table id=\"inventoryTable_2\" class=\"inventoryBrowseTable\">
					<col width=\"20%\">
					<col width=\"70%\">
					<col width=\"10%\">
					<tr>
						<th onclick=\"sortTable_2(0)\">Name</th>
						<th>Contents</th>
						<th>Check In</th>
					</tr>
";

//Grab all items checked out by user as part of a group
$SQL = "SELECT id, location_specific, checkedouttype, checkedouttype_id FROM inventory WHERE checkedoutby=$username_id AND (checkedouttype=1 OR checkedouttype=2)";
$result = mysqli_query($db_handle, $SQL);
$num_rows = mysqli_num_rows($result);
$bundleidArray = [];
$packageidArray = [];

while ($db_field = mysqli_fetch_assoc($result)) {
	$groupType = $db_field['checkedouttype'];
	if ($groupType == 1) {
		//Item was in a bundle
		$bundle_id = $db_field['checkedouttype_id'];
		
		//Check if this bundle is already handled
		if (in_array($bundle_id, $bundleidArray)) {
			//Duplicate, so skip
			continue; 
		}
		
		//Handle new bundle
		$specificLocation=$db_field['location_specific'];
		$one_id=$db_field['id'];
		
		//Get name and ID for listing individual contents with hyperlink and colored based on bundle
		$contentsSQL = "SELECT name, id, checkedoutby FROM inventory WHERE checkedouttype_id = $bundle_id";
		$contentsresult = mysqli_query($db_handle, $contentsSQL);
		$contents = "";
		
		$specificLocationName = $specificLocation;
		//Grab the specific location's name if it is standardized
		if (is_numeric($specificLocationName)) {
			$nameSQL = "SELECT name FROM inventory_location_specific WHERE id = $specificLocation";
			$nameresult = mysqli_query($db_handle, $nameSQL);
			$namedb_field = mysqli_fetch_assoc($nameresult);
			$specificLocationName = $namedb_field['name'];
		}
		
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
		";
		if (($username_id == $_SESSION['TALI_Username_ID']) || (($_SESSION['TALI_User_Level']) < 3)) {
			//This is the user or admin, so allow check in
			echo "
							<input type=\"Submit\" name=\"inventoryCheckIn_Bundle\" value=\"Check In\" onclick=\"checkinUpdateFnc_Bundle($one_id)\">
			";
		}
		else
		{
			//Not the user, don't allow check in
			echo "
							Contact User
			";
		}
		echo "
						</td>
					</tr>
		";
		
		//Add this bundle to the array to avoid duplication
		$bundleidArray[] = $bundle_id;
	}
	else
	{
		//Item was in a package
		$package_id = $db_field['checkedouttype_id'];
		
		//Check if this package is already handled
		if (in_array($package_id, $packageidArray)) {
			//Duplicate, so skip
			continue; 
		}
		
		//Get name and ID for listing individual contents with hyperlink and colored based on package
		$contentsSQL = "SELECT name, id, checkedoutby FROM inventory WHERE checkedouttype_id = $package_id";
		$contentsresult = mysqli_query($db_handle, $contentsSQL);
		$contents = "";
		
		//Grab the package's name
		$nameSQL = "SELECT name FROM inventory_packages WHERE id = $package_id";
		$nameresult = mysqli_query($db_handle, $nameSQL);
		$namedb_field = mysqli_fetch_assoc($nameresult);
		$packageName = $namedb_field['name'];
		
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
						<td><a href=\"inventory.php?package=$package_id\">$packageName</a></td>			
						<td>$contents</td>
						<td style=\"text-align:center;\">
		";
		if (($username_id == $_SESSION['TALI_Username_ID']) || (($_SESSION['TALI_User_Level']) < 3)) {
			//This is the user or admin, so allow check in
			echo "
							<input type=\"Submit\" name=\"inventoryCheckIn_Package\" value=\"Check In\" onclick=\"checkinUpdateFnc_Package($package_id)\">
			";
		}
		else
		{
			//Not the user, don't allow check in
			echo "
							Contact User
			";
		}
		echo "
						</td>
					</tr>
		";
		
		//Add this bundle to the array to avoid duplication
		$packageidArray[] = $package_id;
	}
}


echo "
				</table>
				</form>
"; 

echo "
			</div>
";

//If this user or admin, include users's history report
if (($username_id == $_SESSION['TALI_Username_ID']) || (($_SESSION['TALI_User_Level']) < 3)) {
	//Create history table (basically the master history table but restricted to this username id)
	echo "		
			<div class=\"tali-page-frame\">
				<h1>History Report</h1>
				<table id=\"inventoryHistory\" class=\"inventoryBrowseTable\" style=\"font-size: 62.5%;\">
					<col width=\"5%\">
					<col width=\"12%\">
					<col width=\"5%\">
					<col width=\"5%\">
					<col width=\"73%\">
					<tr>
						<th>ID</th>
						<th>Time</th>
						<th>User ID</th>
						<th>Item ID</th>
						<th>Event</th>
					</tr>
	";

	$SQL = "SELECT * FROM inventory_master_history WHERE username_id=$username_id ORDER BY id DESC";
	$result = mysqli_query($db_handle, $SQL);
	$num_rows = mysqli_num_rows($result);

	while ($db_field = mysqli_fetch_assoc($result)) {
		$id=$db_field['id'];
		$time=$db_field['time'];
		$username_id=$db_field['username_id'];
		$item_id=$db_field['item_id'];
		$event=$db_field['event'];
		echo "
					<tr>
						<td style=\"text-align:center;\">$id</td>
						<td style=\"text-align:center;\">$time</td>
						<td style=\"text-align:center;\"><a href=\"inventory.php?userid=$username_id\">$username_id</a></td>
						<td style=\"text-align:center;\"><a href=\"inventory.php?id=$item_id\">$item_id</a></td>
						<td style=\"text-align:left;\">$event</td>
					</tr>
		";
	}
	echo "
				</table>
			</div>
	";
}

echo "
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

function sortTable_2(n) {
	var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
	table = document.getElementById("inventoryTable_2");
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

function checkinUpdateFnc(itemid) {
	var checkInResponse = prompt("Does Logistics need to be notified regarding something about the item, such as a change in condition or need for maintenance? If yes, enter details in the field. Either way, click Ok to check in or Cancel to abort.");
	if (checkInResponse == "") {
		//Check in, no update
		document.getElementById("checkinUpdate").value = 1;
	}
	
	if (checkInResponse != "") {
		//Check in, send update
		document.getElementById("checkinUpdate").value = checkInResponse;
	}
	
	if (checkInResponse == null) {
		//User aborted
		document.getElementById("checkinUpdate").value = 0;
	}
	
	document.getElementById("checkinItemId").value = itemid;
}

function checkinUpdateFnc_Bundle(itemid) {
	var checkInResponse = prompt("Does Logistics need to be notified regarding something about the bundle, such as a change in condition or need for maintenance? If yes, enter details in the field. Either way, click Ok to check in or Cancel to abort.");
	if (checkInResponse == "") {
		//Check in, no update
		document.getElementById("checkinUpdate_Group").value = 1;
	}
	
	if (checkInResponse != "") {
		//Check in, send update
		document.getElementById("checkinUpdate_Group").value = checkInResponse;
	}
	
	if (checkInResponse == null) {
		//User aborted
		document.getElementById("checkinUpdate_Group").value = 0;
	}
	
	document.getElementById("checkinItemId_Group").value = itemid;
}

function checkinUpdateFnc_Package(packageid) {
	var checkInResponse = prompt("Does Logistics need to be notified regarding something about the package, such as a change in condition or need for maintenance? If yes, enter details in the field. Either way, click Ok to check in or Cancel to abort.");
	if (checkInResponse == "") {
		//Check in, no update
		document.getElementById("checkinUpdate_Group").value = 1;
	}
	
	if (checkInResponse != "") {
		//Check in, send update
		document.getElementById("checkinUpdate_Group").value = checkInResponse;
	}
	
	if (checkInResponse == null) {
		//User aborted
		document.getElementById("checkinUpdate_Group").value = 0;
	}
	
	document.getElementById("checkinItemId_Group").value = packageid;
}
</script>