<?php
//Inventory Module - Custom for TCSAR
	//Changes made to tali's index-main.php to accomodate for this, along with adding module to admin permissions
	
//Just a little CSS specific to this module
echo "
<style>
	.inventoryBrowseTable tr:nth-child(even) {
		background: #DDD;
	}
	
	.inventoryBrowseTable tr:nth-child(odd) {
		background: #FFF;
	}
	
	.inventoryBrowseTable {
		width: 94%;
		border: 1px solid black;
		border-collapse: collapse;
		margin-left: auto;
		margin-right: auto;
		margin-bottom: 15px; 
	}

	.inventoryBrowseTable tr, .inventoryBrowseTable th, .inventoryBrowseTable td {
		border: 1px solid black;
	}
	
	.inventoryBrowseTable th {
		cursor: pointer; 
	}
</style>
";

/*
Function - Inventory_Create_History_Report (adapted from TALI_Create_History_Report)
Used to efficiently make a statement that is then inserted into the DB inventory history, 
in addition to collecting other relevant tracking information.
e.g. 2015-08-27 20:16:44 - Item ID# 99, Name of Item, created by email@mail.org
Select 1 - Action (string) to be read, e.g. 'created'
Select 2 - MySQLi connection return
Select 3 - Name of DB Table that is encountering change (string) e.g. 'inventory'
Select 4 - Column name containing ID of subject (string) e.g. 'id'
Select 5 - ID of item (integer)
Select 6 - Name/designation of item's ID (string) e.g. 'Item ID#'
Select 7 - Name of column containing the display name of the item (string) e.g. 'name'
*/
function Inventory_Create_History_Report($action_str, $db_handle, $db_Table, $db_Table_id, $item_id, $id_type_str, $db_item_col) {
	$SQL = "SELECT * FROM $db_Table WHERE $db_Table_id=$item_id";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);

	$time = date('Y-m-d H:i:s');
	
	$item_name = $db_field[$db_item_col];
	$username = $_SESSION['TALI_Username'];
	$username_id = $_SESSION['TALI_Username_ID'];
	
	$insertHistory = "$time - $id_type_str $item_id, $item_name, $action_str by $username";
	$insertHistory_sql = TALI_quote_smart($insertHistory, $db_handle);
	
	//Master history report
	$SQL = "INSERT INTO inventory_master_history (time, username_id, item_id, event) VALUES (CURRENT_TIMESTAMP, $username_id, $item_id, $insertHistory_sql)";
	$result = mysqli_query($db_handle, $SQL);
		
	return $insertHistory;
}

//Stock variables
$module = "Inventory_Browser";
$errorMessage = "";
$successMessage = "";
$uname = "";
$pword = "";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}

TALI_sessionCheck($module, $db_handle);

//User is logged in, proceed

//Open page
	//Stretch to fill window, preferred for the table viewing
//Display banner options
echo "
	<main>
		<div class=\"tali-container\" style=\"margin-right: 0px;margin-left: 0px; width: 100%;\">
			<div class=\"tali-page-frame\">
				<h1 style=\"text-align:left;\"><a href=\"inventory.php\">Inventory Browser</a></h1>
				<p style=\"text-align:right;\">
";
//Admin-only links
if (($_SESSION['TALI_User_Level']) < 3) {
	echo "
							
					<a style=\"color:red;\" href=\"inventory.php?settings=true\">Settings</a>
					<a style=\"margin-left:10px;color:red;\" href=\"inventory.php?addItem=true\">Add Item</a>
	";
}

echo "
					<a style=\"margin-left:10px;\" href=\"inventory.php?packages=true\">Packages</a>
					<a style=\"margin-left:10px;\" href=\"inventory.php?bundles=true\">Bundles</a>
";

//Link to archive or non-archived table
if (isset($_GET['archive'])) {
	//Viewing archive, link non-archive
	echo "
					<a style=\"margin-left:10px;\" href=\"inventory.php\">Active Items</a>
	";
}
else
{
	//Viewing non-archive, link archive
	echo "
					<a style=\"margin-left:10px;\" href=\"inventory.php?archive=true\">Archived Items</a>
	";
}

echo "
					<a style=\"margin-left:10px;\" href=\"inventory.php?history=true\">Inventory History</a>
					<a style=\"margin-left:10px;\" href=\"inventory.php?userid=".$_SESSION['TALI_Username_ID']."\">User Profile (Check In)</a>
				</p>		
			</div>
";

//User clicked a button
if (($_SERVER['REQUEST_METHOD'] == 'POST') ||  (isset($_GET['addItem']))) {
	$itemId = "";
	$itemOriginalId = ""; 
	$itemName = "";
	$itemDescription = "";
	$itemManufacturer = "";
	$itemModel = "";
	$itemVendor = "";
	$itemUse = "";
	$itemCondition = "";
	$itemCategory = "";
	$itemUnit = "";
	$itemGeneralLocation = "";
	$itemSpecificLocation_Dropdown = "";
	$itemSpecificLocation_Other = "";
	$itemDateinService = "";
	$itemDateinService_str = "";
	$itemDateLifespan = "";
	$itemDateLifespan_str = "";
	$itemDateRetired = "";
	$itemDateRetired_str = "";
	$itemFundingSource = "";
	$itemFundingRestriction = "";
	$itemValue = "";
	$itemAnnualCost = "";
	$itemMaintenance = "";
	$itemNotes = "";
	$itemStatus = "";
	
	//Clicked something in settings? 
	if ((isset($_POST['packageAddBtn'])) || (isset($_POST['packageUpdateBtn'])) || (isset($_POST['packageDeleteBtn'])) || (isset($_POST['locationgeneralAddBtn'])) || (isset($_POST['locationgeneralUpdateBtn'])) || (isset($_POST['locationgeneralDeleteBtn'])) || (isset($_POST['locationspecificAddBtn'])) || (isset($_POST['locationspecificUpdateBtn'])) || (isset($_POST['locationspecificDeleteBtn']))) {
		//Go to settings
		require "inventory/inventory-settings.php"; 
		return;
	}
	
	//Clicked Add Item link
	//bug - kind of weird that I have this in POST like the button was, would rather it go with the other hyperlinks but it needs the variables defined, and not sure what'll happen with error'd pages with inputs if the variables are defined outside of this POST. They'd also define on every page, when really only needed for the add item form. 
	if (isset($_GET['addItem'])) {
		//Display Add New Item form
		//Using TALI Versions as a surrogate for inventory admin permissions
		TALI_sessionCheck("TALI_Versions", $db_handle);
		$itemButtonName = "inventoryAddSubmit"; 
		$itemButtonValue = "Add New Item"; 
		require "inventory/inventory-item.php"; 
		return;
	}
	
	//Submit the form to add new or edit item? 
	if ((isset($_POST['inventoryAddSubmit'])) || (isset($_POST['inventoryEditSubmit']))) {
		//Collect POST data
		$itemId = $_POST['itemId'];
		$itemOriginalId = $_POST['itemOriginalId'];
		$itemName = $_POST['itemName'];
		$itemDescription = $_POST['itemDescription'];
		$itemButtonName = $_POST['itemButtonName'];
		$itemButtonValue = $_POST['itemButtonValue'];

		$itemManufacturer = $_POST['itemManufacturer'];
		$itemModel = $_POST['itemModel'];
		$itemVendor = $_POST['itemVendor'];
		$itemUse = $_POST['itemUse'];
		$itemCondition = $_POST['itemCondition'];
		$itemCategory = $_POST['itemCategory'];
		$itemUnit = $_POST['itemUnit'];
		$itemGeneralLocation = $_POST['itemGeneralLocation'];
		
		$itemSpecificLocation_Dropdown = $_POST['itemSpecificLocation_Dropdown'];
		$itemSpecificLocation_Other = $_POST['itemSpecificLocation_Other'];
		//Now pick one specific location format to use
		if ($itemSpecificLocation_Dropdown == 1) {
			//User selected to use "other" field
			$itemSpecificLocation = $itemSpecificLocation_Other; 
		}
		else
		{
			//User did not select "other" field, so use non-selection or standardized selection
			$itemSpecificLocation = $itemSpecificLocation_Dropdown; 
		}
		
		
		$itemDateinService = $_POST['itemDateinService'];
		if ($itemDateinService == "") {
			$itemDateinService_sql = "NULL";
		}
		else
		{
			$itemDateinService_sql = date("Y-m-d", strtotime($itemDateinService));
			$itemDateinService_sql = htmlspecialchars($itemDateinService_sql);
			$itemDateinService_sql = TALI_quote_smart($itemDateinService_sql, $db_handle);
		}
		
		$itemDateLifespan = $_POST['itemDateLifespan'];
		if ($itemDateLifespan == "") {
			$itemDateLifespan_sql = "NULL";
		}
		else
		{
			$itemDateLifespan_sql = date("Y-m-d", strtotime($itemDateLifespan));
			$itemDateLifespan_sql = htmlspecialchars($itemDateLifespan_sql);
			$itemDateLifespan_sql = TALI_quote_smart($itemDateLifespan_sql, $db_handle);
		}
		
		$itemDateRetired = $_POST['itemDateRetired'];
		if ($itemDateRetired == "") {
			$itemDateRetired_sql = "NULL";
		}
		else
		{
			$itemDateRetired_sql = date("Y-m-d", strtotime($itemDateRetired));
			$itemDateRetired_sql = htmlspecialchars($itemDateRetired_sql);
			$itemDateRetired_sql = TALI_quote_smart($itemDateRetired_sql, $db_handle);
		}
		
		$itemFundingSource = $_POST['itemFundingSource'];
		$itemFundingRestriction = $_POST['itemFundingRestriction'];
		
		$itemValue = $_POST['itemValue'];
		if ($itemValue == "") {
			$itemValue_sql = "NULL";
		}
		
		$itemAnnualCost = $_POST['itemAnnualCost'];
		if ($itemAnnualCost == "") {
			$itemAnnualCost_sql = "NULL";
		}
		
		$itemMaintenance = $_POST['itemMaintenance'];
		$itemNotes = $_POST['itemNotes'];
		
		//Process the itemId for silliness (left empty (rounds to 0), decimal, negative, entered 0)
		if ($itemId === 0) {
			//id was entered as 0
			$errorMessage = "ERROR: The Item ID must be greater than 0!";
			require "inventory/inventory-item.php"; 
			//bug - many of these returns lose their ?id=$id so they lose the history report at the bottom
			return;
		}
		$itemId = round($itemId);
		if ($itemId < 0) {
			//id was entered and is negative
			$errorMessage = "ERROR: The Item ID must be greater than 0!";
			require "inventory/inventory-item.php"; 
			return;
		}
		//Make sure name was filled out
		if ($itemName == "") {
			//Name wasn't entered 
			$errorMessage = "ERROR: You must enter a name for the item!";
			//Fix to keep item blank if it was left blank
			if ($itemId == 0) {
				$itemId = "";
			}
			require "inventory/inventory-item.php"; 
			return; 
		}
		//Manage id for adding new item
		if (isset($_POST['inventoryAddSubmit'])) {
			if ($itemId != 0) {
				//An id was filled in, need to make sure it isn't a duplicate and error if so
				$SQL = "SELECT id FROM inventory WHERE id=$itemId";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$num_rows = mysqli_num_rows($result);
				//If anything is returned, id exists, so need to error
				if ($num_rows > 0) {
					//id already exists, cause error
					$errorMessage = "ERROR: The entered Item ID is already in use!";
					require "inventory/inventory-item.php"; 
					return;
				}
				//Otherwise, it's good to use
				$itemOriginalId = $itemId;
			}
			else
			{
				//id not filled in, so do nothing and let SQL auto increment
			}
		}
		//Manage id for editing item
		if (isset($_POST['inventoryEditSubmit'])) {
			if ($itemId != 0) {
				//An id was filled in, need to make sure it isn't checked out, then make sure it isn't a duplicate and error if so (if it was actually changed from the original)
				if ($itemId != $itemOriginalId) {
					$SQL = "SELECT checkedoutby FROM inventory WHERE id = $itemOriginalId";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					$checkedoutby=$db_field['checkedoutby'];
					
					if ($checkedoutby != 0) {
						//item is checked out
						$errorMessage = "ERROR: The Item ID cannot be changed while the item is checked out!";
						require "inventory/inventory-item.php"; 
						return;
					}
					
					$SQL = "SELECT id FROM inventory WHERE id=$itemId";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					$num_rows = mysqli_num_rows($result);
					//If anything is returned, id exists, so need to error
					if ($num_rows > 0) {
						//id already exists, cause error
						$errorMessage = "ERROR: The entered Item ID is already in use!";
						require "inventory/inventory-item.php"; 
						return;
					}
					
					//At this point the changed id is good and nothing else will stop the change, so go through contents of packages and check for the id in order to change it
					$SQL = "SELECT id, contents FROM inventory_packages";
					$result = mysqli_query($db_handle, $SQL);
					while ($db_field = mysqli_fetch_assoc($result)) {
						$packageContents_IDArray = explode(",", $db_field['contents']);
						$findKey = array_search($itemOriginalId, $packageContents_IDArray);
						if (is_numeric($findKey)) {
							//id is in this array, so delete the old and add the new (goes to end)
							unset($packageContents_IDArray[$findKey]);
							$packageContents_IDArray[] = $itemId;
							//Set back to string
							$packageContents_IDArray_str = implode(",", $packageContents_IDArray);
							$packageContents_IDArray_str_sql = htmlspecialchars($packageContents_IDArray_str);
							$packageContents_IDArray_str_sql = TALI_quote_smart($packageContents_IDArray_str_sql, $db_handle);
							//Update the package
							$keySQL = "UPDATE inventory_packages SET contents=$packageContents_IDArray_str_sql WHERE id=".$db_field['id'].""; 
							$keyresult = mysqli_query($db_handle, $keySQL);
						}
					}
				}
				else
				{
					//No change to id
				}
			}
			else
			{
				//id not filled in, so throw error because you can't delete the id
				$itemId = $itemOriginalId;
				$errorMessage = "ERROR: You cannot delete or reset an Item ID!";
				require "inventory/inventory-item.php"; 
				return;
			}
		}
			
		//Make the entries safe
			//Dates were already cleaned
		$itemName_sql = htmlspecialchars($itemName);
		$itemName_sql = TALI_quote_smart($itemName_sql, $db_handle);
		$itemDescription_sql = htmlspecialchars($itemDescription);
		$itemDescription_sql = TALI_quote_smart($itemDescription_sql, $db_handle);
		$itemManufacturer_sql = htmlspecialchars($itemManufacturer);
		$itemManufacturer_sql = TALI_quote_smart($itemManufacturer_sql, $db_handle);
		$itemModel_sql = htmlspecialchars($itemModel);
		$itemModel_sql = TALI_quote_smart($itemModel_sql, $db_handle);
		$itemVendor_sql = htmlspecialchars($itemVendor);
		$itemVendor_sql = TALI_quote_smart($itemVendor_sql, $db_handle);
		$itemSpecificLocation_sql = htmlspecialchars($itemSpecificLocation);
		$itemSpecificLocation_sql = TALI_quote_smart($itemSpecificLocation_sql, $db_handle);
		$itemFundingRestriction_sql = htmlspecialchars($itemFundingRestriction);
		$itemFundingRestriction_sql = TALI_quote_smart($itemFundingRestriction_sql, $db_handle);

		if ($itemValue != "") {
			$itemValue_sql = htmlspecialchars($itemValue);
			$itemValue_sql = TALI_quote_smart($itemValue_sql, $db_handle);
		}
		
		if ($itemAnnualCost != "") {
			$itemAnnualCost_sql = htmlspecialchars($itemAnnualCost);
			$itemAnnualCost_sql = TALI_quote_smart($itemAnnualCost_sql, $db_handle);
		}

		$itemMaintenance_sql = htmlspecialchars($itemMaintenance);
		$itemMaintenance_sql = TALI_quote_smart($itemMaintenance_sql, $db_handle);
		$itemNotes_sql = htmlspecialchars($itemNotes);
		$itemNotes_sql = TALI_quote_smart($itemNotes_sql, $db_handle);
		
		if (isset($_POST['inventoryAddSubmit'])) {
			if ($itemId == 0) {
				//id wasn't entered, so don't submit the 0
				$SQL = "INSERT INTO inventory (name, description, manufacturer, model, vendor, item_use, item_condition, item_category, assigned_unit, location_general, location_specific, date_in_service, lifespan_date, date_retired, funding_source, funding_restriction, value, annual_cost, maintenance, notes, status, checkedoutby, checkedouttype, checkedouttype_id, checkedoutpurpose) VALUES ($itemName_sql, $itemDescription_sql, $itemManufacturer_sql, $itemModel_sql, $itemVendor_sql, '$itemUse', '$itemCondition', '$itemCategory', '$itemUnit', '$itemGeneralLocation', $itemSpecificLocation_sql, $itemDateinService_sql, $itemDateLifespan_sql, $itemDateRetired_sql, '$itemFundingSource', $itemFundingRestriction_sql, $itemValue_sql, $itemAnnualCost_sql, $itemMaintenance_sql, $itemNotes_sql, 0, 0, 0, 0, '')";
				$result = mysqli_query($db_handle, $SQL);
				$itemOriginalId = mysqli_insert_id($db_handle);
			}
			else
			{
				//id was entered, so do submit it
				$SQL = "INSERT INTO inventory (id, name, description, manufacturer, model, vendor, item_use, item_condition, item_category, assigned_unit, location_general, location_specific, date_in_service, lifespan_date, date_retired, funding_source, funding_restriction, value, annual_cost, maintenance, notes, status, checkedoutby, checkedouttype, checkedouttype_id, checkedoutpurpose) VALUES ($itemId, $itemName_sql, $itemDescription_sql, $itemManufacturer_sql, $itemModel_sql, $itemVendor_sql, '$itemUse', '$itemCondition', '$itemCategory', '$itemUnit', '$itemGeneralLocation', $itemSpecificLocation_sql, $itemDateinService_sql, $itemDateLifespan_sql, $itemDateRetired_sql, '$itemFundingSource', $itemFundingRestriction_sql, $itemValue_sql, $itemAnnualCost_sql, $itemMaintenance_sql, $itemNotes_sql, 0, 0, 0, 0, '')";
				$result = mysqli_query($db_handle, $SQL);
			}
			
			//Create history entry
			$newHistory = Inventory_Create_History_Report('created', $db_handle, 'inventory', 'id', $itemOriginalId, 'Item ID#', 'name');
			
			$successMessage = "New item added!"; 
		}
		
		if (isset($_POST['inventoryEditSubmit'])) {
			$SQL = "UPDATE inventory SET id=$itemId, name=$itemName_sql, description=$itemDescription_sql, manufacturer=$itemManufacturer_sql, model=$itemModel_sql, vendor=$itemVendor_sql, item_use='$itemUse', item_condition='$itemCondition', item_category='$itemCategory', assigned_unit='$itemUnit', location_general='$itemGeneralLocation', location_specific=$itemSpecificLocation_sql, date_in_service=$itemDateinService_sql, lifespan_date=$itemDateLifespan_sql, date_retired=$itemDateRetired_sql, funding_source='$itemFundingSource', funding_restriction=$itemFundingRestriction_sql, value=$itemValue_sql, annual_cost=$itemAnnualCost_sql, maintenance=$itemMaintenance_sql, notes=$itemNotes_sql WHERE id=$itemOriginalId"; 
			$result = mysqli_query($db_handle, $SQL);
				
			//Create history entry
			$newHistory = Inventory_Create_History_Report('updated', $db_handle, 'inventory', 'id', $itemOriginalId, 'Item ID#', 'name');
			
			$successMessage = "Item updated!";
		}
	}
	
	//Archive/UnArchive item
	if ((isset($_POST['inventoryArchive'])) || (isset($_POST['inventoryUnArchive']))) {
		//Collect POST data
		$itemId = $_POST['itemId'];
		$itemOriginalId = $_POST['itemOriginalId'];
		$itemName = $_POST['itemName'];
		$itemDescription = $_POST['itemDescription'];
		
		//Archive item
		if (isset($_POST['inventoryArchive'])) {
			//Make sure item isn't checked out first
			$SQL = "SELECT checkedoutby FROM inventory WHERE id = $itemOriginalId";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$checkedoutby=$db_field['checkedoutby'];
			
			if ($checkedoutby != 0) {
				//item is checked out
				$errorMessage = "ERROR: The item cannot be archived while it is checked out!";
				$itemButtonName = "inventoryEditSubmit"; 
				$itemButtonValue = "Edit Item"; 
				$itemButtonArchiveName = "inventoryArchive"; 
				$itemButtonArchiveValue = "Archive Item"; 
				require "inventory/inventory-item.php"; 
				return;
			}
			
			//At this point nothing will stop the archive, so go through contents of packages and check for the id in order to remove it
			$SQL = "SELECT id, contents FROM inventory_packages";
			$result = mysqli_query($db_handle, $SQL);
			while ($db_field = mysqli_fetch_assoc($result)) {
				$packageContents_IDArray = explode(",", $db_field['contents']);
				$findKey = array_search($itemOriginalId, $packageContents_IDArray);
				if (is_numeric($findKey)) {
					//id is in this array, so delete it
					unset($packageContents_IDArray[$findKey]);
					//Set back to string
					$packageContents_IDArray_str = implode(",", $packageContents_IDArray);
					$packageContents_IDArray_str_sql = htmlspecialchars($packageContents_IDArray_str);
					$packageContents_IDArray_str_sql = TALI_quote_smart($packageContents_IDArray_str_sql, $db_handle);
					//Update the package
					$keySQL = "UPDATE inventory_packages SET contents=$packageContents_IDArray_str_sql WHERE id=".$db_field['id'].""; 
					$keyresult = mysqli_query($db_handle, $keySQL);
				}
			}
			
			$SQL = "UPDATE inventory SET status=2 WHERE id=$itemOriginalId"; 
			$result = mysqli_query($db_handle, $SQL);
			
			//Create history entry
			$newHistory = Inventory_Create_History_Report('archived', $db_handle, 'inventory', 'id', $itemOriginalId, 'Item ID#', 'name');
			
			$successMessage = "Item archived!";
		}
		
		//UnArchive item
		if (isset($_POST['inventoryUnArchive'])) {
			$SQL = "UPDATE inventory SET status=0 WHERE id=$itemOriginalId"; 
			$result = mysqli_query($db_handle, $SQL);
			
			//Create history entry
			$newHistory = Inventory_Create_History_Report('un-archived', $db_handle, 'inventory', 'id', $itemOriginalId, 'Item ID#', 'name');
			
			$successMessage = "Item un-archived!";
		}
	}
	
	//Check out item
	if (isset($_POST['checkoutPurpose'])) {
		$checkoutPurpose = $_POST['checkoutPurpose'];
		$checkoutItemId = $_POST['checkoutItemId'];
		$username_id = $_SESSION['TALI_Username_ID'];
		if ($checkoutPurpose == "false") {
			//User cancelled the prompt
			$errorMessage = "The check out was cancelled or purpose was left blank.";
		}
		else
		{
			//Make the entries safe
			$checkoutPurpose_sql = htmlspecialchars($checkoutPurpose);
			$checkoutPurpose_sql = TALI_quote_smart($checkoutPurpose_sql, $db_handle);
			$SQL = "UPDATE inventory SET status=1, checkedoutby=$username_id, checkedoutpurpose=$checkoutPurpose_sql WHERE id=$checkoutItemId"; 
			$result = mysqli_query($db_handle, $SQL);
			
			//Create history entry
			$newHistory = Inventory_Create_History_Report('checked out for the purpose of '.$checkoutPurpose_sql.'', $db_handle, 'inventory', 'id', $checkoutItemId, 'Item ID#', 'name');
			
			$successMessage = "Item checked out!";
		}
	}
	
	//Check in item
	if (isset($_POST['inventoryCheckIn'])) {
		$checkinUpdate = $_POST['checkinUpdate'];
		$checkinItemId = $_POST['checkinItemId'];
		$username_id = $_SESSION['TALI_Username_ID'];

		//Check if user aborted
		if (($checkinUpdate == 0) && (is_numeric($checkinUpdate))) {
			header ("Location: inventory.php?userid=$username_id");
			exit();
			//Not able to show message
		}
		
		//Check in the item
		$SQL = "UPDATE inventory SET status=0, checkedoutby=0, checkedouttype=0, checkedouttype_id=0, checkedoutpurpose='' WHERE id=$checkinItemId"; 
		$result = mysqli_query($db_handle, $SQL);
		
		//Create history entry
		$newHistory = Inventory_Create_History_Report('checked in', $db_handle, 'inventory', 'id', $checkinItemId, 'Item ID#', 'name');
		
		//bug - not sure how to give a success message here due to header below, so it's pretty much included in the check in prompt
		//$successMessage = "Item checked in!";
		
		//Send Logistics email if field was populated
		if (is_string($checkinUpdate)) {
			if ((strlen($checkinUpdate)) > 1) {
				//Gather some information for email
				$SQL = "SELECT * FROM inventory WHERE id = $checkinItemId";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$itemName = $db_field['name'];
				
				$SQL = "SELECT email FROM tali_admin_accounts WHERE id = ".$_SESSION['TALI_Username_ID']."";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$userEmail = $db_field['email'];
				
				//Send email to Logistics
				$subject = "TCSAR TALI Inventory Notice - ".$itemName."";
				$msgBody = "Attention TCSAR Logistics, 
				<br/>User ".$_SESSION['TALI_Username']." has checked in the below item in the past few minutes with the associated information to report: 
				<br/><br/><a href=\"http://www.tcsar.org/tali/modules/inventory.php?id=$checkinItemId\">$itemName<a/>
				<br/>$checkinUpdate
				<br/><br/>Please correspond without including this e-mail address, web@tcsar.org. Thank you. 
				";
				//Send to Logistics and user
				TALI_EMail ([["logistics@tcsar.org", "TCSAR Logistics"], [$userEmail, $_SESSION['TALI_Username']]], $subject, $msgBody);
			}
		}
		header ("Location: inventory.php?userid=$username_id");
		exit();
	}
	
	//Check out bundle
	if (isset($_POST['checkoutPurpose_Bundle'])) {
		$checkoutPurpose = $_POST['checkoutPurpose_Bundle'];
		//Pull item id being used merely as a reference for the location_specific bundle
		$checkoutItemId = $_POST['checkoutItemId_Bundle'];
		$username_id = $_SESSION['TALI_Username_ID'];
		if ($checkoutPurpose == "false") {
			//User cancelled the prompt
			$errorMessage = "The check out was cancelled or purpose was left blank.";
		}
		else
		{
			//Make the purpose entry safe
			$checkoutPurpose_sql = htmlspecialchars($checkoutPurpose);
			$checkoutPurpose_sql = TALI_quote_smart($checkoutPurpose_sql, $db_handle);
					
			//Get the location_specific
			$SQL = "SELECT location_specific FROM inventory WHERE id = $checkoutItemId";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$specificLocation = $db_field['location_specific'];
			
			//Create a unique bundle id
			$checkedouttype = 1;
			$checkedouttype_id = time() * $username_id;
			
			//Round up and check out available items in the bundle
			$SQL = "SELECT id, checkedoutby FROM inventory WHERE NOT status=2 AND location_specific = $specificLocation";
			$result = mysqli_query($db_handle, $SQL);
			$count = 0;
			while ($db_field = mysqli_fetch_assoc($result)) {
				//Only check out available item
				if (($db_field['checkedoutby']) == 0) {
					$inSQL = "UPDATE inventory SET status=1, checkedoutby=$username_id, checkedouttype=1, checkedouttype_id=$checkedouttype_id, checkedoutpurpose=$checkoutPurpose_sql WHERE id=".$db_field['id'].""; 
					$inresult = mysqli_query($db_handle, $inSQL);
					
					//Create history entry
					$newHistory = Inventory_Create_History_Report('checked out as part of a bundle for the purpose of '.$checkoutPurpose_sql.'', $db_handle, 'inventory', 'id', $db_field['id'], 'Item ID#', 'name');
					
					$count = $count + 1;
				}
			}
			
			//If say success if something was actually checked out
			if ($count > 0) {
				$successMessage = "Bundle checked out!";
			}
			else
			{
				$errorMessage = "No items were available for checkout in this bundle!"; 
			}
		}
	}
	
	//Check out package
	if (isset($_POST['checkoutPurpose_Package'])) {
		$checkoutPurpose = $_POST['checkoutPurpose_Package'];
		$checkoutPackageId = $_POST['checkoutItemId_Package'];
		$username_id = $_SESSION['TALI_Username_ID'];
		if ($checkoutPurpose == "false") {
			//User cancelled the prompt
			$errorMessage = "The check out was cancelled or purpose was left blank.";
		}
		else
		{
			//Make the purpose entry safe
			$checkoutPurpose_sql = htmlspecialchars($checkoutPurpose);
			$checkoutPurpose_sql = TALI_quote_smart($checkoutPurpose_sql, $db_handle);
			
			//Get data on this package
			$SQL = "SELECT name, contents FROM inventory_packages WHERE id=$checkoutPackageId";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$packageName = $db_field['name'];
			$packageContents = $db_field['contents'];

			//Put contents into array
			$packageContents_IDArray = explode(",", $packageContents);
				//Must use ","
			
			//Go through array of contents, only check out what is available
			$count = 0;
			forEach ($packageContents_IDArray as $id) {
				$SQL = "SELECT checkedoutby FROM inventory WHERE id = $id";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				//Only check out available item
				if (($db_field['checkedoutby']) == 0) {
					$inSQL = "UPDATE inventory SET status=1, checkedoutby=$username_id, checkedouttype=2, checkedouttype_id=$checkoutPackageId, checkedoutpurpose=$checkoutPurpose_sql WHERE id=$id"; 
					$inresult = mysqli_query($db_handle, $inSQL);
					
					//Create history entry
					$newHistory = Inventory_Create_History_Report('checked out as part of a package for the purpose of '.$checkoutPurpose_sql.'', $db_handle, 'inventory', 'id', $id, 'Item ID#', 'name');
					
					$count = $count + 1;
				}
			}
			
			//If say success if something was actually checked out
			if ($count > 0) {
				$successMessage = "Package checked out!";
			}
			else
			{
				$errorMessage = "No items were available for checkout in this package!"; 
			}
		}
	}
	
	//Check-in bundle
	if (isset($_POST['inventoryCheckIn_Bundle'])) {
		$checkinUpdate = $_POST['checkinUpdate_Group'];
		$checkinItemId = $_POST['checkinItemId_Group'];
		$username_id = $_SESSION['TALI_Username_ID'];
		
		//Check if user aborted
		if (($checkinUpdate == 0) && (is_numeric($checkinUpdate))) {
			header ("Location: inventory.php?userid=$username_id");
			exit();
			//Not able to show message
		}
		
		//Get the bundle id again using the reference item id
		$SQL = "SELECT checkedouttype_id FROM inventory WHERE id=$checkinItemId";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result); 
		$bundle_id = $db_field['checkedouttype_id'];
		
		//Get array of id's about to be checked in
		$SQL = "SELECT id FROM inventory WHERE checkedouttype_id=$bundle_id";
		$result = mysqli_query($db_handle, $SQL);
		$idArray = [];
		while ($db_field = mysqli_fetch_assoc($result)) {
			$idArray[] = $db_field['id'];
		}
				
		//Check in all items in the group
		$SQL = "UPDATE inventory SET status=0, checkedoutby=0, checkedouttype=0, checkedouttype_id=0, checkedoutpurpose='' WHERE checkedouttype_id=$bundle_id"; 
		$result = mysqli_query($db_handle, $SQL);
		
		forEach ($idArray as $id) {
			//Create history entry
			$newHistory = Inventory_Create_History_Report('checked in as part of a bundle', $db_handle, 'inventory', 'id', $id, 'Item ID#', 'name');
		}
		
		//bug - not sure how to give a success message here due to header below, so it's pretty much included in the check in prompt
		//$successMessage = "Item checked in!";
		
		//Send Logistics email if field was populated
		if (is_string($checkinUpdate)) {
			if ((strlen($checkinUpdate)) > 1) {
				//Gather some information for email
				$SQL = "SELECT location_specific FROM inventory WHERE id = $checkinItemId";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$locationSpecific = $db_field['location_specific'];
				$bundleName = $locationSpecific;
				if (is_numeric($bundleName)) {
					$SQL = "SELECT name FROM inventory_location_specific WHERE id = $locationSpecific";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					$bundleName = $db_field['name'];
				}
				
				$SQL = "SELECT email FROM tali_admin_accounts WHERE id = ".$_SESSION['TALI_Username_ID']."";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$userEmail = $db_field['email'];
				
				//Send email to Logistics
				$subject = "TCSAR TALI Inventory Notice - ".$bundleName."";
				$msgBody = "Attention TCSAR Logistics, 
				<br/>User ".$_SESSION['TALI_Username']." has checked in the below bundle in the past few minutes with the associated information to report: 
				<br/><br/><a href=\"http://www.tcsar.org/tali/modules/inventory.php?specificLocation=$locationSpecific\">$bundleName<a/>
				<br/>$checkinUpdate
				<br/><br/>Please correspond without including this e-mail address, web@tcsar.org. Thank you. 
				";
				//Send to Logistics and user
				TALI_EMail ([["logistics@tcsar.org", "TCSAR Logistics"], [$userEmail, $_SESSION['TALI_Username']]], $subject, $msgBody);
			}
		}
		header ("Location: inventory.php?userid=$username_id");
		exit();				
	}
	
	//Check-in package
	if (isset($_POST['inventoryCheckIn_Package'])) {
		$checkinUpdate = $_POST['checkinUpdate_Group'];
		$checkinPackageId = $_POST['checkinItemId_Group'];
		$username_id = $_SESSION['TALI_Username_ID'];
		
		//Check if user aborted
		if (($checkinUpdate == 0) && (is_numeric($checkinUpdate))) {
			header ("Location: inventory.php?userid=$username_id");
			exit();
			//Not able to show message
		}
		
		//Get array of id's about to be checked in
		$SQL = "SELECT id FROM inventory WHERE checkedouttype_id=$checkinPackageId";
		$result = mysqli_query($db_handle, $SQL);
		$idArray = [];
		while ($db_field = mysqli_fetch_assoc($result)) {
			$idArray[] = $db_field['id'];
		}
				
		//Check in all items in the group
		$SQL = "UPDATE inventory SET status=0, checkedoutby=0, checkedouttype=0, checkedouttype_id=0, checkedoutpurpose='' WHERE checkedouttype_id=$checkinPackageId"; 
		$result = mysqli_query($db_handle, $SQL);
		
		forEach ($idArray as $id) {
			//Create history entry
			$newHistory = Inventory_Create_History_Report('checked in as part of a package', $db_handle, 'inventory', 'id', $id, 'Item ID#', 'name');
		}
		
		//bug - not sure how to give a success message here due to header below, so it's pretty much included in the check in prompt
		//$successMessage = "Item checked in!";
		
		//Send Logistics email if field was populated
		if (is_string($checkinUpdate)) {
			if ((strlen($checkinUpdate)) > 1) {
				//Gather some information for email
				$SQL = "SELECT name FROM inventory_packages WHERE id = $checkinPackageId";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$packageName = $db_field['name'];
				
				$SQL = "SELECT email FROM tali_admin_accounts WHERE id = ".$_SESSION['TALI_Username_ID']."";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$userEmail = $db_field['email'];
				
				//Send email to Logistics
				$subject = "TCSAR TALI Inventory Notice - ".$packageName."";
				$msgBody = "Attention TCSAR Logistics, 
				<br/>User ".$_SESSION['TALI_Username']." has checked in the below package in the past few minutes with the associated information to report: 
				<br/><br/><a href=\"http://www.tcsar.org/tali/modules/inventory.php?packages=true\">$packageName<a/>
				<br/>$checkinUpdate
				<br/><br/>Please correspond without including this e-mail address, web@tcsar.org. Thank you. 
				";
				//Send to Logistics and user
				TALI_EMail ([["logistics@tcsar.org", "TCSAR Logistics"], [$userEmail, $_SESSION['TALI_Username']]], $subject, $msgBody);
			}
		}
		header ("Location: inventory.php?userid=$username_id");
		exit();
	}
}	

//Display notifications
if (($errorMessage != "") || ($successMessage != "")) {
	echo "
		<div class=\"tali-page-frame\">
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
};

//Clicked Edit icon
if (isset($_GET['id'])) {
	$itemId = $_GET['id']; 
	$itemOriginalId = $itemId;
	//Select appropriate item
	$SQL = "SELECT * FROM inventory WHERE id=$itemId";
	$result = mysqli_query($db_handle, $SQL);
	
	//Make sure this id exists
	if ((mysqli_num_rows($result)) < 1) {
		//id doesn't exist, go home
		header ("Location: inventory.php");
		exit();
	}
	//id exists, continue
	
	$db_field = mysqli_fetch_assoc($result);
	
	//Pull the item's data
	$itemName = $db_field['name'];
	$itemDescription = $db_field['description'];
	$itemManufacturer = $db_field['manufacturer'];
	$itemModel = $db_field['model'];
	$itemVendor = $db_field['vendor'];
	$itemUse = $db_field['item_use'];
	$itemCondition = $db_field['item_condition'];
	$itemCategory = $db_field['item_category'];
	$itemUnit = $db_field['assigned_unit'];
	$itemGeneralLocation = $db_field['location_general'];
	
	$itemSpecificLocation = $db_field['location_specific'];
	//Expand specific location to dropdown and other field
	if ($itemSpecificLocation != "") {
		if (is_numeric($itemSpecificLocation)) {
			//Dropdown is being used
			$itemSpecificLocation_Dropdown = $itemSpecificLocation;
			$itemSpecificLocation_Other = "";
		}
		else
		{
			//Other field is being used
			$itemSpecificLocation_Dropdown = 1;
			$itemSpecificLocation_Other = $itemSpecificLocation;
		}
	}
	else
	{
		$itemSpecificLocation_Dropdown = "";
		$itemSpecificLocation_Other = "";
	}
	
	$itemDateinService = $db_field['date_in_service'];
	if ($itemDateinService != "") {
		//$itemDateinService = DateTime::createFromFormat('Y-m-d', $itemDateinService);
		$itemDateinService_str = date("m/d/Y", strtotime($itemDateinService));
	}
	else
	{
		$itemDateinService = "";
		$itemDateinService_str = "";
	}
	
	$itemDateLifespan = $db_field['lifespan_date'];
	if ($itemDateLifespan != "") {
		//$itemDateLifespan = DateTime::createFromFormat('Y-m-d', $itemDateLifespan);
		$itemDateLifespan_str = date("m/d/Y", strtotime($itemDateLifespan));
	}
	else
	{
		$itemDateLifespan = "";
		$itemDateLifespan_str = "";
	}

	$itemDateRetired = $db_field['date_retired'];
	if ($itemDateRetired != "") {
		//$itemDateRetired = DateTime::createFromFormat('Y-m-d', $itemDateRetired);
		$itemDateRetired_str = date("m/d/Y", strtotime($itemDateRetired));
	}
	else
	{
		$itemDateRetired = "";
		$itemDateRetired_str = "";
	}
	
	$itemFundingSource = $db_field['funding_source'];
	$itemFundingRestriction = $db_field['funding_restriction'];
	$itemValue = $db_field['value'];
	$itemAnnualCost = $db_field['annual_cost'];
	$itemMaintenance = $db_field['maintenance'];
	$itemNotes = $db_field['notes'];
	$itemStatus = $db_field['status'];
	
	//Set appropriate archive button
	if ($itemStatus != 2) {
		//Item is not archived, so option is to archive it
		$itemButtonArchiveName = "inventoryArchive"; 
		$itemButtonArchiveValue = "Archive Item"; 
	}
	else
	{
		//Item is archived, so option is to un-archive it
		$itemButtonArchiveName = "inventoryUnArchive"; 
		$itemButtonArchiveValue = "Un-Archive Item"; 
	}
	
	//Open item form for editing
	$itemButtonName = "inventoryEditSubmit"; 
	$itemButtonValue = "Edit Item"; 
	require "inventory/inventory-item.php"; 
	return;
}

//Clicked Master History Report link
if (isset($_GET['history'])) { 
	require "inventory/inventory-history.php"; 
	//fyi, that file ends with </script>
	return;
}

//Clicked a username
if (isset($_GET['userid'])) {
	$userid = $_GET['userid']; 
	//Make sure this id exists
	$SQL = "SELECT * FROM tali_admin_accounts WHERE id=$userid";
	$result = mysqli_query($db_handle, $SQL);
	if ((mysqli_num_rows($result)) < 1) {
		//user id doesn't exist, go home
		header ("Location: inventory.php");
		exit();
	}
	//user id exists, continue
	require "inventory/inventory-user.php"; 
	//fyi, that file ends with </script>
	return;
}

//Clicked settings link
if (isset($_GET['settings'])) {
	//Using TALI Versions as a surrogate for inventory admin permissions
	TALI_sessionCheck("TALI_Versions", $db_handle);
	require "inventory/inventory-settings.php"; 
	return;
}

//Clicked Bundles link
if (isset($_GET['bundles'])) { 
	require "inventory/inventory-bundles.php"; 
	//fyi, that file ends with </script>
	return;
}

//Clicked Packages link
if (isset($_GET['packages'])) { 
	require "inventory/inventory-packages.php"; 
	//fyi, that file ends with </script>
	return;
}

//Nothing else happening, so just display the main screen with browsable table
require "inventory/inventory-table.php"; 
//fyi, that file ends with </script>
?>