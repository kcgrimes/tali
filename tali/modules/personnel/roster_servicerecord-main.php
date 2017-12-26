<?php
//Stock variables
$module = "TALI_Personnel";

$record = "";
$date = "";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}

TALI_sessionCheck($module, $db_handle);

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case "addrecord":
			$personnel_id = $_GET['id'];
			$date = $_POST['date'];
			if ($date == "") {
				$date = date("m/d/Y");
			};
			$record = $_POST['record'];
			
			//bug - need some redesign to allow error here
			//same spot to errormessage for edit
			if ($record == "") {
				header ("Location: personnel.php?sub=roster_servicerecord&id=$personnel_id");
				exit();
			}
			
			$personnel_id_sql = htmlspecialchars($personnel_id);
			$personnel_id_sql = TALI_quote_smart($personnel_id_sql, $db_handle);
								
			$date_sql = date("Y-m-d", strtotime($date));
			$date_sql = htmlspecialchars($date_sql);
			$date_sql = TALI_quote_smart($date_sql, $db_handle);
			
			$record_sql = htmlspecialchars($record);
			$record_sql = TALI_quote_smart($record_sql, $db_handle);
			
			if (isset($_GET['servicerecord_id'])) { 
				//EDIT
				$servicerecord_id = $_GET['servicerecord_id'];
				$SQL = "UPDATE tali_personnel_service_record SET date=$date_sql, record=$record_sql WHERE servicerecord_id=$servicerecord_id";						
				$result = mysqli_query($db_handle, $SQL);
				
				//History Report
				TALI_Create_History_Report('edited', $module, $db_handle, 'tali_personnel_service_record', 'servicerecord_id', $servicerecord_id, 'Service Record ID#', 'date');
			}
			else
			{
				//ADD
				$SQL = "INSERT INTO tali_personnel_service_record (personnel_id, date, record) VALUES ($personnel_id_sql, $date_sql, $record_sql)";						
				$result = mysqli_query($db_handle, $SQL);
				
				$SQL = "SELECT servicerecord_id FROM tali_personnel_service_record ORDER BY servicerecord_id DESC LIMIT 1";
				$result = mysqli_query($db_handle, $SQL);
				$db_field = mysqli_fetch_assoc($result);
				$servicerecord_id=$db_field['servicerecord_id'];	
				
				//History Report
				TALI_Create_History_Report('added', $module, $db_handle, 'tali_personnel_service_record', 'servicerecord_id', $servicerecord_id, 'Service Record ID#', 'date');
			}
			
			header ("Location: personnel.php?sub=roster_servicerecord&id=$personnel_id");
			exit();
		break;
		case "editrecord":
			$personnel_id = $_GET['id'];
			$servicerecord_id = $_GET['servicerecord_id'];
			
			$SQL = "SELECT * FROM tali_personnel_service_record WHERE servicerecord_id=$servicerecord_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			$date = date("m/d/Y", strtotime($db_field['date']));
			$record = $db_field['record'];
			
			echo "
				<main>
					<div class=\"tali-container\">
						<div class=\"tali-page-frame\">
							<h1>Manage Personnel Service Record</h1>
							<p>This page allows you to manage the service record of a specific individual.</p>
						</div>
			";
			
			$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);

			echo "
						<div class=\"tali-page-frame\">
							<h1>Manage Personnel Service Record for ".$db_field['abbreviation']." ".$db_field['firstname']." ".$db_field['lastname']."</h1>
			";
			
			echo "
							<p>Date of record (MM/DD/YYYY):</p>
							<input type=\"text\" class=\"tali-personnel-drillreports-textinput\" name=\"date\" form=\"add_record\" maxlength=\"10\" value=\"$date\">
			";
			
			echo "
							<p>Record:</p>
							<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"record\" form=\"add_record\" value=\"$record\">
							
							<form method=\"POST\" id=\"add_record\" action=\"personnel.php?sub=roster_servicerecord&id=$personnel_id&action=addrecord&servicerecord_id=$servicerecord_id\">
								<p>
								<input type=\"submit\" name=\"btnSubmit\" value=\"Edit Service Record\"/>
								</p>
							</form>
						</div>
					</div>
				</main>
			";

		break;
		case "deleterecord":
			$delid = $_GET['servicerecord_id'];
			$personnel_id = $_GET['id'];
	
			//History Report
			TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_personnel_service_record', 'servicerecord_id', $delid, 'Service Record ID#', 'date');
								
			$delSQL = "DELETE FROM tali_personnel_service_record WHERE servicerecord_id = $delid";
			$delresult = mysqli_query($db_handle, $delSQL);
			
			header ("Location: personnel.php?sub=roster_servicerecord&id=$personnel_id");
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
					<h1>Manage Personnel Service Record</h1>
					<p>This page allows you to manage the service record of a specific individual.</p>
				</div>
	";
	
	$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE personnel_id=$personnel_id";
	$result = mysqli_query($db_handle, $SQL);
	$db_field = mysqli_fetch_assoc($result);

	echo "
				<div class=\"tali-page-frame\">
					<h1>Manage Personnel Service Record for ".$db_field['abbreviation']." ".$db_field['firstname']." ".$db_field['lastname']."</h1>
	";
	
	echo "
					<p>Date of record (MM/DD/YYYY):</p>
					<input type=\"text\" class=\"tali-personnel-drillreports-textinput\" name=\"date\" form=\"add_record\" maxlength=\"10\" value=\"$date\">
	";
	
	echo "
					<p>Record:</p>
					<input type=\"text\" class=\"tali-personnel-awards-textinput\" name=\"record\" form=\"add_record\" value=\"$record\">
					
					<form method=\"POST\" id=\"add_record\" action=\"personnel.php?sub=roster_servicerecord&id=$personnel_id&action=addrecord\">
						<p>
						<input type=\"submit\" name=\"btnSubmit\" value=\"Add Service Record\"/>
						</p>
					</form>
	";
	
	echo "			
					<p>Click to edit or delete previously documented records:</p>
					<table id=\"recordclassTable\" class=\"tali-personnel-table\">
						<col width=\"20%\">
						<col width=\"60%\">
						<col width=\"10%\">
						<col width=\"10%\">
						
						<tr>
							<th>Date Awarded</th>
							<th>Record</th>
							<th>Edit</th>
							<th>Delete</th>
						</tr>
	";
	
	$SQL = "SELECT * FROM tali_personnel_service_record WHERE personnel_id=$personnel_id ORDER BY date DESC";
	$result = mysqli_query($db_handle, $SQL);
	while ($db_field = mysqli_fetch_assoc($result)) {
		$servicerecord_id = $db_field['servicerecord_id'];
		$date = $db_field['date'];
		$date = date("m/d/Y", strtotime($date));
		$record = $db_field['record'];
				
		echo "
						<tr>
							<td style=\"text-align:center;\">$date</td>
							<td style=\"text-align:center;\">$record</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster_servicerecord&id=$personnel_id&servicerecord_id=$servicerecord_id&action=editrecord\">
									<img src=\"../images/display/icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
								</a>
							</td>
							<td style=\"text-align:center;\">
								<a href=\"personnel.php?sub=roster_servicerecord&id=$personnel_id&servicerecord_id=$servicerecord_id&action=deleterecord\" onclick=\"return confirm('Are you sure you want to delete this service record?');\">
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