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
$SQL = "SELECT * FROM tali_admin_permissions";
$result = mysqli_query($db_handle, $SQL);
$levels = mysqli_num_rows($result);

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
				//Delete - New level is lower, so delete all higher levels
				if ($newlevel < $levels) {
					TALI_Create_History_Report('deleted', $module, $db_handle, 'tali_admin_permissions', 'level', $levels, 'Admin Permission Level ', 'level');
					
					$SQL = "DELETE FROM tali_admin_permissions WHERE level>$newlevel";
					$result = mysqli_query($db_handle, $SQL);
				}
				else
				{
					//Add - New level is same or higher, so add new level entries as appropriate
					$diff = $levels;
					while ($diff < $newlevel) {
						$diff = $diff + 1;
						$SQL = "INSERT INTO tali_admin_permissions (level) VALUES ($diff)";
						$result = mysqli_query($db_handle, $SQL);
						
						TALI_Create_History_Report('created', $module, $db_handle, 'tali_admin_permissions', 'level', $newlevel, 'Admin Permission Level ', 'level');
					}
				}
				
				header ("Location: adminpermissions.php");
				exit();
			}
		}
	}
	
	//Specific accesses submit button clicked
	if (isset($_POST['accesslevelbu'])) {
		$level = $_POST['accesslevel'];
		
		$SQL = "SELECT * FROM tali_modules";
		$result = mysqli_query($db_handle, $SQL);
		$module_array = [];
		while ($db_field = mysqli_fetch_assoc($result)) {
			$module = $db_field['module'];
			$module_array[] = "$module='0'";
		}
		$SQL = "UPDATE tali_admin_permissions SET ". implode(",", $module_array) ." WHERE level=$level"; 
		$result = mysqli_query($db_handle, $SQL);
		
		$columnset_array = [];
		forEach ($_POST['accesscheckbox'] as $module) {
			$columnset_array[] = "$module='1'";
		}
		$SQL = "UPDATE tali_admin_permissions SET ". implode(",", $columnset_array) ." WHERE level=$level"; 
		$result = mysqli_query($db_handle, $SQL);
		
		TALI_Create_History_Report('modified', $module, $db_handle, 'tali_admin_permissions', 'level', $level, 'Accesses for Level ', 'level');
		
		header ("Location: adminpermissions.php");
		exit();
	}
	
	//Module visibility submit button clicked
	if (isset($_POST['modulesbu'])) {				
		$SQL = "UPDATE tali_modules SET enabled='0'"; 
		$result = mysqli_query($db_handle, $SQL);
		
		forEach ($_POST['accesscheckbox'] as $module) {
			$SQL = "UPDATE tali_modules SET enabled='1' WHERE module='$module'"; 
			$result = mysqli_query($db_handle, $SQL);
		}
		
		TALI_Create_History_Report('modified', $module, $db_handle, 'tali_modules', 'id', 1, 'Module Visibility ', 'id');
		
		header ("Location: adminpermissions.php");
		exit();
	}
}

echo "
	<div class=\"content PageFrame\">
		<h1><strong>Manage Admin Access</strong></h1>
		<p>On this page you can manage the number of access levels there are in addition to customize the access available to each level, along with adjusting the visibility of modules.</p>
	</div>
	
	<div class=\"content PageFrame\">
		<h1><strong>Manage Visible Modules</strong></h1>
		<p>Define which modules are enabled and thus visible to anyone with access:</p>
		<form action=\"adminpermissions.php\" class=\"c_tali_manage_modules_enabled_form\"method=\"post\">
	";
	
	$SQL = "SELECT * FROM tali_modules";
	$result = mysqli_query($db_handle, $SQL);
	$module_array = [];
	while ($db_field = mysqli_fetch_assoc($result)) {
		$module_array[] = $db_field['module'];
		$enabledvalue_array[] = $db_field['enabled'];
	}
		
	$cnt = 0;
	forEach ($module_array as $module) {
		$value = $enabledvalue_array[$cnt];
		if ($value == 0) {
			echo "
			<input type=\"checkbox\" name=\"accesscheckbox[]\" value=\"$module\"/>
			$module
			<br/>
			";
		}
		else
		{
			echo "
			<input type=\"checkbox\" name=\"accesscheckbox[]\" checked=\"checked\" value=\"$module\"/>
			$module
			<br/>
			";
		}
		$cnt++;
	}
	echo "
			<input type=\"submit\" name=\"modulesbu\" value=\"Submit\"/>
		</form>
		<br/>
	";
	
	echo " 
		
	</div>
	
	<div class=\"content PageFrame\">
		<h1><strong>Manage Access Level Amount</strong></h1>
		<p>Define the number of levels of admin access:</p>
		<form method=\"POST\" id=\"i_tali_admin_permissions_levelsform\" class=\"c_tali_admin_permissions_levelsform\" action=\"adminpermissions.php\">
			<input type=\"integer\" maxlength=\"4\" class=\"c_tali_admin_permissions_levelsfield\" name=\"levelsfield\" value=\"$levels\">
			<input type=\"Submit\" class=\"c_tali_admin_permissions_levelsbu\" name=\"levelspost\" value=\"Set Level Amount\">
		</form>
		<br/>
		<p><font color=\"red\">$newdisplayMessage</font></p>
	</div>
	
	<div class=\"content PageFrame\">
		<h1><strong>Manage Admin Access Settings</strong></h1>
		<p>One level at a time, check the boxes for which modules you want to allow that individual level access to, then click Submit to save changes.</p>
";

$SQL = "SELECT * FROM tali_modules";
$result = mysqli_query($db_handle, $SQL);
$module_array = [];
while ($db_field = mysqli_fetch_assoc($result)) {
	$module_array[] = $db_field['module'];
}

$SQL = "SELECT * FROM tali_admin_permissions";
$result = mysqli_query($db_handle, $SQL);
while ($db_field = mysqli_fetch_assoc($result)) {
	$level = $db_field['level'];
	echo "
		<form action=\"adminpermissions.php\" class=\"c_tali_admin_permissions_form\"method=\"post\">
		Level $level:
		<br/>
	";
	
	forEach ($module_array as $module) {
		$value = $db_field[$module];
		if ($value == 0) {
			echo "
			<input type=\"checkbox\" name=\"accesscheckbox[]\" value=\"$module\"/>
			$module
			<br/>
			";
		}
		else
		{
			echo "
			<input type=\"checkbox\" name=\"accesscheckbox[]\" checked=\"checked\" value=\"$module\"/>
			$module
			<br/>
			";
		}
	}
	echo "
			<input type=\"hidden\" name=\"accesslevel\" value=\"$level\"/>
			<input type=\"submit\" name=\"accesslevelbu\" value=\"Submit\"/>
		</form>
		<br/>
	";
}
echo "
	</div>
";
?>