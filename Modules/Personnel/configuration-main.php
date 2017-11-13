<?php
//Stock variables
$module = "TALI_Personnel";
$rank_addname = "";
$rank_addabbreviation = "";
$rank_addimage = "";
$rank_addweight = "";
$status_addname = "";
$status_addweight = "";
$status_form_action = "";
$status_form_value = "";
$role_addname = "";
$role_addweight = "";
$rank_form_action = "";
$rank_form_value = "";
$role_form_action = "";
$role_form_value = "";
$desig_addname = "";
$desig_addweight = "";
$desig_addleader = "";
$desig_form_action = "";
$desig_form_value = "";
$desig_addreportsTo = "";
$true_reportsTo = "";
$errorMessage = "";
$rankfile_failed = FALSE;
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

//Return if button was clicked, else page is fresh
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//Return - Configure Ranks
	if (isset($_POST['ranks_bu'])) {
		//Check which button was clicked to determine action
		if ((isset($_POST['tali_modules_personnel_ranks_add_addname'])) && (isset($_POST['tali_modules_personnel_ranks_add_addabbreviation'])) && (isset($_POST['tali_modules_personnel_ranks_add_addweight']))) {
			$rank_addname = $_POST['tali_modules_personnel_ranks_add_addname'];
			$rank_addabbreviation = $_POST['tali_modules_personnel_ranks_add_addabbreviation'];
			$rank_addweight = $_POST['tali_modules_personnel_ranks_add_addweight'];
			if (($rank_addname != "") && ($rank_addabbreviation != "") && ($rank_addweight != "")) {
				if ((isset($_GET['id'])) && (isset($_GET['button']))) {
					$rank_id = $_GET['id'];
					switch ($_GET['button']) {
						//Edit
							//Note - Prevents renaming of image file to avoid FTP function issues
						case "rank_edit": 								
							$rank_addname_sql = htmlspecialchars($rank_addname);
							$rank_addname_sql = TALI_quote_smart($rank_addname_sql, $db_handle);

							$rank_addabbreviation_sql = htmlspecialchars($rank_addabbreviation);
							$rank_addabbreviation_sql = TALI_quote_smart($rank_addabbreviation_sql, $db_handle);

							$rank_addweight_sql = htmlspecialchars($rank_addweight);
							$rank_addweight_sql = TALI_quote_smart($rank_addweight_sql, $db_handle);
							
							$SQL = "UPDATE tali_personnel_ranks SET name=$rank_addname_sql, abbreviation=$rank_addabbreviation_sql, weight=$rank_addweight_sql WHERE rank_id=$rank_id"; 
							$result = mysqli_query($db_handle, $SQL);
							
							TALI_Create_History_Report('edited', $module, $db_handle, 'tali_personnel_ranks', 'rank_id', $rank_id, 'Rank ID#', 'name');

							header ("Location: personnel.php?sub=configuration");
							exit();
						break;
						//Delete
						case "rank_delete":
							$getimageSQL = "SELECT image FROM tali_personnel_ranks WHERE rank_id=$rank_id";
							$getimageresult = mysqli_query($db_handle, $getimageSQL);
							$getimage_db_field = mysqli_fetch_assoc($getimageresult);
							$rank_image=$getimage_db_field['image'];	
						
							$ftpDelete_Return = TALI_FTP_Delete($rank_image, $_SESSION['TALI_Ranks_Images_Directory']);
							$errorMessage = $ftpDelete_Return[1]; 
							$delete_success = $ftpDelete_Return[2];
							if ($delete_success) {
								TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_personnel_ranks', 'rank_id', $rank_id, 'Rank ID#', 'name');
							
								$delSQL = "DELETE FROM tali_personnel_ranks WHERE rank_id = $rank_id";
								$delresult = mysqli_query($db_handle, $delSQL);
							
								header ("Location: personnel.php?sub=configuration");
								exit();
							}
						break;
					}
				}
				else
				//Add
				{
					if (isset ($_FILES['rank_file']['tmp_name'])) {
						$source_file = $_FILES['rank_file']['tmp_name'];
		
						//Check file to make sure it's what we want
						if ($source_file != "") {
							$type = "";
							list($width, $height, $type) = getimagesize($source_file);
							if (($type == "") OR (!(($type == 2) OR ($type == 3) OR ($type == 6)))) {
								$errorMessage = "</br>Cannot upload image - must be of .JPG, .PNG, or .BMP format.</br>";
								$rankfile_failed = TRUE;
							}
							
							//bug - add size restrictions?
						}
						else
						{
							$errorMessage = "</br>Cannot upload image - Image size too large or did not completely upload.</br>";
							$rankfile_failed = TRUE;
						}
						
						//Are we ready?
						if (!$rankfile_failed) {
							//Upload single file to FTP
							$ftpUpload_Return = TALI_FTP_Upload('rank_file', $_SESSION['TALI_Ranks_Images_Directory']);
							$errorMessage = $ftpUpload_Return[1]; 
							$upload_success = $ftpUpload_Return[2];
							if ($upload_success) {
						
								$rank_addname_sql = htmlspecialchars($rank_addname);
								$rank_addname_sql = TALI_quote_smart($rank_addname_sql, $db_handle);

								$rank_addabbreviation_sql = htmlspecialchars($rank_addabbreviation);
								$rank_addabbreviation_sql = TALI_quote_smart($rank_addabbreviation_sql, $db_handle);
								
								$rank_addimage_sql = htmlspecialchars($_FILES['rank_file']['name']);
								$rank_addimage_sql = TALI_quote_smart($rank_addimage_sql, $db_handle);

								$rank_addweight_sql = htmlspecialchars($rank_addweight);
								$rank_addweight_sql = TALI_quote_smart($rank_addweight_sql, $db_handle);
								
								$SQL = "INSERT INTO tali_personnel_ranks (name, abbreviation, image, weight) VALUES ($rank_addname_sql, $rank_addabbreviation_sql, $rank_addimage_sql, $rank_addweight_sql)";
								$result = mysqli_query($db_handle, $SQL);
								
								$timeSQL = "SELECT * FROM tali_personnel_ranks ORDER BY rank_id DESC LIMIT 1";
								$timeresult = mysqli_query($db_handle, $timeSQL);
								$db_field = mysqli_fetch_assoc($timeresult);
								$newid=$db_field['rank_id'];		

								TALI_Create_History_Report('created', $module, $db_handle, 'tali_personnel_ranks', 'rank_id', $newid, 'Rank ID#', 'name');						
								
								$rank_addname = "";
								$rank_addabbreviation = "";
								$rank_addimage = "";
								$rank_addweight = "";
								
								header ("Location: personnel.php?sub=configuration");
								exit();
							}
						}
					}
					else
					{
						$errorMessage = "ERROR: Must select rank image file in order to add a rank.";
					}
				}
			}
			else
			{
				$errorMessage = "ERROR: Unexpected rank entry.";
			}
		}
	}
	//Return - Configure Statuses
	if (isset($_POST['statuses_bu'])) {
		//Check which button was clicked to determine action
		if ((isset($_POST['status_add_name'])) && (isset($_POST['status_add_weight']))) {
			$status_addname = $_POST['status_add_name'];
			$status_addweight = $_POST['status_add_weight'];
			if (($status_addname != "") && ($status_addweight != "")) {
				if ((isset($_GET['id'])) && (isset($_GET['button']))) {
					$status_id = $_GET['id'];
					switch ($_GET['button']) {
						//Edit
						case "status_edit": 								
							$status_addname_sql = htmlspecialchars($status_addname);
							$status_addname_sql = TALI_quote_smart($status_addname_sql, $db_handle);
							
							$status_addweight_sql = htmlspecialchars($status_addweight);
							$status_addweight_sql = TALI_quote_smart($status_addweight_sql, $db_handle);
							
							$SQL = "UPDATE tali_personnel_statuses SET name=$status_addname_sql, weight=$status_addweight_sql WHERE status_id=$status_id"; 
							$result = mysqli_query($db_handle, $SQL);
							
							TALI_Create_History_Report('edited', $module, $db_handle, 'tali_personnel_statuses', 'status_id', $status_id, 'Status ID#', 'name');

							header ("Location: personnel.php?sub=configuration");
							exit();
							break;
						//Delete
						case "status_delete":
							TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_personnel_statuses', 'status_id', $status_id, 'Status ID#', 'name');
						
							$delSQL = "DELETE FROM tali_personnel_statuses WHERE status_id = $status_id";
							$delresult = mysqli_query($db_handle, $delSQL);
							
							header ("Location: personnel.php?sub=configuration");
							exit();
							break;
					}
				}
				else
				//Add
				{
					$status_addname_sql = htmlspecialchars($status_addname);
					$status_addname_sql = TALI_quote_smart($status_addname_sql, $db_handle);
					
					$status_addweight_sql = htmlspecialchars($status_addweight);
					$status_addweight_sql = TALI_quote_smart($status_addweight_sql, $db_handle);
					
					$SQL = "INSERT INTO tali_personnel_statuses (name, weight) VALUES ($status_addname_sql, $status_addweight_sql)";
					$result = mysqli_query($db_handle, $SQL);
					
					$timeSQL = "SELECT * FROM tali_personnel_statuses ORDER BY status_id DESC LIMIT 1";
					$timeresult = mysqli_query($db_handle, $timeSQL);
					$db_field = mysqli_fetch_assoc($timeresult);
					$newid=$db_field['status_id'];		

					TALI_Create_History_Report('created', $module, $db_handle, 'tali_personnel_statuses', 'status_id', $newid, 'Status ID#', 'name');						
					
					$status_addname = "";
					$status_addweight = "";
					
					header ("Location: personnel.php?sub=configuration");
					exit();
				}
			}
			else
			{
				$errorMessage = "ERROR: Unexpected status entry.";
			}
		}
	}
	//Return - Configure Roles
	if (isset($_POST['roles_bu'])) {
		//Check which button was clicked to determine action
		if ((isset($_POST['role_add_name'])) && (isset($_POST['role_add_weight']))) {
			$role_addname = $_POST['role_add_name'];
			$role_addweight = $_POST['role_add_weight'];
			if (($role_addname != "") && ($role_addweight != "")) {
				if ((isset($_GET['id'])) && (isset($_GET['button']))) {
					$role_id = $_GET['id'];
					switch ($_GET['button']) {
						//Edit
						case "role_edit": 								
							$role_addname_sql = htmlspecialchars($role_addname);
							$role_addname_sql = TALI_quote_smart($role_addname_sql, $db_handle);

							$role_addweight_sql = htmlspecialchars($role_addweight);
							$role_addweight_sql = TALI_quote_smart($role_addweight_sql, $db_handle);
							
							$SQL = "UPDATE tali_personnel_roles SET name=$role_addname_sql, weight=$role_addweight_sql WHERE role_id=$role_id"; 
							$result = mysqli_query($db_handle, $SQL);
							
							TALI_Create_History_Report('edited', $module, $db_handle, 'tali_personnel_roles', 'role_id', $role_id, 'Role ID#', 'name');

							header ("Location: personnel.php?sub=configuration");
							exit();
							break;
						//Delete
						case "role_delete":
							TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_personnel_roles', 'role_id', $role_id, 'Role ID#', 'name');
						
							$delSQL = "DELETE FROM tali_personnel_roles WHERE role_id = $role_id";
							$delresult = mysqli_query($db_handle, $delSQL);
							
							header ("Location: personnel.php?sub=configuration");
							exit();
							break;
					}
				}
				else
				//Add
				{
					$role_addname_sql = htmlspecialchars($role_addname);
					$role_addname_sql = TALI_quote_smart($role_addname_sql, $db_handle);

					$role_addweight_sql = htmlspecialchars($role_addweight);
					$role_addweight_sql = TALI_quote_smart($role_addweight_sql, $db_handle);

					$SQL = "INSERT INTO tali_personnel_roles (name, weight) VALUES ($role_addname_sql, $role_addweight_sql)";
					$result = mysqli_query($db_handle, $SQL);
					
					$timeSQL = "SELECT * FROM tali_personnel_roles ORDER BY role_id DESC LIMIT 1";
					$timeresult = mysqli_query($db_handle, $timeSQL);
					$db_field = mysqli_fetch_assoc($timeresult);
					$newid=$db_field['role_id'];		

					TALI_Create_History_Report('created', $module, $db_handle, 'tali_personnel_roles', 'role_id', $newid, 'Role ID#', 'name');						
					
					$role_addname = "";
					$role_addweight = "";
					
					header ("Location: personnel.php?sub=configuration");
					exit();
				}
			}
			else
			{
				$errorMessage = "ERROR: Unexpected role entry.";
			}
		}
	}
	
	//Return - Configure Designations
	if (isset($_POST['desig_bu'])) {
		//Check which button was clicked to determine action
		if ((isset($_POST['desig_add_name'])) && (isset($_POST['desig_add_weight'])) && (isset($_POST['desig_add_leader']))) {
			$desig_addname = $_POST['desig_add_name'];
			$desig_addweight = $_POST['desig_add_weight'];
			$desig_addleader = $_POST['desig_add_leader'];
			if (isset($_POST['designation_radio'])) {
				$desig_addreportsTo = $_POST['designation_radio'];
			}
			else
			{
				//Reports to no one/is top of chain of command
				$desig_addreportsTo = 0;
			}
			$SQL = "SELECT * FROM tali_personnel_roster WHERE personnel_id=$desig_addleader";
			if (($desig_addname != "") && ($desig_addweight != "")) {
				if ((isset($_GET['id'])) && (isset($_GET['button']))) {
					$designation_id = $_GET['id'];
					//Edit OR Delete, depending on button return
					switch ($_GET['button']) {
						//Edit
						case "desig_edit": 	
							$desig_addname_sql = htmlspecialchars($desig_addname);
							$desig_addname_sql = TALI_quote_smart($desig_addname_sql, $db_handle);
							
							$desig_addleader_sql = htmlspecialchars($desig_addleader);
							$desig_addleader_sql = TALI_quote_smart($desig_addleader_sql, $db_handle);

							$desig_addweight_sql = htmlspecialchars($desig_addweight);
							$desig_addweight_sql = TALI_quote_smart($desig_addweight_sql, $db_handle);
							
							$desig_addreportsTo_sql = htmlspecialchars($desig_addreportsTo);
							$desig_addreportsTo_sql = TALI_quote_smart($desig_addreportsTo_sql, $db_handle);
							
							$SQL = "UPDATE tali_personnel_designations SET name=$desig_addname_sql, weight=$desig_addweight_sql, leader_personnel_id=$desig_addleader_sql, reportsto_designation_id=$desig_addreportsTo_sql WHERE designation_id=$designation_id"; 
							$result = mysqli_query($db_handle, $SQL);
							
							TALI_Create_History_Report('edited', $module, $db_handle, 'tali_personnel_designations', 'designation_id', $designation_id, 'DesignationID#', 'name');

							header ("Location: personnel.php?sub=configuration");
							exit();
						break;
						//Delete
						case "desig_delete":
							//First check to make sure that deleting this wouldn't destabilize things
							$designation_id = $_GET['id'];
							$SQL = "SELECT * FROM tali_personnel_roster WHERE designation_id=$designation_id";
							$result = mysqli_query($db_handle, $SQL);
							$num_rows = mysqli_num_rows($checkresult);
							if ($num_rows > 0) {
								$errorMessage = "ERROR: Can't delete designation while personnel are assigned to it!";
								goto ErrorTriggered;
							}
							
							$checkSQL = "SELECT * FROM tali_personnel_designations WHERE reportsto_designation_id=$designation_id";
							$checkresult = mysqli_query($db_handle, $checkSQL);
							$num_rows = mysqli_num_rows($checkresult);
							if ($num_rows > 0) {
								$errorMessage = "ERROR: Can't delete designation while other designations still report to it!";
								goto ErrorTriggered;
							}
								
							TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_personnel_designations', 'designation_id', $designation_id, 'Designation ID#', 'name');
						
							$delSQL = "DELETE FROM tali_personnel_designations WHERE designation_id = $designation_id";
							$delresult = mysqli_query($db_handle, $delSQL);
							
							header ("Location: personnel.php?sub=configuration");
							exit();
						break;
					}
				}
				else
				//Add
				{
					$desig_addname_sql = htmlspecialchars($desig_addname);
					$desig_addname_sql = TALI_quote_smart($desig_addname_sql, $db_handle);

					$desig_addleader_sql = htmlspecialchars($desig_addleader);
					$desig_addleader_sql = TALI_quote_smart($desig_addleader_sql, $db_handle);
					
					$desig_addweight_sql = htmlspecialchars($desig_addweight);
					$desig_addweight_sql = TALI_quote_smart($desig_addweight_sql, $db_handle);
					
					$desig_addreportsTo_sql = htmlspecialchars($desig_addreportsTo);
					$desig_addreportsTo_sql = TALI_quote_smart($desig_addreportsTo_sql, $db_handle);
					
					$SQL = "INSERT INTO tali_personnel_designations (name, leader_personnel_id, reportsto_designation_id, weight) VALUES ($desig_addname_sql, $desig_addleader_sql, $desig_addreportsTo_sql, $desig_addweight_sql)";
					$result = mysqli_query($db_handle, $SQL);
					
					$timeSQL = "SELECT * FROM tali_personnel_designations ORDER BY designation_id DESC LIMIT 1";
					$timeresult = mysqli_query($db_handle, $timeSQL);
					$db_field = mysqli_fetch_assoc($timeresult);
					$newid=$db_field['designation_id'];		

					TALI_Create_History_Report('created', $module, $db_handle, 'tali_personnel_designations', 'designation_id', $newid, 'Designation ID#', 'name');						
					
					$desig_addname = "";
					$desig_addleader = "";
					$desig_addweight = "";
					$desig_addreportsTo = "";
					
					header ("Location: personnel.php?sub=configuration");
					exit();
				}
			}
			else
			{
				$errorMessage = "ERROR: Designation Error involving $desig_addname!";
			}
		}
		else
		{
			$errorMessage = "ERROR: Incomplete fields to manipulate designations!";
		}
	}
	if (isset($_POST['desig_button_deactivate'])) {
		$designation_id = $_GET['id'];
		
		$SQL = "SELECT * FROM tali_personnel_roster WHERE designation_id=$designation_id";
		$result = mysqli_query($db_handle, $SQL);
		$num_rows = mysqli_num_rows($result);
		if ($num_rows > 0) {
			$errorMessage = "ERROR: Can't deactivate designation while personnel are assigned to it!";
			goto ErrorTriggered;
		}
		
		$checkSQL = "SELECT * FROM tali_personnel_designations WHERE reportsto_designation_id=$designation_id";
		$checkresult = mysqli_query($db_handle, $checkSQL);
		$num_rows = mysqli_num_rows($checkresult);
		if ($num_rows > 0) {
			$errorMessage = "ERROR: Can't deactivate designation while other designations still report to it!";
			goto ErrorTriggered;
		}
		
		$SQL = "UPDATE tali_personnel_designations SET inactive=1 WHERE designation_id=$designation_id"; 
		$result = mysqli_query($db_handle, $SQL);
		
		TALI_Create_History_Report('deactivated', $module, $db_handle, 'tali_personnel_designations', 'designation_id', $designation_id, 'DesignationID#', 'name');

		header ("Location: personnel.php?sub=configuration");
		exit();
	}
	if (isset($_POST['desig_button_reactivate'])) {
		$designation_id = $_POST['reactivate_designation'];
		if ($designation_id == "") {
			$errorMessage = "ERROR: Click a designation in order to reactivate it!";
			goto ErrorTriggered;
		}
		
		$SQL = "UPDATE tali_personnel_designations SET inactive=0 WHERE designation_id=$designation_id"; 
		$result = mysqli_query($db_handle, $SQL);
		
		TALI_Create_History_Report('reactivated', $module, $db_handle, 'tali_personnel_designations', 'designation_id', $designation_id, 'DesignationID#', 'name');

		header ("Location: personnel.php?sub=configuration");
		exit();
	}
}

//Page Header

//GoTo from error event
ErrorTriggered:

echo "
	<div class=\"content PageFrame\">
		<h1><strong>Configuration</strong></h1>
		<p>On this page you can set a variety of settings in order to customize the TALI Personnel module to best suit your team's needs.</p>
";
	
if ($errorMessage != "") {
	echo "
		<p><font color=\"red\">$errorMessage</font></p>
	";
};
	
echo "
	</div>
";

//bug - ever ganna do this?
//Configure Visibility

//bug - finish this?
/* Commented out because not done
echo "
	<div class=\"content PageFrame\">
		<h1><strong>Configure Visibility</strong></h1>
		<p></p>
	</div>
";
*/

//Configure Ranks

echo "
	
	<div class=\"content PageFrame\">
		<h1><strong>Configure Ranks</strong></h1>
		
		<table class=\"ranksconfigtb\">
		<col width=\"20%\">
		<col width=\"5%\">
		<col width=\"10%\">
		<col width=\"5%\">
		<col width=\"3%\">
		<col width=\"3%\">
		<tr>
			<th>Name</th>
			<th>Abbreviation</th>
			<th>Image</th>
			<th>Weight</th>
			<th></th>
			<th></th>
		</tr>
";

$rankSQL = "SELECT * FROM tali_personnel_ranks ORDER BY weight DESC";
$rankresult = mysqli_query($db_handle, $rankSQL);
	
while ($db_field = mysqli_fetch_assoc($rankresult)) {
	$rank_id=$db_field['rank_id'];
	$name=$db_field['name'];
	$abbreviation=$db_field['abbreviation'];
	$image=$db_field['image'];
	$weight=$db_field['weight'];
	echo "
		<tr>
			<td style=\"text-align:center;\">$name</td>
			<td style=\"text-align:center;\">$abbreviation</td>
			<td style=\"text-align:center;\"><img src=\"".$_SESSION['TALI_Domain_URL']."".$_SESSION['TALI_Ranks_Images_Directory']."$image\" alt=\"Rank\"></img><br/>$image</td>
			<td style=\"text-align:center;\">$weight</td>
			<td style=\"text-align:center;\">
				<a href=\"personnel.php?sub=configuration&id=$rank_id&button=rank_edit\">
					<img src=\"../Images/Display/Icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
				</a>
			</td>
			<td style=\"text-align:center;\">
				<a href=\"personnel.php?sub=configuration&id=$rank_id&button=rank_delete\">
					<img src=\"../Images/Display/Icons/delete.png\" alt=\"Delete Icon\" name=\"Delete Icon\">
				</a>
			</td>
		</tr>
	";
}

echo "
	</table>
";

//Check if initial Edit/Delete button clicked

if ((isset($_GET['id'])) && (isset($_GET['button']))) {
	switch ($_GET['button']) {
		case "rank_edit": 
			$rank_id = $_GET['id'];
			$SQL = "SELECT * FROM tali_personnel_ranks WHERE rank_id=$rank_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			
			$rank_addname=$db_field['name'];	
			$rank_addabbreviation=$db_field['abbreviation'];	
			$rank_addimage=$db_field['image'];
			$rank_addweight=$db_field['weight'];	
			break;
		case "rank_delete":
			$rank_id = $_GET['id'];
			$SQL = "SELECT * FROM tali_personnel_ranks WHERE rank_id=$rank_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			
			$rank_addname=$db_field['name'];	
			$rank_addabbreviation=$db_field['abbreviation'];	
			$rank_addimage=$db_field['image'];
			$rank_addweight=$db_field['weight'];	
			break;
	}
}
	
echo "
		<table class=\"ranksconfigtb\">
			<col width=\"20%\">
			<col width=\"5%\">
			<col width=\"10%\">
			<col width=\"5%\">
			<tr>
				<th>Name</th>
				<th>Abbreviation</th>
				<th>Image</th>
				<th>Weight</th>
			</tr>
			<tr>
				<td>
					<input type=\"text\" class=\"bo\" name=\"tali_modules_personnel_ranks_add_addname\" form=\"tali_modules_personnel_ranks_add_form\" value=\"$rank_addname\">
				</td>
				<td>
					<input type=\"text\" class=\"bo\" name=\"tali_modules_personnel_ranks_add_addabbreviation\" form=\"tali_modules_personnel_ranks_add_form\" value=\"$rank_addabbreviation\">
				</td>
				<td>
";

if ((isset($_GET['button'])) && ((($_GET['button']) == "rank_edit") || (($_GET['button']) == "rank_delete"))) {
	echo "
					$rank_addimage
	";
}
else
{
	echo "
					<input type=\"file\" name=\"rank_file\" id=\"rank_file\" form=\"tali_modules_personnel_ranks_add_form\"/>
	";
}

echo "
				</td>
				<td>
					<input type=\"text\" class=\"bo\" name=\"tali_modules_personnel_ranks_add_addweight\" form=\"tali_modules_personnel_ranks_add_form\" value=\"$rank_addweight\">
				</td>
			</tr>
		</table>
";

//Check what button should be displayed
if ((isset($_GET['id'])) && (isset($_GET['button']))) {
	$rank_id = $_GET['id'];
	switch ($_GET['button']) {
		case "rank_edit": 
			$rank_form_action = "personnel.php?sub=configuration&id=$rank_id&button=rank_edit";
			$rank_form_value = "Edit Rank";
			break;
		case "rank_delete":
			$rank_form_action = "personnel.php?sub=configuration&id=$rank_id&button=rank_delete";
			$rank_form_value = "Confirm Rank Deletion";
			break;
		default: 
			$rank_form_action = "personnel.php?sub=configuration";
			$rank_form_value = "Add Rank";
			break;
	}
}
else
{
	$rank_form_action = "personnel.php?sub=configuration";
	$rank_form_value = "Add Rank";
}

echo "
		<form method=\"POST\" enctype=\"multipart/form-data\" id=\"tali_modules_personnel_ranks_add_form\" action=$rank_form_action>
			<input type=\"submit\" name=\"ranks_bu\" class=\"bu\" value='$rank_form_value'/>
		</form>
	</div>
";

//Configure Statuses

echo "
	
	<div class=\"content PageFrame\">
		<h1><strong>Configure Statuses</strong></h1>
		
		<table class=\"ranksconfigtb\">
		<col width=\"20%\">
		<col width=\"5%\">
		<col width=\"3%\">
		<col width=\"3%\">
		<tr>
			<th>Name</th>
			<th>Weight</th>
			<th></th>
			<th></th>
		</tr>
";

$SQL = "SELECT * FROM tali_personnel_statuses ORDER BY weight DESC";
$result = mysqli_query($db_handle, $SQL);
	
while ($db_field = mysqli_fetch_assoc($result)) {
	$status_id=$db_field['status_id'];
	$name=$db_field['name'];
	$weight=$db_field['weight'];
	echo "
		<tr>
			<td style=\"text-align:center;\">$name</td>
			<td style=\"text-align:center;\">$weight</td>
			<td style=\"text-align:center;\">
				<a href=\"personnel.php?sub=configuration&id=$status_id&button=status_edit\">
					<img src=\"../Images/Display/Icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
				</a>
			</td>
			<td style=\"text-align:center;\">
				<a href=\"personnel.php?sub=configuration&id=$status_id&button=status_delete\">
					<img src=\"../Images/Display/Icons/delete.png\" alt=\"Delete Icon\" name=\"Delete Icon\">
				</a>
			</td>
		</tr>
	";
}

echo "
	</table>
";

//Check if initial Edit/Delete button clicked

if ((isset($_GET['id'])) && (isset($_GET['button']))) {
	switch ($_GET['button']) {
		case "status_edit": 
			$status_id = $_GET['id'];
			$SQL = "SELECT * FROM tali_personnel_statuses WHERE status_id=$status_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			
			$status_addname=$db_field['name'];	
			$status_addweight=$db_field['weight'];	
			break;
		case "status_delete":
			$status_id = $_GET['id'];
			$SQL = "SELECT * FROM tali_personnel_statuses WHERE status_id=$status_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			
			$status_addname=$db_field['name'];
			$status_addweight=$db_field['weight'];	
			break;
	}
}
	
echo "
		<table class=\"ranksconfigtb\">
			<col width=\"20%\">
			<col width=\"5%\">
			<tr>
				<th>Name</th>
				<th>Weight</th>
			</tr>
			<tr>
				<td>
					<input type=\"text\" class=\"bo\" name=\"status_add_name\" form=\"tali_modules_personnel_status_add_form\" value=\"$status_addname\">
				</td>
				<td>
					<input type=\"text\" class=\"bo\" name=\"status_add_weight\" form=\"tali_modules_personnel_status_add_form\" value=\"$status_addweight\">
				</td>
			</tr>
		</table>
";

//Check what button should be displayed
if ((isset($_GET['id'])) && (isset($_GET['button']))) {
	$status_id = $_GET['id'];
	switch ($_GET['button']) {
		case "status_edit": 
			$status_form_action = "personnel.php?sub=configuration&id=$status_id&button=status_edit";
			$status_form_value = "Edit Status";
			break;
		case "status_delete":
			$status_form_action = "personnel.php?sub=configuration&id=$status_id&button=status_delete";
			$status_form_value = "Confirm Status Deletion";
			break;
		Default:
			$status_form_action = "personnel.php?sub=configuration";
			$status_form_value = "Add Status";
			break;
	}
}
else
{
	$status_form_action = "personnel.php?sub=configuration";
	$status_form_value = "Add Status";
}

echo "
		<form method=\"POST\" id=\"tali_modules_personnel_status_add_form\" action=$status_form_action>
			<input type=\"submit\" name=\"statuses_bu\" class=\"bu\" value='$status_form_value'/>
		</form>
	</div>
";

//Configure Roles

echo "
	
	<div class=\"content PageFrame\">
		<h1><strong>Configure Roles</strong></h1>
		
		<table class=\"ranksconfigtb\">
		<col width=\"20%\">
		<col width=\"5%\">
		<col width=\"3%\">
		<col width=\"3%\">
		<tr>
			<th>Role Name</th>
			<th>Weight</th>
			<th></th>
			<th></th>
		</tr>
";

$SQL = "SELECT * FROM tali_personnel_roles ORDER BY weight DESC";
$result = mysqli_query($db_handle, $SQL);
	
while ($db_field = mysqli_fetch_assoc($result)) {
	$role_id=$db_field['role_id'];
	$name=$db_field['name'];
	$weight=$db_field['weight'];
	echo "
		<tr>
			<td style=\"text-align:center;\">$name</td>
			<td style=\"text-align:center;\">$weight</td>
			<td style=\"text-align:center;\">
				<a href=\"personnel.php?sub=configuration&id=$role_id&button=role_edit\">
					<img src=\"../Images/Display/Icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
				</a>
			</td>
			<td style=\"text-align:center;\">
				<a href=\"personnel.php?sub=configuration&id=$role_id&button=role_delete\">
					<img src=\"../Images/Display/Icons/delete.png\" alt=\"Delete Icon\" name=\"Delete Icon\">
				</a>
			</td>
		</tr>
	";
}

echo "
	</table>
";

//Check if initial Edit/Delete button clicked

if ((isset($_GET['id'])) && (isset($_GET['button']))) {
	switch ($_GET['button']) {
		case "role_edit": 
			$role_id = $_GET['id'];
			$SQL = "SELECT * FROM tali_personnel_roles WHERE role_id=$role_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			
			$role_addname=$db_field['name'];	
			$role_addweight=$db_field['weight'];	
			break;
		case "role_delete":
			$role_id = $_GET['id'];
			$SQL = "SELECT * FROM tali_personnel_roles WHERE role_id=$role_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			
			$role_addname=$db_field['name'];	
			$role_addweight=$db_field['weight'];	
			break;
	}
}
	
echo "
		<table class=\"ranksconfigtb\">
			<col width=\"20%\">
			<col width=\"5%\">
			<tr>
				<th>Role Name</th>
				<th>Weight</th>
			</tr>
			<tr>
				<td>
					<input type=\"text\" class=\"bo\" name=\"role_add_name\" form=\"tali_modules_personnel_role_add_form\" value=\"$role_addname\">
				</td>
				<td>
					<input type=\"text\" class=\"bo\" name=\"role_add_weight\" form=\"tali_modules_personnel_role_add_form\" value=\"$role_addweight\">
				</td>
			</tr>
		</table>
";

//Check what button should be displayed
if ((isset($_GET['id'])) && (isset($_GET['button']))) {
	$role_id = $_GET['id'];
	switch ($_GET['button']) {
		case "role_edit": 
			$role_form_action = "personnel.php?sub=configuration&id=$role_id&button=role_edit";
			$role_form_value = "Edit Role";
			break;
		case "role_delete":
			$role_form_action = "personnel.php?sub=configuration&id=$role_id&button=role_delete";
			$role_form_value = "Confirm Role Deletion";
			break;
		Default:
			$role_form_action = "personnel.php?sub=configuration";
			$role_form_value = "Add Role";
			break;
	}
}
else
{
	$role_form_action = "personnel.php?sub=configuration";
	$role_form_value = "Add Role";
}

echo "
		<form method=\"POST\" id=\"tali_modules_personnel_role_add_form\" action=$role_form_action>
			<input type=\"submit\" name=\"roles_bu\" class=\"bu\" value='$role_form_value'/>
		</form>
	</div>
";

//Configure Designations
		
echo "
	<div class=\"content PageFrame\">
		<h1><strong>Configure Designations</strong></h1>
";

//Check if designation Edit/Delete button clicked

if ((isset($_GET['id'])) && (isset($_GET['button']))) {
	switch ($_GET['button']) {
		case "desig_edit": 
			$designation_id = $_GET['id'];
			$SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$designation_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			
			$desig_addname=$db_field['name'];
			$desig_addweight=$db_field['weight'];	
			$desig_addleader=$db_field['leader_personnel_id'];;
			break;
		case "desig_delete":
			$designation_id = $_GET['id'];
			$SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$designation_id";
			$result = mysqli_query($db_handle, $SQL);
			$db_field = mysqli_fetch_assoc($result);
			
			$desig_addname=$db_field['name'];	
			$desig_addweight=$db_field['weight'];	
			$desig_addleader=$db_field['leader_personnel_id'];;
			break;
	}
}

echo "
		<p>Designation details:</p>
		<table class=\"ranksconfigtb\">
			<col width=\"20%\">
			<col width=\"20%\">
			<col width=\"5%\">
			<tr>
				<th>Designation</th>
				<th>Leader</th>
				<th>Weight</th>
			</tr>
			<tr>
				<td>
					<input type=\"text\" class=\"bo\" name='desig_add_name' form='tali_modules_personnel_designation_add_form' value=\"$desig_addname\">
				</td>
				";
				
				echo "
				<td>
					<select class=\"tali_personnel_configuration_designations_dropdown\" name=\"desig_add_leader\" form=\"tali_modules_personnel_designation_add_form\" value=\"$desig_addleader\">
						<option value=\"\">Select a Leader</option>
				";
				
				$SQL = "SELECT * FROM tali_personnel_roster JOIN tali_personnel_ranks ON tali_personnel_roster.rank_id=tali_personnel_ranks.rank_id WHERE discharged=0 ORDER BY tali_personnel_ranks.weight DESC, tali_personnel_roster.date_promoted ASC, tali_personnel_roster.date_enlisted ASC";
				$result = mysqli_query($db_handle, $SQL);
				while ($db_field = mysqli_fetch_assoc($result)) {
					$personnel_id=$db_field['personnel_id'];
					$rank_id=$db_field['rank_id'];
					$rankSQL = "SELECT * FROM tali_personnel_ranks WHERE rank_id=$rank_id";
					$rankresult = mysqli_query($db_handle, $rankSQL);
					$rank_db_field = mysqli_fetch_assoc($rankresult);
					$rank_abr=$rank_db_field['abbreviation'];
					$firstname=$db_field['firstname'];
					$lastname=$db_field['lastname'];
					$compiled = $rank_abr . " " . $firstname . " " . $lastname;
					
					if ($desig_addleader == $personnel_id) {
					$selected = 'selected="selected"';
					}
					else
					{
						$selected = '';
					}
					
					echo "
						<option value=\"$personnel_id\" ".$selected.">$compiled</option>
					";
				}
				echo "
					</select>
				
				</td>
				
				<td>
					<input type=\"text\" class=\"bo\" name='desig_add_weight' form='tali_modules_personnel_designation_add_form' value=\"$desig_addweight\">
				</td>
			</tr>
		</table>
";

//Prepare a table of designations for selection/action
echo "
		<p>Select a designation to report to:</p>
						
		<table class=\"ranksconfigtb\">
		<col width=\"3%\">
		<col width=\"20%\">
		<col width=\"17%\">
		<col width=\"5%\">
		<col width=\"3%\">
		<col width=\"3%\">
		<tr>
			<th></th>
			<th>Designation</th>
			<th>Leader</th>
			<th>Weight</th>
			<th></th>
			<th></th>
		</tr>
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
	$weight=$db_field['weight'];
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
	
	//Attempt to define specific designation's personnel leader
	$leader=$db_field['leader_personnel_id'];
	if ($leader == "0") {
		$leader = "No Leader Listed";
	}
	else
	{
		//Full name of leader with rank
		$exSQL = "SELECT * FROM tali_personnel_roster WHERE personnel_id=$leader";
		$exresult = mysqli_query($db_handle, $exSQL);
		$exdb_field = mysqli_fetch_assoc($exresult);
		$leader_rank_id = $exdb_field['rank_id'];
		$leader_firstname = $exdb_field['firstname'];
		$leader_lastname = $exdb_field['lastname'];
		$seSQL = "SELECT * FROM tali_personnel_ranks WHERE rank_id=$leader_rank_id";
		$seresult = mysqli_query($db_handle, $seSQL);
		$sedb_field = mysqli_fetch_assoc($seresult);
		$leader_rank = $sedb_field['abbreviation'];
		$leader = $leader_rank . " " . $leader_firstname . " " . $leader_lastname;
	}
	
	$desig_radio_checked = "";
	if (isset($_GET['id'])) {
		$reportsTo_SQL_id = $_GET['id'];
		$reportsTo_SQL = "SELECT * FROM tali_personnel_designations WHERE designation_id=$reportsTo_SQL_id";
		$reportsTo_result = mysqli_query($db_handle, $reportsTo_SQL);
		$reportsTo_db_field = mysqli_fetch_assoc($reportsTo_result);
	
		if ($designation_id == ($reportsTo_db_field['reportsto_designation_id'])) {
			$desig_radio_checked = "checked='checked'";
		}
	}
	
	echo "
		<tr>
			<td style=\"text-align:center;\">
				<input type=\"Radio\" form=\"tali_modules_personnel_designation_add_form\" name=\"designation_radio\" value=\"$designation_id\" ".$desig_radio_checked.">
			</td>
			<td style=\"text-align:center;\">$full_desig_name</td>
			<td style=\"text-align:center;\">$leader</td>
			<td style=\"text-align:center;\">$weight</td>
			<td style=\"text-align:center;\">
				<a href=\"personnel.php?sub=configuration&action=designation&id=$designation_id&button=desig_edit\">
					<img src=\"../Images/Display/Icons/edit.png\" alt=\"Edit Icon\" name=\"Edit Icon\">
				</a>
			</td>
			<td style=\"text-align:center;\">
				<a href=\"personnel.php?sub=configuration&action=designation&id=$designation_id&button=desig_delete\">
					<img src=\"../Images/Display/Icons/delete.png\" alt=\"Delete Icon\" name=\"Delete Icon\">
				</a>
			</td>
		</tr>
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
		</table>
";

//Check what button should be displayed
if ((isset($_GET['id'])) && (isset($_GET['button']))) {
	$designation_id = $_GET['id'];
	switch ($_GET['button']) {
		case "desig_edit": 
			$desig_form_action = "personnel.php?sub=configuration&action=designation&id=$designation_id&button=desig_edit";
			$desig_form_value = "Edit " . $desig_addname . " Designation";
			break;
		case "desig_delete":
			$desig_form_action = "personnel.php?sub=configuration&action=designation&id=$designation_id&button=desig_delete";
			$desig_form_value = "Confirm " . $desig_addname . " Designation Deletion";
			break;
		Default:
			$desig_form_action = "personnel.php?sub=configuration&action=designation";
			$desig_form_value = "Add Designation";
			break;
	}
}
else
{
	$desig_form_action = "personnel.php?sub=configuration&action=designation";
	$desig_form_value = "Add Designation";
}
				
	echo "
			<form method=\"POST\" id='tali_modules_personnel_designation_add_form' action=$desig_form_action>
				<input type=\"submit\" name='desig_bu' class=\"bu\" value='$desig_form_value'/>
			</form>
	";
	
	if ((isset($_GET['id'])) && (isset($_GET['button'])) && ($_GET['button'] == "desig_edit")) {
		echo "
			<form method=\"POST\" id='tali_modules_personnel_designation_add_form' action=$desig_form_action>
				<input type=\"submit\" name='desig_button_deactivate' class=\"bu\" value='Deactivate Designation'/>
			</form>
		";
	}
	
	//Reactivation section
	echo "
			<p>Select an inactive designation to reactivate:</p>
			<select form=\"tali_modules_personnel_designation_reactivate_form\" class=\"desigSelect_report\" name=\"reactivate_designation\">
				<option value=\"\" selected>Select a Designation</option>
	";
	
	//Select all designations
	$SQL = "SELECT * FROM tali_personnel_designations WHERE inactive=1";
	$result = mysqli_query($db_handle, $SQL);
	while ($db_field = mysqli_fetch_assoc($result)) {
		$designation_id = $db_field['designation_id'];
		$designation_name = $db_field['name'];
		echo "
				<option value=\"$designation_id\">$designation_name</option>
		";
	}
	echo "
			</select>
			<form method=\"POST\" id='tali_modules_personnel_designation_reactivate_form' action=$desig_form_action>
				<input type=\"submit\" name='desig_button_reactivate' class=\"bu\" value='Reactivate Designation'/>
			</form>
	";
echo "
	</div>
";
?>