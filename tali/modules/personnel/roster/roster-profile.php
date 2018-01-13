<?php		
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
$personnel_id = $_GET['personnel_id'];
$SQL = "SELECT * FROM tali_personnel_roster WHERE personnel_id=$personnel_id";
$result = mysqli_query($db_handle, $SQL);
$db_field = mysqli_fetch_assoc($result);

$rank_id = $db_field['rank_id'];

$rankSQL = "SELECT name FROM tali_personnel_ranks WHERE rank_id=$rank_id";
$rankresult = mysqli_query($db_handle, $rankSQL);
$rank_db_field = mysqli_fetch_assoc($rankresult);
$rank = $rank_db_field['name'];

$firstname = $db_field['firstname'];
$lastname = $db_field['lastname'];
$uniform_display_filename = $db_field['uniform'];

//Find the display uniform
$uniform_display_return = TALI_personnelUniformFinder($uniform_display_filename, TALI_UNIFORMS_IMAGES_URI, TALI_PERSONNEL_UNIFORMS_DEFAULT_FILE);
//url used to actually link to display file for display
$uniform_url = $uniform_display_return[2];

$nickname = $db_field['nickname'];
$status_id = $db_field['status_id'];

$statusSQL = "SELECT name FROM tali_personnel_statuses WHERE status_id=$status_id";
$statusresult = mysqli_query($db_handle, $statusSQL);
$status_db_field = mysqli_fetch_assoc($statusresult);
$status=$status_db_field['name'];

$designation_id = $db_field['designation_id'];
				
$role_id = $db_field['role_id'];

$roleSQL = "SELECT name FROM tali_personnel_roles WHERE role_id=$role_id";
$roleresult = mysqli_query($db_handle, $roleSQL);
$role_db_field = mysqli_fetch_assoc($roleresult);
$role=$role_db_field['name'];

$location = $db_field['location'];
$biography = $db_field['biography'];
		
$today_date = DateTime::createFromFormat('Y-m-d', date("Y-m-d"));

$date_enlisted_str = $db_field['date_enlisted'];
$date_enlisted = DateTime::createFromFormat('Y-m-d', $date_enlisted_str);
$date_enlisted_str = date("m/d/Y", strtotime($date_enlisted_str));
				
$date_promoted_str = $db_field['date_promoted'];
$date_promoted = DateTime::createFromFormat('Y-m-d', $date_promoted_str);
$date_promoted_str = date("m/d/Y", strtotime($date_promoted_str));
		
$date_discharged_str = $db_field['date_discharged'];
$date_discharged = DateTime::createFromFormat('Y-m-d', $date_discharged_str);
$date_discharged_str = date("m/d/Y", strtotime($date_discharged_str));
if ($date_discharged == false) {
	$date_discharged_str = "Unknown Date";
}

$discharged = $db_field['discharged'];

$discharged_designation_str = $db_field['discharged_designation'];
$discharged_designation = explode(",", $discharged_designation_str);

if ($discharged == 1) {
	$time_in_service = "Unknown";
	if ($date_discharged != false) {
		$time_in_service = date_diff($date_enlisted, $date_discharged);
		$time_in_service = $time_in_service->format('%y years, %m months, %d days');
	}
	
	$time_in_grade = "Unknown";
	if ($date_discharged != false) {
		$time_in_grade = date_diff($date_promoted, $date_discharged);
		$time_in_grade = $time_in_grade->format('%y years, %m months, %d days');
	}
}
else
{
	$time_in_service = date_diff($date_enlisted, $today_date);
	$time_in_service = $time_in_service->format('%y years, %m months, %d days');
	
	$time_in_grade = date_diff($date_promoted, $today_date);
	$time_in_grade = $time_in_grade->format('%y years, %m months, %d days');
}

echo "
	<div class=\"tali-personnel-roster-front-page-frame\">
		<div class = \"tali-personnel-roster-front-page-frame-title\">
			<h1>Personnel File</h1>
		</div>
		<br/>
		<table class=\"tali-personnel-roster-front-links\">
			<col width=\"25%\">
			<col width=\"25%\">
			<col width=\"25%\">
			<col width=\"25%\">
			<tr>
				<th><a href=\"roster.php?action=active\">Active Members</a></th>
				<th><a href=\"roster.php?action=past\">Past Members</a></th>
				<th><a href=\"roster.php?action=awards\">Awards</a></th>
				<th><a href=\"roster.php?action=ranks\">Ranks</a></th>
			</tr>
		</table>
";

echo "
		<table class=\"tali-personnel-roster-front-profile-top\">
			<col width=\"100%\">
			<tr>
				<th>$rank $firstname $lastname</th>
			</tr>
			<tr class=\"tali-personnel-roster-front-profile-top_noborder\">
				<td><img src=\"$uniform_url\" class=\"tali-personnel-roster-front-profile-uniform\" alt=\"Uniform\"></td>
			</tr>
		</table>
		<table class=\"tali-personnel-roster-front-profile-body\">
";

if ($discharged == 0) {
	//Active Duty
	
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
	$full_desig_name_array[] = $db_field['designation_id'];

	//If the selected designation reports to another designation, print their info, and continue
	//until the newly selected designation does not report to anyone (top of the chain)
	while ($reportsTo_cycle != 0) {
		$desName_SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$reportsTo_cycle";
		$desName_result = mysqli_query($db_handle, $desName_SQL);
		$desigName_db_field = mysqli_fetch_assoc($desName_result);
		//Add designation to array
		$full_desig_name_array[] = $desigName_db_field['designation_id'];
		//Change reportsTo id for next cycle
		$reportsTo_cycle = $desigName_db_field['reportsto_designation_id'];
	}
	//Reverse the array and turn it into a string to finally define the full designation name
	$full_desig_name_array = array_reverse($full_desig_name_array);
	$colWidth = 100/(count($full_desig_name_array));
	
	echo "
			<col width=\"".$colWidth."%\">
			<tr>
				<th colspan=\"100%\">Designation</th>
			</tr>
			<tr>
	";
	
	foreach ($full_desig_name_array as $designation) {
		//Cycle through each designation
		$SQL = "SELECT name FROM tali_personnel_designations WHERE designation_id=$designation";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		echo "
				<td>".$db_field['name']."</td>
		";
	}
}
else
{
	//Discharged, static designations
	$colWidth = 100/(count($discharged_designation));
	echo "
				<col width=\"".$colWidth."%\">
				<th colspan=\"100%\">Designation at Discharge on $date_discharged_str</th>
			</tr>
			<tr>
	";
	foreach ($discharged_designation as $designation) {
		echo "
				<td>$designation</td>
		";
	}
}
echo "
			</tr>
		</table>
		<table class=\"tali-personnel-roster-front-profile-body\">
			<col width=\"20%\">
			<col width=\"30%\">
			<col width=\"20%\">
			<col width=\"30%\">
			<tr>
				<th>Location</th>
				<td>$location</td>
				<th>Nickname</th>
				<td>$nickname</td>
			</tr>
			<tr>
				<th>Status</th>
				<td>$status</td>
				<th>Role</th>
				<td>$role</td>
			</tr>
			<tr>
				<th>Enlisted</th>
				<td>$date_enlisted_str</td>
				<th>Promoted</th>
				<td>$date_promoted_str</td>
			</tr>
			<tr>
				<th>Time in Service</th>
				<td>$time_in_service</td>
				<th>Time in Grade</th>
				<td>$time_in_grade</td>
			</tr>
		</table>
		<table class=\"tali-personnel-roster-front-profile-body\">
			<tr>
				<th>Biography</th>
			</tr>
			<tr>
				<td align=\"left\" style=\"padding:5px;\">$biography</td>
			</tr>
		</table>
		<table class=\"tali-personnel-roster-front-profile-body\">
			<col width=\"15%\">
			<col width=\"85%\">
			<tr>
				<th colspan=\"2\">Service Record</th>
			</tr>
";
$SQL = "SELECT * FROM tali_personnel_service_record WHERE personnel_id=$personnel_id ORDER BY date DESC";
$result = mysqli_query($db_handle, $SQL);
$maxDesigs = 0;
while ($db_field = mysqli_fetch_assoc($result)) {
	echo "
			<tr>
				<th>".date("m/d/Y", strtotime($db_field['date']))."</th>
				<td align=\"left\" style=\"padding:5px;\">".$db_field['record']."</td>
			</tr>
	";
}

echo "
		</table>
		<table class=\"tali-personnel-roster-front-profile-body\">
			<col width=\"15%\">
			<col width=\"30%\">
			<col width=\"15%\">
			<col width=\"40%\">
			<tr>
				<th colspan=\"4\">Award Record</th>
			</tr>
";
$SQL = "SELECT * FROM tali_personnel_awards_record WHERE personnel_id=$personnel_id ORDER BY date_awarded DESC";
$result = mysqli_query($db_handle, $SQL);
$maxDesigs = 0;
while ($db_field = mysqli_fetch_assoc($result)) {
	$award_id = $db_field['award_id'];
	$awardSQL = "SELECT name, image FROM tali_personnel_awards WHERE award_id=$award_id";
	$awardresult = mysqli_query($db_handle, $awardSQL);
	$award_db_field = mysqli_fetch_assoc($awardresult);
	echo "
			<tr>
				<th>".date("m/d/Y", strtotime($db_field['date_awarded']))."</th>
				<td align=\"left\" style=\"padding:5px;\">".$award_db_field['name']."</td>
				<td><img src=\"".TALI_DOMAIN_URL."".TALI_TALISUPPLEMENT_URI."/personnel/awards/".$award_db_field['image']."\" alt=\"Award\"></img></td>
				<td align=\"left\" style=\"padding:5px;\">".$db_field['record']."</td>
			</tr>
	";
}

echo "
		</table>
	</div>
";
?>