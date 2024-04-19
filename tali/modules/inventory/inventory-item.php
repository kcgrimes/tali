<?php
//Display notifications
echo "
			<div class=\"tali-page-frame\">
";
if ($errorMessage != "") {
	echo "
				<p><font color=\"red\">$errorMessage</font></p>
	";
};
echo "	
				<form method=\"POST\" action=\"inventory.php\">
					<input type=\"hidden\" name=\"itemOriginalId\" value=\"$itemOriginalId\">
					<input type=\"hidden\" name=\"itemButtonName\" value=\"$itemButtonName\">
					<input type=\"hidden\" name=\"itemButtonValue\" value=\"$itemButtonValue\">
					<table style=\"width:100%;\">
						<col width=\"20%\">
						<col width=\"80%\">
						<tr>
							<td style=\"text-align:right;\"><strong>Item ID:</strong></td>
							<td style=\"text-align:left;\"><input type=\"number\" style=\"width:90%;\" name=\"itemId\" value=\"$itemId\"></td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong style=\"color:red;\">Name:</strong></td>
							<td style=\"text-align:left;\"><input type=\"text\" style=\"width:90%;\" name=\"itemName\" value=\"$itemName\"></td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Description:</strong></td>
							<td style=\"text-align:left;\"><textarea style=\"width:90%;height:100px;\" name=\"itemDescription\" value=\"$itemDescription\">$itemDescription</textarea></td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Manufacturer:</strong></td>
							<td style=\"text-align:left;\"><input type=\"text\" style=\"width:90%;\" name=\"itemManufacturer\" value=\"$itemManufacturer\"></td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Model:</strong></td>
							<td style=\"text-align:left;\"><input type=\"text\" style=\"width:90%;\" name=\"itemModel\" value=\"$itemModel\"></td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Vendor:</strong></td>
							<td style=\"text-align:left;\"><input type=\"text\" style=\"width:90%;\" name=\"itemVendor\" value=\"$itemVendor\"></td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Use:</strong></td>
							<td style=\"text-align:left;\">
								<select class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"itemUse\" value=\"$itemUse\">
									<option value=\"\">Select a Use</option>
";
//Create array of use options
$useArray = [[1,"Active Use"],[2,"Training Only"],[3,"Out of Service"]];
foreach ($useArray as $arr) {
	if ($arr[0] == $itemUse) {
		$selected = 'selected="selected"';
	}
	else
	{
		$selected = '';
	}
	echo "
									<option value=\"".$arr[0]."\"".$selected.">".$arr[1]."</option>
	";
}
echo "
								</select>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Condition:</strong></td>
							<td style=\"text-align:left;\">
								<select class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"itemCondition\" value=\"$itemCondition\">
									<option value=\"\">Select a Condition</option>
";
//Create array of condition options
$conditionArray = [[1,"Excellent"],[2,"Good"],[3,"Poor"],[4,"Bad"]];
foreach ($conditionArray as $arr) {
	if ($arr[0] == $itemCondition) {
		$selected = 'selected="selected"';
	}
	else
	{
		$selected = '';
	}
	echo "
									<option value=\"".$arr[0]."\"".$selected.">".$arr[1]."</option>
	";
}
echo "
								</select>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Category:</strong></td>
							<td style=\"text-align:left;\">
								<select class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"itemCategory\" value=\"$itemCategory\">
									<option value=\"\">Select a Category</option>
";
//Create array of Category options
$categoryArray = [[1,"Administrative"],[2,"Technology"],[3,"Communications"],[4,"Field Equipment"],[5,"General"],[6,"Fundraising"]];
foreach ($categoryArray as $arr) {
	if ($arr[0] == $itemCategory) {
		$selected = 'selected="selected"';
	}
	else
	{
		$selected = '';
	}
	echo "
									<option value=\"".$arr[0]."\"".$selected.">".$arr[1]."</option>
	";
}
echo "
								</select>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Unit:</strong></td>
							<td style=\"text-align:left;\">
								<select class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"itemUnit\" value=\"$itemUnit\">
									<option value=\"\">Select a Unit</option>
";
//Create array of Unit options
$unitArray = [[1,"Ground"],[2,"Canine"],[3,"Special Operations"],[4,"Search Management"],[5,"Logistics"],[6,"General"]];
foreach ($unitArray as $arr) {
	if ($arr[0] == $itemUnit) {
		$selected = 'selected="selected"';
	}
	else
	{
		$selected = '';
	}
	echo "
									<option value=\"".$arr[0]."\"".$selected.">".$arr[1]."</option>
	";
}
echo "
								</select>
							</td>
						</tr>				
						<tr>
							<td style=\"text-align:right;\"><strong>General Location:</strong></td>
							<td style=\"text-align:left;\">
								<select class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"itemGeneralLocation\" value=\"$itemGeneralLocation\">
									<option value=\"\">Select a General Location</option>
";
//Create array of General Location options
//ex output. $generallocationArray = [[1,"Command Post"],[2,"Jon Boat"],[3,"Rescue Boat"],[4,"Storage Trailer"],[5,"Other"]];
$SQL = "SELECT * FROM inventory_location_general";
$result = mysqli_query($db_handle, $SQL);
$generallocationArray = [];
while ($db_field = mysqli_fetch_assoc($result)) {
	$generallocationArray[] = [$db_field['id'],$db_field['name']];
}
		
foreach ($generallocationArray as $arr) {
	if ($arr[0] == $itemGeneralLocation) {
		$selected = 'selected="selected"';
	}
	else
	{
		$selected = '';
	}
	echo "
									<option value=\"".$arr[0]."\"".$selected.">".$arr[1]."</option>
	";
}
echo "
								</select>
							</td>
						</tr>
						
						<tr>
							<td style=\"text-align:right;\"><strong>Specific Location:</strong></td>
							<td style=\"text-align:left;\">
								<select class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"itemSpecificLocation_Dropdown\" value=\"$itemSpecificLocation_Dropdown\">
									<option value=\"\">Select a Specific Location</option>
";
//Create array of Specific Location options
//ex output. $specificlocationArray = [[1,"Other"],[2,"Filing Cabinet"],[3,"Spec Ops Bag 1"],[4,"MCP 1"]];
$SQL = "SELECT * FROM inventory_location_specific";
$result = mysqli_query($db_handle, $SQL);
$specificlocationArray = [];
while ($db_field = mysqli_fetch_assoc($result)) {
	$specificlocationArray[] = [$db_field['id'],$db_field['name']];
}

foreach ($specificlocationArray as $arr) {
	if ($arr[0] == $itemSpecificLocation_Dropdown) {
		$selected = 'selected="selected"';
	}
	else
	{
		$selected = '';
	}
	echo "
									<option value=\"".$arr[0]."\"".$selected.">".$arr[1]."</option>
	";
}
echo "
								</select>
							</td>
						</tr>
						
						<tr>
							<td style=\"text-align:right;\"><strong>Specific Location (other):</strong></td>
							<td style=\"text-align:left;\"><input type=\"text\" style=\"width:90%;\" name=\"itemSpecificLocation_Other\" value=\"$itemSpecificLocation_Other\"></td>
						</tr>
";

//Create function for calculating years/months/days until date
function Inventory_Date_Diff($dateToCount,$canExpire) {
	$dateToday = new DateTime();
	//Output nothing if there is no date entered
	if ($dateToCount == "") {
		return "";
	}
	$dateToCount = new DateTime($dateToCount);
	$interval = $dateToday->diff($dateToCount);
	
	//Notice if expired
	if ($canExpire && ($dateToCount < $dateToday)) {
		$expired = "<font style=\"color:red;\">Expired for: </font>";
	}
	else
	{
		$expired = "";
	}
	
	//Add ending for dates that aren't meant to be expired
	if (!$canExpire && ($dateToCount < $dateToday)) {
		$ending = " ago";
	}
	else
	{
		$ending = "";
	}
	
	//Recursively count years/months/days to label accordingly
	$dateString = "";
	if (($interval->y) > 0) {
		//Include years, months, days
		$dateString = $interval->y." years, ".$interval->m." months, ".$interval->d." days";
	}
	else
	{
		if (($interval->m) > 0) {
			//Include months, days
			$dateString = $interval->m." months, ".$interval->d." days";
		}
		else
		{
			//Include days
			if (($interval->d) >= 0) {
				$dateString = $interval->d." days";
			}
		}
	}
	return $expired . $dateString . $ending;
}

echo "
						<tr>
							<td style=\"text-align:right;\"><strong>Date in Service (MM/DD/YYYY):</strong></td>
							<td style=\"text-align:left;\">
								<input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"itemDateinService\" maxlength=\"10\" value=\"$itemDateinService_str\">
								".Inventory_Date_Diff($itemDateinService,false)."
							</td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Date of Lifespan (MM/DD/YYYY):</strong></td>
							<td style=\"text-align:left;\">
								<input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"itemDateLifespan\" maxlength=\"10\" value=\"$itemDateLifespan_str\">
								".Inventory_Date_Diff($itemDateLifespan,true)."
							</td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Date Retired (MM/DD/YYYY):</strong></td>
							<td style=\"text-align:left;\">
								<input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"itemDateRetired\" maxlength=\"10\" value=\"$itemDateRetired_str\">
								".Inventory_Date_Diff($itemDateRetired,false)."
							</td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Funding Source:</strong></td>
							<td style=\"text-align:left;\">
								<select class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"itemFundingSource\" value=\"$itemFundingSource\">
									<option value=\"\">Select a Funding Source</option>
";
//Create array of Unit options
$fundingsourceArray = [[1,"Team Purchase"],[2,"Donated"],[3,"Grant"]];
foreach ($fundingsourceArray as $arr) {
	if ($arr[0] == $itemFundingSource) {
		$selected = 'selected="selected"';
	}
	else
	{
		$selected = '';
	}
	echo "
									<option value=\"".$arr[0]."\"".$selected.">".$arr[1]."</option>
	";
}
echo "
								</select>
							</td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Funding Restriction:</strong></td>
							<td style=\"text-align:left;\"><input type=\"text\" style=\"width:90%;\" name=\"itemFundingRestriction\" value=\"$itemFundingRestriction\"></td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Value ($):</strong></td>
							<td style=\"text-align:left;\"><input type=\"number\" style=\"width:90%;\" name=\"itemValue\" value=\"$itemValue\"></td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Annual Cost ($):</strong></td>
							<td style=\"text-align:left;\"><input type=\"number\" style=\"width:90%;\" name=\"itemAnnualCost\" value=\"$itemAnnualCost\"></td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Maintenance:</strong></td>
							<td style=\"text-align:left;\"><textarea style=\"width:90%;height:100px;\" name=\"itemMaintenance\" value=\"$itemMaintenance\">$itemMaintenance</textarea></td>
						</tr>
						<tr>
							<td style=\"text-align:right;\"><strong>Notes:</strong></td>
							<td style=\"text-align:left;\"><textarea style=\"width:90%;height:100px;\" name=\"itemNotes\" value=\"$itemNotes\">$itemNotes</textarea></td>
						</tr>
					</table>

					<p style=\"text-align:left;\">
						<input type=\"Submit\" name=\"$itemButtonName\" value=\"$itemButtonValue\">
";

//If editing as admin, include Archive/Un-Archive button
if ((isset($_GET['id'])) && (($_SESSION['TALI_User_Level']) < 3)) {
	echo "
						<span style=\"float:right;\">
							<input type=\"Submit\" name=\"$itemButtonArchiveName\" value=\"$itemButtonArchiveValue\">
						</span>
	";
}

echo "
					</p>
				</form>
			</div>
";

//If editing, include item's history report
if (isset($_GET['id'])) {
	//Create history table (basically the master history table but restricted to this item id)
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

	$SQL = "SELECT * FROM inventory_master_history WHERE item_id=".$_GET['id']." ORDER BY id DESC";
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