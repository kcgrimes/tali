<?php
//Stock variables
$module = "TALI_Personnel";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database not found.");
}
	
TALI_sessionCheck($module, $db_handle);

echo "
	<main>
		<div class=\"tali-container\">
			<div class=\"tali-page-frame\">
				<h1>Personnel Metrics</h1>
				<p>This page allows for analysis of attendance records per individual or unit.</p>
			</div>
";	

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//POST
	require "metrics-post.php";
}
else
{
	//Not POST
	//Individual Analysis
	echo "
			<div class=\"tali-page-frame\">
				<h1>Individual Analysis</h1>
				<p>Select an individual from the dropdown:</p>
	";
	
	//bug - how to incorporate past members?
	$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE discharged=0 ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
	$result = mysqli_query($db_handle, $SQL);

	$arrayPersonnel_json = array();
	//Array of everyone in unit at the time
	while ($db_field = mysqli_fetch_assoc($result)) {
		$rank_id=$db_field['rank_id'];
		$rankSQL = "SELECT abbreviation FROM tali_personnel_ranks WHERE rank_id=$rank_id";
		$rankresult = mysqli_query($db_handle, $rankSQL);
		$rank_db_field = mysqli_fetch_assoc($rankresult);
		
		//bug - need to have desigs or no?
		$arrayPersonnel_json[] = array("personnel_id" => $db_field['personnel_id'],"rank_abr" => $rank_db_field['abbreviation'], "firstname" => $db_field['firstname'], "lastname" => $db_field['lastname'], "designation_id" => $db_field['designation_id']);
	};

	echo "
				<p><select name=\"selectindiv\" form=\"indiv_analysis\">
					<option value=\"empty\">Select an Individual</option>
	";
	
	foreach ($arrayPersonnel_json as $i) {
		echo "
					<option value=\"".$i['personnel_id']."\">".$i['rank_abr']." ".$i['firstname']." ".$i['lastname']."</option>
		";
	};

	echo "
				</select>
				<br/>
				<br/>
				Enter a date to analyze from (MM/DD/YYYY):
				<input type=\"text\" class=\"tali_personnel_metrics_textbox\" name=\"indiv_analysis_end\" form=\"indiv_analysis\" maxlength=\"10\" value=\"\">
				<br/>
				Enter a date to analyze up to (MM/DD/YYYY):
				<input type=\"text\" class=\"tali_personnel_metrics_textbox\" name=\"indiv_analysis_start\" form=\"indiv_analysis\" maxlength=\"10\" value=\"\">
				<br/>
				Select analysis techniques:
				<form method=\"POST\" id=\"indiv_analysis\" action=\"personnel.php?sub=metrics&action=post\">
					<p>
					<input type=\"checkbox\" name=\"indiv_technique_array[]\" checked=\"checked\" value=\"attendanceanalysis\"/>
					Attendance Analysis
					<br/>
					<br/>
					<input type=\"submit\" name=\"indiv_subbutton\" class=\"tali_personnel_metrics_subbutton\" value=\"Conduct Analysis\"/>
					</p>
				</form>
			</div>
	"; 
	
	//Designation Analysis
	echo "
			<div class=\"tali-page-frame\">
				<h1>Designation Analysis</h1>
				<p>Select a designation from the dropdown:</p>
	";
	
	//Prepare a dropdown of designations for selection/action
	echo "					
				<p><select form=\"desig_analysis\" class=\"desigSelect_report\" id=\"designation_select\" name=\"designation_select\">
					<option value=\"\" selected>Select a Designation</option>
	";
	
	//Select all designations, including those that are inactive
	$SQL = "SELECT * FROM tali_personnel_designations ORDER BY weight DESC";
	$result = mysqli_query($db_handle, $SQL);
	
	//Store designation data collected from above query in array
	$arrayDesignation = [];
	while ($db_field = mysqli_fetch_assoc($result)) {
		$arrayDesignation[] = ["designation_id" => $db_field["designation_id"], "reportsto_designation_id" => $db_field['reportsto_designation_id']];
	}
	
	//Create function that will print each row in the table
	//$db_handle carried in due to needing to query inside function
	//First parameter is the designation (with array of data) to be printed
	function designationRow ($db_handle, $arrayDesignation_selected) {
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
		
		//List designations as options
		echo "
					<option value=\"$designation_id\">$full_desig_name</option>
		";
	}
	
	//The below function and forEach are used together to correctly list designations
	//based on weight and chain of command, similar to how it's done on the front Roster
	function designationsFill ($db_handle, $arrayDesignation, $designation) {
		foreach ($arrayDesignation as $designation_reportto) {
			if ($designation['designation_id'] == $designation_reportto['reportsto_designation_id']) {
				designationRow ($db_handle, $designation_reportto);
									
				designationsFill ($db_handle, $arrayDesignation, $designation_reportto);
			}
		}
	}
	
	foreach ($arrayDesignation as $designation) {
		if ($designation['reportsto_designation_id'] == 0) {
			designationRow ($db_handle, $designation);
							
			designationsFill ($db_handle, $arrayDesignation, $designation);
		}
	}
	
	echo "
				</select>
	";
		
	//End designation stuff
	
	echo "
				<br/>
				<br/>
				Enter a date to analyze from (MM/DD/YYYY):
				<input type=\"text\" class=\"tali_personnel_metrics_textbox\" name=\"desig_analysis_end\" form=\"desig_analysis\" maxlength=\"10\" value=\"\">
				<br/>
				Enter a date to analyze up to (MM/DD/YYYY):
				<input type=\"text\" class=\"tali_personnel_metrics_textbox\" name=\"desig_analysis_start\" form=\"desig_analysis\" maxlength=\"10\" value=\"\">
				<br/>
				Select analysis techniques:
				<form method=\"POST\" id=\"desig_analysis\" action=\"personnel.php?sub=metrics&action=post\">
					<p>
					<input type=\"checkbox\" name=\"desig_technique_array[]\" checked=\"checked\" value=\"attendanceanalysis\"/>
					Attendance Analysis
					<br/>
					<br/>
					<input type=\"submit\" name=\"desig_subbutton\" class=\"tali_personnel_metrics_subbutton\" value=\"Conduct Analysis\"/>
					</p>
				</form>
			</div>
		
			<div class=\"tali-page-frame\">
				<h1>Miscellaneous Personnel Metrics</h1>
				<p>
	";
		
	//Median Rank
	$SQL = "SELECT tali_personnel_roster.rank_id FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE discharged=0 ORDER BY tali_personnel_ranks.weight DESC";
	$result = mysqli_query($db_handle, $SQL);
	
	$ranks_array = [];
	while ($db_field = mysqli_fetch_assoc($result)) {
		$ranks_array[] = $db_field['rank_id'];
	}
	
	$med_rank_id = $ranks_array[round(count($ranks_array)/2)];
	$SQL = "SELECT name FROM tali_personnel_ranks WHERE rank_id = $med_rank_id";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);
	echo "
				Active Duty Median Rank: ".$db_field['name']."
				<br/>
	";
	//Average Time in Grade
	$SQL = "SELECT date_promoted, date_discharged FROM tali_personnel_roster";
	$result = mysqli_query($db_handle, $SQL);
	
	$times_array = [];
	while ($db_field = mysqli_fetch_assoc($result)) {
		$start_date = strtotime($db_field['date_promoted']);
		if (is_null($db_field['date_discharged'])) {
			$end_date = strtotime(date("Y-m-d"));
		}
		else
		{
			$end_date = strtotime($db_field['date_discharged']);
		}
		$date_diff = $end_date - $start_date;
		$times_array[] = $date_diff;
	}
		
	$avg_time_in_grade = round(array_sum($times_array) / count ($times_array));
	//bug - I want to convert seconds to years like done in Profile, but can't without date object?
	//$avg_time_in_grade->format('%y years, %m months, %d days');
	//Convert seconds to years in a dirty way
	$avg_time_in_grade = round($avg_time_in_grade / 60 / 60 / 24 / 365, 2);
	
	echo "
				Average Time in Grade: $avg_time_in_grade years
				<br/>
	";
	//Median Time in Grade
	sort($times_array);
	$med_time_in_grade = $times_array[round(count($times_array)/2)];
	//bug - I want to convert seconds to years like done in Profile, but can't without date object?
	//Convert seconds to years in a dirty way
	$med_time_in_grade = round($med_time_in_grade / 60 / 60 / 24 / 365, 2);
	
	echo "
				Median Time in Grade: $med_time_in_grade years
				<br/>
	";
	
	//Average Time in Service
	$SQL = "SELECT date_enlisted, date_discharged FROM tali_personnel_roster";
	$result = mysqli_query($db_handle, $SQL);
	
	$times_array = [];
	while ($db_field = mysqli_fetch_assoc($result)) {
		$start_date = strtotime($db_field['date_enlisted']);
		if (is_null($db_field['date_discharged'])) {
			$end_date = strtotime(date("Y-m-d"));
		}
		else
		{
			$end_date = strtotime($db_field['date_discharged']);
		}
		$date_diff = $end_date - $start_date;
		$times_array[] = $date_diff;
	}
		
	$avg_time_in_service = round(array_sum($times_array) / count ($times_array));
	//bug - I want to convert seconds to years like done in Profile, but can't without date object?
	//$avg_time_in_service->format('%y years, %m months, %d days');
	//Convert seconds to years in a dirty way
	$avg_time_in_service = round($avg_time_in_service / 60 / 60 / 24 / 365, 2);
	
	echo "
				Average Time in Service: $avg_time_in_service years
				<br/>
	";
	//Median Time in Service
	sort($times_array);
	$med_time_in_service = $times_array[round(count($times_array)/2)];
	//bug - I want to convert seconds to years like done in Profile, but can't without date object?
	//Convert seconds to years in a dirty way
	$med_time_in_service = round($med_time_in_service / 60 / 60 / 24 / 365, 2);
	
	echo "
				Median Time in Service: $med_time_in_service years
				<br/>
	";

	echo "
				</p>
			</div>
	";
}
echo "
		</div>
	</main>
";
?>