<?php
//Stock variables
$module = "TALI_Personnel";

$record = "";
$date_awarded = "";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case "addaward":
			$personnel_id = $_GET['id'];
			$award_id = $_POST['award_id'];
			$date_awarded = $_POST['date_awarded'];
			if ($date_awarded == "") {
				$date_awarded = date("m/d/Y");
			};
			$record = $_POST['record'];
			
			//bug - need some redesign to allow error here
			//same spot to errormessage for edit
			if (($award_id == "") || ($record == "")) {
				header ("Location: personnel.php?sub=roster_awards&id=$personnel_id");
				exit();
			}
			
			$personnel_id_sql = htmlspecialchars($personnel_id);
			$personnel_id_sql = TALI_quote_smart($personnel_id_sql, $db_handle);
			
			$award_id_sql = htmlspecialchars($award_id);
			$award_id_sql = TALI_quote_smart($award_id_sql, $db_handle);
			
			$date_awarded_sql = date("Y-m-d", strtotime($date_awarded));
			$date_awarded_sql = htmlspecialchars($date_awarded_sql);
			$date_awarded_sql = TALI_quote_smart($date_awarded_sql, $db_handle);
			
			$record_sql = htmlspecialchars($record);
			$record_sql = TALI_quote_smart($record_sql, $db_handle);
			
			if (isset($_GET['awardrecord_id'])) {
				//EDIT
				$awardrecord_id = $_GET['awardrecord_id'];
				$SQL = "UPDATE tali_personnel_awards_record SET award_id=$award_id_sql, date_awarded=$date_awarded_sql, record=$record_sql WHERE awardrecord_id=$awardrecord_id";						
				$result = mysqli_query($db_handle, $SQL);
				
				//History Report
				TALI_Create_History_Report('edited', $module, $db_handle, 'tali_personnel_awards_record', 'awardrecord_id', $awardrecord_id, 'Award Record ID#', 'award_id');
			}
			else
			{
				//ADD
				$SQL = "INSERT INTO tali_personnel_awards_record (personnel_id, award_id, date_awarded, record) VALUES ($personnel_id_sql, $award_id_sql, $date_awarded_sql, $record_sql)";						
				$result = mysqli_query($db_handle, $SQL);
				
				$SQL = "SELECT awardrecord_id FROM tali_personnel_awards_record ORDER BY awardrecord_id DESC LIMIT 1";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$awardrecord_id=$db_field['awardrecord_id'];	
				
				//History Report
				TALI_Create_History_Report('added', $module, $db_handle, 'tali_personnel_awards_record', 'awardrecord_id', $awardrecord_id, 'Award Record ID#', 'award_id');
			}
			
			header ("Location: personnel.php?sub=roster_awards&id=$personnel_id");
			exit();
		break;
		case "editaward":
			$personnel_id = $_GET['id'];
			$awardrecord_id = $_GET['awardrecord_id'];
			
			$SQL = "SELECT * FROM tali_personnel_awards_record WHERE awardrecord_id=$awardrecord_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$award_id = $db_field['award_id'];
			$date_awarded = date("m/d/Y", strtotime($db_field['date_awarded']));
			$record = $db_field['record'];
			
			echo "
				<main>
					<div class=\"tali-container\">
						<div class=\"tali-page-frame\">
							<h1>Manage Personnel Awards</h1>
							<p>This page allows you to manage awards for a specific individual.</p>
						</div>
			";
			
			$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);

			echo "
						<div class=\"tali-page-frame\">
							<h1>Manage Personnel Awards for ".$db_field['abbreviation']." ".$db_field['firstname']." ".$db_field['lastname']."</h1>
			";
			
			echo "
							<p>Select an award from the dropdown below to add:</p>
							<select class=\"tali-personnel-awards-addaward-dropdown\" name=\"award_id\" form=\"add_award\" value=\"\">
								<option value=\"\">Select an Award</option>
			";
			
			//bug - make this sort by awardclass_id then award_class id weight then award_id weight
			$SQL = "SELECT * FROM tali_personnel_awards ORDER BY awardclass_id DESC";
			$result = mysqli_query($db_handle, $SQL);
			while ($db_field = mysqli_fetch_assoc($result)) {
				if ($award_id == $db_field['award_id']) {
					$selected = 'selected="selected"';
				}
				else
				{
					$selected = '';
				}
				echo "
								<option value=\"{$db_field['award_id']}\"".$selected.">{$db_field['name']}</option>
				";
			}
			
			echo "
							</select>
			";
			
			echo "
							<p>Date awarded (MM/DD/YYYY):</p>
							<input type=\"text\" class=\"tali-personnel-drillreports-textinput\" name=\"date_awarded\" form=\"add_award\" maxlength=\"10\" value=\"$date_awarded\">
			";
			
			echo "
							<p>Award Record:</p>
							<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"record\" form=\"add_award\" value=\"$record\">
							
							<form method=\"POST\" id=\"add_award\" action=\"personnel.php?sub=roster_awards&id=$personnel_id&action=addaward&awardrecord_id=$awardrecord_id\">
								<p>
								<input type=\"submit\" name=\"btnSubmit\" value=\"Edit Award\"/>
								</p>
							</form>
						</div>
					</div>
				</main>
			";

		break;
		case "deleteaward":
			$delid = $_GET['awardrecord_id'];
			$personnel_id = $_GET['id'];
	
			//History Report
			TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_personnel_awards_record', 'awardrecord_id', $delid, 'Award Record ID#', 'award_id');
			
			//bug - should history report be going before action...?
			
			$delSQL = "DELETE FROM tali_personnel_awards_record WHERE awardrecord_id = $delid";
			$delresult = mysqli_query($db_handle, $delSQL);
			
			header ("Location: personnel.php?sub=roster_awards&id=$personnel_id");
			exit();
		break;
	};
}
else
{
	//Fresh page
	$personnel_id = $_GET['id'];
	
	echo "
		<main>
			<div class=\"tali-container\">
				<div class=\"tali-page-frame\">
					<h1>Manage Personnel Awards</h1>
					<p>This page allows you to manage awards for a specific individual.</p>
				</div>
	";
	
	$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);

	echo "
				<div class=\"tali-page-frame\">
					<h1>Manage Personnel Awards for ".$db_field['abbreviation']." ".$db_field['firstname']." ".$db_field['lastname']."</h1>
	";
	
	echo "
					<p>Select an award from the dropdown below to add:</p>
					<select class=\"tali-personnel-awards-addaward-dropdown\" name=\"award_id\" form=\"add_award\" value=\"\">
						<option value=\"\">Select an Award</option>
	";
	
	//bug - make this sort by awardclass_id then award_class id weight then award_id weight
	$SQL = "SELECT * FROM tali_personnel_awards ORDER BY awardclass_id DESC";
	$result = mysqli_query($db_handle, $SQL);
	while ($db_field = mysqli_fetch_assoc($result)) {
		echo "
						<option value=\"{$db_field['award_id']}\">{$db_field['name']}</option>
		";
	}
	
	echo "
					</select>
	";
	
	echo "
					<p>Date awarded (MM/DD/YYYY):</p>
					<input type=\"text\" class=\"tali-personnel-drillreports-textinput\" name=\"date_awarded\" form=\"add_award\" maxlength=\"10\" value=\"$date_awarded\">
	";
	
	echo "
					<p>Award Record:</p>
					<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"record\" form=\"add_award\" value=\"$record\">
					
					<form method=\"POST\" id=\"add_award\" action=\"personnel.php?sub=roster_awards&id=$personnel_id&action=addaward\">
						<p>
						<input type=\"submit\" name=\"btnSubmit\" value=\"Add Award\"/>
						</p>
					</form>
	";
	
	echo "			
					<p>Click to edit or delete previously earned awards:</p>
					<table id=\"awardclassTable\" class=\"tali-personnel-table\">
						<col width=\"30%\">
						<col width=\"20%\">
						<col width=\"30%\">
						<col width=\"10%\">
						<col width=\"10%\">
						
						<tr>
							<th>Award</th>
							<th>Date Awarded</th>
							<th>Record</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
	";
	
	$SQL = "SELECT * FROM tali_personnel_awards_record WHERE personnel_id=$personnel_id ORDER BY date_awarded DESC";
	$result = mysqli_query($db_handle, $SQL);
	while ($db_field = mysqli_fetch_assoc($result)) {
		$awardrecord_id = $db_field['awardrecord_id'];
		$award_id = $db_field['award_id'];
		$awardSQL = "SELECT * FROM tali_personnel_awards WHERE award_id=$award_id";
		$awardresult = mysqli_query($db_handle, $awardSQL);
		$awarddb_field = mysqli_fetch_assoc($awardresult);
		$award_name = $awarddb_field['name'];
		$date_awarded = $db_field['date_awarded'];
		$date_awarded = date("m/d/Y", strtotime($date_awarded));
		$record = $db_field['record'];
				
		echo "
						<tr>
							<td style=\"text-align:center;\">$award_name</td>
							<td style=\"text-align:center;\">$date_awarded</td>
							<td style=\"text-align:center;\">$record</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster_awards&id=$personnel_id&awardrecord_id=$awardrecord_id&action=editaward\">
									<img src=\"../images/display/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster_awards&id=$personnel_id&awardrecord_id=$awardrecord_id&action=deleteaward\" onclick=\"return confirm('Are you sure you want to delete this award?');\">
									<img src=\"../images/display/icons/delete.png\" alt=\"Delete Icon\" name=\"Delete Icon\">
								</a>
							</td>
						</tr>
		";
	};
	
	echo "
					</table>
	";
	
	echo "
				</div>
			</div>
		</main>
	";
}
?>