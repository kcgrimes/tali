<?php
//Stock variables
$module = "TALI_Personnel";

//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}

TALI_sessionCheck($module, $db_handle);

//Determine which "submit" button was pressed

echo "
	<div class=\"tali-page-frame\">
		<h1>Analysis Results</h1>
";

//bug - SQL date ranges aren't being inclusive (repro by running with blank
//date fields on date which has drill report, and see difference against overall results
if (isset($_POST['indiv_subbutton'])) {
	//Individual Analysis
	if ((isset($_POST['selectindiv'])) && (($_POST['selectindiv']) != "empty")) {
		$indiv_personnel_id = $_POST['selectindiv'];
	}
	else
	{
		header ("Location: personnel.php?sub=metrics");
		exit();
	}
	
	$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$indiv_personnel_id";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);

	echo "
		<p><strong>".$db_field['abbreviation']." ".$db_field['firstname']." ".$db_field['lastname']."</strong></p>
	";

	if ((isset($_POST['indiv_analysis_start'])) && (($_POST['indiv_analysis_start']) != "")) {
		$indiv_analysis_start = date("Y-m-d", strtotime($_POST['indiv_analysis_start']));
	}
	else
	{
		$indiv_analysis_start = date("Y-m-d");
	};
	if ((isset($_POST['indiv_analysis_end'])) && (($_POST['indiv_analysis_end']) != "")) {
		$indiv_analysis_end = date("Y-m-d", strtotime($_POST['indiv_analysis_end']));
	}
	else
	{
		//Since forever
		$indiv_analysis_end = date("Y-m-d", strtotime("01/01/1970"));
	};
	
	$indiv_analysis_start_sql = htmlspecialchars($indiv_analysis_start);
	$indiv_analysis_start_sql = TALI_quote_smart($indiv_analysis_start_sql, $db_handle);
	
	$indiv_analysis_end_sql = htmlspecialchars($indiv_analysis_end);
	$indiv_analysis_end_sql = TALI_quote_smart($indiv_analysis_end_sql, $db_handle);
	
	if ((isset($_POST['indiv_technique_array'])) && (($_POST['indiv_technique_array']) != "")) {
		$indiv_technique_array = $_POST['indiv_technique_array'];
	}
	else
	{
		header ("Location: personnel.php?sub=metrics");
		exit();
	}
	
	if (in_array("attendanceanalysis",$indiv_technique_array)) {
		//Date-to-Date Attendance Analysis
		$SQL = "SELECT * FROM tali_personnel_drillreports WHERE ((`attended` LIKE '%{$indiv_personnel_id}%') OR (`excused` LIKE '%{$indiv_personnel_id}%') OR (`absent` LIKE '%{$indiv_personnel_id}%')) AND (date_drill BETWEEN $indiv_analysis_end_sql AND $indiv_analysis_start_sql) ORDER BY date_drill ASC";
		$result = mysqli_query($db_handle, $SQL);
		$num_rows = mysqli_num_rows($result);
		$drills_attended = 0;
		$drills_excused = 0;
		$drills_absent = 0;
		while ($db_field = mysqli_fetch_assoc($result)) {
			$attended = explode(",", $db_field['attended']);
			if (in_array($indiv_personnel_id, $attended)) {
				$drills_attended++;
			}
			else
			{
				$excused = explode(",", $db_field['excused']);
				if (in_array($indiv_personnel_id, $excused)) {
					$drills_excused++;
				}
				else
				{
					$drills_absent++;
				}
			}
		}
		
		$drills_missed= $drills_excused + $drills_absent;
		$drills_total = $drills_attended + $drills_excused + $drills_absent;
		//bug - make this all a function!
		if ($drills_total > 0) {
			$drills_attended_perc = round($drills_attended / $drills_total * 100);
			$drills_missed_perc = round($drills_missed / $drills_total * 100);
			$drills_excused_perc = round($drills_excused / $drills_total * 100);
			$drills_absent_perc = round($drills_absent / $drills_total * 100);
		}
		else
		{
			$drills_attended_perc = 0;
			$drills_missed_perc = 0;
			$drills_excused_perc = 0;
			$drills_absent_perc = 0;
		};
		
		if ($drills_missed > 0) {
			$drills_excusedofmissed_perc = round($drills_excused / $drills_missed * 100);
			$drills_absentofmissed_perc = round($drills_absent / $drills_missed * 100);
		}
		else
		{
			$drills_excusedofmissed_perc = 0;
			$drills_absentofmissed_perc = 0;
		}
		//bug - make this a table!
		echo "
		<p><strong>".date("m/d/Y", strtotime($indiv_analysis_end))." to ".date("m/d/Y", strtotime($indiv_analysis_start))."</strong>
		<br/>
		Total Drills Accounted For: $drills_total
		<br/>
		Drills Attended: $drills_attended ($drills_attended_perc% of total)
		<br/>
		Drills Missed: $drills_missed ($drills_missed_perc% of total)
		<br/>
		Drills Excused: $drills_excused ($drills_excused_perc% of total, $drills_excusedofmissed_perc% of missed)
		<br/>
		Drills Absent: $drills_absent ($drills_absent_perc% of total, $drills_absentofmissed_perc% of missed)
		<br/>
		</p>
		";
		
		//Overall Attendance Analysis
		$SQL = "SELECT * FROM tali_personnel_roster WHERE personnel_id=$indiv_personnel_id";
		$result = mysqli_query($db_handle, $SQL);
		$db_field = mysqli_fetch_assoc($result);
		$drills_attended=$db_field['drills_attended'];
		$drills_excused=$db_field['drills_excused'];
		$drills_absent=$db_field['drills_absent'];
		$drills_missed= $drills_excused + $drills_absent;
		$drills_total = $drills_attended + $drills_excused + $drills_absent;
		
		if ($drills_total > 0) {
			$drills_attended_perc = round($drills_attended / $drills_total * 100);
			$drills_missed_perc = round($drills_missed / $drills_total * 100);
			$drills_excused_perc = round($drills_excused / $drills_total * 100);
			$drills_absent_perc = round($drills_absent / $drills_total * 100);
		}
		else
		{
			$drills_attended_perc = 0;
			$drills_missed_perc = 0;
			$drills_excused_perc = 0;
			$drills_absent_perc = 0;
		};
		
		if ($drills_missed > 0) {
			$drills_excusedofmissed_perc = round($drills_excused / $drills_missed * 100);
			$drills_absentofmissed_perc = round($drills_absent / $drills_missed * 100);
		}
		else
		{
			$drills_excusedofmissed_perc = 0;
			$drills_absentofmissed_perc = 0;
		}
		
		echo "
		<p><strong>Overall</strong>
		<br/>
		Total Drills Accounted For: $drills_total
		<br/>
		Drills Attended: $drills_attended ($drills_attended_perc% of total)
		<br/>
		Drills Missed: $drills_missed ($drills_missed_perc% of total)
		<br/>
		Drills Excused: $drills_excused ($drills_excused_perc% of total, $drills_excusedofmissed_perc% of missed)
		<br/>
		Drills Absent: $drills_absent ($drills_absent_perc% of total, $drills_absentofmissed_perc% of missed)
		<br/>
		</p>
		";
	}
}
if (isset($_POST['desig_subbutton'])) {
	//Designation Analysis
	if ((isset($_POST['designation_select'])) && (($_POST['designation_select']) != "")) {
		$designation_id = $_POST['designation_select'];
	}
	else
	{
		header ("Location: personnel.php?sub=metrics");
		exit();
	}
	
	//Obtain information about the designation from the database
	$SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$designation_id";
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

	echo "
		<p><strong>".$full_desig_name."</strong></p>
	";

	if ((isset($_POST['desig_analysis_start'])) && (($_POST['desig_analysis_start']) != "")) {
		$desig_analysis_start = date("Y-m-d", strtotime($_POST['desig_analysis_start']));
	}
	else
	{
		$desig_analysis_start = date("Y-m-d");
	};
	if ((isset($_POST['desig_analysis_end'])) && (($_POST['desig_analysis_end']) != "")) {
		$desig_analysis_end = date("Y-m-d", strtotime($_POST['desig_analysis_end']));
	}
	else
	{
		//Since forever
		$desig_analysis_end = date("Y-m-d", strtotime("01/01/1970"));
	};
	
	$desig_analysis_start_sql = htmlspecialchars($desig_analysis_start);
	$desig_analysis_start_sql = TALI_quote_smart($desig_analysis_start_sql, $db_handle);
	
	$desig_analysis_end_sql = htmlspecialchars($desig_analysis_end);
	$desig_analysis_end_sql = TALI_quote_smart($desig_analysis_end_sql, $db_handle);
	
	if ((isset($_POST['desig_technique_array'])) && (($_POST['desig_technique_array']) != "")) {
		$desig_technique_array = $_POST['desig_technique_array'];
	}
	else
	{
		header ("Location: personnel.php?sub=metrics");
		exit();
	}
	
	if (in_array("attendanceanalysis",$desig_technique_array)) {
		//Date-to-Date Attendance Analysis
		$SQL = "SELECT * FROM tali_personnel_drillreports WHERE designation_id=$designation_id AND (date_drill BETWEEN $desig_analysis_end_sql AND $desig_analysis_start_sql) ORDER BY date_drill ASC";
		$result = mysqli_query($db_handle, $SQL);
		$num_DrillReports = mysqli_num_rows($result);
		if ($num_DrillReports < 1) {
			header ("Location: personnel.php?sub=metrics");
			exit();
		}
		$attended_total = 0;
		$excused_total = 0;
		$absent_total = 0;
		while ($db_field = mysqli_fetch_assoc($result)) {
			$attended = count(explode(",", $db_field['attended']));
			$excused = count(explode(",", $db_field['excused']));
			$absent = count(explode(",", $db_field['absent']));
			$total = $attended + $excused + $absent;
			$attended_total = $attended_total + ($attended/$total);
			$excused_total = $excused_total + ($excused/$total);
			$absent_total = $absent_total + ($absent/$total);
		}
		
		$attended_avg = round($attended_total / $num_DrillReports * 100);
		$excused_avg = round($excused_total / $num_DrillReports * 100);
		$absent_avg = round($absent_total / $num_DrillReports * 100);
		
		//bug - make this a table!
		echo "
		<p><strong>".date("m/d/Y", strtotime($desig_analysis_end))." to ".date("m/d/Y", strtotime($desig_analysis_start))."</strong>
		<br/>
		Total Drills Accounted For: $num_DrillReports
		<br/>
		Drills Attendance Average: $attended_avg%
		<br/>
		Drills Excused Average: $excused_avg%
		<br/>
		Drills Absent Average: $absent_avg%
		<br/>
		</p>
		";
		
		//Overall Attendance Analysis
		$SQL = "SELECT * FROM tali_personnel_drillreports WHERE designation_id=$designation_id ORDER BY date_drill ASC";
		$result = mysqli_query($db_handle, $SQL);
		$num_DrillReports = mysqli_num_rows($result);
		if ($num_DrillReports < 1) {
			header ("Location: personnel.php?sub=metrics");
			exit();
		}
		$attended_total = 0;
		$excused_total = 0;
		$absent_total = 0;
		while ($db_field = mysqli_fetch_assoc($result)) {
			$attended = count(explode(",", $db_field['attended']));
			$excused = count(explode(",", $db_field['excused']));
			$absent = count(explode(",", $db_field['absent']));
			$total = $attended + $excused + $absent;
			$attended_total = $attended_total + ($attended/$total);
			$excused_total = $excused_total + ($excused/$total);
			$absent_total = $absent_total + ($absent/$total);
		}
		
		$attended_avg = round($attended_total / $num_DrillReports * 100);
		$excused_avg = round($excused_total / $num_DrillReports * 100);
		$absent_avg = round($absent_total / $num_DrillReports * 100);
		
		//bug - make this a table!
		echo "
		<p><strong>Overall</strong>
		<br/>
		Total Drills Accounted For: $num_DrillReports
		<br/>
		Drills Attendance Average: $attended_avg%
		<br/>
		Drills Excused Average: $excused_avg%
		<br/>
		Drills Absent Average: $absent_avg%
		<br/>
		</p>
		";
	}
}
echo "
	</div>
";
?>