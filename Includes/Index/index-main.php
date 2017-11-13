<?php
//Stock variables
$error = "";
$module = "TALI_Index";
	
//Connect to database
$db_handle = TALI_dbConnect(); 
if (is_bool($db_handle)) {
	exit("Error Loading Page: Database connection failed.");
}

TALI_sessionCheck($module, $db_handle);
if (isset($_GET['denied'])) {
	$denied = $_GET['denied'];
	$error = "You do not have permission to access the $denied module!";
}

echo "
	<div class=\"content PageFrame\">
		<h1><strong>Team Administration/Logistics Interface (TALI)</strong></h1>
		<p>Welcome to TALI, a php-based interface system designed to create and maintain organized, team-oriented web content. Use the icons below to access the TALI modules and navigate through the interface. At any time you can click the banner up top to return to this index page.</p>
	</div>
	
	<div class=\"selectcontent PageFrame\">
		<h1><strong>TALI Modules</strong></h1>
		<p style=\"text-align:center;color:red\">$error</p>
";
		
$SQL = "SELECT * FROM tali_modules";
$result = mysqli_query($db_handle, $SQL);
$module_array = [];
while ($db_field = mysqli_fetch_assoc($result)) {
	$module_array[] = $db_field['module'];
	$enabledvalue_array[] = $db_field['enabled'];
}

$enabledmodule_array = [];
$cnt = 0;
forEach ($enabledvalue_array as $value) {
	if ($value == 1) {
		$enabledmodule_array[] = $module_array[$cnt];
	}
	$cnt++;
}
		
echo "
		<div class=\"row\">
			<div class=\"col\">
				<a href=\"login.php\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-LogOut.png\" alt=\"Log Out Icon\" name=\"Log Out Icon\">
					<p>Log Out</p>
				</a>
			</div>
";
if (in_array('TALI_Admin_Accounts',$enabledmodule_array,true)) {
	echo "
			<div class=\"col\">
				<a href=\"Modules/adminaccounts.php\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-ManageAdminAccounts.png\" alt=\"Manage Admin Accounts Icon\" name=\"Manage Admin Accounts Icon\">
					<p>Manage Admin Accounts</p>
				</a>
			</div>
	";
}

if (in_array('TALI_Admin_Permissions',$enabledmodule_array,true)) {
	echo "
			<div class=\"col\">
				<a href=\"Modules/adminpermissions.php\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-ManageAdminPermissions.png\" alt=\"Manage Admin Permissions Icon\" name=\"Manage Admin Permissions Icon\">
					<p>Manage Admin Permissions</p>
				</a>
			</div>
	";
}

echo "
			<div class=\"col\">
				<a target=\"_blank\" href=\"".$_SESSION['TALI_Index_cPanel_Link']."\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-AccesscPanel.png\" alt=\"Access cPanel Icon\" name=\"Access cPanel Icon\">
					<p>Access cPanel</p>
				</a>
			</div>
			<div class=\"col\">
				<a href=\"".$_SESSION['TALI_Domain_URL']."\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-Home.png\" alt=\"Return to Homepage Icon\" name=\"Return to Homepage Icon\">
					<p>Return To Homepage</p>
				</a>
			</div>
		</div>
		
		<div class=\"row\">
";
if (in_array('TALI_Pages',$enabledmodule_array,true)) {
	echo "
			<div class=\"col\">
				<a href=\"Modules/pages.php\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-Pages.png\" alt=\"Pages & Content Icon\" name=\"Pages & Content Icon\">
					<p>Manage Pages & Content</p>
				</a>
			</div>
	";
}
if (in_array('TALI_News',$enabledmodule_array,true)) {
	echo "
			<div class=\"col\">
				<a href=\"Modules/news.php\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-News.png\" alt=\"News Icon\" name=\"News Icon\">
					<p>Manage News Entries</p>
				</a>
			</div>
	";
}
if (in_array('TALI_Home_Slider',$enabledmodule_array,true)) {
	echo "
			<div class=\"col\">
				<a href=\"Modules/homeslider.php\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-HomeSlider.png\" alt=\"Home Slider Icon\" name=\"Home Slider Icon\">
					<p>Manage Home Slider</p>
				</a>
			</div>
	";
}
if (in_array('TALI_Master_History',$enabledmodule_array,true)) {
	echo "
			<div class=\"col\">
				<a href=\"Modules/masterhistory.php\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-MasterHistory.png\" alt=\"Master History Report Icon\" name=\"Master History Report Icon\">
					<p>Master History Report</p>
				</a>
			</div>
	";
}
if (in_array('TALI_Versions',$enabledmodule_array,true)) {
	echo "
			<div class=\"col\">
				<a href=\"Modules/versions.php\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-Versions.png\" alt=\"Software Versions Icon\" name=\"Software Versions Icon\">
					<p>Software Versions</p>
				</a>
			</div>
	";
}

echo "
	
		</div>
		
		<div class=\"row\">
";
if (in_array('TALI_Personnel',$enabledmodule_array,true)) {
	echo "
			<div class=\"col\">
				<a href=\"Modules/personnel.php\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Index-Personnel.png\" alt=\"Personnel Icon\" name=\"Personnel Icon\">
					<p>Manage Personnel</p>
				</a>
			</div>
	";
}

//bug - for 3rd ID so all have drill report access
if (in_array('TALI_Personnel',$enabledmodule_array,true)) {
	echo "
			<div class=\"col\">
				<a href=\"Modules/personnel.php?sub=drillreports\" class=\"thumbnail\">
					<img src=\"Images/Display/Icons/Personnel-DrillReports.png\" alt=\"Drill Reports Icon\" name=\"Drill Reports Icon\">
					<p>Manage Drill Reports (Shortcut)</p>
				</a>
			</div>
	";
}
echo "
		</div>
	</div>
";
?>
