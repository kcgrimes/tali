<?php
//Stock variables
$module = "TALI_Personnel";
$form_dateofdrill = "";
$form_dateofreport = "";
$form_designation_id = "";							
$form_report_comments = "";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

//MarkitUp
markItUp_editing ();

if (!isset($_GET['location'])) {
	//Fresh page - Drill Reports launch pad to Create or View
	echo "
		<main>
			<div class=\"tali-container\">
				<div class=\"tali-page-frame\">
					<h1>Manage Drill Reports</h1>
					<p>This page allows for the management of the unit's drill reports.</p>
				</div>

				<div class=\"tali-page-frame\">
					<div class=\"tali-responsive-row\">
						<a href=\"personnel.php?sub=drillreports&location=create\" class=\"tali-responsive-icon\">
							<img src=\"../images/icons/DrillReports-New.png\" alt=\"Create New Drill Report Icon\" name=\"Create New Drill Report Icon\">
							<p>Create New Drill Report</p>
						</a>
						<a href=\"personnel.php?sub=drillreports&location=view\" class=\"tali-responsive-icon\">
							<img src=\"../images/icons/DrillReports-View.png\" alt=\"View/Edit Past Drill Reports Icon\" name=\"View Past Drill Reports Icon\">
							<p>View/Edit Past Drill Reports</p>
						</a>
					</div>
				</div>
			</div>
		</main>
	";
}
else
{
	//GET location is set, so Viewing or Creating
	switch ($_GET['location']) {
		case "view":
			//Viewing archived Drill Reports
			//bug - this needs pagination
			echo "
				<main>
					<div class=\"tali-container\">
						<div class=\"tali-page-frame\">
							<h1>View Drill Reports</h1>
							<p>This page allows you to view all previous Drill Reports and individually edit a selected report.</p>
						</div>
						
						<div class=\"tali-page-frame\">
							<h1>View Drill Reports</h1>
			";
			
			//Select Designation
				
			//Prepare a dropdown of designations for selection/action
			echo "
							<p>Select the desired designation to display past drill reports:</p>
							<p>
							<select form=\"create_drillreport\" class=\"desigSelect_report\" id=\"designation_select\" name=\"designation_select\">
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
							</p>
			";
				
			//End designation stuff
			
			echo "
							<br/>
			";
			
			echo "						
							<table id=\"drillreportsTable\" class=\"tali-personnel-table\">
								<col width=\"50%\">
								<col width=\"40%\">
								<col width=\"10%\">

								<tr>
									<th>Designation</th>
									<th>Drill Date</th>
									<th>Edit</th>
								</tr>
								
								<tr>
									<td style=\"text-align:center;\">No Designation Selected</td>
									<td style=\"text-align:center;\">No Designation Selected</td>
									<td style=\"text-align:center;\">No Designation Selected</td>
								</tr>
			";
			
			//Create drill reports array in JSON format
			$SQL = "SELECT * FROM tali_personnel_drillreports JOIN tali_personnel_designations on tali_personnel_drillreports.designation_id = tali_personnel_designations.designation_id ORDER BY date_drill DESC";
			$result = mysqli_query($db_handle, $SQL);
			$arrayDrillReports_json = array();
			while ($db_field = mysqli_fetch_assoc($result)) {
				$arrayDrillReports_json[] = array("drillreport_id" => $db_field['drillreport_id'],"designation_id" => $db_field['designation_id'], "name" => $db_field['name'], "date_drill" => $db_field['date_drill']);
			};
			
			$arrayDrillReports_json = json_encode($arrayDrillReports_json);
			
			//Javascript for dynamic drill report archive display
			?>
				<script>
					$(function() {
						var i;
						var arrayDrillReports_json = <?php echo $arrayDrillReports_json; ?>;
						$('.desigSelect_report').change(function() {
							drillreportsTable_jq = $("#drillreportsTable");
							drillreportsTable_jq.empty();
							drillreportsTable_jq.append(
								'<col width="50%"><col width="40%"><col width="10%"><tr><th>Designation</th><th>Drill Date</th><th>Edit</th></tr>'
							);
							
							//this.id = the 'select' id, which is designation_weight_id
							var e = document.getElementById(this.id);
							var chosenDesignation_id = e.options[e.selectedIndex].value;
							
							for (i = 0; i < arrayDrillReports_json.length; i++) {
								if (arrayDrillReports_json[i].designation_id == chosenDesignation_id) {
									drillreportsTable_jq.append(
										'<tr><td style="text-align:center;">' + arrayDrillReports_json[i].name + '</td><td style="text-align:center;">' + arrayDrillReports_json[i].date_drill + '</td><td style="text-align:center;"><a href="personnel.php?sub=drillreports&location=create&id=' + arrayDrillReports_json[i].drillreport_id + '"><img src="../images/icons/edit.png" alt="Edit Icon" name="Edit Icon"></a></td></tr>'
									);
								};
							};
						});
					}); 
				</script>
			<?php
			
			echo "
							</table>
						</div>
					</div>
				</main>
			";
		break;
		case "create":
			//Adding new, submitting new, or editing of Drill Report
			if (isset($_GET['action'])) {
				//Attempting to submit New/Edited Drill Report
				//If GET id is set, than this is an Edit submission
				if (isset($_GET['id'])) {
					$drillreport_id = $_GET['id'];
				}
				
				//Manage Date of Drill
				if ((isset($_POST['dateofdrill'])) && (($_POST['dateofdrill']) != "")) {
					//Date was defined, so convert to database format
					$dateofdrill_str = date("Y-m-d", strtotime($_POST['dateofdrill']));
				}
				else
				{
					//Date was not defined, so use today's date
					$dateofdrill_str = date("Y-m-d");
				};
				
				$dateofdrill_sql = htmlspecialchars($dateofdrill_str);
				$dateofdrill_sql = TALI_quote_smart($dateofdrill_sql, $db_handle);
				
				//Date of initial report
				//For new - Today's date, defined in the form already
				//For edit - Unchangeable
				$dateofreport_sql = date("Y-m-d", strtotime($_POST['dateofreport']));
				$dateofreport_sql = htmlspecialchars($dateofreport_sql);
				$dateofreport_sql = TALI_quote_smart($dateofreport_sql, $db_handle);
				
				//Designation
				if (isset($_GET['id'])) {
					//Editing, so use existing designation id
					$SQL = "SELECT designation_id FROM tali_personnel_drillreports WHERE drillreport_id=$drillreport_id";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					$designation_id = $db_field['designation_id'];
				}
				else
				{
					//Adding new, so use newly selected designation
					$designation_id = $_POST['designation_select'];
				}
				
				//Managing Attendance
				
				if (isset($_GET['id'])) {
					//Editing, so reset previous attendance counts
					$SQL = "SELECT attended, excused, absent FROM tali_personnel_drillreports WHERE drillreport_id=$drillreport_id";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					
					$attended=$db_field['attended'];
					$excused=$db_field['excused'];
					$absent=$db_field['absent'];
					
					$attended = explode(",", $attended);
					forEach ($attended as $personnel_id) {
						$SQL = "UPDATE tali_personnel_roster SET drills_attended=drills_attended - 1 WHERE personnel_id=$personnel_id"; 
						$result = mysqli_query($db_handle, $SQL);
					}
					$excused = explode(",", $excused);
					forEach ($excused as $personnel_id) {
						$SQL = "UPDATE tali_personnel_roster SET drills_excused=drills_excused - 1 WHERE personnel_id=$personnel_id"; 
						$result = mysqli_query($db_handle, $SQL);
					}
					$absent = explode(",", $absent);
					forEach ($absent as $personnel_id) {
						$SQL = "UPDATE tali_personnel_roster SET drills_absent=drills_absent - 1 WHERE personnel_id=$personnel_id"; 
						$result = mysqli_query($db_handle, $SQL);
					}
				}
				
				//Attended
				
				if (isset($_POST['attendedcheckbox'])) {
					$attended = implode(",", $_POST['attendedcheckbox']);
				}
				else
				{
					$attended = "";
				};
				
				$attended_sql = htmlspecialchars($attended);
				$attended_sql = TALI_quote_smart($attended_sql, $db_handle);
				
				//Excused
				if (isset($_POST['excusedcheckbox'])) {
					$excused = implode(",", $_POST['excusedcheckbox']);
				}
				else
				{
					$excused = "";
				};
				
				$excused_sql = htmlspecialchars($excused);
				$excused_sql = TALI_quote_smart($excused_sql, $db_handle);
				
				//Absent
				if (isset($_POST['designationmembers'])) {
					if ((isset($_POST['attendedcheckbox'])) && (isset($_POST['excusedcheckbox']))) {
						$absent = implode(",", array_diff($_POST['designationmembers'], $_POST['attendedcheckbox'], $_POST['excusedcheckbox']));
					}
					else
					{
						$absent = implode(",", $_POST['designationmembers']);
						if (isset($_POST['excusedcheckbox'])) {
							$absent = implode(",", array_diff($_POST['designationmembers'], $_POST['excusedcheckbox']));
						};
						if (isset($_POST['attendedcheckbox'])) {
							$absent = implode(",", array_diff($_POST['designationmembers'], $_POST['attendedcheckbox']));
						};
					};
				}
				else
				{
					$absent = "";
				};
				
				$absent_sql = htmlspecialchars($absent);
				$absent_sql = TALI_quote_smart($absent_sql, $db_handle);
				
				if (isset($_POST['report_comments'])) {$report_comments = $_POST['report_comments'];}else{$report_comments = "";};
				
				$report_comments = htmlspecialchars($report_comments);
				$report_comments_sql = TALI_quote_smart($report_comments, $db_handle);
				
				if (isset($_GET['id'])) {
					//Editing, so UPDATE database
					$SQL = "UPDATE tali_personnel_drillreports SET date_drill=$dateofdrill_sql,attended=$attended_sql,excused=$excused_sql,absent=$absent_sql,comments=$report_comments_sql WHERE drillreport_id=$drillreport_id"; 									
					$result = mysqli_query($db_handle, $SQL);
				}
				else
				{
					//Adding, so INSERT to database
					$SQL = "INSERT INTO tali_personnel_drillreports (designation_id, date_drill, date_report, attended, excused, absent, comments) VALUES ($designation_id, $dateofdrill_sql, $dateofreport_sql, $attended_sql, $excused_sql, $absent_sql, $report_comments_sql)";						
					$result = mysqli_query($db_handle, $SQL);
				}
				
				//Manage attendance statistics in personnel table (same for editing and adding, since edit involved a reset)
				
				$attended = explode(",", $attended);
				forEach ($attended as $personnel_id) {
					$SQL = "UPDATE tali_personnel_roster SET drills_attended=drills_attended + 1 WHERE personnel_id=$personnel_id"; 
					$result = mysqli_query($db_handle, $SQL);
				}
				$excused = explode(",", $excused);
				forEach ($excused as $personnel_id) {
					$SQL = "UPDATE tali_personnel_roster SET drills_excused=drills_excused + 1 WHERE personnel_id=$personnel_id"; 
					$result = mysqli_query($db_handle, $SQL);
				}
				$absent = explode(",", $absent);
				forEach ($absent as $personnel_id) {
					$SQL = "UPDATE tali_personnel_roster SET drills_absent=drills_absent + 1 WHERE personnel_id=$personnel_id"; 
					$result = mysqli_query($db_handle, $SQL);
				}
				
				if (!isset($_GET['id'])) {
					//Get drillreport_id from new report
					$SQL = "SELECT drillreport_id FROM tali_personnel_drillreports ORDER BY drillreport_id DESC LIMIT 1";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					$drillreport_id=$db_field['drillreport_id'];
				}
				
				//bug - 3rd ID SMF create topic
				//Only perform in published environment (no forums in dev)
				if (TALI_PLATFORM == "live") {
					//Define variables
					$SQL = "SELECT name FROM tali_personnel_designations WHERE designation_id=$designation_id";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					$designation_name=$db_field['name'];

					$dateofdrill_str = date("m/d/Y", strtotime($dateofdrill_str));						
					
					//msgOptions
					$smf_subject = "$designation_name Drill Report $dateofdrill_str";
					
					//Handle & escape
					$smf_subject = htmlspecialchars($smf_subject);
					
					//BBcode of forum realizes all spaces
					if (!empty($attended[0])) {
						$attended_array = [];
						forEach ($attended as $personnel_id) {
							$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
							$result = mysqli_query($db_handle, $SQL);
							$db_field = mysqli_fetch_assoc($result);
							$attended_array[] = "".$db_field['abbreviation']." ".$db_field['lastname']."";
						}
					}
					else
					{
						$attended_array[] = "N/A";
					}
					
					if (!empty($excused[0])) {
						$excused_array = [];
						forEach ($excused as $personnel_id) {
							$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
							$result = mysqli_query($db_handle, $SQL);
							$db_field = mysqli_fetch_assoc($result);
							$excused_array[] = "".$db_field['abbreviation']." ".$db_field['lastname']."";
						}
					}
					else
					{
						$excused_array[] = "N/A";
					}
					
					if (!empty($absent[0])) {
						$absent_array = [];
						forEach ($absent as $personnel_id) {
							$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
							$result = mysqli_query($db_handle, $SQL);
							$db_field = mysqli_fetch_assoc($result);
							$absent_array[] = "".$db_field['abbreviation']." ".$db_field['lastname']."";
						}
					}
					else
					{
						$absent_array[] = "N/A";
					}
					
					
					$smf_body = 
"$designation_name Drill Report for $dateofdrill_str

[color=white][u][b]Attendance[/b][/u][/color]
[color=green]".implode("[br]", $attended_array)."[/color]

[color=white][u][b]Excused[/b][/u][/color]
[color=orange]".implode("[br]", $excused_array)."[/color]

[color=white][u][b]Unexcused[/b][/u][/color]
[color=red]".implode("[br]", $absent_array)."[/color]

[color=white][u][b]Comments[/b][/u][/color]
$report_comments

Completed on ".date("m/d/Y")."";
					
					//Handle & escape
					$smf_body = htmlspecialchars($smf_body);
					
					if (!isset($_GET['id'])) {
						//Adding new drill report, so make new post
						//topicOptions
						if ($designation_name == "Alpha Squad") {
							$smf_board = 54; //Alpha Squad
						}
						else if ($designation_name == "Bravo Squad") {
							$smf_board = 55; //Bravo Squad
						}
						else if ($designation_name == "Training Section") {
							$smf_board = 60; //Training Section
						}
						else
						{
							$smf_board = 2; //Barracks, overall backup
						}
						
						//bug - add training section/make dynamic-er
						
						//posterOptions
						//Use name of TALI Account to find SMF ID (because account names are same for 3rd ID)
						$tali_username = $_SESSION['username'];
						$tali_username = TALI_quote_smart($tali_username, $db_handle);
						$SQL = "SELECT id_member FROM smf_members WHERE member_name=$tali_username";
						$result = mysqli_query($db_handle, $SQL);
						$db_field = mysqli_fetch_assoc($result);
						
						$smf_id = $db_field['id_member']; 
						if ($smf_id == false) {
							$SQL = "SELECT id_member FROM smf_members WHERE real_name=$tali_username";
							$result = mysqli_query($db_handle, $SQL);
							$db_field = mysqli_fetch_assoc($result);
							$smf_id = $db_field['id_member']; 
						}
						
						//SMF Post function
						require_once('../../forums/SSI.php');
						require_once('../../forums/Sources/Subs-Post.php');
						
						//createPost($msgOptions, $topicOptions, $posterOptions);
						// Create a post, either as new topic (id_topic = 0) or in an existing one.
						// The input parameters of this function assume:
						// - Strings have been escaped.
						// - Integers have been cast to integer.
						// - Mandatory parameters are set.
						
						// Collect all parameters for the creation or modification of a post.
						$msgOptions = array(
							'subject' => $smf_subject,
							'body' => $smf_body,
						);
						$topicOptions = array(
							'board' => $smf_board,
							'mark_as_read' => true,
						);
						$posterOptions = array(
							'id' => $smf_id,
						);
						
						//Excute SMF post
						createPost($msgOptions, $topicOptions, $posterOptions);
						
						//Set topic ID and message ID of drill report post that was just made so it can be edited later
						$insert = "'".$topicOptions['id'].",".$msgOptions['id']."'";

						$SQL = "UPDATE tali_personnel_drillreports SET smf_ids=$insert WHERE drillreport_id=$drillreport_id"; 
						$result = mysqli_query($db_handle, $SQL);
					}
					else
					{
						//Editing drill report, so edit previously made post
						//topicOptions
						//Get topic and message IDs from original report
						$SQL = "SELECT smf_ids FROM tali_personnel_drillreports WHERE drillreport_id=$drillreport_id";
						$result = mysqli_query($db_handle, $SQL);
						$db_field = mysqli_fetch_assoc($result);
						$smf_ids = $db_field['smf_ids'];
						$smf_ids = explode(",", $smf_ids);
						$smf_topic_id = $smf_ids[0];
						$smf_message_id = $smf_ids[1];
						
						//bug - add training section/make dynamic-er
						
						//posterOptions
						//Use name of TALI Account to find SMF ID (because account names are same for 3rd ID)
						$tali_username = $_SESSION['username'];
						
						//SMF Post function
						require_once('../../forums/SSI.php');
						require_once('../../forums/Sources/Subs-Post.php');
						
						//modifyPost($msgOptions, $topicOptions, $posterOptions);
						// Modify existing post based on Topic ID and Message ID
						// The input parameters of this function assume:
						// - Strings have been escaped.
						// - Integers have been cast to integer.
						// - Mandatory parameters are set.
						
						// Collect all parameters for the creation or modification of a post.
						$msgOptions = array(
							'id' => $smf_message_id,
							'body' => $smf_body,
							'subject' => $smf_subject,
							'modify_time' => time(),
							'modify_name' => $tali_username,
						);
						$topicOptions = array(
							'id' => $smf_topic_id,
							'mark_as_read' => true,
						);
						$posterOptions = array(
						);
						
						//Excute SMF post
						modifyPost($msgOptions, $topicOptions, $posterOptions);
					}
				};
				
				//History Report
				if (!isset($_GET['id'])) {
					//Adding new drill report, so created
					TALI_Create_History_Report('created', $module, $db_handle, 'tali_personnel_drillreports', 'drillreport_id', $drillreport_id, 'Drill Report ID#', 'date_drill');
				}
				else
				{
					//Editing drill report, so edited
					TALI_Create_History_Report('edited', $module, $db_handle, 'tali_personnel_drillreports', 'drillreport_id', $drillreport_id, 'Drill Report ID#', 'date_drill');
				}
				header ("Location: personnel.php?sub=drillreports&location=view");
				exit();
			}
			else
			{
				//Adding new or editing Drill Report
				
				if (isset($_GET['id'])) {
					//Editing a Drill Report, so pull values from database
					$drillreport_id = $_GET['id'];
						
					$SQL = "SELECT * FROM tali_personnel_drillreports WHERE drillreport_id=$drillreport_id";
					$result = mysqli_query($db_handle, $SQL);
					$db_field = mysqli_fetch_assoc($result);
					
					$form_dateofdrill=$db_field['date_drill'];
					$form_dateofdrill = date("m/d/Y", strtotime($form_dateofdrill));
					
					$form_dateofreport=$db_field['date_report'];
					$form_dateofreport = date("m/d/Y", strtotime($form_dateofreport));
					
					$attended=$db_field['attended'];
					$attended_array = explode(",", $attended);
					$excused=$db_field['excused'];
					$excused_array = explode(",", $excused);
					$excused_array_json = json_encode($excused_array);
					$absent=$db_field['absent'];
					$absent_array = explode(",", $absent);
					$absent_array_json = json_encode($absent_array);
					
					$allSquad_array = array_merge($attended_array, $excused_array, $absent_array);
					
					$form_designation_id = $db_field['designation_id'];
													
					$form_report_comments = $db_field['comments'];
					
					//Editing drill report, so word heading as such
					$headingStatus = "Edit";
					$paraText = "This page allows you to edit the selected Drill Report. Be sure to \"check\" all attendees and follow up with any remarks before hitting Submit.";
				}
				else
				{
					//Creating new drill report, so word heading as such
					$headingStatus = "Create";
					$paraText = "This page allows you to create a Drill Report. First select the designation you wish to file the report under, then \"check\" all attendees and follow up with any remarks before hitting Submit.";
				}
				
				//BUG - 3rd - non-dyanmic link to 3rd ID forums included here
				echo "
					<main>
						<div class=\"tali-container\">
							<div class=\"tali-page-frame\">
								<h1>$headingStatus Drill Report</h1>
								<p>$paraText</p>
								<p><a target=\"_blank\" href=\"http://www.3rd-infantry-division.org/forums/index.php?board=44.0\">Link to Excuses Board</a></p>
							</div>
							
							<div class=\"tali-page-frame\">
								<h1>$headingStatus Drill Report</h1>
				";
				
				//Date of Drill
				
				echo "
								<p>Date of drill (MM/DD/YYYY):</p>
								<p>
								<input type=\"text\" class=\"tali-personnel-drillreports-textinput\" name=\"dateofdrill\" form=\"create_drillreport\" maxlength=\"10\" value=\"$form_dateofdrill\">
								</p>
				";
				
				//Date of Report (greyed out box)
				if (!isset($_GET['id'])) {
					//Creating new drill report, so much define date as today's date
					$form_dateofreport = date("m/d/Y");
				}
				echo "
								<p>Date of report:</p>
								<p>
								<input type=\"text\" class=\"tali-personnel-drillreports-textinput\" name=\"dateofreport\" form=\"create_drillreport\" maxlength=\"10\" readonly value=\"$form_dateofreport\">
								</p>
				";
				
				if (!isset($_GET['id'])) {
					//New Drill Report, so allow selection of designation
					//Select Designation
					
					//Prepare a dropdown of designations for selection/action
					echo "
								<p>Select the desired designation to display its roster:</p>
								<p>
								<select form=\"create_drillreport\" class=\"desigSelect_report\" id=\"designation_select\" name=\"designation_select\">
									<option value=\"\" selected>Select a Designation</option>
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
								</p>
					";
										
					//End designation stuff
				}
				
				//Attended
				
				echo "
								<p>Place a checkmark for all who attended:</p>
				";
									
				//Need an array of all personnel with rank, firstname, lastname, and designation, sorted by rank_id weight. 
				$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE discharged=0 ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
				$result = mysqli_query($db_handle, $SQL);
				
				$arrayPersonnel_json = array();
				while ($db_field = mysqli_fetch_assoc($result)) {
					$rank_id=$db_field['rank_id'];
					$rankSQL = "SELECT abbreviation FROM tali_personnel_ranks WHERE rank_id=$rank_id";
					$rankresult = mysqli_query($db_handle, $rankSQL);
					$rank_db_field = mysqli_fetch_assoc($rankresult);
				
					$arrayPersonnel_json[] = array("personnel_id" => $db_field['personnel_id'],"rank_abr" => $rank_db_field['abbreviation'], "firstname" => $db_field['firstname'], "lastname" => $db_field['lastname'], "designation_id" => $db_field['designation_id']);
				};
				
				$arrayPersonnel_json_nonencode = $arrayPersonnel_json;
				$arrayPersonnel_json = json_encode($arrayPersonnel_json);
				
				if (!isset($_GET['id'])) {
					//New Drill Report, so must pull personnel based on designation
					
					echo "
								<p id=\"attendedFiller\">No designation selected, or designation has no members.</p>
					";
				
					//BUG - the two below arrays are dirty, and surely could be accomplished in a single array
					//Need an array of designations and the designations they report to
					$arrayInclusiveDesignation_json = array();
					forEach ($arrayDesignation as $designation) {
						$selected_designation_id = $designation['designation_id'];
						$selected_reportsto_designation_id = $designation['reportsto_designation_id'];
						
						//Define array
						$full_desig_CoC_array = array();
						//Add initial designation to array first
						$full_desig_CoC_array[] = $selected_designation_id;

						//If the selected designation reports to another designation, print their info, and continue
						//until the newly selected designation does not report to anyone (top of the chain)
						while ($selected_reportsto_designation_id != 0) {
							$desName_SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$selected_reportsto_designation_id";
							$desName_result = mysqli_query($db_handle, $desName_SQL);
							$desigName_db_field = mysqli_fetch_assoc($desName_result);
							//Add designation to array
							$full_desig_CoC_array[] = $desigName_db_field['designation_id'];
							//Change reportsTo id for next cycle
							$selected_reportsto_designation_id = $desigName_db_field['reportsto_designation_id'];
						}
						$arrayInclusiveDesignation_json[] = array("designation_id" => $selected_designation_id, "reports_to" => $full_desig_CoC_array);
					}
					
					//Convert above array from A to B:
					//A: Array of designations and the designations they report to
					//B: Array of designations and the designations that report to them
					$arrayInclusiveDesignation_json_final = array();
					forEach ($arrayInclusiveDesignation_json as $designation) {
						$selected_designation_id = $designation['designation_id'];
						$inclusiveHoldingArray = array();
						forEach ($arrayInclusiveDesignation_json as $designation_forArray) {
							$selected_reportsto_designation = $designation_forArray['reports_to'];
							if (in_array($selected_designation_id, $selected_reportsto_designation)) {
								$inclusiveHoldingArray[] = $designation_forArray['designation_id'];
							}
						}
						$arrayInclusiveDesignation_json_final[] = array("designation_id" => $selected_designation_id, "inclusive_designation" => $inclusiveHoldingArray);
					}
						
					$arrayInclusiveDesignation_json = json_encode($arrayInclusiveDesignation_json_final);
					
					?>
						<script>
							$(function() {
								var i;
								var a;
								var arrayInclusiveDesignation_json = <?php echo $arrayInclusiveDesignation_json; ?>;
								var arrayPersonnel_json = <?php echo $arrayPersonnel_json; ?>;
								
								$('.desigSelect_report').change(function() {
									attendanceFiller_jq = $("#attendedFiller");
									attendanceFiller_jq.empty();
									
									//this.id = the 'select' id, which is designation_weight_id
									var e = document.getElementById(this.id);
									var chosenDesignation_id = e.options[e.selectedIndex].value;
									
									for (i = 0; i < arrayPersonnel_json.length; i++) {
										for (a = 0; a < arrayInclusiveDesignation_json.length; a++) {
											if (arrayInclusiveDesignation_json[a].designation_id === chosenDesignation_id) {
												if ((arrayInclusiveDesignation_json[a].inclusive_designation.indexOf(arrayPersonnel_json[i].designation_id)) != -1) {
													attendanceFiller_jq.append(
														'<input type="checkbox" class="attendedCheckbox_class" form="create_drillreport" name="attendedcheckbox[]" id="attended_' + arrayPersonnel_json[i].personnel_id+ '" value="' + arrayPersonnel_json[i].personnel_id + '"/> ' + arrayPersonnel_json[i].rank_abr + ' ' + arrayPersonnel_json[i].firstname + ' ' + arrayPersonnel_json[i].lastname + '<input type="hidden" form="create_drillreport" name="designationmembers[]" value="' + arrayPersonnel_json[i].personnel_id + '"/><br/>'
													);
												}
											}
										}
									};
								});
							}); 
						</script>
					<?php
				}
				else
				{
					//Editing drill report, so pull roster from database
					foreach ($arrayPersonnel_json_nonencode as $i) {
						if (in_array($i['personnel_id'], $allSquad_array)) {
							//Of everyone in the unit past and present, only listing those in the squad at the
							//time of the report
							if (in_array($i['personnel_id'], $attended_array)) {
								$checked = "checked=\"checked\"";
							}
							else
							{
								$checked = '';
							}
							
							echo "
								<input type=\"checkbox\" ".$checked." class=\"attendedCheckbox_class\" form=\"create_drillreport\" name=\"attendedcheckbox[]\" id=\"attended_" . $i['personnel_id'] . "\" value=\"" . $i['personnel_id'] . "\"/> " . $i['rank_abr'] . " " . $i['firstname'] . " " . $i['lastname'] . "
								<input type=\"hidden\" form=\"create_drillreport\" name=\"designationmembers[]\" value=\"" . $i['personnel_id'] . "\"/>
								<br/>
							";
						};
					};
				}
				
				//Excused - check if excused, blank=absent
				
				//bug - I feel like this whole setup is very sloppy and slow. But it works. 
										
				echo "
								<br/>
								<br/>
								<p>Place a checkmark for all who were not present but were excused:</p>
								<p id=\"excusedFiller\">All designation members present, or designation has no members.</p>
				";
				
				if (!isset($_GET['id'])) {
					//Javascript for excused section of New Drill Reports
				
					?>
						<script>
							$(function() {
								var i;
								var a;
								var arrayInclusiveDesignation_json = <?php echo $arrayInclusiveDesignation_json; ?>;
								var arrayPersonnel_json = <?php echo $arrayPersonnel_json; ?>;
								
								$('.desigSelect_report').change(function() {
									excusedFiller_jq = $("#excusedFiller");
									excusedFiller_jq.empty();
									
									//this.id = the 'select' id, which is designation_weight_id
									var e = document.getElementById(this.id);
									var chosenDesignation_id = e.options[e.selectedIndex].value;
									
									for (i = 0; i < arrayPersonnel_json.length; i++) {
										for (a = 0; a < arrayInclusiveDesignation_json.length; a++) {
											if (arrayInclusiveDesignation_json[a].designation_id === chosenDesignation_id) {
												if ((arrayInclusiveDesignation_json[a].inclusive_designation.indexOf(arrayPersonnel_json[i].designation_id)) != -1) {
													excusedFiller_jq.append(
														'<input type="checkbox" class="excusedCheckbox_class" form="create_drillreport" name="excusedcheckbox[]" id="excused_' + arrayPersonnel_json[i].personnel_id + '" value="' + arrayPersonnel_json[i].personnel_id+ '"/> ' + arrayPersonnel_json[i].rank_abr + ' ' + arrayPersonnel_json[i].firstname + ' ' + arrayPersonnel_json[i].lastname + '<br/>'
													);
												}
											}
										}
									};
									$(document).on('change' , '.attendedCheckbox_class' , function(){
										//this.id = ID of clicked box
										excusedFiller_jq = $("#excusedFiller");
										excusedFiller_jq.empty();
										
										var allPresent = 0;
										for (i = 0; i < arrayPersonnel_json.length; i++) {
											for (a = 0; a < arrayInclusiveDesignation_json.length; a++) {
												if (arrayInclusiveDesignation_json[a].designation_id === chosenDesignation_id) {
													if ((arrayInclusiveDesignation_json[a].inclusive_designation.indexOf(arrayPersonnel_json[i].designation_id)) != -1) {
														if ((document.getElementById('attended_' + arrayPersonnel_json[i].personnel_id + '')) !== null) {
															if ((document.getElementById('attended_' + arrayPersonnel_json[i].personnel_id + '')).checked) {
																//nothing
															}
															else
															{
																excusedFiller_jq.append(
																	'<input type="checkbox" class="excusedCheckbox_class" form="create_drillreport" name="excusedcheckbox[]" id="excused_' + arrayPersonnel_json[i].personnel_id + '" value="' + arrayPersonnel_json[i].personnel_id+ '"/> ' + arrayPersonnel_json[i].rank_abr + ' ' + arrayPersonnel_json[i].firstname + ' ' + arrayPersonnel_json[i].lastname + '<br/>'
																);
																allPresent = allPresent + 1;
															};
														};
													};
												};
											};
										};
										if (allPresent == 0) {
											excusedFiller_jq.append(
												'All designation members present.'
											);
										};
									});
								});
							}); 
						</script>
					<?php
				}
				else
				{
					//Javascript for excused section of editing Drill Reports
					?>
						<script>
							$(function() {
								var i;
								var arrayPersonnel_json = <?php echo $arrayPersonnel_json; ?>;
								var excused_array_json = <?php echo $excused_array_json; ?>;
								var absent_array_json = <?php echo $absent_array_json; ?>;
								excusedFiller_jq = $("#excusedFiller");
								excusedFiller_jq.empty();
								
								var chosenDesignation_id = <?php echo $_GET['id']; ?>;
								
								for (i = 0; i < arrayPersonnel_json.length; i++) {
									if (($.inArray(arrayPersonnel_json[i].personnel_id, excused_array_json)) != -1) {
									//Only listing those in the squad at this time and not present
										excusedFiller_jq.append(
											'<input type="checkbox" class="excusedCheckbox_class" form="create_drillreport" name="excusedcheckbox[]" id="excused_' + arrayPersonnel_json[i].personnel_id + '" value="' + arrayPersonnel_json[i].personnel_id+ '"/> ' + arrayPersonnel_json[i].rank_abr + ' ' + arrayPersonnel_json[i].firstname + ' ' + arrayPersonnel_json[i].lastname + '<br/>'
										);
									};
								};
								$(document).on('change' , '.attendedCheckbox_class' , function(){
									//this.id = ID of clicked box
									excusedFiller_jq = $("#excusedFiller");
									excusedFiller_jq.empty();
									
									var allPresent = 0;
									for (i = 0; i < arrayPersonnel_json.length; i++) {
										if ((document.getElementById('attended_' + arrayPersonnel_json[i].personnel_id + '')) !== null) {
											if ((document.getElementById('attended_' + arrayPersonnel_json[i].personnel_id + '')).checked) {
												//nothing
											}
											else
											{
												var checkedbox = '';
												if (excused_array_json.indexOf(arrayPersonnel_json[i].personnel_id) !== -1) {
													var checkedbox = 'checked="checked"';
												}
												excusedFiller_jq.append(
													'<input type="checkbox" ' + checkedbox + ' class="excusedCheckbox_class" form="create_drillreport" name="excusedcheckbox[]" id="excused_' + arrayPersonnel_json[i].personnel_id + '" value="' + arrayPersonnel_json[i].personnel_id+ '"/> ' + arrayPersonnel_json[i].rank_abr + ' ' + arrayPersonnel_json[i].firstname + ' ' + arrayPersonnel_json[i].lastname + '<br/>'
												);
												allPresent = allPresent + 1;
											};
										};
									};
									if (allPresent == 0) {
										excusedFiller_jq.append(
											'All designation members present.'
										);
									};
								});
								
								$('.attendedCheckbox_class').change();
							}); 
						</script>
					<?php
				}
				
				//Comments
				
				echo "
								<br/>
								<p>Provide any comments below:</p>
								<p>
								<textarea class=\"tali-personnel-drillreports-textarea\" name=\"report_comments\" form=\"create_drillreport\" value=\"\">$form_report_comments</textarea>
								</p>
				";
				
				if (isset($_GET['id'])) {
					//Editing, so include id in post URL
					$form_postURL_id = "&id=$drillreport_id";
					$form_postbutton = "Edit";
				}
				else
				{
					$form_postURL_id = "";
					$form_postbutton = "Create";
				}
				
				//bug - hide/condition this if designation not selected
				echo "
								<form method=\"POST\" id=\"create_drillreport\" action=\"personnel.php?sub=drillreports&location=create&action=submit".$form_postURL_id."\">
									<p>
									<input type=\"submit\" name=\"btnSubmit\" value=\"$form_postbutton Drill Report\"/>
									</p>
								</form>
				"; 
				
				echo "
							</div>
						</div>
					</main>
				";
			}
		break;
	}
}
?>