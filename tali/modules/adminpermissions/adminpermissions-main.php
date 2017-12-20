<?php
//Stock variables
$newdisplayMessage = "";
$diff = "";
$module = "TALI_Admin_Permissions";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}
	
TALI_sessionCheck($module, $db_handle);

//Get number of permission levels currently in db, which is used as baseline throughout page
$SQL = "SELECT level FROM tali_admin_permissions LIMIT 1";
$result = mysqli_query($db_handle, $SQL);
$db_field = mysqli_fetch_assoc($result);
$levels = $db_field['level'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//Level amount submit button clicked
	if (isset($_POST['levelspost'])) {
		//Round entered level for cleanliness
		$newlevel = round($_POST['levelsfield']);
		//Check for invalid level entry
		if (($newlevel < 1) OR ($newlevel == "")) {
			$newdisplayMessage = "You must enter a numeric value greater than 0!";
		}
		else
		{
			//Level entry is good, so move on
			//Check if level changed
			if ($newlevel != $levels) {
				TALI_Create_History_Report('changed to '.$newlevel.'', $module, $db_handle, 'tali_admin_permissions', 'level', $levels, 'Admin Permission Level ', 'level');
				
				$SQL = "UPDATE tali_admin_permissions SET level=$newlevel WHERE level=$levels";
				$result = mysqli_query($db_handle, $SQL);
				
				header ("Location: adminpermissions.php");
				exit();
			}
		}
	}
	
	//Specific accesses submit button clicked
	if (isset($_POST['accesslevelbu'])) {
		$level = $_POST['accesslevel'];
		
		//Create array of an array of each module and its permitted pre-submit access levels
		$SQL = "SELECT * FROM tali_modules";
		$result = mysqli_query($db_handle, $SQL);
		$module_array = [];
		while ($db_field = mysqli_fetch_assoc($result)) {
			$module_subarray = [];
			$module_subarray[] = $db_field['module'];
			//Convert stored string of permitted levels to an array
			$module_subarray[] = explode(",", $db_field['permission']);
			//Add sub-array to array
			$module_array[] = $module_subarray;
		}
		
		$cnt = 0;
		forEach ($module_array as $module_subarray) {
			$module = $module_subarray[0];
			$permitted_levels_array = $module_subarray[1];
			if (in_array($level, $permitted_levels_array)) {
				if (!in_array($module, $_POST['accesscheckbox'])) {
					//Old was permitted, new is not, so remove
					//Find location of $level in the array
					$key = array_search($level, $permitted_levels_array);
					//Remove level from the array
					unset($module_array[$cnt][1][$key]);
					//Re-index the array so there are no blanks
					array_values($module_array[$cnt][1]);
					//Array-to-string for SQL
					$permitted_levels_array_sql = implode(",", $module_array[$cnt][1]);
					$permitted_levels_array_sql = htmlspecialchars($permitted_levels_array_sql);
					$permitted_levels_array_sql = TALI_quote_smart($permitted_levels_array_sql, $db_handle);
					
					$SQL = "UPDATE tali_modules SET permission=$permitted_levels_array_sql WHERE module='$module'"; 
					$result = mysqli_query($db_handle, $SQL);
				}
			}
			else
			{
				if (in_array($module, $_POST['accesscheckbox'])) {
					//Old was not permitted, new is permitted, so add
					$module_array[$cnt][1][] = $level;
					//Array-to-string for SQL
					$permitted_levels_array_sql = implode(",", $module_array[$cnt][1]);
					$permitted_levels_array_sql = htmlspecialchars($permitted_levels_array_sql);
					$permitted_levels_array_sql = TALI_quote_smart($permitted_levels_array_sql, $db_handle);
					
					$SQL = "UPDATE tali_modules SET permission=$permitted_levels_array_sql WHERE module='$module'"; 
					$result = mysqli_query($db_handle, $SQL);
				}
			}
			$cnt++;
		}
		
		TALI_Create_History_Report('modified', $module, $db_handle, 'tali_admin_permissions', 'level', $level, 'Accesses for Level ', 'level');
		
		header ("Location: adminpermissions.php");
		exit();
	}
}

echo "
	<main>
		<div class=\"tali-container\">
			<div class=\"tali-page-frame\">
				<h1>Manage Admin Access</h1>
				<p>On this page you can manage the number of access levels there are in addition to customize the access available to each level, along with adjusting the visibility of modules.</p>
			</div>
			
			<div class=\"tali-page-frame\">
				<h1>Manage Access Level Amount</h1>
				<p>Define the number of levels of admin access:</p>
				<form method=\"POST\" id=\"i_tali_admin_permissions_levelsform\" action=\"adminpermissions.php\">
					<p>
					<input type=\"integer\" maxlength=\"4\" class=\"tali-admin_permissions-input-levels\" name=\"levelsfield\" value=\"$levels\">
					<input type=\"Submit\" name=\"levelspost\" value=\"Set Level Amount\">
					</p>
				</form>
				<p>
				<font color=\"red\">$newdisplayMessage</font>
				</p>
			</div>
			
			<div class=\"tali-page-frame\">
				<h1>Manage Admin Access Settings</h1>
				<p>One level at a time, check the boxes for which modules you want to allow that individual level access to, then click Submit to save changes.</p>
";

//Create array of an array of each module and its permitted access levels
$SQL = "SELECT * FROM tali_modules";
$result = mysqli_query($db_handle, $SQL);
$module_array = [];
while ($db_field = mysqli_fetch_assoc($result)) {
	$module_subarray = [];
	$module_subarray[] = $db_field['module'];
	//Convert stored string of permitted levels to an array
	$module_subarray[] = explode(",", $db_field['permission']);
	//Add sub-array to array
	$module_array[] = $module_subarray;
}

$level_cnt = 0;
//Run through loop for each access level
while ($level_cnt < $levels) {
	//$level_cnt is the current level being cycled
	$level_cnt++;
	echo "
				<form action=\"adminpermissions.php\" method=\"post\">
					<p>
					Level $level_cnt:
					<br/>
	";
	
	//Loop for each module
	forEach ($module_array as $module_subarray) {
		$module = $module_subarray[0];
		$permitted_levels = $module_subarray[1];
		//If level is listed in permitted levels array, check the box
		if (in_array($level_cnt,$permitted_levels)) {
			//Permitted
			echo "
					<input type=\"checkbox\" name=\"accesscheckbox[]\" checked=\"checked\" value=\"$module\"/>
					$module
					<br/>
			";
		}
		else
		{
			//Not permitted
			echo "
					<input type=\"checkbox\" name=\"accesscheckbox[]\" value=\"$module\"/>
					$module
					<br/>
			";
		}
	}
	echo "
					</p>
					<p>
					<input type=\"hidden\" name=\"accesslevel\" value=\"$level_cnt\"/>
					<input type=\"submit\" name=\"accesslevelbu\" value=\"Submit\"/>
					</p>
				</form>
				<br/>
	";
}
echo "
			</div>
		</div>
	</main>
";
?>