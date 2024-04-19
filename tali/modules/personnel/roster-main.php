<?php
//Stock variables
$module = "TALI_Personnel";
$add_rank_id = "";
$add_firstname = "";
$add_firstname_sql = "";
$add_lastname = "";
$add_lastname_sql = "";
$add_nickname = "";
$add_nickname_sql = "";
$add_role_id = "";
$add_status_id = "";
$add_designation_id = "";
$add_email = "";
$add_email_sql = "";
$add_othercontact = "";
$add_othercontact_sql = "";
$add_location = "";
$add_location_sql = "";
$add_dateofbirth = "";
$add_dateofbirth_sql = "";
$add_biography = "";
$add_biography_sql = "";
$add_dateenlisted = "";
$add_dateenlisted_sql = "";
$add_datepromoted = "";
$add_datepromoted_sql = "";
$add_datedischarged = "";
$add_datedischarged_sql = "";
$add_adminusername = "";
$add_adminusername_sql = "";
$errorMessage = "";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

//Return - Cancel button clicked
if (isset($_POST['btnCancel'])) {
	header ("Location: personnel.php?sub=roster");
	exit();
}

//bug - A better way to do this?
echo "
	<main>
		<div class=\"tali-container\">
";

//Return - Delete button clicked
//bug - What about the associated admin account, if any?
if ((isset($_POST['btnDelete'])) && (isset($_GET['id']))) {
	$delid = $_GET['id'];
	
	//History Report
	TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_personnel_roster', 'personnel_id', $delid, 'Personnel ID#', 'lastname');
				
	$delSQL = "DELETE FROM tali_personnel_roster WHERE personnel_id = $delid";
	$delresult = mysqli_query($db_handle, $delSQL);
	
	header ("Location: personnel.php?sub=roster");
	exit();
}

//Return - Discharge button clicked
if ((isset($_POST['btnDischarge'])) && (isset($_GET['id']))) {
	$personnel_id = $_GET['id'];
	
	//Select appropriate personnel file
	$SQL = "SELECT * FROM tali_personnel_roster WHERE personnel_id=$personnel_id";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);
	
	//Pull date_discharged, which may or may not be defined
	//bug - um... why are you pulling date_discharged from the entry? It's probably not defined
	//there yet...
	$date_discharged = $db_field['date_discharged'];
	if (is_null($date_discharged)) {
		//If date_discharged wasn't defined, just use today's date
		$date_discharged = date("Y-m-d");
	}
	
	//Make date safe for SQL
	$date_discharged_sql = date("Y-m-d", strtotime($date_discharged));
	$date_discharged_sql = htmlspecialchars($date_discharged_sql);
	$date_discharged_sql = TALI_quote_smart($date_discharged_sql, $db_handle);
	
	$designation_id = $db_field['designation_id'];
	if (is_null($designation_id)) {
		//bug - error here, not sure how to manage it, so BOOM!
			//This should never be null now, so... now what?
		header ("Location: personnel.php?sub=roster");
		exit();
	}
	
	//Obtain information about the designation from the database
	$SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$designation_id";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);
	$reportsTo=$db_field['reportsto_designation_id'];
	
	//Prepare to create long, full designation name
	//Will make an array of the chain of command, invert it, and make it a string
	$full_desig_name_array = array();
	//Carry reportsTo number through coming while loop while maintaining original
	$reportsTo_cycle = $reportsTo;
	//Add selected designation's name to array first before adding the rest
	$full_desig_name_array[] = $db_field['name'];

	//If the selected designation reports to another designation, print their info, and continue
	//until the newly selected designation does not report to anyone (top of the chain)
	while ($reportsTo_cycle != 0) {
		$desName_SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$reportsTo_cycle";
		$desName_result = mysqli_query($db_handle, $desName_SQL);
		$desigName_db_field = mysqli_fetch_assoc($desName_result);
		//Add designation to array
		$full_desig_name_array[] = $desigName_db_field['name'];
		//Change reportsTo id for next cycle
		$reportsTo_cycle = $desigName_db_field['reportsto_designation_id'];
	}
	
	//Reverse the array and turn it into a string to finally define the full designation name
	$discharged_designation = implode(",", array_reverse($full_desig_name_array));
	
	//Turn designation name array into clean string safe for SQL
	$discharged_designation_sql = $discharged_designation;
	$discharged_designation_sql = htmlspecialchars($discharged_designation_sql);
	$discharged_designation_sql = TALI_quote_smart($discharged_designation_sql, $db_handle);
	
	//Update personnel file values for discharge and reset active designations
	//bug - well, should the designation be reset?
	$SQL = "UPDATE tali_personnel_roster SET date_discharged=$date_discharged_sql, designation_id='', discharged=1, discharged_designation=$discharged_designation_sql WHERE personnel_id=$personnel_id"; 
	$result = mysqli_query($db_handle, $SQL);
	
	//History Report
	TALI_Create_History_Report('discharged', $module, $db_handle, 'tali_personnel_roster', 'personnel_id', $personnel_id, 'Personnel ID#', 'lastname');
	
	header ("Location: personnel.php?sub=roster");
	exit();
}

//Return - Un-Discharge button clicked
if ((isset($_POST['btnUnDischarge'])) && (isset($_GET['id']))) {
	$personnel_id = $_GET['id'];
	
	//Update personnel file with values to move out of discharged status
	$SQL = "UPDATE tali_personnel_roster SET date_discharged=NULL, discharged=0, discharged_designation='' WHERE personnel_id=$personnel_id"; 
	$result = mysqli_query($db_handle, $SQL);
	
	//History Report
	TALI_Create_History_Report('un-discharged', $module, $db_handle, 'tali_personnel_roster', 'personnel_id', $personnel_id, 'Personnel ID#', 'lastname');
	
	header ("Location: personnel.php?sub=roster");
	exit();
}

//Return - 'Add Personnel File' or 'Edit Profile' clicked
if (isset($_GET['action'])) {
	$action = $_GET['action'];
	
	//GoTo from event where add/edit was attempted but minimum requirements were not met
	MinReqsFailed:

	switch ($action) {
		case "add":
			if ($_GET['action'] == "add") {
				//TRUE add
				$displayButtons = "
					<form method=\"POST\" id=\"add_file\" action=\"personnel.php?sub=roster&action=added\">
						<input type=\"submit\" name=\"added_bu\" class=\"tali-personnel-roster-addpersonnel-button\" value=\"Submit Personnel File\"/>
					</form>
					<form method=\"POST\" id=\"add_file\" action=\"personnel.php?sub=roster\">
						<input type=\"submit\" name=\"btnCancel\" class=\"tali-personnel-roster-addpersonnel-button\" value=\"Cancel\"/>
					</form>
				";
			}
			else
			{
				//Update or Min Reqs not met
				
				$displayButtons = "
					<form method=\"POST\" id=\"add_file\" action=\"personnel.php?sub=roster&action=added\">
						<input type=\"submit\" name=\"added_bu\" class=\"tali-personnel-roster-addpersonnel-button\" value=\"Submit Personnel File\"/>
					</form>
					<form method=\"POST\" id=\"add_file\" action=\"personnel.php?sub=roster\">
						<input type=\"submit\" name=\"btnCancel\" class=\"tali-personnel-roster-addpersonnel-button\" value=\"Cancel\"/>
					</form>
				";
				
				//id only POSTed when true Update, NOT when min reqs not met
				if (isset($_GET['id'])) {
					//Update
					$personnel_id = $_GET['id'];
					
					//Pull discharged status
					$SQL = "SELECT discharged FROM tali_personnel_roster WHERE personnel_id=$personnel_id";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					
					$discharged = $db_field['discharged'];
					
					$discharged_button = "";
					
					//Determine which discharge-related button to display
					if ($discharged == 0) {
						//Active duty, so offer to discharge
						$discharged_button = "<input type=\"submit\" name=\"btnDischarge\" class=\"tali-personnel-roster-addpersonnel-button\" onclick=\"return confirm('Are you sure you want to discharge this personnel file, removing it from Active Duty?');\" value=\"Discharge File\"/>";
					}
					else
					{
						//Already discharged, so offer to un-discharge
						$discharged_button = "<input type=\"submit\" name=\"btnUnDischarge\" class=\"tali-personnel-roster-addpersonnel-button\" onclick=\"return confirm('Are you sure you want to un-discharge this personnel file, placing it on Active Duty?');\" value=\"Un-Discharge File\"/>";
					}
					
					//Display remaining buttons for Update
					$displayButtons = "
						<form method=\"POST\" id=\"add_file\" action=\"personnel.php?sub=roster&action=added&id=$personnel_id\">
							<input type=\"submit\" name=\"added_bu\" class=\"tali-personnel-roster-addpersonnel-button\" value=\"Update Personnel File\"/>
						</form>
						<form method=\"POST\" id=\"add_file\" action=\"personnel.php?sub=roster&id=$personnel_id\">
							<input type=\"submit\" name=\"btnCancel\" class=\"tali-personnel-roster-addpersonnel-button\" value=\"Cancel Update\"/>
							<input type=\"submit\" name=\"btnDelete\" class=\"tali-personnel-roster-addpersonnel-button\" onclick=\"return confirm('Are you sure you want to DELETE this personnel file (NOT discharge)?');\" value=\"Delete File\"/>
							$discharged_button
						</form>
					";
				}
			}
			echo "
				<div class=\"tali-page-frame\">
					<h1>Manage Personnel File</h1>
					<p>This page allows for management of an individual's personnel file.</p>
					<p>Note: To Add/Save a Personnel File, a minimum of Rank, First Name, Last Name, Designation, Role, Status, E-Mail, and Date Enlisted must be provided.</p>
			";
			
			//If defined, display errorMessage
			if ($errorMessage != "") {
				echo "
					<p><font color=\"red\">$errorMessage</font></p>
				";
			};
			
			echo "
				</div>
			";
			
			echo "
				<div class=\"tali-page-frame\">
					<h1>
			";
			
			//Display section title depending on action
			if (($_GET['action'] == 'add') || (!isset($_POST['id']))) {
				echo "
						Add Personnel File
				";
			}
			else
			{
				$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				echo "
						Edit Personnel File of ".$db_field['abbreviation']." ".$db_field['firstname']." ".$db_field['lastname']."
				";
			}
			
			echo "	
					</h1>
					
					<table class=\"tali-personnel-roster-addpersonnel-table\">
					<col width=\"50%\">
					<col width=\"50%\">
					
					<tr>
						<td style=\"text-align:right;\"><strong>Rank:</strong></td>
						<td style=\"text-align:left;\">
							<select class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_rank_id\" form=\"add_file\" value=\"$add_rank_id\">
								<option value=\"\">Select a Rank</option>
			";
			$SQL = "SELECT * FROM tali_personnel_ranks ORDER BY weight DESC";
			$result = mysqli_query($db_handle, $SQL);
			while ($db_field = mysqli_fetch_assoc($result)) {
				if ($add_rank_id == $db_field['rank_id']) {
					$selected = 'selected="selected"';
				}
				else
				{
					$selected = '';
				}
				echo "
								<option value=\"{$db_field['rank_id']}\"".$selected.">{$db_field['name']}</option>
				";
			}
			echo "
							</select>
						
						</td>
					</tr>
					<tr>
						<td style=\"text-align:right;\"><strong>First Name:</strong></td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_firstname\" form=\"add_file\" value=\"$add_firstname\"></td>
					</tr>
					<tr>
						<td style=\"text-align:right;\"><strong>Last Name:</strong></td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_lastname\" form=\"add_file\" value=\"$add_lastname\"></td>
					</tr>
					<tr>
						<td style=\"text-align:right;\">Nickname:</td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_nickname\" form=\"add_file\" value=\"$add_nickname\"></td>
					</tr>
					<tr>
						<td style=\"text-align:right;\"><strong>Designation:</strong></td>
						<td style=\"text-align:left;\">
							
			";
			
			//Select Designation
			
			//Prepare a dropdown of designations for selection/action
			echo "						
				<select form=\"add_file\" id=\"designation_select\" name=\"designation_select\">
					<option value=\"\">Select a Designation</option>
			";
			
			//Select all designations
			$SQL = "SELECT * FROM tali_personnel_designations WHERE inactive=0 ORDER BY weight DESC";
			$result = mysqli_query($db_handle, $SQL);
			
			//Store designation data collected from above query in array
			$arrayDesignation = [];
			while ($db_field = mysqli_fetch_assoc($result)) {
				$arrayDesignation[] = ["designation_id" => $db_field["designation_id"], "reportsto_designation_id" => $db_field['reportsto_designation_id']];
			}
			
			//Create function that will print each row in the table
			//$db_handle carried in due to needing to query inside function
			//First parameter is the designation (with array of data) to be printed
			function designationRow ($db_handle, $arrayDesignation_selected, $add_designation_id) {
				$chosen_id = $arrayDesignation_selected['designation_id'];
				//Obtain information about the designation from the database
				$SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$chosen_id";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$designation_id=$db_field['designation_id'];
				$reportsTo=$db_field['reportsto_designation_id'];
				
				//Prepare to create long, full designation name
				//Will make an array of the chain of command, invert it, and make it a string
				$full_desig_name_array = array();
				//Carry reportsTo number through coming while loop while maintaining original
				$reportsTo_cycle = $reportsTo;
				//Add selected designation's name to array first before adding the rest
				$full_desig_name_array[] = $db_field['name'];

				//If the selected designation reports to another designation, print their info, and continue
				//until the newly selected designation does not report to anyone (top of the chain)
				while ($reportsTo_cycle != 0) {
					$desName_SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$reportsTo_cycle";
					$desName_result = mysqli_query($db_handle, $desName_SQL);
					$desigName_db_field = mysqli_fetch_assoc($desName_result);
					//Add designation to array
					$full_desig_name_array[] = $desigName_db_field['name'];
					//Change reportsTo id for next cycle
					$reportsTo_cycle = $desigName_db_field['reportsto_designation_id'];
				}
				
				//Reverse the array and turn it into a string to finally define the full designation name
				$full_desig_name = implode(", ", array_reverse($full_desig_name_array));
				
				if ($add_designation_id == $designation_id) {
					$selected = 'selected="selected"';
				}
				else
				{
					$selected = '';
				}
				
				//List designations as options
				echo "
					<option value=\"$designation_id\" ".$selected.">$full_desig_name</option>
				";
			}
			
			//The below function and forEach are used together to correctly list designations
			//based on weight and chain of command, similar to how it's done on the front Roster
			function designationsFill ($db_handle, $arrayDesignation, $designation, $add_designation_id) {
				foreach ($arrayDesignation as $designation_reportto) {
					if ($designation['designation_id'] == $designation_reportto['reportsto_designation_id']) {
						designationRow ($db_handle, $designation_reportto, $add_designation_id);
											
						designationsFill ($db_handle, $arrayDesignation, $designation_reportto, $add_designation_id);
					}
				}
			}
			
			foreach ($arrayDesignation as $designation) {
				if ($designation['reportsto_designation_id'] == 0) {
					designationRow ($db_handle, $designation, $add_designation_id);
									
					designationsFill ($db_handle, $arrayDesignation, $designation, $add_designation_id);
				}
			}
			
			echo "
				</select>
			";

			//End designation stuff
			
			echo "
					<tr>
						<td style=\"text-align:right;\"><strong>Role:</strong></td>
						<td style=\"text-align:left;\">
							<select class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_role_id\" form=\"add_file\" value=\"$add_role_id\">
								<option value=\"\">Select a Role</option>
			";
			$SQL = "SELECT * FROM tali_personnel_roles ORDER BY weight DESC";
			$result = mysqli_query($db_handle, $SQL);
			while ($db_field = mysqli_fetch_assoc($result)) {
				if ($add_role_id == $db_field['role_id']) {
					$selected = 'selected="selected"';
				}
				else
				{
					$selected = '';
				}
				echo "
								<option value=\"{$db_field['role_id']}\"".$selected.">{$db_field['name']}</option>
				";
			}
			echo "
							</select>
						
						</td>
					</tr>						
					
					<tr>
						<td style=\"text-align:right;\"><strong>Status:</strong></td>
						<td style=\"text-align:left;\">
							<select class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_status_id\" form=\"add_file\" value=\"$add_status_id\">
								<option value=\"\">Select a Status</option>
			";
			$SQL = "SELECT * FROM tali_personnel_statuses ORDER BY weight DESC";
			$result = mysqli_query($db_handle, $SQL);
			while ($db_field = mysqli_fetch_assoc($result)) {
				if ($add_status_id == $db_field['status_id']) {
					$selected = 'selected="selected"';
				}
				else
				{
					$selected = '';
				}
				echo "
								<option value=\"{$db_field['status_id']}\"".$selected.">{$db_field['name']}</option>
				";
			}
			//bug - "points stuff" at bottom of this echo, should it be?
			echo "
							</select>
						
						</td>
					</tr>
					<tr><td><br/></td></tr>
					<tr>
						<td style=\"text-align:right;\"><strong>E-Mail:</strong></td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_email\" form=\"add_file\" value=\"$add_email\"></td>
					</tr>
					<tr>
						<td style=\"text-align:right;\">Other Contact:</td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_othercontact\" form=\"add_file\" value=\"$add_othercontact\"></td>
					</tr>
					<tr>
						<td style=\"text-align:right;\">Location:</td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_location\" form=\"add_file\" value=\"$add_location\"></td>
					</tr>
					<tr>
						<td style=\"text-align:right;\">Date of Birth (MM/DD/YYYY):</td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_dateofbirth\" form=\"add_file\" maxlength=\"10\" value=\"$add_dateofbirth\"></td>
					</tr>
					<tr>
						<td style=\"text-align:right;\">Biography:</td>
						<td style=\"text-align:left;\"><textarea class=\"tali-personnel-roster-addpersonnel-textarea\" name=\"add_biography\" form=\"add_file\" value=\"$add_biography\">$add_biography</textarea>
					</tr>
					<tr><td><br/></td></tr>
					<tr>
						<td style=\"text-align:right;\"><strong>Date Enlisted (MM/DD/YYYY):</strong></td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_dateenlisted\" form=\"add_file\" maxlength=\"10\" value=\"$add_dateenlisted\"></td>
					</tr>
					<tr>
						<td style=\"text-align:right;\">Date Last Promoted (MM/DD/YYYY):</td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_datepromoted\" form=\"add_file\" maxlength=\"10\" value=\"$add_datepromoted\"></td>
					</tr>
					<tr>
						<td style=\"text-align:right;\">Date Discharged (MM/DD/YYYY):</td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_datedischarged\" form=\"add_file\" maxlength=\"10\" value=\"$add_datedischarged\"></td>
					</tr>
					<tr><td><br/></td></tr>							
					<tr>
						<td style=\"text-align:right;\">Admin Account Username:</td>
						<td style=\"text-align:left;\"><input type=\"text\" class=\"tali-personnel-roster-addpersonnel-inline_input\" name=\"add_adminusername\" form=\"add_file\" value=\"$add_adminusername\"></td>
					</tr>
					<tr><td><br/></td></tr>
					<!--points stuff-->
				</table>
				
				$displayButtons
				</div>
			";
		break;
		
		case "added":
		
			//'Add File' button clicked, attempt to submit data
			
			//Assigns input variables from post, will be used either way
			if (isset($_POST['add_rank_id'])) {$add_rank_id = $_POST['add_rank_id'];};
			if (isset($_POST['add_firstname'])) {$add_firstname = $_POST['add_firstname'];};
			if (isset($_POST['add_lastname'])) {$add_lastname = $_POST['add_lastname'];};
			if (isset($_POST['add_nickname'])) {$add_nickname = $_POST['add_nickname'];};
			if (isset($_POST['add_status_id'])) {$add_status_id = $_POST['add_status_id'];};
			
			if (isset($_POST['designation_select'])) {$add_designation_id = $_POST['designation_select'];};
			
			if (isset($_POST['add_role_id'])) {$add_role_id = $_POST['add_role_id'];};

			if (isset($_POST['add_email'])) {$add_email = $_POST['add_email'];};
			if (isset($_POST['add_othercontact'])) {$add_othercontact = $_POST['add_othercontact'];};
			if (isset($_POST['add_location'])) {$add_location = $_POST['add_location'];};
			if (isset($_POST['add_dateofbirth'])) {$add_dateofbirth = $_POST['add_dateofbirth'];};
			if (isset($_POST['add_biography'])) {$add_biography = $_POST['add_biography'];};
			
			if (isset($_POST['add_dateenlisted'])) {$add_dateenlisted = $_POST['add_dateenlisted'];};
			if (isset($_POST['add_datepromoted'])) {$add_datepromoted = $_POST['add_datepromoted'];};
			if (isset($_POST['add_datedischarged'])) {$add_datedischarged = $_POST['add_datedischarged'];};
		
			if (isset($_POST['add_adminusername'])) {$add_adminusername = $_POST['add_adminusername'];};
	
			//Check minimum input
			//BUG - role only here because it doesn't like adding null?
			if (
			(isset($_POST['add_rank_id'])) && (($_POST['add_rank_id']) != "") &&
			(isset($_POST['add_firstname'])) && (($_POST['add_firstname']) != "") &&
			(isset($_POST['add_lastname'])) && (($_POST['add_lastname']) != "") &&
			(isset($_POST['add_status_id'])) && (($_POST['add_status_id']) != "") &&
			(isset($_POST['designation_select'])) && (($_POST['designation_select']) != "") &&
			(isset($_POST['add_role_id'])) && (($_POST['add_role_id']) != "") && 
			(isset($_POST['add_email'])) && (($_POST['add_email']) != "") &&
			(isset($_POST['add_dateenlisted'])) && (($_POST['add_dateenlisted']) != "")
			) {
				//Enough input provided, submit to DB and redirect to roster
				
				//"Straightens up" specific input for safe insertion
				$add_firstname_sql = htmlspecialchars($add_firstname);
				$add_firstname_sql = TALI_quote_smart($add_firstname_sql, $db_handle);
				
				$add_lastname_sql = htmlspecialchars($add_lastname);
				$add_lastname_sql = TALI_quote_smart($add_lastname_sql, $db_handle);
				
				$add_nickname_sql = htmlspecialchars($add_nickname);
				$add_nickname_sql = TALI_quote_smart($add_nickname_sql, $db_handle);
				
				$add_email_sql = htmlspecialchars($add_email);
				$add_email_sql = TALI_quote_smart($add_email_sql, $db_handle);
				
				$add_othercontact_sql = htmlspecialchars($add_othercontact);
				$add_othercontact_sql = TALI_quote_smart($add_othercontact_sql, $db_handle);
				
				$add_location_sql = htmlspecialchars($add_location);
				$add_location_sql = TALI_quote_smart($add_location_sql, $db_handle);
				
				$add_biography_sql = htmlspecialchars($add_biography);
				$add_biography_sql = TALI_quote_smart($add_biography_sql, $db_handle);
				
				if ($add_dateofbirth == "") {
					$add_dateofbirth_sql = "NULL";
				}
				else
				{
					$add_dateofbirth_sql = date("Y-m-d", strtotime($add_dateofbirth));
					$add_dateofbirth_sql = htmlspecialchars($add_dateofbirth_sql);
					$add_dateofbirth_sql = TALI_quote_smart($add_dateofbirth_sql, $db_handle);
				}
				
				if ($add_dateenlisted == "") {
					$add_dateenlisted_sql = "NULL";
				}
				else
				{
					$add_dateenlisted_sql = date("Y-m-d", strtotime($add_dateenlisted));
					$add_dateenlisted_sql = htmlspecialchars($add_dateenlisted_sql);
					$add_dateenlisted_sql = TALI_quote_smart($add_dateenlisted_sql, $db_handle);
				}
				
				if ($add_datepromoted == "") {
					$add_datepromoted_sql = $add_dateenlisted_sql;
				}
				else
				{
					$add_datepromoted_sql = date("Y-m-d", strtotime($add_datepromoted));
					$add_datepromoted_sql = htmlspecialchars($add_datepromoted_sql);
					$add_datepromoted_sql = TALI_quote_smart($add_datepromoted_sql, $db_handle);
				}
				
				if ($add_datedischarged == "") {
					$add_datedischarged_sql = "NULL";
				}
				else
				{
					$add_datedischarged_sql = date("Y-m-d", strtotime($add_datedischarged));
					$add_datedischarged_sql = htmlspecialchars($add_datedischarged_sql);
					$add_datedischarged_sql = TALI_quote_smart($add_datedischarged_sql, $db_handle);
				}
				
				//Admin username
				//MYSQL attempt to pull the original username from personnel id
				if (isset($_GET['id'])) {
					//Updating existing account
					$personnel_id = $_GET['id'];
					$SQL = "SELECT username FROM tali_admin_accounts WHERE personnel_id=$personnel_id";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					//if returned false, means no admin/personnnel account association,
					//so only proceed if it's true
					$num_rows = mysqli_num_rows($result);
					if ($num_rows > 0) {
						//Therefore admin account associated with personnel record
						$oldusername = $db_field['username'];
						//If the new username is the same as the old, no action is taken
						//If the new username is different, then we do stuff
						if ($add_adminusername != $oldusername) {
							//New username entered
							//Check to see if it is blank, indicating deletion of admin account
							if ($add_adminusername == "") {
								//Indication to delete account
								$oldusername_sql = htmlspecialchars($oldusername);
								$oldusername_sql = TALI_quote_smart($oldusername_sql, $db_handle);
								$SQL = "SELECT id FROM tali_admin_accounts WHERE username=$oldusername_sql";
								$result = mysqli_query($db_handle, $SQL);
								$db_field = mysqli_fetch_assoc($result);
								$delid = $db_field['id'];
								//History Report before data deleted
								TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_admin_accounts', 'id', $delid, 'Admin Account ID#', 'username');
		
								//Delete admin account
								$delSQL = "DELETE FROM tali_admin_accounts WHERE username=$oldusername_sql";
								$delresult = mysqli_query($db_handle, $delSQL);
							}
							else
							{
								$add_adminusername_sql = htmlspecialchars($add_adminusername);
								$add_adminusername_sql = TALI_quote_smart($add_adminusername_sql, $db_handle);
								//Indication to update account
								//Check to see if it is in use
								$SQL = "SELECT username FROM tali_admin_accounts WHERE username=$add_adminusername_sql";
								$result = mysqli_query($db_handle, $SQL);
								$db_field = mysqli_fetch_assoc($result);
								$num_rows = mysqli_num_rows($result);
								//If anything is returned, username exists, so need to error
								if ($num_rows > 0) {
									//Username already exists, cause error
									$action = "add";
									$errorMessage = "ERROR: The Admin Account Username that was entered is already in use.";
									goto MinReqsFailed;
								}
								else
								{
									//Otherwise, update
									$SQL = "UPDATE tali_admin_accounts SET username=$add_adminusername_sql WHERE personnel_id=$personnel_id"; 
									$result = mysqli_query($db_handle, $SQL);
									
									//History Report
									$SQL = "SELECT id FROM tali_admin_accounts WHERE personnel_id=$personnel_id";
									$result = mysqli_query($db_handle, $SQL);
									$db_field = mysqli_fetch_assoc($result);
									$id=$db_field['id'];	
									
									TALI_Create_History_Report('edited', $module, $db_handle, 'tali_admin_accounts', 'id', $id, 'Admin Account ID#', 'username');
								}
							}
						}
					}
					else
					{
						if ($add_adminusername != "") {
							//Using update to add new admin account
							//Check to see if it is in use
							$add_adminusername_sql = htmlspecialchars($add_adminusername);
							$add_adminusername_sql = TALI_quote_smart($add_adminusername_sql, $db_handle);
							$SQL = "SELECT username FROM tali_admin_accounts WHERE username=$add_adminusername_sql";
							$result = mysqli_query($db_handle, $SQL);
							$num_rows = mysqli_num_rows($result);
							//If anything is returned, username exists, so need to error
							if ($num_rows > 0) {
								//Username already exists, cause error
								$action = "add";
								$errorMessage = "ERROR: The Admin Account Username that was entered is already in use.";
								goto MinReqsFailed;
							}
							else
							{
								//Otherwise, insert										
								//Obtain random password and clean it up
								//Generate random password
								$characters = "abcdefghijklmnopqrstuvwxyz0123456789";
								$newpassword = "";
								$max = strlen($characters) - 1;
								for ($i = 0; $i < 10; $i++) {
									$newpassword .= $characters[mt_rand(0, $max)];
								}
								$newpassword_sql = htmlspecialchars($newpassword);
								$newpassword_sql = TALI_quote_smart($newpassword_sql, $db_handle);
								
								//Obtain highest (lowest power) admin level from database for default
								$SQL = "SELECT level FROM tali_admin_permissions LIMIT 1";
								$result = mysqli_query($db_handle, $SQL);
								$db_field = mysqli_fetch_assoc($result);
								$newlevel_sql = TALI_quote_smart($db_field['level'], $db_handle);
								
								$SQL = "INSERT INTO tali_admin_accounts (level, username, email, password, , password_reset_token, personnel_id) VALUES ($newlevel_sql, $add_adminusername_sql, $add_email_sql, md5($newpassword_sql), '', $personnel_id)";
								$result = mysqli_query($db_handle, $SQL);
								
								//History Report
								$SQL = "SELECT id FROM tali_admin_accounts ORDER BY id DESC LIMIT 1";
								$result = mysqli_query($db_handle, $SQL);
								$db_field = mysqli_fetch_assoc($result);
								$newadminid=$db_field['id'];	
								
								TALI_Create_History_Report('created', $module, $db_handle, 'tali_admin_accounts', 'id', $newadminid, 'Admin Account ID#', 'username');
							}
						}
					}
				}
				else
				{
					//Adding file, so any username is new
					if ($add_adminusername != "") {
						//Check to see if it is in use
						$add_adminusername_sql = htmlspecialchars($add_adminusername);
						$add_adminusername_sql = TALI_quote_smart($add_adminusername_sql, $db_handle);
						$SQL = "SELECT username FROM tali_admin_accounts WHERE username=$add_adminusername_sql";
						$result = mysqli_query($db_handle, $SQL);
						$num_rows = mysqli_num_rows($result);
						//If anything is returned, username exists, so need to error
						if ($num_rows > 0) {
							//Username already exists, cause error
							$action = "add";
							$errorMessage = "ERROR: The Admin Account Username that was entered is already in use.";
							goto MinReqsFailed;
						}
						else
						{
							//Otherwise, insert
							//Clean up username for MySQL
							$add_adminusername_sql = htmlspecialchars($add_adminusername);
							$add_adminusername_sql = TALI_quote_smart($add_adminusername_sql, $db_handle);
							
							//Generate random password
							$characters = "abcdefghijklmnopqrstuvwxyz0123456789";
							$newpassword = "";
							$max = strlen($characters) - 1;
							for ($i = 0; $i < 10; $i++) {
								$newpassword .= $characters[mt_rand(0, $max)];
							}
							$newpassword_sql = htmlspecialchars($newpassword);
							$newpassword_sql = TALI_quote_smart($newpassword_sql, $db_handle);
							
							//Obtain highest (lowest power) admin level from database for default
								//bug - make more edits to allow below "$" fix, delete this bug line?
							$SQL = "SELECT level FROM tali_admin_permissions LIMIT 1";
							$result = mysqli_query($db_handle, $SQL);
							$db_field = mysqli_fetch_assoc($result);
							$newlevel_sql = TALI_quote_smart($db_field['level'], $db_handle);
							
							$SQL = "INSERT INTO tali_admin_accounts (level, username, email, password, password_reset_token, personnel_id) VALUES ($newlevel_sql, $add_adminusername_sql, $add_email_sql, md5($newpassword_sql), '', '')";
							$result = mysqli_query($db_handle, $SQL);
							
							//History Report
							$SQL = "SELECT id FROM tali_admin_accounts ORDER BY id DESC LIMIT 1";
							$result = mysqli_query($db_handle, $SQL);
							$db_field = mysqli_fetch_assoc($result);
							$newadminid=$db_field['id'];	
							
							TALI_Create_History_Report('created', $module, $db_handle, 'tali_admin_accounts', 'id', $newadminid, 'Admin Account ID#', 'username');
						}
					}
				}
				if (isset($_GET['id'])) {
					//UPDATE
					$personnel_id = $_GET['id'];
					$SQL = "UPDATE tali_personnel_roster SET rank_id=$add_rank_id, firstname=$add_firstname_sql, lastname=$add_lastname_sql, nickname=$add_nickname_sql, status_id=$add_status_id, designation_id='$add_designation_id', role_id=$add_role_id, email=$add_email_sql, othercontact=$add_othercontact_sql, location=$add_location_sql, biography=$add_biography_sql, dateofbirth=$add_dateofbirth_sql, date_enlisted=$add_dateenlisted_sql, date_promoted=$add_datepromoted_sql, date_discharged=$add_datedischarged_sql WHERE personnel_id=$personnel_id"; 
					$result = mysqli_query($db_handle, $SQL);
				
					//History Report
					TALI_Create_History_Report('updated', $module, $db_handle, 'tali_personnel_roster', 'personnel_id', $personnel_id, 'Personnel ID#', 'lastname');
				
					//Also attempt to update e-mail in associated admin account if it exists
					$SQL = "SELECT id FROM tali_admin_accounts WHERE personnel_id=$personnel_id";
					$result = mysqli_query($db_handle, $SQL);
					$num_rows = mysqli_num_rows($result);
					if ($num_rows > 0) {
						//Account is associated, so update e-mail
						$SQL = "UPDATE tali_admin_accounts SET email=$add_email_sql WHERE personnel_id=$personnel_id"; 
						$result = mysqli_query($db_handle, $SQL);
						
						//History Report
						$SQL = "SELECT id FROM tali_admin_accounts WHERE personnel_id=$personnel_id";
						$result = mysqli_query($db_handle, $SQL);
						$db_field = mysqli_fetch_assoc($result);
						$id=$db_field['id'];	
						
						TALI_Create_History_Report('edited', $module, $db_handle, 'tali_admin_accounts', 'id', $id, 'Admin Account ID#', 'username');
					}
				}
				else
				{
					//ADD
					//bug - with move to PHP 7, this is broken (need to fill this with the entire table and no longer assume empties, then take that opportunity to test everything in personnel)
					$SQL = "INSERT INTO tali_personnel_roster (rank_id, firstname, lastname, nickname, status_id, designation_id, role_id, email, othercontact, location, biography, dateofbirth, date_enlisted, date_promoted, date_discharged, discharged) VALUES ($add_rank_id, $add_firstname_sql, $add_lastname_sql, $add_nickname_sql, $add_status_id, '$add_designation_id', $add_role_id, $add_email_sql, $add_othercontact_sql, $add_location_sql, $add_biography_sql, $add_dateofbirth_sql, $add_dateenlisted_sql, $add_datepromoted_sql, $add_datedischarged_sql, 0)";
					$result = mysqli_query($db_handle, $SQL);
					
					$SQL = "SELECT personnel_id FROM tali_personnel_roster ORDER BY personnel_id DESC LIMIT 1";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					$personnel_id=$db_field['personnel_id'];	
					
					if ((isset($newadminid)) && ($newadminid != "")) {
						$SQL = "UPDATE tali_admin_accounts SET personnel_id=$personnel_id WHERE id=$newadminid";
						$result = mysqli_query($db_handle, $SQL);
					}
					
					//History Report
					TALI_Create_History_Report('created', $module, $db_handle, 'tali_personnel_roster', 'personnel_id', $personnel_id, 'Personnel ID#', 'lastname');
				}
										
				header ("Location: personnel.php?sub=roster");
				exit();
			}
			else
			{
				//Minimum not provided to submit, so open edit page back up and state what is missing
				$action = "add";
				$errorMessage = "ERROR: Minimum requirements for adding a personnel file not met! See note.";
				goto MinReqsFailed;
			}
		
		break;
		
		case "edit":
			$personnel_id = $_GET['id'];
			$SQL = "SELECT * FROM tali_personnel_roster WHERE personnel_id=$personnel_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			
			$add_rank_id = $db_field['rank_id'];
			$add_firstname = $db_field['firstname'];
			$add_lastname = $db_field['lastname'];
			$add_nickname = $db_field['nickname'];
			$add_role_id = $db_field['role_id'];
			$add_status_id = $db_field['status_id'];
			$add_designation_id = $db_field['designation_id'];
			$add_email = $db_field['email'];
			$add_othercontact = $db_field['othercontact'];
			$add_location = $db_field['location'];
			$add_biography = $db_field['biography'];

			//For dates below, converting dates from SQL format (YYYY-MM-DD) to 
			//human format (MM/DD/YYYY), respecting NULL value
			
			$add_dateofbirth = $db_field['dateofbirth'];
			if (!is_null($add_dateofbirth)) {
				$add_dateofbirth = date("m/d/Y", strtotime($add_dateofbirth));
			}
			$add_dateenlisted = $db_field['date_enlisted'];
			if (!is_null($add_dateenlisted)) {
				$add_dateenlisted = date("m/d/Y", strtotime($add_dateenlisted));
			}
			$add_datepromoted = $db_field['date_promoted'];
			if (!is_null($add_datepromoted)) {
				$add_datepromoted = date("m/d/Y", strtotime($add_datepromoted));
			}
			$add_datedischarged = $db_field['date_discharged'];
			if (!is_null($add_datedischarged)) {
				$add_datedischarged = date("m/d/Y", strtotime($add_datedischarged));
			}
			
			//Pull admin account, if associated
			$SQL = "SELECT username FROM tali_admin_accounts WHERE personnel_id=$personnel_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$num_rows = mysqli_num_rows($result);
			if ($num_rows > 0) {
				$add_adminusername = $db_field['username'];
			}
			
			$action="add";
			goto MinReqsFailed;
							
		break;
	}
}
else
{
	if (isset($_GET['type'])) {
		//bug - make this more simple, more functional, less duplicate!
		//Past Roster
		echo "
			<div class=\"tali-page-frame\">
				<h1>Manage Roster</h1>
				<p>This page allows for management of the personnel roster and all associated personnel data.</p>
			</div>
		";
		
		echo "
			<div class=\"tali-page-frame\">
				<h1>Past Members Roster</h1>
				<br/>
				<table class=\"tali-personnel-roster-links\">
					<col width=\"50%\">
					<col width=\"50%\">
					<tr>
						<th><a href=\"personnel.php?sub=roster\">Active Members</a></th>
						<th><a href=\"personnel.php?sub=roster&type=past\">Past Members</a></th>
					</tr>
				</table>
				
				<table class=\"tali-personnel-table\">
					<col width=\"10%\">
					<col width=\"30%\">
					<col width=\"20%\">
					<col width=\"10%\">
					<col width=\"10%\">
					<col width=\"10%\">
					<col width=\"5%\">
					<col width=\"5%\">
					<tr>
						<th>Rank</th>
						<th>Name</th>
						<th>Status</th>
						<th>Awards</th>
						<th>Service Record</th>
						<th>Uniform</th>
						<th>Profile</th>
						<th>ID</th>
					</tr>
		";
		
		$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE discharged=1 ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
		$result = mysqli_query($db_handle, $SQL);
			
		while ($db_field = mysqli_fetch_assoc($result)) {
			if ($db_field['discharged'] == 1) {
				$personnel_id=$db_field['personnel_id'];
				$rank_id=$db_field['rank_id'];
				$rankSQL = "SELECT * FROM tali_personnel_ranks WHERE rank_id=$rank_id";
				$rankresult = mysqli_query($db_handle, $rankSQL);
				$rank_db_field = mysqli_fetch_assoc($rankresult);
				$rank_abr=$rank_db_field['abbreviation'];
				$firstname=$db_field['firstname'];
				$lastname=$db_field['lastname'];
				$status_id=$db_field['status_id'];
				$statusSQL = "SELECT * FROM tali_personnel_statuses WHERE status_id=$status_id";
				$statusresult = mysqli_query($db_handle, $statusSQL);
				$status_db_field = mysqli_fetch_assoc($statusresult);
				$status=$status_db_field['name'];
				
				echo "
						<tr>
							<td style=\"text-align:center;\">$rank_abr</td>
							<td style=\"text-align:center;\">$firstname $lastname</td>
							<td style=\"text-align:center;\">$status</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster_awards&id=$personnel_id\">
									<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster_servicerecord&id=$personnel_id\">
									<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster_uniform&id=$personnel_id\">
									<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster&action=edit&id=$personnel_id\">
									<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							<td style=\"text-align:center;\">$personnel_id</td>
						</tr>
				";
			}
		}
		
		echo "
				</table>
			
				<form method=\"POST\" id=\"\" action=\"personnel.php?sub=roster&action=add\">
					<p>
					<input type=\"submit\" name=\"add_bu\" value=\"Add Personnel File\"/>
					</p>
				</form>
			</div>
		";
	}
	else
	{
		//Active Roster
		echo "
			<div class=\"tali-page-frame\">
				<h1>Manage Roster</h1>
				<p>This page allows for management of the personnel roster and all associated personnel data.</p>
			</div>
		";
		
		echo "
			<div class=\"tali-page-frame\">
				<h1>Active Members Roster</h1>
				<br/>
				<table class=\"tali-personnel-roster-links\">
					<col width=\"50%\">
					<col width=\"50%\">
					<tr>
						<th><a href=\"personnel.php?sub=roster\">Active Members</a></th>
						<th><a href=\"personnel.php?sub=roster&type=past\">Past Members</a></th>
					</tr>
				</table>
				
				<table class=\"tali-personnel-table\">
					<col width=\"10%\">
					<col width=\"30%\">
					<col width=\"20%\">
					<col width=\"10%\">
					<col width=\"10%\">
					<col width=\"10%\">
					<col width=\"5%\">
					<col width=\"5%\">
					<tr>
						<th>Rank</th>
						<th>Name</th>
						<th>Status</th>
						<th>Awards</th>
						<th>Service Record</th>
						<th>Uniform</th>
						<th>Profile</th>
						<th>ID</th>
					</tr>
		";
		
		$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE discharged=0 ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
		$result = mysqli_query($db_handle, $SQL);
			
		while ($db_field = mysqli_fetch_assoc($result)) {
			if ($db_field['discharged'] == 0) {
				$personnel_id=$db_field['personnel_id'];
				$rank_id=$db_field['rank_id'];
				$rankSQL = "SELECT * FROM tali_personnel_ranks WHERE rank_id=$rank_id";
				$rankresult = mysqli_query($db_handle, $rankSQL);
				$rank_db_field = mysqli_fetch_assoc($rankresult);
				$rank_abr=$rank_db_field['abbreviation'];
				$firstname=$db_field['firstname'];
				$lastname=$db_field['lastname'];
				$status_id=$db_field['status_id'];
				$statusSQL = "SELECT * FROM tali_personnel_statuses WHERE status_id=$status_id";
				$statusresult = mysqli_query($db_handle, $statusSQL);
				$status_db_field = mysqli_fetch_assoc($statusresult);
				$status=$status_db_field['name'];
				
				echo "
						<tr>
							<td style=\"text-align:center;\">$rank_abr</td>
							<td style=\"text-align:center;\">$firstname $lastname</td>
							<td style=\"text-align:center;\">$status</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster_awards&id=$personnel_id\">
									<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster_servicerecord&id=$personnel_id\">
									<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster_uniform&id=$personnel_id\">
									<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster&action=edit&id=$personnel_id\">
									<img src=\"../images/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">$personnel_id</td>
						</tr>
				";
			}
		}
		
		echo "
				</table>
			
				<form method=\"POST\" id=\"\" action=\"personnel.php?sub=roster&action=add\">
					<p>
					<input type=\"submit\" name=\"add_bu\" value=\"Add Personnel File\"/>
					</p>
				</form>
			</div>
		";
	}
}

//bug - A better way to do this?
echo "
		</div>
	</main>
";
?>