<?php
//Inventory Settings

$packageAddName = "";
$locationgeneralAddName = "";
$locationspecificAddName = "";

//User clicked a button
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//Add Package
	if (isset($_POST['packageAddBtn'])) {
		$packageAddName = $_POST['packageAdd'];
		
		//Kick back if name wasn't entered
		if ($packageAddName == "") {
			$errorMessage = "You must enter a name in order to create a new package!";
			goto errorEvent;
		};
		
		//Make entry safe
		$packageAddName_sql = htmlspecialchars($packageAddName);
		$packageAddName_sql = TALI_quote_smart($packageAddName_sql, $db_handle);
		
		//Kick back if name already exists
		$SQL = "SELECT id FROM inventory_packages WHERE name = $packageAddName_sql";
		$result = mysqli_query($db_handle, $SQL);
		if ((mysqli_num_rows($result)) > 0) {
			$errorMessage = "The package name you entered already exists!";
			goto errorEvent;
		}
		
		//Go ahead and add it
		$SQL = "INSERT INTO inventory_packages (name, contents) VALUES ($packageAddName_sql, '')";
		$result = mysqli_query($db_handle, $SQL);
		$packageAddId = mysqli_insert_id($db_handle);
		
		//Create history entry
		Inventory_Create_History_Report('created', $db_handle, 'inventory_packages', 'id', $packageAddId, 'Package ID#', 'name');
		
		$successMessage = "Package $packageAddName was created!";
	}
	
	//Update Package
	if (isset($_POST['packageUpdateBtn'])) {
		//Skip if user cancelled any prompts
		if (($_POST['packageUpdate']) == 0) {
			$errorMessage = "The package update was cancelled!";
			goto errorEvent;
		}
		
		//Collect POST data
		$packageId = $_POST['packageUpdate'];
		$packageName_New = $_POST['packageUpdateName'];
		$packageContents = $_POST['packageUpdateContents'];
		
		//Error if any items in package are checked out
		$SQL = "SELECT id FROM inventory WHERE checkedouttype_id = $packageId";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		if ((mysqli_num_rows($result)) > 0) {
			//Package is under use
			$errorMessage = "The package cannot be updated because an item is checked out under it!";
			goto errorEvent;
		}
		
		//Proceed with update
		//Get the old name just in case it changes
		$SQL = "SELECT name FROM inventory_packages WHERE id = $packageId";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		$packageName_Old = $db_field['name'];
		
		//Make entry safe
		$packageName_New_sql = htmlspecialchars($packageName_New);
		$packageName_New_sql = TALI_quote_smart($packageName_New_sql, $db_handle);
		$packageContents_sql = htmlspecialchars($packageContents);
		$packageContents_sql = TALI_quote_smart($packageContents_sql, $db_handle);
		
		$SQL = "UPDATE inventory_packages SET name=$packageName_New_sql, contents=$packageContents_sql WHERE id=$packageId"; 
		$result = mysqli_query($db_handle, $SQL);
		
		//Create history entry
		Inventory_Create_History_Report('updated', $db_handle, 'inventory_packages', 'id', $packageId, 'Package ID#', 'name');
		
		$successMessage = "Package $packageName_Old was updated!";
	}
	
	//Delete package
	if (isset($_POST['packageDeleteBtn'])) {
		$packageId = $_POST['packageDelete'];
		if ($packageId < 1) {
			//User cancelled the prompt
			$errorMessage = "The package deletion was cancelled.";
		}
		else
		{
			//Error if any items in package are checked out
			$SQL = "SELECT id FROM inventory WHERE checkedouttype_id = $packageId";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			if ((mysqli_num_rows($result)) > 0) {
				//Package is under use
				$errorMessage = "The package cannot be deleted because an item is checked out under it!";
				goto errorEvent;
			}
			
			//Delete the package
			
			//Get name from package for history
			$SQL = "SELECT name FROM inventory_packages WHERE id=$packageId";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$packageName=$db_field['name'];	
			
			//Create history entry
			Inventory_Create_History_Report('deleted', $db_handle, 'inventory_packages', 'id', $packageId, 'Package ID#', 'name');
						
			$SQL = "DELETE FROM inventory_packages WHERE id = $packageId";
			$result = mysqli_query($db_handle, $SQL);
		
			$successMessage = "Package $packageName was deleted!";
		}
	}
	
	//Add general location
	if (isset($_POST['locationgeneralAddBtn'])) {
		$locationgeneralAddName = $_POST['locationgeneralAdd'];
		
		//Kick back if name wasn't entered
		if ($locationgeneralAddName == "") {
			$errorMessage = "You must enter a name in order to create a new general location!";
			goto errorEvent;
		};
		
		//Make entry safe
		$locationgeneralAddName_sql = htmlspecialchars($locationgeneralAddName);
		$locationgeneralAddName_sql = TALI_quote_smart($locationgeneralAddName_sql, $db_handle);
		
		//Kick back if name already exists
		$SQL = "SELECT id FROM inventory_location_general WHERE name = $locationgeneralAddName_sql";
		$result = mysqli_query($db_handle, $SQL);
		if ((mysqli_num_rows($result)) > 0) {
			$errorMessage = "The general location name you entered already exists!";
			goto errorEvent;
		}
		
		//Go ahead and add it
		$SQL = "INSERT INTO inventory_location_general (name) VALUES ($locationgeneralAddName_sql)";
		$result = mysqli_query($db_handle, $SQL);
		$locationgeneralAddId = mysqli_insert_id($db_handle);
		
		//Create history entry
		Inventory_Create_History_Report('created', $db_handle, 'inventory_location_general', 'id', $locationgeneralAddId, 'General Location ID#', 'name');
		
		$successMessage = "General Location $locationgeneralAddName was created!";
	}
	
	//Update general location
	if (isset($_POST['locationgeneralUpdateBtn'])) {
		//Skip if user cancelled any prompts
		if (($_POST['locationgeneralUpdate']) == 0) {
			$errorMessage = "The general location update was cancelled!";
			goto errorEvent;
		}
		
		//Collect POST data
		$locationgeneralId = $_POST['locationgeneralUpdate'];
		$locationgeneralName_New = $_POST['locationgeneralUpdateName'];
		
		//Get the old name just in case it changes
		$SQL = "SELECT name FROM inventory_location_general WHERE id = $locationgeneralId";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		$locationgeneralName_Old = $db_field['name'];
		
		//Make entry safe
		$locationgeneralName_New_sql = htmlspecialchars($locationgeneralName_New);
		$locationgeneralName_New_sql = TALI_quote_smart($locationgeneralName_New_sql, $db_handle);
		
		$SQL = "UPDATE inventory_location_general SET name=$locationgeneralName_New_sql WHERE id=$locationgeneralId"; 
		$result = mysqli_query($db_handle, $SQL);
		
		//Create history entry
		Inventory_Create_History_Report('updated', $db_handle, 'inventory_location_general', 'id', $locationgeneralId, 'General Location ID#', 'name');
		
		$successMessage = "General Location $locationgeneralName_Old was updated!";
	}
	
	//Delete general location
	if (isset($_POST['locationgeneralDeleteBtn'])) {
		$locationgeneralId = $_POST['locationgeneralDelete'];
		if ($locationgeneralId < 1) {
			//User cancelled the prompt
			$errorMessage = "The general location deletion was cancelled.";
		}
		else
		{
			//Find items using this general location and reset their general location
			$SQL = "UPDATE inventory SET location_general='' WHERE location_general=$locationgeneralId"; 
			$result = mysqli_query($db_handle, $SQL);
						
			//Get name for history
			$SQL = "SELECT name FROM inventory_location_general WHERE id=$locationgeneralId";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$locationgeneralName=$db_field['name'];	
			
			//Create history entry
			Inventory_Create_History_Report('deleted', $db_handle, 'inventory_location_general', 'id', $locationgeneralId, 'General Location ID#', 'name');
						
			$SQL = "DELETE FROM inventory_location_general WHERE id = $locationgeneralId";
			$result = mysqli_query($db_handle, $SQL);
		
			$successMessage = "General location $locationgeneralName was deleted!";
		}
	}
	
	//Add specific location
	if (isset($_POST['locationspecificAddBtn'])) {
		$locationspecificAddName = $_POST['locationspecificAdd'];
		
		//Kick back if name wasn't entered
		if ($locationspecificAddName == "") {
			$errorMessage = "You must enter a name in order to create a new specific location!";
			goto errorEvent;
		};
		
		//Make entry safe
		$locationspecificAddName_sql = htmlspecialchars($locationspecificAddName);
		$locationspecificAddName_sql = TALI_quote_smart($locationspecificAddName_sql, $db_handle);
		
		//Kick back if name already exists
		$SQL = "SELECT id FROM inventory_location_specific WHERE name = $locationspecificAddName_sql";
		$result = mysqli_query($db_handle, $SQL);
		if ((mysqli_num_rows($result)) > 0) {
			$errorMessage = "The specific location name you entered already exists!";
			goto errorEvent;
		}
		
		//Go ahead and add it
		$SQL = "INSERT INTO inventory_location_specific (name) VALUES ($locationspecificAddName_sql)";
		$result = mysqli_query($db_handle, $SQL);
		$locationspecificAddId = mysqli_insert_id($db_handle);
		
		//Create history entry
		Inventory_Create_History_Report('created', $db_handle, 'inventory_location_specific', 'id', $locationspecificAddId, 'Specific Location ID#', 'name');
		
		$successMessage = "Specific Location $locationspecificAddName was created!";
	}
	
	//Update specific location
	if (isset($_POST['locationspecificUpdateBtn'])) {
		//Skip if user cancelled any prompts
		if (($_POST['locationspecificUpdate']) == 0) {
			$errorMessage = "The specific location update was cancelled!";
			goto errorEvent;
		}
		
		//Collect POST data
		$locationspecificId = $_POST['locationspecificUpdate'];
		$locationspecificName_New = $_POST['locationspecificUpdateName'];
		
		//Get the old name just in case it changes
		$SQL = "SELECT name FROM inventory_location_specific WHERE id = $locationspecificId";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		$locationspecificName_Old = $db_field['name'];
		
		//Make entry safe
		$locationspecificName_New_sql = htmlspecialchars($locationspecificName_New);
		$locationspecificName_New_sql = TALI_quote_smart($locationspecificName_New_sql, $db_handle);
		
		$SQL = "UPDATE inventory_location_specific SET name=$locationspecificName_New_sql WHERE id=$locationspecificId"; 
		$result = mysqli_query($db_handle, $SQL);
		
		//Create history entry
		Inventory_Create_History_Report('updated', $db_handle, 'inventory_location_specific', 'id', $locationspecificId, 'Specific Location ID#', 'name');
		
		$successMessage = "Specific Location $locationspecificName_Old was updated!";
	}
	
	//Delete specific location
	if (isset($_POST['locationspecificDeleteBtn'])) {
		$locationspecificId = $_POST['locationspecificDelete'];
		if ($locationspecificId < 1) {
			//User cancelled the prompt
			$errorMessage = "The specific location deletion was cancelled.";
		}
		else
		{
			//Find items using this specific location and reset their specific location
			$SQL = "UPDATE inventory SET location_specific='' WHERE location_specific=$locationspecificId"; 
			$result = mysqli_query($db_handle, $SQL);
						
			//Get name for history
			$SQL = "SELECT name FROM inventory_location_specific WHERE id=$locationspecificId";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$locationspecificName=$db_field['name'];	
			
			//Create history entry
			Inventory_Create_History_Report('deleted', $db_handle, 'inventory_location_specific', 'id', $locationspecificId, 'Specific Location ID#', 'name');
						
			$SQL = "DELETE FROM inventory_location_specific WHERE id = $locationspecificId";
			$result = mysqli_query($db_handle, $SQL);
		
			$successMessage = "Specific location $locationspecificName was deleted!";
		}
	}
}

//Errors jump in here
errorEvent: 

//Start building page
echo "
			<div class=\"tali-page-frame\">
				<h1>Inventory Settings</h1>
";

if ($errorMessage != "") {
	echo "
				<p><font color=\"red\">$errorMessage</font></p>
	";
};

if ($successMessage != "") {
	echo "
				<p><font color=\"green\">$successMessage</font></p>
	";
};

echo "
			</div>
";

//Table for viewing packages
echo "
			<div class=\"tali-page-frame\">
				<h1>Manage Packages</h1>
				<form method=\"POST\" action=\"inventory.php\">
				<input type=\"hidden\" id=\"packageUpdate\" name=\"packageUpdate\">
				<input type=\"hidden\" id=\"packageUpdateName\" name=\"packageUpdateName\">
				<input type=\"hidden\" id=\"packageUpdateContents\" name=\"packageUpdateContents\">
				<input type=\"hidden\" id=\"packageDelete\" name=\"packageDelete\">
				<table class=\"inventoryBrowseTable\">
					<col width=\"5%\">
					<col width=\"15%\">
					<col width=\"50%\">
					<col width=\"20%\">
					<col width=\"5%\">
					<col width=\"5%\">
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Contents by Name</th>
						<th>Contents by Item ID</th>
						<th>Update</th>
						<th>Delete</th>
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

	echo "
					<tr>
						<td>$packageId</td>
						<td><a href=\"inventory.php?package=$packageId\">$packageName</a></td>
						<td>$packageContents_NameString</td>
						<td>$packageContents</td>
						<td style=\"text-align:center;\">
							<input type=\"Submit\" name=\"packageUpdateBtn\" value=\"Update\" onclick=\"packageUpdateFnc($packageId, '$packageName', '$packageContents')\">
						</td>
						<td style=\"text-align:center;\">
							<input type=\"Submit\" name=\"packageDeleteBtn\" value=\"Delete\" onclick=\"packageDeleteFnc($packageId)\">
						</td>
					</tr>
	";
}

echo "
				</table>
				</form>
";
//Create new package just by adding a name
echo "
				<h1>Create Package</h1>
				<p>To create a new package, enter the desired package name below and click Create New Package, then update its contents above.</p>
				<form method=\"POST\" id=\"packageAddForm\" action=\"inventory.php\">
					<p>
					<input type=\"text\" name=\"packageAdd\" value=\"$packageAddName\">
					<br/>
					<br/>
					<input type=\"Submit\" name=\"packageAddBtn\" value=\"Create New Package\">
					</p>
				</form>
			</div>
";

//Table for viewing General Locations
echo "
			<div class=\"tali-page-frame\">
				<h1>Manage General Locations</h1>
				<form method=\"POST\" action=\"inventory.php\">
				<input type=\"hidden\" id=\"locationgeneralUpdate\" name=\"locationgeneralUpdate\">
				<input type=\"hidden\" id=\"locationgeneralUpdateName\" name=\"locationgeneralUpdateName\">
				<input type=\"hidden\" id=\"locationgeneralDelete\" name=\"locationgeneralDelete\">
				<table class=\"inventoryBrowseTable\">
					<col width=\"5%\">
					<col width=\"85%\">
					<col width=\"5%\">
					<col width=\"5%\">
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Update</th>
						<th>Delete</th>
					</tr>
";

//Get general location data
$SQL = "SELECT * FROM inventory_location_general";
$result = mysqli_query($db_handle, $SQL);
while ($db_field = mysqli_fetch_assoc($result)) {
	$locationgeneralId = $db_field['id'];
	$locationgeneralName = $db_field['name'];

	echo "
					<tr>
						<td>$locationgeneralId</td>
						<td><a href=\"inventory.php?generalLocation=$locationgeneralId\">$locationgeneralName</a></td>
						<td style=\"text-align:center;\">
							<input type=\"Submit\" name=\"locationgeneralUpdateBtn\" value=\"Update\" onclick=\"locationgeneralUpdateFnc($locationgeneralId, '$locationgeneralName')\">
						</td>
						<td style=\"text-align:center;\">
							<input type=\"Submit\" name=\"locationgeneralDeleteBtn\" value=\"Delete\" onclick=\"locationgeneralDeleteFnc($locationgeneralId)\">
						</td>
					</tr>
	";
}

echo "
				</table>
				</form>
";
//Create new general location just by adding a name
echo "
				<h1>Create General Location</h1>
				<p>To create a new general location, enter the desired package name below and click Create New General Location.</p>
				<form method=\"POST\" id=\"locationgeneralAddForm\" action=\"inventory.php\">
					<p>
					<input type=\"text\" name=\"locationgeneralAdd\" value=\"$locationgeneralAddName\">
					<br/>
					<br/>
					<input type=\"Submit\" name=\"locationgeneralAddBtn\" value=\"Create New General Location\">
					</p>
				</form>
			</div>
";

//Table for viewing Specific Locations
echo "
			<div class=\"tali-page-frame\">
				<h1>Manage Specific Locations</h1>
				<form method=\"POST\" action=\"inventory.php\">
				<input type=\"hidden\" id=\"locationspecificUpdate\" name=\"locationspecificUpdate\">
				<input type=\"hidden\" id=\"locationspecificUpdateName\" name=\"locationspecificUpdateName\">
				<input type=\"hidden\" id=\"locationspecificDelete\" name=\"locationspecificDelete\">
				<table class=\"inventoryBrowseTable\">
					<col width=\"5%\">
					<col width=\"85%\">
					<col width=\"5%\">
					<col width=\"5%\">
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Update</th>
						<th>Delete</th>
					</tr>
";

//Get specific location data
$SQL = "SELECT * FROM inventory_location_specific";
$result = mysqli_query($db_handle, $SQL);
while ($db_field = mysqli_fetch_assoc($result)) {
	$locationspecificId = $db_field['id'];
	$locationspecificName = $db_field['name'];

	echo "
					<tr>
						<td>$locationspecificId</td>
						<td><a href=\"inventory.php?specificLocation=$locationspecificId\">$locationspecificName</a></td>
						<td style=\"text-align:center;\">
							<input type=\"Submit\" name=\"locationspecificUpdateBtn\" value=\"Update\" onclick=\"locationspecificUpdateFnc($locationspecificId, '$locationspecificName')\">
						</td>
						<td style=\"text-align:center;\">
							<input type=\"Submit\" name=\"locationspecificDeleteBtn\" value=\"Delete\" onclick=\"locationspecificDeleteFnc($locationspecificId)\">
						</td>
					</tr>
	";
}

echo "
				</table>
				</form>
";
//Create new specific location just by adding a name
echo "
				<h1>Create Specific Location</h1>
				<p>To create a new specific location, enter the desired package name below and click Create New Specific Location.</p>
				<form method=\"POST\" id=\"locationspecificAddForm\" action=\"inventory.php\">
					<p>
					<input type=\"text\" name=\"locationspecificAdd\" value=\"$locationspecificAddName\">
					<br/>
					<br/>
					<input type=\"Submit\" name=\"locationspecificAddBtn\" value=\"Create New Specific Location\">
					</p>
				</form>
			</div>
";

//Finish the page
echo "
		</div>
	</main>
";

?>

<script>
function packageUpdateFnc(packageid, packagename, packagecontents) {
	//Create variables to use
	var packageUpdate;
	var packageUpdateName;
	var packageUpdateContents;
	
	//Prompt to update name
	var nameResponse = prompt("Enter package name. Click Ok to use this package name:", packagename);
	if (nameResponse == null || nameResponse == "") {
		//User hit cancel or left name blank, close out of this stuff
		packageUpdate = 0;
	} else {
		packageUpdate = packageid;
		packageUpdateName = nameResponse;
	}
	
	if (packageUpdate == 0) {
		//User hit cancel earlier, so don't display next prompt
	}
	else
	{
		//User will continue
		var contentsResponse = prompt("Enter package contents (as ID's) in the format of '1234,1235' without quotes. Click Ok to use this package contents:", packagecontents);
		if (contentsResponse == null) {
			//User hit cancel, close out of this stuff
			packageUpdate = 0;
		} else {
			packageUpdate = packageid;
			packageUpdateContents = contentsResponse;
		}
	}
	
	//Set POST input values
	document.getElementById("packageUpdate").value = packageUpdate;
	document.getElementById("packageUpdateName").value = packageUpdateName;
	document.getElementById("packageUpdateContents").value = packageUpdateContents;
}

function packageDeleteFnc(packageid) {
	if (confirm("Are you sure you want to delete this package? This will not delete the items it lists as contents.")) {
		//Yes, delete
		document.getElementById("packageDelete").value = packageid;
	}
	else
	{
		//No, return to settings and announce deletion was cancelled
	}
}

function locationgeneralUpdateFnc(locationgeneralid, locationgeneralname) {
	//Create variables to use
	var locationgeneralUpdate;
	var locationgeneralUpdateName;
	
	//Prompt to update name
	var nameResponse = prompt("Enter general location name. Click Ok to use this general location name:", locationgeneralname);
	if (nameResponse == null || nameResponse == "") {
		//User hit cancel or left name blank, close out of this stuff
		locationgeneralUpdate = 0;
	} else {
		locationgeneralUpdate = locationgeneralid;
		locationgeneralUpdateName = nameResponse;
	}
	
	//Set POST input values
	document.getElementById("locationgeneralUpdate").value = locationgeneralUpdate;
	document.getElementById("locationgeneralUpdateName").value = locationgeneralUpdateName;
}

function locationgeneralDeleteFnc(locationgeneralid) {
	if (confirm("Are you sure you want to delete this general location? This will reset the general location of items assigned to it.")) {
		//Yes, delete
		document.getElementById("locationgeneralDelete").value = locationgeneralid;
	}
	else
	{
		//No, return to settings and announce deletion was cancelled
	}
}

function locationspecificUpdateFnc(locationspecificid, locationspecificname) {
	//Create variables to use
	var locationspecificUpdate;
	var locationspecificUpdateName;
	
	//Prompt to update name
	var nameResponse = prompt("Enter specific location name. Click Ok to use this specific location name:", locationspecificname);
	if (nameResponse == null || nameResponse == "") {
		//User hit cancel or left name blank, close out of this stuff
		locationspecificUpdate = 0;
	} else {
		locationspecificUpdate = locationspecificid;
		locationspecificUpdateName = nameResponse;
	}
	
	//Set POST input values
	document.getElementById("locationspecificUpdate").value = locationspecificUpdate;
	document.getElementById("locationspecificUpdateName").value = locationspecificUpdateName;
}

function locationspecificDeleteFnc(locationspecificid) {
	if (confirm("Are you sure you want to delete this specific location? This will reset the specific location of items assigned to it.")) {
		//Yes, delete
		document.getElementById("locationspecificDelete").value = locationspecificid;
	}
	else
	{
		//No, return to settings and announce deletion was cancelled
	}
}
</script>